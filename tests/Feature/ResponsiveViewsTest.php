<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FishCatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveViewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function catches_index_renders_mobile_cards_and_table()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        // create one catch (factory will create species optionally)
        FishCatch::factory()->create(['user_id'=>$user->id]);

        $response = $this->get(route('catches.index'));
        $response->assertStatus(200);
        // Table (desktop) markup
        $response->assertSee('<table', false);
        // Mobile card container
        $response->assertSee('md:hidden', false);
    }
}
