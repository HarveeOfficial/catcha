<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FishCatch;
use App\Models\Species;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ZoneController extends Controller
{
    public function index(): View
    {
        $zones = Zone::with('species')->get();

        return view('admin.zones.index', compact('zones'));
    }

    public function create(): View
    {
        $species = Species::all();

        return view('admin.zones.create', compact('species'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'geometry' => 'required|json',
            'species_ids' => 'nullable|array',
            'species_ids.*' => 'integer|exists:species,id',
        ]);

        $zone = Zone::create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'geometry' => json_decode($validated['geometry'], true),
            'is_active' => true,
        ]);

        if (! empty($validated['species_ids'])) {
            $zone->species()->sync($validated['species_ids']);
        }

        return redirect()->route('admin.zones.show', $zone)
            ->with('success', 'Zone created successfully.');
    }

    public function show(Zone $zone): View
    {
        $zone->load('species');

        return view('admin.zones.show', compact('zone'));
    }

    public function edit(Zone $zone): View
    {
        $zone->load('species');
        $species = Species::all();
        $selectedSpeciesIds = $zone->species->pluck('id')->toArray();

        return view('admin.zones.edit', compact('zone', 'species', 'selectedSpeciesIds'));
    }

    public function update(Request $request, Zone $zone): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string',
            'geometry' => 'required|json',
            'species_ids' => 'nullable|array',
            'species_ids.*' => 'integer|exists:species,id',
            'is_active' => 'boolean',
        ]);

        $zone->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
            'geometry' => json_decode($validated['geometry'], true),
            'is_active' => $validated['is_active'] ?? $zone->is_active,
        ]);

        if (! empty($validated['species_ids'])) {
            $zone->species()->sync($validated['species_ids']);
        } else {
            $zone->species()->detach();
        }

        return redirect()->route('admin.zones.show', $zone)
            ->with('success', 'Zone updated successfully.');
    }

    public function destroy(Zone $zone): \Illuminate\Http\RedirectResponse
    {
        $zone->delete();

        return redirect()->route('admin.zones.index')
            ->with('success', 'Zone deleted successfully.');
    }

    public function data(): JsonResponse
    {
        $zones = Zone::where('is_active', true)->get(['id', 'name', 'color', 'geometry']);

        return response()->json([
            'zones' => $zones->map(function ($zone) {
                // Get species from catches
                $catchSpecies = FishCatch::where('zone_id', $zone->id)
                    ->selectRaw('species_id, COUNT(*) as catches, COALESCE(SUM(quantity),0) as total_qty')
                    ->groupBy('species_id')
                    ->orderByDesc('total_qty')
                    ->limit(10)
                    ->with('species:id,common_name')
                    ->get()
                    ->map(fn ($catch) => [
                        'name' => $catch->species?->common_name ?? 'Unknown',
                        'catches' => (int) $catch->catches,
                        'qty' => (float) $catch->total_qty,
                    ]);

                // Also get species assigned to this zone (zone_species relationship)
                $assignedSpecies = $zone->species()
                    ->get(['species.id', 'species.common_name'])
                    ->map(fn ($sp) => [
                        'name' => $sp->common_name,
                        'catches' => 0,
                        'qty' => 0.0,
                    ]);

                // Merge them (catches take priority if species appears in both)
                $allSpecies = collect();
                $speciesIds = [];

                // Add catch species first
                foreach ($catchSpecies as $sp) {
                    $allSpecies->push($sp);
                    $speciesIds[] = $sp['name'];
                }

                // Add assigned species that weren't in catches
                foreach ($assignedSpecies as $sp) {
                    if (! in_array($sp['name'], $speciesIds)) {
                        $allSpecies->push($sp);
                    }
                }

                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'color' => $zone->color,
                    'geometry' => $zone->geometry,
                    'species' => $allSpecies,
                ];
            }),
        ]);
    }
}
