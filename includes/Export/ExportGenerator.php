<?php
/**
 * ExportGenerator Class
 * 
 * Handles PDF generation for portfolio exports with customization styling.
 * Validates: Requirements 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 8.7
 */

namespace Export;

use PDO;
use TCPDF;
use Customization\CustomizationEngine;
use Customization\CustomizationSettings;
use Portfolio\PortfolioManager;
use Portfolio\PortfolioItem;

require_once __DIR__ . '/../Auth/User.php';
require_once __DIR__ . '/../Auth/UserRepository.php';
require_once __DIR__ . '/../Portfolio/PortfolioManager.php';
require_once __DIR__ . '/../Customization/CustomizationEngine.php';

class PDFResult {
    public bool $success;
    public ?string $filePath;
    public ?string $error;
    public int $generationTime;

    public function __construct(bool $success, ?string $filePath = null, ?string $error = null, int $generationTime = 0) {
        $this->success = $success;
        $this->filePath = $filePath;
        $this->error = $error;
        $this->generationTime = $generationTime;
    }
}

class ExportGenerator {
    private PDO $db;
    private CustomizationEngine $customizationEngine;
    private PortfolioManager $portfolioManager;
    private string $tempDir;

    public function __construct(PDO $db, CustomizationEngine $customizationEngine, PortfolioManager $portfolioManager) {
        $this->db = $db;
        $this->customizationEngine = $customizationEngine;
        $this->portfolioManager = $portfolioManager;
        $this->tempDir = __DIR__ . '/../../temp/exports';
        
        // Create temp directory if it doesn't exist
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Generate PDF export of portfolio
     * 
     * @param int $userId User ID
     * @param array $itemIds Optional array of specific item IDs to include (empty = all items)
     * @return PDFResult Result object with success status and file path or error
     */
    public function generatePDF(int $userId, array $itemIds = []): PDFResult {
        $startTime = microtime(true);

        try {
            // Get user information
            $userRepo = new \UserRepository($this->db);
            $user = $userRepo->findById($userId);
            
            if (!$user) {
                return new PDFResult(false, null, 'User not found', 0);
            }

            // Get portfolio items
            $allItems = $this->portfolioManager->getItems($userId);
            
            // Filter items if specific IDs provided
            if (!empty($itemIds)) {
                $allItems = array_filter($allItems, function($item) use ($itemIds) {
                    return in_array($item->getId(), $itemIds);
                });
            }

            // Get customization settings
            $settings = $this->customizationEngine->getSettings($userId);

            // Create PDF
            $pdf = $this->createPDF($user, $allItems, $settings);

            // Generate unique filename
            $filename = 'portfolio_' . $user->username . '_' . date('Ymd_His') . '.pdf';
            $filePath = $this->tempDir . '/' . $filename;

            // Output PDF to file
            $pdf->Output($filePath, 'F');

            $generationTime = (int)((microtime(true) - $startTime) * 1000); // milliseconds

            return new PDFResult(true, $filePath, null, $generationTime);

        } catch (\Exception $e) {
            $generationTime = (int)((microtime(true) - $startTime) * 1000);
            return new PDFResult(false, null, 'PDF generation failed: ' . $e->getMessage(), $generationTime);
        }
    }

    /**
     * Create TCPDF instance with portfolio content
     * 
     * @param \User $user User object
     * @param array $items Array of PortfolioItem objects
     * @param CustomizationSettings $settings Customization settings
     * @return TCPDF PDF object
     */
    private function createPDF(\User $user, array $items, CustomizationSettings $settings): TCPDF {
        // Create new PDF document (Letter size: 8.5" x 11")
        $pdf = new TCPDF('P', 'in', 'LETTER', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('Portfolio Platform');
        $pdf->SetAuthor($user->fullName);
        $pdf->SetTitle($user->fullName . ' - Portfolio');
        $pdf->SetSubject('Student Portfolio');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins (0.5 inch on all sides)
        $pdf->SetMargins(0.5, 0.5, 0.5);
        $pdf->SetAutoPageBreak(true, 0.5);

        // Set font
        $pdf->SetFont($this->mapFont($settings->bodyFont), '', 10);

        // Add first page
        $pdf->AddPage();

        // Add content
        $this->addHeader($pdf, $user, $settings);
        $this->addProfileSection($pdf, $user, $settings);
        $this->addPortfolioItems($pdf, $items, $settings);

        return $pdf;
    }

    /**
     * Add header section to PDF
     */
    private function addHeader(TCPDF $pdf, \User $user, CustomizationSettings $settings): void {
        // Set heading font
        $pdf->SetFont($this->mapFont($settings->headingFont), 'B', 24);
        $pdf->SetTextColor($this->hexToRGB($settings->primaryColor));
        
        // Add name
        $pdf->Cell(0, 0.4, $user->fullName, 0, 1, 'C');
        
        // Add program
        $pdf->SetFont($this->mapFont($settings->bodyFont), '', 12);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 0.3, $user->program . ' Student', 0, 1, 'C');
        
        // Add spacing
        $pdf->Ln(0.2);
        
        // Add horizontal line
        $pdf->SetDrawColor($this->hexToRGB($settings->accentColor));
        $pdf->SetLineWidth(0.02);
        $pdf->Line(0.5, $pdf->GetY(), 8, $pdf->GetY());
        
        $pdf->Ln(0.3);
    }

    /**
     * Add profile section to PDF
     */
    private function addProfileSection(TCPDF $pdf, \User $user, CustomizationSettings $settings): void {
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont($this->mapFont($settings->bodyFont), '', 10);

        // Add profile photo if exists
        if ($user->profilePhotoPath && file_exists($user->profilePhotoPath)) {
            try {
                $pdf->Image($user->profilePhotoPath, 0.5, $pdf->GetY(), 1.5, 1.5, '', '', '', true, 150, '', false, false, 1, false, false, false);
                $pdf->SetX(2.2);
                $yStart = $pdf->GetY();
            } catch (\Exception $e) {
                // If image fails, continue without it
                $yStart = $pdf->GetY();
            }
        } else {
            $yStart = $pdf->GetY();
        }

        // Add bio if exists
        if ($user->bio) {
            $pdf->SetFont($this->mapFont($settings->headingFont), 'B', 12);
            $pdf->SetTextColor($this->hexToRGB($settings->primaryColor));
            $pdf->Cell(0, 0.25, 'About', 0, 1, 'L');
            
            $pdf->SetFont($this->mapFont($settings->bodyFont), '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->MultiCell(0, 0.2, $user->bio, 0, 'L');
            $pdf->Ln(0.1);
        }

        // Add contact information if exists
        if ($user->contactInfo && !empty($user->contactInfo)) {
            $pdf->SetFont($this->mapFont($settings->headingFont), 'B', 12);
            $pdf->SetTextColor($this->hexToRGB($settings->primaryColor));
            $pdf->Cell(0, 0.25, 'Contact', 0, 1, 'L');
            
            $pdf->SetFont($this->mapFont($settings->bodyFont), '', 10);
            $pdf->SetTextColor(0, 0, 0);
            
            foreach ($user->contactInfo as $key => $value) {
                if (!empty($value)) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $pdf->Cell(0, 0.2, $label . ': ' . $value, 0, 1, 'L');
                }
            }
            $pdf->Ln(0.1);
        }

        $pdf->Ln(0.2);
    }

    /**
     * Add portfolio items to PDF
     */
    private function addPortfolioItems(TCPDF $pdf, array $items, CustomizationSettings $settings): void {
        if (empty($items)) {
            $pdf->SetFont($this->mapFont($settings->bodyFont), 'I', 10);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(0, 0.3, 'No portfolio items to display', 0, 1, 'C');
            return;
        }

        // Group items by type
        $groupedItems = [];
        foreach ($items as $item) {
            $type = $item->getItemType();
            if (!isset($groupedItems[$type])) {
                $groupedItems[$type] = [];
            }
            $groupedItems[$type][] = $item;
        }

        // Display each group
        $typeLabels = [
            'project' => 'Projects',
            'achievement' => 'Achievements',
            'milestone' => 'Milestones',
            'skill' => 'Skills'
        ];

        foreach ($groupedItems as $type => $typeItems) {
            // Add section header
            $pdf->SetFont($this->mapFont($settings->headingFont), 'B', 16);
            $pdf->SetTextColor($this->hexToRGB($settings->primaryColor));
            $pdf->Cell(0, 0.3, $typeLabels[$type] ?? ucfirst($type), 0, 1, 'L');
            $pdf->Ln(0.1);

            // Add items
            foreach ($typeItems as $item) {
                $this->addPortfolioItem($pdf, $item, $settings);
            }

            $pdf->Ln(0.2);
        }
    }

    /**
     * Add single portfolio item to PDF
     */
    private function addPortfolioItem(TCPDF $pdf, PortfolioItem $item, CustomizationSettings $settings): void {
        // Item title
        $pdf->SetFont($this->mapFont($settings->headingFont), 'B', 12);
        $pdf->SetTextColor($this->hexToRGB($settings->accentColor));
        $pdf->Cell(0, 0.25, $item->getTitle(), 0, 1, 'L');

        // Item date if exists
        if ($item->getItemDate()) {
            $pdf->SetFont($this->mapFont($settings->bodyFont), 'I', 9);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 0.2, date('F Y', strtotime($item->getItemDate())), 0, 1, 'L');
        }

        // Item description
        $pdf->SetFont($this->mapFont($settings->bodyFont), '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 0.18, $item->getDescription(), 0, 'L');

        // Tags if exist
        if (!empty($item->getTags())) {
            $pdf->SetFont($this->mapFont($settings->bodyFont), '', 9);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->Cell(0, 0.2, 'Tags: ' . implode(', ', $item->getTags()), 0, 1, 'L');
        }

        // Links if exist
        if (!empty($item->getLinks())) {
            $pdf->SetFont($this->mapFont($settings->bodyFont), '', 9);
            $pdf->SetTextColor(0, 0, 255);
            foreach ($item->getLinks() as $link) {
                if (is_array($link) && isset($link['url'])) {
                    $linkText = $link['label'] ?? $link['url'];
                    $pdf->Cell(0, 0.18, $linkText, 0, 1, 'L', false, $link['url']);
                } elseif (is_string($link)) {
                    $pdf->Cell(0, 0.18, $link, 0, 1, 'L', false, $link);
                }
            }
        }

        // Embed images if exist
        $this->embedItemImages($pdf, $item);

        $pdf->Ln(0.15);
    }

    /**
     * Embed images for portfolio item
     */
    private function embedItemImages(TCPDF $pdf, PortfolioItem $item): void {
        // Get files for this item
        $stmt = $this->db->prepare("
            SELECT file_path, file_type, original_filename 
            FROM files 
            WHERE portfolio_item_id = ? 
            AND file_type IN ('image/jpeg', 'image/png', 'image/webp', 'image/gif')
            LIMIT 3
        ");
        $stmt->execute([$item->getId()]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($files)) {
            return;
        }

        $pdf->Ln(0.1);
        $xPos = 0.5;
        $imageWidth = 2.3; // Width for each image
        $imagesPerRow = 3;
        $imageCount = 0;

        foreach ($files as $file) {
            if (!file_exists($file['file_path'])) {
                continue;
            }

            try {
                // Calculate position
                if ($imageCount > 0 && $imageCount % $imagesPerRow === 0) {
                    $pdf->Ln(1.8);
                    $xPos = 0.5;
                }

                // Add image
                $pdf->Image($file['file_path'], $xPos, $pdf->GetY(), $imageWidth, 0, '', '', '', true, 150, '', false, false, 1, false, false, false);
                
                $xPos += $imageWidth + 0.1;
                $imageCount++;

            } catch (\Exception $e) {
                // Skip image if it fails to load
                continue;
            }
        }

        if ($imageCount > 0) {
            $pdf->Ln(1.8);
        }
    }

    /**
     * Map font name to TCPDF-compatible font
     */
    private function mapFont(string $fontName): string {
        $fontMap = [
            'Roboto' => 'helvetica',
            'Open Sans' => 'helvetica',
            'Lato' => 'helvetica',
            'Montserrat' => 'helvetica',
            'Poppins' => 'helvetica',
            'Arial' => 'helvetica',
            'Georgia' => 'times',
            'Times New Roman' => 'times',
            'Courier New' => 'courier'
        ];

        return $fontMap[$fontName] ?? 'helvetica';
    }

    /**
     * Convert hex color to RGB array
     */
    private function hexToRGB(string $hex): array {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 6) {
            list($r, $g, $b) = [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ];
            return [$r, $g, $b];
        }
        
        return [0, 0, 0]; // Default to black
    }

    /**
     * Generate HTML version of portfolio (for preview or alternative export)
     */
    public function generateHTML(int $userId, array $itemIds = []): string {
        try {
            // Get user information
            $userRepo = new \UserRepository($this->db);
            $user = $userRepo->findById($userId);
            
            if (!$user) {
                return '<p>User not found</p>';
            }

            // Get portfolio items
            $allItems = $this->portfolioManager->getItems($userId);
            
            // Filter items if specific IDs provided
            if (!empty($itemIds)) {
                $allItems = array_filter($allItems, function($item) use ($itemIds) {
                    return in_array($item->getId(), $itemIds);
                });
            }

            // Get customization settings
            $settings = $this->customizationEngine->getSettings($userId);

            // Generate HTML
            $html = $this->buildHTML($user, $allItems, $settings);

            return $html;

        } catch (\Exception $e) {
            return '<p>Error generating HTML: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
    }

    /**
     * Build HTML content for portfolio
     */
    private function buildHTML(\User $user, array $items, CustomizationSettings $settings): string {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<title>' . htmlspecialchars($user->fullName) . ' - Portfolio</title>';
        $html .= '<style>' . $this->generateHTMLStyles($settings) . '</style>';
        $html .= '</head><body>';
        
        // Header
        $html .= '<header>';
        $html .= '<h1>' . htmlspecialchars($user->fullName) . '</h1>';
        $html .= '<p class="program">' . htmlspecialchars($user->program) . ' Student</p>';
        $html .= '</header>';

        // Profile section
        if ($user->bio || $user->contactInfo) {
            $html .= '<section class="profile">';
            if ($user->bio) {
                $html .= '<h2>About</h2>';
                $html .= '<p>' . nl2br(htmlspecialchars($user->bio)) . '</p>';
            }
            if ($user->contactInfo) {
                $html .= '<h2>Contact</h2>';
                $html .= '<ul>';
                foreach ($user->contactInfo as $key => $value) {
                    if (!empty($value)) {
                        $label = ucfirst(str_replace('_', ' ', $key));
                        $html .= '<li><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars($value) . '</li>';
                    }
                }
                $html .= '</ul>';
            }
            $html .= '</section>';
        }

        // Portfolio items
        if (!empty($items)) {
            $groupedItems = [];
            foreach ($items as $item) {
                $type = $item->getItemType();
                if (!isset($groupedItems[$type])) {
                    $groupedItems[$type] = [];
                }
                $groupedItems[$type][] = $item;
            }

            $typeLabels = [
                'project' => 'Projects',
                'achievement' => 'Achievements',
                'milestone' => 'Milestones',
                'skill' => 'Skills'
            ];

            foreach ($groupedItems as $type => $typeItems) {
                $html .= '<section class="portfolio-section">';
                $html .= '<h2>' . htmlspecialchars($typeLabels[$type] ?? ucfirst($type)) . '</h2>';
                
                foreach ($typeItems as $item) {
                    $html .= '<article class="portfolio-item">';
                    $html .= '<h3>' . htmlspecialchars($item->getTitle()) . '</h3>';
                    if ($item->getItemDate()) {
                        $html .= '<p class="date">' . date('F Y', strtotime($item->getItemDate())) . '</p>';
                    }
                    $html .= '<p>' . nl2br(htmlspecialchars($item->getDescription())) . '</p>';
                    
                    if (!empty($item->getTags())) {
                        $html .= '<p class="tags">Tags: ' . htmlspecialchars(implode(', ', $item->getTags())) . '</p>';
                    }
                    
                    if (!empty($item->getLinks())) {
                        $html .= '<ul class="links">';
                        foreach ($item->getLinks() as $link) {
                            if (is_array($link) && isset($link['url'])) {
                                $linkText = $link['label'] ?? $link['url'];
                                $html .= '<li><a href="' . htmlspecialchars($link['url']) . '">' . htmlspecialchars($linkText) . '</a></li>';
                            } elseif (is_string($link)) {
                                $html .= '<li><a href="' . htmlspecialchars($link) . '">' . htmlspecialchars($link) . '</a></li>';
                            }
                        }
                        $html .= '</ul>';
                    }
                    
                    $html .= '</article>';
                }
                
                $html .= '</section>';
            }
        }

        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Generate CSS styles for HTML export
     */
    private function generateHTMLStyles(CustomizationSettings $settings): string {
        return "
            body {
                font-family: {$settings->bodyFont}, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 8.5in;
                margin: 0 auto;
                padding: 0.5in;
            }
            header {
                text-align: center;
                border-bottom: 2px solid {$settings->accentColor};
                padding-bottom: 1em;
                margin-bottom: 2em;
            }
            h1 {
                font-family: {$settings->headingFont}, sans-serif;
                color: {$settings->primaryColor};
                margin: 0;
                font-size: 2em;
            }
            h2 {
                font-family: {$settings->headingFont}, sans-serif;
                color: {$settings->primaryColor};
                font-size: 1.5em;
                margin-top: 1.5em;
            }
            h3 {
                font-family: {$settings->headingFont}, sans-serif;
                color: {$settings->accentColor};
                font-size: 1.2em;
                margin-bottom: 0.5em;
            }
            .program {
                color: #666;
                font-size: 1.1em;
            }
            .profile {
                margin-bottom: 2em;
            }
            .portfolio-section {
                margin-bottom: 2em;
            }
            .portfolio-item {
                margin-bottom: 1.5em;
                padding-bottom: 1em;
                border-bottom: 1px solid #eee;
            }
            .date {
                color: #666;
                font-style: italic;
                margin: 0.5em 0;
            }
            .tags {
                color: #555;
                font-size: 0.9em;
            }
            .links {
                list-style: none;
                padding: 0;
            }
            .links li {
                margin: 0.3em 0;
            }
            .links a {
                color: {$settings->primaryColor};
                text-decoration: none;
            }
            .links a:hover {
                text-decoration: underline;
            }
        ";
    }
}
