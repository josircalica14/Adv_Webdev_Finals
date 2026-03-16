<?php

// Properties 39–40: Email

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeMail;
use App\Mail\PasswordResetMail;

// Property 39: Registration always queues a WelcomeMail
it('P39: registration queues WelcomeMail', function () {
    Mail::fake();

    $this->post('/register', [
        'full_name'             => 'Email Test User',
        'email'                 => 'emailtest@example.com',
        'username'              => 'emailtestuser',
        'program'               => 'BSIT',
        'password'              => 'Password1!',
        'password_confirmation' => 'Password1!',
    ]);

    Mail::assertQueued(WelcomeMail::class, function ($mail) {
        return $mail->hasTo('emailtest@example.com');
    });
});

// Property 40: Password reset request queues a PasswordResetMail
it('P40: password reset request queues PasswordResetMail', function () {
    Mail::fake();
    $user = User::factory()->create(['email' => 'resetmail@example.com']);

    $this->post('/password/email', ['email' => 'resetmail@example.com']);

    Mail::assertQueued(PasswordResetMail::class, function ($mail) {
        return $mail->hasTo('resetmail@example.com');
    });
});
