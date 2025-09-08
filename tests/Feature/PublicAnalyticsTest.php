<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_analytics_page_loads(): void
    {
        FishCatch::factory()->count(3)->create();

        $resp = $this->get(route('analytics.public'));

        $resp->assertOk();
        $resp->assertSee('Public Catch Analytics');
    }

    public function test_public_analytics_aggregates_totals(): void
    {
        FishCatch::factory()->count(2)->create(['quantity' => 10, 'count' => 5]);
        FishCatch::factory()->create(['quantity' => 5, 'count' => 2]);

        $resp = $this->get(route('analytics.public'));

        $resp->assertOk();
        $resp->assertSee('Total Catches');
        $resp->assertSee('3'); // catches count
    }
}
