<?php
/**
 * Bootstrap File
 * 
 * Initializes core infrastructure components and sets up the application environment.
 * Include this file at the beginning of all PHP scripts.
 */

// Set error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Autoload core classes
spl_autoload_register(function ($class) {
    // Handle namespaced classes
    $class = str_replace('\\', '/', $class);
    $file = BASE_PATH . '/includes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize core components
try {
    // Initialize configuration
    $config = Config::getInstance();
    
    // Set timezone from config
    date_default_timezone_set($config->get('app.timezone', 'UTC'));
    
    // Initialize logger
    $logger = Logger::getInstance();
    $logger->info('Application bootstrap started');
    
    // Initialize error handler
    $errorHandler = ErrorHandler::getInstance();
    
    // Initialize database connection
    $database = Database::getInstance();
    
    $logger->info('Application bootstrap completed successfully');
    
} catch (Exception $e) {
    // If bootstrap fails, log to PHP error log and display error
    error_log('Bootstrap failed: ' . $e->getMessage());
    
    if (ini_get('display_errors')) {
        echo '<h1>Application Initialization Failed</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    } else {
        echo '<h1>Application Error</h1>';
        echo '<p>The application could not be initialized. Please contact support.</p>';
    }
    
    exit(1);
}

// Helper function to get database instance
function db(): Database {
    return Database::getInstance();
}

// Helper function to get config instance
function config(): Config {
    return Config::getInstance();
}

// Helper function to get logger instance
function logger(): Logger {
    return Logger::getInstance();
}

// Helper function to get current authenticated user
function currentUser(): ?User {
    static $authManager = null;
    
    if ($authManager === null) {
        require_once BASE_PATH . '/includes/Auth/AuthenticationManager.php';
        $authManager = new AuthenticationManager();
    }
    
    return $authManager->validateSession();
}

// Helper function to check if user is authenticated
function isAuthenticated(): bool {
    return currentUser() !== null;
}

// Helper function to require authentication (redirect to login if not authenticated)
function requireAuth(string $redirectUrl = '/login.php'): User {
    $user = currentUser();
    
    if (!$user) {
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    return $user;
}

// Ensure cache directory exists
$cacheDir = BASE_PATH . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
