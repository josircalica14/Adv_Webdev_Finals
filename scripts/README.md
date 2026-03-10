# Deployment Scripts

This directory contains scripts to help with deployment and maintenance of the Portfolio Platform.

## Available Scripts

### setup-directories.sh

Sets up the required directory structure with proper permissions for production deployment.

**Usage:**
```bash
sudo bash scripts/setup-directories.sh
```

**What it does:**
- Creates upload directories (uploads, thumbnails, profile-photos, portfolio-items)
- Creates temp, logs, cache, and config directories
- Sets proper ownership (www-data:www-data by default)
- Sets appropriate permissions (755 for writable dirs, 750 for config)
- Creates .htaccess files to protect sensitive directories
- Prevents PHP execution in uploads directory

**Configuration:**
Edit the script to change:
- `BASE_DIR` - Base installation directory (default: /var/www/portfolio-platform)
- `WEB_USER` - Web server user (default: www-data)
- `WEB_GROUP` - Web server group (default: www-data)

**Common web server users:**
- Ubuntu/Debian: `www-data:www-data`
- CentOS/RHEL: `apache:apache`
- Nginx: `nginx:nginx`

### Future Scripts

Additional scripts that may be added:

- `backup-database.sh` - Automated database backup
- `backup-files.sh` - Automated file backup
- `deploy.sh` - Automated deployment script
- `health-check.sh` - System health verification
- `cleanup-temp.sh` - Clean up temporary files
- `rotate-logs.sh` - Manual log rotation

## Notes

- All scripts should be run with appropriate permissions (usually sudo)
- Review scripts before running in production
- Test scripts in staging environment first
- Keep scripts under version control
- Document any customizations made for your environment

## Permissions

Make scripts executable:
```bash
chmod +x scripts/*.sh
```

## Security

- Scripts should not contain sensitive credentials
- Use environment variables or config files for credentials
- Restrict script permissions (chmod 750)
- Log script execution for audit trail
