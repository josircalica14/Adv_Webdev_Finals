<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\PortfolioApiController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ProfileController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Portfolio\CustomizationController;
use App\Http\Controllers\Portfolio\FileController;
use App\Http\Controllers\Portfolio\PortfolioController;
use App\Http\Controllers\Portfolio\PortfolioItemController;
use App\Http\Controllers\Showcase\ShowcaseController;
use Illuminate\Support\Facades\Route;

// Public
Route::get('/', fn() => redirect()->route('showcase.index'));
Route::get('/showcase', [ShowcaseController::class, 'index'])->name('showcase.index');
Route::get('/portfolio/{username}', [PortfolioController::class, 'publicView'])->name('portfolio.public');

// Chatbot API (rate-limited)
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat')->middleware('throttle:20,1');

// Email verification (auth required for resend)
Route::get('/email/verify/{token}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend')->middleware('auth');

// Guest-only auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/password/reset', [PasswordResetController::class, 'showRequestForm'])->name('password.request');
    Route::post('/password/email', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard (auth required)
Route::middleware('auth')->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.photo');
    Route::put('/profile/username', [ProfileController::class, 'changeUsername'])->name('profile.username');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Settings
    Route::get('/settings', [SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings/visibility', [SettingsController::class, 'updateVisibility'])->name('settings.visibility');

    // Portfolio visibility
    Route::put('/portfolio/visibility', [PortfolioController::class, 'toggleVisibility'])->name('portfolio.visibility');

    // Portfolio items
    Route::get('/items/create', [PortfolioItemController::class, 'create'])->name('items.create');
    Route::post('/items', [PortfolioItemController::class, 'store'])->name('items.store');
    Route::put('/items/reorder', [PortfolioItemController::class, 'reorder'])->name('items.reorder');
    Route::get('/items/{item}/edit', [PortfolioItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [PortfolioItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [PortfolioItemController::class, 'destroy'])->name('items.destroy');
    Route::put('/items/{item}/visibility', [PortfolioItemController::class, 'toggleVisibility'])->name('items.visibility');

    // Files
    Route::post('/items/{item}/files', [FileController::class, 'store'])->name('files.store')->middleware('upload.ratelimit');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // Customization
    Route::get('/customize', [CustomizationController::class, 'show'])->name('customize.show');
    Route::put('/customize', [CustomizationController::class, 'save'])->name('customize.save');
    Route::post('/customize/reset', [CustomizationController::class, 'reset'])->name('customize.reset');

    // PDF export
    Route::get('/export/pdf', [PortfolioApiController::class, 'export'])->name('export.pdf');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/items/{item}/flag', [AdminController::class, 'flagItem'])->name('items.flag');
    Route::put('/items/{item}/hide', [AdminController::class, 'hideItem'])->name('items.hide');
    Route::put('/items/{item}/unhide', [AdminController::class, 'unhideItem'])->name('items.unhide');
    Route::post('/users/{user}/notify', [AdminController::class, 'notify'])->name('users.notify');
    Route::get('/flagged', [AdminController::class, 'flaggedContent'])->name('flagged');
});
