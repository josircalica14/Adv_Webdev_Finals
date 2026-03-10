# Admin Moderation System

## Overview

The Admin Moderation System provides comprehensive tools for platform administrators to manage content, moderate portfolios, and maintain platform quality. It includes content flagging, visibility control, student notifications, and complete audit logging.

## Components

### AdminManager

The `AdminManager` class is the core component that handles all admin moderation operations.

**Location:** `includes/Admin/AdminManager.php`

**Dependencies:**
- PDO database connection
- EmailService for notifications
- Logger for audit trails

## Features

### 1. Portfolio Management

**getAllPortfolios(int $page, int $perPage): array**

Retrieve all portfolios with pagination, regardless of visibility status.

```php
$result = $adminManager->getAllPortfolios(1, 20);
// Returns:
// [
//     'items' => [...],      // Array of portfolio data with user info
//     'total' => 150,        // Total portfolio count
//     'page' => 1,           // Current page
//     'perPage' => 20,       // Items per page
//     'totalPages' => 8      // Total pages
// ]
```

**getRecentPortfolios(int $limit): array**

Get recently created portfolios for dashboard display.

```php
$recent = $adminManager->getRecentPortfolios(10);
// Returns array of 10 most recent portfolios with user details
```

### 2. Content Moderation

**flagItem(int $itemId, int $adminId, string $reason): bool**

Flag a portfolio item for review.

```php
$success = $adminManager->flagItem(
    $itemId,
    $adminId,
    "Contains inappropriate content"
);
```

**hideItem(int $itemId, int $adminId, string $reason): bool**

Hide a portfolio item from public view.

```php
$success = $adminManager->hideItem(
    $itemId,
    $adminId,
    "Policy violation - offensive language"
);
```

**unhideItem(int $itemId, int $adminId): bool**

Restore a hidden portfolio item to public view.

```php
$success = $adminManager->unhideItem($itemId, $adminId);
```

**getFlaggedContent(string $status): array**

Retrieve flagged content filtered by status.

```php
// Get pending flags
$pending = $adminManager->getFlaggedContent('pending');

// Get all flagged content
$all = $adminManager->getFlaggedContent('all');

// Status options: 'pending', 'reviewed', 'resolved', 'all'
```

### 3. Student Communication

**sendNotification(int $userId, int $adminId, string $subject, string $message): bool**

Send email notification to a student.

```php
$success = $adminManager->sendNotification(
    $userId,
    $adminId,
    "Portfolio Content Review",
    "Your portfolio item has been flagged for review. Please update the content to comply with our guidelines."
);
```

### 4. Audit Logging

**logAction(int $adminId, string $actionType, ?string $targetType, ?int $targetId, array $details): bool**

Log admin actions for audit trail (automatically called by other methods).

```php
$adminManager->logAction(
    $adminId,
    'custom_action',
    'portfolio',
    $portfolioId,
    ['reason' => 'Manual review']
);
```

## Database Tables

### flagged_content

Stores flagged portfolio items for review.

```sql
- id: Primary key
- portfolio_item_id: Reference to portfolio_items
- flagged_by: Admin user ID
- reason: Reason for flagging
- status: 'pending', 'reviewed', 'resolved'
- is_hidden: Whether item is hidden
- created_at: Flag timestamp
- reviewed_at: Review timestamp
```

### admin_actions

Audit log of all admin actions.

```sql
- id: Primary key
- admin_id: Admin user ID
- action_type: Type of action (flag_item, hide_item, etc.)
- target_type: Type of target entity
- target_id: ID of target entity
- details: JSON with additional details
- created_at: Action timestamp
```

## Usage Example

```php
<?php
require_once 'includes/bootstrap.php';

use Admin\AdminManager;
use Email\EmailService;

// Initialize dependencies
$db = Database::getInstance()->getConnection();
$logger = Logger::getInstance();
$emailService = new EmailService($emailConfig, $logger);

// Create AdminManager
$adminManager = new AdminManager($db, $emailService, $logger);

// Get all portfolios for admin dashboard
$portfolios = $adminManager->getAllPortfolios(1, 20);

// Get flagged content
$flagged = $adminManager->getFlaggedContent('pending');

// Flag inappropriate content
$adminManager->flagItem(
    $itemId,
    $adminId,
    "Contains copyrighted material"
);

// Hide the item
$adminManager->hideItem(
    $itemId,
    $adminId,
    "Copyright violation"
);

// Notify the student
$adminManager->sendNotification(
    $userId,
    $adminId,
    "Content Removed",
    "Your portfolio item was removed due to copyright concerns. Please upload original work only."
);
```

## Testing

Run the test suite to verify AdminManager functionality:

```bash
php test_admin.php
```

The test suite covers:
- Portfolio retrieval with pagination
- Recent portfolios listing
- Flagged content retrieval
- Item flagging
- Item hiding/unhiding
- Notification sending
- Audit log verification

## Security Considerations

1. **Access Control**: Always verify admin privileges before allowing access to AdminManager methods
2. **Audit Logging**: All actions are automatically logged for accountability
3. **Transaction Safety**: Hide/unhide operations use database transactions
4. **Input Validation**: Validate all input parameters before processing
5. **Email Validation**: Student emails are validated before sending notifications

## Admin Privileges

To grant admin privileges to a user:

```sql
UPDATE users SET is_admin = 1 WHERE id = ?;
```

Check admin status:

```php
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$isAdmin = (bool)$user['is_admin'];
```

## Error Handling

All methods return boolean success status or arrays. Check return values and log errors:

```php
$result = $adminManager->hideItem($itemId, $adminId, $reason);
if (!$result) {
    error_log("Failed to hide item: $itemId");
    // Handle error appropriately
}
```

## Future Enhancements

Potential improvements for the admin system:

1. Bulk moderation actions
2. Content appeal system
3. Automated content filtering
4. Admin role hierarchy (super admin, moderator, etc.)
5. Moderation statistics and reports
6. Content review workflow with multiple stages
7. Integration with external content moderation APIs
