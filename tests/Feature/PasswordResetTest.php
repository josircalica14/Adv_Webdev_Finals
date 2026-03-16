<?php

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\PasswordResetMail;

it('sends password reset link for existing email', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'reset@example.com']);

    $response = $this->post('/password/email', ['email' => 'reset@example.com']);
    $response->assertRedirect();

    Mail::assertQueued(PasswordResetMail::class);
});

it('returns same response for non-existent email (no enumeration)', function () {
    Mail::fake();

    $response = $this->post('/password/email', ['email' => 'nobody@example.com']);
    $response->assertRedirect();

    // No mail should be sent for non-existent email
    Mail::assertNothingQueued();
});

it('resets password with valid token', function () {
    $user = User::factory()->create(['email' => 'resetme@example.com']);
    $token = \Illuminate\Support\Str::random(64);

    \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make($token),
        'created_at' => now(),
    ]);

    $response = $this->from('/password/reset/' . $token)->post('/password/reset', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ]);

    $response->assertRedirect(route('login'));
    expect(Hash::check('NewPassword1!', $user->fresh()->password))->toBeTrue();
});

it('rejects reset with expired token', function () {
    $user = User::factory()->create(['email' => 'expired@example.com']);
    $token = \Illuminate\Support\Str::random(64);

    \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
        'email'      => $user->email,
        'token'      => Hash::make($token),
        'created_at' => now()->subHours(2)->toDateTimeString(),
    ]);

    $originalPassword = $user->password;

    $this->from('/password/reset/' . $token)->post('/password/reset', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ]);

    // Password should NOT have changed (expired token)
    // Note: if the controller doesn't properly detect expiry, this test will catch it
    expect($user->fresh()->getRawOriginal('password'))->toBe($originalPassword);
});
