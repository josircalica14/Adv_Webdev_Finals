<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

$currentUser = requireAuth('login.php');
$csrf = new CSRFProtection();

$db = Database::getInstance()->getConnection();
$config = Config::getInstance();
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);

$error = '';
$success = '';

// Get current portfolio visibility
$portfolio = $portfolioManager->getPortfolio($currentUser->id);
$isPublic = $portfolio ? $portfolio->isPublic() : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'visibility') {
            $newVisibility = isset($_POST['is_public']) && $_POST['is_public'] === '1';
            $result = $portfolioManager->updateVisibility($currentUser->id, $newVisibility);
            
            if ($result['success']) {
                $success = 'Portfolio visibility updated successfully!';
                $isPublic = $newVisibility;
            } else {
                $error = $result['error'] ?? 'Failed to update visibility';
            }
        }
    }
}
?>
<?php include "../../includes/header.php"; ?>
<link rel="stylesheet" href="../../css/dashboard.css">
<link rel="stylesheet" href="../../css/forms.css">

<body>
<div class="wrapper">
<?php include "../../includes/nav-dashboard.php"; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Settings</h1>
        <p>Manage your portfolio settings</p>
        <a href="dashboard.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="settings-container">
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
        
        <div class="settings-section">
            <h2>Portfolio Visibility</h2>
            <form method="POST" class="settings-form">
                <?php echo $csrf->getTokenField(); ?>
                <input type="hidden" name="action" value="visibility">
                
                <div class="form-group">
                    <div class="toggle-wrapper">
                        <label class="switch-label">
                            <input type="checkbox" name="is_public" value="1" <?php echo $isPublic ? 'checked' : ''; ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <div class="toggle-content">
                            <span class="switch-text">Make my portfolio public</span>
                            <p class="form-hint">When enabled, your portfolio will be visible to everyone on the showcase page.</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
        
        <div class="settings-section">
            <h2>Account Settings</h2>
            <div class="settings-links">
                <a href="profile.php" class="settings-link">
                    <i class="fas fa-user"></i>
                    <div>
                        <h3>Edit Profile</h3>
                        <p>Update your name, bio, and profile picture</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="change-password.php" class="settings-link">
                    <i class="fas fa-lock"></i>
                    <div>
                        <h3>Change Password</h3>
                        <p>Update your account password</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="change-username.php" class="settings-link">
                    <i class="fas fa-at"></i>
                    <div>
                        <h3>Change Username</h3>
                        <p>Update your username</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <div class="settings-section">
            <h2>Portfolio Actions</h2>
            <div class="settings-links">
                <a href="customize-pdf.php" class="settings-link">
                    <i class="fas fa-palette"></i>
                    <div>
                        <h3>Customize Theme</h3>
                        <p>Personalize your portfolio appearance</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="export-portfolio.php" class="settings-link">
                    <i class="fas fa-download"></i>
                    <div>
                        <h3>Export Portfolio</h3>
                        <p>Download your portfolio as PDF</p>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <div class="settings-actions">
            <a href="dashboard.php" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

</div>
<?php include "../../includes/footer.php"; ?>
</body>
</html>
