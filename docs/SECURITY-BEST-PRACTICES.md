# Security Best Practices

## Overview

This document outlines security best practices for deploying and maintaining the Multi-User Portfolio Platform. Following these guidelines will help protect user data, prevent unauthorized access, and maintain system integrity.

---

## Authentication and Authorization

### Password Security

1. **Strong Password Requirements**
   - Minimum 8 characters
   - Must contain uppercase, lowercase, and numbers
   - Consider adding special character requirement
   - Implement password strength meter on registration

2. **Password Storage**
   - Always use bcrypt with cost factor 12 or higher
   - Never store plaintext passwords
   - Never log passwords (even hashed)
   - Implement secure password reset flow

3. **Account Lockout**
   - Implement rate limiting (5 attempts per 15 minutes)
   - Consider temporary account lockout after repeated failures
   - Log all failed authentication attempts
   - Alert admins of suspicious activity

### Session Management

1. **Session Configuration**
   ```php
   // Secure session settings
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_samesite', 'Strict');
   ini_set('session.use_strict_mode', 1);
   ini_set('session.use_only_cookies', 1);
   ```

2. **Session Lifecycle**
   - Regenerate session ID on login
   - Regenerate session ID periodically (every 5 minutes)
   - Expire sessions after 24 hours of inactivity
   - Clear all session data on logout
   - Implement "remember me" securely if needed

3. **Session Storage**
   - Store sessions server-side only
   - Use database or Redis for session storage in production
   - Never store sensitive data in cookies
   - Encrypt session data if storing sensitive information

---

## Input Validation and Sanitization

### General Principles

1. **Validate All Input**
   - Never trust user input
   - Validate on both client and server side
   - Use whitelist validation when possible
   - Reject invalid input, don't try to fix it

2. **Sanitization**
   ```php
   // Use prepared statements for SQL
   $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   
   // Escape output for HTML
   echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
   
   // Sanitize file names
   $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
   ```

3. **Type Checking**
   - Enforce strict type checking
   - Validate data types (string, int, email, URL)
   - Check string lengths
   - Validate numeric ranges

### SQL Injection Prevention

1. **Use Prepared Statements**
   ```php
   // GOOD
   $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
   $stmt->execute([$userId]);
   
   // BAD - Never do this
   $query = "SELECT * FROM users WHERE id = " . $_GET['id'];
   ```

2. **Additional Measures**
   - Use PDO with emulate prepares disabled
   - Limit database user permissions
   - Use different database users for different operations
   - Never display database errors to users

### XSS Prevention

1. **Output Encoding**
   ```php
   // HTML context
   echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
   
   // JavaScript context
   echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP);
   
   // URL context
   echo urlencode($data);
   ```

2. **Content Security Policy**
   ```php
   header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com");
   ```

3. **Additional Measures**
   - Sanitize rich text input (use HTML Purifier)
   - Validate URLs before rendering
   - Use textContent instead of innerHTML in JavaScript
   - Implement X-XSS-Protection header

### CSRF Prevention

1. **Token Implementation**
   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // Validate token
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       die('CSRF token validation failed');
   }
   ```

2. **Best Practices**
   - Use unique tokens per session
   - Validate tokens on all state-changing operations
   - Set SameSite cookie attribute
   - Implement double-submit cookie pattern for APIs

---

## File Upload Security

### Validation

1. **File Type Validation**
   ```php
   // Check MIME type
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mimeType = finfo_file($finfo, $filePath);
   
   // Validate against whitelist
   $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
   if (!in_array($mimeType, $allowedTypes)) {
       throw new Exception('Invalid file type');
   }
   ```

2. **File Size Limits**
   - Enforce maximum file size (10MB for documents, 5MB for photos)
   - Check size before processing
   - Configure PHP upload limits appropriately

3. **File Name Sanitization**
   ```php
   // Generate unique, safe file name
   $extension = pathinfo($originalName, PATHINFO_EXTENSION);
   $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
   ```

### Storage

1. **Directory Structure**
   - Store uploads outside web root if possible
   - Use organized directory structure (by user, date, type)
   - Prevent directory traversal attacks

2. **Permissions**
   ```bash
   # Uploads directory
   chmod 755 uploads/
   chown www-data:www-data uploads/
   
   # Prevent PHP execution
   # .htaccess in uploads directory
   <FilesMatch "\.php$">
       Order Deny,Allow
       Deny from all
   </FilesMatch>
   ```

3. **Malware Scanning**
   - Implement ClamAV or similar scanner
   - Scan files before storing
   - Quarantine suspicious files
   - Log all scan results

---

## HTTPS and Transport Security

### SSL/TLS Configuration

1. **Certificate Management**
   - Use valid SSL certificate (Let's Encrypt recommended)
   - Enable automatic renewal
   - Use strong cipher suites
   - Disable SSLv3, TLS 1.0, TLS 1.1

2. **HTTPS Enforcement**
   ```php
   // Redirect HTTP to HTTPS
   if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
       header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
       exit();
   }
   ```

3. **Security Headers**
   ```php
   // HSTS
   header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
   
   // Other security headers
   header('X-Frame-Options: DENY');
   header('X-Content-Type-Options: nosniff');
   header('X-XSS-Protection: 1; mode=block');
   header('Referrer-Policy: strict-origin-when-cross-origin');
   ```

---

## Database Security

### Configuration

1. **User Permissions**
   ```sql
   -- Create limited user
   CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'strong-password';
   
   -- Grant only necessary permissions
   GRANT SELECT, INSERT, UPDATE, DELETE ON portfolio_platform.* 
   TO 'portfolio_user'@'localhost';
   
   -- No DROP, CREATE, or admin privileges
   ```

2. **Connection Security**
   - Use localhost connection when possible
   - Use SSL for remote connections
   - Limit connection attempts
   - Use strong passwords

3. **Data Protection**
   - Encrypt sensitive data at rest
   - Use encrypted backups
   - Implement data retention policies
   - Regularly audit database access

### Query Security

1. **Prepared Statements**
   - Always use prepared statements
   - Never concatenate user input into queries
   - Use parameterized queries

2. **Error Handling**
   - Don't display database errors to users
   - Log errors securely
   - Use generic error messages

---

## Access Control

### Principle of Least Privilege

1. **User Permissions**
   - Users can only access their own data
   - Implement role-based access control (RBAC)
   - Validate ownership on every operation
   - Log access attempts

2. **File System Permissions**
   ```bash
   # Application files - read-only
   find /var/www/portfolio-platform -type f -exec chmod 644 {} \;
   find /var/www/portfolio-platform -type d -exec chmod 755 {} \;
   
   # Configuration - restricted
   chmod 600 config/app.config.php
   chmod 600 .env
   
   # Writable directories
   chmod 755 uploads/ logs/ cache/ temp/
   ```

3. **Admin Access**
   - Separate admin interface
   - Additional authentication for admin actions
   - Log all admin operations
   - Implement admin approval workflows

---

## Logging and Monitoring

### Security Logging

1. **What to Log**
   - Authentication attempts (success and failure)
   - Authorization failures
   - Input validation failures
   - File upload attempts
   - Admin actions
   - Configuration changes
   - Suspicious activity

2. **Log Format**
   ```php
   // Include relevant context
   $logEntry = [
       'timestamp' => date('Y-m-d H:i:s'),
       'event' => 'login_failure',
       'user_id' => $userId,
       'ip_address' => $_SERVER['REMOTE_ADDR'],
       'user_agent' => $_SERVER['HTTP_USER_AGENT'],
       'details' => 'Invalid password'
   ];
   ```

3. **Log Security**
   - Store logs securely
   - Restrict log access
   - Implement log rotation
   - Never log sensitive data (passwords, tokens)
   - Consider centralized logging

### Monitoring

1. **Real-Time Alerts**
   - Multiple failed login attempts
   - Unusual access patterns
   - High error rates
   - Disk space issues
   - SSL certificate expiration

2. **Regular Reviews**
   - Daily: Review error logs
   - Weekly: Review security logs
   - Monthly: Security audit
   - Quarterly: Penetration testing

---

## Rate Limiting

### Implementation

1. **Login Attempts**
   ```php
   // 5 attempts per 15 minutes per IP
   $rateLimiter->checkLimit($_SERVER['REMOTE_ADDR'], 'login', 5, 900);
   ```

2. **File Uploads**
   ```php
   // 20 uploads per hour per user
   $rateLimiter->checkLimit($userId, 'upload', 20, 3600);
   ```

3. **API Endpoints**
   - Implement rate limiting on all endpoints
   - Use different limits for different operations
   - Consider user-based and IP-based limits
   - Return appropriate HTTP status codes (429)

---

## Dependency Management

### Updates

1. **Regular Updates**
   - Update PHP regularly
   - Update dependencies (Composer packages)
   - Update web server software
   - Update database server
   - Update operating system

2. **Security Patches**
   - Subscribe to security mailing lists
   - Monitor CVE databases
   - Apply critical patches immediately
   - Test patches in staging first

3. **Dependency Scanning**
   ```bash
   # Check for known vulnerabilities
   composer audit
   ```

---

## Backup and Recovery

### Backup Strategy

1. **What to Backup**
   - Database (daily)
   - Uploaded files (weekly)
   - Configuration files (on change)
   - Application code (version control)

2. **Backup Security**
   - Encrypt backups
   - Store off-site
   - Test restoration regularly
   - Implement retention policies
   - Restrict backup access

3. **Recovery Plan**
   - Document recovery procedures
   - Test recovery process
   - Define RTO and RPO
   - Maintain backup inventory

---

## Incident Response

### Preparation

1. **Incident Response Plan**
   - Define incident types
   - Assign responsibilities
   - Document procedures
   - Maintain contact list

2. **Detection**
   - Monitor logs continuously
   - Set up alerts
   - Regular security audits
   - User reporting mechanism

3. **Response**
   - Isolate affected systems
   - Preserve evidence
   - Notify stakeholders
   - Document incident
   - Implement fixes
   - Post-incident review

---

## Compliance and Privacy

### Data Protection

1. **User Data**
   - Collect only necessary data
   - Implement data retention policies
   - Provide data export functionality
   - Allow account deletion
   - Encrypt sensitive data

2. **Privacy Policy**
   - Clearly state data collection practices
   - Explain data usage
   - Provide opt-out mechanisms
   - Comply with GDPR/CCPA if applicable

3. **Audit Trail**
   - Log data access
   - Track data modifications
   - Maintain compliance records

---

## Security Checklist

### Pre-Deployment

- [ ] All dependencies updated
- [ ] Security headers configured
- [ ] HTTPS enforced
- [ ] Strong passwords required
- [ ] Rate limiting implemented
- [ ] Input validation on all forms
- [ ] CSRF protection enabled
- [ ] SQL injection prevention verified
- [ ] XSS prevention verified
- [ ] File upload security implemented
- [ ] Session security configured
- [ ] Error handling configured
- [ ] Logging implemented
- [ ] Backups configured

### Post-Deployment

- [ ] SSL certificate valid
- [ ] Security headers verified
- [ ] File permissions correct
- [ ] Database permissions restricted
- [ ] Monitoring configured
- [ ] Alerts configured
- [ ] Backup tested
- [ ] Incident response plan ready
- [ ] Security audit completed
- [ ] Penetration testing completed

### Ongoing

- [ ] Regular security updates
- [ ] Log reviews
- [ ] Backup verification
- [ ] Access reviews
- [ ] Security training
- [ ] Vulnerability scanning
- [ ] Compliance audits

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [CWE Top 25](https://cwe.mitre.org/top25/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)

---

**Last Updated**: 2024
**Version**: 1.0
