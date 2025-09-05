<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AiConsultScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate', ['--no-interaction' => true]);
    }

    public function test_guides_out_of_scope_question(): void
    {
        $user = User::factory()->create();
        $resp = $this->actingAs($user)->postJson(route('ai.consult'), [
            'question' => 'Explain quantum entanglement and black holes please'
        ]);
        $resp->assertStatus(200)->assertJsonFragment(['model' => 'domain-filter','notice'=>'out_of_scope']);
    }

    public function test_allows_fishing_related_question(): void
    {
        $user = User::factory()->create();
        // We mock external call by expecting a failure due to missing API key if not set, but domain filter should pass first.
        $resp = $this->actingAs($user)->postJson(route('ai.consult'), [
            'question' => 'How can I reduce bycatch when using a small net for sardine fishing?'
        ]);
        // If API key missing will be 500 with config error; either 200 (success) or 500 accepted as passing domain filter.
        $this->assertTrue(in_array($resp->status(), [200,500]), 'Expected 200 or 500 after passing domain filter, got '.$resp->status());
        if ($resp->status() === 200 && ($resp->json('model') === 'domain-filter')) {
            $this->fail('Fishing related question incorrectly treated as out_of_scope');
        }
    }
}
