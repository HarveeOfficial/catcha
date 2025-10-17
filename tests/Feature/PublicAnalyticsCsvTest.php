<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\FishCatch;

class PublicAnalyticsCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_csv_export_returns_csv_and_columns()
    {
        // Create a few catches across different dates
        FishCatch::factory()->create(['quantity' => 10.5, 'count' => 2, 'caught_at' => now()->subDays(1)]);
        FishCatch::factory()->create(['quantity' => 5.25, 'count' => 1, 'caught_at' => now()->subMonths(2)]);
        FishCatch::factory()->create(['quantity' => 3.0, 'count' => 3, 'caught_at' => now()->subYears(1)]);

        $resp = $this->get('/analytics?format=csv&series=annual');
        $resp->assertStatus(200);
        $resp->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $content = $resp->getContent();
        // first line should be header
        $firstLine = strtok($content, "\n");
        $this->assertStringContainsString('Period', $firstLine);
    $this->assertStringContainsString('Qty(Kg)', $firstLine);
        $this->assertStringContainsString('Count', $firstLine);
    }
}
