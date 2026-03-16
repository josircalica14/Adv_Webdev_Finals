<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, string $token): RedirectResponse
    {
        $record = \App\Models\EmailVerification::where('token', $token)->first();

        if (!$record || now()->isAfter($record->expires_at)) {
            return redirect()->route('dashboard.index')
                ->withErrors(['verification' => 'Invalid or expired verification link.']);
        }

        $record->user->update(['is_verified' => true, 'email_verified_at' => now()]);
        $record->delete();

        return redirect()->route('dashboard.index')->with('status', 'Email verified successfully.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->is_verified) {
            return back()->with('status', 'Email already verified.');
        }

        \App\Models\EmailVerification::where('user_id', $user->id)->delete();

        $token = Str::random(64);
        $user->emailVerifications()->create([
            'token'      => $token,
            'expires_at' => now()->addHours(24),
        ]);

        try {
            Mail::to($user->email)->queue(new WelcomeMail($user, $token));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Resend verification failed', ['user_id' => $user->id]);
        }

        return back()->with('status', 'Verification email resent.');
    }
}
