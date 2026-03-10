<?php
/**
 * Email Validator
 * 
 * Validates email format and checks uniqueness against database.
 */

require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/../Logger.php';

class EmailValidator {
    private UserRepository $userRepository;
    private Logger $logger;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->logger = Logger::getInstance();
    }

    /**
     * Validate email format
     * 
     * @param string $email Email address to validate
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validateFormat(string $email): array {
        $errors = [];

        // Check if email is empty
        if (empty($email)) {
            $errors[] = "Email address is required";
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        // Validate email format using PHP's filter_var
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        // Additional format checks
        if (strlen($email) > 255) {
            $errors[] = "Email address is too long (maximum 255 characters)";
        }

        // Check for valid domain
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $domain = $parts[1];
            // Check if domain has at least one dot
            if (strpos($domain, '.') === false) {
                $errors[] = "Email domain must contain at least one dot";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if email is unique (not already registered)
     * 
     * @param string $email Email address to check
     * @return bool True if email is unique
     */
    public function isUnique(string $email): bool {
        $exists = $this->userRepository->emailExists($email);
        
        $this->logger->debug("Email uniqueness check", [
            'email' => $email,
            'exists' => $exists
        ]);
        
        return !$exists;
    }

    /**
     * Validate email format and uniqueness
     * 
     * @param string $email Email address to validate
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validate(string $email): array {
        // First check format
        $formatResult = $this->validateFormat($email);
        
        if (!$formatResult['valid']) {
            return $formatResult;
        }

        // Then check uniqueness
        if (!$this->isUnique($email)) {
            return [
                'valid' => false,
                'errors' => ['Email address is already registered']
            ];
        }

        return [
            'valid' => true,
            'errors' => []
        ];
    }
}
