# Quick Start Guide

## File Structure Overview

The project has been reorganized for better maintainability:

### Main Entry Points
- `index.php` - Homepage (stays in root)
- `pages/auth/login.php` - Login page
- `pages/auth/register.php` - Registration page
- `pages/dashboard/dashboard.php` - User dashboard
- `pages/portfolio/view.php` - Public portfolio view

### Key Directories
- `pages/` - All user-facing PHP pages (organized by function)
- `includes/` - PHP classes and backend logic
- `css/` - Stylesheets
- `js/` - JavaScript files
- `config/` - Configuration files
- `database/` - Migrations and seeds
- `docs/` - Documentation

### Quick Links

**For Users:**
- Homepage: `/index.php`
- Login: `/pages/auth/login.php`
- Register: `/pages/auth/register.php`
- Browse Portfolios: `/pages/showcase.php`

**For Developers:**
- File Organization: `/docs/FILE-ORGANIZATION.md`
- Database Setup: `/database/README.md`
- Deployment Guide: `/DEPLOYMENT.md`

### Development Workflow

1. **Frontend Changes**: Edit files in `css/` and `js/`
2. **Page Updates**: Edit files in `pages/`
3. **Backend Logic**: Edit classes in `includes/`
4. **Database Changes**: Add migrations to `database/migrations/`

### Common Tasks

**Add a new page:**
1. Create PHP file in appropriate `pages/` subdirectory
2. Include navigation: `include __DIR__ . '/../../includes/nav.php';`
3. Include header: `include __DIR__ . '/../../includes/header.php';`

**Update styling:**
1. Edit relevant CSS file in `css/` directory
2. Clear browser cache to see changes

**Add database table:**
1. Create migration in `database/migrations/`
2. Run: `php database/migrate.php`

## Need Help?

- Check `/docs/FILE-ORGANIZATION.md` for complete file structure
- See `/TROUBLESHOOTING.md` for common issues
- Review `/README.md` for project overview
