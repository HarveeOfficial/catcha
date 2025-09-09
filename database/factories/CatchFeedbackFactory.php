<?php

namespace Database\Factories;

use App\Models\CatchFeedback;
use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatchFeedback>
 */
class CatchFeedbackFactory extends Factory
{
    protected $model = CatchFeedback::class;

    public function definition(): array
    {
        return [
            'fish_catch_id' => FishCatch::factory(),
            'expert_id' => User::factory()->state(['role' => 'expert']),
            'approved' => $this->faker->boolean(),
            'comments' => $this->faker->sentence(8),
            'flags' => null,
        ];
    }
}
