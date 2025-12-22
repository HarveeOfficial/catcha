<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\GearType;
use App\Models\Species;
use App\Services\PsgcLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatchController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isExpert() || $user->isAdmin() || $user->isMao()) {
            $query = FishCatch::with(['species', 'user'])->withCount('feedbacks')->latest('caught_at');
            if ($request->filled('species_id')) {
                $query->where('species_id', $request->input('species_id'));
            }
            // Add date range filter
            if ($request->filled('date_from')) {
                $query->whereDate('caught_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('caught_at', '<=', $request->input('date_to'));
            }
            $catches = $query->paginate(20)->appends($request->only('species_id', 'date_from', 'date_to'));
        } else {
            $query = FishCatch::with(['species'])
                ->where('user_id', $user->id)
                ->latest('caught_at');
            // Add date range filter for non-admin users too
            if ($request->filled('date_from')) {
                $query->whereDate('caught_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('caught_at', '<=', $request->input('date_to'));
            }
            $catches = $query->paginate(15);
        }

        $species = Species::orderBy('common_name')->get();

        return view('catches.index', compact('catches', 'species'));
    }

    public function create(PsgcLocationService $locationService)
    {
        $user = Auth::user();
        if ($user->isMao() || $user->isAdmin()) {
            abort(403, 'Admin and Mao users cannot create catches.');
        }

        $species = Species::orderBy('common_name')->get();
        $categories = Species::distinct()->orderBy('category')->pluck('category');
        $gearTypes = GearType::orderBy('name')->get();
        $regions = $locationService->getRegions();

        return view('catches.create', compact('species', 'categories', 'gearTypes', 'regions'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isMao() || $user->isAdmin()) {
            abort(403, 'Admin and Mao users cannot create catches.');
        }

        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'gear_type_id' => 'nullable|exists:gear_types,id',
            'location' => 'nullable|string|max:150',
            'psgc_region' => 'nullable|string|max:100',
            'psgc_municipality' => 'nullable|string|max:100',
            'psgc_barangay' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geo_accuracy_m' => 'nullable|numeric|min:0|max:50000',
            'geo_source' => 'nullable|string|max:30',
            'geohash' => 'nullable|string|max:16',
            'caught_at' => 'required|date',
            'quantity' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'avg_size_cm' => 'nullable|numeric|min:0',
            'bycatch_quantity' => 'nullable|numeric|min:0',
            'bycatch_species_ids' => 'nullable|array',
            'bycatch_species_ids.*' => 'exists:species,id',
            'discard_quantity' => 'nullable|numeric|min:0',
            'discard_species_ids' => 'nullable|array',
            'discard_species_ids.*' => 'exists:species,id',
            'discard_reason' => 'nullable|string|in:too_small,damaged,dead,species_not_allowed,over_quota,other',
            'discard_reason_other' => 'nullable|string|max:255',
            'weather' => 'nullable|string|in:sunny,rainy,cloudy,windy,stormy,foggy',
            'vessel_name' => 'nullable|string|max:150',
            'environmental_data' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);
        $data['user_id'] = Auth::id();

        // Store weather in environmental_data
        if ($request->filled('weather')) {
            $data['environmental_data'] = array_merge($data['environmental_data'] ?? [], ['weather' => $request->input('weather')]);
        }

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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! ($user->isExpert() || $user->isAdmin() || $user->isMao()) && $fishCatch->user_id !== $user->id) {
            abort(403);
        }
        $fishCatch->loadMissing(['species', 'user']);

        return view('catches.show', ['catch' => $fishCatch]);
    }

    public function edit(FishCatch $fishCatch)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Everyone can only edit their own catches
        if ($fishCatch->user_id !== $user->id) {
            abort(403);
        }
        // Experts cannot edit if feedback exists
        if ($user->isExpert() && $fishCatch->feedbacks()->exists()) {
            abort(403);
        }
        // Regular fishers cannot edit if feedback exists
        if (! ($user->isAdmin() || $user->isMao() || $user->isExpert()) && $fishCatch->feedbacks()->exists()) {
            abort(403);
        }
        $species = Species::orderBy('common_name')->get();
        $gearTypes = GearType::orderBy('name')->get();

        return view('catches.edit', [
            'catch' => $fishCatch,
            'species' => $species,
            'gearTypes' => $gearTypes,
        ]);
    }

    public function update(Request $request, FishCatch $fishCatch)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Everyone can only edit their own catches
        if ($fishCatch->user_id !== $user->id) {
            abort(403);
        }
        // Experts cannot edit if feedback exists
        if ($user->isExpert() && $fishCatch->feedbacks()->exists()) {
            abort(403);
        }
        // Regular fishers cannot edit if feedback exists
        if (! ($user->isAdmin() || $user->isMao() || $user->isExpert()) && $fishCatch->feedbacks()->exists()) {
            abort(403);
        }
        $data = $request->validate([
            'species_id' => 'nullable|exists:species,id',
            'location' => 'nullable|string|max:150',
            'psgc_region' => 'nullable|string|max:100',
            'psgc_municipality' => 'nullable|string|max:100',
            'psgc_barangay' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'geo_accuracy_m' => 'nullable|numeric|min:0|max:50000',
            'geo_source' => 'nullable|string|max:30',
            'geohash' => 'nullable|string|max:16',
            'caught_at' => 'required|date',
            'quantity' => 'nullable|numeric|min:0',
            'count' => 'nullable|integer|min:0',
            'avg_size_cm' => 'nullable|numeric|min:0',
            'bycatch_quantity' => 'nullable|numeric|min:0',
            'bycatch_species_ids' => 'nullable|array',
            'bycatch_species_ids.*' => 'exists:species,id',
            'discard_quantity' => 'nullable|numeric|min:0',
            'discard_species_ids' => 'nullable|array',
            'discard_species_ids.*' => 'exists:species,id',
            'discard_reason' => 'nullable|string|in:too_small,damaged,dead,species_not_allowed,over_quota,other',
            'discard_reason_other' => 'nullable|string|max:255',
            'weather' => 'nullable|string|in:sunny,rainy,cloudy,windy,stormy,foggy',
            'gear_type' => 'nullable|string|max:100',
            'vessel_name' => 'nullable|string|max:150',
            'environmental_data' => 'nullable|array',
            'notes' => 'nullable|array',
        ]);

        // Store weather in environmental_data
        if ($request->filled('weather')) {
            $currentEnvData = $fishCatch->environmental_data ?? [];
            $data['environmental_data'] = array_merge($currentEnvData, ['weather' => $request->input('weather')]);
        }

        // If lat/lon provided and geohash missing, recompute
        if ((isset($data['latitude']) && isset($data['longitude'])) && empty($data['geohash'])) {
            $data['geohash'] = $this->encodeGeohash((float) $data['latitude'], (float) $data['longitude']);
        }

        $fishCatch->update($data);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('catches.show', $fishCatch)->with('status', 'Catch updated');
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
