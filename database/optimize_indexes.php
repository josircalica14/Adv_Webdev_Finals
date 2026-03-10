<?php
/**
 * Database Index Optimization Script
 * 
 * Adds performance indexes to database tables
 */

require_once __DIR__ . '/../includes/bootstrap.php';

echo "=== Database Index Optimization ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/optimize_indexes.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    $success = 0;
    $failed = 0;
    $skipped = 0;
    
    foreach ($statements as $statement) {
        // Skip comments
        if (preg_match('/^--/', $statement)) {
            continue;
        }
        
        // Extract index name for reporting
        if (preg_match('/CREATE INDEX.*?(\w+)\s+ON/i', $statement, $matches)) {
            $indexName = $matches[1];
        } elseif (preg_match('/ANALYZE TABLE\s+(\w+)/i', $statement, $matches)) {
            $indexName = "ANALYZE {$matches[1]}";
        } else {
            $indexName = substr($statement, 0, 50) . '...';
        }
        
        try {
            $db->exec($statement);
            echo "✓ {$indexName}\n";
            $success++;
        } catch (PDOException $e) {
            // Check if index already exists
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "⊙ {$indexName} (already exists)\n";
                $skipped++;
            } else {
                echo "✗ {$indexName}: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }
    
    echo "\n";
    echo "------------------------------------------------------------\n";
    echo "Total statements: " . count($statements) . "\n";
    echo "Success: {$success}\n";
    echo "Skipped: {$skipped}\n";
    echo "Failed: {$failed}\n";
    echo "------------------------------------------------------------\n";
    
    if ($failed === 0) {
        echo "\n✓ Database optimization completed successfully!\n";
        exit(0);
    } else {
        echo "\n⚠ Some optimizations failed. Please review the output above.\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
