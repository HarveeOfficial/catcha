@component('mail::message')
# ![Catcha Logo]({{ asset('logo/catcha_logo.png') }})

Hello {{ $notifiable->name }},

We received a request to reset your password for your Catcha account.

If you did not request this, you can safely ignore this email.

@component('mail::button', ['url' => $actionUrl])
Reset Password
@endcomponent

This password reset link will expire in {{ config('auth.passwords.users.expire') }} minutes.

For security reasons, do not share this link with anyone.

Thank you,

The Catcha Team
@endcomponent
