<?php
/**
 * RateLimiter Class
 * 
 * Implements rate limiting functionality to prevent brute force attacks
 * and abuse of system resources. Tracks attempts by identifier (e.g., IP address)
 * and action type with configurable time windows and thresholds.
 */

require_once __DIR__ . '/../SecurityLogger.php';

class RateLimiter {
    private PDO $db;
    private SecurityLogger $securityLogger;
    
    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db) {
        $this->db = $db;
        $this->securityLogger = SecurityLogger::getInstance();
    }
    
    /**
     * Check if rate limit has been exceeded
     * 
     * @param string $identifier Unique identifier (e.g., IP address, user ID)
     * @param string $action Action being rate limited (e.g., 'login', 'upload')
     * @param int $maxAttempts Maximum allowed attempts within the window
     * @param int $windowSeconds Time window in seconds
     * @return bool True if limit is not exceeded, false if exceeded
     */
    public function checkLimit(string $identifier, string $action, int $maxAttempts, int $windowSeconds): bool {
        try {
            // Clean up expired entries first
            $this->cleanupExpiredEntries($windowSeconds);
            
            // Get current attempt count within the window
            $sql = "SELECT attempt_count, window_start 
                    FROM rate_limits 
                    WHERE identifier = :identifier 
                    AND action = :action";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$record) {
                // No record exists, limit not exceeded
                return true;
            }
            
            // Check if window has expired
            $windowStart = strtotime($record['window_start']);
            $windowEnd = $windowStart + $windowSeconds;
            $now = time();
            
            if ($now > $windowEnd) {
                // Window expired, reset the limit
                $this->resetLimit($identifier, $action);
                return true;
            }
            
            // Check if attempt count exceeds limit
            $limitExceeded = $record['attempt_count'] >= $maxAttempts;
            
            if ($limitExceeded) {
                // Log rate limit violation
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                $this->securityLogger->logRateLimitViolation($identifier, $action, $ipAddress);
            }
            
            return !$limitExceeded;
            
        } catch (PDOException $e) {
            Logger::getInstance()->error('Rate limit check failed', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            // On error, allow the action (fail open for availability)
            return true;
        }
    }
    
    /**
     * Record an attempt for rate limiting
     * 
     * @param string $identifier Unique identifier (e.g., IP address, user ID)
     * @param string $action Action being rate limited
     * @return void
     */
    public function recordAttempt(string $identifier, string $action): void {
        try {
            // Try to insert or update the record
            $sql = "INSERT INTO rate_limits (identifier, action, attempt_count, window_start)
                    VALUES (:identifier, :action, 1, NOW())
                    ON DUPLICATE KEY UPDATE 
                        attempt_count = attempt_count + 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);
            
            Logger::getInstance()->debug('Rate limit attempt recorded', [
                'identifier' => $identifier,
                'action' => $action
            ]);
            
        } catch (PDOException $e) {
            Logger::getInstance()->error('Failed to record rate limit attempt', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reset rate limit for a specific identifier and action
     * 
     * @param string $identifier Unique identifier
     * @param string $action Action being rate limited
     * @return void
     */
    public function resetLimit(string $identifier, string $action): void {
        try {
            $sql = "DELETE FROM rate_limits 
                    WHERE identifier = :identifier 
                    AND action = :action";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);
            
            Logger::getInstance()->debug('Rate limit reset', [
                'identifier' => $identifier,
                'action' => $action
            ]);
            
        } catch (PDOException $e) {
            Logger::getInstance()->error('Failed to reset rate limit', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get current attempt count for an identifier and action
     * 
     * @param string $identifier Unique identifier
     * @param string $action Action being rate limited
     * @return int Current attempt count
     */
    public function getAttemptCount(string $identifier, string $action): int {
        try {
            $sql = "SELECT attempt_count 
                    FROM rate_limits 
                    WHERE identifier = :identifier 
                    AND action = :action";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':identifier' => $identifier,
                ':action' => $action
            ]);
            
            $record = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $record ? (int)$record['attempt_count'] : 0;
            
        } catch (PDOException $e) {
            Logger::getInstance()->error('Failed to get attempt count', [
                'identifier' => $identifier,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Clean up expired rate limit entries
     * 
     * @param int $windowSeconds Time window in seconds
     * @return void
     */
    private function cleanupExpiredEntries(int $windowSeconds): void {
        try {
            $sql = "DELETE FROM rate_limits 
                    WHERE TIMESTAMPDIFF(SECOND, window_start, NOW()) > :window_seconds";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':window_seconds' => $windowSeconds]);
            
        } catch (PDOException $e) {
            Logger::getInstance()->error('Failed to cleanup expired rate limits', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get remaining attempts before rate limit is hit
     * 
     * @param string $identifier Unique identifier
     * @param string $action Action being rate limited
     * @param int $maxAttempts Maximum allowed attempts
     * @return int Remaining attempts (0 if limit exceeded)
     */
    public function getRemainingAttempts(string $identifier, string $action, int $maxAttempts): int {
        $currentAttempts = $this->getAttemptCount($identifier, $action);
        $remaining = $maxAttempts - $currentAttempts;
        return max(0, $remaining);
    }
}
