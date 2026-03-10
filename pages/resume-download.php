<?php
/**
 * Resume Download Handler
 * Securely serves the resume PDF file with download tracking
 */

// Start session for potential user tracking
session_start();

// Configuration
define('RESUME_FILE', 'resume.pdf');
define('RESUME_DIR', __DIR__ . '/assets/');
define('DOWNLOAD_LOG', __DIR__ . '/data/resume-downloads.txt');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB in bytes

/**
 * Sanitize filename to prevent directory traversal attacks
 */
function sanitizeFilename($filename) {
    // Remove any path components
    $filename = basename($filename);
    // Remove any non-alphanumeric characters except dots and hyphens
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

/**
 * Log download event
 */
function logDownload() {
    $logDir = dirname(DOWNLOAD_LOG);
    
    // Create data directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = sprintf(
        "[%s] IP: %s | User-Agent: %s\n",
        $timestamp,
        $ipAddress,
        $userAgent
    );
    
    // Append to log file
    file_put_contents(DOWNLOAD_LOG, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Update download counter
    $counterFile = $logDir . '/resume-download-count.txt';
    $count = file_exists($counterFile) ? (int)file_get_contents($counterFile) : 0;
    $count++;
    file_put_contents($counterFile, $count, LOCK_EX);
}

/**
 * Serve the resume file for download
 */
function serveResume($filePath, $filename) {
    // Validate file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('Resume file not found. Please contact the site administrator.');
    }
    
    // Validate file size
    $fileSize = filesize($filePath);
    if ($fileSize > MAX_FILE_SIZE) {
        http_response_code(413);
        die('Resume file is too large.');
    }
    
    // Validate it's a PDF file
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    if ($mimeType !== 'application/pdf') {
        http_response_code(400);
        die('Invalid file type. Only PDF files are allowed.');
    }
    
    // Log the download
    logDownload();
    
    // Clear any output buffers
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
    
    // Read and output the file
    readfile($filePath);
    exit;
}

// Main execution
try {
    // Sanitize the resume filename
    $safeFilename = sanitizeFilename(RESUME_FILE);
    
    // Construct full file path
    $filePath = RESUME_DIR . $safeFilename;
    
    // Security check: Ensure the resolved path is within the assets directory
    $realPath = realpath($filePath);
    $realAssetsDir = realpath(RESUME_DIR);
    
    if ($realPath === false || strpos($realPath, $realAssetsDir) !== 0) {
        http_response_code(403);
        die('Access denied.');
    }
    
    // Generate descriptive filename for download
    $downloadFilename = 'BSIT_Student_Resume.pdf';
    
    // Serve the file
    serveResume($realPath, $downloadFilename);
    
} catch (Exception $e) {
    error_log('Resume download error: ' . $e->getMessage());
    http_response_code(500);
    die('An error occurred while processing your request.');
}
