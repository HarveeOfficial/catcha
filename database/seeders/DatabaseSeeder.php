<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Species;
use App\Models\Location;
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
            'password' => bcrypt('password')
        ]);
        User::factory()->create([
            'name' => 'Expert One',
            'email' => 'expert@catcha.local',
            'role' => 'expert',
            'password' => bcrypt('password')
        ]);
        User::factory()->create([
            'name' => 'Fisher Joe',
            'email' => 'fisher@catcha.local',
            'role' => 'fisher',
            'password' => bcrypt('password')
        ]);

        // Reference species
        if (Species::count() === 0) {
            Species::insert([
                ['common_name' => 'Yellowfin Tuna','scientific_name' => 'Thunnus albacares','conservation_status' => 'LC','min_size_cm' => 40,'created_at'=>now(),'updated_at'=>now()],
                ['common_name' => 'Skipjack Tuna','scientific_name' => 'Katsuwonus pelamis','conservation_status' => 'LC','min_size_cm' => 35,'created_at'=>now(),'updated_at'=>now()],
                ['common_name' => 'Mahi-Mahi','scientific_name' => 'Coryphaena hippurus','conservation_status' => 'LC','min_size_cm' => 50,'created_at'=>now(),'updated_at'=>now()],
            ]);
        }

        // Reference locations
        if (Location::count() === 0) {
            Location::insert([
                ['name' => 'Zone A','latitude' => 14.5995,'longitude' => 120.9842,'created_at'=>now(),'updated_at'=>now()],
                ['name' => 'Zone B','latitude' => 14.7000,'longitude' => 121.0000,'created_at'=>now(),'updated_at'=>now()],
            ]);
        }
    }
}
