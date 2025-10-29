<?php

namespace Database\Seeders;

use App\Models\GearType;
use Illuminate\Database\Seeder;

class GearTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gearTypes = [
            // Cast/Hand nets
            ['Cast Net', 'Pukot / Hilaka', 'Circular net thrown by hand to catch fish in shallow water'],
            ['Dip Net', 'Sibat / Salambao', 'Hand-held net on a pole for catching fish'],
            ['Push Net', 'Empuyuhan', 'Net pushed through water to trap fish'],

            // Hooks and lines
            ['Hand Line', 'Pulutan / Linya', 'Single line with hooks for manual fishing'],
            ['Rod and Reel', 'Bawitan', 'Fishing rod with reel for sport or commercial fishing'],
            ['Longline', 'Pilapil / Layag-layag', 'Long line with many baited hooks'],
            ['Troll Line', 'Trawl', 'Line towed behind a boat with baited hooks or lures'],

            // Traps and cages
            ['Fish Trap', 'Bubo / Pukot-bubo', 'Cage trap with funnel entrance for passive fishing'],
            ['Crab Trap', 'Bubo alimango', 'Trap specifically designed for crabs'],
            ['Fish Corral', 'Baklad / Talangka', 'Enclosure built in water to trap migrating fish'],

            // Nets
            ['Seine Net', 'Sipa / Lambat', 'Large net dragged along the bottom of water'],
            ['Gill Net', 'Lambat / Ayungin net', 'Net with mesh that entangles fish by gills'],
            ['Trawl Net', 'Trawl', 'Large net towed behind a boat for commercial fishing'],
            ['Scoop Net', 'Opo / Salading', 'Small basket-like net for scooping fish'],
            ['Pound Net', 'Balas', 'Fixed net arrangement with lead and pound sections'],

            // Explosives and chemicals (illegal)
            ['Dynamite / Blast', 'Pukaw', 'Explosive device (illegal in most areas)'],
            ['Poison / Cyanide', 'Lason', 'Chemical poison (illegal in most areas)'],

            // Spears and harpooning
            ['Spear', 'Sibat / Pangga', 'Pointed weapon for stabbing fish'],
            ['Fish Wheel', 'Pandal', 'Rotating wheel with baskets to catch fish'],

            // Traditional methods
            ['Cormorant Fishing', 'Iti-iti', 'Training birds to catch fish (rarely used)'],
            ['Electrofishing', 'Kuryente', 'Electric current to stun fish (often illegal)'],

            // Other
            ['Gleaning / Hand Picking', 'Aksyon / Pagkuha sa kamay', 'Manually picking or collecting sea creatures'],
            ['Not Specified', null, 'Gear type not specified or unknown'],
        ];

        foreach ($gearTypes as [$name, $localName, $description]) {
            GearType::create([
                'name' => $name,
                'local_name' => $localName,
                'description' => $description,
            ]);
        }
    }
}
