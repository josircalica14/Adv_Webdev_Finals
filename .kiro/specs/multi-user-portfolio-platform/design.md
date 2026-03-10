# Design Document: Multi-User Portfolio Platform

## Overview

This design transforms the existing single-user BSIT student portfolio into a comprehensive multi-user portfolio showcase platform. The system enables BSIT and CSE students to register accounts, create and manage personalized portfolios, customize their portfolio appearance, and share their work publicly. The platform provides a public showcase for browsing student portfolios with search and filtering capabilities, along with PDF export functionality for offline sharing.

### Key Design Goals

1. **Multi-tenancy**: Support multiple students with isolated portfolio data and customization
2. **Security**: Implement robust authentication, authorization, and data protection
3. **Scalability**: Design database schema and architecture to handle growing user base
4. **Customization**: Enable students to personalize portfolio appearance while maintaining performance
5. **Migration**: Preserve existing portfolio content during transition to multi-user system
6. **User Experience**: Maintain responsive design and performance across devices

### Technology Stack

- **Backend**: PHP 7.4+ with PDO for database access
- **Database**: MySQL 8.0+ with InnoDB storage engine
- **Frontend**: Existing HTML/CSS/JavaScript with Three.js for backgrounds
- **Authentication**: Session-based authentication with secure cookie handling
- **File Storage**: Server filesystem with organized directory structure
- **PDF Generation**: TCPDF or similar PHP PDF library
- **Email**: PHPMailer for transactional emails

## Architecture

### System Architecture

The platform follows a layered MVC-inspired architecture:

```
┌─────────────────────────────────────────────────────────────┐
│                     Presentation Layer                       │
│  (PHP Views, HTML/CSS/JS, Responsive UI Components)         │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                    Application Layer                         │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Auth System  │  │ Portfolio    │  │ Customization│     │
│  │              │  │ Manager      │  │ Engine       │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Export       │  │ File Storage │  │ Email        │     │
│  │ Generator    │  │ Manager      │  │ Service      │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
                            │
┌─────────────────────────────────────────────────────────────┐
│                      Data Layer                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │ Database     │  │ File System  │  │ Session      │     │
│  │ (MySQL)      │  │ Storage      │  │ Store        │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└─────────────────────────────────────────────────────────────┘
```

### Component Responsibilities

**Authentication System**
- User registration with validation and email verification
- Secure login/logout with session management
- Password hashing using bcrypt (cost factor 12)
- Rate limiting for brute force protection
- CSRF token generation and validation
- Session token management and expiration

**Portfolio Manager**
- CRUD operations for portfolio items (projects, achievements, milestones)
- Portfolio visibility control (public/private)
- Item ordering and organization
- Access control enforcement (students can only modify their own portfolios)
- Portfolio data retrieval for showcase and individual views

**Customization Engine**
- Storage and retrieval of customization settings
- Theme application (colors, fonts, layouts)
- Live preview generation
- CSS generation from customization settings
- Default theme management

**Export Generator**
- PDF generation from portfolio data
- Customization styling application to PDFs
- Image embedding and optimization
- Selective item inclusion
- Error handling for generation failures

**File Storage Manager**
- File upload validation (type, size, malware scanning)
- Unique filename generation
- Thumbnail creation for images
- File association with portfolio items
- Secure file deletion
- Storage quota management

**Email Service**
- Transactional email sending (verification, password reset, notifications)
- Template rendering
- Retry logic for failed sends
- Unsubscribe management
- Email logging

### Security Architecture

**Authentication Flow**
```
Registration → Email Verification → Login → Session Creation → Access Control
```

**Security Measures**
1. **Password Security**: bcrypt hashing with salt (cost 12)
2. **Session Security**: HTTP-only cookies, secure flag, SameSite attribute
3. **CSRF Protection**: Token validation on all state-changing operations
4. **SQL Injection Prevention**: Prepared statements with parameterized queries
5. **XSS Prevention**: Input sanitization and output escaping
6. **Rate Limiting**: IP-based throttling on authentication and uploads
7. **File Upload Security**: Type validation, size limits, malware scanning
8. **Access Control**: Role-based permissions with ownership validation

## Components and Interfaces

### Authentication System

**Class: AuthenticationManager**

```php
class AuthenticationManager {
    private PDO $db;
    private SessionManager $sessionManager;
    private RateLimiter $rateLimiter;
    
    public function register(string $email, string $password, string $fullName, 
                           string $program): RegistrationResult;
    public function login(string $email, string $password): LoginResult;
    public function logout(): void;
    public function verifyEmail(string $token): bool;
    public function requestPasswordReset(string $email): bool;
    public function resetPassword(string $token, string $newPassword): bool;
    public function changePassword(int $userId, string $currentPassword, 
                                  string $newPassword): bool;
    public function validateSession(): ?User;
    public function isAuthenticated(): bool;
}
```

**Class: SessionManager**

```php
class SessionManager {
    private string $sessionStore;
    
    public function createSession(User $user): string;
    public function validateSession(string $token): ?Session;
    public function regenerateToken(string $oldToken): string;
    public function destroySession(string $token): void;
    public function cleanExpiredSessions(): void;
    public function getSessionData(string $token): ?array;
}
```

**Class: RateLimiter**

```php
class RateLimiter {
    private PDO $db;
    
    public function checkLimit(string $identifier, string $action, 
                              int $maxAttempts, int $windowSeconds): bool;
    public function recordAttempt(string $identifier, string $action): void;
    public function resetLimit(string $identifier, string $action): void;
}
```

### Portfolio Manager

**Class: PortfolioManager**

```php
class PortfolioManager {
    private PDO $db;
    private FileStorageManager $fileManager;
    
    public function getPortfolio(int $userId): Portfolio;
    public function getPublicPortfolio(string $username): ?Portfolio;
    public function updateVisibility(int $userId, bool $isPublic): bool;
    
    public function createItem(int $userId, PortfolioItemData $data): PortfolioItem;
    public function updateItem(int $itemId, int $userId, PortfolioItemData $data): bool;
    public function deleteItem(int $itemId, int $userId): bool;
    public function getItem(int $itemId): ?PortfolioItem;
    public function getItems(int $userId): array;
    public function reorderItems(int $userId, array $itemIds): bool;
    public function updateItemVisibility(int $itemId, int $userId, bool $isVisible): bool;
}
```

**Class: ShowcaseManager**

```php
class ShowcaseManager {
    private PDO $db;
    
    public function getPublicPortfolios(int $page, int $perPage): PaginatedResult;
    public function searchPortfolios(SearchCriteria $criteria, int $page, 
                                    int $perPage): PaginatedResult;
    public function filterByProgram(string $program, int $page, 
                                   int $perPage): PaginatedResult;
    public function sortPortfolios(array $portfolios, string $sortBy): array;
}
```

### Customization Engine

**Class: CustomizationEngine**

```php
class CustomizationEngine {
    private PDO $db;
    
    public function getSettings(int $userId): CustomizationSettings;
    public function updateSettings(int $userId, CustomizationSettings $settings): bool;
    public function resetToDefaults(int $userId): bool;
    public function generateCSS(CustomizationSettings $settings): string;
    public function getAvailableThemes(): array;
    public function getAvailableFonts(): array;
}
```

**Class: CustomizationSettings**

```php
class CustomizationSettings {
    public string $theme;
    public string $layout; // 'grid', 'list', 'timeline'
    public string $primaryColor;
    public string $accentColor;
    public string $headingFont;
    public string $bodyFont;
    
    public function toArray(): array;
    public static function fromArray(array $data): self;
    public static function getDefaults(): self;
}
```

### Export Generator

**Class: ExportGenerator**

```php
class ExportGenerator {
    private PDO $db;
    private CustomizationEngine $customizationEngine;
    
    public function generatePDF(int $userId, array $itemIds = []): PDFResult;
    public function generateHTML(int $userId, array $itemIds = []): string;
    private function embedImages(array $items): array;
    private function applyCustomization(string $html, 
                                       CustomizationSettings $settings): string;
}
```

### File Storage Manager

**Class: FileStorageManager**

```php
class FileStorageManager {
    private string $uploadPath;
    private array $allowedTypes;
    private int $maxFileSize;
    
    public function uploadFile(UploadedFile $file, int $userId, 
                              int $itemId): FileRecord;
    public function deleteFile(int $fileId, int $userId): bool;
    public function getFile(int $fileId): ?FileRecord;
    public function getFilesForItem(int $itemId): array;
    public function generateThumbnail(string $filePath): string;
    public function validateFile(UploadedFile $file): ValidationResult;
    private function scanForMalware(string $filePath): bool;
    private function generateUniqueFilename(string $originalName): string;
}
```

### Profile Manager

**Class: ProfileManager**

```php
class ProfileManager {
    private PDO $db;
    private FileStorageManager $fileManager;
    
    public function getProfile(int $userId): UserProfile;
    public function updateProfile(int $userId, ProfileData $data): bool;
    public function uploadProfilePhoto(int $userId, UploadedFile $file): bool;
    public function updateUsername(int $userId, string $newUsername): bool;
    public function canChangeUsername(int $userId): bool;
}
```

### Admin Manager

**Class: AdminManager**

```php
class AdminManager {
    private PDO $db;
    
    public function getAllPortfolios(int $page, int $perPage): PaginatedResult;
    public function flagItem(int $itemId, string $reason): bool;
    public function hideItem(int $itemId, string $reason): bool;
    public function unhideItem(int $itemId): bool;
    public function sendNotification(int $userId, string $message): bool;
    public function getFlaggedContent(): array;
    public function getRecentPortfolios(int $limit): array;
    public function logAction(int $adminId, string $action, array $details): void;
}
```

## Data Models

### Database Schema

**users table**
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    program ENUM('BSIT', 'CSE') NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    bio TEXT,
    contact_info JSON,
    profile_photo_path VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_username_change TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_program (program)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**portfolios table**
```sql
CREATE TABLE portfolios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    is_public BOOLEAN DEFAULT FALSE,
    view_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_public (is_public),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**portfolio_items table**
```sql
CREATE TABLE portfolio_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_id INT NOT NULL,
    item_type ENUM('project', 'achievement', 'milestone', 'skill') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    item_date DATE,
    tags JSON,
    links JSON,
    is_visible BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    INDEX idx_portfolio_id (portfolio_id),
    INDEX idx_item_type (item_type),
    INDEX idx_display_order (display_order),
    INDEX idx_tags ((CAST(tags AS CHAR(255) ARRAY)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**files table**
```sql
CREATE TABLE files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_item_id INT NOT NULL,
    user_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    thumbnail_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_item_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_portfolio_item_id (portfolio_item_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**customization_settings table**
```sql
CREATE TABLE customization_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_id INT NOT NULL,
    theme VARCHAR(50) DEFAULT 'default',
    layout VARCHAR(20) DEFAULT 'grid',
    primary_color VARCHAR(7) DEFAULT '#3498db',
    accent_color VARCHAR(7) DEFAULT '#e74c3c',
    heading_font VARCHAR(100) DEFAULT 'Roboto',
    body_font VARCHAR(100) DEFAULT 'Open Sans',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (portfolio_id) REFERENCES portfolios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_portfolio (portfolio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**sessions table**
```sql
CREATE TABLE sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**email_verifications table**
```sql
CREATE TABLE email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    verification_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification_token (verification_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**password_resets table**
```sql
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    reset_token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reset_token (reset_token),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**rate_limits table**
```sql
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    identifier VARCHAR(255) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempt_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_identifier_action (identifier, action),
    INDEX idx_window_start (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**admin_actions table**
```sql
CREATE TABLE admin_actions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**flagged_content table**
```sql
CREATE TABLE flagged_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    portfolio_item_id INT NOT NULL,
    flagged_by INT NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    is_hidden BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (portfolio_item_id) REFERENCES portfolio_items(id) ON DELETE CASCADE,
    FOREIGN KEY (flagged_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Entity Relationships

```
users (1) ──── (1) portfolios
portfolios (1) ──── (many) portfolio_items
portfolio_items (1) ──── (many) files
portfolios (1) ──── (1) customization_settings
users (1) ──── (many) sessions
users (1) ──── (many) email_verifications
users (1) ──── (many) password_resets
users (1) ──── (many) admin_actions
portfolio_items (1) ──── (many) flagged_content
```

### Data Transfer Objects

**RegistrationResult**
```php
class RegistrationResult {
    public bool $success;
    public ?int $userId;
    public ?string $error;
    public ?string $verificationToken;
}
```

**LoginResult**
```php
class LoginResult {
    public bool $success;
    public ?User $user;
    public ?string $sessionToken;
    public ?string $error;
}
```

**PortfolioItemData**
```php
class PortfolioItemData {
    public string $itemType;
    public string $title;
    public string $description;
    public ?string $itemDate;
    public array $tags;
    public array $links;
    public bool $isVisible;
}
```

**SearchCriteria**
```php
class SearchCriteria {
    public ?string $query;
    public ?string $program;
    public ?string $sortBy;
    public array $tags;
}
```

**PaginatedResult**
```php
class PaginatedResult {
    public array $items;
    public int $total;
    public int $page;
    public int $perPage;
    public int $totalPages;
}
```

**PDFResult**
```php
class PDFResult {
    public bool $success;
    public ?string $filePath;
    public ?string $error;
    public int $generationTime;
}
```

**ValidationResult**
```php
class ValidationResult {
    public bool $isValid;
    public array $errors;
}
```


## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property Reflection

After analyzing all acceptance criteria, I identified several areas of redundancy:

1. **File validation properties** (4.4, 11.1, 11.2) can be consolidated into comprehensive file validation properties
2. **Access control properties** (5.5, 18.5) test the same concept and can be combined
3. **Session management properties** (2.4, 13.3) overlap and can be unified
4. **Timestamp storage properties** (4.7, 5.6) are similar and can be combined
5. **Email sending properties** (1.5, 19.1, 19.2) can be grouped by trigger event
6. **Visibility control properties** (7.2, 7.6, 14.6) test similar access control concepts

The following properties represent the unique, non-redundant correctness requirements:

### Authentication and Security Properties

### Property 1: Valid Registration Creates Account

*For any* valid registration data (unique email, strong password, full name, program), the Authentication_System should successfully create a user account with all provided information stored correctly.

**Validates: Requirements 1.1, 1.7**

### Property 2: Email Uniqueness Enforcement

*For any* email address already registered in the system, attempting to register a new account with that email should fail with an appropriate error message.

**Validates: Requirements 1.2, 1.4**

### Property 3: Password Strength Validation

*For any* password that violates security requirements (less than 8 characters, missing uppercase, missing lowercase, or missing number), the Authentication_System should reject the registration with a validation error.

**Validates: Requirements 1.3**

### Property 4: Email Format Validation

*For any* string that does not match valid email format patterns, the Authentication_System should reject it as an invalid email address.

**Validates: Requirements 1.6**

### Property 5: Password Hashing

*For any* user account in the database, the stored password should be a bcrypt hash, not plaintext, and should verify correctly against the original password.

**Validates: Requirements 2.3**

### Property 6: Valid Login Creates Session

*For any* valid email and password combination, the Authentication_System should create an authenticated session with a valid session token.

**Validates: Requirements 2.1, 13.1**

### Property 7: Invalid Credentials Rejection

*For any* invalid email or password combination, the Authentication_System should deny access and return an error message without creating a session.

**Validates: Requirements 2.2**

### Property 8: Session Validation for Authenticated Access

*For any* authenticated request with a valid session token, the Platform should allow access to authenticated features.

**Validates: Requirements 2.4, 13.3**

### Property 9: Logout Session Termination

*For any* active session, when logout is performed, the session token should become invalid and subsequent requests with that token should be rejected.

**Validates: Requirements 2.5**

### Property 10: Session Expiration

*For any* session that has been inactive for 24 hours or more, the Authentication_System should treat it as expired and reject authentication attempts.

**Validates: Requirements 2.6**

### Property 11: Login Rate Limiting

*For any* IP address that makes 5 failed login attempts within 15 minutes, the Authentication_System should block further login attempts from that IP for the remainder of the window.

**Validates: Requirements 2.7**

### Property 12: Session Token Security

*For any* session created by the Authentication_System, the session token should be stored in an HTTP-only cookie to prevent XSS attacks.

**Validates: Requirements 13.2**

### Property 13: Session Token Regeneration

*For any* successful login, the Authentication_System should generate a new session token rather than reusing any existing token.

**Validates: Requirements 13.4**

### Property 14: CSRF Protection

*For any* state-changing operation (POST, PUT, DELETE), the Platform should require a valid CSRF token and reject requests without one.

**Validates: Requirements 13.7**

### Property 15: SQL Injection Prevention

*For any* user input containing SQL injection patterns (e.g., `' OR '1'='1`, `'; DROP TABLE`), the Platform should sanitize the input and prevent execution of malicious SQL.

**Validates: Requirements 18.1**

### Property 16: XSS Prevention

*For any* user input containing XSS patterns (e.g., `<script>`, `javascript:`, event handlers), the Platform should sanitize the input and prevent script execution.

**Validates: Requirements 18.2**

### Property 17: Authentication Logging

*For any* authentication attempt (successful or failed), the Platform should create a log entry with timestamp, IP address, and outcome.

**Validates: Requirements 18.6**

### Profile Management Properties

### Property 18: Profile Viewing Access Control

*For any* authenticated user, they should be able to view their own profile information.

**Validates: Requirements 3.1**

### Property 19: Profile Update Persistence

*For any* authenticated user updating their profile fields (name, bio, contact info, program), the changes should be persisted and retrievable in subsequent profile views.

**Validates: Requirements 3.2**

### Property 20: Profile Update Validation

*For any* profile update with invalid input (e.g., empty name, invalid email format), the Platform should reject the update with validation errors.

**Validates: Requirements 3.3**

### Property 21: Password Change Requires Current Password

*For any* password change attempt without the correct current password, the Authentication_System should reject the change.

**Validates: Requirements 3.5**

### Property 22: Profile Photo Upload Validation

*For any* file uploaded as a profile photo that is not JPEG, PNG, or WebP, or exceeds 5MB, the Platform should reject the upload with an appropriate error.

**Validates: Requirements 3.7**

### Portfolio Management Properties

### Property 23: Portfolio Item Creation

*For any* authenticated user with valid portfolio item data (title, description, type), the Portfolio_Manager should create the item and associate it with the user's portfolio.

**Validates: Requirements 4.1, 4.6**

### Property 24: Required Field Validation

*For any* portfolio item creation attempt missing required fields (title, description, or type), the Portfolio_Manager should reject the creation with validation errors.

**Validates: Requirements 4.2**

### Property 25: Optional Field Handling

*For any* portfolio item, it should be possible to create it with or without optional fields (date, tags, links, attachments), and the item should be stored correctly in both cases.

**Validates: Requirements 4.3**

### Property 26: File Upload Validation

*For any* file uploaded for a portfolio item that is not an allowed type (JPEG, PNG, WebP, GIF, PDF) or exceeds 10MB, the Platform should reject the upload with a descriptive error.

**Validates: Requirements 4.4, 11.1, 11.2**

### Property 27: File Attachment Limit

*For any* portfolio item with 10 file attachments, attempting to add an 11th file should be rejected.

**Validates: Requirements 4.5**

### Property 28: Timestamp Storage

*For any* portfolio item created or updated, the Platform should store the creation timestamp and update the last modified timestamp on edits.

**Validates: Requirements 4.7, 5.6**

### Property 29: Portfolio Item Retrieval

*For any* authenticated user, they should be able to retrieve all portfolio items belonging to their portfolio.

**Validates: Requirements 5.1**

### Property 30: Portfolio Item Update

*For any* authenticated user and any of their portfolio items, they should be able to update the item's fields and have the changes persisted.

**Validates: Requirements 5.2**

### Property 31: Portfolio Item Deletion

*For any* authenticated user and any of their portfolio items, they should be able to delete the item and it should no longer appear in their portfolio.

**Validates: Requirements 5.3**

### Property 32: Cascade File Deletion

*For any* portfolio item with file attachments, when the item is deleted, all associated files should also be deleted from storage.

**Validates: Requirements 5.4, 12.7**

### Property 33: Access Control for Portfolio Items

*For any* user attempting to edit or delete a portfolio item belonging to another user, the Portfolio_Manager should deny the operation.

**Validates: Requirements 5.5, 18.5**

### Property 34: Portfolio Item Reordering

*For any* authenticated user reordering their portfolio items, the new order should be persisted and reflected in subsequent retrievals.

**Validates: Requirements 5.7**

### Customization Properties

### Property 35: Customization Settings Persistence

*For any* authenticated user updating customization settings (theme, layout, colors, fonts), the changes should be persisted and applied to their public portfolio view.

**Validates: Requirements 6.1, 6.2, 6.3, 6.4, 6.5**

### Property 36: Customization Reset

*For any* authenticated user resetting customization to defaults, all customization settings should revert to the default theme, layout, colors, and fonts.

**Validates: Requirements 6.7**

### Visibility Control Properties

### Property 37: Portfolio Visibility Control

*For any* authenticated user setting their portfolio visibility to private, unauthenticated users should not be able to view the portfolio, and it should not appear in the public showcase.

**Validates: Requirements 7.1, 7.2**

### Property 38: Public Portfolio Showcase Inclusion

*For any* portfolio set to public, it should appear in the public showcase listing.

**Validates: Requirements 7.3**

### Property 39: Default Portfolio Visibility

*For any* newly created portfolio, it should be set to private by default.

**Validates: Requirements 7.4**

### Property 40: Portfolio Item Visibility Control

*For any* portfolio item set to hidden, it should not appear in the public portfolio view but should remain visible to the portfolio owner.

**Validates: Requirements 7.5, 7.6**

### Export Properties

### Property 41: PDF Generation Success

*For any* authenticated user with a valid portfolio, the Export_Generator should successfully generate a PDF file containing their profile and visible portfolio items.

**Validates: Requirements 8.1**

### Property 42: PDF Content Completeness

*For any* generated PDF, it should include the student's profile information, all visible portfolio items (or selected items), and applied customization styling.

**Validates: Requirements 8.2**

### Property 43: PDF Image Embedding

*For any* portfolio item with images included in a PDF export, the images should be embedded in the generated PDF.

**Validates: Requirements 8.4**

### Property 44: Selective PDF Export

*For any* PDF export with a specified subset of portfolio items, only the selected items should be included in the generated PDF.

**Validates: Requirements 8.6**

### Property 45: PDF Generation Error Handling

*For any* PDF generation that fails, the Export_Generator should return a descriptive error message indicating the reason for failure.

**Validates: Requirements 8.7**

### Showcase Properties

### Property 46: Public Portfolio Display

*For any* set of public portfolios, the Showcase should display all of them on the showcase page.

**Validates: Requirements 9.1**

### Property 47: Portfolio Preview Card Content

*For any* portfolio preview card in the showcase, it should display the student's name, program, profile photo (or placeholder), and bio.

**Validates: Requirements 9.2, 9.7**

### Property 48: Portfolio Customization Rendering

*For any* student portfolio displayed in the showcase, it should be rendered using that student's customization settings.

**Validates: Requirements 9.4**

### Property 49: Portfolio Item Ordering

*For any* student portfolio, the portfolio items should be displayed in the order specified by the student.

**Validates: Requirements 9.5**

### Property 50: Showcase Pagination

*For any* showcase listing with more than 20 portfolios, the results should be paginated with at most 20 portfolios per page.

**Validates: Requirements 9.6, 17.6**

### Search and Filter Properties

### Property 51: Portfolio Search

*For any* search query, the Showcase should return only portfolios where the query matches the student name, bio keywords, or portfolio item tags.

**Validates: Requirements 10.1**

### Property 52: Program Filter

*For any* program filter selection (BSIT, CSE, or All), the Showcase should display only portfolios matching the selected program.

**Validates: Requirements 10.3, 10.4**

### Property 53: Portfolio Sorting

*For any* sort option (most recently updated or alphabetically by name), the Showcase should display portfolios in the correct order.

**Validates: Requirements 10.5**

### Property 54: Search Result Count

*For any* search and filter criteria, the displayed count should match the actual number of portfolios in the results.

**Validates: Requirements 10.6**

### File Storage Properties

### Property 55: Unique File Storage

*For any* uploaded file, the Platform should store it with a unique identifier to prevent filename collisions.

**Validates: Requirements 11.3**

### Property 56: File Association

*For any* uploaded file, it should be correctly associated with the portfolio item and user account that uploaded it.

**Validates: Requirements 11.4**

### Property 57: File Upload Error Messages

*For any* failed file upload, the Platform should return a descriptive error message indicating the specific reason for failure.

**Validates: Requirements 11.5**

### Property 58: Malware Scanning

*For any* uploaded file, the Platform should scan it for malware before storing it.

**Validates: Requirements 11.7**

### Username and URL Properties

### Property 59: Unique Portfolio URLs

*For any* student, the Platform should generate a unique portfolio URL in the format /portfolio/{username}.

**Validates: Requirements 14.1**

### Property 60: Username Creation

*For any* student registration, the Platform should create a username (from email or custom choice) and associate it with the account.

**Validates: Requirements 14.2**

### Property 61: Username Uniqueness

*For any* username already in use, attempting to register or change to that username should be rejected.

**Validates: Requirements 14.3**

### Property 62: Username Format Validation

*For any* username containing characters other than alphanumeric, hyphens, or underscores, the Platform should reject it with a validation error.

**Validates: Requirements 14.4**

### Property 63: Public Portfolio URL Access

*For any* public portfolio, accessing its URL should display the portfolio; for private portfolios, it should display an unavailable message.

**Validates: Requirements 14.5, 14.6**

### Property 64: Username Change Rate Limiting

*For any* user who has changed their username within the last 30 days, attempting to change it again should be rejected.

**Validates: Requirements 14.7**

### Admin Properties

### Property 65: Admin Portfolio Access

*For any* user with admin privileges, they should be able to view all portfolios regardless of visibility settings.

**Validates: Requirements 15.1**

### Property 66: Admin Content Flagging

*For any* portfolio item, an admin should be able to flag it for review and the flag should be recorded.

**Validates: Requirements 15.2**

### Property 67: Admin Content Hiding

*For any* portfolio item, an admin should be able to hide it from public view, and it should no longer appear in public portfolio displays.

**Validates: Requirements 15.3**

### Property 68: Admin Notification Sending

*For any* student user, an admin should be able to send them a notification message about content issues.

**Validates: Requirements 15.4**

### Property 69: Admin Dashboard Data

*For any* admin dashboard view, it should display recently created portfolios and flagged content.

**Validates: Requirements 15.5**

### Property 70: Admin Action Logging

*For any* admin moderation action, the Platform should log the action with timestamp and admin identifier.

**Validates: Requirements 15.6**

### Property 71: Admin Content Restoration

*For any* hidden portfolio item, an admin should be able to restore it to public visibility.

**Validates: Requirements 15.7**

### Rate Limiting Properties

### Property 72: File Upload Rate Limiting

*For any* student who has uploaded 20 files within the last hour, attempting to upload another file should be rejected.

**Validates: Requirements 18.7**

### Email Properties

### Property 73: Registration Email Sending

*For any* successful registration, the Platform should send a welcome email with an account verification link to the registered email address.

**Validates: Requirements 1.5, 19.1**

### Property 74: Password Reset Email

*For any* password reset request, the Platform should send an email with a secure token that is valid for 1 hour.

**Validates: Requirements 19.2**

### Property 75: Milestone Notification Email

*For any* student with notifications enabled whose portfolio reaches a view milestone, the Platform should send a milestone notification email.

**Validates: Requirements 19.3**

### Property 76: Email Unsubscribe Links

*For any* notification email sent by the Platform, it should include an unsubscribe link.

**Validates: Requirements 19.4**

### Property 77: Email Address Validation

*For any* email address that fails format validation, the Platform should not attempt to send emails to it.

**Validates: Requirements 19.5**

### Property 78: Email Retry Logic

*For any* email that fails to send, the Platform should log the error and retry up to 3 times before giving up.

**Validates: Requirements 19.6**

### Migration Properties

### Property 79: Migration Data Conversion

*For any* existing portfolio data, the migration script should successfully convert it to the multi-user schema without data loss.

**Validates: Requirements 20.1**

### Property 80: Project Migration

*For any* project in projects-data.js, the migration script should create a corresponding portfolio_item record in the database.

**Validates: Requirements 20.3**

### Property 81: Skills Migration

*For any* skill in skills-data.js, the migration script should create a corresponding portfolio_item record with type 'skill' in the database.

**Validates: Requirements 20.4**

### Property 82: File Reference Preservation

*For any* file reference in the existing portfolio, the migration script should preserve the reference and update the path if necessary.

**Validates: Requirements 20.5**

### Property 83: Migration Report Generation

*For any* completed migration, the Platform should generate a report showing all migrated items and any errors encountered.

**Validates: Requirements 20.7**

## Error Handling

### Error Categories

**Validation Errors**
- Invalid input format (email, password, username)
- Missing required fields
- Constraint violations (uniqueness, length limits)
- File type or size violations

**Authentication Errors**
- Invalid credentials
- Expired sessions
- Rate limit exceeded
- CSRF token mismatch
- Insufficient permissions

**Resource Errors**
- Portfolio not found
- Portfolio item not found
- File not found
- User not found

**System Errors**
- Database connection failures
- File system errors
- PDF generation failures
- Email sending failures
- Malware scan failures

### Error Handling Strategy

**User-Facing Errors**
- Return clear, actionable error messages
- Avoid exposing system internals or security details
- Provide suggestions for resolution when possible
- Log detailed error information server-side

**Error Response Format**
```php
class ErrorResponse {
    public bool $success = false;
    public string $error;
    public ?string $errorCode;
    public ?array $validationErrors;
    public ?string $suggestion;
}
```

**Error Logging**
- Log all errors with severity levels (ERROR, WARNING, INFO)
- Include context: user ID, request path, timestamp, stack trace
- Store logs in rotating files with retention policy
- Monitor critical errors for alerting

**Graceful Degradation**
- If PDF generation fails, offer HTML export alternative
- If email sending fails, display message in user dashboard
- If thumbnail generation fails, use original image
- If customization CSS fails, fall back to default theme

**Transaction Management**
- Use database transactions for multi-step operations
- Roll back on errors to maintain consistency
- Example: Portfolio item creation with file uploads should be atomic

**Retry Logic**
- Email sending: 3 retries with exponential backoff
- File uploads: No automatic retry (user-initiated)
- Database queries: 1 retry for deadlock errors
- External API calls: 3 retries with backoff

## Testing Strategy

### Dual Testing Approach

The platform requires both unit testing and property-based testing for comprehensive coverage:

**Unit Tests** focus on:
- Specific examples demonstrating correct behavior
- Edge cases and boundary conditions
- Integration points between components
- Error handling scenarios
- Mock external dependencies (database, file system, email)

**Property-Based Tests** focus on:
- Universal properties that hold for all inputs
- Comprehensive input coverage through randomization
- Invariants that must be maintained
- Round-trip properties (e.g., serialize/deserialize)
- Security properties (injection prevention, access control)

### Property-Based Testing Configuration

**Framework**: Use PHPUnit with [php-quickcheck](https://github.com/steos/php-quickcheck) or similar property-based testing library

**Test Configuration**:
- Minimum 100 iterations per property test
- Each test must reference its design document property
- Tag format: `@group Feature: multi-user-portfolio-platform, Property {number}: {property_text}`

**Example Property Test Structure**:
```php
/**
 * @group Feature: multi-user-portfolio-platform, Property 1: Valid Registration Creates Account
 */
public function testValidRegistrationCreatesAccount() {
    $this->forAll(
        Generator::validEmail(),
        Generator::strongPassword(),
        Generator::fullName(),
        Generator::program()
    )->then(function($email, $password, $name, $program) {
        $result = $this->authManager->register($email, $password, $name, $program);
        
        $this->assertTrue($result->success);
        $this->assertNotNull($result->userId);
        
        $user = $this->userRepository->findById($result->userId);
        $this->assertEquals($email, $user->email);
        $this->assertEquals($name, $user->fullName);
        $this->assertEquals($program, $user->program);
    });
}
```

### Test Data Generators

**Custom Generators Needed**:
- `validEmail()`: Generates valid email addresses
- `invalidEmail()`: Generates invalid email formats
- `strongPassword()`: Generates passwords meeting requirements
- `weakPassword()`: Generates passwords violating requirements
- `fullName()`: Generates realistic names
- `program()`: Generates 'BSIT' or 'CSE'
- `portfolioItemData()`: Generates valid portfolio item data
- `sqlInjectionPattern()`: Generates SQL injection attempts
- `xssPattern()`: Generates XSS attack patterns
- `validFile()`: Generates valid file uploads
- `invalidFile()`: Generates invalid file uploads (wrong type, too large)

### Unit Test Coverage

**Authentication Tests**:
- Registration with valid/invalid data
- Login with correct/incorrect credentials
- Session creation and validation
- Password reset flow
- Rate limiting enforcement
- CSRF token validation

**Portfolio Management Tests**:
- CRUD operations for portfolio items
- File upload and deletion
- Access control enforcement
- Visibility toggling
- Item reordering

**Customization Tests**:
- Settings persistence
- CSS generation from settings
- Theme application
- Reset to defaults

**Export Tests**:
- PDF generation with various portfolio sizes
- Image embedding
- Selective item export
- Error handling for generation failures

**Showcase Tests**:
- Public portfolio listing
- Search and filtering
- Pagination
- Sorting

**Admin Tests**:
- Content moderation
- Flagging and hiding
- Dashboard data retrieval
- Action logging

**Migration Tests**:
- Data conversion from old schema
- Project and skill migration
- File reference preservation
- Report generation

### Integration Tests

**Database Integration**:
- Test actual database operations (not mocked)
- Verify foreign key constraints
- Test cascade deletes
- Verify transaction rollbacks

**File System Integration**:
- Test actual file uploads and deletions
- Verify thumbnail generation
- Test storage quota enforcement

**Email Integration**:
- Test email sending (using test SMTP server)
- Verify email templates render correctly
- Test retry logic

### Security Tests

**Injection Prevention**:
- SQL injection attempts in all input fields
- XSS attempts in text fields
- Path traversal attempts in file uploads

**Access Control**:
- Unauthorized access attempts
- Cross-user data access attempts
- Admin privilege escalation attempts

**Rate Limiting**:
- Brute force login attempts
- Excessive file uploads
- API abuse scenarios

### Performance Tests

**Load Testing**:
- Showcase page load with 1000+ portfolios
- Concurrent user sessions
- Bulk file uploads
- PDF generation under load

**Database Performance**:
- Query performance with large datasets
- Index effectiveness
- N+1 query detection

### Test Environment

**Database**: Use separate test database with migrations
**File Storage**: Use temporary directory cleaned after tests
**Email**: Use test SMTP server or mock email service
**Sessions**: Use in-memory session store for tests

### Continuous Integration

**Pre-commit Hooks**:
- Run unit tests
- Check code style (PSR-12)
- Run static analysis (PHPStan)

**CI Pipeline**:
1. Run all unit tests
2. Run all property-based tests (100 iterations each)
3. Run integration tests
4. Run security tests
5. Generate coverage report (target: 80%+ coverage)
6. Run static analysis
7. Check for security vulnerabilities

### Test Maintenance

- Update tests when requirements change
- Review and update generators as data models evolve
- Monitor test execution time and optimize slow tests
- Regularly review test coverage and add tests for uncovered code

