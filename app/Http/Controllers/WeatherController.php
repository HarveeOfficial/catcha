<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        $lat = round($data['lat'], 3);
        $lon = round($data['lon'], 3);
        $cacheKey = "ow_current_{$lat}_{$lon}";

        $normalized = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($lat,$lon) {
            $apiKey = config('services.openweather.key');
            if (!$apiKey) {
                return ['error' => 'API key missing'];
            }
            $resp = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);
            if ($resp->failed()) {
                return ['error' => 'Weather service unavailable'];
            }
            $p = $resp->json();
            return [
                'latitude' => $lat,
                'longitude' => $lon,
                'time' => isset($p['dt']) ? date('c', $p['dt']) : null,
                'temperature_c' => $p['main']['temp'] ?? null,
                'humidity_percent' => $p['main']['humidity'] ?? null,
                // OpenWeather gives m/s -> convert to km/h
                'wind_speed_kmh' => isset($p['wind']['speed']) ? round($p['wind']['speed'] * 3.6, 1) : null,
                'wind_dir_deg' => $p['wind']['deg'] ?? null,
                'precipitation_mm' => $p['rain']['1h'] ?? $p['snow']['1h'] ?? 0,
                'conditions' => $p['weather'][0]['description'] ?? null,
                'source' => 'openweather'
            ];
        });

        if (isset($normalized['error'])) {
            return response()->json($normalized, 500);
        }
        return response()->json($normalized);
    }
}
