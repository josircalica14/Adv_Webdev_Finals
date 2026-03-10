<?php
/**
 * CSRFProtection Class
 * 
 * Implements Cross-Site Request Forgery (CSRF) protection through token
 * generation and validation. Tokens are stored in the session and must
 * be included in all state-changing operations (POST, PUT, DELETE).
 */

require_once __DIR__ . '/../SecurityLogger.php';

class CSRFProtection {
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;
    private SecurityLogger $securityLogger;
    
    public function __construct() {
        $this->securityLogger = SecurityLogger::getInstance();
    }
    
    /**
     * Generate a new CSRF token and store it in the session
     * 
     * @return string The generated CSRF token
     */
    public function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        try {
            // Generate cryptographically secure random token
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
            
            // Store token in session
            $_SESSION[self::TOKEN_NAME] = $token;
            
            Logger::getInstance()->debug('CSRF token generated');
            
            return $token;
            
        } catch (Exception $e) {
            Logger::getInstance()->error('Failed to generate CSRF token', [
                'error' => $e->getMessage()
            ]);
            throw new Exception('Failed to generate CSRF token');
        }
    }
    
    /**
     * Get the current CSRF token from the session
     * If no token exists, generate a new one
     * 
     * @return string The CSRF token
     */
    public function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::TOKEN_NAME]) || empty($_SESSION[self::TOKEN_NAME])) {
            return $this->generateToken();
        }
        
        return $_SESSION[self::TOKEN_NAME];
    }
    
    /**
     * Validate a CSRF token against the session token
     * 
     * @param string $token The token to validate
     * @return bool True if token is valid, false otherwise
     */
    public function validateToken(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::TOKEN_NAME]) || empty($_SESSION[self::TOKEN_NAME])) {
            Logger::getInstance()->warning('CSRF validation failed: No token in session');
            return false;
        }
        
        $sessionToken = $_SESSION[self::TOKEN_NAME];
        
        // Use hash_equals to prevent timing attacks
        $isValid = hash_equals($sessionToken, $token);
        
        if (!$isValid) {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $endpoint = $_SERVER['REQUEST_URI'] ?? 'unknown';
            
            Logger::getInstance()->warning('CSRF validation failed: Token mismatch', [
                'provided_token' => substr($token, 0, 8) . '...',
                'ip_address' => $ipAddress
            ]);
            
            // Log security event
            $this->securityLogger->logCSRFViolation($ipAddress, $endpoint);
        } else {
            Logger::getInstance()->debug('CSRF token validated successfully');
        }
        
        return $isValid;
    }
    
    /**
     * Validate CSRF token from request
     * Checks POST data, headers, and query parameters
     * 
     * @return bool True if token is valid, false otherwise
     */
    public function validateRequest(): bool {
        $token = null;
        
        // Check POST data first
        if (isset($_POST[self::TOKEN_NAME])) {
            $token = $_POST[self::TOKEN_NAME];
        }
        // Check custom header (for AJAX requests)
        elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        // Check query parameter (fallback, less secure)
        elseif (isset($_GET[self::TOKEN_NAME])) {
            $token = $_GET[self::TOKEN_NAME];
        }
        
        if ($token === null) {
            Logger::getInstance()->warning('CSRF validation failed: No token provided in request');
            return false;
        }
        
        return $this->validateToken($token);
    }
    
    /**
     * Regenerate CSRF token
     * Should be called after successful login or other security-sensitive operations
     * 
     * @return string The new CSRF token
     */
    public function regenerateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Remove old token
        unset($_SESSION[self::TOKEN_NAME]);
        
        // Generate new token
        return $this->generateToken();
    }
    
    /**
     * Get HTML input field for CSRF token
     * Convenient method for including token in forms
     * 
     * @return string HTML hidden input field
     */
    public function getTokenField(): string {
        $token = $this->getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Get meta tag for CSRF token
     * Useful for including token in page head for AJAX requests
     * 
     * @return string HTML meta tag
     */
    public function getTokenMeta(): string {
        $token = $this->getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
    
    /**
     * Verify request method is state-changing and validate CSRF token
     * 
     * @param array $allowedMethods Methods that require CSRF validation (default: POST, PUT, DELETE, PATCH)
     * @return bool True if method doesn't require validation or token is valid
     */
    public function verifyRequest(array $allowedMethods = ['POST', 'PUT', 'DELETE', 'PATCH']): bool {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // If method doesn't require CSRF validation, allow it
        if (!in_array($method, $allowedMethods)) {
            return true;
        }
        
        // Validate CSRF token for state-changing methods
        return $this->validateRequest();
    }
    
    /**
     * Clear CSRF token from session
     * 
     * @return void
     */
    public function clearToken(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::TOKEN_NAME]);
        Logger::getInstance()->debug('CSRF token cleared');
    }
}
