<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showRequestForm(): View
    {
        return view('auth.password-reset-request');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token = Str::random(64)), 'created_at' => now()]
            );

            try {
                Mail::to($user->email)->queue(new PasswordResetMail($user, $token));
            } catch (\Throwable $e) {
                Log::error('Password reset email failed', ['email' => $user->email, 'error' => $e->getMessage()]);
            }
        }

        // Always return the same response regardless of whether email exists
        return back()->with('status', 'If that email is registered, a reset link has been sent.');
    }

    public function showResetForm(string $token): View
    {
        return view('auth.password-reset', compact('token'));
    }

    public function reset(PasswordResetRequest $request): RedirectResponse
    {
        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Invalid or expired reset token.']);
        }

        $createdAt = $record->created_at ? \Carbon\Carbon::parse($record->created_at) : null;
        if (!$createdAt || $createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Reset token has expired.']);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => $request->password]);

        // Invalidate all sessions for this user
        DB::table('sessions')->where('user_id', $user->id)->delete();
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        Log::channel('single')->info('Password reset', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return redirect()->route('login')->with('status', 'Password reset successfully. Please log in.');
    }
}
