<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        $hash = (string) $request->route('hash');

        // Check if hash matches pending email (email change verification)
        if ($user->pending_email && hash_equals($hash, sha1($user->pending_email))) {
            $user->email = $user->pending_email;
            $user->pending_email = null;
            $user->email_verified_at = now();
            $user->save();

            event(new Verified($user));

            return redirect()->intended(route('profile.edit', absolute: false).'?verified=1')->with('status', 'email-updated');
        }

        // Check if hash matches current email (initial verification)
        if (hash_equals($hash, sha1($user->getEmailForVerification()))) {
            if ($user->hasVerifiedEmail()) {
                return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        // Hash doesn't match either current or pending email
        abort(403);
    }
}
