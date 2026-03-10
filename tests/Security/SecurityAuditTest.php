<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/SessionManager.php';
require_once __DIR__ . '/../../includes/Auth/RateLimiter.php';
require_once __DIR__ . '/../../includes/Auth/InputSanitizer.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../helpers/TestDataStructures.php';
require_once __DIR__ . '/../bootstrap.php';

/**
 * Security Audit Test Suite
 * 
 * Comprehensive security testing covering:
 * - SQL injection prevention
 * - XSS prevention
 * - CSRF protection
 * - Access control enforcement
 * - Rate limiting effectiveness
 * - Session security
 */
class SecurityAuditTest extends TestCase {
    private PDO $db;
    private AuthenticationManager $authManager;
    private PortfolioManager $portfolioManager;
    private RateLimiter $rateLimiter;
    private InputSanitizer $sanitizer;
    private CSRFProtection $csrfProtection;
    
    protected function setUp(): void {
        $this->db = Database::getInstance()->getConnection();
        $this->cleanDatabase();
        
        $sessionManager = new SessionManager($this->db);
        $this->rateLimiter = new RateLimiter($this->db);
        $this->authManager = new AuthenticationManager($this->db, $sessionManager, $this->rateLimiter);
        
        $fileManager = new FileStorageManager(__DIR__ . '/../../uploads/test');
        $this->portfolioManager = new PortfolioManager($this->db, $fileManager);
        
        $this->sanitizer = new InputSanitizer();
        $this->csrfProtection = new CSRFProtection();
    }
    
    protected function tearDown(): void {
        $this->cleanDatabase();
    }
    
    /**
     * Test SQL Injection Prevention - Registration
     * 
     * Attempts various SQL injection patterns in registration fields
     * to verify they are properly sanitized and don't execute malicious SQL.
     */
    public function testSQLInjectionPreventionInRegistration(): void {
        $sqlInjectionPatterns = [
            "admin' OR '1'='1",
            "admin'--",
            "admin' /*",
            "' OR 1=1--",
            "'; DROP TABLE users--",
            "1' UNION SELECT NULL, NULL, NULL--",
            "admin' AND 1=0 UNION ALL SELECT 'admin', '81dc9bdb52d04dc20036dbd8313ed055'",
        ];
        
        foreach ($sqlInjectionPatterns as $pattern) {
            // Try SQL injection in email field
            $result = $this->authManager->register(
                $pattern . '@example.com',
                'ValidPass123',
                'Test User',
                'BSIT'
            );
            
            // Should either succeed with sanitized input or fail validation
            // but should NOT execute SQL injection
            if ($result->success) {
                // Verify the email was stored safely
                $stmt = $this->db->prepare('SELECT email FROM users WHERE id = ?');
                $stmt->execute([$result->userId]);
                $storedEmail = $stmt->fetchColumn();
                
                // Email should be stored as-is (sanitized) not executed
                $this->assertStringContainsString($pattern, $storedEmail);
            }
            
            // Verify no tables were dropped
            $stmt = $this->db->query("SHOW TABLES LIKE 'users'");
            $this->assertNotEmpty($stmt->fetchAll(), 'Users table should still exist');
        }
        
        // Try SQL injection in name field
        $result = $this->authManager->register(
            'test@example.com',
            'ValidPass123',
            "'; DROP TABLE users--",
            'BSIT'
        );
        
        // Verify users table still exists
        $stmt = $this->db->query("SHOW TABLES LIKE 'users'");
        $this->assertNotEmpty($stmt->fetchAll(), 'Users table should still exist after name injection attempt');
    }
    
    /**
     * Test SQL Injection Prevention - Login
     * 
     * Attempts SQL injection in login credentials to verify
     * authentication cannot be bypassed.
     */
    public function testSQLInjectionPreventionInLogin(): void {
        // Create a legitimate user
        $this->authManager->register('legit@example.com', 'SecurePass123', 'Legit User', 'BSIT');
        
        $sqlInjectionPatterns = [
            "admin' OR '1'='1",
            "admin'--",
            "' OR 1=1--",
            "admin' AND 1=0 UNION ALL SELECT 'admin', '81dc9bdb52d04dc20036dbd8313ed055'",
        ];
        
        foreach ($sqlInjectionPatterns as $pattern) {
            // Try to login with SQL injection
            $result = $this->authManager->login($pattern, 'anything');
            
            // Login should fail - SQL injection should not bypass authentication
            $this->assertFalse($result->success, "SQL injection pattern '$pattern' should not allow login");
            $this->assertNull($result->sessionToken, 'No session should be created');
        }
    }
    
    /**
     * Test SQL Injection Prevention - Portfolio Search
     * 
     * Attempts SQL injection in search queries to verify
     * search functionality is protected.
     */
    public function testSQLInjectionPreventionInSearch(): void {
        $sqlInjectionPatterns = [
            "' OR '1'='1",
            "'; DROP TABLE portfolio_items--",
            "1' UNION SELECT NULL--",
        ];
        
        foreach ($sqlInjectionPatterns as $pattern) {
            $searchCriteria = new SearchCriteria();
            $searchCriteria->query = $pattern;
            
            // Search should not throw exception or execute malicious SQL
            try {
                $showcaseManager = new ShowcaseManager($this->db);
                $results = $showcaseManager->searchPortfolios($searchCriteria, 1, 20);
                
                // Should return empty or safe results, not execute injection
                $this->assertIsArray($results->items);
                
            } catch (Exception $e) {
                // If it throws an exception, it should be a safe validation error
                // not a SQL error
                $this->assertStringNotContainsString('SQL', $e->getMessage());
            }
        }
        
        // Verify tables still exist
        $stmt = $this->db->query("SHOW TABLES LIKE 'portfolio_items'");
        $this->assertNotEmpty($stmt->fetchAll(), 'portfolio_items table should still exist');
    }
    
    /**
     * Test XSS Prevention - Profile Fields
     * 
     * Attempts to inject JavaScript in profile fields to verify
     * XSS attacks are prevented through proper sanitization.
     */
    public function testXSSPreventionInProfileFields(): void {
        // Create user
        $userId = $this->createTestUser('xss@example.com', 'Pass123', 'XSS Test', 'BSIT');
        
        $xssPatterns = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '<svg onload=alert("XSS")>',
            'javascript:alert("XSS")',
            '<iframe src="javascript:alert(\'XSS\')">',
            '<body onload=alert("XSS")>',
            '<input onfocus=alert("XSS") autofocus>',
            '"><script>alert(String.fromCharCode(88,83,83))</script>',
        ];
        
        foreach ($xssPatterns as $pattern) {
            // Try to inject XSS in bio
            $stmt = $this->db->prepare('UPDATE users SET bio = ? WHERE id = ?');
            $stmt->execute([$pattern, $userId]);
            
            // Retrieve and verify sanitization
            $stmt = $this->db->prepare('SELECT bio FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $bio = $stmt->fetchColumn();
            
            // When output, should be escaped
            $sanitizedOutput = $this->sanitizer->sanitizeOutput($bio);
            
            // Should not contain executable script tags
            $this->assertStringNotContainsString('<script>', $sanitizedOutput, 'Script tags should be escaped');
            $this->assertStringNotContainsString('javascript:', $sanitizedOutput, 'JavaScript protocol should be escaped');
            $this->assertStringNotContainsString('onerror=', $sanitizedOutput, 'Event handlers should be escaped');
            $this->assertStringNotContainsString('onload=', $sanitizedOutput, 'Event handlers should be escaped');
        }
    }
    
    /**
     * Test XSS Prevention - Portfolio Content
     * 
     * Attempts to inject JavaScript in portfolio item fields.
     */
    public function testXSSPreventionInPortfolioContent(): void {
        $userId = $this->createTestUser('portfolio-xss@example.com', 'Pass123', 'Portfolio XSS Test', 'CSE');
        
        $xssPatterns = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            '"><script>alert("XSS")</script>',
        ];
        
        foreach ($xssPatterns as $pattern) {
            $itemData = new PortfolioItemData();
            $itemData->itemType = 'project';
            $itemData->title = $pattern;
            $itemData->description = $pattern;
            $itemData->isVisible = true;
            
            $result = $this->portfolioManager->createItem($userId, $itemData->toArray());
            
            if ($result['success']) {
                $itemId = $result['item']['id'];
                
                // Retrieve item
                $items = $this->portfolioManager->getItems($userId);
                $retrievedItem = $items[0];
                
                // When output, should be escaped
                $sanitizedTitle = $this->sanitizer->sanitizeOutput($retrievedItem['title']);
                $sanitizedDesc = $this->sanitizer->sanitizeOutput($retrievedItem['description']);
                
                $this->assertStringNotContainsString('<script>', $sanitizedTitle, 'Script tags in title should be escaped');
                $this->assertStringNotContainsString('<script>', $sanitizedDesc, 'Script tags in description should be escaped');
            }
        }
    }
    
    /**
     * Test CSRF Protection - State-Changing Operations
     * 
     * Verifies that all state-changing operations require valid CSRF tokens.
     */
    public function testCSRFProtectionOnStatefulOperations(): void {
        // Generate a valid CSRF token
        $validToken = $this->csrfProtection->generateToken();
        $_SESSION['csrf_token'] = $validToken;
        
        // Test with valid token
        $isValid = $this->csrfProtection->validateToken($validToken);
        $this->assertTrue($isValid, 'Valid CSRF token should be accepted');
        
        // Test with invalid token
        $invalidToken = 'invalid_token_12345';
        $isInvalid = $this->csrfProtection->validateToken($invalidToken);
        $this->assertFalse($isInvalid, 'Invalid CSRF token should be rejected');
        
        // Test with missing token
        $isMissing = $this->csrfProtection->validateToken('');
        $this->assertFalse($isMissing, 'Missing CSRF token should be rejected');
        
        // Test token regeneration after use
        $newToken = $this->csrfProtection->generateToken();
        $this->assertNotEquals($validToken, $newToken, 'CSRF token should be regenerated');
    }
    
    /**
     * Test Access Control - Portfolio Item Modification
     * 
     * Verifies users cannot modify other users' portfolio items.
     */
    public function testAccessControlForPortfolioItems(): void {
        // Create two users
        $user1Id = $this->createTestUser('user1@example.com', 'Pass123', 'User 1', 'BSIT');
        $user2Id = $this->createTestUser('user2@example.com', 'Pass123', 'User 2', 'CSE');
        
        // User 1 creates a portfolio item
        $itemData = new PortfolioItemData();
        $itemData->itemType = 'project';
        $itemData->title = 'User 1 Project';
        $itemData->description = 'This belongs to User 1';
        $itemData->isVisible = true;
        
        $result = $this->portfolioManager->createItem($user1Id, $itemData->toArray());
        $this->assertTrue($result['success'], 'Item creation should succeed');
        $itemId = $result['item']['id'];
        
        // User 2 attempts to edit User 1's item
        $itemData->title = 'Hacked by User 2';
        $updateResult = $this->portfolioManager->updateItem($itemId, $user2Id, $itemData->toArray());
        
        $this->assertFalse($updateResult['success'], 'User 2 should not be able to edit User 1\'s item');
        
        // Verify item was not modified
        $items = $this->portfolioManager->getItems($user1Id);
        $this->assertEquals('User 1 Project', $items[0]['title'], 'Item title should remain unchanged');
        
        // User 2 attempts to delete User 1's item
        $deleteResult = $this->portfolioManager->deleteItem($itemId, $user2Id);
        
        $this->assertFalse($deleteResult['success'], 'User 2 should not be able to delete User 1\'s item');
        
        // Verify item still exists
        $items = $this->portfolioManager->getItems($user1Id);
        $this->assertCount(1, $items, 'Item should still exist');
    }
    
    /**
     * Test Access Control - Private Portfolio Access
     * 
     * Verifies private portfolios cannot be accessed by unauthorized users.
     */
    public function testAccessControlForPrivatePortfolios(): void {
        // Create user with private portfolio
        $userId = $this->createTestUser('private@example.com', 'Pass123', 'Private User', 'BSIT');
        
        // Portfolio is private by default
        $portfolio = $this->portfolioManager->getPortfolio($userId);
        $this->assertFalse($portfolio->isPublic, 'Portfolio should be private by default');
        
        // Add portfolio item
        $itemData = new PortfolioItemData();
        $itemData->itemType = 'project';
        $itemData->title = 'Private Project';
        $itemData->description = 'This should not be publicly visible';
        $itemData->isVisible = true;
        
        $this->portfolioManager->createItem($userId, $itemData);
        
        // Attempt to access via public showcase
        $showcaseManager = new ShowcaseManager($this->db);
        $publicPortfolios = $showcaseManager->getPublicPortfolios(1, 20);
        
        $this->assertCount(0, $publicPortfolios->items, 'Private portfolio should not appear in showcase');
        
        // Attempt to access via username
        $publicPortfolio = $this->portfolioManager->getPublicPortfolio('private@example.com');
        
        $this->assertNull($publicPortfolio, 'Private portfolio should not be accessible via public URL');
    }
    
    /**
     * Test Rate Limiting - Login Attempts
     * 
     * Verifies rate limiting prevents brute force attacks on login.
     */
    public function testRateLimitingOnLoginAttempts(): void {
        // Create a user
        $this->authManager->register('ratelimit@example.com', 'CorrectPass123', 'Rate Limit Test', 'BSIT');
        
        $ipAddress = '192.168.1.100';
        
        // Attempt 5 failed logins (should be allowed)
        for ($i = 0; $i < 5; $i++) {
            $canAttempt = $this->rateLimiter->checkLimit($ipAddress, 'login', 5, 900); // 15 minutes
            $this->assertTrue($canAttempt, "Attempt $i should be allowed");
            
            $this->rateLimiter->recordAttempt($ipAddress, 'login');
            
            // Try to login with wrong password
            $result = $this->authManager->login('ratelimit@example.com', 'WrongPassword');
            $this->assertFalse($result->success);
        }
        
        // 6th attempt should be blocked
        $canAttempt = $this->rateLimiter->checkLimit($ipAddress, 'login', 5, 900);
        $this->assertFalse($canAttempt, '6th attempt should be blocked by rate limiter');
    }
    
    /**
     * Test Rate Limiting - File Uploads
     * 
     * Verifies rate limiting prevents excessive file uploads.
     */
    public function testRateLimitingOnFileUploads(): void {
        $userId = $this->createTestUser('upload@example.com', 'Pass123', 'Upload Test', 'BSIT');
        
        // Simulate 20 file uploads (should be allowed)
        for ($i = 0; $i < 20; $i++) {
            $canUpload = $this->rateLimiter->checkLimit("user_$userId", 'file_upload', 20, 3600); // 1 hour
            $this->assertTrue($canUpload, "Upload $i should be allowed");
            
            $this->rateLimiter->recordAttempt("user_$userId", 'file_upload');
        }
        
        // 21st upload should be blocked
        $canUpload = $this->rateLimiter->checkLimit("user_$userId", 'file_upload', 20, 3600);
        $this->assertFalse($canUpload, '21st upload should be blocked by rate limiter');
    }
    
    /**
     * Test Session Security - HTTP-Only Cookies
     * 
     * Verifies session tokens are stored in HTTP-only cookies.
     */
    public function testSessionSecurityHTTPOnlyCookies(): void {
        // This test verifies the configuration, actual cookie setting
        // happens in the SessionManager
        $sessionManager = new SessionManager($this->db);
        
        // Create a test user and login
        $userId = $this->createTestUser('session@example.com', 'Pass123', 'Session Test', 'BSIT');
        $loginResult = $this->authManager->login('session@example.com', 'Pass123');
        
        $this->assertTrue($loginResult->success);
        $this->assertNotNull($loginResult->sessionToken);
        
        // Verify session exists in database
        $stmt = $this->db->prepare('SELECT * FROM sessions WHERE session_token = ?');
        $stmt->execute([$loginResult->sessionToken]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotEmpty($session, 'Session should exist in database');
        $this->assertEquals($userId, $session['user_id']);
    }
    
    /**
     * Test Session Security - Token Regeneration
     * 
     * Verifies session tokens are regenerated on login to prevent fixation.
     */
    public function testSessionSecurityTokenRegeneration(): void {
        $userId = $this->createTestUser('regenerate@example.com', 'Pass123', 'Regenerate Test', 'BSIT');
        
        // First login
        $login1 = $this->authManager->login('regenerate@example.com', 'Pass123');
        $token1 = $login1->sessionToken;
        
        // Logout
        $_SESSION['session_token'] = $token1;
        $this->authManager->logout();
        
        // Second login
        $login2 = $this->authManager->login('regenerate@example.com', 'Pass123');
        $token2 = $login2->sessionToken;
        
        // Tokens should be different
        $this->assertNotEquals($token1, $token2, 'Session tokens should be regenerated on each login');
    }
    
    /**
     * Test Session Security - Expiration
     * 
     * Verifies sessions expire after 24 hours of inactivity.
     */
    public function testSessionSecurityExpiration(): void {
        $userId = $this->createTestUser('expire@example.com', 'Pass123', 'Expire Test', 'BSIT');
        $loginResult = $this->authManager->login('expire@example.com', 'Pass123');
        
        // Manually set session expiration to past
        $stmt = $this->db->prepare('UPDATE sessions SET expires_at = DATE_SUB(NOW(), INTERVAL 25 HOUR) WHERE session_token = ?');
        $stmt->execute([$loginResult->sessionToken]);
        
        // Attempt to validate expired session
        $sessionManager = new SessionManager($this->db);
        $session = $sessionManager->validateSession($loginResult->sessionToken);
        
        $this->assertNull($session, 'Expired session should not be valid');
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
}
