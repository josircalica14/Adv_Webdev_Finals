# Session Management Implementation

## Overview

The session management system provides secure, database-backed session handling with cryptographically secure tokens, automatic expiration, and secure cookie configuration.

## Components

### SessionManager Class

Located in `includes/Auth/SessionManager.php`

**Key Features:**
- Cryptographically secure token generation using `random_bytes()`
- Database-backed session storage
- Automatic session expiration (24-hour default)
- Session token regeneration for security
- Secure cookie handling with HTTP-only, Secure, and SameSite attributes
- Automatic cleanup of expired sessions

**Methods:**

```php
// Create a new session for a user
public function createSession(User $user): string

// Validate a session token and return the user
public function validateSession(string $token): ?User

// Regenerate session token (for security after login)
public function regenerateToken(string $oldToken): ?string

// Destroy a session (logout)
public function destroySession(string $token): bool

// Clean up expired sessions
public function cleanExpiredSessions(): int

// Get session data
public function getSessionData(string $token): ?array

// Set session cookie with secure settings
public function setSessionCookie(string $token, ?int $lifetime = null): bool

// Clear session cookie
public function clearSessionCookie(): bool

// Get session token from cookie
public function getSessionTokenFromCookie(): ?string
```

### AuthenticationManager Integration

The `AuthenticationManager` class has been updated to integrate with `SessionManager`:

**Login Flow:**
1. User provides email and password
2. Credentials are validated
3. Session is created with `SessionManager::createSession()`
4. Session token is stored in secure HTTP-only cookie
5. User is authenticated

**Logout Flow:**
1. Session token is retrieved from cookie
2. Session is destroyed in database
3. Cookie is cleared
4. User is logged out

**Session Validation:**
```php
// Check if user is authenticated
$authManager->isAuthenticated(): bool

// Get current authenticated user
$authManager->validateSession(): ?User
```

## Security Features

### 1. Cryptographically Secure Tokens

Session tokens are generated using PHP's `random_bytes()` function, which provides cryptographically secure random data:

```php
$token = bin2hex(random_bytes(32)); // 64-character hex string
```

### 2. HTTP-Only Cookies

Session cookies are set with the `httponly` flag to prevent JavaScript access, protecting against XSS attacks:

```php
'httponly' => true
```

### 3. Secure Flag

When HTTPS is available, cookies are set with the `secure` flag to ensure transmission only over encrypted connections:

```php
'secure' => $isSecure // true when HTTPS is detected
```

### 4. SameSite Attribute

Cookies are set with `SameSite=Strict` to prevent CSRF attacks:

```php
'samesite' => 'Strict'
```

### 5. Automatic Expiration

Sessions automatically expire after 24 hours of inactivity (configurable):

```php
$sessionLifetime = $config->get('security.session_lifetime', 86400);
```

### 6. Token Regeneration

Session tokens are regenerated after login to prevent session fixation attacks.

## Database Schema

The `sessions` table stores session data:

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
);
```

## Usage Examples

### Basic Authentication Check

```php
require_once 'includes/bootstrap.php';

// Check if user is authenticated
if (isAuthenticated()) {
    $user = currentUser();
    echo "Welcome, {$user->fullName}!";
} else {
    echo "Please log in.";
}
```

### Require Authentication

```php
require_once 'includes/bootstrap.php';

// Require authentication (redirect to login if not authenticated)
$user = requireAuth();

// User is guaranteed to be authenticated here
echo "Welcome to your dashboard, {$user->fullName}!";
```

### Manual Session Management

```php
require_once 'includes/bootstrap.php';
require_once 'includes/Auth/SessionManager.php';

$sessionManager = new SessionManager();

// Create session
$token = $sessionManager->createSession($user);
$sessionManager->setSessionCookie($token);

// Validate session
$user = $sessionManager->validateSession($token);

// Destroy session
$sessionManager->destroySession($token);
$sessionManager->clearSessionCookie();
```

### Cleanup Expired Sessions

You can run this periodically (e.g., via cron job):

```php
require_once 'includes/bootstrap.php';
require_once 'includes/Auth/SessionManager.php';

$sessionManager = new SessionManager();
$count = $sessionManager->cleanExpiredSessions();

echo "Cleaned up {$count} expired sessions.";
```

## Configuration

Session settings are configured in `config/app.config.php`:

```php
'security' => [
    'session_lifetime' => 86400, // 24 hours in seconds
    // ... other security settings
]
```

## Testing

Run the session management test suite:

```bash
php test_session.php
```

This will test:
- Session creation
- Session validation
- Token regeneration
- Session data retrieval
- Secure cookie settings
- Session destruction
- Expired session cleanup

## Helper Functions

The following helper functions are available globally after including `bootstrap.php`:

```php
// Get current authenticated user (or null)
currentUser(): ?User

// Check if user is authenticated
isAuthenticated(): bool

// Require authentication (redirect if not authenticated)
requireAuth(string $redirectUrl = '/login.php'): User
```

## Best Practices

1. **Always use HTTPS in production** to ensure secure cookie transmission
2. **Run session cleanup regularly** to remove expired sessions from the database
3. **Regenerate tokens after privilege escalation** (e.g., after password change)
4. **Log all authentication events** for security auditing
5. **Set appropriate session lifetime** based on your security requirements
6. **Monitor session activity** for suspicious patterns

## Requirements Satisfied

This implementation satisfies the following requirements from the spec:

- **Requirement 2.4**: Session validation for authenticated access
- **Requirement 2.5**: Session termination on logout
- **Requirement 2.6**: Automatic session expiration after 24 hours
- **Requirement 13.1**: Secure session token generation
- **Requirement 13.2**: HTTP-only cookies for XSS protection
- **Requirement 13.3**: Session validation on authenticated requests
- **Requirement 13.4**: Session token regeneration after login
- **Requirement 13.5**: Server-side session storage
- **Requirement 13.6**: Automatic cleanup of expired sessions

## Next Steps

1. Implement rate limiting for login attempts (Task 4.1)
2. Add CSRF protection (Task 4.2)
3. Implement property-based tests for session management (Task 3.3)
