<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function showForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'      => $request->full_name,
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'email'     => $request->email,
            'program'   => $request->program,
            'password'  => $request->password,
        ]);

        $this->portfolioService->getOrCreatePortfolio($user);
        app(\App\Services\ShowcaseService::class)->invalidateCache();

        $token = Str::random(64);
        $user->emailVerifications()->create([
            'token'      => $token,
            'expires_at' => now()->addHours(24),
        ]);

        try {
            Mail::to($user->email)->queue(new WelcomeMail($user, $token));
        } catch (\Throwable $e) {
            Log::error('Welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }

        Auth::login($user);
        Log::channel('single')->info('Registration', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return redirect()->route('dashboard.index');
    }
}
