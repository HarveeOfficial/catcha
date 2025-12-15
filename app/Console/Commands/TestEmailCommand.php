<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending verification email';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $user = User::first();

        if (! $user) {
            $this->error('No users found. Create a user first.');

            return;
        }

        $this->info("Sending test email to {$user->email}...");

        try {
            $user->sendEmailVerificationNotification('test-newemail@example.com');
            $this->info('Email sent successfully!');
            $this->info('Check storage/logs/laravel.log for the email content.');
        } catch (\Exception $e) {
            $this->error('Error sending email: '.$e->getMessage());
        }
    }
}
