# Email Service

This directory contains the email service implementation for the Portfolio Platform, including PHPMailer integration, email templates, and retry logic.

## Components

### EmailService Class

The `EmailService` class handles all email sending functionality with the following features:

- **PHPMailer Integration**: Uses PHPMailer for reliable email delivery
- **Retry Logic**: Automatically retries failed sends up to 3 times with exponential backoff (1s, 2s, 4s)
- **Email Logging**: Logs all email attempts for debugging
- **Template Rendering**: Renders HTML email templates with variable substitution
- **Unsubscribe Links**: Automatically includes unsubscribe links in all emails

### Email Templates

Three HTML email templates are provided:

1. **welcome.html** - Welcome email with email verification link
2. **password_reset.html** - Password reset email with secure token
3. **milestone.html** - Milestone notification email for portfolio views

All templates include:
- Responsive HTML design
- Inline CSS for email client compatibility
- Unsubscribe links
- Professional branding

## Configuration

Email settings are configured in `config/app.config.php`:

```php
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
]
```

## Usage

### Basic Usage

```php
use Email\EmailService;

$config = require __DIR__ . '/config/app.config.php';
$logger = new Logger();
$emailService = new EmailService($config['email'], $logger);

// Send welcome email
$emailService->sendWelcomeEmail(
    'user@example.com',
    'John Doe',
    'verification-token-here'
);

// Send password reset email
$emailService->sendPasswordResetEmail(
    'user@example.com',
    'John Doe',
    'reset-token-here'
);

// Send milestone notification
$emailService->sendMilestoneNotification(
    'user@example.com',
    'John Doe',
    '100 Views Milestone',
    100
);
```

### Custom Email

```php
$subject = 'Custom Email Subject';
$htmlBody = '<h1>Hello</h1><p>This is a custom email.</p>';
$textBody = 'Hello. This is a custom email.';

$success = $emailService->send('user@example.com', $subject, $htmlBody, $textBody);

if ($success) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send after retries.";
}
```

## Installation

1. Install PHPMailer via Composer:
   ```bash
   composer install
   ```

2. Configure SMTP settings in `config/app.config.php`

3. For local development, you can use a tool like MailHog or Mailtrap to test emails without sending real emails

## Testing

For local development without a real SMTP server:

### Option 1: MailHog (Recommended)

1. Install MailHog: https://github.com/mailhog/MailHog
2. Run MailHog: `mailhog`
3. Configure settings:
   ```php
   'smtp_host' => 'localhost',
   'smtp_port' => 1025,
   'smtp_auth' => false
   ```
4. View emails at http://localhost:8025

### Option 2: Mailtrap

1. Sign up at https://mailtrap.io
2. Get SMTP credentials from your inbox
3. Configure settings with Mailtrap credentials

### Option 3: PHP mail() Function

Set `smtp_host` to empty string to use PHP's built-in `mail()` function (not recommended for production).

## Email Template Customization

To customize email templates:

1. Edit the HTML files in `includes/Email/templates/`
2. Use `{{variable_name}}` syntax for dynamic content
3. Test with different email clients (Gmail, Outlook, etc.)
4. Keep inline CSS for maximum compatibility

### Available Variables

**welcome.html:**
- `{{full_name}}` - User's full name
- `{{verification_url}}` - Email verification link
- `{{unsubscribe_url}}` - Unsubscribe link

**password_reset.html:**
- `{{full_name}}` - User's full name
- `{{reset_url}}` - Password reset link
- `{{expiry_hours}}` - Token expiry time in hours
- `{{unsubscribe_url}}` - Unsubscribe link

**milestone.html:**
- `{{full_name}}` - User's full name
- `{{milestone}}` - Milestone description
- `{{view_count}}` - Number of portfolio views
- `{{portfolio_url}}` - Link to user's dashboard
- `{{unsubscribe_url}}` - Unsubscribe link

## Security

- Unsubscribe links use HMAC-SHA256 for token generation
- Email addresses are validated before sending
- All user input in templates is HTML-escaped
- SMTP credentials should be kept secure and not committed to version control

## Logging

All email attempts are logged with the following information:
- Recipient email address
- Subject line
- Attempt number
- Success/failure status
- Error messages (if any)

Logs are written to the application log file configured in the Logger class.

## Requirements

- PHP 7.4 or higher
- PHPMailer 6.8 or higher
- SMTP server or mail() function configured
- Logger class for logging

## Troubleshooting

### Emails not sending

1. Check SMTP credentials in config
2. Verify SMTP server is accessible
3. Check application logs for error messages
4. Test with a simple email client to verify SMTP settings

### Emails going to spam

1. Configure SPF, DKIM, and DMARC records for your domain
2. Use a reputable SMTP service (SendGrid, Mailgun, etc.)
3. Avoid spam trigger words in subject lines
4. Include unsubscribe links (already implemented)

### Template rendering issues

1. Verify template file exists in `includes/Email/templates/`
2. Check that all required variables are provided
3. Test HTML in email testing tools like Litmus or Email on Acid
