<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/includes/Auth/CSRFProtection.php';

$error = '';
$success = false;
$csrf = new CSRFProtection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } else {
            $authManager = new AuthenticationManager();
            $result = $authManager->requestPasswordReset($email);
            
            // Always show success message for security (don't reveal if email exists)
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Portfolio Showcase</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-key"></i> Reset Your Password</h1>
                <p>Enter your email address and we'll send you a reset link</p>
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
                    <strong>Reset link sent!</strong>
                    <p>If an account exists with that email, you'll receive a password reset link shortly. Please check your inbox.</p>
                </div>
                
                <div class="auth-footer">
                    <p><a href="login.php">← Back to Login</a></p>
                </div>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <?php echo $csrf->getTokenField(); ?>
                    
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
                        <small class="form-hint">
                            Enter the email address associated with your account
                        </small>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Login here</a></p>
                    <p><a href="index.php">← Back to Home</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="auth-info">
            <h2>Password Reset Process</h2>
            <ul>
                <li><i class="fas fa-check"></i> Enter your email address</li>
                <li><i class="fas fa-check"></i> Check your inbox for reset link</li>
                <li><i class="fas fa-check"></i> Click the link (valid for 1 hour)</li>
                <li><i class="fas fa-check"></i> Create a new secure password</li>
                <li><i class="fas fa-check"></i> Login with your new password</li>
            </ul>
        </div>
    </div>
</body>
</html>
