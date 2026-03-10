<?php
/**
 * Authentication Manager
 * 
 * Handles user registration, login, logout, email verification,
 * password reset, and password change operations.
 */

require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/PasswordValidator.php';
require_once __DIR__ . '/EmailValidator.php';
require_once __DIR__ . '/SessionManager.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Logger.php';
require_once __DIR__ . '/../SecurityLogger.php';
require_once __DIR__ . '/../ErrorHandler.php';

class AuthenticationManager {
    private UserRepository $userRepository;
    private PasswordValidator $passwordValidator;
    private EmailValidator $emailValidator;
    private SessionManager $sessionManager;
    private PDO $db;
    private Logger $logger;
    private SecurityLogger $securityLogger;

    public function __construct() {
        $this->userRepository = new UserRepository();
        $this->passwordValidator = new PasswordValidator();
        $this->emailValidator = new EmailValidator();
        $this->sessionManager = new SessionManager();
        $this->db = Database::getInstance()->getConnection();
        $this->logger = Logger::getInstance();
        $this->securityLogger = SecurityLogger::getInstance();
    }

    /**
     * Register a new user
     * 
     * @param string $email User email
     * @param string $password Plain text password
     * @param string $fullName User's full name
     * @param string $program User's program (BSIT or CSE)
     * @return array Registration result
     */
    public function register(string $email, string $password, string $fullName, string $program): array {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        try {
            // Validate email format and uniqueness
            $emailValidation = $this->emailValidator->validate($email);
            if (!$emailValidation['valid']) {
                $this->securityLogger->logRegistration($email, false, $ipAddress, 'Invalid email format or duplicate');
                return ErrorHandler::validationError([
                    'email' => $emailValidation['errors']
                ]);
            }

            // Validate password strength
            $passwordValidation = $this->passwordValidator->validate($password);
            if (!$passwordValidation['valid']) {
                $this->securityLogger->logRegistration($email, false, $ipAddress, 'Weak password');
                return ErrorHandler::validationError([
                    'password' => $passwordValidation['errors']
                ]);
            }

            // Validate full name
            if (empty(trim($fullName))) {
                $this->securityLogger->logRegistration($email, false, $ipAddress, 'Missing full name');
                return ErrorHandler::validationError([
                    'fullName' => ['Full name is required']
                ]);
            }

            // Validate program
            if (!in_array($program, ['BSIT', 'CSE'])) {
                $this->securityLogger->logRegistration($email, false, $ipAddress, 'Invalid program');
                return ErrorHandler::validationError([
                    'program' => ['Program must be either BSIT or CSE']
                ]);
            }

            // Generate username from email
            $username = $this->generateUsername($email);

            // Hash password
            $passwordHash = $this->passwordValidator->hash($password);

            // Create user
            $user = $this->userRepository->create($email, $passwordHash, $fullName, $program, $username);

            if (!$user) {
                $this->securityLogger->logRegistration($email, false, $ipAddress, 'Database error');
                return ErrorHandler::systemError('Failed to create user account');
            }

            // Generate verification token
            $verificationToken = $this->generateVerificationToken($user->id);

            $this->logger->info("User registered successfully", [
                'user_id' => $user->id,
                'email' => $email
            ]);
            
            $this->securityLogger->logRegistration($email, true, $ipAddress);

            return [
                'success' => true,
                'userId' => $user->id,
                'verificationToken' => $verificationToken,
                'user' => $user
            ];
        } catch (Exception $e) {
            $this->logger->error("Registration failed", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            $this->securityLogger->logRegistration($email, false, $ipAddress, 'System error: ' . $e->getMessage());
            return ErrorHandler::systemError('Registration failed');
        }
    }

    /**
     * Login user with email and password
     * 
     * @param string $email User email
     * @param string $password Plain text password
     * @return array Login result
     */
    public function login(string $email, string $password): array {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        try {
            // Find user by email
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                $this->logger->warning("Login attempt with non-existent email", ['email' => $email]);
                $this->securityLogger->logAuthAttempt($email, false, $ipAddress, 'User not found');
                return ErrorHandler::authenticationError('Invalid email or password', 'INVALID_CREDENTIALS');
            }

            // Verify password
            if (!$this->passwordValidator->verify($password, $user->passwordHash)) {
                $this->logger->warning("Login attempt with incorrect password", [
                    'user_id' => $user->id,
                    'email' => $email
                ]);
                $this->securityLogger->logAuthAttempt($email, false, $ipAddress, 'Invalid password');
                return ErrorHandler::authenticationError('Invalid email or password', 'INVALID_CREDENTIALS');
            }

            // Create session
            $sessionToken = $this->sessionManager->createSession($user);
            
            // Set session cookie
            $this->sessionManager->setSessionCookie($sessionToken);

            $this->logger->info("User logged in successfully", [
                'user_id' => $user->id,
                'email' => $email
            ]);
            
            $this->securityLogger->logAuthAttempt($email, true, $ipAddress);

            return [
                'success' => true,
                'user' => $user,
                'sessionToken' => $sessionToken
            ];
        } catch (Exception $e) {
            $this->logger->error("Login failed", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            $this->securityLogger->logAuthAttempt($email, false, $ipAddress, 'System error: ' . $e->getMessage());
            return ErrorHandler::systemError('Login failed');
        }
    }

    /**
     * Logout user (session termination)
     * 
     * @return array Logout result
     */
    public function logout(): array {
        try {
            // Get session token from cookie
            $sessionToken = $this->sessionManager->getSessionTokenFromCookie();
            
            if ($sessionToken) {
                // Destroy session in database
                $this->sessionManager->destroySession($sessionToken);
                
                // Clear session cookie
                $this->sessionManager->clearSessionCookie();
            }
            
            $this->logger->info("User logged out");
            
            return [
                'success' => true,
                'message' => 'Logged out successfully'
            ];
        } catch (Exception $e) {
            $this->logger->error("Logout failed", ['error' => $e->getMessage()]);
            return ErrorHandler::systemError('Logout failed');
        }
    }

    /**
     * Validate current session and return user
     * 
     * @return User|null The authenticated user or null
     */
    public function validateSession(): ?User {
        try {
            $sessionToken = $this->sessionManager->getSessionTokenFromCookie();
            
            if (!$sessionToken) {
                return null;
            }
            
            return $this->sessionManager->validateSession($sessionToken);
        } catch (Exception $e) {
            $this->logger->error("Session validation failed", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated(): bool {
        return $this->validateSession() !== null;
    }

    /**
     * Generate email verification token
     * 
     * @param int $userId User ID
     * @return string Verification token
     */
    private function generateVerificationToken(int $userId): string {
        try {
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $sql = "INSERT INTO email_verifications (user_id, verification_token, expires_at) 
                    VALUES (:user_id, :token, :expires_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);

            $this->logger->info("Verification token generated", ['user_id' => $userId]);

            return $token;
        } catch (Exception $e) {
            $this->logger->error("Failed to generate verification token", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify email with token
     * 
     * @param string $token Verification token
     * @return bool Success status
     */
    public function verifyEmail(string $token): bool {
        try {
            // Find verification record
            $sql = "SELECT user_id, expires_at FROM email_verifications 
                    WHERE verification_token = :token";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->logger->warning("Invalid verification token", ['token' => $token]);
                return false;
            }

            // Check if token is expired
            if (strtotime($record['expires_at']) < time()) {
                $this->logger->warning("Expired verification token", ['token' => $token]);
                return false;
            }

            // Update user verification status
            $sql = "UPDATE users SET is_verified = 1 WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $record['user_id']]);

            // Delete verification token
            $sql = "DELETE FROM email_verifications WHERE verification_token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);

            $this->logger->info("Email verified successfully", ['user_id' => $record['user_id']]);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Email verification failed", [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Request password reset
     * 
     * @param string $email User email
     * @return bool Success status
     */
    public function requestPasswordReset(string $email): bool {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        try {
            // Find user by email
            $user = $this->userRepository->findByEmail($email);

            if (!$user) {
                // Don't reveal if email exists or not for security
                $this->logger->warning("Password reset requested for non-existent email", ['email' => $email]);
                $this->securityLogger->logPasswordResetRequest($email, false, $ipAddress);
                return true; // Return true to prevent email enumeration
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Delete any existing reset tokens for this user
            $sql = "DELETE FROM password_resets WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user->id]);

            // Insert new reset token
            $sql = "INSERT INTO password_resets (user_id, reset_token, expires_at) 
                    VALUES (:user_id, :token, :expires_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $user->id,
                ':token' => $token,
                ':expires_at' => $expiresAt
            ]);

            $this->logger->info("Password reset requested", ['user_id' => $user->id]);
            $this->securityLogger->logPasswordResetRequest($email, true, $ipAddress);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Password reset request failed", [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            $this->securityLogger->logPasswordResetRequest($email, false, $ipAddress);
            return false;
        }
    }

    /**
     * Reset password with token
     * 
     * @param string $token Reset token
     * @param string $newPassword New plain text password
     * @return bool Success status
     */
    public function resetPassword(string $token, string $newPassword): bool {
        try {
            // Validate new password
            $passwordValidation = $this->passwordValidator->validate($newPassword);
            if (!$passwordValidation['valid']) {
                $this->logger->warning("Password reset failed: weak password", ['token' => $token]);
                return false;
            }

            // Find reset record
            $sql = "SELECT user_id, expires_at FROM password_resets 
                    WHERE reset_token = :token";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$record) {
                $this->logger->warning("Invalid password reset token", ['token' => $token]);
                return false;
            }

            // Check if token is expired
            if (strtotime($record['expires_at']) < time()) {
                $this->logger->warning("Expired password reset token", ['token' => $token]);
                return false;
            }

            // Hash new password
            $passwordHash = $this->passwordValidator->hash($newPassword);

            // Update user password
            $sql = "UPDATE users SET password_hash = :password_hash WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':password_hash' => $passwordHash,
                ':user_id' => $record['user_id']
            ]);

            // Delete reset token
            $sql = "DELETE FROM password_resets WHERE reset_token = :token";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);

            $this->logger->info("Password reset successfully", ['user_id' => $record['user_id']]);

            return true;
        } catch (Exception $e) {
            $this->logger->error("Password reset failed", [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Change password for authenticated user
     * 
     * @param int $userId User ID
     * @param string $currentPassword Current plain text password
     * @param string $newPassword New plain text password
     * @return array Change result
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        try {
            // Find user
            $user = $this->userRepository->findById($userId);

            if (!$user) {
                $this->securityLogger->logPasswordChange($userId, false, $ipAddress);
                return ErrorHandler::notFoundError('User');
            }

            // Verify current password
            if (!$this->passwordValidator->verify($currentPassword, $user->passwordHash)) {
                $this->logger->warning("Password change failed: incorrect current password", [
                    'user_id' => $userId
                ]);
                $this->securityLogger->logPasswordChange($userId, false, $ipAddress);
                return ErrorHandler::authenticationError('Current password is incorrect', 'INVALID_PASSWORD');
            }

            // Validate new password
            $passwordValidation = $this->passwordValidator->validate($newPassword);
            if (!$passwordValidation['valid']) {
                $this->securityLogger->logPasswordChange($userId, false, $ipAddress);
                return ErrorHandler::validationError([
                    'newPassword' => $passwordValidation['errors']
                ]);
            }

            // Hash new password
            $passwordHash = $this->passwordValidator->hash($newPassword);

            // Update password
            $user->passwordHash = $passwordHash;
            $success = $this->userRepository->update($user);

            if (!$success) {
                $this->securityLogger->logPasswordChange($userId, false, $ipAddress);
                return ErrorHandler::systemError('Failed to update password');
            }

            $this->logger->info("Password changed successfully", ['user_id' => $userId]);
            $this->securityLogger->logPasswordChange($userId, true, $ipAddress);

            return [
                'success' => true,
                'message' => 'Password changed successfully'
            ];
        } catch (Exception $e) {
            $this->logger->error("Password change failed", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            $this->securityLogger->logPasswordChange($userId, false, $ipAddress);
            return ErrorHandler::systemError('Password change failed');
        }
    }

    /**
     * Generate username from email
     * 
     * @param string $email Email address
     * @return string Generated username
     */
    private function generateUsername(string $email): string {
        // Extract username part from email
        $parts = explode('@', $email);
        $baseUsername = preg_replace('/[^a-zA-Z0-9_-]/', '', $parts[0]);

        // Ensure username is not empty
        if (empty($baseUsername)) {
            $baseUsername = 'user';
        }

        // Check if username exists and add number suffix if needed
        $username = $baseUsername;
        $counter = 1;

        while ($this->userRepository->usernameExists($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }
}
