<?php
/**
 * SecurityLogger Class
 * 
 * Specialized logger for security events including authentication attempts,
 * security violations, and admin actions. Provides separate log files for
 * different security event types with automatic rotation and retention.
 */

require_once __DIR__ . '/Logger.php';

class SecurityLogger {
    private static ?SecurityLogger $instance = null;
    private string $logPath;
    private const MAX_LOG_SIZE = 10485760; // 10MB
    private const MAX_ARCHIVED_LOGS = 30; // Keep 30 days of logs
    private const LOG_RETENTION_DAYS = 90; // Delete logs older than 90 days

    // Log file types
    private const AUTH_LOG = 'auth.log';
    private const SECURITY_LOG = 'security.log';
    private const ADMIN_LOG = 'admin.log';

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logPath = __DIR__ . '/../logs';
        $this->ensureLogDirectory();
    }

    /**
     * Get singleton instance of SecurityLogger
     * 
     * @return SecurityLogger
     */
    public static function getInstance(): SecurityLogger {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory(): void {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log authentication attempt
     * 
     * @param string $email User email
     * @param bool $success Whether login was successful
     * @param string $ipAddress IP address of attempt
     * @param string|null $reason Failure reason if unsuccessful
     */
    public function logAuthAttempt(string $email, bool $success, string $ipAddress, ?string $reason = null): void {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        $message = "Authentication attempt for {$email} from {$ipAddress}: {$status}";
        
        $context = [
            'email' => $email,
            'success' => $success,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => time()
        ];
        
        if (!$success && $reason) {
            $message .= " - {$reason}";
            $context['reason'] = $reason;
        }
        
        $this->writeLog(self::AUTH_LOG, $message, $context);
    }

    /**
     * Log registration attempt
     * 
     * @param string $email User email
     * @param bool $success Whether registration was successful
     * @param string $ipAddress IP address of attempt
     * @param string|null $reason Failure reason if unsuccessful
     */
    public function logRegistration(string $email, bool $success, string $ipAddress, ?string $reason = null): void {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        $message = "Registration attempt for {$email} from {$ipAddress}: {$status}";
        
        $context = [
            'email' => $email,
            'success' => $success,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => time()
        ];
        
        if (!$success && $reason) {
            $message .= " - {$reason}";
            $context['reason'] = $reason;
        }
        
        $this->writeLog(self::AUTH_LOG, $message, $context);
    }

    /**
     * Log password change
     * 
     * @param int $userId User ID
     * @param bool $success Whether change was successful
     * @param string $ipAddress IP address of attempt
     */
    public function logPasswordChange(int $userId, bool $success, string $ipAddress): void {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        $message = "Password change for user {$userId} from {$ipAddress}: {$status}";
        
        $context = [
            'user_id' => $userId,
            'success' => $success,
            'ip_address' => $ipAddress,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::AUTH_LOG, $message, $context);
    }

    /**
     * Log password reset request
     * 
     * @param string $email User email
     * @param bool $success Whether request was successful
     * @param string $ipAddress IP address of attempt
     */
    public function logPasswordResetRequest(string $email, bool $success, string $ipAddress): void {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        $message = "Password reset request for {$email} from {$ipAddress}: {$status}";
        
        $context = [
            'email' => $email,
            'success' => $success,
            'ip_address' => $ipAddress,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::AUTH_LOG, $message, $context);
    }

    /**
     * Log security event
     * 
     * @param string $eventType Type of security event
     * @param string $description Event description
     * @param array $context Additional context data
     */
    public function logSecurityEvent(string $eventType, string $description, array $context = []): void {
        $message = "[{$eventType}] {$description}";
        
        $context['event_type'] = $eventType;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $context['timestamp'] = time();
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log rate limit violation
     * 
     * @param string $identifier Rate limit identifier (IP or user ID)
     * @param string $action Action being rate limited
     * @param string $ipAddress IP address
     */
    public function logRateLimitViolation(string $identifier, string $action, string $ipAddress): void {
        $message = "Rate limit exceeded for {$action} by {$identifier} from {$ipAddress}";
        
        $context = [
            'identifier' => $identifier,
            'action' => $action,
            'ip_address' => $ipAddress,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log CSRF token validation failure
     * 
     * @param string $ipAddress IP address
     * @param string $endpoint Endpoint being accessed
     */
    public function logCSRFViolation(string $ipAddress, string $endpoint): void {
        $message = "CSRF token validation failed from {$ipAddress} on {$endpoint}";
        
        $context = [
            'ip_address' => $ipAddress,
            'endpoint' => $endpoint,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'timestamp' => time()
        ];
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log SQL injection attempt
     * 
     * @param string $input Suspicious input
     * @param string $ipAddress IP address
     * @param string $endpoint Endpoint being accessed
     */
    public function logSQLInjectionAttempt(string $input, string $ipAddress, string $endpoint): void {
        $message = "Potential SQL injection attempt from {$ipAddress} on {$endpoint}";
        
        $context = [
            'suspicious_input' => substr($input, 0, 200), // Limit input length
            'ip_address' => $ipAddress,
            'endpoint' => $endpoint,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log XSS attempt
     * 
     * @param string $input Suspicious input
     * @param string $ipAddress IP address
     * @param string $endpoint Endpoint being accessed
     */
    public function logXSSAttempt(string $input, string $ipAddress, string $endpoint): void {
        $message = "Potential XSS attempt from {$ipAddress} on {$endpoint}";
        
        $context = [
            'suspicious_input' => substr($input, 0, 200), // Limit input length
            'ip_address' => $ipAddress,
            'endpoint' => $endpoint,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log unauthorized access attempt
     * 
     * @param int|null $userId User ID attempting access
     * @param string $resource Resource being accessed
     * @param string $ipAddress IP address
     */
    public function logUnauthorizedAccess(?int $userId, string $resource, string $ipAddress): void {
        $userInfo = $userId ? "User {$userId}" : "Unauthenticated user";
        $message = "{$userInfo} attempted unauthorized access to {$resource} from {$ipAddress}";
        
        $context = [
            'user_id' => $userId,
            'resource' => $resource,
            'ip_address' => $ipAddress,
            'timestamp' => time()
        ];
        
        $this->writeLog(self::SECURITY_LOG, $message, $context);
    }

    /**
     * Log admin action
     * 
     * @param int $adminId Admin user ID
     * @param string $action Action performed
     * @param array $details Action details
     */
    public function logAdminAction(int $adminId, string $action, array $details = []): void {
        $message = "Admin {$adminId} performed action: {$action}";
        
        $context = [
            'admin_id' => $adminId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'timestamp' => time()
        ];
        
        $this->writeLog(self::ADMIN_LOG, $message, $context);
    }

    /**
     * Write log entry to specified log file
     * 
     * @param string $logType Log file type
     * @param string $message Log message
     * @param array $context Additional context data
     */
    private function writeLog(string $logType, string $message, array $context = []): void {
        $logFile = $this->logPath . '/' . $logType;
        
        $this->rotateLogIfNeeded($logFile);
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$message}{$contextStr}\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file if it exceeds maximum size
     * 
     * @param string $logFile Log file path
     */
    private function rotateLogIfNeeded(string $logFile): void {
        if (!file_exists($logFile)) {
            return;
        }
        
        if (filesize($logFile) >= self::MAX_LOG_SIZE) {
            $timestamp = date('Y-m-d_H-i-s');
            $basename = basename($logFile, '.log');
            $archiveFile = $this->logPath . "/{$basename}_{$timestamp}.log";
            rename($logFile, $archiveFile);
            
            // Clean old logs for this type
            $this->cleanOldLogs($basename);
        }
    }

    /**
     * Clean old log files, keeping only the most recent archived logs
     * and deleting logs older than retention period
     * 
     * @param string $basename Base name of log file (without .log extension)
     */
    private function cleanOldLogs(string $basename): void {
        $logFiles = glob($this->logPath . "/{$basename}_*.log");
        
        if (empty($logFiles)) {
            return;
        }
        
        // Sort by modification time (oldest first)
        usort($logFiles, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        $retentionTimestamp = time() - (self::LOG_RETENTION_DAYS * 86400);
        
        foreach ($logFiles as $index => $file) {
            $fileTime = filemtime($file);
            
            // Delete if older than retention period
            if ($fileTime < $retentionTimestamp) {
                unlink($file);
                continue;
            }
            
            // Delete if exceeds max archived logs count
            if ($index < count($logFiles) - self::MAX_ARCHIVED_LOGS) {
                unlink($file);
            }
        }
    }

    /**
     * Get recent log entries from specified log type
     * 
     * @param string $logType Log file type
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getRecentLogs(string $logType, int $lines = 100): array {
        $logFile = $this->logPath . '/' . $logType;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $file = new SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        $logs = [];
        while (!$file->eof()) {
            $line = trim($file->current());
            if (!empty($line)) {
                $logs[] = $line;
            }
            $file->next();
        }
        
        return $logs;
    }

    /**
     * Get authentication logs
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getAuthLogs(int $lines = 100): array {
        return $this->getRecentLogs(self::AUTH_LOG, $lines);
    }

    /**
     * Get security event logs
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getSecurityLogs(int $lines = 100): array {
        return $this->getRecentLogs(self::SECURITY_LOG, $lines);
    }

    /**
     * Get admin action logs
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getAdminLogs(int $lines = 100): array {
        return $this->getRecentLogs(self::ADMIN_LOG, $lines);
    }

    /**
     * Search logs for specific pattern
     * 
     * @param string $logType Log file type
     * @param string $pattern Search pattern
     * @param int $maxResults Maximum results to return
     * @return array
     */
    public function searchLogs(string $logType, string $pattern, int $maxResults = 100): array {
        $logFile = $this->logPath . '/' . $logType;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $results = [];
        $file = new SplFileObject($logFile);
        
        while (!$file->eof() && count($results) < $maxResults) {
            $line = trim($file->current());
            if (!empty($line) && stripos($line, $pattern) !== false) {
                $results[] = $line;
            }
            $file->next();
        }
        
        return $results;
    }

    /**
     * Get log statistics
     * 
     * @param string $logType Log file type
     * @param int $hours Number of hours to analyze
     * @return array
     */
    public function getLogStats(string $logType, int $hours = 24): array {
        $logs = $this->getRecentLogs($logType, 10000); // Get more logs for analysis
        $cutoffTime = time() - ($hours * 3600);
        
        $stats = [
            'total_events' => 0,
            'events_by_hour' => [],
            'unique_ips' => [],
            'event_types' => []
        ];
        
        foreach ($logs as $log) {
            // Parse timestamp from log entry
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $log, $matches)) {
                $logTime = strtotime($matches[1]);
                
                if ($logTime >= $cutoffTime) {
                    $stats['total_events']++;
                    
                    // Count by hour
                    $hour = date('Y-m-d H:00', $logTime);
                    $stats['events_by_hour'][$hour] = ($stats['events_by_hour'][$hour] ?? 0) + 1;
                    
                    // Extract IP address
                    if (preg_match('/"ip_address":"([^"]+)"/', $log, $ipMatch)) {
                        $stats['unique_ips'][$ipMatch[1]] = true;
                    }
                    
                    // Extract event type for security logs
                    if ($logType === self::SECURITY_LOG && preg_match('/\[([^\]]+)\]/', $log, $typeMatch)) {
                        $eventType = $typeMatch[1];
                        $stats['event_types'][$eventType] = ($stats['event_types'][$eventType] ?? 0) + 1;
                    }
                }
            }
        }
        
        $stats['unique_ip_count'] = count($stats['unique_ips']);
        unset($stats['unique_ips']); // Remove the array, keep only count
        
        return $stats;
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
