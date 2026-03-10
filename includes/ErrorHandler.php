<?php
/**
 * Error Handler Class
 * 
 * Provides centralized error handling and exception management
 * with logging and user-friendly error responses.
 */

class ErrorHandler {
    private static ?ErrorHandler $instance = null;
    private Logger $logger;
    private Config $config;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->logger = Logger::getInstance();
        $this->config = Config::getInstance();
        $this->registerHandlers();
    }

    /**
     * Get singleton instance of ErrorHandler
     * 
     * @return ErrorHandler
     */
    public static function getInstance(): ErrorHandler {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register error and exception handlers
     */
    private function registerHandlers(): void {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     * 
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where error occurred
     * @param int $errline Line number where error occurred
     * @return bool
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorType = $this->getErrorType($errno);
        
        $this->logger->error("PHP Error [{$errorType}]: {$errstr}", [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errorType
        ]);

        if ($this->config->get('app.debug', false)) {
            echo "<b>{$errorType}</b>: {$errstr} in <b>{$errfile}</b> on line <b>{$errline}</b><br>";
        }

        return true;
    }

    /**
     * Handle uncaught exceptions
     * 
     * @param Throwable $exception
     */
    public function handleException(Throwable $exception): void {
        $this->logger->error('Uncaught Exception: ' . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        if ($this->config->get('app.debug', false)) {
            echo "<h1>Uncaught Exception</h1>";
            echo "<p><b>Message:</b> {$exception->getMessage()}</p>";
            echo "<p><b>File:</b> {$exception->getFile()}</p>";
            echo "<p><b>Line:</b> {$exception->getLine()}</p>";
            echo "<pre>{$exception->getTraceAsString()}</pre>";
        } else {
            echo "<h1>An error occurred</h1>";
            echo "<p>We're sorry, but something went wrong. Please try again later.</p>";
        }
    }

    /**
     * Handle fatal errors on shutdown
     */
    public function handleShutdown(): void {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $this->logger->error('Fatal Error: ' . $error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $this->getErrorType($error['type'])
            ]);

            if ($this->config->get('app.debug', false)) {
                echo "<h1>Fatal Error</h1>";
                echo "<p><b>Message:</b> {$error['message']}</p>";
                echo "<p><b>File:</b> {$error['file']}</p>";
                echo "<p><b>Line:</b> {$error['line']}</p>";
            }
        }
    }

    /**
     * Create a validation error response
     * 
     * @param array $errors Validation errors
     * @param string $message General error message
     * @return array
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): array {
        return [
            'success' => false,
            'error' => $message,
            'errorCode' => 'VALIDATION_ERROR',
            'validationErrors' => $errors,
            'suggestion' => 'Please check the highlighted fields and try again.'
        ];
    }

    /**
     * Create an authentication error response
     * 
     * @param string $message Error message
     * @param string $errorCode Error code
     * @return array
     */
    public static function authenticationError(string $message, string $errorCode = 'AUTH_ERROR'): array {
        return [
            'success' => false,
            'error' => $message,
            'errorCode' => $errorCode,
            'validationErrors' => null,
            'suggestion' => null
        ];
    }

    /**
     * Create a resource not found error response
     * 
     * @param string $resource Resource type
     * @return array
     */
    public static function notFoundError(string $resource = 'Resource'): array {
        return [
            'success' => false,
            'error' => "{$resource} not found",
            'errorCode' => 'NOT_FOUND',
            'validationErrors' => null,
            'suggestion' => 'Please check the URL and try again.'
        ];
    }

    /**
     * Create a permission denied error response
     * 
     * @param string $message Error message
     * @return array
     */
    public static function permissionError(string $message = 'Permission denied'): array {
        return [
            'success' => false,
            'error' => $message,
            'errorCode' => 'PERMISSION_DENIED',
            'validationErrors' => null,
            'suggestion' => 'You do not have permission to perform this action.'
        ];
    }

    /**
     * Create a system error response
     * 
     * @param string $message Error message
     * @return array
     */
    public static function systemError(string $message = 'A system error occurred'): array {
        return [
            'success' => false,
            'error' => $message,
            'errorCode' => 'SYSTEM_ERROR',
            'validationErrors' => null,
            'suggestion' => 'Please try again later or contact support if the problem persists.'
        ];
    }

    /**
     * Get error type name from error number
     * 
     * @param int $errno Error number
     * @return string
     */
    private function getErrorType(int $errno): string {
        $errorTypes = [
            E_ERROR => 'E_ERROR',
            E_WARNING => 'E_WARNING',
            E_PARSE => 'E_PARSE',
            E_NOTICE => 'E_NOTICE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED => 'E_DEPRECATED',
            E_USER_DEPRECATED => 'E_USER_DEPRECATED'
        ];

        return $errorTypes[$errno] ?? 'UNKNOWN';
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
