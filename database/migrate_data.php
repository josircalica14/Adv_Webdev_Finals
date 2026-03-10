<?php
/**
 * Data Migration Script
 * 
 * Converts existing single-user portfolio data from JavaScript files
 * to the multi-user database schema.
 * 
 * Run this script from the command line: php database/migrate_data.php
 */

require_once __DIR__ . '/../includes/bootstrap.php';

class DataMigration {
    private Database $db;
    private array $report = [];
    private int $userId;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Initialize report arrays
        $this->report['files_migrated'] = [];
        $this->report['file_warnings'] = [];
        $this->report['file_errors'] = [];
    }
    
    /**
     * Run the complete data migration
     * 
     * @return array Migration report
     */
    public function run(): array {
        echo "=== Data Migration: Single-User to Multi-User ===\n\n";
        
        // Validate prerequisites
        echo "Validating prerequisites...\n";
        $validation = $this->validatePrerequisites();
        
        if (!$validation['valid']) {
            $this->report['success'] = false;
            $this->report['error'] = 'Prerequisites validation failed';
            $this->report['validation_errors'] = $validation['errors'];
            
            echo "✗ Prerequisites validation failed:\n";
            foreach ($validation['errors'] as $error) {
                echo "  - {$error}\n";
            }
            
            return $this->report;
        }
        
        echo "✓ Prerequisites validated\n\n";
        
        try {
            $this->db->beginTransaction();
            
            // Step 1: Create default admin account
            echo "Step 1: Creating default admin account...\n";
            $this->userId = $this->createDefaultAdmin();
            $this->report['admin_created'] = true;
            $this->report['user_id'] = $this->userId;
            echo "✓ Admin account created (ID: {$this->userId})\n\n";
            
            // Step 2: Create portfolio for admin
            echo "Step 2: Creating portfolio...\n";
            $portfolioId = $this->createPortfolio();
            $this->report['portfolio_id'] = $portfolioId;
            echo "✓ Portfolio created (ID: {$portfolioId})\n\n";
            
            // Step 3: Migrate projects
            echo "Step 3: Migrating projects...\n";
            $projectsResult = $this->migrateProjects($portfolioId);
            $this->report['projects'] = $projectsResult;
            echo "✓ Migrated {$projectsResult['count']} projects\n\n";
            
            // Step 4: Migrate skills
            echo "Step 4: Migrating skills...\n";
            $skillsResult = $this->migrateSkills($portfolioId);
            $this->report['skills'] = $skillsResult;
            echo "✓ Migrated {$skillsResult['count']} skills\n\n";
            
            // Step 5: Create default customization settings
            echo "Step 5: Creating default customization settings...\n";
            $this->createDefaultCustomization($portfolioId);
            $this->report['customization_created'] = true;
            echo "✓ Customization settings created\n\n";
            
            $this->db->commit();
            
            $this->report['success'] = true;
            $this->report['message'] = 'Migration completed successfully';
            
            echo "=== Migration Complete ===\n";
            $this->printReport();
            
            return $this->report;
            
        } catch (Exception $e) {
            $this->db->rollback();
            $this->report['success'] = false;
            $this->report['error'] = $e->getMessage();
            
            echo "✗ Migration failed: {$e->getMessage()}\n";
            
            return $this->report;
        }
    }
    
    /**
     * Validate prerequisites before migration
     * 
     * @return array Validation result
     */
    private function validatePrerequisites(): array {
        $errors = [];
        
        // Check if data files exist
        $projectsFile = __DIR__ . '/../data/projects-data.js';
        $skillsFile = __DIR__ . '/../data/skills-data.js';
        
        if (!file_exists($projectsFile)) {
            $errors[] = "Projects data file not found: {$projectsFile}";
        }
        
        if (!file_exists($skillsFile)) {
            $errors[] = "Skills data file not found: {$skillsFile}";
        }
        
        // Check if database tables exist
        $requiredTables = [
            'users', 'portfolios', 'portfolio_items', 'files', 
            'customization_settings'
        ];
        
        foreach ($requiredTables as $table) {
            try {
                $this->db->query("SELECT 1 FROM {$table} LIMIT 1");
            } catch (Exception $e) {
                $errors[] = "Required table '{$table}' does not exist. Run database migrations first.";
            }
        }
        
        // Check if admin user already exists
        try {
            $result = $this->db->query(
                "SELECT id FROM users WHERE email = :email",
                ['email' => 'admin@portfolio.local']
            );
            
            if (!empty($result)) {
                $errors[] = "Admin user already exists. Migration may have already been run.";
            }
        } catch (Exception $e) {
            // Table doesn't exist, already caught above
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Create default admin account from existing portfolio
     * 
     * @return int User ID
     */
    private function createDefaultAdmin(): int {
        $sql = "INSERT INTO users (email, password_hash, full_name, program, username, 
                bio, is_verified, is_admin, created_at) 
                VALUES (:email, :password_hash, :full_name, :program, :username, 
                :bio, :is_verified, :is_admin, NOW())";
        
        $params = [
            'email' => 'admin@portfolio.local',
            'password_hash' => password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]),
            'full_name' => 'Portfolio Admin',
            'program' => 'BSIT',
            'username' => 'admin',
            'bio' => 'Original portfolio owner - migrated from single-user system',
            'is_verified' => 1,
            'is_admin' => 1
        ];
        
        $this->db->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Create portfolio for the admin user
     * 
     * @return int Portfolio ID
     */
    private function createPortfolio(): int {
        $sql = "INSERT INTO portfolios (user_id, is_public, created_at) 
                VALUES (:user_id, :is_public, NOW())";
        
        $params = [
            'user_id' => $this->userId,
            'is_public' => 1
        ];
        
        $this->db->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Migrate projects from projects-data.js
     * 
     * @param int $portfolioId Portfolio ID
     * @return array Migration result
     */
    private function migrateProjects(int $portfolioId): array {
        $projectsFile = __DIR__ . '/../data/projects-data.js';
        
        if (!file_exists($projectsFile)) {
            throw new Exception("Projects data file not found: {$projectsFile}");
        }
        
        $content = file_get_contents($projectsFile);
        
        // Extract JSON data from JavaScript file
        preg_match('/const projectsData = (\[.*?\]);/s', $content, $matches);
        
        if (!isset($matches[1])) {
            throw new Exception("Could not parse projects data");
        }
        
        $projects = json_decode($matches[1], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in projects data: " . json_last_error_msg());
        }
        
        $migrated = [];
        $errors = [];
        
        foreach ($projects as $index => $project) {
            try {
                // Build comprehensive description
                $description = $project['detailedDescription'] ?? $project['description'] ?? '';
                
                if (!empty($project['features']) && is_array($project['features'])) {
                    $description .= "\n\nKey Features:\n";
                    foreach ($project['features'] as $feature) {
                        $description .= "• " . $feature . "\n";
                    }
                }
                
                $itemId = $this->createPortfolioItem($portfolioId, [
                    'item_type' => 'project',
                    'title' => $project['title'] ?? "Project " . ($index + 1),
                    'description' => trim($description),
                    'tags' => json_encode([
                        'technologies' => $project['technologies'] ?? [],
                        'category' => $project['category'] ?? 'General'
                    ]),
                    'links' => json_encode(array_filter([
                        'live' => $project['liveUrl'] ?? null,
                        'github' => $project['githubUrl'] ?? null
                    ])),
                    'display_order' => $index
                ]);
                
                // Migrate file references
                $this->migrateFileReferences($itemId, $project);
                
                $migrated[] = [
                    'id' => $itemId,
                    'title' => $project['title'] ?? "Project " . ($index + 1),
                    'original_id' => $project['id'] ?? null
                ];
                
            } catch (Exception $e) {
                $errors[] = [
                    'project' => $project['title'] ?? "Unknown",
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'count' => count($migrated),
            'items' => $migrated,
            'errors' => $errors
        ];
    }
    
    /**
     * Migrate skills from skills-data.js
     * 
     * @param int $portfolioId Portfolio ID
     * @return array Migration result
     */
    private function migrateSkills(int $portfolioId): array {
        $skillsFile = __DIR__ . '/../data/skills-data.js';
        
        if (!file_exists($skillsFile)) {
            throw new Exception("Skills data file not found: {$skillsFile}");
        }
        
        $content = file_get_contents($skillsFile);
        
        // Extract JSON data from JavaScript file
        preg_match('/const skillsData = (\{.*?\});/s', $content, $matches);
        
        if (!isset($matches[1])) {
            throw new Exception("Could not parse skills data");
        }
        
        $skillsData = json_decode($matches[1], true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in skills data: " . json_last_error_msg());
        }
        
        $migrated = [];
        $errors = [];
        $displayOrder = 0;
        
        foreach ($skillsData as $category => $skills) {
            foreach ($skills as $skill) {
                try {
                    $itemId = $this->createPortfolioItem($portfolioId, [
                        'item_type' => 'skill',
                        'title' => $skill['name'] ?? 'Unknown Skill',
                        'description' => "Proficiency: {$skill['level']}% | Experience: {$skill['yearsExperience']} years",
                        'tags' => json_encode([
                            'category' => $category,
                            'level' => $skill['level'] ?? 0,
                            'icon' => $skill['icon'] ?? ''
                        ]),
                        'display_order' => $displayOrder++
                    ]);
                    
                    $migrated[] = [
                        'id' => $itemId,
                        'title' => $skill['name'] ?? 'Unknown Skill',
                        'category' => $category
                    ];
                    
                } catch (Exception $e) {
                    $errors[] = [
                        'skill' => $skill['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
        }
        
        return [
            'count' => count($migrated),
            'items' => $migrated,
            'errors' => $errors
        ];
    }
    
    /**
     * Create a portfolio item
     * 
     * @param int $portfolioId Portfolio ID
     * @param array $data Item data
     * @return int Item ID
     */
    private function createPortfolioItem(int $portfolioId, array $data): int {
        $sql = "INSERT INTO portfolio_items (portfolio_id, item_type, title, description, 
                tags, links, display_order, created_at) 
                VALUES (:portfolio_id, :item_type, :title, :description, 
                :tags, :links, :display_order, NOW())";
        
        $params = [
            'portfolio_id' => $portfolioId,
            'item_type' => $data['item_type'],
            'title' => $data['title'],
            'description' => $data['description'],
            'tags' => $data['tags'] ?? null,
            'links' => $data['links'] ?? null,
            'display_order' => $data['display_order'] ?? 0
        ];
        
        $this->db->query($sql, $params);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Create default customization settings matching current styling
     * 
     * @param int $portfolioId Portfolio ID
     */
    private function createDefaultCustomization(int $portfolioId): void {
        $sql = "INSERT INTO customization_settings (portfolio_id, theme, layout, 
                primary_color, accent_color, heading_font, body_font, created_at) 
                VALUES (:portfolio_id, :theme, :layout, :primary_color, :accent_color, 
                :heading_font, :body_font, NOW())";
        
        // Match existing portfolio styling from css/style.css
        $params = [
            'portfolio_id' => $portfolioId,
            'theme' => 'default',
            'layout' => 'grid',
            'primary_color' => '#2d7a4f', // --accent from existing CSS
            'accent_color' => '#d6a5ad',  // Logo background from existing CSS
            'heading_font' => 'Arial',
            'body_font' => 'Arial'
        ];
        
        $this->db->query($sql, $params);
    }
    
    /**
     * Preserve and update file references from projects
     * 
     * @param int $itemId Portfolio item ID
     * @param array $project Project data with file references
     */
    private function migrateFileReferences(int $itemId, array $project): void {
        // Handle thumbnail
        if (!empty($project['thumbnail'])) {
            $this->createFileRecord($itemId, $project['thumbnail'], 'thumbnail');
        }
        
        // Handle screenshots
        if (!empty($project['screenshots']) && is_array($project['screenshots'])) {
            foreach ($project['screenshots'] as $screenshot) {
                if (!empty($screenshot)) {
                    $this->createFileRecord($itemId, $screenshot, 'screenshot');
                }
            }
        }
    }
    
    /**
     * Create file record in database
     * 
     * @param int $itemId Portfolio item ID
     * @param string $filename Original filename
     * @param string $type File type (thumbnail, screenshot)
     */
    private function createFileRecord(int $itemId, string $filename, string $type): void {
        // Check if file exists
        $filePath = __DIR__ . '/../' . $filename;
        
        if (!file_exists($filePath)) {
            $this->report['file_warnings'][] = "File not found: {$filename}";
            return;
        }
        
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);
        
        $sql = "INSERT INTO files (portfolio_item_id, user_id, original_filename, 
                stored_filename, file_path, file_type, file_size, created_at) 
                VALUES (:portfolio_item_id, :user_id, :original_filename, 
                :stored_filename, :file_path, :file_type, :file_size, NOW())";
        
        $params = [
            'portfolio_item_id' => $itemId,
            'user_id' => $this->userId,
            'original_filename' => basename($filename),
            'stored_filename' => basename($filename),
            'file_path' => $filename,
            'file_type' => $mimeType,
            'file_size' => $fileSize
        ];
        
        try {
            $this->db->query($sql, $params);
            $this->report['files_migrated'][] = $filename;
        } catch (Exception $e) {
            $this->report['file_errors'][] = [
                'file' => $filename,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Print migration report
     */
    private function printReport(): void {
        echo "\n=== Migration Report ===\n";
        echo "Status: " . ($this->report['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        echo "User ID: {$this->report['user_id']}\n";
        echo "Portfolio ID: {$this->report['portfolio_id']}\n";
        echo "Projects migrated: {$this->report['projects']['count']}\n";
        echo "Skills migrated: {$this->report['skills']['count']}\n";
        
        if (!empty($this->report['files_migrated'])) {
            echo "Files migrated: " . count($this->report['files_migrated']) . "\n";
        }
        
        if (!empty($this->report['file_warnings'])) {
            echo "\nFile warnings:\n";
            foreach ($this->report['file_warnings'] as $warning) {
                echo "  ⚠ {$warning}\n";
            }
        }
        
        if (!empty($this->report['projects']['errors'])) {
            echo "\nProject errors:\n";
            foreach ($this->report['projects']['errors'] as $error) {
                echo "  ✗ {$error['project']}: {$error['error']}\n";
            }
        }
        
        if (!empty($this->report['skills']['errors'])) {
            echo "\nSkill errors:\n";
            foreach ($this->report['skills']['errors'] as $error) {
                echo "  ✗ {$error['skill']}: {$error['error']}\n";
            }
        }
        
        if (!empty($this->report['file_errors'])) {
            echo "\nFile errors:\n";
            foreach ($this->report['file_errors'] as $error) {
                echo "  ✗ {$error['file']}: {$error['error']}\n";
            }
        }
        
        echo "\n=== Summary ===\n";
        echo "Total items migrated: " . ($this->report['projects']['count'] + $this->report['skills']['count']) . "\n";
        echo "Total errors: " . (count($this->report['projects']['errors']) + count($this->report['skills']['errors'])) . "\n";
        
        if ($this->report['success']) {
            echo "\n✓ Migration completed successfully!\n";
            echo "\nDefault admin credentials:\n";
            echo "  Email: admin@portfolio.local\n";
            echo "  Password: admin123\n";
            echo "  (Please change these credentials after first login)\n";
        }
    }
    
    /**
     * Get migration report
     * 
     * @return array
     */
    public function getReport(): array {
        return $this->report;
    }
}

// Run migration if executed directly
if (php_sapi_name() === 'cli') {
    $migration = new DataMigration();
    $result = $migration->run();
    exit($result['success'] ? 0 : 1);
}
