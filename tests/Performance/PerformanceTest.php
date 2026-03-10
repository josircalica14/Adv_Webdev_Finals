<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/SessionManager.php';
require_once __DIR__ . '/../../includes/Auth/RateLimiter.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Showcase/ShowcaseManager.php';
require_once __DIR__ . '/../../includes/Export/ExportGenerator.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../helpers/TestDataStructures.php';
require_once __DIR__ . '/../bootstrap.php';

/**
 * Performance Test Suite
 * 
 * Tests system performance under various load conditions:
 * - Showcase page load with 100+ portfolios
 * - PDF generation with large portfolios
 * - Concurrent user sessions
 * - Database query performance
 * - Image loading and caching
 */
class PerformanceTest extends TestCase {
    private PDO $db;
    private AuthenticationManager $authManager;
    private PortfolioManager $portfolioManager;
    private ShowcaseManager $showcaseManager;
    private ExportGenerator $exportGenerator;
    private CustomizationEngine $customizationEngine;
    private FileStorageManager $fileManager;
    
    private string $testUploadPath;
    
    protected function setUp(): void {
        $this->db = Database::getInstance()->getConnection();
        $this->cleanDatabase();
        
        $this->testUploadPath = __DIR__ . '/../../uploads/perf_test_' . uniqid();
        if (!is_dir($this->testUploadPath)) {
            mkdir($this->testUploadPath, 0777, true);
        }
        
        $sessionManager = new SessionManager($this->db);
        $rateLimiter = new RateLimiter($this->db);
        $this->authManager = new AuthenticationManager($this->db, $sessionManager, $rateLimiter);
        $this->fileManager = new FileStorageManager($this->testUploadPath);
        $this->portfolioManager = new PortfolioManager($this->db, $this->fileManager);
        $this->showcaseManager = new ShowcaseManager($this->db);
        $this->customizationEngine = new CustomizationEngine($this->db);
        $this->exportGenerator = new ExportGenerator($this->db, $this->customizationEngine);
    }
    
    protected function tearDown(): void {
        $this->cleanDatabase();
        
        if (is_dir($this->testUploadPath)) {
            $this->recursiveRemoveDirectory($this->testUploadPath);
        }
    }
    
    /**
     * Test 1: Load Test Showcase with 100+ Portfolios
     * 
     * Creates 150 portfolios and measures showcase page load time.
     * Target: Should load within 3 seconds.
     */
    public function testShowcaseLoadWith100PlusPortfolios(): void {
        echo "\n[Performance Test] Creating 150 portfolios for load testing...\n";
        
        // Create 150 users with public portfolios
        $startSetup = microtime(true);
        
        for ($i = 1; $i <= 150; $i++) {
            $userId = $this->createTestUser(
                "user$i@example.com",
                'Pass123',
                "Test User $i",
                $i % 2 === 0 ? 'BSIT' : 'CSE'
            );
            
            // Set portfolio to public
            $this->portfolioManager->updateVisibility($userId, true);
            
            // Add 3 portfolio items per user
            for ($j = 1; $j <= 3; $j++) {
                $itemData = new PortfolioItemData();
                $itemData->itemType = 'project';
                $itemData->title = "Project $j for User $i";
                $itemData->description = "Description for project $j";
                $itemData->tags = ['PHP', 'Testing', 'Performance'];
                $itemData->isVisible = true;
                
                $this->portfolioManager->createItem($userId, $itemData->toArray());
            }
            
            if ($i % 50 === 0) {
                echo "  Created $i portfolios...\n";
            }
        }
        
        $setupTime = microtime(true) - $startSetup;
        echo "  Setup completed in " . round($setupTime, 2) . " seconds\n";
        
        // Test showcase page load (first page)
        echo "\n[Performance Test] Testing showcase page load...\n";
        $startLoad = microtime(true);
        
        $result = $this->showcaseManager->getPublicPortfolios(1, 20);
        
        $loadTime = microtime(true) - $startLoad;
        echo "  Showcase page loaded in " . round($loadTime, 3) . " seconds\n";
        
        $this->assertEquals(150, $result->total, 'Should have 150 total portfolios');
        $this->assertCount(20, $result->items, 'Should return 20 items per page');
        $this->assertLessThan(3.0, $loadTime, 'Showcase should load within 3 seconds');
        
        // Test pagination performance
        echo "\n[Performance Test] Testing pagination performance...\n";
        $startPagination = microtime(true);
        
        $page2 = $this->showcaseManager->getPublicPortfolios(2, 20);
        $page5 = $this->showcaseManager->getPublicPortfolios(5, 20);
        
        $paginationTime = microtime(true) - $startPagination;
        echo "  Pagination loaded in " . round($paginationTime, 3) . " seconds\n";
        
        $this->assertLessThan(2.0, $paginationTime, 'Pagination should be fast');
    }
    
    /**
     * Test 2: PDF Generation with Large Portfolios
     * 
     * Creates a portfolio with 50 items and measures PDF generation time.
     * Target: Should generate within 30 seconds.
     */
    public function testPDFGenerationWithLargePortfolio(): void {
        echo "\n[Performance Test] Creating large portfolio for PDF generation...\n";
        
        $userId = $this->createTestUser('pdftest@example.com', 'Pass123', 'PDF Test User', 'BSIT');
        
        // Create 50 portfolio items
        $startSetup = microtime(true);
        
        for ($i = 1; $i <= 50; $i++) {
            $itemData = new PortfolioItemData();
            $itemData->itemType = $i % 3 === 0 ? 'achievement' : 'project';
            $itemData->title = "Portfolio Item $i";
            $itemData->description = "This is a detailed description for portfolio item $i. " .
                                    "It contains multiple sentences to simulate real content. " .
                                    "The description should be long enough to test PDF rendering performance.";
            $itemData->itemDate = date('Y-m-d', strtotime("-$i days"));
            $itemData->tags = ['Tag1', 'Tag2', 'Tag3'];
            $itemData->links = ["https://example.com/project$i"];
            $itemData->isVisible = true;
            
            $this->portfolioManager->createItem($userId, $itemData->toArray());
        }
        
        $setupTime = microtime(true) - $startSetup;
        echo "  Created 50 portfolio items in " . round($setupTime, 2) . " seconds\n";
        
        // Set customization
        $settings = new CustomizationSettings();
        $settings->theme = 'professional';
        $settings->primaryColor = '#2c3e50';
        $settings->accentColor = '#3498db';
        $this->customizationEngine->updateSettings($userId, $settings);
        
        // Generate PDF
        echo "\n[Performance Test] Generating PDF...\n";
        $startGeneration = microtime(true);
        
        $pdfResult = $this->exportGenerator->generatePDF($userId);
        
        $generationTime = microtime(true) - $startGeneration;
        echo "  PDF generated in " . round($generationTime, 2) . " seconds\n";
        
        $this->assertTrue($pdfResult->success, 'PDF generation should succeed');
        $this->assertNotNull($pdfResult->filePath, 'PDF file path should be provided');
        $this->assertLessThan(30.0, $generationTime, 'PDF should generate within 30 seconds for 50 items');
        
        // Verify PDF file exists and has content
        if ($pdfResult->filePath && file_exists($pdfResult->filePath)) {
            $fileSize = filesize($pdfResult->filePath);
            echo "  PDF file size: " . round($fileSize / 1024, 2) . " KB\n";
            $this->assertGreaterThan(0, $fileSize, 'PDF should have content');
            
            // Clean up
            unlink($pdfResult->filePath);
        }
    }
    
    /**
     * Test 3: Concurrent User Sessions
     * 
     * Simulates multiple concurrent user sessions to test session management
     * and database connection handling.
     */
    public function testConcurrentUserSessions(): void {
        echo "\n[Performance Test] Testing concurrent user sessions...\n";
        
        $numUsers = 50;
        $users = [];
        
        // Create users
        echo "  Creating $numUsers users...\n";
        for ($i = 1; $i <= $numUsers; $i++) {
            $email = "concurrent$i@example.com";
            $users[] = [
                'email' => $email,
                'password' => 'Pass123',
                'userId' => $this->createTestUser($email, 'Pass123', "Concurrent User $i", 'BSIT')
            ];
        }
        
        // Simulate concurrent logins
        echo "  Simulating $numUsers concurrent logins...\n";
        $startLogins = microtime(true);
        $sessions = [];
        
        foreach ($users as $user) {
            $loginResult = $this->authManager->login($user['email'], $user['password']);
            $this->assertTrue($loginResult->success, "Login should succeed for {$user['email']}");
            $sessions[] = $loginResult->sessionToken;
        }
        
        $loginTime = microtime(true) - $startLogins;
        echo "  All logins completed in " . round($loginTime, 2) . " seconds\n";
        echo "  Average login time: " . round($loginTime / $numUsers * 1000, 2) . " ms\n";
        
        // Verify all sessions are valid
        echo "  Validating all sessions...\n";
        $startValidation = microtime(true);
        
        $sessionManager = new SessionManager($this->db);
        $validCount = 0;
        
        foreach ($sessions as $token) {
            $session = $sessionManager->validateSession($token);
            if ($session !== null) {
                $validCount++;
            }
        }
        
        $validationTime = microtime(true) - $startValidation;
        echo "  Session validation completed in " . round($validationTime, 2) . " seconds\n";
        
        $this->assertEquals($numUsers, $validCount, 'All sessions should be valid');
        $this->assertLessThan(2.0, $validationTime, 'Session validation should be fast');
    }
    
    /**
     * Test 4: Database Query Performance
     * 
     * Tests query performance with large datasets to verify indexes
     * are working correctly.
     */
    public function testDatabaseQueryPerformance(): void {
        echo "\n[Performance Test] Testing database query performance...\n";
        
        // Create 100 users with portfolios
        echo "  Creating 100 users with portfolios...\n";
        $userIds = [];
        
        for ($i = 1; $i <= 100; $i++) {
            $userId = $this->createTestUser(
                "dbperf$i@example.com",
                'Pass123',
                "DB Perf User $i",
                $i % 2 === 0 ? 'BSIT' : 'CSE'
            );
            $userIds[] = $userId;
            
            $this->portfolioManager->updateVisibility($userId, true);
            
            // Add 5 items per user
            for ($j = 1; $j <= 5; $j++) {
                $itemData = new PortfolioItemData();
                $itemData->itemType = 'project';
                $itemData->title = "Project $j";
                $itemData->description = "Description";
                $itemData->tags = ['PHP', 'MySQL', 'Performance'];
                $itemData->isVisible = true;
                
                $this->portfolioManager->createItem($userId, $itemData->toArray());
            }
        }
        
        // Test 1: User lookup by email (should use index)
        echo "\n  Test 1: User lookup by email...\n";
        $startQuery = microtime(true);
        
        for ($i = 1; $i <= 100; $i++) {
            $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute(["dbperf$i@example.com"]);
            $stmt->fetch();
        }
        
        $queryTime = microtime(true) - $startQuery;
        echo "    100 email lookups in " . round($queryTime, 3) . " seconds\n";
        $this->assertLessThan(0.5, $queryTime, 'Email lookups should be fast with index');
        
        // Test 2: Portfolio items by user (should use index)
        echo "\n  Test 2: Portfolio items lookup...\n";
        $startQuery = microtime(true);
        
        foreach ($userIds as $userId) {
            $items = $this->portfolioManager->getItems($userId);
            $this->assertCount(5, $items);
        }
        
        $queryTime = microtime(true) - $startQuery;
        echo "    100 portfolio lookups in " . round($queryTime, 3) . " seconds\n";
        $this->assertLessThan(1.0, $queryTime, 'Portfolio lookups should be fast with index');
        
        // Test 3: Public portfolio filtering (should use index)
        echo "\n  Test 3: Public portfolio filtering...\n";
        $startQuery = microtime(true);
        
        $stmt = $this->db->prepare('
            SELECT p.*, u.full_name, u.email, u.program 
            FROM portfolios p
            JOIN users u ON p.user_id = u.id
            WHERE p.is_public = 1
            LIMIT 20
        ');
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        $queryTime = microtime(true) - $startQuery;
        echo "    Public portfolio query in " . round($queryTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.1, $queryTime, 'Public portfolio query should be very fast');
        
        // Test 4: Search by tags (JSON search)
        echo "\n  Test 4: Tag search performance...\n";
        $startQuery = microtime(true);
        
        $searchCriteria = new SearchCriteria();
        $searchCriteria->tags = ['PHP'];
        
        $results = $this->showcaseManager->searchPortfolios($searchCriteria, 1, 20);
        
        $queryTime = microtime(true) - $startQuery;
        echo "    Tag search in " . round($queryTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.5, $queryTime, 'Tag search should be reasonably fast');
    }
    
    /**
     * Test 5: Image Loading and Caching
     * 
     * Tests file storage and retrieval performance.
     */
    public function testImageLoadingAndCaching(): void {
        echo "\n[Performance Test] Testing image loading and caching...\n";
        
        $userId = $this->createTestUser('imagetest@example.com', 'Pass123', 'Image Test', 'BSIT');
        
        // Create portfolio item
        $itemData = new PortfolioItemData();
        $itemData->itemType = 'project';
        $itemData->title = 'Image Test Project';
        $itemData->description = 'Testing image performance';
        $itemData->isVisible = true;
        
        $item = $this->portfolioManager->createItem($userId, $itemData);
        
        // Create test images
        echo "  Creating and uploading 10 test images...\n";
        $startUpload = microtime(true);
        $fileIds = [];
        
        for ($i = 1; $i <= 10; $i++) {
            // Create a simple test image
            $image = imagecreatetruecolor(800, 600);
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefill($image, 0, 0, $color);
            
            $tempFile = $this->testUploadPath . "/test_image_$i.jpg";
            imagejpeg($image, $tempFile, 85);
            imagedestroy($image);
            
            // Upload file
            $uploadedFile = new UploadedFile($tempFile, "test_image_$i.jpg", 'image/jpeg', filesize($tempFile));
            $fileRecord = $this->fileManager->uploadFile($uploadedFile, $userId, $item->id);
            
            $fileIds[] = $fileRecord->id;
        }
        
        $uploadTime = microtime(true) - $startUpload;
        echo "  Uploaded 10 images in " . round($uploadTime, 2) . " seconds\n";
        echo "  Average upload time: " . round($uploadTime / 10 * 1000, 2) . " ms per image\n";
        
        // Test file retrieval
        echo "\n  Testing file retrieval performance...\n";
        $startRetrieval = microtime(true);
        
        foreach ($fileIds as $fileId) {
            $file = $this->fileManager->getFile($fileId);
            $this->assertNotNull($file, 'File should be retrievable');
        }
        
        $retrievalTime = microtime(true) - $startRetrieval;
        echo "  Retrieved 10 files in " . round($retrievalTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.1, $retrievalTime, 'File retrieval should be very fast');
        
        // Test thumbnail generation
        echo "\n  Testing thumbnail generation...\n";
        $startThumbnail = microtime(true);
        
        $file = $this->fileManager->getFile($fileIds[0]);
        $thumbnailPath = $this->fileManager->generateThumbnail($file->filePath);
        
        $thumbnailTime = microtime(true) - $startThumbnail;
        echo "  Generated thumbnail in " . round($thumbnailTime * 1000, 2) . " ms\n";
        
        $this->assertNotNull($thumbnailPath, 'Thumbnail should be generated');
        $this->assertFileExists($thumbnailPath, 'Thumbnail file should exist');
        $this->assertLessThan(1.0, $thumbnailTime, 'Thumbnail generation should be fast');
    }
    
    /**
     * Test 6: Search Performance with Complex Queries
     * 
     * Tests search performance with various filter combinations.
     */
    public function testSearchPerformanceWithComplexQueries(): void {
        echo "\n[Performance Test] Testing search performance...\n";
        
        // Create 50 portfolios with varied data
        echo "  Creating 50 portfolios with varied data...\n";
        
        for ($i = 1; $i <= 50; $i++) {
            $userId = $this->createTestUser(
                "search$i@example.com",
                'Pass123',
                "Search User $i",
                $i % 2 === 0 ? 'BSIT' : 'CSE'
            );
            
            $stmt = $this->db->prepare('UPDATE users SET bio = ? WHERE id = ?');
            $stmt->execute(["Bio for user $i with keywords: developer, designer, engineer", $userId]);
            
            $this->portfolioManager->updateVisibility($userId, true);
            
            $itemData = new PortfolioItemData();
            $itemData->itemType = 'project';
            $itemData->title = "Project $i";
            $itemData->description = "Description with searchable content";
            $itemData->tags = $i % 3 === 0 ? ['PHP', 'MySQL'] : ['JavaScript', 'React'];
            $itemData->isVisible = true;
            
            $this->portfolioManager->createItem($userId, $itemData->toArray());
        }
        
        // Test 1: Simple keyword search
        echo "\n  Test 1: Simple keyword search...\n";
        $startSearch = microtime(true);
        
        $criteria = new SearchCriteria();
        $criteria->query = 'developer';
        $results = $this->showcaseManager->searchPortfolios($criteria, 1, 20);
        
        $searchTime = microtime(true) - $startSearch;
        echo "    Search completed in " . round($searchTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.5, $searchTime, 'Simple search should be fast');
        
        // Test 2: Program filter
        echo "\n  Test 2: Program filter...\n";
        $startSearch = microtime(true);
        
        $results = $this->showcaseManager->filterByProgram('BSIT', 1, 20);
        
        $searchTime = microtime(true) - $startSearch;
        echo "    Filter completed in " . round($searchTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.3, $searchTime, 'Program filter should be very fast');
        
        // Test 3: Combined search and filter
        echo "\n  Test 3: Combined search and filter...\n";
        $startSearch = microtime(true);
        
        $criteria = [
            'query' => 'developer',
            'program' => 'BSIT'
        ];
        $results = $this->showcaseManager->searchAndFilter($criteria, 1, 20);
        
        $searchTime = microtime(true) - $startSearch;
        echo "    Combined search completed in " . round($searchTime * 1000, 2) . " ms\n";
        $this->assertLessThan(0.5, $searchTime, 'Combined search should be reasonably fast');
    }
    
    // Helper methods
    
    private function createTestUser(string $email, string $password, string $fullName, string $program): int {
        $result = $this->authManager->register($email, $password, $fullName, $program);
        if (!$result->success) {
            throw new Exception("Failed to create test user: " . $result->error);
        }
        return $result->userId;
    }
    
    private function cleanDatabase(): void {
        $tables = [
            'admin_actions',
            'flagged_content',
            'files',
            'portfolio_items',
            'customization_settings',
            'portfolios',
            'sessions',
            'email_verifications',
            'password_resets',
            'rate_limits',
            'users'
        ];
        
        foreach ($tables as $table) {
            $this->db->exec("DELETE FROM $table");
        }
    }
    
    private function recursiveRemoveDirectory(string $directory): void {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $path = $directory . '/' . $file;
            is_dir($path) ? $this->recursiveRemoveDirectory($path) : unlink($path);
        }
        rmdir($directory);
    }
}
