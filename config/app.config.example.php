<?php

// Application Configuration Template
// Copy this file to app.config.php and update with your actual values
// DO NOT commit app.config.php to version control

return [
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
        'smtp_auth' => false,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_secure' => 'tls',
        'from_email' => 'noreply@portfolio-platform.local',
        'from_name' => 'Portfolio Platform',
        'base_url' => 'http://localhost',
        'secret_key' => 'change-this-to-a-random-secret-key'
    ],
    'app' => [
        'name' => 'Portfolio Platform',
        'url' => 'http://localhost',
        'debug' => true,
        'timezone' => 'UTC'
    ]
];
