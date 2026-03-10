<?php

/**
 * FileStorageManager
 * 
 * Manages file uploads, storage, validation, and deletion for portfolio items.
 * Implements secure file handling with type validation, size limits, unique naming,
 * thumbnail generation, and malware scanning.
 */
class FileStorageManager {
    private PDO $db;
    private array $config;
    private string $uploadPath;
    private string $thumbnailPath;
    private array $allowedTypes;
    private int $maxFileSize;
    private int $maxFilesPerItem;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     * @param array $config Application configuration
     */
    public function __construct(PDO $db, array $config) {
        $this->db = $db;
        $this->config = $config;
        
        // Set up paths
        $this->uploadPath = $config['paths']['uploads'] ?? __DIR__ . '/../uploads';
        $this->thumbnailPath = $config['paths']['thumbnails'] ?? $this->uploadPath . '/thumbnails';
        
        // Set up file constraints
        $this->maxFileSize = $config['files']['max_file_size'] ?? 10485760; // 10MB default
        $this->maxFilesPerItem = $config['files']['max_files_per_item'] ?? 10;
        
        // Merge allowed types
        $allowedImages = $config['files']['allowed_image_types'] ?? [];
        $allowedDocs = $config['files']['allowed_document_types'] ?? [];
        $this->allowedTypes = array_merge($allowedImages, $allowedDocs);
        
        // Ensure directories exist
        $this->ensureDirectoriesExist();
    }
    
    /**
     * Upload a file for a portfolio item
     * 
     * @param array $file Uploaded file from $_FILES
     * @param int $userId User ID who owns the file
     * @param int $itemId Portfolio item ID to associate with
     * @return array Result with success status and file record or error
     */
    public function uploadFile(array $file, int $userId, int $itemId): array {
        // Validate file
        $validation = $this->validateFile($file);
        if (!$validation['isValid']) {
            return [
                'success' => false,
                'error' => $validation['errors'][0] ?? 'File validation failed'
            ];
        }
        
        // Check file count limit for this item
        $currentCount = $this->getFileCountForItem($itemId);
        if ($currentCount >= $this->maxFilesPerItem) {
            return [
                'success' => false,
                'error' => "Maximum {$this->maxFilesPerItem} files allowed per portfolio item"
            ];
        }
        
        // Generate unique filename
        $originalFilename = basename($file['name']);
        $storedFilename = $this->generateUniqueFilename($originalFilename);
        $filePath = $this->uploadPath . '/' . $storedFilename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'success' => false,
                'error' => 'Failed to save uploaded file'
            ];
        }
        
        // Compress image if it's an image file
        if ($this->isImage($file['type'])) {
            $this->compressImage($filePath, $file['type']);
        }
        
        // Scan for malware
        if (!$this->scanForMalware($filePath)) {
            unlink($filePath);
            return [
                'success' => false,
                'error' => 'File failed security scan'
            ];
        }
        
        // Generate thumbnail for images
        $thumbnailPath = null;
        if ($this->isImage($file['type'])) {
            $thumbnailPath = $this->generateThumbnail($filePath, $storedFilename);
        }
        
        // Store file record in database
        try {
            $stmt = $this->db->prepare("
                INSERT INTO files (
                    portfolio_item_id, user_id, original_filename, 
                    stored_filename, file_path, file_type, file_size, thumbnail_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $itemId,
                $userId,
                $originalFilename,
                $storedFilename,
                $filePath,
                $file['type'],
                $file['size'],
                $thumbnailPath
            ]);
            
            $fileId = (int)$this->db->lastInsertId();
            
            return [
                'success' => true,
                'file' => [
                    'id' => $fileId,
                    'original_filename' => $originalFilename,
                    'stored_filename' => $storedFilename,
                    'file_path' => $filePath,
                    'file_type' => $file['type'],
                    'file_size' => $file['size'],
                    'thumbnail_path' => $thumbnailPath
                ]
            ];
            
        } catch (PDOException $e) {
            // Clean up uploaded file on database error
            unlink($filePath);
            if ($thumbnailPath && file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            return [
                'success' => false,
                'error' => 'Failed to save file record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a file
     * 
     * @param int $fileId File ID to delete
     * @param int $userId User ID (for access control)
     * @return bool True if deleted successfully
     */
    public function deleteFile(int $fileId, int $userId): bool {
        // Get file record
        $file = $this->getFile($fileId);
        if (!$file) {
            return false;
        }
        
        // Verify ownership
        if ($file['user_id'] !== $userId) {
            return false;
        }
        
        // Delete from database
        try {
            $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
            $stmt->execute([$fileId]);
            
            // Delete physical files
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            if ($file['thumbnail_path'] && file_exists($file['thumbnail_path'])) {
                unlink($file['thumbnail_path']);
            }
            
            return true;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get file record by ID
     * 
     * @param int $fileId File ID
     * @return array|null File record or null if not found
     */
    public function getFile(int $fileId): ?array {
        $stmt = $this->db->prepare("
            SELECT id, portfolio_item_id, user_id, original_filename, 
                   stored_filename, file_path, file_type, file_size, 
                   thumbnail_path, created_at
            FROM files 
            WHERE id = ?
        ");
        
        $stmt->execute([$fileId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $file ?: null;
    }
    
    /**
     * Get all files for a portfolio item
     * 
     * @param int $itemId Portfolio item ID
     * @return array Array of file records
     */
    public function getFilesForItem(int $itemId): array {
        $stmt = $this->db->prepare("
            SELECT id, portfolio_item_id, user_id, original_filename, 
                   stored_filename, file_path, file_type, file_size, 
                   thumbnail_path, created_at
            FROM files 
            WHERE portfolio_item_id = ?
            ORDER BY created_at ASC
        ");
        
        $stmt->execute([$itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete all files for a portfolio item (cascade delete)
     * 
     * @param int $itemId Portfolio item ID
     * @return bool True if all files deleted successfully
     */
    public function deleteFilesForItem(int $itemId): bool {
        $files = $this->getFilesForItem($itemId);
        
        foreach ($files as $file) {
            // Delete physical files
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            if ($file['thumbnail_path'] && file_exists($file['thumbnail_path'])) {
                unlink($file['thumbnail_path']);
            }
        }
        
        // Delete database records
        try {
            $stmt = $this->db->prepare("DELETE FROM files WHERE portfolio_item_id = ?");
            $stmt->execute([$itemId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file Uploaded file from $_FILES
     * @return array Validation result with isValid and errors
     */
    public function validateFile(array $file): array {
        $errors = [];
        
        // Check for upload errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $this->getUploadErrorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE);
        }
        
        // Check file size
        if (isset($file['size']) && $file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 1);
            $errors[] = "File size exceeds maximum allowed size of {$maxSizeMB}MB";
        }
        
        // Check file type
        if (isset($file['type']) && !in_array($file['type'], $this->allowedTypes)) {
            $errors[] = "File type '{$file['type']}' is not allowed. Allowed types: JPEG, PNG, WebP, GIF, PDF";
        }
        
        // Additional validation: check actual file type (not just MIME)
        if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actualType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($actualType, $this->allowedTypes)) {
                $errors[] = "File content does not match allowed types";
            }
        }
        
        return [
            'isValid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Generate thumbnail for image file
     * 
     * @param string $filePath Path to original image
     * @param string $storedFilename Stored filename
     * @return string|null Path to thumbnail or null on failure
     */
    public function generateThumbnail(string $filePath, string $storedFilename): ?string {
        $thumbnailFilename = 'thumb_' . $storedFilename;
        $thumbnailPath = $this->thumbnailPath . '/' . $thumbnailFilename;
        
        // Get image info
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return null;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Create image resource based on type
        $source = null;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = @imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = @imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = @imagecreatefromwebp($filePath);
                break;
            case IMAGETYPE_GIF:
                $source = @imagecreatefromgif($filePath);
                break;
        }
        
        if (!$source) {
            return null;
        }
        
        // Calculate thumbnail dimensions (max 300x300, maintain aspect ratio)
        $maxDimension = 300;
        if ($width > $height) {
            $thumbWidth = $maxDimension;
            $thumbHeight = (int)(($height / $width) * $maxDimension);
        } else {
            $thumbHeight = $maxDimension;
            $thumbWidth = (int)(($width / $height) * $maxDimension);
        }
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
        
        // Save thumbnail
        $success = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($thumbnail, $thumbnailPath, 85);
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($thumbnail, $thumbnailPath, 8);
                break;
            case IMAGETYPE_WEBP:
                $success = imagewebp($thumbnail, $thumbnailPath, 85);
                break;
            case IMAGETYPE_GIF:
                $success = imagegif($thumbnail, $thumbnailPath);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return $success ? $thumbnailPath : null;
    }

    /**
     * Scan file for malware
     * 
     * Basic implementation using file signature checking.
     * In production, integrate with ClamAV or similar antivirus solution.
     * 
     * @param string $filePath Path to file to scan
     * @return bool True if file is safe, false if malware detected
     */
    private function scanForMalware(string $filePath): bool {
        // Basic check: look for suspicious patterns in file content
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        // Read first 8KB for signature checking
        $content = fread($handle, 8192);
        fclose($handle);
        
        // Check for common malware signatures
        $suspiciousPatterns = [
            '/eval\s*\(/i',                    // PHP eval
            '/base64_decode\s*\(/i',           // Base64 decode (often used in malware)
            '/system\s*\(/i',                  // System calls
            '/exec\s*\(/i',                    // Exec calls
            '/shell_exec\s*\(/i',              // Shell exec
            '/passthru\s*\(/i',                // Passthru
            '/<\?php.*eval/is',                // PHP eval in code
            '/\$_(?:GET|POST|REQUEST|COOKIE)\[.*\]\s*\(/i', // Variable function calls
        ];
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return false;
            }
        }
        
        // TODO: In production, integrate with ClamAV:
        // exec("clamscan --no-summary " . escapeshellarg($filePath), $output, $returnCode);
        // return $returnCode === 0;
        
        return true;
    }
    
    /**
     * Generate unique filename to prevent collisions
     * 
     * @param string $originalFilename Original filename
     * @return string Unique filename
     */
    private function generateUniqueFilename(string $originalFilename): string {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        
        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        // Generate unique identifier
        $uniqueId = uniqid('', true);
        $timestamp = time();
        
        // Combine: timestamp_uniqueid_basename.ext
        return "{$timestamp}_{$uniqueId}_{$basename}.{$extension}";
    }
    
    /**
     * Check if file type is an image
     * 
     * @param string $mimeType MIME type
     * @return bool True if image
     */
    private function isImage(string $mimeType): bool {
        return strpos($mimeType, 'image/') === 0;
    }
    
    /**
     * Get file count for a portfolio item
     * 
     * @param int $itemId Portfolio item ID
     * @return int Number of files
     */
    private function getFileCountForItem(int $itemId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM files WHERE portfolio_item_id = ?");
        $stmt->execute([$itemId]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get user-friendly upload error message
     * 
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage(int $errorCode): string {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File size exceeds maximum allowed size';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectoriesExist(): void {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
        
        if (!is_dir($this->thumbnailPath)) {
            mkdir($this->thumbnailPath, 0755, true);
        }
    }
    
    /**
     * Get storage usage for a user
     * 
     * @param int $userId User ID
     * @return int Total bytes used
     */
    public function getUserStorageUsage(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(file_size), 0) as total_size
            FROM files
            WHERE user_id = ?
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)($result['total_size'] ?? 0);
    }
    
    /**
     * Check if user has exceeded storage quota
     * 
     * @param int $userId User ID
     * @param int $additionalSize Size of file to be uploaded
     * @return bool True if within quota
     */
    public function checkStorageQuota(int $userId, int $additionalSize): bool {
        // Default quota: 100MB per user
        $quota = $this->config['files']['storage_quota'] ?? 104857600;
        
        $currentUsage = $this->getUserStorageUsage($userId);
        
        return ($currentUsage + $additionalSize) <= $quota;
    }
    
    /**
     * Get file extension from MIME type
     * 
     * @param string $mimeType MIME type
     * @return string File extension
     */
    public function getExtensionFromMimeType(string $mimeType): string {
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
        ];
        
        return $mimeMap[$mimeType] ?? 'bin';
    }
    
    /**
     * Compress image file to optimize for web
     * 
     * @param string $filePath Path to image file
     * @param string $mimeType MIME type of image
     * @return bool True if compressed successfully
     */
    private function compressImage(string $filePath, string $mimeType): bool {
        // Get image info
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        list($width, $height, $type) = $imageInfo;
        
        // Create image resource based on type
        $source = null;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = @imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_PNG:
                $source = @imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_WEBP:
                $source = @imagecreatefromwebp($filePath);
                break;
            case IMAGETYPE_GIF:
                // Don't compress GIFs (may be animated)
                return true;
        }
        
        if (!$source) {
            return false;
        }
        
        // Resize if image is too large (max 2000px on longest side)
        $maxDimension = 2000;
        $needsResize = $width > $maxDimension || $height > $maxDimension;
        
        if ($needsResize) {
            if ($width > $height) {
                $newWidth = $maxDimension;
                $newHeight = (int)(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = (int)(($width / $height) * $maxDimension);
            }
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }
        
        // Save compressed image
        $success = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $success = imagejpeg($source, $filePath, 85); // 85% quality
                break;
            case IMAGETYPE_PNG:
                $success = imagepng($source, $filePath, 8); // Compression level 8
                break;
            case IMAGETYPE_WEBP:
                $success = imagewebp($source, $filePath, 85); // 85% quality
                break;
        }
        
        imagedestroy($source);
        
        return $success;
    }
}
