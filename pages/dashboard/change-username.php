<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Profile/ProfileManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

// Require authentication
$user = requireAuth();

$error = '';
$success = false;
$csrf = new CSRFProtection();
$profileManager = new ProfileManager();

// Check if user can change username
$canChange = $profileManager->canChangeUsername($user->id);
$profile = $profileManager->getProfile($user->id);

// Calculate days until next change
$daysUntilChange = 0;
if (!$canChange && !empty($profile['last_username_change'])) {
    $lastChange = new DateTime($profile['last_username_change']);
    $nextChange = $lastChange->modify('+30 days');
    $now = new DateTime();
    $diff = $now->diff($nextChange);
    $daysUntilChange = $diff->days;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } elseif (!$canChange) {
        $error = 'You can only change your username once every 30 days.';
    } else {
        $newUsername = $_POST['username'] ?? '';
        
        if (empty($newUsername)) {
            $error = 'Please enter a username';
        } else {
            $result = $profileManager->updateUsername($user->id, $newUsername);
            
            if ($result['success']) {
                $success = true;
                // Refresh profile
                $profile = $profileManager->getProfile($user->id);
                $canChange = false;
            } else {
                $error = $result['error'] ?? 'Failed to update username. Please try again.';
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
    <title>Change Username - Portfolio Showcase</title>
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
                <h1><i class="fas fa-at"></i> Change Username</h1>
                <p>Update your portfolio URL username</p>
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
                    <strong>Username changed successfully!</strong>
                    <p>Your new username is: <strong><?php echo htmlspecialchars($profile['username']); ?></strong></p>
                    <p>Your portfolio URL is now: <code>/portfolio/<?php echo htmlspecialchars($profile['username']); ?></code></p>
                </div>
                
                <div class="form-actions">
                    <a href="profile.php" class="btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Profile
                    </a>
                </div>
            <?php else: ?>
                <div class="card">
                    <?php if (!$canChange): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            <strong>Username Change Limit</strong>
                            <p>You can only change your username once every 30 days.</p>
                            <p>You can change your username again in <strong><?php echo $daysUntilChange; ?> days</strong>.</p>
                        </div>
                        
                        <div class="form-actions">
                            <a href="profile.php" class="btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Profile
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="username-info">
                            <p><strong>Current Username:</strong> <?php echo htmlspecialchars($profile['username']); ?></p>
                            <p><strong>Current Portfolio URL:</strong> <code>/portfolio/<?php echo htmlspecialchars($profile['username']); ?></code></p>
                        </div>
                        
                        <form method="POST" class="profile-form">
                            <?php echo $csrf->getTokenField(); ?>
                            
                            <div class="form-group">
                                <label for="username">
                                    <i class="fas fa-at"></i> New Username
                                </label>
                                <input 
                                    type="text" 
                                    id="username" 
                                    name="username" 
                                    required
                                    placeholder="Enter new username"
                                    pattern="[a-zA-Z0-9_-]+"
                                    minlength="3"
                                    maxlength="50"
                                >
                                <small class="form-hint">
                                    3-50 characters. Only letters, numbers, hyphens, and underscores allowed.
                                </small>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Important:</strong>
                                <ul>
                                    <li>You can only change your username once every 30 days</li>
                                    <li>Your portfolio URL will be updated to reflect the new username</li>
                                    <li>Old links to your portfolio will no longer work</li>
                                    <li>Choose carefully!</li>
                                </ul>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">
                                    <i class="fas fa-check"></i> Change Username
                                </button>
                                <a href="profile.php" class="btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
