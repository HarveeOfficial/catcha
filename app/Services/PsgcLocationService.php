<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class PsgcLocationService
{
    private const CACHE_TTL = 86400; // 24 hours

    private ?array $psgcData = null;

    /**
     * Load the PSGC data from JSON file.
     */
    private function loadPsgcData(): array
    {
        if ($this->psgcData === null) {
            $path = database_path('data/psgc_data.json');
            $this->psgcData = json_decode(file_get_contents($path), true) ?? [];
        }

        return $this->psgcData;
    }

    /**
     * Get all regions.
     */
    public function getRegions(): array
    {
        return Cache::remember('psgc_regions', self::CACHE_TTL, function () {
            $data = $this->loadPsgcData();

            return collect(array_keys($data))
                ->map(fn ($regionName) => [
                    'id' => $this->normalizeRegionId($regionName),
                    'name' => $regionName,
                ])
                ->sortBy('name')
                ->values()
                ->all();
        });
    }

    /**
     * Get municipalities/cities by region name.
     */
    public function getMunicipalitiesByRegion(string $regionId): array
    {
        $cacheKey = 'psgc_municipalities_'.$regionId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($regionId) {
            $data = $this->loadPsgcData();

            // Find region by normalized ID
            $regionName = $this->findRegionNameById($regionId);

            if (! $regionName || ! isset($data[$regionName]['province_list'])) {
                return [];
            }

            $municipalities = [];

            // Loop through provinces to get their municipalities
            foreach ($data[$regionName]['province_list'] as $provinceName => $provinceData) {
                if (isset($provinceData['municipality_list'])) {
                    foreach (array_keys($provinceData['municipality_list']) as $municipalityName) {
                        $municipalities[] = [
                            'id' => $this->normalizeMunicipalityId($regionId, $provinceName, $municipalityName),
                            'name' => $municipalityName,
                            'province' => $provinceName,
                        ];
                    }
                }
            }

            return collect($municipalities)
                ->sortBy('name')
                ->values()
                ->all();
        });
    }

    /**
     * Get barangays by municipality name.
     */
    public function getBarangaysByMunicipality(string $municipalityId): array
    {
        $cacheKey = 'psgc_barangays_'.$municipalityId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($municipalityId) {
            $data = $this->loadPsgcData();

            // Parse the municipality ID to find the barangays
            $parts = explode('|', $municipalityId);
            if (count($parts) < 3) {
                return [];
            }

            $regionId = $parts[0];
            $provinceName = $parts[1];
            $municipalityName = $parts[2];

            $regionName = $this->findRegionNameById($regionId);

            if (! $regionName) {
                return [];
            }

            $barangays = $data[$regionName]['province_list'][$provinceName]['municipality_list'][$municipalityName]['barangay_list'] ?? [];

            return collect($barangays)
                ->map(fn ($barangayName, $index) => [
                    'id' => $municipalityId.'|'.$barangayName,
                    'name' => $barangayName,
                ])
                ->sortBy('name')
                ->values()
                ->all();
        });
    }

    /**
     * Normalize region name to a simple ID.
     */
    private function normalizeRegionId(string $regionName): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $regionName));
    }

    /**
     * Create a unique municipality ID.
     */
    private function normalizeMunicipalityId(string $regionId, string $provinceName, string $municipalityName): string
    {
        return $regionId.'|'.$provinceName.'|'.$municipalityName;
    }

    /**
     * Find region name by normalized ID.
     */
    private function findRegionNameById(string $regionId): ?string
    {
        $data = $this->loadPsgcData();

        foreach (array_keys($data) as $regionName) {
            if ($this->normalizeRegionId($regionName) === $regionId) {
                return $regionName;
            }
        }

        return null;
    }
}
