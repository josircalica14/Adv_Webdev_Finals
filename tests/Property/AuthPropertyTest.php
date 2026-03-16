<?php

// Properties 1–7: Auth

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;

// Property 1: Any valid registration creates a user with hashed password
it('P1: registration always hashes the password', function () {
    Mail::fake();
    $password = 'ValidPass1!';

    $this->post('/register', [
        'full_name'             => 'Test User',
        'email'                 => 'p1@example.com',
        'username'              => 'p1user',
        'program'               => 'BSIT',
        'password'              => $password,
        'password_confirmation' => $password,
    ]);

    $user = User::where('email', 'p1@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->password)->not->toBe($password)
        ->and(Hash::check($password, $user->password))->toBeTrue();
});

// Property 2: Login with correct credentials always succeeds
it('P2: login with correct credentials always authenticates', function () {
    $user = User::factory()->create(['password' => Hash::make('Correct1!')]);

    $this->post('/login', ['email' => $user->email, 'password' => 'Correct1!']);
    $this->assertAuthenticatedAs($user);
});

// Property 3: Login with wrong password always fails
it('P3: login with wrong password never authenticates', function () {
    User::factory()->create(['email' => 'p3@example.com', 'password' => Hash::make('Correct1!')]);

    $this->post('/login', ['email' => 'p3@example.com', 'password' => 'Wrong1!']);
    $this->assertGuest();
});

// Property 4: Logout always clears the session
it('P4: logout always clears authentication', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post('/logout');
    $this->assertGuest();
});

// Property 5: Password reset token is valid for exactly 1 hour
it('P5: password reset token expires after 1 hour', function () {
    $user = User::factory()->create();
    $token = \Illuminate\Support\Str::random(64);

    \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make($token),
        'created_at' => now()->subMinutes(61),
    ]);

    $response = $this->post('/password/reset', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'NewPass1!',
        'password_confirmation' => 'NewPass1!',
    ]);

    $response->assertSessionHasErrors();
});

// Property 6: Email verification token is valid for 24 hours
it('P6: email verification token valid within 24 hours', function () {
    $user = User::factory()->create(['is_verified' => false]);
    $token = \Illuminate\Support\Str::random(64);

    \App\Models\EmailVerification::create([
        'user_id'    => $user->id,
        'token'      => $token,
        'expires_at' => now()->addHours(23), // still valid
    ]);

    $this->get("/email/verify/{$token}");
    expect($user->fresh()->is_verified)->toBeTrue();
});

// Property 7: bcrypt cost is at least 12 (or the configured BCRYPT_ROUNDS)
it('P7: bcrypt cost matches configured rounds', function () {
    $hash = Hash::make('TestPassword1!');
    // Extract cost from bcrypt hash: $2y$COST$...
    preg_match('/^\$2y\$(\d+)\$/', $hash, $matches);
    $cost = (int) $matches[1];
    // In test env BCRYPT_ROUNDS=4, in production it should be 12
    expect($cost)->toBeGreaterThanOrEqual(4);
    // Verify the configured rounds are used
    expect($cost)->toBe((int) config('hashing.bcrypt.rounds', 12));
});
