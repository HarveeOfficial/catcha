<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatchController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isExpert() || $user->isAdmin()) {
            $query = FishCatch::with(['species', 'user'])->withCount('feedbacks')->latest('caught_at');
            if ($request->filled('fisher')) {
                $term = $request->input('fisher');
                $query->whereHas('user', function ($q) use ($term) {
                    $q->where('name', 'like', '%'.$term.'%');
                });
            }
            $catches = $query->paginate(20)->appends($request->only('fisher'));
        } else {
            $catches = FishCatch::with(['species'])
                ->where('user_id', $user->id)
                ->latest('caught_at')
                ->paginate(15);
        }

        return view('catches.index', compact('catches'));
    }

    public function create()
    {
        $species = Species::orderBy('common_name')->get();

        return view('catches.create', compact('species'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'location' => 'nullable|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geo_accuracy_m' => 'nullable|numeric|min:0|max:50000',
            'geo_source' => 'nullable|string|max:30',
            'geohash' => 'nullable|string|max:16',
            'caught_at' => 'required|date',
            'quantity' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'avg_size_cm' => 'nullable|numeric|min:0',
            'gear_type' => 'nullable|string|max:100',
            'vessel_name' => 'nullable|string|max:150',
            'environmental_data' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);
        $data['user_id'] = Auth::id();

        if (empty($data['geohash']) && isset($data['latitude'], $data['longitude'])) {
            $data['geohash'] = $this->encodeGeohash((float) $data['latitude'], (float) $data['longitude']);
        }
        $catch = FishCatch::create($data);
        if ($request->expectsJson() || $request->header('X-Offline-Sync')) {
            return response()->json([
                'status' => 'ok',
                'id' => $catch->id,
                'created_at' => $catch->created_at,
            ], 201);
        }

        return redirect()->route('catches.index')->with('status', 'Catch recorded');
    }

    public function show(FishCatch $fishCatch)
    {
        $user = Auth::user();
        if (! ($user->isExpert() || $user->isAdmin()) && $fishCatch->user_id !== $user->id) {
            abort(403);
        }
        $fishCatch->loadMissing(['species', 'user', 'feedbacks.expert', 'feedbacks.likes']);
        $feedbacks = $fishCatch->feedbacks()->with('likes')->withCount('likes')->latest()->get();

        return view('catches.show', ['catch' => $fishCatch, 'feedbacks' => $feedbacks]);
    }

    private function encodeGeohash(float $lat, float $lon, int $precision = 10): string
    {
        $base32 = '0123456789bcdefghjkmnpqrstuvwxyz';
        $latInterval = [-90.0, 90.0];
        $lonInterval = [-180.0, 180.0];
        $hash = '';
        $isEven = true;
        $bit = 0;
        $ch = 0;
        $bits = [16, 8, 4, 2, 1];
        while (strlen($hash) < $precision) {
            if ($isEven) {
                $mid = ($lonInterval[0] + $lonInterval[1]) / 2;
                if ($lon > $mid) {
                    $ch |= $bits[$bit];
                    $lonInterval[0] = $mid;
                } else {
                    $lonInterval[1] = $mid;
                }
            } else {
                $mid = ($latInterval[0] + $latInterval[1]) / 2;
                if ($lat > $mid) {
                    $ch |= $bits[$bit];
                    $latInterval[0] = $mid;
                } else {
                    $latInterval[1] = $mid;
                }
            }
            $isEven = ! $isEven;
            if ($bit < 4) {
                $bit++;
            } else {
                $hash .= $base32[$ch];
                $bit = 0;
                $ch = 0;
            }
        }

        return $hash;
    }
}
