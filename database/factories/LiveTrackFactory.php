<?php

namespace Database\Factories;

use App\Models\LiveTrack;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LiveTrack>
 */
class LiveTrackFactory extends Factory
{
    protected $model = LiveTrack::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'public_id' => Str::random(10),
            'write_key_hash' => bcrypt('secret'),
            'title' => $this->faker->sentence(3),
            'started_at' => now(),
            'is_active' => true,
        ];
    }
}
