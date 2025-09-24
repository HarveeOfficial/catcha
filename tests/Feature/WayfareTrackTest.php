<?php

namespace Tests\Feature;

use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WayfareTrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_catch_with_wayfare_track(): void
    {
        $user = User::factory()->create();
        $species = Species::factory()->create();

        $payload = [
            'species_id' => $species->id,
            'caught_at' => now()->toISOString(),
            'latitude' => 18.33,
            'longitude' => 121.61,
            'geo_source' => 'wayfare',
            'environmental_data' => [
                'wayfare_track_json' => json_encode([
                    'meta' => ['startedAt' => now()->subHour()->toISOString(), 'stoppedAt' => now()->toISOString(), 'total' => 2],
                    'points' => [
                        ['lat' => 18.33, 'lon' => 121.61, 'acc' => 10, 'ts' => now()->subMinutes(30)->getTimestampMs()],
                        ['lat' => 18.331, 'lon' => 121.611, 'acc' => 8, 'ts' => now()->subMinutes(10)->getTimestampMs()],
                    ],
                ]),
                'wayfare_summary' => '0.15 km (2 pts)'
            ],
        ];

        $res = $this->actingAs($user)->post(route('catches.store'), $payload);

        $res->assertRedirect(route('catches.index'));
        $this->assertDatabaseHas('fish_catches', [
            'user_id' => $user->id,
            'species_id' => $species->id,
            'geo_source' => 'wayfare',
        ]);
    }
}
