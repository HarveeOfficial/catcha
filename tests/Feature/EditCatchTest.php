<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditCatchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function owner_can_view_edit_form_and_update(): void
    {
        $user = User::factory()->create();
        $catch = FishCatch::factory()->create(['user_id' => $user->id, 'quantity' => 1.5]);
        $this->actingAs($user);

        $this->get(route('catches.edit', $catch))->assertOk();

        $payload = [
            'caught_at' => now()->format('Y-m-d H:i:s'),
            'quantity' => 2.75,
        ];
        $resp = $this->patch(route('catches.update', $catch), $payload);
        $resp->assertRedirect(route('catches.show', $catch));

        $this->assertDatabaseHas('fish_catches', [
            'id' => $catch->id,
            'quantity' => 2.75,
        ]);
    }

    /** @test */
    public function non_owner_cannot_edit_when_not_admin_or_expert(): void
    {
    $owner = User::factory()->create();
    $other = User::factory()->create();
        $catch = FishCatch::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other);
        $this->get(route('catches.edit', $catch))->assertForbidden();
        $this->patch(route('catches.update', $catch), [
            'caught_at' => now()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }
}
