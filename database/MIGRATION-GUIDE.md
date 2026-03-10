# Data Migration Guide

## Overview

This guide explains how to migrate your existing single-user portfolio data to the new multi-user platform schema.

## Prerequisites

Before running the migration:

1. **Backup your data**: Create backups of your database and data files
2. **Run database migrations**: Execute `php database/migrate.php` to create all required tables
3. **Verify data files exist**:
   - `data/projects-data.js`
   - `data/skills-data.js`
4. **Ensure file assets exist**: All referenced images (thumbnails, screenshots) should be in place

## Migration Process

### Step 1: Verify Prerequisites

The migration script will automatically check:
- Data files exist and are readable
- Database tables are created
- No existing admin user (prevents duplicate migration)

### Step 2: Run Migration

Execute the migration script from the command line:

```bash
php database/migrate_data.php
```

### Step 3: Review Migration Report

The script will output a detailed report showing:
- Admin account creation status
- Number of projects migrated
- Number of skills migrated
- Number of files migrated
- Any warnings or errors encountered

## What Gets Migrated

### 1. Admin Account
- **Email**: `admin@portfolio.local`
- **Password**: `admin123` (change immediately after first login)
- **Program**: BSIT
- **Username**: `admin`
- **Admin privileges**: Enabled

### 2. Projects
From `data/projects-data.js`:
- Title
- Detailed description (including features list)
- Technologies (stored as tags)
- Category (stored as tags)
- Live URL and GitHub URL (stored as links)
- Thumbnail and screenshots (file references)

### 3. Skills
From `data/skills-data.js`:
- Skill name
- Proficiency level
- Years of experience
- Category
- Icon reference

### 4. File References
- Thumbnails from projects
- Screenshots from projects
- File metadata (size, type, path)

### 5. Customization Settings
Default settings matching current portfolio styling:
- **Theme**: default
- **Layout**: grid
- **Primary color**: #2d7a4f (existing accent color)
- **Accent color**: #d6a5ad (existing logo background)
- **Fonts**: Arial (matching current font family)

## Migration Report

The migration generates a comprehensive report including:

```
=== Migration Report ===
Status: SUCCESS
User ID: 1
Portfolio ID: 1
Projects migrated: 9
Skills migrated: 25
Files migrated: 18

=== Summary ===
Total items migrated: 34
Total errors: 0

✓ Migration completed successfully!

Default admin credentials:
  Email: admin@portfolio.local
  Password: admin123
  (Please change these credentials after first login)
```

## Post-Migration Steps

### 1. Change Admin Credentials
Immediately log in and change the default password:
1. Navigate to `/login.php`
2. Log in with `admin@portfolio.local` / `admin123`
3. Go to profile settings
4. Change password to a secure one

### 2. Verify Data
- Check that all projects appear in the dashboard
- Verify skills are properly categorized
- Ensure file references are working
- Test customization settings

### 3. Update Profile
- Add a profile photo
- Update bio and contact information
- Customize portfolio appearance if desired

## Troubleshooting

### Migration Already Run
**Error**: "Admin user already exists"

**Solution**: The migration has already been completed. If you need to re-run it:
1. Backup your current database
2. Drop and recreate the database
3. Run `php database/migrate.php` to recreate tables
4. Run `php database/migrate_data.php` again

### Data Files Not Found
**Error**: "Projects data file not found" or "Skills data file not found"

**Solution**: Ensure the data files exist at:
- `data/projects-data.js`
- `data/skills-data.js`

### Database Tables Missing
**Error**: "Required table 'X' does not exist"

**Solution**: Run the database schema migration first:
```bash
php database/migrate.php
```

### File References Not Working
**Warning**: "File not found: X"

**Solution**: 
- Check that image files exist in the correct location
- Update file paths in the data files if needed
- Files will still be migrated to the database, but won't display until files are in place

### JSON Parse Errors
**Error**: "Could not parse projects data" or "Invalid JSON"

**Solution**:
- Check that the JavaScript data files have valid JSON syntax
- Ensure no trailing commas in arrays/objects
- Verify all strings are properly quoted

## Data Structure Mapping

### Projects Mapping
```
projects-data.js          →  portfolio_items table
├── id                    →  (not used, new ID generated)
├── title                 →  title
├── description           →  description (part)
├── detailedDescription   →  description (main)
├── features[]            →  description (appended)
├── technologies[]        →  tags.technologies
├── category              →  tags.category
├── liveUrl               →  links.live
├── githubUrl             →  links.github
├── thumbnail             →  files table
└── screenshots[]         →  files table
```

### Skills Mapping
```
skills-data.js            →  portfolio_items table
├── name                  →  title
├── level                 →  tags.level + description
├── yearsExperience       →  description
├── icon                  →  tags.icon
└── category              →  tags.category
```

## Rollback

If you need to rollback the migration:

1. **Restore database backup**:
   ```bash
   mysql -u username -p database_name < backup.sql
   ```

2. **Or manually clean up**:
   ```sql
   DELETE FROM users WHERE email = 'admin@portfolio.local';
   -- This will cascade delete portfolios, items, files, and settings
   ```

## Support

If you encounter issues not covered in this guide:
1. Check the migration report for specific error messages
2. Review the database logs
3. Verify all prerequisites are met
4. Ensure proper file permissions on data directories

## Security Notes

- The default admin password (`admin123`) is intentionally weak
- **You MUST change it immediately after first login**
- Consider using a strong password manager
- Enable two-factor authentication if available
- Review and update admin privileges as needed
