<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Species;
use App\Models\FishCatch;
use Illuminate\Support\Carbon;

class SeasonalTrendCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_seasonal_trend_csv_exports_monthly_by_species()
    {
        // Create two species
        $s1 = Species::factory()->create(['common_name' => 'Alpha']);
        $s2 = Species::factory()->create(['common_name' => 'Beta']);

        // Create catches across different months for both species
        FishCatch::factory()->create(['species_id' => $s1->id, 'quantity' => 10, 'caught_at' => Carbon::now()->subMonths(1)]);
        FishCatch::factory()->create(['species_id' => $s2->id, 'quantity' => 5, 'caught_at' => Carbon::now()->subMonths(1)]);
        FishCatch::factory()->create(['species_id' => $s1->id, 'quantity' => 2, 'caught_at' => Carbon::now()->subMonths(3)]);

        $resp = $this->actingAs($this->createAdmin())->get('/ai/seasonal-trends?format=csv');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $content = $resp->getContent();
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $this->assertGreaterThanOrEqual(2, count($lines));

        // Header includes Period and both species common names
        $header = str_getcsv($lines[0]);
        $this->assertEquals('Period', $header[0]);
        $this->assertContains('Alpha', $header);
        $this->assertContains('Beta', $header);
    }

    protected function createAdmin()
    {
        $user = \App\Models\User::factory()->create(['role' => 'admin']);
        return $user;
    }
}
