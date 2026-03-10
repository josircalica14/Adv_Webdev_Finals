<?php
/**
 * Session Manager
 * 
 * Handles secure session management including token generation,
 * validation, regeneration, and cleanup.
 */

require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../Logger.php';
require_once __DIR__ . '/../Config.php';
require_once __DIR__ . '/User.php';

class SessionManager {
    private PDO $db;
    private Config $config;
    private Logger $logger;
    private int $sessionLifetime;

    /**
     * Constructor
     */
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
        $this->sessionLifetime = $this->config->get('security.session_lifetime', 86400); // 24 hours default
    }

    /**
     * Create a new session for a user
     * 
     * @param User $user The user to create a session for
     * @return string The session token
     * @throws Exception if session creation fails
     */
    public function createSession(User $user): string {
        try {
            // Generate cryptographically secure session token
            $sessionToken = $this->generateSecureToken();
            
            // Calculate expiration time
            $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);
            
            // Get client information
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Insert session into database
            $sql = "INSERT INTO sessions (user_id, session_token, ip_address, user_agent, expires_at) 
                    VALUES (:user_id, :session_token, :ip_address, :user_agent, :expires_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $user->id,
                ':session_token' => $sessionToken,
                ':ip_address' => $ipAddress,
                ':user_agent' => $userAgent,
                ':expires_at' => $expiresAt
            ]);
            
            $this->logger->info('Session created', [
                'user_id' => $user->id,
                'ip_address' => $ipAddress
            ]);
            
            return $sessionToken;
        } catch (PDOException $e) {
            $this->logger->error('Session creation failed: ' . $e->getMessage());
            throw new Exception('Failed to create session');
        }
    }

    /**
     * Validate a session token and return the associated user
     * 
     * @param string $token The session token to validate
     * @return User|null The user if session is valid, null otherwise
     */
    public function validateSession(string $token): ?User {
        try {
            // Query session with user data
            $sql = "SELECT s.*, u.* 
                    FROM sessions s
                    INNER JOIN users u ON s.user_id = u.id
                    WHERE s.session_token = :token 
                    AND s.expires_at > NOW()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            // Create User object from result
            $user = User::fromArray($result);
            
            return $user;
        } catch (PDOException $e) {
            $this->logger->error('Session validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Regenerate session token for security
     * 
     * @param string $oldToken The current session token
     * @return string|null The new session token, or null if regeneration fails
     */
    public function regenerateToken(string $oldToken): ?string {
        try {
            // Validate old token first
            $user = $this->validateSession($oldToken);
            
            if (!$user) {
                return null;
            }
            
            // Generate new token
            $newToken = $this->generateSecureToken();
            
            // Update session with new token and extend expiration
            $expiresAt = date('Y-m-d H:i:s', time() + $this->sessionLifetime);
            
            $sql = "UPDATE sessions 
                    SET session_token = :new_token, expires_at = :expires_at 
                    WHERE session_token = :old_token";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':new_token' => $newToken,
                ':old_token' => $oldToken,
                ':expires_at' => $expiresAt
            ]);
            
            if ($stmt->rowCount() > 0) {
                $this->logger->info('Session token regenerated', ['user_id' => $user->id]);
                return $newToken;
            }
            
            return null;
        } catch (PDOException $e) {
            $this->logger->error('Session token regeneration failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Destroy a session (logout)
     * 
     * @param string $token The session token to destroy
     * @return bool True if session was destroyed, false otherwise
     */
    public function destroySession(string $token): bool {
        try {
            $sql = "DELETE FROM sessions WHERE session_token = :token";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $deleted = $stmt->rowCount() > 0;
            
            if ($deleted) {
                $this->logger->info('Session destroyed', ['token' => substr($token, 0, 10) . '...']);
            }
            
            return $deleted;
        } catch (PDOException $e) {
            $this->logger->error('Session destruction failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean up expired sessions
     * 
     * @return int Number of sessions cleaned up
     */
    public function cleanExpiredSessions(): int {
        try {
            $sql = "DELETE FROM sessions WHERE expires_at <= NOW()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $count = $stmt->rowCount();
            
            if ($count > 0) {
                $this->logger->info('Expired sessions cleaned up', ['count' => $count]);
            }
            
            return $count;
        } catch (PDOException $e) {
            $this->logger->error('Session cleanup failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get session data for a token
     * 
     * @param string $token The session token
     * @return array|null Session data or null if not found
     */
    public function getSessionData(string $token): ?array {
        try {
            $sql = "SELECT * FROM sessions WHERE session_token = :token AND expires_at > NOW()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: null;
        } catch (PDOException $e) {
            $this->logger->error('Failed to get session data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Set session cookie with secure settings
     * 
     * @param string $token The session token
     * @param int|null $lifetime Cookie lifetime in seconds (null for session cookie)
     * @return bool True if cookie was set successfully
     */
    public function setSessionCookie(string $token, ?int $lifetime = null): bool {
        $cookieName = 'session_token';
        $cookieLifetime = $lifetime ?? $this->sessionLifetime;
        $expires = time() + $cookieLifetime;
        
        // Determine if we're on HTTPS
        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                    || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        
        // Set cookie with secure options
        $options = [
            'expires' => $expires,
            'path' => '/',
            'domain' => '', // Use current domain
            'secure' => $isSecure, // Only send over HTTPS if available
            'httponly' => true, // Prevent JavaScript access (XSS protection)
            'samesite' => 'Strict' // CSRF protection
        ];
        
        $result = setcookie($cookieName, $token, $options);
        
        if ($result) {
            $this->logger->info('Session cookie set', [
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        return $result;
    }

    /**
     * Clear session cookie
     * 
     * @return bool True if cookie was cleared successfully
     */
    public function clearSessionCookie(): bool {
        $cookieName = 'session_token';
        
        // Set cookie with past expiration to delete it
        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ];
        
        return setcookie($cookieName, '', $options);
    }

    /**
     * Get session token from cookie
     * 
     * @return string|null The session token or null if not found
     */
    public function getSessionTokenFromCookie(): ?string {
        return $_COOKIE['session_token'] ?? null;
    }

    /**
     * Generate a cryptographically secure random token
     * 
     * @param int $length Token length in bytes (will be hex encoded, so actual string length is $length * 2)
     * @return string The generated token
     */
    private function generateSecureToken(int $length = 32): string {
        return bin2hex(random_bytes($length));
    }
}

