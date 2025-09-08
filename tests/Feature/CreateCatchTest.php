<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_create_catch_with_lat_lon(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'caught_at' => now()->subHour()->format('Y-m-d H:i:s'),
            'latitude' => 14.500123,
            'longitude' => 120.900456,
            'quantity' => 5,
        ];

        $resp = $this->post(route('catches.store'), $payload);
        $resp->assertRedirect(route('catches.index'));

        $this->assertDatabaseHas('fish_catches', [
            'user_id' => $user->id,
            'latitude' => 14.500123,
            'longitude' => 120.900456,
        ]);
    }
}
