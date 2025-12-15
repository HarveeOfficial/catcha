<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>{{ __('Hello!') }}</h2>
        
        <p>{{ __('Please click the button below to verify your email address.') }}</p>
        
        <p>
            <a href="{{ $verificationUrl }}" style="display: inline-block; padding: 12px 30px; background-color: #0066cc; color: #fff; text-decoration: none; border-radius: 4px;">
                {{ __('Verify Email Address') }}
            </a>
        </p>
        
        <p style="color: #666; font-size: 14px;">
            {{ __('Or copy and paste this link in your browser:') }}<br>
            <a href="{{ $verificationUrl }}" style="color: #0066cc;">{{ $verificationUrl }}</a>
        </p>
        
        <p style="color: #666; font-size: 14px;">{{ __('If you did not change your email address, no further action is required.') }}</p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="color: #666; font-size: 12px;">
            {{ __('Regards') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
