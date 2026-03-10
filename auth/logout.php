<?php
/**
 * Logout Handler
 * 
 * Handles user logout by destroying the session and clearing cookies.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use real authentication system
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/Auth/AuthenticationManager.php';

// Create authentication manager
$authManager = new AuthenticationManager();

// Perform logout
$result = $authManager->logout();

// Also destroy PHP session as backup
session_destroy();

// Redirect to home page
header('Location: ../index.php');
exit;
?>
