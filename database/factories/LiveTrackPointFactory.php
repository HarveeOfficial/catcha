<?php

namespace Database\Factories;

use App\Models\LiveTrackPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LiveTrackPoint>
 */
class LiveTrackPointFactory extends Factory
{
    protected $model = LiveTrackPoint::class;

    public function definition(): array
    {
        return [
            'live_track_id' => null,
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'accuracy_m' => $this->faker->randomFloat(2, 1, 20),
            'speed_mps' => $this->faker->randomFloat(2, 0, 10),
            'bearing_deg' => $this->faker->randomFloat(2, 0, 360),
            'recorded_at' => now(),
            'meta' => null,
        ];
    }
}
