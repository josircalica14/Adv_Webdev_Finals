<?php
/**
 * Database Migration Runner
 * 
 * Executes all SQL migration files in the migrations directory.
 * Run this script from the command line: php database/migrate.php
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== Database Migration Runner ===\n\n";

try {
    $database = Database::getInstance();
    $migrationsPath = __DIR__ . '/migrations';
    
    echo "Running migrations from: {$migrationsPath}\n\n";
    
    $results = $database->runMigrations($migrationsPath);
    
    echo "Migration Results:\n";
    echo str_repeat('-', 60) . "\n";
    
    $successCount = 0;
    $failureCount = 0;
    
    foreach ($results as $result) {
        $status = $result['status'] === 'success' ? '✓' : '✗';
        echo "{$status} {$result['file']} - {$result['status']}\n";
        
        if ($result['status'] === 'success') {
            $successCount++;
        } else {
            $failureCount++;
            if (isset($result['error'])) {
                echo "   Error: {$result['error']}\n";
            }
        }
    }
    
    echo str_repeat('-', 60) . "\n";
    echo "Total: " . count($results) . " migrations\n";
    echo "Success: {$successCount}\n";
    echo "Failed: {$failureCount}\n\n";
    
    if ($failureCount === 0) {
        echo "✓ All migrations completed successfully!\n";
        exit(0);
    } else {
        echo "✗ Some migrations failed. Please check the errors above.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "✗ Migration failed with error:\n";
    echo $e->getMessage() . "\n";
    exit(1);
}
