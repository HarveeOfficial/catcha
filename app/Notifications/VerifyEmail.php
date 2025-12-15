<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    public ?string $emailToVerify = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(?string $email = null)
    {
        $this->emailToVerify = $email;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('Verify Email Address'))
            ->greeting(__('Hello!'))
            ->line(__('Please click the button below to verify your email address.'))
            ->action(__('Verify Email Address'), $verificationUrl)
            ->line(__('If you did not change your email address, no further action is required.'));
    }

    /**
     * Get the email address the notification should be sent to.
     */
    public function routeNotificationForMail(object $notifiable): string|array
    {
        return $this->emailToVerify ?? $notifiable->email;
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl(mixed $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            \Carbon\Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($this->emailToVerify ?? $notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
