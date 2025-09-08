<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCatchGeoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_geolocation_and_generates_geohash(): void
    {
        $user = User::factory()->create();

        $payload = [
            'caught_at' => now()->format('Y-m-d\TH:i'),
            'latitude' => 14.599512,
            'longitude' => 120.984222,
            'geo_accuracy_m' => 15.25,
            'geo_source' => 'html5',
        ];

        $this->actingAs($user)
            ->post(route('catches.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('fish_catches', [
            'user_id' => $user->id,
            'latitude' => 14.599512,
            'longitude' => 120.984222,
            'geo_source' => 'html5',
        ]);

        $this->assertDatabaseMissing('fish_catches', [
            'geohash' => null,
        ]);
    }
}
