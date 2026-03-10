<?php

namespace Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;
    private array $config;
    private \Logger $logger;
    private int $maxRetries = 3;
    private int $baseRetryDelay = 1; // seconds

    public function __construct(array $config, \Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->initializeMailer();
    }

    private function initializeMailer(): void
    {
        $this->mailer = new PHPMailer(true);
        
        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp_host'] ?? 'localhost';
        $this->mailer->SMTPAuth = $this->config['smtp_auth'] ?? false;
        $this->mailer->Username = $this->config['smtp_username'] ?? '';
        $this->mailer->Password = $this->config['smtp_password'] ?? '';
        $this->mailer->SMTPSecure = $this->config['smtp_secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['smtp_port'] ?? 587;
        
        // Default sender
        $this->mailer->setFrom(
            $this->config['from_email'] ?? 'noreply@portfolio.local',
            $this->config['from_name'] ?? 'Portfolio Platform'
        );
        
        // Character set
        $this->mailer->CharSet = 'UTF-8';
    }

    /**
     * Send an email with retry logic
     */
    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->maxRetries) {
            try {
                // Reset recipients for retry attempts
                $this->mailer->clearAddresses();
                $this->mailer->clearAttachments();
                
                $this->mailer->addAddress($to);
                $this->mailer->Subject = $subject;
                $this->mailer->isHTML(true);
                $this->mailer->Body = $htmlBody;
                $this->mailer->AltBody = $textBody ?: strip_tags($htmlBody);

                $result = $this->mailer->send();
                
                if ($result) {
                    $this->logger->info("Email sent successfully", [
                        'to' => $to,
                        'subject' => $subject,
                        'attempt' => $attempt + 1
                    ]);
                    return true;
                }
            } catch (Exception $e) {
                $lastException = $e;
                $this->logger->warning("Email send attempt failed", [
                    'to' => $to,
                    'subject' => $subject,
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage()
                ]);
            }

            $attempt++;
            
            // Exponential backoff: wait 1s, 2s, 4s
            if ($attempt < $this->maxRetries) {
                $delay = $this->baseRetryDelay * pow(2, $attempt - 1);
                sleep($delay);
            }
        }

        // All retries failed
        $this->logger->error("Email send failed after all retries", [
            'to' => $to,
            'subject' => $subject,
            'attempts' => $this->maxRetries,
            'last_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
        ]);

        return false;
    }

    /**
     * Send welcome email with verification link
     */
    public function sendWelcomeEmail(string $to, string $fullName, string $verificationToken): bool
    {
        $subject = "Welcome to Portfolio Platform - Verify Your Email";
        $verificationUrl = $this->config['base_url'] . '/verify-email.php?token=' . urlencode($verificationToken);
        
        $htmlBody = $this->renderTemplate('welcome', [
            'full_name' => $fullName,
            'verification_url' => $verificationUrl,
            'unsubscribe_url' => $this->getUnsubscribeUrl($to)
        ]);

        return $this->send($to, $subject, $htmlBody);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(string $to, string $fullName, string $resetToken): bool
    {
        $subject = "Password Reset Request - Portfolio Platform";
        $resetUrl = $this->config['base_url'] . '/reset-password.php?token=' . urlencode($resetToken);
        
        $htmlBody = $this->renderTemplate('password_reset', [
            'full_name' => $fullName,
            'reset_url' => $resetUrl,
            'expiry_hours' => 1,
            'unsubscribe_url' => $this->getUnsubscribeUrl($to)
        ]);

        return $this->send($to, $subject, $htmlBody);
    }

    /**
     * Send milestone notification email
     */
    public function sendMilestoneNotification(string $to, string $fullName, string $milestone, int $viewCount): bool
    {
        $subject = "Portfolio Milestone Reached - {$milestone}";
        
        $htmlBody = $this->renderTemplate('milestone', [
            'full_name' => $fullName,
            'milestone' => $milestone,
            'view_count' => $viewCount,
            'portfolio_url' => $this->config['base_url'] . '/dashboard.php',
            'unsubscribe_url' => $this->getUnsubscribeUrl($to)
        ]);

        return $this->send($to, $subject, $htmlBody);
    }

    /**
     * Render email template with variables
     */
    private function renderTemplate(string $templateName, array $variables): string
    {
        $templatePath = __DIR__ . '/templates/' . $templateName . '.html';
        
        if (!file_exists($templatePath)) {
            $this->logger->error("Email template not found", ['template' => $templateName]);
            throw new \RuntimeException("Email template not found: {$templateName}");
        }

        $template = file_get_contents($templatePath);
        
        // Replace variables in template
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $template);
        }

        return $template;
    }

    /**
     * Generate unsubscribe URL
     */
    private function getUnsubscribeUrl(string $email): string
    {
        $token = hash_hmac('sha256', $email, $this->config['secret_key'] ?? 'default-secret');
        return $this->config['base_url'] . '/unsubscribe.php?email=' . urlencode($email) . '&token=' . $token;
    }

    /**
     * Validate email address format
     */
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
