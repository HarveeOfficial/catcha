<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHeatmapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // minimal seed of catch data
        FishCatch::factory()->count(3)->create([
            'latitude' => 14.6,
            'longitude' => 121.0,
            'quantity' => 2.5,
        ]);
    }

    public function test_guest_can_load_heatmap_view(): void
    {
        $this->get(route('catches.heatmap'))
            ->assertOk()
            ->assertSee('Fishing Grounds Heatmap');
    }

    public function test_guest_can_fetch_heatmap_data(): void
    {
        $this->getJson(route('catches.heatmap.data'))
            ->assertOk()
            ->assertJsonStructure(['points']);
    }

    public function test_guest_can_fetch_point_info(): void
    {
        $this->getJson(route('catches.heatmap.point-info', ['lat' => 14.6, 'lon' => 121.0, 'zoom' => 8]))
            ->assertOk()
            ->assertJsonStructure([
                'radius_km',
                'summary' => ['catches', 'total_qty', 'total_count'],
                'species',
            ]);
    }
}
