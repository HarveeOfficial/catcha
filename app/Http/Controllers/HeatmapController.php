<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeatmapController extends Controller
{
    public function data(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $base = FishCatch::query()->whereNotNull('latitude')->whereNotNull('longitude');
        if (! $user->isExpert() && ! $user->isAdmin()) {
            $base->where('user_id', $user->id);
        }
        $rows = $base->select(['latitude','longitude','quantity','count'])
            ->limit(10000)
            ->get()
            ->map(function ($r) {
                $w = 1.0;
                if (! is_null($r->quantity)) { $w = (float) $r->quantity; }
                elseif (! is_null($r->count)) { $w = (float) $r->count; }
                return [ (float) $r->latitude, (float) $r->longitude, $w ];
            });
        return response()->json(['points' => $rows]);
    }

    public function view()
    {
        return view('catches.heatmap');
    }

    public function pointInfo(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'zoom' => 'nullable|integer|min:1|max:20',
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $lat = (float) $request->input('lat');
        $lon = (float) $request->input('lon');
        $zoom = (int) ($request->input('zoom', 8));

        // Derive radius km from zoom (approx) – tighter at higher zoom
        $radiusKm = match(true) {
            $zoom >= 15 => 0.25,
            $zoom === 14 => 0.5,
            $zoom === 13 => 1.0,
            $zoom === 12 => 2.5,
            $zoom === 11 => 5.0,
            $zoom === 10 => 10.0,
            $zoom === 9 => 20.0,
            $zoom === 8 => 40.0,
            $zoom === 7 => 80.0,
            $zoom === 6 => 150.0,
            default => 300.0,
        };
        $latDelta = $radiusKm / 111.0; // 1 deg lat ≈ 111 km
        $lonDelta = $radiusKm / (111.0 * max(cos(deg2rad($lat)), 0.01));

        $base = FishCatch::query()->whereNotNull('latitude')->whereNotNull('longitude');
        if (! $user->isExpert() && ! $user->isAdmin()) {
            $base->where('user_id', $user->id);
        }

        $region = (clone $base)
            ->whereBetween('latitude', [$lat - $latDelta, $lat + $latDelta])
            ->whereBetween('longitude', [$lon - $lonDelta, $lon + $lonDelta]);

        $summary = (clone $region)->selectRaw('COUNT(*) as catches, COALESCE(SUM(quantity),0) as total_qty, COALESCE(SUM(count),0) as total_count')->first();

        $species = (clone $region)
            ->selectRaw('species_id, COUNT(*) as catches, COALESCE(SUM(quantity),0) as qty, COALESCE(SUM(count),0) as total_count')
            ->groupBy('species_id')
            ->orderByDesc('qty')
            ->limit(15)
            ->with('species:id,common_name')
            ->get()
            ->map(fn($r) => [
                'species_id' => $r->species_id,
                'name' => $r->species?->common_name ?? 'Unknown',
                'catches' => (int) $r->catches,
                'qty' => (float) $r->qty,
                'count' => (int) $r->total_count,
            ]);

        return response()->json([
            'radius_km' => $radiusKm,
            'summary' => [
                'catches' => (int) ($summary->catches ?? 0),
                'total_qty' => (float) ($summary->total_qty ?? 0.0),
                'total_count' => (int) ($summary->total_count ?? 0),
            ],
            'species' => $species,
        ]);
    }
}
