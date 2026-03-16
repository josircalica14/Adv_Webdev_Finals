<?php

// Properties 28–30: File Storage

use App\Models\User;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\File;
use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('portfolio');
});

// Property 28: Generated thumbnail longest side is ≤ 300px
it('P28: thumbnail longest side is at most 300px', function () {
    if (!function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension not available');
    }

    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    // Create a real 600x400 image in the fake storage
    $img = imagecreatetruecolor(600, 400);
    $tmpPath = sys_get_temp_dir() . '/test_thumb.jpg';
    imagejpeg($img, $tmpPath);
    imagedestroy($img);

    Storage::disk('portfolio')->put('uploads/test_thumb.jpg', file_get_contents($tmpPath));
    unlink($tmpPath);

    $service = new FileStorageService();
    $thumbPath = $service->generateThumbnail('uploads/test_thumb.jpg');

    $thumbFull = Storage::disk('portfolio')->path($thumbPath);
    [$w, $h] = getimagesize($thumbFull);

    expect(max($w, $h))->toBeLessThanOrEqual(300);
});

// Property 29: Compressed image longest side is ≤ 2000px
it('P29: compressed image longest side is at most 2000px', function () {
    if (!function_exists('imagecreatetruecolor')) {
        $this->markTestSkipped('GD extension not available');
    }

    $img = imagecreatetruecolor(3000, 2500);
    $tmpPath = sys_get_temp_dir() . '/test_compress.jpg';
    imagejpeg($img, $tmpPath);
    imagedestroy($img);

    Storage::disk('portfolio')->put('uploads/test_compress.jpg', file_get_contents($tmpPath));
    unlink($tmpPath);

    $service = new FileStorageService();
    $service->compressImage('uploads/test_compress.jpg');

    $fullPath = Storage::disk('portfolio')->path('uploads/test_compress.jpg');
    [$w, $h] = getimagesize($fullPath);

    expect(max($w, $h))->toBeLessThanOrEqual(2000);
});

// Property 30: Deleting a file removes both the file and its thumbnail
it('P30: deleting a file removes file and thumbnail from storage', function () {
    $user = User::factory()->create();
    $portfolio = Portfolio::factory()->create(['user_id' => $user->id]);
    $item = PortfolioItem::factory()->create(['portfolio_id' => $portfolio->id, 'item_type' => 'project']);

    Storage::disk('portfolio')->put('uploads/test.jpg', 'fake content');
    Storage::disk('portfolio')->put('thumbs/test_thumb.jpg', 'fake thumb');

    $file = File::factory()->create([
        'user_id'           => $user->id,
        'portfolio_item_id' => $item->id,
        'file_path'         => 'uploads/test.jpg',
        'thumbnail_path'    => 'thumbs/test_thumb.jpg',
    ]);

    $service = new FileStorageService();
    $service->delete($file);

    Storage::disk('portfolio')->assertMissing('uploads/test.jpg');
    Storage::disk('portfolio')->assertMissing('thumbs/test_thumb.jpg');
    expect(File::find($file->id))->toBeNull();
});
