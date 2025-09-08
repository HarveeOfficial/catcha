<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Species;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Base users
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@catcha.local',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'Expert One',
            'email' => 'expert@catcha.local',
            'role' => 'expert',
            'password' => bcrypt('password'),
        ]);
        User::factory()->create([
            'name' => 'Fisher Joe',
            'email' => 'fisher@catcha.local',
            'role' => 'fisher',
            'password' => bcrypt('password'),
        ]);

        // Reference species (Philippines commercial / common catch list)
        if (Species::count() === 0) {
            $this->call(SpeciesPhilippinesSeeder::class);
        }

        // Reference locations
        if (Location::count() === 0) {
            Location::insert([
                ['name' => 'Zone A', 'latitude' => 14.5995, 'longitude' => 120.9842, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Zone B', 'latitude' => 14.7000, 'longitude' => 121.0000, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
