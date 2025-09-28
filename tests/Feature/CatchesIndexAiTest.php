<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatchesIndexAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_suggestions_ui_renders_on_show(): void
    {
        $user = User::factory()->create();
        $species = Species::factory()->create();
        $catch = FishCatch::factory()->for($user)->for($species)->create();

        $this->actingAs($user)
            ->get(route('catches.show', $catch))
            ->assertOk()
            ->assertSee('AI suggestions')
            ->assertSee('aiSuggestShow', false); // ensure Alpine component is present
    }
}
