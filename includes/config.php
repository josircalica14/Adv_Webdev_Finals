<?php
/**
 * Configuration Management Class
 * 
 * Manages application configuration including database credentials,
 * file paths, and security settings.
 */

class Config {
    private static ?Config $instance = null;
    private array $config = [];
    private string $configFile;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct() {
        $this->configFile = __DIR__ . '/../config/app.config.php';
        $this->loadConfig();
    }

    /**
     * Get singleton instance of Config
     * 
     * @return Config
     */
    public static function getInstance(): Config {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load configuration from file
     * 
     * @throws Exception if config file doesn't exist
     */
    private function loadConfig(): void {
        if (!file_exists($this->configFile)) {
            // Create default config if it doesn't exist
            $this->createDefaultConfig();
        }
        
        $this->config = require $this->configFile;
    }

    /**
     * Create default configuration file
     */
    private function createDefaultConfig(): void {
        $defaultConfig = [
            'db' => [
                'host' => 'localhost',
                'port' => 3306,
                'name' => 'portfolio_platform',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ],
            'paths' => [
                'uploads' => __DIR__ . '/../uploads',
                'thumbnails' => __DIR__ . '/../uploads/thumbnails',
                'temp' => __DIR__ . '/../temp',
                'logs' => __DIR__ . '/../logs'
            ],
            'security' => [
                'session_lifetime' => 86400, // 24 hours in seconds
                'csrf_token_name' => 'csrf_token',
                'password_cost' => 12,
                'max_login_attempts' => 5,
                'login_attempt_window' => 900, // 15 minutes in seconds
                'max_upload_attempts' => 20,
                'upload_attempt_window' => 3600 // 1 hour in seconds
            ],
            'files' => [
                'max_file_size' => 10485760, // 10MB in bytes
                'max_profile_photo_size' => 5242880, // 5MB in bytes
                'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                'allowed_document_types' => ['application/pdf'],
                'max_files_per_item' => 10
            ],
            'email' => [
                'smtp_host' => 'localhost',
                'smtp_port' => 587,
                'smtp_username' => '',
                'smtp_password' => '',
                'smtp_encryption' => 'tls',
                'from_email' => 'noreply@portfolio-platform.local',
                'from_name' => 'Portfolio Platform'
            ],
            'app' => [
                'name' => 'Portfolio Platform',
                'url' => 'http://localhost',
                'debug' => true,
                'timezone' => 'UTC'
            ]
        ];

        $configDir = dirname($this->configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configContent = "<?php\n\n// Application Configuration\n// DO NOT commit this file to version control\n\nreturn " . var_export($defaultConfig, true) . ";\n";
        file_put_contents($this->configFile, $configContent);
    }

    /**
     * Get configuration value by key
     * 
     * @param string $key Dot-notation key (e.g., 'db.host')
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null) {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value by key
     * 
     * @param string $key Dot-notation key (e.g., 'db.host')
     * @param mixed $value Value to set
     */
    public function set(string $key, $value): void {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }

    /**
     * Check if configuration key exists
     * 
     * @param string $key Dot-notation key
     * @return bool
     */
    public function has(string $key): bool {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return true;
    }

    /**
     * Get all configuration
     * 
     * @return array
     */
    public function all(): array {
        return $this->config;
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
