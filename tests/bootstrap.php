<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Sets up the testing environment
 */

// Load application bootstrap
require_once __DIR__ . '/../includes/bootstrap.php';

// Set test environment flag
define('TESTING', true);

// Helper function to get test database configuration
function getTestDatabaseConfig(): array {
    return [
        'host' => getenv('TEST_DB_HOST') ?: 'localhost',
        'port' => getenv('TEST_DB_PORT') ?: 3306,
        'name' => getenv('TEST_DB_NAME') ?: 'portfolio_test',
        'username' => getenv('TEST_DB_USER') ?: 'root',
        'password' => getenv('TEST_DB_PASS') ?: '',
        'charset' => 'utf8mb4'
    ];
}
