<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowCatchMapTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_own_catch_with_coordinates(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $catch = FishCatch::factory()->create([
            'user_id' => $user->id,
            'latitude' => 14.500100,
            'longitude' => 120.900200,
        ]);
        $resp = $this->get(route('catches.show', $catch));
        $resp->assertOk();
        $resp->assertSee((string) number_format($catch->latitude, 6));
    }

    /** @test */
    public function user_cannot_view_another_users_catch(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $this->actingAs($u1);
        $catch = FishCatch::factory()->create([
            'user_id' => $u2->id,
            'latitude' => 10.1,
            'longitude' => 120.1,
        ]);
        $this->get(route('catches.show', $catch))->assertStatus(403);
    }
}
