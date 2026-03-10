<?php
/**
 * Logger Class
 * 
 * Provides logging functionality with multiple severity levels
 * and automatic log rotation.
 */

class Logger {
    private static ?Logger $instance = null;
    private string $logPath;
    private string $logFile;
    private const MAX_LOG_SIZE = 10485760; // 10MB

    // Log levels
    public const DEBUG = 'DEBUG';
    public const INFO = 'INFO';
    public const WARNING = 'WARNING';
    public const ERROR = 'ERROR';
    public const CRITICAL = 'CRITICAL';

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logPath = __DIR__ . '/../logs';
        $this->ensureLogDirectory();
        $this->logFile = $this->logPath . '/app.log';
    }

    /**
     * Get singleton instance of Logger
     * 
     * @return Logger
     */
    public static function getInstance(): Logger {
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
     * Log a debug message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function debug(string $message, array $context = []): void {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Log an info message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function info(string $message, array $context = []): void {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * Log a warning message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function warning(string $message, array $context = []): void {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * Log an error message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function error(string $message, array $context = []): void {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * Log a critical message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function critical(string $message, array $context = []): void {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Write log entry
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     */
    private function log(string $level, string $message, array $context = []): void {
        $this->rotateLogIfNeeded();

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";

        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file if it exceeds maximum size
     */
    private function rotateLogIfNeeded(): void {
        if (!file_exists($this->logFile)) {
            return;
        }

        if (filesize($this->logFile) >= self::MAX_LOG_SIZE) {
            $timestamp = date('Y-m-d_H-i-s');
            $archiveFile = $this->logPath . "/app_{$timestamp}.log";
            rename($this->logFile, $archiveFile);
            
            // Keep only last 10 archived logs
            $this->cleanOldLogs();
        }
    }

    /**
     * Clean old log files, keeping only the most recent 10
     */
    private function cleanOldLogs(): void {
        $logFiles = glob($this->logPath . '/app_*.log');
        
        if (count($logFiles) > 10) {
            usort($logFiles, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $filesToDelete = array_slice($logFiles, 0, count($logFiles) - 10);
            foreach ($filesToDelete as $file) {
                unlink($file);
            }
        }
    }

    /**
     * Get log file path
     * 
     * @return string
     */
    public function getLogFile(): string {
        return $this->logFile;
    }

    /**
     * Get recent log entries
     * 
     * @param int $lines Number of lines to retrieve
     * @return array
     */
    public function getRecentLogs(int $lines = 100): array {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $file = new SplFileObject($this->logFile);
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
     * Clear current log file
     */
    public function clearLog(): void {
        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '');
        }
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
