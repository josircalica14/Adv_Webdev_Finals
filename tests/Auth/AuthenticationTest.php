<?php
/**
 * Basic Authentication System Tests
 * 
 * Tests core authentication functionality including registration,
 * login, password validation, and email validation.
 */

require_once __DIR__ . '/../../includes/Config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Logger.php';
require_once __DIR__ . '/../../includes/ErrorHandler.php';
require_once __DIR__ . '/../../includes/Auth/User.php';
require_once __DIR__ . '/../../includes/Auth/UserRepository.php';
require_once __DIR__ . '/../../includes/Auth/PasswordValidator.php';
require_once __DIR__ . '/../../includes/Auth/EmailValidator.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';

class AuthenticationTest {
    private AuthenticationManager $authManager;
    private PasswordValidator $passwordValidator;
    private EmailValidator $emailValidator;
    private UserRepository $userRepository;

    public function __construct() {
        $this->authManager = new AuthenticationManager();
        $this->passwordValidator = new PasswordValidator();
        $this->emailValidator = new EmailValidator();
        $this->userRepository = new UserRepository();
    }

    /**
     * Test password validation
     */
    public function testPasswordValidation(): void {
        echo "Testing password validation...\n";

        // Test valid password
        $result = $this->passwordValidator->validate('ValidPass123');
        assert($result['valid'] === true, "Valid password should pass validation");
        echo "✓ Valid password accepted\n";

        // Test password too short
        $result = $this->passwordValidator->validate('Short1');
        assert($result['valid'] === false, "Short password should fail");
        assert(count($result['errors']) > 0, "Should have errors");
        echo "✓ Short password rejected\n";

        // Test password without uppercase
        $result = $this->passwordValidator->validate('lowercase123');
        assert($result['valid'] === false, "Password without uppercase should fail");
        echo "✓ Password without uppercase rejected\n";

        // Test password without lowercase
        $result = $this->passwordValidator->validate('UPPERCASE123');
        assert($result['valid'] === false, "Password without lowercase should fail");
        echo "✓ Password without lowercase rejected\n";

        // Test password without number
        $result = $this->passwordValidator->validate('NoNumbers');
        assert($result['valid'] === false, "Password without number should fail");
        echo "✓ Password without number rejected\n";

        echo "Password validation tests passed!\n\n";
    }

    /**
     * Test password hashing and verification
     */
    public function testPasswordHashing(): void {
        echo "Testing password hashing...\n";

        $password = 'TestPassword123';
        $hash = $this->passwordValidator->hash($password);

        assert(!empty($hash), "Hash should not be empty");
        assert($hash !== $password, "Hash should not equal plain password");
        echo "✓ Password hashed successfully\n";

        // Test verification
        $verified = $this->passwordValidator->verify($password, $hash);
        assert($verified === true, "Password should verify against hash");
        echo "✓ Password verification successful\n";

        // Test wrong password
        $verified = $this->passwordValidator->verify('WrongPassword123', $hash);
        assert($verified === false, "Wrong password should not verify");
        echo "✓ Wrong password rejected\n";

        echo "Password hashing tests passed!\n\n";
    }

    /**
     * Test email format validation
     */
    public function testEmailValidation(): void {
        echo "Testing email validation...\n";

        // Test valid email
        $result = $this->emailValidator->validateFormat('test@example.com');
        assert($result['valid'] === true, "Valid email should pass");
        echo "✓ Valid email accepted\n";

        // Test invalid email format
        $result = $this->emailValidator->validateFormat('invalid-email');
        assert($result['valid'] === false, "Invalid email should fail");
        echo "✓ Invalid email format rejected\n";

        // Test email without domain
        $result = $this->emailValidator->validateFormat('test@');
        assert($result['valid'] === false, "Email without domain should fail");
        echo "✓ Email without domain rejected\n";

        // Test empty email
        $result = $this->emailValidator->validateFormat('');
        assert($result['valid'] === false, "Empty email should fail");
        echo "✓ Empty email rejected\n";

        echo "Email validation tests passed!\n\n";
    }

    /**
     * Test user registration
     */
    public function testRegistration(): void {
        echo "Testing user registration...\n";

        $email = 'testuser' . time() . '@example.com';
        $password = 'TestPass123';
        $fullName = 'Test User';
        $program = 'BSIT';

        $result = $this->authManager->register($email, $password, $fullName, $program);

        assert($result['success'] === true, "Registration should succeed");
        assert(isset($result['userId']), "Should return user ID");
        assert(isset($result['verificationToken']), "Should return verification token");
        echo "✓ User registered successfully\n";

        // Test duplicate email
        $result = $this->authManager->register($email, $password, $fullName, $program);
        assert($result['success'] === false, "Duplicate email should fail");
        echo "✓ Duplicate email rejected\n";

        // Clean up
        $this->userRepository->delete($result['userId'] ?? 0);

        echo "Registration tests passed!\n\n";
    }

    /**
     * Test user login
     */
    public function testLogin(): void {
        echo "Testing user login...\n";

        // Create test user
        $email = 'logintest' . time() . '@example.com';
        $password = 'TestPass123';
        $fullName = 'Login Test';
        $program = 'CSE';

        $regResult = $this->authManager->register($email, $password, $fullName, $program);
        assert($regResult['success'] === true, "Registration should succeed");

        // Test successful login
        $loginResult = $this->authManager->login($email, $password);
        assert($loginResult['success'] === true, "Login should succeed");
        assert(isset($loginResult['user']), "Should return user object");
        echo "✓ Login successful\n";

        // Test login with wrong password
        $loginResult = $this->authManager->login($email, 'WrongPass123');
        assert($loginResult['success'] === false, "Login with wrong password should fail");
        echo "✓ Login with wrong password rejected\n";

        // Test login with non-existent email
        $loginResult = $this->authManager->login('nonexistent@example.com', $password);
        assert($loginResult['success'] === false, "Login with non-existent email should fail");
        echo "✓ Login with non-existent email rejected\n";

        // Clean up
        $this->userRepository->delete($regResult['userId']);

        echo "Login tests passed!\n\n";
    }

    /**
     * Run all tests
     */
    public function runAll(): void {
        echo "=== Running Authentication System Tests ===\n\n";

        try {
            $this->testPasswordValidation();
            $this->testPasswordHashing();
            $this->testEmailValidation();
            $this->testRegistration();
            $this->testLogin();

            echo "=== All tests passed! ===\n";
        } catch (AssertionError $e) {
            echo "\n✗ Test failed: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            exit(1);
        } catch (Exception $e) {
            echo "\n✗ Error: " . $e->getMessage() . "\n";
            echo "File: " . $e->getFile() . "\n";
            echo "Line: " . $e->getLine() . "\n";
            exit(1);
        }
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    $test = new AuthenticationTest();
    $test->runAll();
}
