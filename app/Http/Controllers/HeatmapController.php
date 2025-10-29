<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HeatmapController extends Controller
{
    public function data(Request $request)
    {
        $base = FishCatch::query()->whereNotNull('latitude')->whereNotNull('longitude');
        // Guests see aggregated public data across all users. Authenticated non-experts previously
        // saw only their own data; for public landing heatmap, expose anonymized aggregate for all.
        // If you need to restrict for logged-in non-experts later, detect Auth::check() and filter.
        $catches = $base->select(['id', 'latitude', 'longitude', 'quantity', 'count', 'species_id'])
            ->with('species:id,common_name')
            ->limit(10000)
            ->get();

        // Get all active zones with their geometries
        $zones = Zone::where('is_active', true)->get(['id', 'name', 'color', 'geometry']);

        $rows = $catches->map(function ($catch) use ($zones) {
            $w = 1.0;
            if (! is_null($catch->quantity)) {
                $w = (float) $catch->quantity;
            } elseif (! is_null($catch->count)) {
                $w = (float) $catch->count;
            }

            // Determine which zones contain this catch point
            $containingZones = [];
            foreach ($zones as $zone) {
                if ($this->pointInZone($catch->latitude, $catch->longitude, $zone->geometry)) {
                    $containingZones[] = [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'color' => $zone->color,
                    ];
                }
            }

            return [
                'id' => $catch->id,
                'lat' => (float) $catch->latitude,
                'lng' => (float) $catch->longitude,
                'weight' => $w,
                'species' => $catch->species?->common_name ?? 'Unknown',
                'qty' => (float) ($catch->quantity ?? $catch->count ?? 0),
                'zones' => $containingZones,
            ];
        });

        return response()->json(['points' => $rows]);
    }

    /**
     * Check if a point is inside a GeoJSON polygon or multi-polygon.
     */
    private function pointInZone(float $lat, float $lng, mixed $geometry): bool
    {
        if (is_string($geometry)) {
            $geometry = json_decode($geometry, true);
        }

        if (! is_array($geometry)) {
            return false;
        }

        // Handle FeatureCollection
        if (isset($geometry['features']) && is_array($geometry['features'])) {
            foreach ($geometry['features'] as $feature) {
                if ($this->pointInFeature($lat, $lng, $feature)) {
                    return true;
                }
            }

            return false;
        }

        // Handle single Feature or Geometry
        return $this->pointInFeature($lat, $lng, $geometry);
    }

    /**
     * Check if a point is in a single GeoJSON feature.
     */
    private function pointInFeature(float $lat, float $lng, array $feature): bool
    {
        $geom = isset($feature['geometry']) ? $feature['geometry'] : $feature;

        if (! isset($geom['type']) || ! isset($geom['coordinates'])) {
            return false;
        }

        return match ($geom['type']) {
            'Polygon' => $this->pointInPolygon($lat, $lng, $geom['coordinates']),
            'MultiPolygon' => $this->pointInMultiPolygon($lat, $lng, $geom['coordinates']),
            default => false,
        };
    }

    /**
     * Ray casting algorithm to check if point is in polygon.
     * Polygon coordinates: [[[lng, lat], [lng, lat], ...]]
     */
    private function pointInPolygon(float $lat, float $lng, array $polygonCoords): bool
    {
        // GeoJSON uses [lng, lat] order
        $x = $lng;
        $y = $lat;

        $inside = false;
        $p1X = null;
        $p1Y = null;

        foreach ($polygonCoords as $ring) {
            foreach ($ring as $point) {
                $p2X = $point[0];
                $p2Y = $point[1];

                if ($p1X !== null) {
                    if (($p2Y > $y) !== ($p1Y > $y) && $x < ($p1X - $p2X) * ($y - $p2Y) / ($p1Y - $p2Y) + $p2X) {
                        $inside = ! $inside;
                    }
                }

                $p1X = $p2X;
                $p1Y = $p2Y;
            }
        }

        return $inside;
    }

    /**
     * Check if point is in any polygon of a MultiPolygon.
     */
    private function pointInMultiPolygon(float $lat, float $lng, array $multiPolygonCoords): bool
    {
        foreach ($multiPolygonCoords as $polygonCoords) {
            if ($this->pointInPolygon($lat, $lng, $polygonCoords)) {
                return true;
            }
        }

        return false;
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
        $lat = (float) $request->input('lat');
        $lon = (float) $request->input('lon');
        $zoom = (int) ($request->input('zoom', 8));

        // Derive radius km from zoom (approx) – tighter at higher zoom
        $radiusKm = match (true) {
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
        // Public endpoint: aggregate across all users. If needed, introduce privacy thresholds here.

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
            ->map(fn ($r) => [
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
