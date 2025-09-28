<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiSuggestionCatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_and_caches_suggestion_for_catch_show(): void
    {
        config(['services.openai.key' => 'test-key']);

        $user = User::factory()->create();
        $species = Species::factory()->create(['common_name' => 'Tuna']);
        $catch = FishCatch::factory()->for($user)->for($species)->create([
            'quantity' => 12.3,
            'count' => 3,
        ]);

        // Fake OpenAI response
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => "• Consider slot limits\n• Don\'t touch that: size is compliant\n• Use barbless hooks"]],
                ],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 20],
            ], 200),
        ]);

        // First call should generate and store
        $this->actingAs($user)
            ->postJson(route('ai.suggestions.catches.generate', $catch))
            ->assertOk()
            ->assertJson(['cached' => false])
            ->assertJsonStructure(['content', 'model', 'updated_at']);

        // Second call without force returns cached and should NOT call OpenAI again
        Http::fake(); // if it tries to call, test would fail with missing fake

        $this->actingAs($user)
            ->postJson(route('ai.suggestions.catches.generate', $catch))
            ->assertOk()
            ->assertJson(['cached' => true])
            ->assertJsonStructure(['content', 'model', 'updated_at']);

        // GET endpoint returns exists=true
        $this->actingAs($user)
            ->getJson(route('ai.suggestions.catches.show', $catch))
            ->assertOk()
            ->assertJson(['exists' => true]);
    }
}
