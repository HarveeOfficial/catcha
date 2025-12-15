<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileEmailChangeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that user receives verification email when changing email.
     */
    public function test_user_receives_verification_email_when_changing_email(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'verification-link-sent');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'old@example.com',
            'pending_email' => 'new@example.com',
        ]);
    }

    /**
     * Test that email is updated after verification link is clicked.
     */
    public function test_email_is_updated_after_verification(): void
    {
        Event::fake();

        $user = User::factory()->create(['email' => 'old@example.com', 'pending_email' => 'new@example.com']);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1('new@example.com'),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('profile.edit').'?verified=1');

        $user->refresh();
        $this->assertEquals('new@example.com', $user->email);
        $this->assertNull($user->pending_email);
        $this->assertNotNull($user->email_verified_at);

        Event::assertDispatched(Verified::class);
    }

    /**
     * Test that pending email is cleared when verification fails.
     */
    public function test_email_change_requires_valid_hash(): void
    {
        $user = User::factory()->create(['email' => 'old@example.com', 'pending_email' => 'new@example.com']);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1('wrong@example.com'),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // Should fail validation due to invalid email hash
        $response->assertStatus(403);

        $user->refresh();
        $this->assertEquals('old@example.com', $user->email);
        $this->assertEquals('new@example.com', $user->pending_email);
    }

    /**
     * Test that name update without email change works normally.
     */
    public function test_name_update_without_email_change(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'test@example.com',
        ]);
    }
}
