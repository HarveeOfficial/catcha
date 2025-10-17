<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Species;
use App\Models\FishCatch;
use Illuminate\Support\Carbon;

class PublicAnalyticsCsvBySpeciesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_analytics_monthly_by_species_csv()
    {
        // Create two species
        $s1 = Species::factory()->create(['common_name' => 'Alpha']);
        $s2 = Species::factory()->create(['common_name' => 'Beta']);

        // Create catches across different months for both species
        FishCatch::factory()->create(['species_id' => $s1->id, 'quantity' => 10, 'caught_at' => Carbon::now()->subMonths(1)]);
        FishCatch::factory()->create(['species_id' => $s2->id, 'quantity' => 5, 'caught_at' => Carbon::now()->subMonths(1)]);
        FishCatch::factory()->create(['species_id' => $s1->id, 'quantity' => 2, 'caught_at' => Carbon::now()->subMonths(3)]);

        $resp = $this->get('/analytics?format=csv&series=monthly&separated=species');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');

        $content = $resp->getContent();
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        $this->assertGreaterThanOrEqual(2, count($lines));

        // Header includes Period and Species
        $header = str_getcsv($lines[0]);
        $this->assertEquals('Period', $header[0]);
        $this->assertEquals('Species', $header[1]);

        // Ensure at least one data row contains a species in the Species column
        $dataRowFound = false;
        for ($i = 1; $i < count($lines); $i++) {
            $cols = str_getcsv($lines[$i]);
            if (isset($cols[1]) && in_array($cols[1], ['Alpha','Beta'])) {
                $dataRowFound = true;
                break;
            }
        }
        $this->assertTrue($dataRowFound, 'Expected at least one data row with a species name in the Species column');
    }
}
