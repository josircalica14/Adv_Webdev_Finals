<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/Portfolio/Portfolio.php';

$error = '';
$success = '';
$errors = [];
$csrf = new CSRFProtection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    $program = $_POST['program'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password) || empty($fullName) || empty($program)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $authManager = new AuthenticationManager();
        $result = $authManager->register($email, $password, $fullName, $program);
        
        if ($result['success']) {
            // Create a portfolio for the new user
            try {
                $db = Database::getInstance()->getConnection();
                $portfolioRepo = new Portfolio\PortfolioRepository($db);
                
                // Create a new portfolio object
                $portfolio = new Portfolio\Portfolio(
                    $result['userId'], // userId
                    true, // is_public - default to true so they appear in showcase
                    0, // view_count
                    null, // id will be auto-generated
                    null, // created_at
                    null  // updated_at
                );
                
                $portfolioRepo->create($portfolio);
            } catch (Exception $e) {
                error_log("Failed to create portfolio for new user: " . $e->getMessage());
                // Don't fail registration if portfolio creation fails
            }
            
            $success = 'Registration successful! Redirecting to login...';
            header('refresh:2;url=login.php?registered=1');
        } else {
            // Handle validation errors
            if (isset($result['errors'])) {
                $errors = $result['errors'];
                $errorMessages = [];
                foreach ($errors as $field => $fieldErrors) {
                    $errorMessages = array_merge($errorMessages, $fieldErrors);
                }
                $error = implode(', ', $errorMessages);
            } else {
                $error = $result['error'] ?? 'Registration failed. Please try again.';
            }
        }
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Portfolio Showcase</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-user-plus"></i> Create Your Portfolio</h1>
                <p>Join BSIT and CSE students showcasing their work</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <?php echo $csrf->getTokenField(); ?>
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        required
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        placeholder="your.email@example.com"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="program">
                        <i class="fas fa-graduation-cap"></i> Program
                    </label>
                    <select id="program" name="program" required>
                        <option value="">Select your program</option>
                        <option value="BSIT" <?php echo ($_POST['program'] ?? '') === 'BSIT' ? 'selected' : ''; ?>>
                            BSIT - Information Technology
                        </option>
                        <option value="CSE" <?php echo ($_POST['program'] ?? '') === 'CSE' ? 'selected' : ''; ?>>
                            CSE - Computer Science & Engineering
                        </option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="At least 8 characters"
                        minlength="8"
                    >
                    <small class="form-hint">
                        Must be at least 8 characters with uppercase, lowercase, and number
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i> Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                        placeholder="Re-enter your password"
                        minlength="8"
                    >
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p><a href="../../index.php">← Back to Home</a></p>
            </div>
        </div>
        
        <div class="auth-info">
            <h2>Why Create a Portfolio?</h2>
            <ul>
                <li><i class="fas fa-check"></i> Showcase your projects and achievements</li>
                <li><i class="fas fa-check"></i> Customize your portfolio appearance</li>
                <li><i class="fas fa-check"></i> Share with potential employers</li>
                <li><i class="fas fa-check"></i> Download as PDF resume</li>
                <li><i class="fas fa-check"></i> Connect with other students</li>
            </ul>
        </div>
    </div>
</body>
</html>
