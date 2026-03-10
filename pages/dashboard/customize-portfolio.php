<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationSettings.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

$currentUser = requireAuth('login.php');
$csrf = new CSRFProtection();

$db = Database::getInstance()->getConnection();
$portfolioRepo = new Portfolio\PortfolioRepository($db);
$customization = new Customization\CustomizationEngine($db, $portfolioRepo);

$error = '';
$success = '';

// Get current settings
$settingsObj = $customization->getSettings($currentUser->id);
$settings = $settingsObj ? [
    'theme' => $settingsObj->theme,
    'primary_color' => $settingsObj->primaryColor,
    'accent_color' => $settingsObj->accentColor,
    'heading_font' => $settingsObj->headingFont,
    'body_font' => $settingsObj->bodyFont,
    'layout' => $settingsObj->layout
] : [
    'theme' => 'default',
    'primary_color' => '#0f0f0f',
    'accent_color' => '#d6a5ad',
    'heading_font' => 'system',
    'body_font' => 'system',
    'layout' => 'grid'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $newSettings = new Customization\CustomizationSettings();
        $newSettings->theme = $_POST['theme'] ?? 'default';
        $newSettings->primaryColor = $_POST['primary_color'] ?? '#0f0f0f';
        $newSettings->accentColor = $_POST['accent_color'] ?? '#d6a5ad';
        $newSettings->headingFont = $_POST['heading_font'] ?? 'system';
        $newSettings->bodyFont = $_POST['body_font'] ?? 'system';
        $newSettings->layout = $_POST['layout'] ?? 'grid';
        
        $result = $customization->updateSettings($currentUser->id, $newSettings);
        
        if ($result['success']) {
            $success = 'Theme customization saved successfully!';
            $settings = [
                'theme' => $newSettings->theme,
                'primary_color' => $newSettings->primaryColor,
                'accent_color' => $newSettings->accentColor,
                'heading_font' => $newSettings->headingFont,
                'body_font' => $newSettings->bodyFont,
                'layout' => $newSettings->layout
            ];
        } else {
            $error = $result['error'] ?? 'Failed to save customization';
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
        <h1>Customize PDF Export</h1>
        <p>Personalize the appearance of your PDF portfolio export (your public portfolio keeps the website theme)</p>
        <a href="dashboard.php" class="back-to-dashboard">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="customize-container">
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
        
        <form method="POST" class="customize-form">
            <?php echo $csrf->getTokenField(); ?>
            
            <div class="alert alert-info" style="margin-bottom: 30px; background: #e3f2fd; border: 1px solid #2196f3; color: #1976d2;">
                <i class="fas fa-info-circle"></i>
                <strong>Note:</strong> These settings only affect your PDF export. Your public portfolio will always use the website's clean black and pink theme.
            </div>
            
            <div class="form-section">
                <h2>Theme</h2>
                <div class="theme-options">
                    <label class="theme-option">
                        <input type="radio" name="theme" value="default" <?php echo ($settings['theme'] ?? 'default') === 'default' ? 'checked' : ''; ?>>
                        <div class="theme-preview default">
                            <span>Default</span>
                        </div>
                    </label>
                    
                    <label class="theme-option">
                        <input type="radio" name="theme" value="dark" <?php echo ($settings['theme'] ?? '') === 'dark' ? 'checked' : ''; ?>>
                        <div class="theme-preview dark">
                            <span>Dark</span>
                        </div>
                    </label>
                    
                    <label class="theme-option">
                        <input type="radio" name="theme" value="minimal" <?php echo ($settings['theme'] ?? '') === 'minimal' ? 'checked' : ''; ?>>
                        <div class="theme-preview minimal">
                            <span>Minimal</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Colors</h2>
                <div class="color-options">
                    <div class="form-group">
                        <label for="primary_color">Primary Color</label>
                        <input type="color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#0f0f0f'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="accent_color">Accent Color</label>
                        <input type="color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($settings['accent_color'] ?? '#d6a5ad'); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Typography</h2>
                <div class="form-group">
                    <label for="heading_font">Heading Font</label>
                    <select id="heading_font" name="heading_font">
                        <option value="system" <?php echo ($settings['heading_font'] ?? 'system') === 'system' ? 'selected' : ''; ?>>System Default</option>
                        <option value="Roboto" <?php echo ($settings['heading_font'] ?? '') === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
                        <option value="Open Sans" <?php echo ($settings['heading_font'] ?? '') === 'Open Sans' ? 'selected' : ''; ?>>Open Sans</option>
                        <option value="Montserrat" <?php echo ($settings['heading_font'] ?? '') === 'Montserrat' ? 'selected' : ''; ?>>Montserrat</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="body_font">Body Font</label>
                    <select id="body_font" name="body_font">
                        <option value="system" <?php echo ($settings['body_font'] ?? 'system') === 'system' ? 'selected' : ''; ?>>System Default</option>
                        <option value="Roboto" <?php echo ($settings['body_font'] ?? '') === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
                        <option value="Open Sans" <?php echo ($settings['body_font'] ?? '') === 'Open Sans' ? 'selected' : ''; ?>>Open Sans</option>
                        <option value="Lato" <?php echo ($settings['body_font'] ?? '') === 'Lato' ? 'selected' : ''; ?>>Lato</option>
                    </select>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Layout</h2>
                <div class="layout-options">
                    <label class="layout-option">
                        <input type="radio" name="layout" value="grid" <?php echo ($settings['layout'] ?? 'grid') === 'grid' ? 'checked' : ''; ?>>
                        <div class="layout-preview">
                            <i class="fas fa-th"></i>
                            <span>Grid</span>
                        </div>
                    </label>
                    
                    <label class="layout-option">
                        <input type="radio" name="layout" value="list" <?php echo ($settings['layout'] ?? '') === 'list' ? 'checked' : ''; ?>>
                        <div class="layout-preview">
                            <i class="fas fa-list"></i>
                            <span>List</span>
                        </div>
                    </label>
                    
                    <label class="layout-option">
                        <input type="radio" name="layout" value="masonry" <?php echo ($settings['layout'] ?? '') === 'masonry' ? 'checked' : ''; ?>>
                        <div class="layout-preview">
                            <i class="fas fa-th-large"></i>
                            <span>Masonry</span>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Customization
                </button>
                <a href="../portfolio/view.php?username=<?php echo $currentUser->username; ?>" class="btn-secondary" target="_blank">
                    <i class="fas fa-eye"></i> Preview Portfolio
                </a>
                <a href="dashboard.php" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

</div>
<?php include "../../includes/footer.php"; ?>
</body>
</html>
