<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

// Require authentication
$user = requireAuth();

$error = '';
$success = false;
$csrf = new CSRFProtection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'All fields are required';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match';
        } elseif ($currentPassword === $newPassword) {
            $error = 'New password must be different from current password';
        } else {
            $authManager = new AuthenticationManager();
            $result = $authManager->changePassword($user->id, $currentPassword, $newPassword);
            
            if ($result['success']) {
                $success = true;
            } else {
                $error = $result['error'] ?? 'Failed to change password. Please try again.';
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
    <title>Change Password - Portfolio Showcase</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php 
    include '../../includes/header.php'; 
    include '../../includes/nav-dashboard.php';
    ?>
    
    <main class="dashboard-main">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1><i class="fas fa-key"></i> Change Password</h1>
                <p>Update your account password</p>
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
                    <strong>Password changed successfully!</strong>
                    <p>Your password has been updated. Please use your new password for future logins.</p>
                </div>
                
                <div class="form-actions">
                    <a href="profile.php" class="btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
            <?php else: ?>
                <div class="card">
                    <form method="POST" class="profile-form">
                        <?php echo $csrf->getTokenField(); ?>
                        
                        <div class="form-group">
                            <label for="current_password">
                                <i class="fas fa-lock"></i> Current Password
                            </label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                required
                                placeholder="Enter your current password"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i> New Password
                            </label>
                            <input 
                                type="password" 
                                id="new_password" 
                                name="new_password" 
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
                                placeholder="Re-enter your new password"
                                minlength="8"
                            >
                        </div>
                        
                        <div class="password-requirements">
                            <h4>Password Requirements:</h4>
                            <ul>
                                <li><i class="fas fa-check-circle"></i> At least 8 characters long</li>
                                <li><i class="fas fa-check-circle"></i> Contains uppercase letter (A-Z)</li>
                                <li><i class="fas fa-check-circle"></i> Contains lowercase letter (a-z)</li>
                                <li><i class="fas fa-check-circle"></i> Contains number (0-9)</li>
                            </ul>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-check"></i> Change Password
                            </button>
                            <a href="profile.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
