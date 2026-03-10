<?php
/**
 * InputSanitizer Class
 * 
 * Provides input sanitization and validation to prevent security vulnerabilities
 * including SQL injection, XSS attacks, and other malicious input patterns.
 * 
 * Note: This class provides defense-in-depth. Primary SQL injection prevention
 * should always use prepared statements with parameterized queries.
 */

require_once __DIR__ . '/../SecurityLogger.php';

class InputSanitizer {
    private SecurityLogger $securityLogger;
    
    public function __construct() {
        $this->securityLogger = SecurityLogger::getInstance();
    }
    
    /**
     * Sanitize string input to prevent XSS attacks
     * Removes or encodes potentially dangerous HTML/JavaScript
     * 
     * @param string $input The input string to sanitize
     * @param bool $allowHtml Whether to allow safe HTML tags (default: false)
     * @return string Sanitized string
     */
    public function sanitizeString(string $input, bool $allowHtml = false): string {
        if ($allowHtml) {
            // Allow only safe HTML tags
            $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6>';
            $sanitized = strip_tags($input, $allowedTags);
            
            // Additional XSS prevention for allowed tags
            $sanitized = $this->removeXSSPatterns($sanitized);
        } else {
            // Remove all HTML tags
            $sanitized = strip_tags($input);
        }
        
        // Trim whitespace
        $sanitized = trim($sanitized);
        
        return $sanitized;
    }
    
    /**
     * Escape output for safe HTML display
     * Use this when displaying user-generated content in HTML
     * 
     * @param string $output The output string to escape
     * @return string Escaped string safe for HTML output
     */
    public function escapeHtml(string $output): string {
        return htmlspecialchars($output, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitize email address
     * 
     * @param string $email Email address to sanitize
     * @return string|null Sanitized email or null if invalid
     */
    public function sanitizeEmail(string $email): ?string {
        $email = trim($email);
        $sanitized = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        // Validate after sanitization
        if (filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            return strtolower($sanitized);
        }
        
        return null;
    }
    
    /**
     * Sanitize URL
     * 
     * @param string $url URL to sanitize
     * @return string|null Sanitized URL or null if invalid
     */
    public function sanitizeUrl(string $url): ?string {
        $url = trim($url);
        $sanitized = filter_var($url, FILTER_SANITIZE_URL);
        
        // Validate after sanitization
        if (filter_var($sanitized, FILTER_VALIDATE_URL)) {
            // Additional check: only allow http and https protocols
            $parsed = parse_url($sanitized);
            if (isset($parsed['scheme']) && in_array($parsed['scheme'], ['http', 'https'])) {
                return $sanitized;
            }
        }
        
        return null;
    }
    
    /**
     * Sanitize integer input
     * 
     * @param mixed $input Input to sanitize
     * @return int|null Sanitized integer or null if invalid
     */
    public function sanitizeInt($input): ?int {
        $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        
        if (filter_var($sanitized, FILTER_VALIDATE_INT) !== false) {
            return (int)$sanitized;
        }
        
        return null;
    }
    
    /**
     * Sanitize float input
     * 
     * @param mixed $input Input to sanitize
     * @return float|null Sanitized float or null if invalid
     */
    public function sanitizeFloat($input): ?float {
        $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        
        if (filter_var($sanitized, FILTER_VALIDATE_FLOAT) !== false) {
            return (float)$sanitized;
        }
        
        return null;
    }
    
    /**
     * Sanitize filename to prevent directory traversal attacks
     * 
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    public function sanitizeFilename(string $filename): string {
        // Remove directory separators and null bytes
        $sanitized = str_replace(['/', '\\', "\0"], '', $filename);
        
        // Remove leading dots to prevent hidden files
        $sanitized = ltrim($sanitized, '.');
        
        // Remove any remaining dangerous characters
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '_', $sanitized);
        
        // Ensure filename is not empty
        if (empty($sanitized)) {
            $sanitized = 'file_' . time();
        }
        
        return $sanitized;
    }
    
    /**
     * Detect and log potential SQL injection patterns
     * Note: This is for logging/monitoring only. Always use prepared statements!
     * 
     * @param string $input Input to check
     * @return bool True if suspicious patterns detected
     */
    public function detectSQLInjection(string $input): bool {
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bCREATE\b.*\bTABLE\b)/i',
            '/(\'|\")(\s*)(OR|AND)(\s*)(\'|\")(\s*)=(\s*)(\'|\")/i',
            '/(\bOR\b.*=.*)/i',
            '/(--|\#|\/\*)/i', // SQL comments
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bxp_cmdshell\b)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $endpoint = $_SERVER['REQUEST_URI'] ?? 'unknown';
                
                Logger::getInstance()->warning('Potential SQL injection attempt detected', [
                    'input' => substr($input, 0, 100),
                    'pattern' => $pattern,
                    'ip_address' => $ipAddress
                ]);
                
                // Log security event
                $this->securityLogger->logSQLInjectionAttempt($input, $ipAddress, $endpoint);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Remove XSS patterns from input
     * 
     * @param string $input Input to clean
     * @return string Cleaned input
     */
    private function removeXSSPatterns(string $input): string {
        // Remove javascript: protocol
        $input = preg_replace('/javascript:/i', '', $input);
        
        // Remove on* event handlers
        $input = preg_replace('/\bon\w+\s*=/i', '', $input);
        
        // Remove data: protocol (can be used for XSS)
        $input = preg_replace('/data:text\/html/i', '', $input);
        
        // Remove vbscript: protocol
        $input = preg_replace('/vbscript:/i', '', $input);
        
        return $input;
    }
    
    /**
     * Detect potential XSS patterns
     * 
     * @param string $input Input to check
     * @return bool True if suspicious patterns detected
     */
    public function detectXSS(string $input): bool {
        $patterns = [
            '/<script\b[^>]*>/i',
            '/<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i', // Event handlers like onclick, onload
            '/<iframe\b[^>]*>/i',
            '/<object\b[^>]*>/i',
            '/<embed\b[^>]*>/i',
            '/<applet\b[^>]*>/i',
            '/vbscript:/i',
            '/data:text\/html/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $endpoint = $_SERVER['REQUEST_URI'] ?? 'unknown';
                
                Logger::getInstance()->warning('Potential XSS attempt detected', [
                    'input' => substr($input, 0, 100),
                    'pattern' => $pattern,
                    'ip_address' => $ipAddress
                ]);
                
                // Log security event
                $this->securityLogger->logXSSAttempt($input, $ipAddress, $endpoint);
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize array of inputs recursively
     * 
     * @param array $data Array of data to sanitize
     * @param bool $allowHtml Whether to allow HTML in string values
     * @return array Sanitized array
     */
    public function sanitizeArray(array $data, bool $allowHtml = false): array {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Sanitize the key
            $cleanKey = $this->sanitizeString($key, false);
            
            // Sanitize the value based on type
            if (is_array($value)) {
                $sanitized[$cleanKey] = $this->sanitizeArray($value, $allowHtml);
            } elseif (is_string($value)) {
                $sanitized[$cleanKey] = $this->sanitizeString($value, $allowHtml);
            } else {
                $sanitized[$cleanKey] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Validate and sanitize username
     * 
     * @param string $username Username to sanitize
     * @return string|null Sanitized username or null if invalid
     */
    public function sanitizeUsername(string $username): ?string {
        $username = trim($username);
        
        // Only allow alphanumeric, hyphens, and underscores
        if (preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            return strtolower($username);
        }
        
        return null;
    }
    
    /**
     * Sanitize JSON input
     * 
     * @param string $json JSON string to sanitize
     * @return array|null Decoded and sanitized array or null if invalid
     */
    public function sanitizeJson(string $json): ?array {
        $decoded = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::getInstance()->warning('Invalid JSON input', [
                'error' => json_last_error_msg()
            ]);
            return null;
        }
        
        if (is_array($decoded)) {
            return $this->sanitizeArray($decoded);
        }
        
        return null;
    }
}
