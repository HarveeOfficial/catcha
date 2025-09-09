<?php

namespace Tests\Feature;

use App\Models\CatchFeedback;
use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpertCanEditOwnFeedbackTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function expert_can_update_their_own_feedback(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $owner = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $owner->id]);
        $feedback = CatchFeedback::factory()->create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => false,
            'comments' => 'Initial',
        ]);

        $this->actingAs($expert);
        $resp = $this->patch(route('catches.feedback.update', $feedback), [
            'comments' => 'Updated comment',
            'approved' => 1,
        ]);
        $resp->assertRedirect();

        $this->assertDatabaseHas('catch_feedback', [
            'id' => $feedback->id,
            'comments' => 'Updated comment',
            'approved' => 1,
        ]);
    }

    /** @test */
    public function expert_cannot_update_others_feedback(): void
    {
        $expertA = User::factory()->create(['role' => 'expert']);
        $expertB = User::factory()->create(['role' => 'expert']);
        $owner = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $owner->id]);
        $feedback = CatchFeedback::factory()->create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expertA->id,
            'approved' => false,
            'comments' => 'Initial',
        ]);

        $this->actingAs($expertB);
        $this->patch(route('catches.feedback.update', $feedback), [
            'comments' => 'Hacked',
        ])->assertForbidden();
    }
}
