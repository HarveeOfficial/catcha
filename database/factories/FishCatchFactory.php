<?php

namespace Database\Factories;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<FishCatch>
 */
class FishCatchFactory extends Factory
{
    protected $model = FishCatch::class;

    public function definition(): array
    {
        $bycatchIds = $this->faker->optional(0.3)->randomElements(Species::query()->pluck('id')->all(), rand(1, 3)) ?? [];
        $discardIds = $this->faker->optional(0.2)->randomElements(Species::query()->pluck('id')->all(), rand(1, 2)) ?? [];

        return [
            'user_id' => User::factory()->state(['role' => 'fisher']),
            'species_id' => Species::query()->inRandomOrder()->value('id'),
            'location' => $this->faker->city(),
            'caught_at' => Carbon::now()->subHours(rand(1, 48)),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'count' => $this->faker->numberBetween(1, 30),
            'avg_size_cm' => $this->faker->randomFloat(2, 10, 120),
            'bycatch_quantity' => $this->faker->optional(0.3)->randomFloat(2, 0.1, 10),
            'bycatch_species_ids' => count($bycatchIds) > 0 ? $bycatchIds : null,
            'discard_quantity' => $this->faker->optional(0.2)->randomFloat(2, 0.1, 5),
            'discard_species_ids' => count($discardIds) > 0 ? $discardIds : null,
            'discard_reason' => $this->faker->optional(0.2)->randomElement(['too_small', 'damaged', 'dead', 'species_not_allowed', 'over_quota', 'other']),
            'gear_type' => $this->faker->randomElement(['net', 'line', 'trap', 'trawl']),
            'vessel_name' => $this->faker->optional()->word(),
            'environmental_data' => null,
            'notes' => null,
            'flagged' => false,
            'flag_reason' => null,
        ];
    }
}
