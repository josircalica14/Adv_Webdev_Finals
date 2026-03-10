<?php
/**
 * Password Validator
 * 
 * Validates password strength and handles password hashing/verification.
 */

require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/../Logger.php';

class PasswordValidator {
    private Config $config;
    private Logger $logger;
    private int $minLength = 8;

    public function __construct() {
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Validate password strength
     * 
     * Requirements:
     * - At least 8 characters
     * - Contains at least one uppercase letter
     * - Contains at least one lowercase letter
     * - Contains at least one number
     * 
     * @param string $password Password to validate
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validate(string $password): array {
        $errors = [];

        // Check minimum length
        if (strlen($password) < $this->minLength) {
            $errors[] = "Password must be at least {$this->minLength} characters long";
        }

        // Check for uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }

        // Check for lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }

        // Check for number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Hash password using bcrypt
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hash(string $password): string {
        $cost = $this->config->get('security.password_cost', 12);
        
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
        
        if ($hash === false) {
            $this->logger->error("Failed to hash password");
            throw new RuntimeException("Password hashing failed");
        }
        
        $this->logger->debug("Password hashed successfully");
        
        return $hash;
    }

    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches hash
     */
    public function verify(string $password, string $hash): bool {
        $result = password_verify($password, $hash);
        
        $this->logger->debug("Password verification", ['result' => $result ? 'success' : 'failed']);
        
        return $result;
    }

    /**
     * Check if password hash needs rehashing
     * 
     * @param string $hash Password hash
     * @return bool True if rehashing is needed
     */
    public function needsRehash(string $hash): bool {
        $cost = $this->config->get('security.password_cost', 12);
        
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
