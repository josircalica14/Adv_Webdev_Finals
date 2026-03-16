<?php

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('portfolio');
});

// Helper: create a minimal real JPEG binary file that passes finfo and getimagesize
function fakeJpeg(string $name = 'photo.jpg'): UploadedFile
{
    // 1x1 pixel JPEG (valid, parseable by getimagesize)
    $jpegData = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFAABAAAAAAAAAAAAAAAAAAAACf/EABQQAQAAAAAAAAAAAAAAAAAAAAD/xAAUAQEAAAAAAAAAAAAAAAAAAAAA/8QAFBEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AJQAB/9k=');
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.jpg';
    file_put_contents($tmpPath, $jpegData);
    return new UploadedFile($tmpPath, $name, 'image/jpeg', null, true);
}

it('uploads a valid image file', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    $file = fakeJpeg();

    $response = $this->actingAs($user)
        ->post(route('dashboard.files.store', $item), ['file' => $file]);

    $response->assertRedirect();
    expect(File::where('portfolio_item_id', $item->id)->exists())->toBeTrue();
});

it('rejects files over 10MB', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    // Create a fake file larger than 10MB (11264 KB = 11MB)
    $file = UploadedFile::fake()->create('large.jpg', 11264, 'image/jpeg');

    $response = $this->actingAs($user)
        ->post(route('dashboard.files.store', $item), ['file' => $file]);

    $response->assertSessionHasErrors('file');
});

it('enforces storage quota', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    // Simulate user already at quota by creating file records totaling 100MB
    File::factory()->create([
        'user_id'           => $user->id,
        'portfolio_item_id' => $item->id,
        'file_size'         => 100 * 1024 * 1024,
    ]);

    $file = fakeJpeg('extra.jpg');

    $response = $this->actingAs($user)
        ->post(route('dashboard.files.store', $item), ['file' => $file]);

    $response->assertSessionHasErrors('file');
});
