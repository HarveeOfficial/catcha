<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherForecastController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
        ]);

        $lat = round($data['lat'], 3);
        $lon = round($data['lon'], 3);
        $apiKey = config('services.openweather.key');
        if (! $apiKey) {
            return response()->json(['error' => 'API key missing'], 500);
        }

        $cacheKey = "ow_forecast_{$lat}_{$lon}";
        $forecast = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($lat, $lon, $apiKey) {
            $resp = Http::timeout(10)->withoutVerifying()->get('https://api.openweathermap.org/data/2.5/forecast', [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);
            if ($resp->failed()) {
                return ['error' => 'Forecast service unavailable'];
            }
            $json = $resp->json();
            if (isset($json['cod']) && (int) $json['cod'] !== 200) {
                return ['error' => $json['message'] ?? 'Forecast error'];
            }
            $list = $json['list'] ?? [];
            $hourly = [];
            $dailyBuckets = [];
            foreach ($list as $entry) {
                $dtTxt = $entry['dt_txt'] ?? null;
                $date = $dtTxt ? substr($dtTxt, 0, 10) : null; // YYYY-MM-DD
                $item = [
                    'time' => $dtTxt,
                    'timestamp' => $entry['dt'] ?? null,
                    'temperature_c' => $entry['main']['temp'] ?? null,
                    'feels_like_c' => $entry['main']['feels_like'] ?? null,
                    'temp_min_c' => $entry['main']['temp_min'] ?? null,
                    'temp_max_c' => $entry['main']['temp_max'] ?? null,
                    'pressure_hpa' => $entry['main']['pressure'] ?? null,
                    'humidity_percent' => $entry['main']['humidity'] ?? null,
                    'wind_speed_kmh' => isset($entry['wind']['speed']) ? round($entry['wind']['speed'] * 3.6, 1) : null,
                    'wind_dir_deg' => $entry['wind']['deg'] ?? null,
                    'precipitation_mm' => ($entry['rain']['3h'] ?? $entry['snow']['3h'] ?? 0),
                    'conditions' => $entry['weather'][0]['description'] ?? null,
                    'icon' => $entry['weather'][0]['icon'] ?? null,
                ];
                $hourly[] = $item;
                if ($date) {
                    if (! isset($dailyBuckets[$date])) {
                        $dailyBuckets[$date] = [
                            'date' => $date,
                            'min_temp' => $item['temperature_c'],
                            'max_temp' => $item['temperature_c'],
                            'sum_humidity' => $item['humidity_percent'],
                            'count' => 1,
                            'total_precip' => $item['precipitation_mm'],
                            'icons' => [$item['icon']],
                            'conditions_samples' => [$item['conditions']],
                            'representative' => $item,
                            'noon_diff' => $dtTxt ? abs(strtotime($dtTxt) - strtotime($date.' 12:00:00')) : 999999,
                        ];
                    } else {
                        $b = &$dailyBuckets[$date];
                        $b['min_temp'] = min($b['min_temp'], $item['temperature_c']);
                        $b['max_temp'] = max($b['max_temp'], $item['temperature_c']);
                        $b['sum_humidity'] += $item['humidity_percent'];
                        $b['count']++;
                        $b['total_precip'] += $item['precipitation_mm'];
                        $b['icons'][] = $item['icon'];
                        $b['conditions_samples'][] = $item['conditions'];
                        $diff = $dtTxt ? abs(strtotime($dtTxt) - strtotime($date.' 12:00:00')) : 999999;
                        if ($diff < $b['noon_diff']) {
                            $b['noon_diff'] = $diff;
                            $b['representative'] = $item;
                        }
                    }
                }
            }
            // Build daily array
            $daily = [];
            foreach ($dailyBuckets as $d) {
                $iconsCount = array_count_values(array_filter($d['icons']));
                arsort($iconsCount);
                $topIcon = array_key_first($iconsCount);
                $conditionsCount = array_count_values(array_filter($d['conditions_samples']));
                arsort($conditionsCount);
                $topCond = array_key_first($conditionsCount);
                $daily[] = [
                    'date' => $d['date'],
                    'min_temp_c' => round($d['min_temp'], 1),
                    'max_temp_c' => round($d['max_temp'], 1),
                    'avg_humidity_percent' => round($d['sum_humidity'] / max(1, $d['count'])),
                    'total_precip_mm' => round($d['total_precip'], 1),
                    'icon' => $topIcon ?: ($d['representative']['icon'] ?? null),
                    'conditions' => $topCond ?: ($d['representative']['conditions'] ?? null),
                ];
            }

            return [
                'latitude' => $lat,
                'longitude' => $lon,
                'forecast' => $hourly, // backward compat: now full list
                'daily' => $daily,
                'source' => 'openweather',
            ];
        });

        if (isset($forecast['error'])) {
            return response()->json($forecast, 500);
        }

        return response()->json($forecast);
    }
}
