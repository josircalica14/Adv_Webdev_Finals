<?php

/**
 * Test Data Structures
 * 
 * Helper classes and structures for integration tests
 */

/**
 * PortfolioItemData - Data transfer object for portfolio item creation/updates
 */
class PortfolioItemData {
    public string $itemType;
    public string $title;
    public string $description;
    public ?string $itemDate = null;
    public array $tags = [];
    public array $links = [];
    public bool $isVisible = true;
    
    public function toArray(): array {
        return [
            'item_type' => $this->itemType,
            'title' => $this->title,
            'description' => $this->description,
            'item_date' => $this->itemDate,
            'tags' => $this->tags,
            'links' => $this->links,
            'is_visible' => $this->isVisible
        ];
    }
}

/**
 * SearchCriteria - Data transfer object for search and filter operations
 */
class SearchCriteria {
    public ?string $query = null;
    public ?string $program = null;
    public ?string $sortBy = null;
    public array $tags = [];
    
    public function toArray(): array {
        return [
            'query' => $this->query,
            'program' => $this->program,
            'sortBy' => $this->sortBy,
            'tags' => $this->tags
        ];
    }
}

/**
 * CustomizationSettings - Data transfer object for customization settings
 */
class CustomizationSettings {
    public string $theme = 'default';
    public string $layout = 'grid';
    public string $primaryColor = '#3498db';
    public string $accentColor = '#e74c3c';
    public string $headingFont = 'Roboto';
    public string $bodyFont = 'Open Sans';
    
    public function toArray(): array {
        return [
            'theme' => $this->theme,
            'layout' => $this->layout,
            'primary_color' => $this->primaryColor,
            'accent_color' => $this->accentColor,
            'heading_font' => $this->headingFont,
            'body_font' => $this->bodyFont
        ];
    }
    
    public static function fromArray(array $data): self {
        $settings = new self();
        $settings->theme = $data['theme'] ?? 'default';
        $settings->layout = $data['layout'] ?? 'grid';
        $settings->primaryColor = $data['primary_color'] ?? '#3498db';
        $settings->accentColor = $data['accent_color'] ?? '#e74c3c';
        $settings->headingFont = $data['heading_font'] ?? 'Roboto';
        $settings->bodyFont = $data['body_font'] ?? 'Open Sans';
        return $settings;
    }
}

/**
 * Helper function to get test database configuration
 */
function getTestDatabaseConfig(): array {
    return [
        'host' => getenv('TEST_DB_HOST') ?: 'localhost',
        'name' => getenv('TEST_DB_NAME') ?: 'portfolio_test',
        'user' => getenv('TEST_DB_USER') ?: 'root',
        'pass' => getenv('TEST_DB_PASS') ?: ''
    ];
}

/**
 * UploadedFile - Mock uploaded file for testing
 */
class UploadedFile {
    public string $tmpName;
    public string $name;
    public string $type;
    public int $size;
    public int $error = 0;
    
    public function __construct(string $tmpName, string $name, string $type, int $size, int $error = 0) {
        $this->tmpName = $tmpName;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
    }
    
    public function toArray(): array {
        return [
            'tmp_name' => $this->tmpName,
            'name' => $this->name,
            'type' => $this->type,
            'size' => $this->size,
            'error' => $this->error
        ];
    }
}
