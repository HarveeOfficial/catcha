<?php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        return [
            'common_name' => $this->faker->unique()->word(),
            'scientific_name' => $this->faker->unique()->word(). 'us testus',
            'conservation_status' => $this->faker->randomElement(['least concern','vulnerable','endangered']),
            'min_size_cm' => $this->faker->numberBetween(10, 80),
            'seasonal_restrictions' => null,
        ];
    }
}
