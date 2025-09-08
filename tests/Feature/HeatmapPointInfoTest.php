<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeatmapPointInfoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_species_summary_for_clicked_area(): void
    {
        $user = User::factory()->create();
        FishCatch::factory()->create(['user_id' => $user->id, 'latitude' => 10.0001, 'longitude' => 121.0001, 'quantity' => 4]);
        FishCatch::factory()->create(['user_id' => $user->id, 'latitude' => 10.0002, 'longitude' => 121.0002, 'quantity' => 6]);

        $resp = $this->actingAs($user)->getJson(route('catches.heatmap.point-info', ['lat'=>10.0001,'lon'=>121.0001,'zoom'=>14]));
        $resp->assertOk();
        $resp->assertJsonStructure(['radius_km','summary'=>['catches','total_qty','total_count'],'species']);
        $this->assertGreaterThan(0, $resp->json('summary.catches'));
    }
}
