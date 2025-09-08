<?php

namespace Tests\Feature;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiConversationDeleteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_own_conversation_via_json(): void
    {
        $user = User::factory()->create();
        $conv = AiConversation::create(['user_id' => $user->id, 'title' => 'Test', 'model' => 'gpt-4o-mini']);
        AiMessage::create(['ai_conversation_id' => $conv->id, 'role' => 'user', 'content' => 'Hi']);
        $response = $this->actingAs($user)->deleteJson(route('ai.conversations.destroy', $conv));
        $response->assertStatus(302); // redirect (controller returns redirect)
        $this->assertDatabaseMissing('ai_conversations', ['id' => $conv->id]);
    }

    public function test_user_cannot_delete_others_conversation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $conv = AiConversation::create(['user_id' => $other->id, 'title' => 'Other', 'model' => 'gpt-4o-mini']);
        $resp = $this->actingAs($user)->delete(route('ai.conversations.destroy', $conv));
        $resp->assertStatus(403);
        $this->assertDatabaseHas('ai_conversations', ['id' => $conv->id]);
    }
}
