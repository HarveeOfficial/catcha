<?php

namespace App\Services;

use App\Models\FishCatch;
use App\Models\Zone;

class ZoneService
{
    /**
     * Find and assign zone to a catch based on its coordinates.
     */
    public function assignZone(FishCatch $catch): void
    {
        if ($catch->latitude === null || $catch->longitude === null) {
            return;
        }

        $zone = $this->findZoneForCoordinates($catch->latitude, $catch->longitude);
        if ($zone) {
            $catch->update(['zone_id' => $zone->id]);
        }
    }

    /**
     * Find which zone coordinates belong to.
     */
    public function findZoneForCoordinates(float $lat, float $lng): ?Zone
    {
        $zones = Zone::where('is_active', true)->get();

        foreach ($zones as $zone) {
            if ($this->pointInZoneGeometry($lat, $lng, $zone->geometry)) {
                return $zone;
            }
        }

        return null;
    }

    /**
     * Check if point is in zone geometry (handles FeatureCollection wrapper).
     */
    private function pointInZoneGeometry(float $lat, float $lng, array $geometry): bool
    {
        // Handle FeatureCollection wrapper
        if ($geometry['type'] === 'FeatureCollection' && isset($geometry['features'])) {
            foreach ($geometry['features'] as $feature) {
                if ($this->pointInPolygon($lat, $lng, $feature['geometry'] ?? [])) {
                    return true;
                }
            }

            return false;
        }

        return $this->pointInPolygon($lat, $lng, $geometry);
    }

    /**
     * Ray casting algorithm to check if point is in polygon.
     * Polygon coordinates: [[[lng, lat], [lng, lat], ...]]
     */
    private function pointInPolygon(float $lat, float $lng, array $geometry): bool
    {
        if (! isset($geometry['type']) || ! isset($geometry['coordinates'])) {
            return false;
        }

        return match ($geometry['type']) {
            'Polygon' => $this->checkPolygon($lat, $lng, $geometry['coordinates']),
            'MultiPolygon' => $this->checkMultiPolygon($lat, $lng, $geometry['coordinates']),
            default => false,
        };
    }

    /**
     * Check if point is in a single polygon.
     */
    private function checkPolygon(float $lat, float $lng, array $polygonCoords): bool
    {
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
    private function checkMultiPolygon(float $lat, float $lng, array $multiPolygonCoords): bool
    {
        foreach ($multiPolygonCoords as $polygonCoords) {
            if ($this->checkPolygon($lat, $lng, $polygonCoords)) {
                return true;
            }
        }

        return false;
    }
}
