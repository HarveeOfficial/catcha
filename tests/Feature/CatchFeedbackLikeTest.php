<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FishCatch;
use App\Models\CatchFeedback;

class CatchFeedbackLikeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--no-interaction' => true]);
    }

    public function test_user_can_like_and_unlike_feedback(): void
    {
        $expert = User::factory()->create(['role'=>'expert']);
        $fisher = User::factory()->create(['role'=>'fisher']);
        $catch = FishCatch::factory()->create();
        $fb = CatchFeedback::create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => true,
            'comments' => 'Test sustainability notes.'
        ]);

        $this->actingAs($fisher)->post(route('catches.feedback.like',$fb));
        $this->assertDatabaseHas('catch_feedback_likes', [
            'catch_feedback_id' => $fb->id,
            'user_id' => $fisher->id
        ]);

        $this->actingAs($fisher)->delete(route('catches.feedback.unlike',$fb));
        $this->assertDatabaseMissing('catch_feedback_likes', [
            'catch_feedback_id' => $fb->id,
            'user_id' => $fisher->id
        ]);
    }

    public function test_expert_can_delete_own_feedback(): void
    {
        $expert = User::factory()->create(['role'=>'expert']);
        $catch = FishCatch::factory()->create();
        $fb = CatchFeedback::create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => false,
            'comments' => 'Temp comment'
        ]);
        $this->actingAs($expert)->delete(route('catches.feedback.destroy',$fb));
        $this->assertDatabaseMissing('catch_feedback', ['id'=>$fb->id]);
    }

    public function test_admin_cannot_delete_others_feedback(): void
    {
        $expert = User::factory()->create(['role'=>'expert']);
        $admin = User::factory()->create(['role'=>'admin']);
        $catch = FishCatch::factory()->create();
        $fb = CatchFeedback::create([
            'fish_catch_id' => $catch->id,
            'expert_id' => $expert->id,
            'approved' => false,
            'comments' => 'Owner comment'
        ]);
        $resp = $this->actingAs($admin)->delete(route('catches.feedback.destroy',$fb));
        $resp->assertStatus(403);
        $this->assertDatabaseHas('catch_feedback', ['id'=>$fb->id]);
    }
}
