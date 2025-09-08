<?php

namespace Tests\Feature;

use App\Models\Species;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeciesPhilippinesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_expected_species(): void
    {
        $this->seed(\Database\Seeders\SpeciesPhilippinesSeeder::class);

        $map = [
            'Yellowfin Tuna' => 'Tambakol / Tulingan',
            'Skipjack Tuna' => 'Gulyasan / Tulingan',
            'Mahi-Mahi' => 'Dorado',
            'Round Scad (Galunggong)' => 'Galunggong',
            'Rabbitfish (Siganid)' => 'Danggit / Samaral',
        ];

        foreach ($map as $english => $filipino) {
            $this->assertDatabaseHas('species', [
                'common_name' => $english,
                'filipino_name' => $filipino,
            ]);
        }

        $this->assertGreaterThanOrEqual(count($map), Species::count());
    }
}
