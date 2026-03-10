<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/includes/Auth/CSRFProtection.php';

$error = '';
$success = false;
$token = $_GET['token'] ?? '';
$csrf = new CSRFProtection();

if (empty($token)) {
    $error = 'Invalid reset link. Please request a new password reset.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($token)) {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please enter and confirm your new password';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            $authManager = new AuthenticationManager();
            $result = $authManager->resetPassword($token, $newPassword);
            
            if ($result) {
                $success = true;
            } else {
                $error = 'Password reset failed. The link may have expired or is invalid. Please request a new reset link.';
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
    <title>Reset Password - Portfolio Showcase</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-lock"></i> Create New Password</h1>
                <p>Enter your new password below</p>
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
                    <strong>Password reset successful!</strong>
                    <p>Your password has been changed. You can now login with your new password.</p>
                </div>
                
                <div class="auth-footer">
                    <a href="login.php" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i> Go to Login
                    </a>
                </div>
            <?php elseif (!empty($token)): ?>
                <form method="POST" class="auth-form">
                    <?php echo $csrf->getTokenField(); ?>
                    
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> New Password
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
                            <i class="fas fa-lock"></i> Confirm New Password
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
                        <i class="fas fa-check"></i> Reset Password
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p><a href="login.php">← Back to Login</a></p>
                </div>
            <?php else: ?>
                <div class="auth-footer">
                    <a href="password-reset-request.php" class="btn-submit">
                        <i class="fas fa-key"></i> Request New Reset Link
                    </a>
                    <p><a href="login.php">← Back to Login</a></p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="auth-info">
            <h2>Password Requirements</h2>
            <ul>
                <li><i class="fas fa-check"></i> At least 8 characters long</li>
                <li><i class="fas fa-check"></i> Contains uppercase letter (A-Z)</li>
                <li><i class="fas fa-check"></i> Contains lowercase letter (a-z)</li>
                <li><i class="fas fa-check"></i> Contains number (0-9)</li>
                <li><i class="fas fa-check"></i> Avoid common passwords</li>
            </ul>
        </div>
    </div>
</body>
</html>
