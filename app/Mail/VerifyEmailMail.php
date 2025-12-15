<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class VerifyEmailMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $recipientEmail,
        public int $userId,
        public string $verifyEmail,
    ) {
        $this->to($recipientEmail);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->recipientEmail],
            subject: __('Verify Email Address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $this->userId,
                'hash' => sha1($this->verifyEmail),
            ]
        );

        return new Content(
            view: 'emails.verify-email',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
