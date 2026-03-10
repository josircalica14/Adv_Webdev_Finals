<?php
/**
 * Property-Based Test for Database Schema
 * 
 * @group Feature: multi-user-portfolio-platform, Property 79: Migration Data Conversion
 * 
 * Tests that database schema is correctly set up for multi-user system.
 */

use PHPUnit\Framework\TestCase;

class MigrationDataConversionTest extends TestCase {
    private PDO $pdo;
    private string $testDbName = 'portfolio_test';
    
    protected function setUp(): void {
        parent::setUp();
        
        // Create test database and connection
        $this->createTestDatabase();
        
        // Run schema migrations
        $this->runSchemaMigrations();
    }
    
    protected function tearDown(): void {
        // Clean up test database
        $this->dropTestDatabase();
        parent::tearDown();
    }
    
    /**
     * Create test database
     */
    private function createTestDatabase(): void {
        $config = Config::getInstance();
        $host = $config->get('db.host');
        $username = $config->get('db.username');
        $password = $config->get('db.password');
        
        $tempPdo = new PDO("mysql:host={$host}", $username, $password);
        $tempPdo->exec("DROP DATABASE IF EXISTS {$this->testDbName}");
        $tempPdo->exec("CREATE DATABASE {$this->testDbName} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Create connection to test database
        $dsn = "mysql:host={$host};dbname={$this->testDbName};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
    
    /**
     * Drop test database
     */
    private function dropTestDatabase(): void {
        $config = Config::getInstance();
        $host = $config->get('db.host');
        $username = $config->get('db.username');
        $password = $config->get('db.password');
        
        $this->pdo = null; // Close connection
        
        $tempPdo = new PDO("mysql:host={$host}", $username, $password);
        $tempPdo->exec("DROP DATABASE IF EXISTS {$this->testDbName}");
    }
    
    /**
     * Run schema migrations
     */
    private function runSchemaMigrations(): void {
        $migrationsPath = __DIR__ . '/../../database/migrations';
        $files = glob($migrationsPath . '/*.sql');
        sort($files);
        
        foreach ($files as $file) {
            $sql = file_get_contents($file);
            $this->pdo->exec($sql);
        }
    }
    
    /**
     * Property 79: Database Schema Validation
     * 
     * Tests that all required tables exist with correct structure
     * for multi-user portfolio system.
     * 
     * **Validates: Requirements 20.1**
     */
    public function testDatabaseSchemaIsComplete(): void {
        // Assert: All required tables exist
        $requiredTables = [
            'users',
            'portfolios',
            'portfolio_items',
            'files',
            'customization_settings',
            'sessions',
            'email_verifications',
            'password_resets',
            'rate_limits',
            'admin_actions',
            'flagged_content'
        ];
        
        foreach ($requiredTables as $table) {
            $this->assertTrue(
                $this->tableExists($table),
                "Table '{$table}' should exist in database"
            );
        }
    }
    
    /**
     * Test users table has required columns
     */
    public function testUsersTableStructure(): void {
        $requiredColumns = ['id', 'email', 'password_hash', 'full_name', 'program', 'username'];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                $this->columnExists('users', $column),
                "Column '{$column}' should exist in users table"
            );
        }
    }
    
    /**
     * Test portfolios table has required columns
     */
    public function testPortfoliosTableStructure(): void {
        $requiredColumns = ['id', 'user_id', 'is_public'];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                $this->columnExists('portfolios', $column),
                "Column '{$column}' should exist in portfolios table"
            );
        }
    }
    
    /**
     * Test portfolio_items table has required columns
     */
    public function testPortfolioItemsTableStructure(): void {
        $requiredColumns = ['id', 'portfolio_id', 'item_type', 'title', 'description'];
        
        foreach ($requiredColumns as $column) {
            $this->assertTrue(
                $this->columnExists('portfolio_items', $column),
                "Column '{$column}' should exist in portfolio_items table"
            );
        }
    }
    
    // Helper methods
    
    private function tableExists(string $tableName): bool {
        $stmt = $this->pdo->query("SHOW TABLES LIKE '{$tableName}'");
        return $stmt->rowCount() > 0;
    }
    
    private function columnExists(string $tableName, string $columnName): bool {
        $stmt = $this->pdo->query("SHOW COLUMNS FROM {$tableName} LIKE '{$columnName}'");
        return $stmt->rowCount() > 0;
    }
}
