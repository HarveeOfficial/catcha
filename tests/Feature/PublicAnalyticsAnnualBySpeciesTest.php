<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Species;
use App\Models\FishCatch;
use Illuminate\Support\Carbon;

class PublicAnalyticsAnnualBySpeciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_analytics_annual_by_species_csv()
    {
        $s1 = Species::factory()->create(['common_name' => 'Alpha']);
        $s2 = Species::factory()->create(['common_name' => 'Beta']);

        // create catches in different years
        FishCatch::factory()->create(['species_id' => $s1->id, 'quantity' => 100, 'caught_at' => Carbon::create(2024,6,1)]);
        FishCatch::factory()->create(['species_id' => $s2->id, 'quantity' => 50, 'caught_at' => Carbon::create(2025,3,1)]);

        $resp = $this->get('/analytics?format=csv&series=annual&separated=species');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $content = $resp->getContent();
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $this->assertGreaterThanOrEqual(2, count($lines));

        $header = str_getcsv($lines[0]);
        $this->assertEquals('Period', $header[0]);
        $this->assertEquals('Species', $header[1]);

        // Ensure data rows include Alpha and Beta in species column
        $foundAlpha = false;
        $foundBeta = false;
        for ($i = 1; $i < count($lines); $i++) {
            $cols = str_getcsv($lines[$i]);
            if (isset($cols[1])) {
                if ($cols[1] === 'Alpha') $foundAlpha = true;
                if ($cols[1] === 'Beta') $foundBeta = true;
            }
        }
        $this->assertTrue($foundAlpha);
        $this->assertTrue($foundBeta);
    }
}
