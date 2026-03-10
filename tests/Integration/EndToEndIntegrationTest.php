<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/SessionManager.php';
require_once __DIR__ . '/../../includes/Auth/RateLimiter.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';
require_once __DIR__ . '/../../includes/Export/ExportGenerator.php';
require_once __DIR__ . '/../../includes/Showcase/ShowcaseManager.php';
require_once __DIR__ . '/../../includes/Admin/AdminManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../helpers/TestDataStructures.php';
require_once __DIR__ . '/../bootstrap.php';

/**
 * End-to-End Integration Tests for Multi-User Portfolio Platform
 * 
 * Tests complete user flows from registration through portfolio management,
 * customization, export, showcase browsing, and admin moderation.
 */
class EndToEndIntegrationTest extends TestCase {
    private PDO $db;
    private AuthenticationManager $authManager;
    private PortfolioManager $portfolioManager;
    private CustomizationEngine $customizationEngine;
    private ExportGenerator $exportGenerator;
    private ShowcaseManager $showcaseManager;
    private AdminManager $adminManager;
    private FileStorageManager $fileManager;
    
    private string $testUploadPath;
    
    protected function setUp(): void {
        // Set up test database
        $config = getTestDatabaseConfig();
        $this->db = Database::getInstance()->getConnection();
        
        // Clean database
        $this->cleanDatabase();
        
        // Set up test upload directory
        $this->testUploadPath = __DIR__ . '/../../uploads/test_' . uniqid();
        if (!is_dir($this->testUploadPath)) {
            mkdir($this->testUploadPath, 0777, true);
        }
        
        // Initialize managers
        $sessionManager = new SessionManager($this->db);
        $rateLimiter = new RateLimiter($this->db);
        $this->authManager = new AuthenticationManager($this->db, $sessionManager, $rateLimiter);
        $this->fileManager = new FileStorageManager($this->testUploadPath);
        $this->portfolioManager = new PortfolioManager($this->db, $this->fileManager);
        $this->customizationEngine = new CustomizationEngine($this->db);
        $this->exportGenerator = new ExportGenerator($this->db, $this->customizationEngine);
        $this->showcaseManager = new ShowcaseManager($this->db);
        $this->adminManager = new AdminManager($this->db);
    }
    
    protected function tearDown(): void {
        $this->cleanDatabase();
        
        // Clean up test upload directory
        if (is_dir($this->testUploadPath)) {
            $this->recursiveRemoveDirectory($this->testUploadPath);
        }
    }
    
    /**
     * Test 1: Complete user registration and login flow
     * 
     * Flow:
     * 1. Register new user
     * 2. Verify account created
     * 3. Login with credentials
     * 4. Verify session created
     * 5. Logout
     * 6. Verify session terminated
     */
    public function testCompleteRegistrationAndLoginFlow(): void {
        // Step 1: Register new user
        $email = 'test@example.com';
        $password = 'SecurePass123';
        $fullName = 'Test User';
        $program = 'BSIT';
        
        $registrationResult = $this->authManager->register($email, $password, $fullName, $program);
        
        $this->assertTrue($registrationResult->success, 'Registration should succeed');
        $this->assertNotNull($registrationResult->userId, 'User ID should be assigned');
        
        // Step 2: Verify account created in database
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$registrationResult->userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotEmpty($user, 'User should exist in database');
        $this->assertEquals($email, $user['email']);
        $this->assertEquals($fullName, $user['full_name']);
        $this->assertEquals($program, $user['program']);
        $this->assertNotEquals($password, $user['password_hash'], 'Password should be hashed');
        
        // Step 3: Login with credentials
        $loginResult = $this->authManager->login($email, $password);
        
        $this->assertTrue($loginResult->success, 'Login should succeed');
        $this->assertNotNull($loginResult->sessionToken, 'Session token should be created');
        $this->assertNotNull($loginResult->user, 'User object should be returned');
        
        // Step 4: Verify session created
        $stmt = $this->db->prepare('SELECT * FROM sessions WHERE session_token = ?');
        $stmt->execute([$loginResult->sessionToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotEmpty($session, 'Session should exist in database');
        $this->assertEquals($registrationResult->userId, $session['user_id']);
        
        // Step 5: Logout
        $_SESSION['session_token'] = $loginResult->sessionToken;
        $this->authManager->logout();
        
        // Step 6: Verify session terminated
        $stmt = $this->db->prepare('SELECT * FROM sessions WHERE session_token = ?');
        $stmt->execute([$loginResult->sessionToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertEmpty($session, 'Session should be deleted after logout');
    }
    
    /**
     * Test 2: Complete portfolio creation, editing, and deletion flow
     * 
     * Flow:
     * 1. Create user and login
     * 2. Create portfolio item
     * 3. Verify item created
     * 4. Edit portfolio item
     * 5. Verify changes persisted
     * 6. Delete portfolio item
     * 7. Verify item deleted
     */
    public function testCompletePortfolioManagementFlow(): void {
        // Step 1: Create user and login
        $userId = $this->createTestUser('portfolio@example.com', 'Pass123', 'Portfolio User', 'CSE');
        
        // Step 2: Create portfolio item
        $itemData = new PortfolioItemData();
        $itemData->itemType = 'project';
        $itemData->title = 'Test Project';
        $itemData->description = 'A test project description';
        $itemData->itemDate = '2024-01-15';
        $itemData->tags = ['PHP', 'Testing'];
        $itemData->links = ['https://github.com/test/project'];
        $itemData->isVisible = true;
        
        $result = $this->portfolioManager->createItem($userId, $itemData->toArray());
        
        $this->assertTrue($result['success'], 'Portfolio item creation should succeed');
        $this->assertNotNull($result['item'], 'Portfolio item should be returned');
        $this->assertEquals('Test Project', $result['item']['title']);
        $this->assertEquals('project', $result['item']['item_type']);
        
        $itemId = $result['item']['id'];
        
        // Step 3: Verify item created in database
        $items = $this->portfolioManager->getItems($userId);
        $this->assertCount(1, $items, 'Should have 1 portfolio item');
        $this->assertEquals('Test Project', $items[0]['title']);
        
        // Step 4: Edit portfolio item
        $itemData->title = 'Updated Test Project';
        $itemData->description = 'Updated description';
        $itemData->tags = ['PHP', 'Testing', 'Integration'];
        
        $updateResult = $this->portfolioManager->updateItem($itemId, $userId, $itemData->toArray());
        $this->assertTrue($updateResult['success'], 'Update should succeed');
        
        // Step 5: Verify changes persisted
        $items = $this->portfolioManager->getItems($userId);
        $this->assertEquals('Updated Test Project', $items[0]['title']);
        $this->assertEquals('Updated description', $items[0]['description']);
        $this->assertCount(3, $items[0]['tags']);
        
        // Step 6: Delete portfolio item
        $deleteResult = $this->portfolioManager->deleteItem($itemId, $userId);
        $this->assertTrue($deleteResult['success'], 'Delete should succeed');
        
        // Step 7: Verify item deleted
        $items = $this->portfolioManager->getItems($userId);
        $this->assertCount(0, $items, 'Should have 0 portfolio items after deletion');
    }
    
    /**
     * Test 3: Complete customization and preview flow
     * 
     * Flow:
     * 1. Create user
     * 2. Get default customization settings
     * 3. Update customization settings
     * 4. Verify settings persisted
     * 5. Generate CSS from settings
     * 6. Reset to defaults
     * 7. Verify reset successful
     */
    public function testCompleteCustomizationFlow(): void {
        // Step 1: Create user
        $userId = $this->createTestUser('custom@example.com', 'Pass123', 'Custom User', 'BSIT');
        
        // Step 2: Get default customization settings
        $defaultSettings = $this->customizationEngine->getSettings($userId);
        $this->assertNotNull($defaultSettings);
        $this->assertEquals('default', $defaultSettings->theme);
        $this->assertEquals('grid', $defaultSettings->layout);
        
        // Step 3: Update customization settings
        $newSettings = new CustomizationSettings();
        $newSettings->theme = 'dark';
        $newSettings->layout = 'timeline';
        $newSettings->primaryColor = '#2c3e50';
        $newSettings->accentColor = '#e67e22';
        $newSettings->headingFont = 'Montserrat';
        $newSettings->bodyFont = 'Lato';
        
        $updateResult = $this->customizationEngine->updateSettings($userId, $newSettings);
        $this->assertTrue($updateResult, 'Customization update should succeed');
        
        // Step 4: Verify settings persisted
        $savedSettings = $this->customizationEngine->getSettings($userId);
        $this->assertEquals('dark', $savedSettings->theme);
        $this->assertEquals('timeline', $savedSettings->layout);
        $this->assertEquals('#2c3e50', $savedSettings->primaryColor);
        $this->assertEquals('#e67e22', $savedSettings->accentColor);
        $this->assertEquals('Montserrat', $savedSettings->headingFont);
        $this->assertEquals('Lato', $savedSettings->bodyFont);
        
        // Step 5: Generate CSS from settings
        $css = $this->customizationEngine->generateCSS($savedSettings);
        $this->assertStringContainsString('#2c3e50', $css, 'CSS should contain primary color');
        $this->assertStringContainsString('#e67e22', $css, 'CSS should contain accent color');
        $this->assertStringContainsString('Montserrat', $css, 'CSS should contain heading font');
        $this->assertStringContainsString('Lato', $css, 'CSS should contain body font');
        
        // Step 6: Reset to defaults
        $resetResult = $this->customizationEngine->resetToDefaults($userId);
        $this->assertTrue($resetResult, 'Reset should succeed');
        
        // Step 7: Verify reset successful
        $resetSettings = $this->customizationEngine->getSettings($userId);
        $this->assertEquals('default', $resetSettings->theme);
        $this->assertEquals('grid', $resetSettings->layout);
    }
    
    /**
     * Test 4: Complete PDF export flow
     * 
     * Flow:
     * 1. Create user with portfolio items
     * 2. Set customization
     * 3. Generate PDF with all items
     * 4. Verify PDF created successfully
     * 5. Generate PDF with selected items
     * 6. Verify selective export works
     */
    public function testCompletePDFExportFlow(): void {
        // Step 1: Create user with portfolio items
        $userId = $this->createTestUser('export@example.com', 'Pass123', 'Export User', 'BSIT');
        
        // Create multiple portfolio items
        $itemIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $itemData = new PortfolioItemData();
            $itemData->itemType = 'project';
            $itemData->title = "Project $i";
            $itemData->description = "Description for project $i";
            $itemData->isVisible = true;
            
            $result = $this->portfolioManager->createItem($userId, $itemData->toArray());
            if ($result['success']) {
                $itemIds[] = $result['item']['id'];
            }
        }
        
        // Step 2: Set customization
        $settings = new CustomizationSettings();
        $settings->theme = 'professional';
        $settings->primaryColor = '#34495e';
        $this->customizationEngine->updateSettings($userId, $settings);
        
        // Step 3: Generate PDF with all items
        $pdfResult = $this->exportGenerator->generatePDF($userId);
        
        $this->assertTrue($pdfResult->success, 'PDF generation should succeed');
        $this->assertNotNull($pdfResult->filePath, 'PDF file path should be provided');
        $this->assertFileExists($pdfResult->filePath, 'PDF file should exist');
        $this->assertLessThan(30, $pdfResult->generationTime, 'PDF should generate within 30 seconds');
        
        // Clean up generated PDF
        if (file_exists($pdfResult->filePath)) {
            unlink($pdfResult->filePath);
        }
        
        // Step 4: Generate PDF with selected items (first 2 items)
        $selectedItemIds = array_slice($itemIds, 0, 2);
        $selectivePdfResult = $this->exportGenerator->generatePDF($userId, $selectedItemIds);
        
        $this->assertTrue($selectivePdfResult->success, 'Selective PDF generation should succeed');
        $this->assertNotNull($selectivePdfResult->filePath, 'PDF file path should be provided');
        
        // Clean up
        if (file_exists($selectivePdfResult->filePath)) {
            unlink($selectivePdfResult->filePath);
        }
    }
    
    /**
     * Test 5: Complete showcase browsing and search flow
     * 
     * Flow:
     * 1. Create multiple users with public portfolios
     * 2. Browse showcase (pagination)
     * 3. Search by keyword
     * 4. Filter by program
     * 5. Sort portfolios
     * 6. Verify result counts
     */
    public function testCompleteShowcaseBrowsingAndSearchFlow(): void {
        // Step 1: Create multiple users with public portfolios
        $users = [
            ['email' => 'user1@example.com', 'name' => 'Alice Johnson', 'program' => 'BSIT', 'bio' => 'Web developer'],
            ['email' => 'user2@example.com', 'name' => 'Bob Smith', 'program' => 'CSE', 'bio' => 'AI researcher'],
            ['email' => 'user3@example.com', 'name' => 'Carol Davis', 'program' => 'BSIT', 'bio' => 'Mobile developer'],
            ['email' => 'user4@example.com', 'name' => 'David Wilson', 'program' => 'CSE', 'bio' => 'Data scientist'],
        ];
        
        foreach ($users as $userData) {
            $userId = $this->createTestUser($userData['email'], 'Pass123', $userData['name'], $userData['program']);
            
            // Update bio
            $stmt = $this->db->prepare('UPDATE users SET bio = ? WHERE id = ?');
            $stmt->execute([$userData['bio'], $userId]);
            
            // Set portfolio to public
            $this->portfolioManager->updateVisibility($userId, true);
            
            // Add a portfolio item with tags
            $itemData = new PortfolioItemData();
            $itemData->itemType = 'project';
            $itemData->title = $userData['name'] . "'s Project";
            $itemData->description = 'A sample project';
            $itemData->tags = $userData['program'] === 'BSIT' ? ['PHP', 'Web'] : ['Python', 'AI'];
            $itemData->isVisible = true;
            
            $this->portfolioManager->createItem($userId, $itemData->toArray());
        }
        
        // Step 2: Browse showcase with pagination
        $page1 = $this->showcaseManager->getPublicPortfolios(1, 2);
        $this->assertEquals(4, $page1['total'], 'Should have 4 total portfolios');
        $this->assertCount(2, $page1['items'], 'Page 1 should have 2 items');
        $this->assertEquals(2, $page1['totalPages'], 'Should have 2 pages');
        
        $page2 = $this->showcaseManager->getPublicPortfolios(2, 2);
        $this->assertCount(2, $page2['items'], 'Page 2 should have 2 items');
        
        // Step 3: Search by keyword
        $searchResults = $this->showcaseManager->searchPortfolios('developer', 1, 20);
        $this->assertEquals(2, $searchResults['total'], 'Should find 2 portfolios with "developer"');
        
        // Step 4: Filter by program
        $bsitResults = $this->showcaseManager->filterByProgram('BSIT', 1, 20);
        $this->assertEquals(2, $bsitResults['total'], 'Should have 2 BSIT portfolios');
        
        $cseResults = $this->showcaseManager->filterByProgram('CSE', 1, 20);
        $this->assertEquals(2, $cseResults['total'], 'Should have 2 CSE portfolios');
        
        // Step 5: Sort portfolios
        $allPortfolios = $this->showcaseManager->getPublicPortfolios(1, 20);
        $sortedByName = $this->showcaseManager->sortPortfolios($allPortfolios['items'], 'name');
        
        $this->assertEquals('Alice Johnson', $sortedByName[0]['user']['full_name'], 'First should be Alice');
        $this->assertEquals('David Wilson', $sortedByName[3]['user']['full_name'], 'Last should be David');
    }
    
    /**
     * Test 6: Complete admin moderation flow
     * 
     * Flow:
     * 1. Create admin user
     * 2. Create regular user with portfolio
     * 3. Admin views all portfolios
     * 4. Admin flags content
     * 5. Admin hides content
     * 6. Verify content hidden from public
     * 7. Admin restores content
     * 8. Verify action logging
     */
    public function testCompleteAdminModerationFlow(): void {
        // Step 1: Create admin user
        $adminId = $this->createTestUser('admin@example.com', 'AdminPass123', 'Admin User', 'BSIT');
        $stmt = $this->db->prepare('UPDATE users SET is_admin = 1 WHERE id = ?');
        $stmt->execute([$adminId]);
        
        // Step 2: Create regular user with portfolio
        $userId = $this->createTestUser('regular@example.com', 'Pass123', 'Regular User', 'CSE');
        $this->portfolioManager->updateVisibility($userId, true);
        
        $itemData = new PortfolioItemData();
        $itemData->itemType = 'project';
        $itemData->title = 'Test Project';
        $itemData->description = 'Potentially inappropriate content';
        $itemData->isVisible = true;
        
        $item = $this->portfolioManager->createItem($userId, $itemData);
        
        // Step 3: Admin views all portfolios
        $allPortfolios = $this->adminManager->getAllPortfolios(1, 20);
        $this->assertGreaterThanOrEqual(1, $allPortfolios->total, 'Admin should see all portfolios');
        
        // Step 4: Admin flags content
        $flagResult = $this->adminManager->flagItem($item->id, 'Inappropriate content');
        $this->assertTrue($flagResult, 'Flagging should succeed');
        
        $flaggedContent = $this->adminManager->getFlaggedContent();
        $this->assertCount(1, $flaggedContent, 'Should have 1 flagged item');
        
        // Step 5: Admin hides content
        $hideResult = $this->adminManager->hideItem($item->id, 'Violates community guidelines');
        $this->assertTrue($hideResult, 'Hiding should succeed');
        
        // Step 6: Verify content hidden from public
        $publicPortfolio = $this->portfolioManager->getPublicPortfolio('regular@example.com');
        $visibleItems = array_filter($publicPortfolio->items, fn($i) => $i->isVisible);
        $this->assertCount(0, $visibleItems, 'Hidden item should not appear in public view');
        
        // Step 7: Admin restores content
        $restoreResult = $this->adminManager->unhideItem($item->id);
        $this->assertTrue($restoreResult, 'Restoration should succeed');
        
        // Step 8: Verify action logging
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM admin_actions WHERE admin_id = ?');
        $stmt->execute([$adminId]);
        $logCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        $this->assertGreaterThanOrEqual(3, $logCount, 'Should have logged flag, hide, and unhide actions');
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
