<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeatmapDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function fisher_sees_only_own_points(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        FishCatch::factory()->count(3)->create([ 'user_id' => $user->id, 'latitude' => 10.1, 'longitude' => 121.5, 'quantity' => 5 ]);
        FishCatch::factory()->count(2)->create([ 'user_id' => $other->id, 'latitude' => 11.2, 'longitude' => 121.6, 'quantity' => 8 ]);
        $resp = $this->actingAs($user)->getJson(route('catches.heatmap.data'));
        $resp->assertOk();
        $points = $resp->json('points');
        $this->assertCount(3, $points);
    }
}
