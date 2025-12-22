<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Boat>
 */
class BoatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $registrationDate = fake()->dateTimeBetween('-2 years', 'now');
        $expiryDate = fake()->dateTimeBetween($registrationDate, '+3 years');

        return [
            'user_id' => User::factory(),
            'registration_number' => strtoupper(fake()->unique()->bothify('??-####-??')),
            'name' => fake()->randomElement(['F/B', 'M/B', 'B/C']).' '.fake()->lastName(),
            'owner_name' => fake()->name(),
            'owner_contact' => fake()->phoneNumber(),
            'boat_type' => fake()->randomElement(['motorized', 'non-motorized']),
            'length_m' => fake()->randomFloat(2, 3, 25),
            'width_m' => fake()->randomFloat(2, 1, 6),
            'gross_tonnage' => fake()->randomFloat(2, 0.5, 50),
            'engine_type' => fake()->randomElement(['Diesel', 'Gasoline', 'Outboard', null]),
            'engine_horsepower' => fake()->optional()->numberBetween(5, 200),
            'home_port' => fake()->city(),
            'psgc_region' => null,
            'psgc_municipality' => null,
            'psgc_barangay' => null,
            'registration_date' => $registrationDate,
            'expiry_date' => $expiryDate,
            'status' => fake()->randomElement(['active', 'active', 'active', 'expired', 'suspended']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiry_date' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    public function motorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'boat_type' => 'motorized',
            'engine_type' => fake()->randomElement(['Diesel', 'Gasoline', 'Outboard']),
            'engine_horsepower' => fake()->numberBetween(10, 200),
        ]);
    }

    public function nonMotorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'boat_type' => 'non-motorized',
            'engine_type' => null,
            'engine_horsepower' => null,
        ]);
    }
}
