<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherCityController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'q' => 'required|string|max:60'
        ]);
        $city = trim($data['q']);
        $apiKey = config('services.openweather.key');
        if (!$apiKey) {
            return response()->json(['error' => 'API key missing'], 500);
        }
        $cacheKey = 'ow_city_'.strtolower($city);
        $payload = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($city, $apiKey) {
            $resp = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);
            if ($resp->failed()) {
                return ['error' => 'Lookup failed'];
            }
            $p = $resp->json();
            if (isset($p['cod']) && (int)$p['cod'] !== 200) {
                return ['error' => $p['message'] ?? 'City not found'];
            }
            return [
                'city' => $p['name'] ?? $city,
                'country' => $p['sys']['country'] ?? null,
                'temperature_c' => $p['main']['temp'] ?? null,
                'humidity_percent' => $p['main']['humidity'] ?? null,
                'conditions' => $p['weather'][0]['description'] ?? null,
                'icon' => $p['weather'][0]['icon'] ?? null,
                'wind_speed_kmh' => isset($p['wind']['speed']) ? round($p['wind']['speed'] * 3.6,1) : null,
                'wind_dir_deg' => $p['wind']['deg'] ?? null,
                'time' => isset($p['dt']) ? date('c', $p['dt']) : null,
                'source' => 'openweather'
            ];
        });
        if (isset($payload['error'])) {
            return response()->json($payload, 404);
        }
        return response()->json($payload);
    }
}
