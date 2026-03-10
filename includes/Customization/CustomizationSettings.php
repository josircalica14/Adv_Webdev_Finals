<?php

namespace Customization;

/**
 * CustomizationSettings Entity
 * 
 * Represents visual and layout preferences for a student's portfolio.
 * Validates: Requirements 6.1, 6.2, 6.3, 6.4
 */
class CustomizationSettings {
    public int $id;
    public int $portfolioId;
    public string $theme;
    public string $layout; // 'grid', 'list', 'timeline'
    public string $primaryColor;
    public string $accentColor;
    public string $headingFont;
    public string $bodyFont;
    public string $createdAt;
    public string $updatedAt;

    /**
     * Convert settings to associative array
     */
    public function toArray(): array {
        return [
            'id' => $this->id ?? null,
            'portfolio_id' => $this->portfolioId,
            'theme' => $this->theme,
            'layout' => $this->layout,
            'primary_color' => $this->primaryColor,
            'accent_color' => $this->accentColor,
            'heading_font' => $this->headingFont,
            'body_font' => $this->bodyFont,
            'created_at' => $this->createdAt ?? null,
            'updated_at' => $this->updatedAt ?? null
        ];
    }

    /**
     * Create CustomizationSettings from associative array
     */
    public static function fromArray(array $data): self {
        $settings = new self();
        $settings->id = $data['id'] ?? 0;
        $settings->portfolioId = $data['portfolio_id'];
        $settings->theme = $data['theme'];
        $settings->layout = $data['layout'];
        $settings->primaryColor = $data['primary_color'];
        $settings->accentColor = $data['accent_color'];
        $settings->headingFont = $data['heading_font'];
        $settings->bodyFont = $data['body_font'];
        $settings->createdAt = $data['created_at'] ?? '';
        $settings->updatedAt = $data['updated_at'] ?? '';
        return $settings;
    }

    /**
     * Get default customization settings
     */
    public static function getDefaults(): self {
        $settings = new self();
        $settings->theme = 'default';
        $settings->layout = 'grid';
        $settings->primaryColor = '#3498db';
        $settings->accentColor = '#e74c3c';
        $settings->headingFont = 'Roboto';
        $settings->bodyFont = 'Open Sans';
        return $settings;
    }

    /**
     * Validate settings values
     */
    public function validate(): array {
        $errors = [];

        // Validate layout
        $validLayouts = ['grid', 'list', 'timeline'];
        if (!in_array($this->layout, $validLayouts)) {
            $errors[] = "Layout must be one of: " . implode(', ', $validLayouts);
        }

        // Validate color format (hex color)
        if (!$this->isValidHexColor($this->primaryColor)) {
            $errors[] = "Primary color must be a valid hex color (e.g., #3498db)";
        }

        if (!$this->isValidHexColor($this->accentColor)) {
            $errors[] = "Accent color must be a valid hex color (e.g., #e74c3c)";
        }

        // Validate fonts (basic validation - not empty)
        if (empty(trim($this->headingFont))) {
            $errors[] = "Heading font cannot be empty";
        }

        if (empty(trim($this->bodyFont))) {
            $errors[] = "Body font cannot be empty";
        }

        return $errors;
    }

    /**
     * Check if a string is a valid hex color
     */
    private function isValidHexColor(string $color): bool {
        return preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1;
    }
}
