<?php

// Properties 8–11: Profile

use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Property 8: Profile update always persists all provided fields
it('P8: profile update persists all provided fields', function () {
    $user = User::factory()->create();
    $service = new ProfileService();

    $service->updateProfile($user, [
        'full_name' => 'Updated Name',
        'bio'       => 'My bio',
        'program'   => 'CSE',
    ]);

    $fresh = $user->fresh();
    expect($fresh->full_name)->toBe('Updated Name')
        ->and($fresh->bio)->toBe('My bio')
        ->and($fresh->program)->toBe('CSE');
});

// Property 9: Profile photo upload stores file and updates path
it('P9: profile photo upload stores file and updates profile_photo_path', function () {
    Storage::fake('portfolio');
    $user = User::factory()->create();

    // 1x1 pixel JPEG — valid binary, no GD required
    $jpegData = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AJQAB/9k=');
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.jpg';
    file_put_contents($tmpPath, $jpegData);
    $file = new UploadedFile($tmpPath, 'avatar.jpg', 'image/jpeg', null, true);

    $response = $this->actingAs($user)
        ->post(route('dashboard.profile.photo'), ['photo' => $file]);

    $response->assertRedirect();
    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

// Property 10: Invalid photo types are rejected
it('P10: invalid photo types are rejected', function () {
    Storage::fake('portfolio');
    $user = User::factory()->create();

    $file = UploadedFile::fake()->create('script.php', 100, 'application/x-php');

    $response = $this->actingAs($user)
        ->post(route('dashboard.profile.photo'), ['photo' => $file]);

    $response->assertSessionHasErrors('photo');
});

// Property 11: Username must be unique across all users
it('P11: username uniqueness is enforced', function () {
    $existing = User::factory()->create(['username' => 'takenname']);
    $user = User::factory()->create(['last_username_change' => now()->subDays(31)]);

    $response = $this->actingAs($user)
        ->put(route('dashboard.profile.username'), ['username' => 'takenname']);

    $response->assertSessionHasErrors('username');
});
