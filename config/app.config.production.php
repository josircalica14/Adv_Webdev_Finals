<?php

// Production Configuration Template
// Copy this file to app.config.php on production server
// Update all values with production credentials
// CRITICAL: Ensure this file has restricted permissions (chmod 600)

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'port' => (int)(getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'portfolio_platform',
        'username' => getenv('DB_USERNAME') ?: '',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset' => 'utf8mb4',
        // Production database options
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false, // Set to true for connection pooling if needed
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ]
    ],
    
    'paths' => [
        'uploads' => getenv('UPLOAD_PATH') ?: '/var/www/portfolio-platform/uploads',
        'thumbnails' => getenv('THUMBNAIL_PATH') ?: '/var/www/portfolio-platform/uploads/thumbnails',
        'temp' => getenv('TEMP_PATH') ?: '/var/www/portfolio-platform/temp',
        'logs' => getenv('LOG_PATH') ?: '/var/www/portfolio-platform/logs',
        'cache' => getenv('CACHE_PATH') ?: '/var/www/portfolio-platform/cache'
    ],
    
    'security' => [
        // Session configuration
        'session_lifetime' => 86400, // 24 hours in seconds
        'session_name' => 'PORTFOLIO_SESSION',
        'session_cookie_httponly' => true,
        'session_cookie_secure' => true, // HTTPS only
        'session_cookie_samesite' => 'Strict',
        'session_use_strict_mode' => true,
        'session_use_only_cookies' => true,
        
        // CSRF protection
        'csrf_token_name' => 'csrf_token',
        'csrf_token_length' => 32,
        
        // Password hashing
        'password_cost' => 12,
        
        // Rate limiting
        'max_login_attempts' => 5,
        'login_attempt_window' => 900, // 15 minutes in seconds
        'max_upload_attempts' => 20,
        'upload_attempt_window' => 3600, // 1 hour in seconds
        
        // Security headers
        'enable_security_headers' => true,
        'hsts_max_age' => 31536000, // 1 year
        
        // Secret key for token generation
        'secret_key' => getenv('APP_SECRET_KEY') ?: '', // MUST be set in production
    ],
    
    'files' => [
        'max_file_size' => 10485760, // 10MB in bytes
        'max_profile_photo_size' => 5242880, // 5MB in bytes
        'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'allowed_document_types' => ['application/pdf'],
        'max_files_per_item' => 10,
        'enable_malware_scanning' => true,
        'storage_quota_per_user' => 104857600 // 100MB per user
    ],
    
    'email' => [
        'smtp_host' => getenv('SMTP_HOST') ?: '',
        'smtp_port' => (int)(getenv('SMTP_PORT') ?: 587),
        'smtp_auth' => true,
        'smtp_username' => getenv('SMTP_USERNAME') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'smtp_secure' => getenv('SMTP_SECURE') ?: 'tls', // 'tls' or 'ssl'
        'from_email' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@portfolio-platform.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'Portfolio Platform',
        'base_url' => getenv('APP_URL') ?: 'https://portfolio-platform.com',
        'max_retries' => 3,
        'retry_delay' => 5 // seconds
    ],
    
    'app' => [
        'name' => 'Portfolio Platform',
        'url' => getenv('APP_URL') ?: 'https://portfolio-platform.com',
        'debug' => false, // MUST be false in production
        'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
        'environment' => 'production',
        
        // HTTPS enforcement
        'force_https' => true,
        
        // Logging
        'log_level' => 'error', // 'debug', 'info', 'warning', 'error'
        'log_rotation' => true,
        'log_max_files' => 30,
        
        // Cache settings
        'cache_enabled' => true,
        'cache_ttl' => 300, // 5 minutes default
        
        // Performance
        'enable_compression' => true,
        'enable_opcache' => true
    ],
    
    'showcase' => [
        'items_per_page' => 20,
        'cache_ttl' => 300, // 5 minutes
        'max_search_results' => 100
    ],
    
    'export' => [
        'max_generation_time' => 30, // seconds
        'max_items_per_export' => 50,
        'temp_file_cleanup' => true
    ],
    
    'admin' => [
        'items_per_page' => 50,
        'log_retention_days' => 90
    ]
];
