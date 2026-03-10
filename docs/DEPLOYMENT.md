# Deployment Guide: Multi-User Portfolio Platform

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Installation Steps](#installation-steps)
4. [Configuration](#configuration)
5. [Database Setup](#database-setup)
6. [Migration Process](#migration-process)
7. [Security Hardening](#security-hardening)
8. [Backup Procedures](#backup-procedures)
9. [Monitoring and Maintenance](#monitoring-and-maintenance)
10. [Troubleshooting](#troubleshooting)

---

## Server Requirements

### Minimum Requirements

- **PHP**: 7.4 or higher (8.0+ recommended)
- **MySQL**: 8.0 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for HTTPS
- **Disk Space**: 10GB minimum (more depending on user uploads)
- **Memory**: 512MB minimum (1GB+ recommended)

### Required PHP Extensions

```bash
php -m | grep -E 'pdo|pdo_mysql|mbstring|openssl|json|curl|gd|fileinfo|zip'
```

Required extensions:
- `pdo`
- `pdo_mysql`
- `mbstring`
- `openssl`
- `json`
- `curl`
- `gd` (for image processing)
- `fileinfo` (for file type detection)
- `zip` (for export functionality)

### PHP Configuration

Edit `php.ini` with these minimum settings:

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 60
memory_limit = 256M
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
expose_php = Off
```

---

## Pre-Deployment Checklist

- [ ] Server meets all requirements
- [ ] SSL certificate installed and configured
- [ ] Database server installed and running
- [ ] PHP and required extensions installed
- [ ] Web server configured
- [ ] Domain name configured and pointing to server
- [ ] SMTP credentials obtained for email service
- [ ] Backup strategy planned
- [ ] Monitoring tools configured

---

## Installation Steps

### 1. Clone or Upload Application Files

```bash
# Create application directory
sudo mkdir -p /var/www/portfolio-platform
cd /var/www/portfolio-platform

# Upload files via Git, FTP, or rsync
# Example with Git:
git clone https://github.com/your-org/portfolio-platform.git .

# Or upload files manually
```

### 2. Set Up Directory Structure

Run the directory setup script:

```bash
sudo bash scripts/setup-directories.sh
```

Or manually create directories:

```bash
sudo mkdir -p uploads/{thumbnails,profile-photos,portfolio-items}
sudo mkdir -p temp logs cache config
sudo chown -R www-data:www-data uploads temp logs cache
sudo chmod 755 uploads temp logs cache
sudo chmod 750 config
```

### 3. Install Dependencies

If using Composer for dependencies:

```bash
composer install --no-dev --optimize-autoloader
```

### 4. Configure Web Server

#### Apache Configuration

Create virtual host file `/etc/apache2/sites-available/portfolio-platform.conf`:

```apache
<VirtualHost *:80>
    ServerName portfolio-platform.com
    ServerAlias www.portfolio-platform.com
    
    # Redirect all HTTP to HTTPS
    Redirect permanent / https://portfolio-platform.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName portfolio-platform.com
    ServerAlias www.portfolio-platform.com
    
    DocumentRoot /var/www/portfolio-platform
    
    <Directory /var/www/portfolio-platform>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/portfolio-platform.crt
    SSLCertificateKeyFile /etc/ssl/private/portfolio-platform.key
    SSLCertificateChainFile /etc/ssl/certs/portfolio-platform-chain.crt
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
    Header always set X-Frame-Options "DENY"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/portfolio-platform-error.log
    CustomLog ${APACHE_LOG_DIR}/portfolio-platform-access.log combined
</VirtualHost>
```

Enable site and modules:

```bash
sudo a2ensite portfolio-platform
sudo a2enmod ssl rewrite headers
sudo systemctl restart apache2
```

#### Nginx Configuration

Create configuration file `/etc/nginx/sites-available/portfolio-platform`:

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name portfolio-platform.com www.portfolio-platform.com;
    return 301 https://$server_name$request_uri;
}

# HTTPS Server
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name portfolio-platform.com www.portfolio-platform.com;
    
    root /var/www/portfolio-platform;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/ssl/certs/portfolio-platform.crt;
    ssl_certificate_key /etc/ssl/private/portfolio-platform.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # File upload size
    client_max_body_size 12M;
    
    # PHP handling
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|logs|temp|cache)/ {
        deny all;
    }
    
    # Prevent PHP execution in uploads
    location ~* ^/uploads/.*\.php$ {
        deny all;
    }
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Logging
    access_log /var/log/nginx/portfolio-platform-access.log;
    error_log /var/log/nginx/portfolio-platform-error.log;
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/portfolio-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Configuration

### 1. Environment Variables

Copy and configure environment file:

```bash
cp .env.example .env
nano .env
```

Update all values in `.env`:

```env
APP_NAME="Portfolio Platform"
APP_URL=https://portfolio-platform.com
APP_TIMEZONE=UTC
APP_SECRET_KEY=your-random-32-character-secret-key

DB_HOST=localhost
DB_PORT=3306
DB_NAME=portfolio_platform
DB_USERNAME=portfolio_user
DB_PASSWORD=your-secure-database-password

SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=noreply@portfolio-platform.com
SMTP_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@portfolio-platform.com
MAIL_FROM_NAME="Portfolio Platform"
```

Secure the file:

```bash
sudo chmod 600 .env
sudo chown www-data:www-data .env
```

### 2. Application Configuration

Copy production configuration:

```bash
cp config/app.config.production.php config/app.config.php
nano config/app.config.php
```

Verify all settings, especially:
- Database credentials
- SMTP settings
- File paths
- Secret keys

Secure the file:

```bash
sudo chmod 600 config/app.config.php
sudo chown www-data:www-data config/app.config.php
```

### 3. Generate Secret Key

Generate a secure random key:

```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

Update `APP_SECRET_KEY` in `.env` with the generated key.

---

## Database Setup

### 1. Create Database and User

```bash
mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE portfolio_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your-secure-password';

-- Grant privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER 
ON portfolio_platform.* TO 'portfolio_user'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW GRANTS FOR 'portfolio_user'@'localhost';

EXIT;
```

### 2. Run Migrations

```bash
cd /var/www/portfolio-platform
php database/migrate.php
```

Verify migrations:

```bash
mysql -u portfolio_user -p portfolio_platform -e "SHOW TABLES;"
```

### 3. Optimize Database

```bash
php database/optimize_indexes.php
```

Or run SQL directly:

```bash
mysql -u portfolio_user -p portfolio_platform < database/optimize_indexes.sql
```

---

## Migration Process

### Migrating from Single-User to Multi-User

If migrating from an existing single-user portfolio:

1. **Backup existing data**:

```bash
# Backup files
tar -czf portfolio-backup-$(date +%Y%m%d).tar.gz \
    data/ uploads/ css/ js/ *.php

# Backup database (if applicable)
mysqldump -u root -p old_database > old-portfolio-backup.sql
```

2. **Run data migration**:

```bash
php database/migrate_data.php
```

3. **Verify migration**:

```bash
php test_migration.php
```

4. **Review migration report**:

Check `logs/migration-report-*.log` for details.

See `database/MIGRATION-GUIDE.md` for detailed migration instructions.

---

## Security Hardening

### 1. File Permissions

```bash
# Application files - read-only for web server
sudo find /var/www/portfolio-platform -type f -exec chmod 644 {} \;
sudo find /var/www/portfolio-platform -type d -exec chmod 755 {} \;

# Writable directories
sudo chmod 755 uploads temp logs cache
sudo chown -R www-data:www-data uploads temp logs cache

# Secure configuration
sudo chmod 600 config/app.config.php .env
sudo chmod 750 config
```

### 2. Disable Directory Listing

Ensure `.htaccess` or Nginx config prevents directory listing.

### 3. Prevent PHP Execution in Uploads

Already configured in web server setup above.

### 4. Enable Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# Firewalld (CentOS)
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 5. Configure Fail2Ban

Install and configure Fail2Ban to prevent brute force attacks:

```bash
sudo apt install fail2ban
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

Add custom filter for portfolio platform if needed.

### 6. Regular Security Updates

```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade

# CentOS/RHEL
sudo yum update
```

### 7. Database Security

```sql
-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root login
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Remove test database
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';

-- Reload privileges
FLUSH PRIVILEGES;
```

---

## Backup Procedures

### 1. Automated Database Backups

Create backup script `/usr/local/bin/backup-portfolio-db.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/portfolio-platform"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="portfolio_platform"
DB_USER="portfolio_user"
DB_PASS="your-password"

mkdir -p "$BACKUP_DIR"

# Backup database
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db-$DATE.sql.gz"

# Keep only last 30 days
find "$BACKUP_DIR" -name "db-*.sql.gz" -mtime +30 -delete

echo "Database backup completed: db-$DATE.sql.gz"
```

Make executable and schedule:

```bash
sudo chmod +x /usr/local/bin/backup-portfolio-db.sh
sudo crontab -e
```

Add cron job (daily at 2 AM):

```cron
0 2 * * * /usr/local/bin/backup-portfolio-db.sh >> /var/log/portfolio-backup.log 2>&1
```

### 2. File Backups

Create file backup script `/usr/local/bin/backup-portfolio-files.sh`:

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/portfolio-platform"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/portfolio-platform"

mkdir -p "$BACKUP_DIR"

# Backup uploads directory
tar -czf "$BACKUP_DIR/uploads-$DATE.tar.gz" -C "$APP_DIR" uploads/

# Keep only last 7 days (files are large)
find "$BACKUP_DIR" -name "uploads-*.tar.gz" -mtime +7 -delete

echo "File backup completed: uploads-$DATE.tar.gz"
```

Schedule weekly (Sunday at 3 AM):

```cron
0 3 * * 0 /usr/local/bin/backup-portfolio-files.sh >> /var/log/portfolio-backup.log 2>&1
```

### 3. Backup Verification

Regularly test backup restoration:

```bash
# Test database restore
gunzip < /var/backups/portfolio-platform/db-latest.sql.gz | mysql -u root -p test_restore_db

# Test file restore
tar -tzf /var/backups/portfolio-platform/uploads-latest.tar.gz | head
```

### 4. Off-Site Backups

Consider using:
- AWS S3
- Google Cloud Storage
- Rsync to remote server
- Backup service (e.g., Backblaze B2)

Example rsync to remote server:

```bash
rsync -avz /var/backups/portfolio-platform/ user@backup-server:/backups/portfolio/
```

---

## Monitoring and Maintenance

### 1. Log Monitoring

Monitor application logs:

```bash
# Error logs
tail -f /var/www/portfolio-platform/logs/error.log

# Security logs
tail -f /var/www/portfolio-platform/logs/security.log

# Web server logs
tail -f /var/log/apache2/portfolio-platform-error.log
# or
tail -f /var/log/nginx/portfolio-platform-error.log
```

### 2. Disk Space Monitoring

```bash
# Check disk usage
df -h

# Check upload directory size
du -sh /var/www/portfolio-platform/uploads
```

Set up alerts when disk usage exceeds 80%.

### 3. Database Monitoring

```bash
# Check database size
mysql -u portfolio_user -p -e "
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'portfolio_platform'
GROUP BY table_schema;
"
```

### 4. Performance Monitoring

Monitor:
- Page load times
- Database query performance
- Server resource usage (CPU, memory, disk I/O)
- SSL certificate expiration

Tools:
- New Relic
- Datadog
- Prometheus + Grafana
- Built-in server monitoring

### 5. Regular Maintenance Tasks

**Daily**:
- Review error logs
- Check backup completion

**Weekly**:
- Review security logs
- Check disk space
- Review user activity

**Monthly**:
- Update dependencies
- Review and optimize database
- Test backup restoration
- Security audit

**Quarterly**:
- Review and update SSL certificates
- Performance optimization review
- Security penetration testing

### 6. Log Rotation

Configure logrotate `/etc/logrotate.d/portfolio-platform`:

```
/var/www/portfolio-platform/logs/*.log {
    daily
    rotate 30
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        # Reload application if needed
    endscript
}
```

---

## Troubleshooting

### Common Issues

#### 1. Database Connection Errors

**Symptom**: "Could not connect to database"

**Solutions**:
- Verify database credentials in `config/app.config.php`
- Check MySQL service: `sudo systemctl status mysql`
- Verify user permissions: `SHOW GRANTS FOR 'portfolio_user'@'localhost';`
- Check firewall rules if database is remote

#### 2. File Upload Failures

**Symptom**: "Failed to upload file"

**Solutions**:
- Check directory permissions: `ls -la uploads/`
- Verify PHP upload settings: `php -i | grep upload`
- Check disk space: `df -h`
- Review error logs for specific errors

#### 3. Email Not Sending

**Symptom**: Verification emails not received

**Solutions**:
- Verify SMTP credentials in config
- Test SMTP connection: `telnet smtp.example.com 587`
- Check email logs: `tail -f logs/email.log`
- Verify firewall allows outbound SMTP

#### 4. Session Issues

**Symptom**: Users logged out unexpectedly

**Solutions**:
- Check session directory permissions
- Verify session configuration in `php.ini`
- Check server time/timezone settings
- Review session cleanup cron jobs

#### 5. HTTPS Redirect Loop

**Symptom**: Page keeps redirecting

**Solutions**:
- Check web server SSL configuration
- Verify `force_https` setting in config
- Check for conflicting redirect rules
- Review proxy/load balancer configuration

#### 6. Performance Issues

**Symptom**: Slow page loads

**Solutions**:
- Enable caching in config
- Optimize database queries
- Check server resources: `top`, `htop`
- Review slow query log
- Enable OPcache

### Getting Help

- Review logs in `/var/www/portfolio-platform/logs/`
- Check web server error logs
- Review database error logs
- Consult documentation in repository
- Contact support team

---

## Post-Deployment Checklist

- [ ] Application accessible via HTTPS
- [ ] HTTP redirects to HTTPS
- [ ] Database migrations completed
- [ ] Admin account created
- [ ] Email sending working
- [ ] File uploads working
- [ ] Backups configured and tested
- [ ] Monitoring configured
- [ ] Security headers verified
- [ ] SSL certificate valid
- [ ] Firewall configured
- [ ] Log rotation configured
- [ ] Documentation updated with server-specific details

---

## Additional Resources

- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [MySQL Security Guide](https://dev.mysql.com/doc/refman/8.0/en/security.html)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Let's Encrypt SSL](https://letsencrypt.org/)

---

**Last Updated**: 2024
**Version**: 1.0
