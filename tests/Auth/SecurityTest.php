<?php
/**
 * Security Test
 * 
 * Unit tests for RateLimiter, CSRFProtection, and InputSanitizer classes
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth/RateLimiter.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';
require_once __DIR__ . '/../../includes/Auth/InputSanitizer.php';

class SecurityTest extends TestCase {
    private PDO $db;
    private RateLimiter $rateLimiter;
    private CSRFProtection $csrfProtection;
    private InputSanitizer $inputSanitizer;
    
    protected function setUp(): void {
        parent::setUp();
        
        // Get database connection
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        
        // Initialize security classes
        $this->rateLimiter = new RateLimiter($this->db);
        $this->csrfProtection = new CSRFProtection();
        $this->inputSanitizer = new InputSanitizer();
        
        // Clean up rate limits table
        $this->db->exec("DELETE FROM rate_limits");
    }
    
    protected function tearDown(): void {
        // Clean up
        $this->db->exec("DELETE FROM rate_limits");
        parent::tearDown();
    }
    
    // ========== RateLimiter Tests ==========
    
    public function testRateLimiterAllowsFirstAttempt(): void {
        $identifier = '192.168.1.1';
        $action = 'login';
        
        $allowed = $this->rateLimiter->checkLimit($identifier, $action, 5, 900);
        
        $this->assertTrue($allowed, 'First attempt should be allowed');
    }
    
    public function testRateLimiterRecordsAttempt(): void {
        $identifier = '192.168.1.1';
        $action = 'login';
        
        $this->rateLimiter->recordAttempt($identifier, $action);
        
        $count = $this->rateLimiter->getAttemptCount($identifier, $action);
        $this->assertEquals(1, $count, 'Attempt count should be 1');
    }
    
    public function testRateLimiterBlocksAfterMaxAttempts(): void {
        $identifier = '192.168.1.1';
        $action = 'login';
        $maxAttempts = 5;
        
        // Record 5 attempts
        for ($i = 0; $i < $maxAttempts; $i++) {
            $this->rateLimiter->recordAttempt($identifier, $action);
        }
        
        // 6th attempt should be blocked
        $allowed = $this->rateLimiter->checkLimit($identifier, $action, $maxAttempts, 900);
        
        $this->assertFalse($allowed, 'Should block after max attempts');
    }
    
    public function testRateLimiterResetClearsAttempts(): void {
        $identifier = '192.168.1.1';
        $action = 'login';
        
        $this->rateLimiter->recordAttempt($identifier, $action);
        $this->rateLimiter->resetLimit($identifier, $action);
        
        $count = $this->rateLimiter->getAttemptCount($identifier, $action);
        $this->assertEquals(0, $count, 'Attempt count should be 0 after reset');
    }
    
    public function testRateLimiterGetRemainingAttempts(): void {
        $identifier = '192.168.1.1';
        $action = 'login';
        $maxAttempts = 5;
        
        // Record 2 attempts
        $this->rateLimiter->recordAttempt($identifier, $action);
        $this->rateLimiter->recordAttempt($identifier, $action);
        
        $remaining = $this->rateLimiter->getRemainingAttempts($identifier, $action, $maxAttempts);
        $this->assertEquals(3, $remaining, 'Should have 3 remaining attempts');
    }
    
    // ========== CSRFProtection Tests ==========
    
    public function testCSRFTokenGeneration(): void {
        $token = $this->csrfProtection->generateToken();
        
        $this->assertNotEmpty($token, 'Token should not be empty');
        $this->assertEquals(64, strlen($token), 'Token should be 64 characters (32 bytes hex)');
    }
    
    public function testCSRFTokenValidation(): void {
        $token = $this->csrfProtection->generateToken();
        
        $isValid = $this->csrfProtection->validateToken($token);
        
        $this->assertTrue($isValid, 'Token should be valid');
    }
    
    public function testCSRFTokenValidationFailsForInvalidToken(): void {
        $this->csrfProtection->generateToken();
        
        $isValid = $this->csrfProtection->validateToken('invalid_token');
        
        $this->assertFalse($isValid, 'Invalid token should fail validation');
    }
    
    public function testCSRFTokenRegeneration(): void {
        $token1 = $this->csrfProtection->generateToken();
        $token2 = $this->csrfProtection->regenerateToken();
        
        $this->assertNotEquals($token1, $token2, 'Regenerated token should be different');
        $this->assertFalse($this->csrfProtection->validateToken($token1), 'Old token should be invalid');
        $this->assertTrue($this->csrfProtection->validateToken($token2), 'New token should be valid');
    }
    
    public function testCSRFTokenFieldGeneration(): void {
        $field = $this->csrfProtection->getTokenField();
        
        $this->assertStringContainsString('<input', $field, 'Should contain input tag');
        $this->assertStringContainsString('type="hidden"', $field, 'Should be hidden input');
        $this->assertStringContainsString('name="csrf_token"', $field, 'Should have correct name');
    }
    
    // ========== InputSanitizer Tests ==========
    
    public function testSanitizeStringRemovesHTMLTags(): void {
        $input = '<script>alert("XSS")</script>Hello';
        $sanitized = $this->inputSanitizer->sanitizeString($input);
        
        // strip_tags removes tags but keeps text content
        $this->assertEquals('alert("XSS")Hello', $sanitized, 'Should remove script tags but keep text content');
        $this->assertStringNotContainsString('<script>', $sanitized, 'Should not contain script tags');
    }
    
    public function testSanitizeStringAllowsSafeHTML(): void {
        $input = '<p>Hello <strong>World</strong></p>';
        $sanitized = $this->inputSanitizer->sanitizeString($input, true);
        
        $this->assertStringContainsString('<p>', $sanitized, 'Should allow p tags');
        $this->assertStringContainsString('<strong>', $sanitized, 'Should allow strong tags');
    }
    
    public function testEscapeHtmlEncodesSpecialCharacters(): void {
        $input = '<script>alert("XSS")</script>';
        $escaped = $this->inputSanitizer->escapeHtml($input);
        
        $this->assertStringContainsString('&lt;', $escaped, 'Should encode < character');
        $this->assertStringContainsString('&gt;', $escaped, 'Should encode > character');
    }
    
    public function testSanitizeEmailReturnsValidEmail(): void {
        $email = '  TEST@EXAMPLE.COM  ';
        $sanitized = $this->inputSanitizer->sanitizeEmail($email);
        
        $this->assertEquals('test@example.com', $sanitized, 'Should lowercase and trim email');
    }
    
    public function testSanitizeEmailReturnsNullForInvalidEmail(): void {
        $email = 'not-an-email';
        $sanitized = $this->inputSanitizer->sanitizeEmail($email);
        
        $this->assertNull($sanitized, 'Should return null for invalid email');
    }
    
    public function testSanitizeUrlReturnsValidUrl(): void {
        $url = 'https://example.com/path?query=value';
        $sanitized = $this->inputSanitizer->sanitizeUrl($url);
        
        $this->assertEquals($url, $sanitized, 'Should return valid URL');
    }
    
    public function testSanitizeUrlRejectsJavascriptProtocol(): void {
        $url = 'javascript:alert("XSS")';
        $sanitized = $this->inputSanitizer->sanitizeUrl($url);
        
        $this->assertNull($sanitized, 'Should reject javascript: protocol');
    }
    
    public function testSanitizeIntReturnsInteger(): void {
        $input = '123';
        $sanitized = $this->inputSanitizer->sanitizeInt($input);
        
        $this->assertSame(123, $sanitized, 'Should return integer');
    }
    
    public function testSanitizeIntReturnsNullForInvalidInput(): void {
        $input = 'not-a-number';
        $sanitized = $this->inputSanitizer->sanitizeInt($input);
        
        $this->assertNull($sanitized, 'Should return null for invalid input');
    }
    
    public function testSanitizeFilenameRemovesDangerousCharacters(): void {
        $filename = '../../../etc/passwd';
        $sanitized = $this->inputSanitizer->sanitizeFilename($filename);
        
        $this->assertStringNotContainsString('/', $sanitized, 'Should remove slashes');
        $this->assertStringNotContainsString('..', $sanitized, 'Should remove dots');
    }
    
    public function testDetectSQLInjectionIdentifiesSuspiciousPatterns(): void {
        $input = "' OR '1'='1";
        $detected = $this->inputSanitizer->detectSQLInjection($input);
        
        $this->assertTrue($detected, 'Should detect SQL injection pattern');
    }
    
    public function testDetectSQLInjectionAllowsNormalInput(): void {
        $input = 'This is a normal string';
        $detected = $this->inputSanitizer->detectSQLInjection($input);
        
        $this->assertFalse($detected, 'Should not detect SQL injection in normal input');
    }
    
    public function testDetectXSSIdentifiesSuspiciousPatterns(): void {
        $input = '<script>alert("XSS")</script>';
        $detected = $this->inputSanitizer->detectXSS($input);
        
        $this->assertTrue($detected, 'Should detect XSS pattern');
    }
    
    public function testDetectXSSAllowsNormalInput(): void {
        $input = 'This is a normal string';
        $detected = $this->inputSanitizer->detectXSS($input);
        
        $this->assertFalse($detected, 'Should not detect XSS in normal input');
    }
    
    public function testSanitizeUsernameAllowsValidCharacters(): void {
        $username = 'user_name-123';
        $sanitized = $this->inputSanitizer->sanitizeUsername($username);
        
        $this->assertEquals('user_name-123', $sanitized, 'Should allow valid username');
    }
    
    public function testSanitizeUsernameRejectsInvalidCharacters(): void {
        $username = 'user@name!';
        $sanitized = $this->inputSanitizer->sanitizeUsername($username);
        
        $this->assertNull($sanitized, 'Should reject username with invalid characters');
    }
    
    public function testSanitizeArrayRecursively(): void {
        $data = [
            'name' => '<script>alert("XSS")</script>John',
            'nested' => [
                'value' => '<b>Bold</b>'
            ]
        ];
        
        $sanitized = $this->inputSanitizer->sanitizeArray($data);
        
        // strip_tags removes tags but keeps text content
        $this->assertEquals('alert("XSS")John', $sanitized['name'], 'Should sanitize top-level values');
        $this->assertEquals('Bold', $sanitized['nested']['value'], 'Should sanitize nested values');
        $this->assertStringNotContainsString('<script>', $sanitized['name'], 'Should not contain script tags');
    }
}
