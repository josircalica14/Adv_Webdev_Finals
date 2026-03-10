<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationSettings.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../../includes/Auth/CSRFProtection.php';

$currentUser = requireAuth('login.php');
$csrf = new CSRFProtection();

$db = Database::getInstance()->getConnection();
$config = Config::getInstance();
$portfolioRepo = new Portfolio\PortfolioRepository($db);
$customization = new Customization\CustomizationEngine($db, $portfolioRepo);

// Get portfolio items
$configArray = [
    'paths' => $config->get('paths'),
    'files' => $config->get('files')
];
$fileManager = new FileStorageManager($db, $configArray);
$portfolioManager = new Portfolio\PortfolioManager($db, $fileManager);
$portfolioItems = $portfolioManager->getItems($currentUser->id);

// Filter visible items
$visibleItems = array_filter($portfolioItems, fn($item) => $item['is_visible'] ?? true);

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
    'primary_color' => '#3498db',
    'accent_color' => '#e74c3c',
    'heading_font' => 'Roboto',
    'body_font' => 'Open Sans',
    'layout' => 'grid'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$csrf->validateRequest()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $newSettings = new Customization\CustomizationSettings();
        $newSettings->theme = $_POST['theme'] ?? 'default';
        $newSettings->primaryColor = $_POST['primary_color'] ?? '#3498db';
        $newSettings->accentColor = $_POST['accent_color'] ?? '#e74c3c';
        $newSettings->headingFont = $_POST['heading_font'] ?? 'Roboto';
        $newSettings->bodyFont = $_POST['body_font'] ?? 'Open Sans';
        $newSettings->layout = $_POST['layout'] ?? 'grid';
        
        $result = $customization->updateSettings($currentUser->id, $newSettings);
        
        if ($result['success']) {
            $success = 'PDF customization saved successfully!';
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize PDF Export</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Open+Sans:wght@300;400;600;700&family=Montserrat:wght@300;400;600;700;900&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            background: #eaeaea;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        
        .pdf-customizer-wrapper {
            display: flex;
            height: calc(100vh - 80px);
            margin-top: 80px;
        }
        
        /* Editor Panel - Left Side */
        .editor-panel {
            width: 400px;
            background: white;
            border-right: 1px solid #0f0f0f;
            overflow-y: auto;
            padding: 30px;
        }
        
        .editor-header {
            margin-bottom: 25px;
        }
        
        .editor-header h1 {
            font-size: 24px;
            font-weight: 900;
            color: #0f0f0f;
            margin: 0 0 8px 0;
        }
        
        .editor-header p {
            font-size: 13px;
            color: #666;
            margin: 0;
            line-height: 1.5;
        }
        
        .editor-section {
            margin-bottom: 30px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .editor-section:last-child {
            border-bottom: none;
        }
        
        .editor-section h3 {
            font-size: 14px;
            font-weight: 700;
            color: #0f0f0f;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .color-picker-group {
            display: flex;
            gap: 15px;
        }
        
        .color-picker {
            flex: 1;
        }
        
        .color-picker label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }
        
        .color-input-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            background: #f5f5f5;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
        }
        
        .color-input-wrapper input[type="color"] {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .color-input-wrapper input[type="text"] {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 600;
            color: #0f0f0f;
        }
        
        .font-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 12px;
            background: white;
        }
        
        .template-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .template-card {
            padding: 20px;
            border: 2px solid #d0d0d0;
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .template-card:hover {
            border-color: #d6a5ad;
            transform: translateY(-2px);
        }
        
        .template-card.active {
            border-color: #0f0f0f;
            background: #f5e8eb;
        }
        
        .template-card i {
            font-size: 24px;
            margin-bottom: 8px;
            color: #666;
        }
        
        .template-card span {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #0f0f0f;
        }
        
        .save-button {
            width: 100%;
            padding: 14px;
            background: #0f0f0f;
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .save-button:hover {
            background: #d6a5ad;
            color: #0f0f0f;
        }
        
        /* Preview Panel - Right Side */
        .preview-panel {
            flex: 1;
            background: #f5f5f5;
            overflow-y: auto;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .pdf-preview {
            width: 8.5in;
            min-height: 11in;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            padding: 0.5in;
        }
        
        .pdf-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent-color, #e74c3c);
            margin-bottom: 30px;
        }
        
        .pdf-name {
            font-family: var(--heading-font, 'Roboto'), sans-serif;
            font-size: 32px;
            font-weight: 900;
            color: var(--primary-color, #3498db);
            margin: 0 0 8px 0;
        }
        
        .pdf-program {
            font-family: var(--body-font, 'Open Sans'), sans-serif;
            font-size: 14px;
            color: #666;
        }
        
        .pdf-section {
            margin-bottom: 25px;
        }
        
        .pdf-section-title {
            font-family: var(--heading-font, 'Roboto'), sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color, #3498db);
            margin: 0 0 15px 0;
        }
        
        .pdf-item {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .pdf-item-title {
            font-family: var(--heading-font, 'Roboto'), sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: var(--accent-color, #e74c3c);
            margin: 0 0 8px 0;
        }
        
        .pdf-item-text {
            font-family: var(--body-font, 'Open Sans'), sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        
        .back-link {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background: white;
            color: #0f0f0f;
            text-decoration: none;
            border: 1px solid #0f0f0f;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            background: #0f0f0f;
            color: white;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        
        .alert-info {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            color: #1976d2;
        }
        
        .alert-success {
            background: #e8f5e9;
            border: 1px solid #4caf50;
            color: #2e7d32;
        }
        
        .alert-error {
            background: #ffebee;
            border: 1px solid #f44336;
            color: #c62828;
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="pdf-customizer-wrapper">
        <!-- Editor Panel -->
        <div class="editor-panel">
            <div class="editor-header">
                <h1>PDF Customizer</h1>
                <p>Design your PDF export with live preview. Your public portfolio keeps the website theme.</p>
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
            
            <form method="POST" id="customizeForm">
                <?php echo $csrf->getTokenField(); ?>
                
                <div class="editor-section">
                    <h3>Colors</h3>
                    <div class="color-picker-group">
                        <div class="color-picker">
                            <label>Primary Color</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($settings['primary_color']); ?>">
                                <input type="text" id="primary_color_text" value="<?php echo htmlspecialchars($settings['primary_color']); ?>" readonly>
                            </div>
                        </div>
                        <div class="color-picker">
                            <label>Accent Color</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="accent_color" name="accent_color" value="<?php echo htmlspecialchars($settings['accent_color']); ?>">
                                <input type="text" id="accent_color_text" value="<?php echo htmlspecialchars($settings['accent_color']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="editor-section">
                    <h3>Typography</h3>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 8px;">Heading Font</label>
                    <select id="heading_font" name="heading_font" class="font-select">
                        <option value="Roboto" <?php echo $settings['heading_font'] === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
                        <option value="Open Sans" <?php echo $settings['heading_font'] === 'Open Sans' ? 'selected' : ''; ?>>Open Sans</option>
                        <option value="Montserrat" <?php echo $settings['heading_font'] === 'Montserrat' ? 'selected' : ''; ?>>Montserrat</option>
                        <option value="Lato" <?php echo $settings['heading_font'] === 'Lato' ? 'selected' : ''; ?>>Lato</option>
                    </select>
                    
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #666; margin-bottom: 8px;">Body Font</label>
                    <select id="body_font" name="body_font" class="font-select">
                        <option value="Open Sans" <?php echo $settings['body_font'] === 'Open Sans' ? 'selected' : ''; ?>>Open Sans</option>
                        <option value="Roboto" <?php echo $settings['body_font'] === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
                        <option value="Lato" <?php echo $settings['body_font'] === 'Lato' ? 'selected' : ''; ?>>Lato</option>
                    </select>
                </div>
                
                <div class="editor-section">
                    <h3>Template</h3>
                    <div class="template-grid">
                        <label class="template-card <?php echo $settings['theme'] === 'default' ? 'active' : ''; ?>">
                            <input type="radio" name="theme" value="default" style="display: none;" <?php echo $settings['theme'] === 'default' ? 'checked' : ''; ?>>
                            <i class="fas fa-file-alt"></i>
                            <span>Professional</span>
                        </label>
                        <label class="template-card <?php echo $settings['theme'] === 'dark' ? 'active' : ''; ?>">
                            <input type="radio" name="theme" value="dark" style="display: none;" <?php echo $settings['theme'] === 'dark' ? 'checked' : ''; ?>>
                            <i class="fas fa-moon"></i>
                            <span>Dark</span>
                        </label>
                        <label class="template-card <?php echo $settings['theme'] === 'light' ? 'active' : ''; ?>">
                            <input type="radio" name="theme" value="light" style="display: none;" <?php echo $settings['theme'] === 'light' ? 'checked' : ''; ?>>
                            <i class="fas fa-sun"></i>
                            <span>Light</span>
                        </label>
                        <label class="template-card <?php echo $settings['theme'] === 'creative' ? 'active' : ''; ?>">
                            <input type="radio" name="theme" value="creative" style="display: none;" <?php echo $settings['theme'] === 'creative' ? 'checked' : ''; ?>>
                            <i class="fas fa-palette"></i>
                            <span>Creative</span>
                        </label>
                    </div>
                </div>
                
                <input type="hidden" name="layout" value="<?php echo htmlspecialchars($settings['layout']); ?>">
                
                <button type="submit" class="save-button">
                    <i class="fas fa-save"></i> Save & Export PDF
                </button>
            </form>
        </div>
        
        <!-- Preview Panel -->
        <div class="preview-panel">
            <div class="pdf-preview" id="pdfPreview">
                <div class="pdf-header">
                    <div class="pdf-name"><?php echo htmlspecialchars($currentUser->fullName); ?></div>
                    <div class="pdf-program"><?php echo htmlspecialchars($currentUser->program); ?> Student</div>
                </div>
                
                <div class="pdf-section">
                    <div class="pdf-section-title">About</div>
                    <div class="pdf-item-text">
                        <?php echo $currentUser->bio ? nl2br(htmlspecialchars($currentUser->bio)) : 'Your bio will appear here...'; ?>
                    </div>
                </div>
                
                <div class="pdf-section">
                    <div class="pdf-section-title">Projects</div>
                    <?php if (!empty($visibleItems)): ?>
                        <?php foreach (array_slice($visibleItems, 0, 3) as $item): ?>
                            <div class="pdf-item">
                                <div class="pdf-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="pdf-item-text"><?php echo htmlspecialchars(substr($item['description'], 0, 150)) . (strlen($item['description']) > 150 ? '...' : ''); ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="pdf-item">
                            <div class="pdf-item-title">Sample Project Title</div>
                            <div class="pdf-item-text">Add portfolio items to see them in your PDF preview. Go to Dashboard → Add Project to get started.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Live preview updates
        const primaryColor = document.getElementById('primary_color');
        const accentColor = document.getElementById('accent_color');
        const headingFont = document.getElementById('heading_font');
        const bodyFont = document.getElementById('body_font');
        const preview = document.getElementById('pdfPreview');
        
        function updatePreview() {
            preview.style.setProperty('--primary-color', primaryColor.value);
            preview.style.setProperty('--accent-color', accentColor.value);
            preview.style.setProperty('--heading-font', headingFont.value);
            preview.style.setProperty('--body-font', bodyFont.value);
            
            document.getElementById('primary_color_text').value = primaryColor.value;
            document.getElementById('accent_color_text').value = accentColor.value;
        }
        
        primaryColor.addEventListener('input', updatePreview);
        accentColor.addEventListener('input', updatePreview);
        headingFont.addEventListener('change', updatePreview);
        bodyFont.addEventListener('change', updatePreview);
        
        // Template card selection
        document.querySelectorAll('.template-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Initialize preview
        updatePreview();
    </script>
</body>
</html>
