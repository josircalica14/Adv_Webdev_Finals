# Database Setup

This directory contains database migration files and utilities for the Multi-User Portfolio Platform.

## Directory Structure

```
database/
├── migrations/          # SQL migration files
│   ├── 001_create_users_table.sql
│   ├── 002_create_portfolios_table.sql
│   ├── 003_create_portfolio_items_table.sql
│   ├── 004_create_files_table.sql
│   ├── 005_create_customization_settings_table.sql
│   ├── 006_create_sessions_table.sql
│   ├── 007_create_email_verifications_table.sql
│   ├── 008_create_password_resets_table.sql
│   ├── 009_create_rate_limits_table.sql
│   ├── 010_create_admin_actions_table.sql
│   └── 011_create_flagged_content_table.sql
├── migrate.php          # Schema migration runner script
├── migrate_data.php     # Data migration script (single-user to multi-user)
├── optimize_indexes.php # Database optimization script
├── optimize_indexes.sql # SQL optimization queries
├── MIGRATION-GUIDE.md   # Comprehensive data migration guide
└── README.md           # This file
```

## Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- PDO MySQL extension enabled

## Setup Instructions

### 1. Create Database

First, create the database in MySQL:

```sql
CREATE DATABASE portfolio_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configure Database Connection

The configuration file will be automatically created at `config/app.config.php` when you first run the application. Update the database credentials:

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

### 3. Run Migrations

Execute the migration script from the command line:

```bash
php database/migrate.php
```

This will create all required tables in the database.

### 4. Migrate Existing Data (Optional)

If you're upgrading from a single-user portfolio to the multi-user platform, run the data migration:

```bash
php database/migrate_data.php
```

This will:
- Create a default admin account
- Migrate projects from `data/projects-data.js`
- Migrate skills from `data/skills-data.js`
- Preserve file references
- Create default customization settings

**See `MIGRATION-GUIDE.md` for detailed instructions.**

### 5. Test Migration (Optional)

Before running the data migration, you can test it:

```bash
php test_migration.php
```

This validates data files, database connection, and table structure.

## Migration Files

Each migration file creates a specific table:

1. **001_create_users_table.sql** - User accounts with authentication and profile information
2. **002_create_portfolios_table.sql** - Portfolio metadata for each user
3. **003_create_portfolio_items_table.sql** - Individual portfolio items (projects, achievements, etc.)
4. **004_create_files_table.sql** - File metadata and paths for attachments
5. **005_create_customization_settings_table.sql** - Visual customization preferences
6. **006_create_sessions_table.sql** - Active user sessions for authentication
7. **007_create_email_verifications_table.sql** - Email verification tokens
8. **008_create_password_resets_table.sql** - Password reset tokens
9. **009_create_rate_limits_table.sql** - Rate limiting data for security
10. **010_create_admin_actions_table.sql** - Audit log of admin actions
11. **011_create_flagged_content_table.sql** - Flagged content for moderation

## Database Schema

### Entity Relationships

```
users (1) ──── (1) portfolios
portfolios (1) ──── (many) portfolio_items
portfolio_items (1) ──── (many) files
portfolios (1) ──── (1) customization_settings
users (1) ──── (many) sessions
users (1) ──── (many) email_verifications
users (1) ──── (many) password_resets
users (1) ──── (many) admin_actions
portfolio_items (1) ──── (many) flagged_content
```

### Key Features

- **Foreign Key Constraints**: All relationships use foreign keys with CASCADE DELETE for data integrity
- **Indexes**: Optimized indexes on frequently queried columns (email, username, session tokens, etc.)
- **JSON Fields**: Uses JSON columns for flexible data storage (tags, links, contact info)
- **Timestamps**: Automatic creation and update timestamps on all tables
- **Character Set**: UTF-8 (utf8mb4) for full Unicode support including emojis

## Troubleshooting

### Connection Failed

If you see "Database connection failed", check:
- MySQL service is running
- Database credentials in `config/app.config.php` are correct
- Database exists and user has proper permissions

### Migration Errors

If migrations fail:
- Check MySQL error logs for detailed error messages
- Ensure MySQL version is 8.0 or higher
- Verify user has CREATE TABLE permissions
- Check if tables already exist (migrations use IF NOT EXISTS)

### Permission Issues

Grant necessary permissions to your database user:

```sql
GRANT ALL PRIVILEGES ON portfolio_platform.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

## Re-running Migrations

The migration files use `CREATE TABLE IF NOT EXISTS`, so they can be run multiple times safely. However, if you need to reset the database:

```sql
DROP DATABASE portfolio_platform;
CREATE DATABASE portfolio_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then run migrations again.
