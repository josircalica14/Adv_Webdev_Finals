<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ChangeUsernameRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\FileStorageService;
use App\Services\ProfileService;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private FileStorageService $fileStorage,
        private PortfolioService $portfolioService
    ) {}

    public function show(Request $request): View
    {
        return view('dashboard.profile', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        try {
            $this->profileService->updateProfile($request->user(), $request->validated());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['username' => $e->getMessage()]);
        }
        return back()->with('status', 'Profile updated.');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate(['photo' => 'required|file|mimes:jpeg,png,webp|max:5120']);
        $user = $request->user();

        try {
            // Store directly on the portfolio disk without creating a portfolio item
            $photo = $request->file('photo');
            $name  = 'profile_photos/' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('portfolio')->put($name, file_get_contents($photo->getRealPath()));

            // Delete old photo if exists
            if ($user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('portfolio')->exists($user->profile_photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('portfolio')->delete($user->profile_photo_path);
            }

            $user->update(['profile_photo_path' => $name]);
        } catch (\Throwable $e) {
            return back()->withErrors(['photo' => $e->getMessage()]);
        }

        return back()->with('status', 'Photo updated.');
    }

    public function changeUsername(ChangeUsernameRequest $request): RedirectResponse
    {
        try {
            $this->profileService->changeUsername($request->user(), $request->validated('username'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['username' => $e->getMessage()]);
        }
        return back()->with('status', 'Username updated.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => $request->password]);
        \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', $user->id)
            ->where('id', '!=', $request->session()->getId())->delete();

        Log::channel('single')->info('Password changed', ['user_id' => $user->id]);
        return back()->with('status', 'Password changed.');
    }
}
