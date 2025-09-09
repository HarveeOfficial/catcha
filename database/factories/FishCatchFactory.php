<?php

namespace Database\Factories;

use App\Models\FishCatch;
use App\Models\User;
use App\Models\Species;
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
        return [
            'user_id' => User::factory()->state(['role' => 'fisher']),
            'species_id' => Species::query()->inRandomOrder()->value('id'),
            'location' => $this->faker->city(),
            'caught_at' => Carbon::now()->subHours(rand(1,48)),
            'quantity' => $this->faker->randomFloat(2, 1, 50),
            'count' => $this->faker->numberBetween(1, 30),
            'avg_size_cm' => $this->faker->randomFloat(2, 10, 120),
            'gear_type' => $this->faker->randomElement(['net','line','trap','trawl']),
            'vessel_name' => $this->faker->optional()->word(),
            'environmental_data' => null,
            'notes' => null,
            'flagged' => false,
            'flag_reason' => null,
        ];
    }
}
