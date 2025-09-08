<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeatherEndpointsTest extends TestCase
{
    use RefreshDatabase;
    public function test_current_weather_validation_errors(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $resp = $this->getJson('/weather/current');
        $resp->assertStatus(422);
    }

    public function test_current_weather_success(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        Config::set('services.openweather.key', 'test');
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'dt' => time(),
                'main' => ['temp'=>25,'humidity'=>70],
                'wind' => ['speed'=>5,'deg'=>180],
                'weather' => [['description'=>'clear sky','icon'=>'01d']],
            ], 200)
        ]);
        $resp = $this->getJson('/weather/current?lat=10&lon=120');
        $resp->assertOk()->assertJsonFragment([
            'temperature_c'=>25,
            'humidity_percent'=>70,
            'conditions'=>'clear sky',
            'source'=>'openweather'
        ]);
    }

    public function test_forecast_weather_success(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());
        Config::set('services.openweather.key', 'test');
        $list = [];
        $now = time();
        for ($i=0;$i<8;$i++) { // 24h 3h steps
            $ts = $now + ($i*10800);
            $list[] = [
                'dt' => $ts,
                'dt_txt' => date('Y-m-d H:00:00',$ts),
                'main' => [
                    'temp' => 24 + $i*0.2,
                    'feels_like' => 24 + $i*0.2,
                    'temp_min' => 23 + $i*0.2,
                    'temp_max' => 25 + $i*0.2,
                    'pressure' => 1000 + $i,
                    'humidity' => 60 + $i
                ],
                'wind' => ['speed'=>4 + $i*0.1, 'deg'=>90],
                'weather' => [['description'=>'clear','icon'=>'01d']],
                'rain' => ['3h'=>0],
            ];
        }
        Http::fake([
            'api.openweathermap.org/data/2.5/forecast*' => Http::response([
                'cod' => 200,
                'list' => $list,
            ], 200)
        ]);
        $resp = $this->getJson('/weather/forecast?lat=10&lon=120');
        $resp->assertOk()->assertJsonFragment(['source'=>'openweather']);
    }
}
