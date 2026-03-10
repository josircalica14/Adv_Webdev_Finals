<?php
/**
 * AdminManager
 * 
 * Handles admin moderation functionality including portfolio management,
 * content flagging, notifications, and audit logging.
 */

namespace Admin;

use PDO;
use PDOException;
use Email\EmailService;

class AdminManager {
    private PDO $db;
    private EmailService $emailService;
    private \Logger $logger;
    private \SecurityLogger $securityLogger;

    public function __construct(PDO $db, EmailService $emailService, \Logger $logger) {
        $this->db = $db;
        $this->emailService = $emailService;
        $this->logger = $logger;
        $this->securityLogger = \SecurityLogger::getInstance();
    }

    /**
     * Get all portfolios with pagination (admin view)
     * 
     * @param int $page Page number (1-indexed)
     * @param int $perPage Items per page
     * @return array Paginated result with portfolios and metadata
     */
    public function getAllPortfolios(int $page = 1, int $perPage = 20): array {
        try {
            $offset = ($page - 1) * $perPage;

            // Get total count
            $countStmt = $this->db->query("SELECT COUNT(*) FROM portfolios");
            $total = (int)$countStmt->fetchColumn();

            // Get portfolios with user information
            $stmt = $this->db->prepare(
                "SELECT p.*, u.full_name, u.email, u.username, u.program, u.profile_photo_path
                 FROM portfolios p
                 INNER JOIN users u ON p.user_id = u.id
                 ORDER BY p.updated_at DESC
                 LIMIT :limit OFFSET :offset"
            );
            $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $portfolios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'items' => $portfolios,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => (int)ceil($total / $perPage)
            ];
        } catch (PDOException $e) {
            $this->logger->error("Error getting all portfolios", [
                'error' => $e->getMessage()
            ]);
            return [
                'items' => [],
                'total' => 0,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => 0
            ];
        }
    }

    /**
     * Flag a portfolio item for review
     * 
     * @param int $itemId Portfolio item ID
     * @param int $adminId Admin user ID
     * @param string $reason Reason for flagging
     * @return bool Success status
     */
    public function flagItem(int $itemId, int $adminId, string $reason): bool {
        try {
            // Check if item exists
            $checkStmt = $this->db->prepare("SELECT id FROM portfolio_items WHERE id = :id");
            $checkStmt->execute([':id' => $itemId]);
            if (!$checkStmt->fetch()) {
                $this->logger->warning("Attempted to flag non-existent item", [
                    'item_id' => $itemId,
                    'admin_id' => $adminId
                ]);
                return false;
            }

            // Insert flagged content record
            $stmt = $this->db->prepare(
                "INSERT INTO flagged_content (portfolio_item_id, flagged_by, reason, status)
                 VALUES (:item_id, :flagged_by, :reason, 'pending')"
            );

            $result = $stmt->execute([
                ':item_id' => $itemId,
                ':flagged_by' => $adminId,
                ':reason' => $reason
            ]);

            if ($result) {
                $this->logAction($adminId, 'flag_item', 'portfolio_item', $itemId, [
                    'reason' => $reason
                ]);
                $this->logger->info("Portfolio item flagged", [
                    'item_id' => $itemId,
                    'admin_id' => $adminId
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Error flagging item", [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Hide a portfolio item from public view
     * 
     * @param int $itemId Portfolio item ID
     * @param int $adminId Admin user ID
     * @param string $reason Reason for hiding
     * @return bool Success status
     */
    public function hideItem(int $itemId, int $adminId, string $reason): bool {
        try {
            $this->db->beginTransaction();

            // Update item visibility
            $stmt = $this->db->prepare(
                "UPDATE portfolio_items SET is_visible = 0 WHERE id = :id"
            );
            $stmt->execute([':id' => $itemId]);

            // Update flagged content status if exists
            $flagStmt = $this->db->prepare(
                "UPDATE flagged_content 
                 SET is_hidden = 1, status = 'reviewed', reviewed_at = NOW()
                 WHERE portfolio_item_id = :item_id"
            );
            $flagStmt->execute([':item_id' => $itemId]);

            $this->db->commit();

            $this->logAction($adminId, 'hide_item', 'portfolio_item', $itemId, [
                'reason' => $reason
            ]);

            $this->logger->info("Portfolio item hidden", [
                'item_id' => $itemId,
                'admin_id' => $adminId
            ]);

            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Error hiding item", [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Unhide a portfolio item (restore visibility)
     * 
     * @param int $itemId Portfolio item ID
     * @param int $adminId Admin user ID
     * @return bool Success status
     */
    public function unhideItem(int $itemId, int $adminId): bool {
        try {
            $this->db->beginTransaction();

            // Update item visibility
            $stmt = $this->db->prepare(
                "UPDATE portfolio_items SET is_visible = 1 WHERE id = :id"
            );
            $stmt->execute([':id' => $itemId]);

            // Update flagged content status if exists
            $flagStmt = $this->db->prepare(
                "UPDATE flagged_content 
                 SET is_hidden = 0, status = 'resolved'
                 WHERE portfolio_item_id = :item_id"
            );
            $flagStmt->execute([':item_id' => $itemId]);

            $this->db->commit();

            $this->logAction($adminId, 'unhide_item', 'portfolio_item', $itemId, []);

            $this->logger->info("Portfolio item unhidden", [
                'item_id' => $itemId,
                'admin_id' => $adminId
            ]);

            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Error unhiding item", [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send notification to a student
     * 
     * @param int $userId Student user ID
     * @param int $adminId Admin user ID
     * @param string $subject Email subject
     * @param string $message Notification message
     * @return bool Success status
     */
    public function sendNotification(int $userId, int $adminId, string $subject, string $message): bool {
        try {
            // Get user information
            $stmt = $this->db->prepare(
                "SELECT email, full_name FROM users WHERE id = :id"
            );
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $this->logger->warning("Attempted to send notification to non-existent user", [
                    'user_id' => $userId,
                    'admin_id' => $adminId
                ]);
                return false;
            }

            // Send email
            $htmlBody = $this->buildNotificationEmail($user['full_name'], $message);
            $result = $this->emailService->send($user['email'], $subject, $htmlBody);

            if ($result) {
                $this->logAction($adminId, 'send_notification', 'user', $userId, [
                    'subject' => $subject,
                    'message' => $message
                ]);

                $this->logger->info("Admin notification sent", [
                    'user_id' => $userId,
                    'admin_id' => $adminId,
                    'subject' => $subject
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Error sending notification", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get all flagged content
     * 
     * @param string $status Filter by status (pending, reviewed, resolved, or 'all')
     * @return array List of flagged content with details
     */
    public function getFlaggedContent(string $status = 'pending'): array {
        try {
            $sql = "SELECT fc.*, pi.title, pi.item_type, u.full_name, u.email, u.username
                    FROM flagged_content fc
                    INNER JOIN portfolio_items pi ON fc.portfolio_item_id = pi.id
                    INNER JOIN portfolios p ON pi.portfolio_id = p.id
                    INNER JOIN users u ON p.user_id = u.id";

            if ($status !== 'all') {
                $sql .= " WHERE fc.status = :status";
            }

            $sql .= " ORDER BY fc.created_at DESC";

            $stmt = $this->db->prepare($sql);
            
            if ($status !== 'all') {
                $stmt->execute([':status' => $status]);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error getting flagged content", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get recently created portfolios
     * 
     * @param int $limit Number of portfolios to retrieve
     * @return array List of recent portfolios
     */
    public function getRecentPortfolios(int $limit = 10): array {
        try {
            $stmt = $this->db->prepare(
                "SELECT p.*, u.full_name, u.email, u.username, u.program, u.profile_photo_path
                 FROM portfolios p
                 INNER JOIN users u ON p.user_id = u.id
                 ORDER BY p.created_at DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error getting recent portfolios", [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Log admin action for audit trail
     * 
     * @param int $adminId Admin user ID
     * @param string $actionType Type of action performed
     * @param string|null $targetType Type of target entity
     * @param int|null $targetId ID of target entity
     * @param array $details Additional action details
     * @return bool Success status
     */
    public function logAction(int $adminId, string $actionType, ?string $targetType = null, 
                             ?int $targetId = null, array $details = []): bool {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO admin_actions (admin_id, action_type, target_type, target_id, details)
                 VALUES (:admin_id, :action_type, :target_type, :target_id, :details)"
            );

            $success = $stmt->execute([
                ':admin_id' => $adminId,
                ':action_type' => $actionType,
                ':target_type' => $targetType,
                ':target_id' => $targetId,
                ':details' => json_encode($details)
            ]);
            
            // Also log to security logger
            if ($success) {
                $this->securityLogger->logAdminAction($adminId, $actionType, array_merge([
                    'target_type' => $targetType,
                    'target_id' => $targetId
                ], $details));
            }
            
            return $success;
        } catch (PDOException $e) {
            $this->logger->error("Error logging admin action", [
                'admin_id' => $adminId,
                'action_type' => $actionType,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Build notification email HTML
     * 
     * @param string $fullName Student's full name
     * @param string $message Notification message
     * @return string HTML email body
     */
    private function buildNotificationEmail(string $fullName, string $message): string {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #3498db; color: white; padding: 20px; text-align: center; }
        .content { background-color: #f9f9f9; padding: 20px; margin: 20px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Portfolio Platform Notification</h1>
        </div>
        <div class="content">
            <p>Hello {$fullName},</p>
            <p>{$message}</p>
            <p>If you have any questions, please contact the platform administrators.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from Portfolio Platform.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
