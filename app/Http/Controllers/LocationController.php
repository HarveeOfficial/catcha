<?php

namespace App\Http\Controllers;

use App\Services\PsgcLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(private PsgcLocationService $locationService) {}

    public function getRegions(): JsonResponse
    {
        return response()->json([
            'regions' => $this->locationService->getRegions(),
        ]);
    }

    public function getMunicipalitiesByRegion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'region_id' => 'required|string',
        ]);

        return response()->json([
            'places' => $this->locationService->getMunicipalitiesByRegion($validated['region_id']),
        ]);
    }

    public function getBarangaysByMunicipality(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'municipality_id' => 'required|string',
        ]);

        return response()->json([
            'barangays' => $this->locationService->getBarangaysByMunicipality($validated['municipality_id']),
        ]);
    }
}
