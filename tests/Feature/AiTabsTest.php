<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AiTabsTest extends TestCase
{

    public function test_consult_get_redirects_to_chat_with_consult_tab(): void
    {
    $user = User::factory()->make();

        $resp = $this->actingAs($user)->get('/ai/consult');

        $resp->assertRedirect(route('ai.chat', ['tab' => 'consult']));
    }

    public function test_chat_page_loads_and_shows_tabs(): void
    {
    $user = User::factory()->make();

        $resp = $this->actingAs($user)->get(route('ai.chat'));

        $resp->assertOk();
        $resp->assertSee('AI Assistant');
        $resp->assertSee('Chat');
        $resp->assertSee('Consult');
    }
}
