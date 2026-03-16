<?php

use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->service = new FileStorageService();
});

// --- validateMimeType ---

it('accepts a real JPEG file regardless of extension', function () {
    // Minimal valid JPEG binary (SOI + EOI markers)
    $jpegData = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9";
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.png'; // wrong extension
    file_put_contents($tmpPath, $jpegData);

    $file = new UploadedFile($tmpPath, 'photo.png', 'image/png', null, true);
    // finfo reads actual content — it's a JPEG, so mime is image/jpeg
    expect($this->service->validateMimeType($file))->toBeTrue();
    unlink($tmpPath);
});

it('rejects a PHP file disguised as an image', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.jpg';
    file_put_contents($tmpPath, '<?php echo "evil"; ?>');

    $file = new UploadedFile($tmpPath, 'photo.jpg', 'image/jpeg', null, true);
    expect($this->service->validateMimeType($file))->toBeFalse();
    unlink($tmpPath);
});

it('accepts a real PNG file', function () {
    // Minimal valid PNG binary (PNG signature + IHDR + IDAT + IEND)
    $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.png';
    file_put_contents($tmpPath, $pngData);

    $file = new UploadedFile($tmpPath, 'image.png', 'image/png', null, true);
    expect($this->service->validateMimeType($file))->toBeTrue();
    unlink($tmpPath);
});

// --- scanForMalware ---

it('detects PHP opening tag', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, '<?php system("rm -rf /"); ?>');
    $file = new UploadedFile($tmpPath, 'evil.php', 'text/plain', null, true);
    expect($this->service->scanForMalware($file))->toBeTrue();
    unlink($tmpPath);
});

it('detects eval() pattern', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, 'some content eval(base64_decode("...")) more');
    $file = new UploadedFile($tmpPath, 'file.txt', 'text/plain', null, true);
    expect($this->service->scanForMalware($file))->toBeTrue();
    unlink($tmpPath);
});

it('detects script tag', function () {
    $tmpPath = tempnam(sys_get_temp_dir(), 'test');
    file_put_contents($tmpPath, '<html><script>alert(1)</script></html>');
    $file = new UploadedFile($tmpPath, 'file.html', 'text/html', null, true);
    expect($this->service->scanForMalware($file))->toBeTrue();
    unlink($tmpPath);
});

it('passes clean image content', function () {
    $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    $tmpPath = tempnam(sys_get_temp_dir(), 'test') . '.png';
    file_put_contents($tmpPath, $pngData);

    $file = new UploadedFile($tmpPath, 'clean.png', 'image/png', null, true);
    expect($this->service->scanForMalware($file))->toBeFalse();
    unlink($tmpPath);
});
