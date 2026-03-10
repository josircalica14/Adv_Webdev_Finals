# Infrastructure Setup Guide

This guide explains the core infrastructure components of the Multi-User Portfolio Platform and how to set them up.

## Overview

The platform uses a layered architecture with the following core components:

- **Database Layer**: MySQL with PDO for secure database operations
- **Configuration Management**: Centralized configuration with environment-specific settings
- **Error Handling**: Comprehensive error handling and exception management
- **Logging**: Structured logging with automatic rotation

## Directory Structure

```
project/
├── config/                      # Configuration files (not in version control)
│   ├── app.config.php          # Main configuration (auto-generated)
│   └── app.config.example.php  # Configuration template
├── database/                    # Database migrations and utilities
│   ├── migrations/             # SQL migration files
│   └── migrate.php             # Migration runner script
├── includes/                    # Core PHP classes
│   ├── bootstrap.php           # Application initialization
│   ├── Config.php              # Configuration management
│   ├── Database.php            # Database connection and operations
│   ├── ErrorHandler.php        # Error and exception handling
│   └── Logger.php              # Logging functionality
├── logs/                        # Application logs (auto-generated)
├── uploads/                     # User uploaded files (auto-generated)
└── temp/                        # Temporary files (auto-generated)
```

## Setup Instructions

### 1. Database Setup

#### Create Database

```sql
CREATE DATABASE portfolio_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Configure Database Connection

The configuration file will be automatically created when you first include `bootstrap.php`. To customize:

1. Copy the example configuration:
   ```bash
   cp config/app.config.example.php config/app.config.php
   ```

2. Edit `config/app.config.php` and update database credentials:
   ```php
   'db' => [
       'host' => 'localhost',
       'port' => 3306,
       'name' => 'portfolio_platform',
       'username' => 'your_username',
       'password' => 'your_password',
       'charset' => 'utf8mb4'
   ]
   ```

#### Run Migrations

Execute the migration script to create all database tables:

```bash
php database/migrate.php
```

### 2. Directory Permissions

Ensure the following directories are writable by the web server:

```bash
chmod 755 config/
chmod 755 logs/
chmod 755 uploads/
chmod 755 temp/
```

### 3. PHP Configuration

Ensure the following PHP extensions are enabled:

- PDO
- pdo_mysql
- json
- mbstring

Check your `php.ini` file or use `php -m` to verify.

## Core Components

### Database Class

The `Database` class provides a singleton PDO connection with the following features:

- **Prepared Statements**: All queries use prepared statements to prevent SQL injection
- **Transaction Support**: Begin, commit, and rollback transactions
- **Migration Runner**: Execute SQL migration files
- **Error Handling**: Comprehensive error logging

#### Usage Example

```php
require_once 'includes/bootstrap.php';

// Get database instance
$db = Database::getInstance();

// Execute a query with parameters
$stmt = $db->query(
    'SELECT * FROM users WHERE email = ?',
    ['user@example.com']
);
$user = $stmt->fetch();

// Use transactions
$db->beginTransaction();
try {
    $db->query('INSERT INTO users (email, password_hash) VALUES (?, ?)', [$email, $hash]);
    $userId = $db->lastInsertId();
    $db->query('INSERT INTO portfolios (user_id) VALUES (?)', [$userId]);
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

### Config Class

The `Config` class manages application configuration with dot-notation access:

#### Usage Example

```php
// Get configuration values
$dbHost = config()->get('db.host');
$maxFileSize = config()->get('files.max_file_size');
$debugMode = config()->get('app.debug', false); // with default value

// Set configuration values (runtime only)
config()->set('app.debug', true);

// Check if key exists
if (config()->has('email.smtp_host')) {
    // ...
}
```

### Logger Class

The `Logger` class provides structured logging with automatic rotation:

#### Log Levels

- **DEBUG**: Detailed debug information
- **INFO**: Informational messages
- **WARNING**: Warning messages
- **ERROR**: Error messages
- **CRITICAL**: Critical errors

#### Usage Example

```php
// Log messages
logger()->info('User logged in', ['user_id' => 123]);
logger()->error('Database query failed', ['query' => $sql, 'error' => $e->getMessage()]);
logger()->warning('Rate limit exceeded', ['ip' => $_SERVER['REMOTE_ADDR']]);

// Get recent logs
$recentLogs = logger()->getRecentLogs(50);
```

#### Log Format

```
[2024-01-15 10:30:45] [INFO] User logged in {"user_id":123}
[2024-01-15 10:31:12] [ERROR] Database query failed {"query":"SELECT...","error":"..."}
```

#### Log Rotation

- Logs automatically rotate when they reach 10MB
- Last 10 archived logs are kept
- Older logs are automatically deleted

### ErrorHandler Class

The `ErrorHandler` class provides centralized error handling:

#### Features

- Catches PHP errors and converts them to exceptions
- Handles uncaught exceptions
- Catches fatal errors on shutdown
- Logs all errors with context
- Provides user-friendly error responses

#### Error Response Helpers

```php
// Validation error
$response = ErrorHandler::validationError([
    'email' => 'Invalid email format',
    'password' => 'Password too short'
]);

// Authentication error
$response = ErrorHandler::authenticationError('Invalid credentials');

// Not found error
$response = ErrorHandler::notFoundError('Portfolio');

// Permission error
$response = ErrorHandler::permissionError();

// System error
$response = ErrorHandler::systemError('Database connection failed');
```

#### Response Format

```php
[
    'success' => false,
    'error' => 'Error message',
    'errorCode' => 'ERROR_CODE',
    'validationErrors' => [...], // optional
    'suggestion' => 'Helpful suggestion' // optional
]
```

## Bootstrap Process

The `bootstrap.php` file initializes all core components:

1. Sets error reporting and timezone
2. Defines base path constant
3. Registers autoloader for core classes
4. Initializes Config, Logger, ErrorHandler, and Database
5. Provides helper functions (db(), config(), logger())

### Usage

Include bootstrap at the beginning of every PHP file:

```php
<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Now you can use all core components
$db = db();
$config = config();
$logger = logger();
```

## Security Considerations

### Configuration Security

- **Never commit** `config/app.config.php` to version control
- Store sensitive credentials in configuration file
- Use environment variables for production deployments
- Restrict file permissions on configuration files

### Database Security

- All queries use prepared statements
- PDO emulate prepares is disabled for true prepared statements
- Connection uses secure defaults (ERRMODE_EXCEPTION, FETCH_ASSOC)
- Passwords are never logged

### Error Handling Security

- Debug mode should be disabled in production
- Error messages don't expose system internals in production
- All errors are logged with full context
- User-facing errors are sanitized

## Troubleshooting

### Database Connection Issues

**Problem**: "Database connection failed"

**Solutions**:
- Verify MySQL is running
- Check database credentials in `config/app.config.php`
- Ensure database exists
- Verify user has proper permissions
- Check MySQL error logs

### Permission Issues

**Problem**: "Failed to create directory" or "Permission denied"

**Solutions**:
- Ensure web server user has write permissions
- Check directory ownership: `chown -R www-data:www-data logs/ uploads/ temp/`
- Verify SELinux settings if applicable

### Migration Failures

**Problem**: Migration script fails

**Solutions**:
- Check MySQL version (requires 8.0+)
- Verify user has CREATE TABLE permissions
- Check for existing tables with same names
- Review MySQL error logs for details

### Logging Issues

**Problem**: Logs not being written

**Solutions**:
- Ensure `logs/` directory exists and is writable
- Check disk space
- Verify file permissions
- Check PHP error logs for permission errors

## Production Deployment

### Configuration Changes

1. Set `app.debug` to `false`
2. Use strong database credentials
3. Configure proper SMTP settings for email
4. Set appropriate `app.url`
5. Configure timezone to match server location

### Security Hardening

1. Restrict file permissions:
   ```bash
   chmod 600 config/app.config.php
   chmod 755 logs/
   chmod 755 uploads/
   ```

2. Disable directory listing in web server configuration

3. Use HTTPS for all connections

4. Configure proper CORS headers

5. Set up regular log rotation and monitoring

### Performance Optimization

1. Enable PHP OPcache
2. Use persistent database connections if needed
3. Configure proper MySQL indexes (already in migrations)
4. Set up database connection pooling
5. Monitor log file sizes and rotation

## Next Steps

After setting up the infrastructure:

1. Run database migrations: `php database/migrate.php`
2. Verify all tables were created successfully
3. Test database connection by including bootstrap in a test script
4. Review logs to ensure no errors during initialization
5. Proceed with implementing authentication system (Task 2)

## Support

For issues or questions:
- Check the troubleshooting section above
- Review application logs in `logs/app.log`
- Check MySQL error logs
- Verify PHP error logs
