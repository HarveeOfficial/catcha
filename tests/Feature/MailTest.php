<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailTest extends TestCase
{
    public function test_email_can_be_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $user->sendEmailVerificationNotification('newemail@example.com');

        Mail::assertSent(\App\Notifications\VerifyEmail::class);
    }
}
