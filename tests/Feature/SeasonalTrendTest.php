<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SeasonalTrendTest extends TestCase
{
    use RefreshDatabase;

    public function test_seasonal_trend_endpoint_returns_expected_structure(): void
    {
        $user = User::factory()->create();
        // Species with open months only Jan, Feb
        $s1 = Species::factory()->create([
            'common_name' => 'TestFishA',
            'seasonal_restrictions' => ['open_months' => [1, 2]],
        ]);
        // Species with closed months Mar
        $s2 = Species::factory()->create([
            'common_name' => 'TestFishB',
            'seasonal_restrictions' => ['closed_months' => [3]],
        ]);
        // Species with window crossing year end
        $s3 = Species::factory()->create([
            'common_name' => 'TestFishC',
            'seasonal_restrictions' => [
                'windows' => [
                    ['start' => '11-01', 'end' => '02-15'],
                ],
            ],
        ]);

        // Create some catches in recent months
        $now = Carbon::create(null, 1, 15); // Force January for deterministic test
        Carbon::setTestNow($now);

        FishCatch::factory()->create(['species_id' => $s1->id, 'caught_at' => $now->copy()->subDays(5)]);
        FishCatch::factory()->create(['species_id' => $s2->id, 'caught_at' => $now->copy()->subMonths(2)]);
        FishCatch::factory()->create(['species_id' => $s3->id, 'caught_at' => $now->copy()->subMonths(1)]);

        $response = $this->actingAs($user)->getJson(route('ai.seasonal-trends'));
        $response->assertOk();
        $json = $response->json();
        $this->assertArrayHasKey('species', $json);
        $this->assertIsArray($json['species']);
        // Ensure each has needed keys
        foreach ($json['species'] as $sp) {
            $this->assertArrayHasKey('id', $sp);
            $this->assertArrayHasKey('status', $sp);
            $this->assertArrayHasKey('trend_12m', $sp);
        }
        // Check specific in-season statuses based on forced now (January)
        $fishA = collect($json['species'])->firstWhere('common_name', 'TestFishA');
        $fishB = collect($json['species'])->firstWhere('common_name', 'TestFishB');
        $fishC = collect($json['species'])->firstWhere('common_name', 'TestFishC');
        $this->assertTrue($fishA['status']['in_season']); // Jan in open months
        $this->assertTrue($fishB['status']['in_season']); // Jan not closed
        $this->assertTrue($fishC['status']['in_season']); // Window 11-01 -> 02-15 includes Jan
    }
}
