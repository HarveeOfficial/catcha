<?php

namespace Tests\Feature;

use App\Models\CatchFeedback;
use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FisherCannotEditAfterFeedbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function fisher_owner_cannot_edit_after_feedback_exists(): void
    {
        $owner = User::factory()->create(['role' => 'fisher']);
        $expert = User::factory()->create(['role' => 'expert']);
        $catch = FishCatch::factory()->create(['user_id' => $owner->id]);
        // Add feedback by expert
        CatchFeedback::factory()->create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => true,
            'comments' => 'Looks good.'
        ]);

        $this->actingAs($owner);
        $this->get(route('catches.edit', $catch))->assertForbidden();
        $this->patch(route('catches.update', $catch), [
            'caught_at' => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    /** @test */
    public function expert_can_edit_even_if_feedback_exists(): void
    {
        $owner = User::factory()->create(['role' => 'fisher']);
        $expert = User::factory()->create(['role' => 'expert']);
        $catch = FishCatch::factory()->create(['user_id' => $owner->id]);
        CatchFeedback::factory()->create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => true,
            'comments' => 'Looks good.'
        ]);

        $this->actingAs($expert);
        $this->get(route('catches.edit', $catch))->assertOk();
        $this->patch(route('catches.update', $catch), [
            'caught_at' => now()->format('Y-m-d H:i:s'),
            'quantity' => 1,
        ])->assertRedirect(route('catches.show', $catch));
    }
}
