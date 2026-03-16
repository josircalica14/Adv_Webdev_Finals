<?php

namespace App\Services;

use App\Exceptions\FileTooLargeException;
use App\Exceptions\InvalidMimeTypeException;
use App\Exceptions\MaliciousFileException;
use App\Exceptions\StorageQuotaExceededException;
use App\Models\File;
use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileStorageService
{
    private const MAX_FILE_SIZE   = 10 * 1024 * 1024;  // 10 MB
    private const QUOTA_BYTES     = 100 * 1024 * 1024; // 100 MB
    private const THUMB_MAX       = 300;
    private const COMPRESS_MAX    = 2000;

    private const ALLOWED_MIMES = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf',
    ];

    public function upload(UploadedFile $file, User $user, PortfolioItem $item): File
    {
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new FileTooLargeException('File exceeds 10 MB limit.');
        }

        if (!$this->validateMimeType($file)) {
            throw new InvalidMimeTypeException('Unsupported file type.');
        }

        if ($this->scanForMalware($file)) {
            throw new MaliciousFileException('File contains potentially malicious content.');
        }

        $usage = $this->getUserStorageUsage($user);
        if ($usage + $file->getSize() > self::QUOTA_BYTES) {
            throw new StorageQuotaExceededException('Storage quota of 100 MB exceeded.');
        }

        $storedName = now()->format('YmdHis') . '_' . Str::uuid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $storedName, 'portfolio');

        $thumbnailPath = null;
        if (str_starts_with($file->getMimeType(), 'image/')) {
            try {
                $thumbnailPath = $this->generateThumbnail($path);
                $this->compressImage($path);
            } catch (\Throwable $e) {
                // GD not available or image processing failed — continue without thumbnail
                \Illuminate\Support\Facades\Log::warning('Image processing failed', ['error' => $e->getMessage()]);
            }
        }

        return File::create([
            'portfolio_item_id' => $item->id,
            'user_id'           => $user->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename'   => $storedName,
            'file_path'         => $path,
            'file_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'thumbnail_path'    => $thumbnailPath,
        ]);
    }

    public function delete(File $file): void
    {
        Storage::disk('portfolio')->delete($file->file_path);
        if ($file->thumbnail_path) {
            Storage::disk('portfolio')->delete($file->thumbnail_path);
        }
        $file->delete();
    }

    public function deleteForItem(PortfolioItem $item): void
    {
        foreach ($item->files as $file) {
            $this->delete($file);
        }
    }

    public function getUserStorageUsage(User $user): int
    {
        return (int) File::where('user_id', $user->id)->sum('file_size');
    }

    public function generateThumbnail(string $storedPath): string
    {
        $fullPath = Storage::disk('portfolio')->path($storedPath);
        [$width, $height] = getimagesize($fullPath);

        $scale = min(self::THUMB_MAX / $width, self::THUMB_MAX / $height, 1.0);
        $newW  = (int) round($width * $scale);
        $newH  = (int) round($height * $scale);

        $src   = $this->loadImage($fullPath);
        $thumb = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);

        $thumbName = 'thumbs/' . pathinfo($storedPath, PATHINFO_FILENAME) . '_thumb.' . pathinfo($storedPath, PATHINFO_EXTENSION);
        $thumbFull = Storage::disk('portfolio')->path($thumbName);
        @mkdir(dirname($thumbFull), 0755, true);
        $this->saveImage($thumb, $thumbFull, $fullPath);

        imagedestroy($src);
        imagedestroy($thumb);

        return $thumbName;
    }

    public function compressImage(string $storedPath): void
    {
        $fullPath = Storage::disk('portfolio')->path($storedPath);
        [$width, $height] = getimagesize($fullPath);

        if (max($width, $height) <= self::COMPRESS_MAX) {
            return;
        }

        $scale = self::COMPRESS_MAX / max($width, $height);
        $newW  = (int) round($width * $scale);
        $newH  = (int) round($height * $scale);

        $src  = $this->loadImage($fullPath);
        $dest = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dest, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        $this->saveImage($dest, $fullPath, $fullPath);

        imagedestroy($src);
        imagedestroy($dest);
    }

    public function validateMimeType(UploadedFile $file): bool
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file->getRealPath());
        return in_array($mime, self::ALLOWED_MIMES, true);
    }

    public function scanForMalware(UploadedFile $file): bool
    {
        $content = file_get_contents($file->getRealPath());
        $patterns = ['<?php', '<?=', '<script', 'eval(', 'base64_decode(', 'exec(', 'system(', 'passthru('];
        foreach ($patterns as $pattern) {
            if (stripos($content, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }

    private function loadImage(string $path): \GdImage
    {
        $mime = mime_content_type($path);
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif'  => imagecreatefromgif($path),
            default      => throw new InvalidMimeTypeException("Cannot process image type: {$mime}"),
        };
    }

    private function saveImage(\GdImage $image, string $path, string $originalPath): void
    {
        $mime = mime_content_type($originalPath);
        match ($mime) {
            'image/jpeg' => imagejpeg($image, $path, 85),
            'image/png'  => imagepng($image, $path, 8),
            'image/webp' => imagewebp($image, $path, 85),
            'image/gif'  => imagegif($image, $path),
            default      => imagejpeg($image, $path, 85),
        };
    }
}
