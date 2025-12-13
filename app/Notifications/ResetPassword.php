<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], absolute: true);

        return (new MailMessage)
            ->subject('Catcha - Password Reset Request')
            ->markdown('mail.reset-password', [
                'actionUrl' => $resetUrl,
                'notifiable' => $notifiable,
            ]);
    }
}
