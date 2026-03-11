<?php
/**
 * Automatic Setup Script
 * 
 * This script automatically configures the application for any device
 * by detecting the project directory and setting up paths accordingly.
 */

echo "Portfolio Platform - Automatic Setup\n";
echo "=====================================\n\n";

// Define paths
$configFile = __DIR__ . '/config/app.config.php';
$exampleConfigFile = __DIR__ . '/config/app.config.example.php';

// Check if config already exists
if (file_exists($configFile)) {
    echo "✓ Configuration file already exists\n";
    
    // Update paths to use relative paths
    $config = require $configFile;
    
    // Update paths to be relative
    $config['paths'] = [
        'uploads' => __DIR__ . '/uploads',
        'thumbnails' => __DIR__ . '/uploads/thumbnails',
        'temp' => __DIR__ . '/temp',
        'logs' => __DIR__ . '/logs'
    ];
    
    // Save updated config
    $configContent = "<?php\n\n// Application Configuration\n// DO NOT commit this file to version control\n\nreturn " . var_export($config, true) . ";\n";
    file_put_contents($configFile, $configContent);
    
    echo "✓ Paths updated to use relative directories\n";
} else {
    echo "Creating new configuration file...\n";
    
    // Copy from example
    if (!file_exists($exampleConfigFile)) {
        die("✗ Error: Example configuration file not found!\n");
    }
    
    copy($exampleConfigFile, $configFile);
    echo "✓ Configuration file created from example\n";
}

// Create required directories
$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/thumbnails',
    __DIR__ . '/uploads/profile_photos',
    __DIR__ . '/temp',
    __DIR__ . '/temp/exports',
    __DIR__ . '/logs',
    __DIR__ . '/cache'
];

echo "\nCreating required directories...\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created: " . basename($dir) . "\n";
    } else {
        echo "✓ Exists: " . basename($dir) . "\n";
    }
}

// Set permissions (Unix/Linux/Mac only)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "\nSetting directory permissions...\n";
    foreach ($directories as $dir) {
        chmod($dir, 0755);
    }
    echo "✓ Permissions set\n";
}

echo "\n=====================================\n";
echo "Setup Complete!\n\n";
echo "Next steps:\n";
echo "1. Create database: CREATE DATABASE portfolio_platform;\n";
echo "2. Import database: mysql -u root portfolio_platform < database/portfolio_platform_export.sql\n";
echo "3. Update database credentials in config/app.config.php if needed\n";
echo "4. Visit http://localhost/your-project-folder in your browser\n\n";
echo "Default login:\n";
echo "Username: testuser1\n";
echo "Password: password123\n\n";
