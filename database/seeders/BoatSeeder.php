<?php

namespace Database\Seeders;

use App\Models\Boat;
use App\Models\User;
use Illuminate\Database\Seeder;

class BoatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a MAO user to own the boats
        $maoUser = User::where('role', 'mao')->first();

        if (! $maoUser) {
            $maoUser = User::factory()->create([
                'name' => 'MAO Officer',
                'email' => 'mao@catcha.com',
                'role' => 'mao',
            ]);
        }

        // Philippine fishing boat names
        $boatNames = [
            'F/B Maria Clara',
            'F/B Dalisay',
            'F/B Sampaguita',
            'M/B Bayanihan',
            'F/B Maganda',
            'F/B Perlas ng Silangan',
            'B/C San Pedro',
            'F/B Bituin',
            'M/B Lakambini',
            'F/B Maligaya',
            'F/B Bagong Pag-asa',
            'M/B Malakas',
            'F/B Marikit',
            'F/B Paraluman',
            'B/C Santo Niño',
            'F/B Masaya',
            'M/B Matapang',
            'F/B Malaya',
            'F/B Bukang Liwayway',
            'M/B Bantay Dagat',
        ];

        $homePorts = [
            'Navotas Fish Port',
            'General Santos Fish Port',
            'Zamboanga City Port',
            'Lucena City Port',
            'Iloilo Fish Port',
            'Davao Fish Port',
            'Batangas Port',
            'Cebu Fish Port',
            'Puerto Princesa Port',
            'Aparri Port',
        ];

        $regions = [
            'Region I (Ilocos Region)',
            'Region II (Cagayan Valley)',
            'Region III (Central Luzon)',
            'Region IV-A (CALABARZON)',
            'Region V (Bicol Region)',
            'Region VI (Western Visayas)',
            'Region VII (Central Visayas)',
            'Region VIII (Eastern Visayas)',
            'Region IX (Zamboanga Peninsula)',
            'Region X (Northern Mindanao)',
            'Region XI (Davao Region)',
            'Region XII (SOCCSKSARGEN)',
            'NCR (National Capital Region)',
            'CAR (Cordillera Administrative Region)',
            'BARMM (Bangsamoro)',
            'Region XIII (Caraga)',
        ];

        // Create motorized boats
        foreach (array_slice($boatNames, 0, 15) as $index => $name) {
            Boat::factory()->motorized()->create([
                'user_id' => $maoUser->id,
                'name' => $name,
                'registration_number' => sprintf('PH-%04d-%s', $index + 1001, fake()->randomLetter().fake()->randomLetter()),
                'home_port' => fake()->randomElement($homePorts),
                'psgc_region' => fake()->randomElement($regions),
                'status' => fake()->randomElement(['active', 'active', 'active', 'expired']),
            ]);
        }

        // Create non-motorized boats
        foreach (array_slice($boatNames, 15) as $index => $name) {
            Boat::factory()->nonMotorized()->create([
                'user_id' => $maoUser->id,
                'name' => $name,
                'registration_number' => sprintf('PH-%04d-%s', $index + 2001, fake()->randomLetter().fake()->randomLetter()),
                'home_port' => fake()->randomElement($homePorts),
                'psgc_region' => fake()->randomElement($regions),
            ]);
        }

        // Create some additional random boats
        Boat::factory(10)->create(['user_id' => $maoUser->id]);

        $this->command->info('Created '.Boat::count().' boats.');
    }
}
