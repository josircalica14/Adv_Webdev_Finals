<?php

namespace Customization;

use PDO;
use Portfolio\PortfolioRepository;

/**
 * CustomizationEngine
 * 
 * Manages portfolio customization settings and CSS generation.
 * Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5, 6.7
 */
class CustomizationEngine {
    private PDO $db;
    private CustomizationSettingsRepository $settingsRepository;
    private PortfolioRepository $portfolioRepository;

    // Available themes
    private const THEMES = [
        'default' => 'Default Theme',
        'dark' => 'Dark Theme',
        'light' => 'Light Theme',
        'professional' => 'Professional Theme',
        'creative' => 'Creative Theme'
    ];

    // Available fonts
    private const FONTS = [
        'Roboto' => 'Roboto',
        'Open Sans' => 'Open Sans',
        'Lato' => 'Lato',
        'Montserrat' => 'Montserrat',
        'Poppins' => 'Poppins',
        'Raleway' => 'Raleway',
        'Ubuntu' => 'Ubuntu',
        'Nunito' => 'Nunito',
        'Playfair Display' => 'Playfair Display',
        'Merriweather' => 'Merriweather'
    ];

    public function __construct(PDO $db, PortfolioRepository $portfolioRepository = null) {
        $this->db = $db;
        $this->settingsRepository = new CustomizationSettingsRepository($db);
        $this->portfolioRepository = $portfolioRepository ?? new PortfolioRepository($db);
    }

    /**
     * Get customization settings for a user
     * Creates default settings if none exist
     * 
     * Validates: Requirement 6.1
     */
    public function getSettings(int $userId): ?CustomizationSettings {
        // Get user's portfolio
        $portfolio = $this->portfolioRepository->findByUserId($userId);
        if (!$portfolio) {
            return null;
        }

        // Try to find existing settings
        $settings = $this->settingsRepository->findByPortfolioId($portfolio->getId());

        // If no settings exist, create defaults
        if (!$settings) {
            $settings = CustomizationSettings::getDefaults();
            $settings->portfolioId = $portfolio->getId();
            
            $settingsId = $this->settingsRepository->create($settings);
            if ($settingsId) {
                $settings->id = $settingsId;
            }
        }

        return $settings;
    }

    /**
     * Update customization settings for a user
     * 
     * Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5
     */
    public function updateSettings(int $userId, CustomizationSettings $settings): array {
        $result = ['success' => false, 'errors' => []];

        // Get user's portfolio
        $portfolio = $this->portfolioRepository->findByUserId($userId);
        if (!$portfolio) {
            $result['errors'][] = "Portfolio not found for user";
            return $result;
        }

        // Validate settings
        $validationErrors = $settings->validate();
        if (!empty($validationErrors)) {
            $result['errors'] = $validationErrors;
            return $result;
        }

        // Set portfolio ID
        $settings->portfolioId = $portfolio->getId();

        // Check if settings exist
        $existingSettings = $this->settingsRepository->findByPortfolioId($portfolio->getId());

        if ($existingSettings) {
            // Update existing settings
            $success = $this->settingsRepository->update($settings);
        } else {
            // Create new settings
            $settingsId = $this->settingsRepository->create($settings);
            $success = $settingsId !== null;
        }

        $result['success'] = $success;
        if (!$success) {
            $result['errors'][] = "Failed to save customization settings";
        }

        return $result;
    }

    /**
     * Reset customization settings to defaults
     * 
     * Validates: Requirement 6.7
     */
    public function resetToDefaults(int $userId): bool {
        // Get user's portfolio
        $portfolio = $this->portfolioRepository->findByUserId($userId);
        if (!$portfolio) {
            return false;
        }

        // Get default settings
        $defaults = CustomizationSettings::getDefaults();
        $defaults->portfolioId = $portfolio->getId();

        // Check if settings exist
        $existingSettings = $this->settingsRepository->findByPortfolioId($portfolio->getId());

        if ($existingSettings) {
            // Update to defaults
            return $this->settingsRepository->update($defaults);
        } else {
            // Create defaults
            return $this->settingsRepository->create($defaults) !== null;
        }
    }

    /**
     * Generate custom CSS from settings
     * 
     * Validates: Requirement 6.5
     */
    public function generateCSS(CustomizationSettings $settings): string {
        $css = "/* Custom Portfolio Styles */\n\n";

        // Website theme default colors
        $websitePrimaryColor = '#0f0f0f';
        $websiteAccentColor = '#d6a5ad';
        
        // Default customization colors (from getDefaults)
        $defaultPrimaryColor = '#3498db';
        $defaultAccentColor = '#e74c3c';
        
        // Use website theme colors if user hasn't changed from defaults
        $primaryColor = ($settings->primaryColor === $defaultPrimaryColor) ? $websitePrimaryColor : $settings->primaryColor;
        $accentColor = ($settings->accentColor === $defaultAccentColor) ? $websiteAccentColor : $settings->accentColor;

        // Import Google Fonts
        $css .= $this->generateFontImports($settings);

        // Root variables
        $css .= ":root {\n";
        $css .= "    --primary-color: {$primaryColor} !important;\n";
        $css .= "    --accent-color: {$accentColor} !important;\n";
        $css .= "    --heading-font: '{$settings->headingFont}', sans-serif !important;\n";
        $css .= "    --body-font: '{$settings->bodyFont}', sans-serif !important;\n";
        $css .= "}\n\n";

        // Apply fonts
        $css .= "body {\n";
        $css .= "    font-family: var(--body-font) !important;\n";
        $css .= "}\n\n";

        $css .= "h1, h2, h3, h4, h5, h6, .portfolio-name, .bowls-header h3, .skills-category-title, .project-title {\n";
        $css .= "    font-family: var(--heading-font) !important;\n";
        $css .= "}\n\n";

        // Apply primary color to main elements
        $css .= ".portfolio-program-badge, .project-type-badge.type-project, .skill-tag.technical-skill {\n";
        $css .= "    background: var(--primary-color) !important;\n";
        $css .= "    border-color: var(--primary-color) !important;\n";
        $css .= "}\n\n";

        // Apply accent color to hover states and accents
        $css .= ".portfolio-photo {\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".portfolio-program-badge:hover {\n";
        $css .= "    background: var(--accent-color) !important;\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "    color: #0f0f0f !important;\n";
        $css .= "}\n\n";

        $css .= ".back-to-showcase-btn:hover, .project-card:hover {\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".project-type-badge.type-achievement {\n";
        $css .= "    background: var(--accent-color) !important;\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".project-tag:hover, .skill-tag.technical-skill:hover {\n";
        $css .= "    background: var(--accent-color) !important;\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".portfolio-photo, .project-image-placeholder i, .project-author i, .project-date i, .project-view-link, .skills-category-title i {\n";
        $css .= "    color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".project-tag, .skills-category-title {\n";
        $css .= "    background-color: var(--accent-color) !important;\n";
        $css .= "    border-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        $css .= ".project-card:hover .project-image-placeholder {\n";
        $css .= "    background-color: var(--accent-color) !important;\n";
        $css .= "}\n\n";

        // Layout-specific styles
        $css .= $this->generateLayoutCSS($settings->layout);

        // Theme-specific styles
        $css .= $this->generateThemeCSS($settings->theme);

        return $css;
    }

    /**
     * Generate Google Fonts import statements
     */
    private function generateFontImports(CustomizationSettings $settings): string {
        $fonts = [];
        
        if (!empty($settings->headingFont)) {
            $fonts[] = str_replace(' ', '+', $settings->headingFont);
        }
        
        if (!empty($settings->bodyFont) && $settings->bodyFont !== $settings->headingFont) {
            $fonts[] = str_replace(' ', '+', $settings->bodyFont);
        }

        if (empty($fonts)) {
            return "";
        }

        $fontString = implode('|', $fonts);
        return "@import url('https://fonts.googleapis.com/css2?family={$fontString}:wght@300;400;500;600;700&display=swap');\n\n";
    }

    /**
     * Generate layout-specific CSS
     */
    private function generateLayoutCSS(string $layout): string {
        $css = "/* Layout: {$layout} */\n";

        switch ($layout) {
            case 'grid':
                $css .= ".portfolio-items {\n";
                $css .= "    display: grid;\n";
                $css .= "    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));\n";
                $css .= "    gap: 2rem;\n";
                $css .= "}\n\n";
                break;

            case 'list':
                $css .= ".portfolio-items {\n";
                $css .= "    display: flex;\n";
                $css .= "    flex-direction: column;\n";
                $css .= "    gap: 1.5rem;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    display: flex;\n";
                $css .= "    flex-direction: row;\n";
                $css .= "    align-items: flex-start;\n";
                $css .= "}\n\n";
                break;

            case 'timeline':
                $css .= ".portfolio-items {\n";
                $css .= "    position: relative;\n";
                $css .= "    padding-left: 2rem;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-items::before {\n";
                $css .= "    content: '';\n";
                $css .= "    position: absolute;\n";
                $css .= "    left: 0;\n";
                $css .= "    top: 0;\n";
                $css .= "    bottom: 0;\n";
                $css .= "    width: 2px;\n";
                $css .= "    background: var(--primary-color);\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    position: relative;\n";
                $css .= "    margin-bottom: 2rem;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item::before {\n";
                $css .= "    content: '';\n";
                $css .= "    position: absolute;\n";
                $css .= "    left: -2.5rem;\n";
                $css .= "    top: 0.5rem;\n";
                $css .= "    width: 12px;\n";
                $css .= "    height: 12px;\n";
                $css .= "    border-radius: 50%;\n";
                $css .= "    background: var(--accent-color);\n";
                $css .= "    border: 2px solid white;\n";
                $css .= "}\n\n";
                break;
        }

        return $css;
    }

    /**
     * Generate theme-specific CSS
     */
    private function generateThemeCSS(string $theme): string {
        $css = "/* Theme: {$theme} */\n";

        switch ($theme) {
            case 'dark':
                $css .= "body {\n";
                $css .= "    background-color: #1a1a1a;\n";
                $css .= "    color: #e0e0e0;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    background-color: #2a2a2a;\n";
                $css .= "    border-color: #3a3a3a;\n";
                $css .= "}\n\n";
                break;

            case 'light':
                $css .= "body {\n";
                $css .= "    background-color: #ffffff;\n";
                $css .= "    color: #333333;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    background-color: #f9f9f9;\n";
                $css .= "    border-color: #e0e0e0;\n";
                $css .= "}\n\n";
                break;

            case 'professional':
                $css .= "body {\n";
                $css .= "    background-color: #f5f5f5;\n";
                $css .= "    color: #2c3e50;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    background-color: #ffffff;\n";
                $css .= "    border: 1px solid #ddd;\n";
                $css .= "    box-shadow: 0 2px 4px rgba(0,0,0,0.1);\n";
                $css .= "}\n\n";
                break;

            case 'creative':
                $css .= "body {\n";
                $css .= "    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n";
                $css .= "    color: #ffffff;\n";
                $css .= "}\n\n";
                $css .= ".portfolio-item {\n";
                $css .= "    background-color: rgba(255, 255, 255, 0.1);\n";
                $css .= "    backdrop-filter: blur(10px);\n";
                $css .= "    border: 1px solid rgba(255, 255, 255, 0.2);\n";
                $css .= "}\n\n";
                break;

            default: // 'default' theme
                $css .= "/* Using default theme styles */\n\n";
                break;
        }

        return $css;
    }

    /**
     * Get list of available themes
     * 
     * Validates: Requirement 6.1
     */
    public function getAvailableThemes(): array {
        return self::THEMES;
    }

    /**
     * Get list of available fonts
     * 
     * Validates: Requirement 6.4
     */
    public function getAvailableFonts(): array {
        return self::FONTS;
    }

    /**
     * Get settings by portfolio ID (for public viewing)
     */
    public function getSettingsByPortfolioId(int $portfolioId): ?CustomizationSettings {
        $settings = $this->settingsRepository->findByPortfolioId($portfolioId);
        
        // Return defaults if no settings exist
        if (!$settings) {
            $settings = CustomizationSettings::getDefaults();
            $settings->portfolioId = $portfolioId;
        }
        
        return $settings;
    }
}
