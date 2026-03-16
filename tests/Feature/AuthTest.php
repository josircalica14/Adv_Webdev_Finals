<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\WelcomeMail;

// --- Registration ---

it('registers a new user and creates a portfolio', function () {
    Mail::fake();

    $response = $this->post('/register', [
        'full_name'             => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'username'              => 'janedoe',
        'program'               => 'BSIT',
        'password'              => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertRedirect();

    $user = User::where('email', 'jane@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->full_name)->toBe('Jane Doe')
        ->and($user->program)->toBe('BSIT');

    expect(Portfolio::where('user_id', $user->id)->exists())->toBeTrue();
    Mail::assertQueued(WelcomeMail::class);
});

it('rejects registration with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post('/register', [
        'full_name'             => 'Another User',
        'email'                 => 'taken@example.com',
        'username'              => 'anotheruser',
        'program'               => 'CSE',
        'password'              => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects registration with weak password', function () {
    $response = $this->post('/register', [
        'full_name'             => 'Weak Pass',
        'email'                 => 'weak@example.com',
        'username'              => 'weakpass',
        'program'               => 'BSIT',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertSessionHasErrors('password');
});

// --- Email Verification ---

it('verifies email with valid token', function () {
    $user = User::factory()->create(['is_verified' => false]);
    $token = \Illuminate\Support\Str::random(64);
    EmailVerification::create([
        'user_id'    => $user->id,
        'token'      => $token,
        'expires_at' => now()->addHours(24),
    ]);

    $response = $this->get("/email/verify/{$token}");
    $response->assertRedirect();

    expect($user->fresh()->is_verified)->toBeTrue();
});

it('rejects expired verification token', function () {
    $user = User::factory()->create(['is_verified' => false]);
    $token = \Illuminate\Support\Str::random(64);
    EmailVerification::create([
        'user_id'    => $user->id,
        'token'      => $token,
        'expires_at' => now()->subHour(),
    ]);

    $response = $this->get("/email/verify/{$token}");
    expect($user->fresh()->is_verified)->toBeFalse();
});

// --- Login ---

it('logs in with valid credentials', function () {
    $user = User::factory()->create([
        'email'    => 'login@example.com',
        'password' => Hash::make('Password1!'),
    ]);

    $response = $this->post('/login', [
        'email'    => 'login@example.com',
        'password' => 'Password1!',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
});

it('rejects login with wrong password', function () {
    User::factory()->create([
        'email'    => 'wrong@example.com',
        'password' => Hash::make('CorrectPass1!'),
    ]);

    $response = $this->post('/login', [
        'email'    => 'wrong@example.com',
        'password' => 'WrongPass1!',
    ]);

    $response->assertSessionHasErrors();
    $this->assertGuest();
});

// --- Logout ---

it('logs out authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');
    $response->assertRedirect();
    $this->assertGuest();
});
