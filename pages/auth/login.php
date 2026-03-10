<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../../pages/dashboard/dashboard.php');
    exit;
}

$error = '';
$csrf = new CSRFProtection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $authManager = new AuthenticationManager();
        $result = $authManager->login($email, $password);
        
        if ($result['success']) {
            // Store user info in session
            $_SESSION['user_id'] = $result['user']->id;
            $_SESSION['user_email'] = $result['user']->email;
            $_SESSION['user_name'] = $result['user']->fullName;
            $_SESSION['user_program'] = $result['user']->program;
            
            header('Location: ../../pages/dashboard/dashboard.php');
            exit;
        } else {
            $error = $result['error'] ?? 'Invalid email or password';
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
    <title>Login - Portfolio Showcase</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><i class="fas fa-sign-in-alt"></i> Welcome Back</h1>
                <p>Login to manage your portfolio</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($_GET['registered'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Registration successful! Please login with your credentials.
                </div>
            <?php endif; ?>
            
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
                        placeholder="Enter your password"
                    >
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="password-reset-request.php" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p><a href="../../index.php">← Back to Home</a></p>
            </div>
        </div>
        
        <div class="auth-info">
            <h2>Access Your Portfolio</h2>
            <ul>
                <li><i class="fas fa-check"></i> Manage your projects and achievements</li>
                <li><i class="fas fa-check"></i> Customize your portfolio design</li>
                <li><i class="fas fa-check"></i> Upload files and images</li>
                <li><i class="fas fa-check"></i> Export to PDF</li>
                <li><i class="fas fa-check"></i> Control visibility settings</li>
            </ul>
        </div>
    </div>
</body>
</html>
