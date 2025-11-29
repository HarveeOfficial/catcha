<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\User;
use Tests\TestCase;

class ExpertCanEditCatchTest extends TestCase
{
    public function test_expert_can_view_edit_button_on_catch_show_page(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $fisher = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $fisher->id]);

        $response = $this->actingAs($expert)->get(route('catches.show', $catch));

        $response->assertStatus(200);
        $response->assertSee('Edit', escape: false); // Check for edit button
    }

    public function test_expert_can_access_edit_form(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $fisher = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $fisher->id]);

        $response = $this->actingAs($expert)->get(route('catches.edit', $catch));

        $response->assertStatus(200);
    }

    public function test_expert_can_edit_catch_with_feedback(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $fisher = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $fisher->id]);

        // Create feedback on the catch
        $catch->feedbacks()->create([
            'expert_id' => $expert->id,
            'comments' => 'Test feedback',
            'approved' => true,
        ]);

        // Expert should still be able to edit
        $response = $this->actingAs($expert)->patch(route('catches.update', $catch), [
            'caught_at' => '2025-11-29',
            'quantity' => 50,
        ]);

        $response->assertRedirect(route('catches.show', $catch));
        $this->assertEquals(50, $catch->fresh()->quantity);
    }

    public function test_fisher_cannot_edit_catch_with_feedback(): void
    {
        $fisher = User::factory()->create(['role' => 'fisher']);
        $expert = User::factory()->create(['role' => 'expert']);
        $catch = FishCatch::factory()->create(['user_id' => $fisher->id]);

        // Create feedback on the catch
        $catch->feedbacks()->create([
            'expert_id' => $expert->id,
            'comments' => 'Test feedback',
            'approved' => true,
        ]);

        // Fisher should not be able to edit
        $response = $this->actingAs($fisher)->patch(route('catches.update', $catch), [
            'caught_at' => '2025-11-29',
            'quantity' => 50,
        ]);

        $response->assertStatus(403);
    }

    public function test_fisher_can_edit_own_catch_without_feedback(): void
    {
        $fisher = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $fisher->id]);

        $response = $this->actingAs($fisher)->patch(route('catches.update', $catch), [
            'caught_at' => '2025-11-29',
            'quantity' => 50,
        ]);

        $response->assertRedirect(route('catches.show', $catch));
        $this->assertEquals(50, $catch->fresh()->quantity);
    }
}
