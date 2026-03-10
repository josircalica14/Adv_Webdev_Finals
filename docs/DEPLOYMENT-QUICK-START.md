# Deployment Quick Start Guide

This is a condensed version of the full deployment guide. For detailed instructions, see [DEPLOYMENT.md](DEPLOYMENT.md).

## Prerequisites

- PHP 7.4+ with required extensions
- MySQL 8.0+
- Apache 2.4+ or Nginx 1.18+
- SSL certificate
- SMTP credentials

## Quick Setup (5 Steps)

### 1. Upload Files

```bash
# Upload application files to server
cd /var/www/portfolio-platform
```

### 2. Configure Environment

```bash
# Copy and edit environment file
cp .env.example .env
nano .env

# Copy and edit config
cp config/app.config.production.php config/app.config.php
nano config/app.config.php

# Secure files
chmod 600 .env config/app.config.php
```

### 3. Set Up Directories

```bash
# Run setup script
sudo bash scripts/setup-directories.sh

# Or manually:
sudo mkdir -p uploads/{thumbnails,profile-photos,portfolio-items} temp logs cache
sudo chown -R www-data:www-data uploads temp logs cache
sudo chmod 755 uploads temp logs cache
```

### 4. Database Setup

```bash
# Create database and user
mysql -u root -p << EOF
CREATE DATABASE portfolio_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your-secure-password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER ON portfolio_platform.* TO 'portfolio_user'@'localhost';
FLUSH PRIVILEGES;
EOF

# Run migrations
php database/migrate.php

# Optimize database
php database/optimize_indexes.php
```

### 5. Configure Web Server

**Apache:**
```bash
sudo a2enmod ssl rewrite headers
sudo systemctl restart apache2
```

**Nginx:**
```bash
sudo nginx -t
sudo systemctl restart nginx
```

## Verification Checklist

- [ ] Application loads via HTTPS
- [ ] HTTP redirects to HTTPS
- [ ] Can register new account
- [ ] Can log in
- [ ] Can upload files
- [ ] Email sending works
- [ ] Database queries work

## Security Checklist

- [ ] `.env` file has 600 permissions
- [ ] `config/app.config.php` has 600 permissions
- [ ] `debug` is set to `false` in config
- [ ] `force_https` is set to `true` in config
- [ ] Strong `APP_SECRET_KEY` generated
- [ ] Database user has limited permissions
- [ ] Firewall configured (ports 80, 443)
- [ ] SSL certificate valid
- [ ] Backups configured

## Common Commands

```bash
# View error logs
tail -f logs/error.log

# View security logs
tail -f logs/security.log

# Check disk space
df -h

# Check database size
mysql -u portfolio_user -p -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'portfolio_platform' GROUP BY table_schema;"

# Backup database
mysqldump -u portfolio_user -p portfolio_platform | gzip > backup-$(date +%Y%m%d).sql.gz

# Backup files
tar -czf uploads-backup-$(date +%Y%m%d).tar.gz uploads/
```

## Troubleshooting

**Database connection error:**
- Check credentials in `config/app.config.php`
- Verify MySQL is running: `sudo systemctl status mysql`

**File upload fails:**
- Check directory permissions: `ls -la uploads/`
- Check disk space: `df -h`

**Email not sending:**
- Verify SMTP credentials in config
- Check email logs: `tail -f logs/email.log`

**Session issues:**
- Check session configuration in `config/security.php`
- Verify session directory permissions

## Next Steps

1. Review [DEPLOYMENT.md](DEPLOYMENT.md) for detailed instructions
2. Review [SECURITY-BEST-PRACTICES.md](SECURITY-BEST-PRACTICES.md)
3. Set up monitoring and alerts
4. Configure automated backups
5. Test backup restoration
6. Perform security audit

## Support

For detailed documentation:
- Full deployment guide: [DEPLOYMENT.md](DEPLOYMENT.md)
- Security practices: [SECURITY-BEST-PRACTICES.md](SECURITY-BEST-PRACTICES.md)
- Database migration: [database/MIGRATION-GUIDE.md](database/MIGRATION-GUIDE.md)
- Infrastructure setup: [INFRASTRUCTURE-SETUP.md](INFRASTRUCTURE-SETUP.md)
