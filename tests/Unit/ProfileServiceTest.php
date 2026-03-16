<?php

use App\Models\User;
use App\Services\ProfileService;
use Carbon\Carbon;

beforeEach(function () {
    $this->service = new ProfileService();
});

it('returns 0 when user has never changed username', function () {
    $user = new User(['last_username_change' => null]);
    expect($this->service->getDaysUntilUsernameChange($user))->toBe(0);
});

it('returns 0 when exactly 30 days have passed', function () {
    $user = new User(['last_username_change' => now()->subDays(30)]);
    expect($this->service->getDaysUntilUsernameChange($user))->toBe(0);
});

it('returns 0 when more than 30 days have passed', function () {
    $user = new User(['last_username_change' => now()->subDays(31)]);
    expect($this->service->getDaysUntilUsernameChange($user))->toBe(0);
});

it('returns positive days when fewer than 30 days have passed', function () {
    $user = new User(['last_username_change' => now()->subDays(20)]);
    $days = $this->service->getDaysUntilUsernameChange($user);
    expect($days)->toBeGreaterThan(0)->toBeLessThanOrEqual(10);
});

it('returns 1 when 29 days have passed (boundary)', function () {
    $user = new User(['last_username_change' => now()->subDays(29)]);
    $days = $this->service->getDaysUntilUsernameChange($user);
    expect($days)->toBe(1);
});

it('returns 30 when changed just now', function () {
    $user = new User(['last_username_change' => now()]);
    $days = $this->service->getDaysUntilUsernameChange($user);
    expect($days)->toBe(30);
});
