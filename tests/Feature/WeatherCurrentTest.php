<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http; 
use Tests\TestCase;

class WeatherCurrentTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_current_weather_returns_icon_field(): void
    {
        $this->actingAs(\App\Models\User::factory()->create());

        Config::set('services.openweather.key', 'dummy');

        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'dt' => time(),
                'main' => ['temp' => 28.4, 'humidity' => 73],
                'wind' => ['speed' => 2.75, 'deg' => 120],
                'weather' => [ ['description' => 'overcast clouds', 'icon' => '04d'] ],
                'rain' => ['1h' => 0],
            ], 200),
        ]);

        $resp = $this->getJson(route('weather.current', ['lat' => 10.1234, 'lon' => 123.4567]));
        $resp->assertOk();
        $resp->assertJsonStructure(['icon']);
        $this->assertSame('04d', $resp->json('icon'));
    }
}
