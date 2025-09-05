<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FishCatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CatchFeedbackAiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure migrations run
        $this->artisan('migrate', ['--no-interaction' => true]);
    }

    public function test_ai_assistant_visible_for_expert(): void
    {
        $expert = User::factory()->create(['role' => 'expert']);
        $catch = FishCatch::factory()->create(['user_id' => $expert->id]);

        $resp = $this->actingAs($expert)->get(route('catches.feedback.index', $catch));
        $resp->assertStatus(200);
        $resp->assertSee('AI Assistant Suggestion');
    }

    public function test_ai_assistant_hidden_for_regular_user(): void
    {
    $user = User::factory()->create(['role' => 'fisher']);
        $catch = FishCatch::factory()->create(['user_id' => $user->id]);

        $resp = $this->actingAs($user)->get(route('catches.feedback.index', $catch));
        $resp->assertStatus(200);
        $resp->assertDontSee('AI Assistant Suggestion');
    }
}
