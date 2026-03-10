<?php
/**
 * Portfolio Export Endpoint
 * 
 * Handles PDF export requests for authenticated users
 * Validates: Requirements 8.1, 8.6, 8.7
 */

require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth/AuthenticationManager.php';
require_once __DIR__ . '/../../includes/Export/ExportGenerator.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../../includes/Portfolio/PortfolioRepository.php';
require_once __DIR__ . '/../../includes/FileStorageManager.php';
require_once __DIR__ . '/../../includes/Customization/CustomizationEngine.php';

use Export\ExportGenerator;
use Portfolio\PortfolioManager;
use Customization\CustomizationEngine;

// Start session
session_start();

// Get database connection
$db = Database::getInstance()->getConnection();

// Check authentication
$authManager = new AuthenticationManager();
$user = $authManager->validateSession();

if (!$user) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized. Please log in.']));
}

// Get request parameters
$format = $_GET['format'] ?? 'pdf'; // pdf or html
$itemIds = isset($_GET['items']) ? explode(',', $_GET['items']) : [];

// Validate item IDs
$itemIds = array_filter(array_map('intval', $itemIds));

try {
    // Initialize PortfolioManager for preview
    $config = Config::getInstance();
    $configArray = [
        'paths' => $config->get('paths'),
        'files' => $config->get('files')
    ];
    $fileManager = new FileStorageManager($db, $configArray);
    $portfolioManager = new PortfolioManager($db, $fileManager);
    
    // Load user's customization settings
    $portfolioRepo = new Portfolio\PortfolioRepository($db);
    $customization = new CustomizationEngine($db, $portfolioRepo);
    $settingsObj = $customization->getSettings($user->id);
    $userSettings = $settingsObj ? [
        'primary_color' => $settingsObj->primaryColor,
        'accent_color' => $settingsObj->accentColor,
        'heading_font' => $settingsObj->headingFont,
        'body_font' => $settingsObj->bodyFont
    ] : [
        'primary_color' => '#3498db',
        'accent_color' => '#e74c3c',
        'heading_font' => 'Roboto',
        'body_font' => 'Open Sans'
    ];
    
    // Check if TCPDF is available
    if (!class_exists('TCPDF')) {
        // Show PDF preview with customize button instead of error
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>PDF Export Preview</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="../../css/dashboard.css">
            <link rel="stylesheet" href="../../css/forms.css">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
            <style>
                body {
                    background: #f5f5f5;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    margin: 0;
                    padding: 40px 20px;
                }
                .preview-container {
                    max-width: 900px;
                    margin: 0 auto;
                }
                .preview-header {
                    text-align: center;
                    margin-bottom: 30px;
                }
                .preview-header h2 {
                    font-size: 28px;
                    font-weight: 900;
                    color: #0f0f0f;
                    margin: 0 0 10px 0;
                }
                .preview-header p {
                    font-size: 15px;
                    color: #666;
                    margin: 0;
                }
                .pdf-preview-box {
                    width: 8.5in;
                    min-height: 11in;
                    background: white;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    padding: 0.5in;
                    margin: 0 auto 30px;
                }
                .pdf-header {
                    text-align: center;
                    padding-bottom: 20px;
                    border-bottom: 2px solid <?php echo htmlspecialchars($userSettings['accent_color']); ?>;
                    margin-bottom: 30px;
                }
                .pdf-name {
                    font-family: '<?php echo htmlspecialchars($userSettings['heading_font']); ?>', sans-serif;
                    font-size: 32px;
                    font-weight: 900;
                    color: <?php echo htmlspecialchars($userSettings['primary_color']); ?>;
                    margin: 0 0 8px 0;
                }
                .pdf-program {
                    font-family: '<?php echo htmlspecialchars($userSettings['body_font']); ?>', sans-serif;
                    font-size: 14px;
                    color: #666;
                }
                .pdf-section {
                    margin-bottom: 25px;
                }
                .pdf-section-title {
                    font-family: '<?php echo htmlspecialchars($userSettings['heading_font']); ?>', sans-serif;
                    font-size: 20px;
                    font-weight: 700;
                    color: <?php echo htmlspecialchars($userSettings['primary_color']); ?>;
                    margin: 0 0 15px 0;
                }
                .pdf-item {
                    margin-bottom: 20px;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #eee;
                }
                .pdf-item-title {
                    font-family: '<?php echo htmlspecialchars($userSettings['heading_font']); ?>', sans-serif;
                    font-size: 16px;
                    font-weight: 600;
                    color: <?php echo htmlspecialchars($userSettings['accent_color']); ?>;
                    margin: 0 0 8px 0;
                }
                .pdf-item-text {
                    font-family: '<?php echo htmlspecialchars($userSettings['body_font']); ?>', sans-serif;
                    font-size: 12px;
                    line-height: 1.6;
                    color: #333;
                }
                .button-group {
                    display: flex;
                    gap: 15px;
                    justify-content: center;
                    flex-wrap: wrap;
                }
                .btn-primary, .btn-secondary {
                    padding: 14px 35px;
                    border-radius: 50px;
                    font-size: 14px;
                    font-weight: 700;
                    text-decoration: none;
                    display: inline-flex;
                    align-items: center;
                    gap: 10px;
                    transition: all 0.3s;
                    border: 1px solid #0f0f0f;
                }
                .btn-primary {
                    background: #0f0f0f;
                    color: white;
                }
                .btn-primary:hover {
                    background: #d6a5ad;
                    color: #0f0f0f;
                    border-color: #d6a5ad;
                }
                .btn-secondary {
                    background: white;
                    color: #0f0f0f;
                }
                .btn-secondary:hover {
                    background: #0f0f0f;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="preview-container">
                <div class="preview-header">
                    <h2>PDF Export Preview</h2>
                    <p>This is how your PDF will look. Customize colors, fonts, and layout to personalize it.</p>
                </div>
                
                <div class="pdf-preview-box">
                    <div class="pdf-header">
                        <div class="pdf-name"><?php echo htmlspecialchars($user->fullName); ?></div>
                        <div class="pdf-program"><?php echo htmlspecialchars($user->program); ?> Student</div>
                    </div>
                    
                    <div class="pdf-section">
                        <div class="pdf-section-title">About</div>
                        <div class="pdf-item-text">
                            <?php echo $user->bio ? nl2br(htmlspecialchars($user->bio)) : 'Your bio will appear here...'; ?>
                        </div>
                    </div>
                    
                    <div class="pdf-section">
                        <div class="pdf-section-title">Portfolio Items</div>
                        <?php 
                        $items = $portfolioManager->getItems($user->id);
                        $visibleItems = array_filter($items, fn($item) => $item['is_visible'] ?? true);
                        if (!empty($visibleItems)): 
                            foreach (array_slice($visibleItems, 0, 3) as $item): 
                        ?>
                            <div class="pdf-item">
                                <div class="pdf-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="pdf-item-text"><?php echo htmlspecialchars(substr($item['description'], 0, 200)) . (strlen($item['description']) > 200 ? '...' : ''); ?></div>
                            </div>
                        <?php 
                            endforeach;
                        else: 
                        ?>
                            <div class="pdf-item">
                                <div class="pdf-item-text">No portfolio items yet. Add projects to see them in your PDF.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="button-group">
                    <a href="customize-pdf.php" class="btn-primary">
                        <i class="fas fa-palette"></i> Customize PDF
                    </a>
                    <a href="dashboard.php" class="btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    // Initialize dependencies
    $config = Config::getInstance();
    $configArray = [
        'paths' => $config->get('paths'),
        'files' => $config->get('files')
    ];
    $fileManager = new FileStorageManager($db, $configArray);
    $portfolioRepo = new Portfolio\PortfolioRepository($db);
    
    // Initialize managers
    $portfolioManager = new PortfolioManager($db, $fileManager);
    $customizationEngine = new CustomizationEngine($db, $portfolioRepo);
    $exportGenerator = new ExportGenerator($db, $customizationEngine, $portfolioManager);

    if ($format === 'html') {
        // Generate HTML export
        $html = $exportGenerator->generateHTML($user->id, $itemIds);
        
        // Set headers for HTML download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="portfolio_' . $user->username . '_' . date('Ymd') . '.html"');
        
        echo $html;
        exit;
        
    } else {
        // Generate PDF export
        $result = $exportGenerator->generatePDF($user->id, $itemIds);
        
        if ($result->success && file_exists($result->filePath)) {
            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="portfolio_' . $user->username . '_' . date('Ymd') . '.pdf"');
            header('Content-Length: ' . filesize($result->filePath));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            // Output file
            readfile($result->filePath);
            
            // Clean up temp file
            unlink($result->filePath);
            exit;
            
        } else {
            http_response_code(500);
            die(json_encode([
                'error' => 'PDF generation failed',
                'message' => $result->error ?? 'Unknown error',
                'generation_time' => $result->generationTime
            ]));
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Export failed',
        'message' => $e->getMessage()
    ]));
}
