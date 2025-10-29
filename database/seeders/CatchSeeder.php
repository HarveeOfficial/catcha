<?php

namespace Database\Seeders;

use App\Models\FishCatch;
use App\Models\GearType;
use App\Models\Species;
use App\Models\User;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all available references
        $users = User::where('role', 'fisher')->get();
        $species = Species::all();
        $gearTypes = GearType::all();
        $zones = Zone::all();

        if ($users->isEmpty()) {
            $this->command->info('No fisher users found. Create users first.');

            return;
        }

        if ($species->isEmpty()) {
            $this->command->info('No species found. Run SpeciesPhilippinesSeeder first.');

            return;
        }

        if ($gearTypes->isEmpty()) {
            $this->command->info('No gear types found. Run GearTypeSeeder first.');

            return;
        }

        // Define realistic fishing locations around Aparri, Cagayan waters
        $locations = [
            ['name' => 'Aparri Main Port', 'lat' => 18.3589, 'lng' => 121.8336],
            ['name' => 'Punta Engaño, Aparri', 'lat' => 18.3650, 'lng' => 121.8400],
            ['name' => 'Camalaniugan Waters', 'lat' => 18.3500, 'lng' => 121.8200],
            ['name' => 'Gonzaga Shoals', 'lat' => 18.3200, 'lng' => 121.8500],
            ['name' => 'Cagayan Bay - North', 'lat' => 18.3750, 'lng' => 121.8250],
            ['name' => 'Cagayan Bay - South', 'lat' => 18.3400, 'lng' => 121.8450],
            ['name' => 'Tuao Channel', 'lat' => 18.3300, 'lng' => 121.8100],
            ['name' => 'Ballesteros Reef', 'lat' => 18.3600, 'lng' => 121.8550],
        ];

        // Generate catches for the last 6 months
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        $catches = [];
        $totalCatches = 150;

        for ($i = 0; $i < $totalCatches; $i++) {
            $randomDate = $startDate->copy()->addDays(rand(0, $endDate->diffInDays($startDate)));
            $randomDate = $randomDate->addHours(rand(0, 23))->addMinutes(rand(0, 59));

            $location = $locations[array_rand($locations)];
            $randomSpecies = $species->random();
            $randomGear = $gearTypes->random();
            $randomUser = $users->random();

            // Calculate more realistic quantities based on species
            $baseQty = match ($randomSpecies->common_name) {
                'Tilapia' => rand(5, 50),
                'Milkfish' => rand(3, 30),
                'Catfish' => rand(2, 25),
                'Shrimp' => rand(1, 15),
                'Crab' => rand(2, 20),
                'Grouper' => rand(1, 10),
                'Snapper' => rand(1, 15),
                'Tuna' => rand(5, 100),
                'Mackerel' => rand(10, 80),
                'Sardine' => rand(20, 150),
                default => rand(3, 40),
            };

            $quantity = round($baseQty * (0.5 + (rand(0, 100) / 100)), 2);
            $count = ceil($quantity / (rand(2, 5)));
            $avgSize = round(rand(15, 120) + (rand(0, 100) / 100), 2);

            $catches[] = [
                'user_id' => $randomUser->id,
                'species_id' => $randomSpecies->id,
                'zone_id' => $zones->isNotEmpty() ? $zones->random()->id : null,
                'gear_type_id' => $randomGear->id,
                'location' => $location['name'],
                'latitude' => round($location['lat'] + (rand(-100, 100) / 10000), 6),
                'longitude' => round($location['lng'] + (rand(-100, 100) / 10000), 6),
                'geo_accuracy_m' => rand(5, 50),
                'caught_at' => $randomDate,
                'quantity' => $quantity,
                'count' => $count,
                'avg_size_cm' => $avgSize,
                'vessel_name' => $this->getVesselName(),
                'environmental_data' => json_encode($this->getEnvironmentalData()),
                'created_at' => $randomDate,
                'updated_at' => $randomDate,
            ];
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($catches, 25) as $chunk) {
            FishCatch::insert($chunk);
        }

        $this->command->info("Created {$totalCatches} catch records successfully!");
    }

    protected function getVesselName(): ?string
    {
        $vessels = [
            'Makabayan I',
            'Tipas III',
            'Iba Nang Buhay',
            'Tao Ngiti',
            'Madali',
            'Karanasan',
            'Umaasenso',
            'Pag-asa',
            'Marinduque',
            'Sarao',
            null,
            null,
        ];

        return $vessels[array_rand($vessels)];
    }

    protected function getEnvironmentalData(): ?array
    {
        $weathers = ['sunny', 'cloudy', 'rainy', 'windy', 'stormy'];
        $windSpeeds = ['calm', 'light', 'moderate', 'strong'];

        if (rand(0, 1) === 0) {
            return null;
        }

        return [
            'weather' => $weathers[array_rand($weathers)],
            'water_temperature_c' => rand(25, 32),
            'tide' => rand(0, 1) === 0 ? 'high' : 'low',
            'wind_speed' => $windSpeeds[array_rand($windSpeeds)],
            'notes' => rand(0, 1) === 0 ? 'Good catch today' : null,
        ];
    }
}
