<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    Storage::fake('portfolio');
});

it('allows up to 20 uploads before blocking', function () {
    $user = User::factory()->create();
    $key = "upload:{$user->id}";
    RateLimiter::clear($key);

    // Hit 20 times
    for ($i = 0; $i < 20; $i++) {
        RateLimiter::hit($key, 3600);
    }

    expect(RateLimiter::tooManyAttempts($key, 20))->toBeTrue();
    expect(RateLimiter::remaining($key, 20))->toBe(0);
});

it('blocks the 21st upload attempt with 429', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    $key = "upload:{$user->id}";
    RateLimiter::clear($key);

    // Exhaust the rate limit
    for ($i = 0; $i < 20; $i++) {
        RateLimiter::hit($key, 3600);
    }

    $jpegData = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9";
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.jpg';
    file_put_contents($tmpPath, $jpegData);
    $file = new \Illuminate\Http\UploadedFile($tmpPath, 'photo.jpg', 'image/jpeg', null, true);

    $response = $this->actingAs($user)
        ->post(route('dashboard.files.store', $item), ['file' => $file]);

    $response->assertStatus(429);
});

it('rate limit resets after the window', function () {
    $user = User::factory()->create();
    $key = "upload:{$user->id}";

    // Exhaust limit
    for ($i = 0; $i < 20; $i++) {
        RateLimiter::hit($key, 3600);
    }

    expect(RateLimiter::tooManyAttempts($key, 20))->toBeTrue();

    // Clear simulates window expiry
    RateLimiter::clear($key);
    expect(RateLimiter::tooManyAttempts($key, 20))->toBeFalse();
});
