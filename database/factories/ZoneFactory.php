<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'description' => $this->faker->sentence(),
            'geometry' => [
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [123.5, 10.3],
                                    [123.6, 10.3],
                                    [123.6, 10.4],
                                    [123.5, 10.4],
                                    [123.5, 10.3],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
