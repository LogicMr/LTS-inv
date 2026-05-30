<?php
/**
 * Process Re-authentication
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php');
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/auth/reauth.php');
}

$password = $_POST['password'] ?? '';

if (empty($password)) {
    $_SESSION['reauth_error'] = 'Password is required';
    redirect(BASE_URL . '/auth/reauth.php');
}

// Verify the password
if (verifyReauth($password)) {
    // Re-authentication successful
    unset($_SESSION['reauth_required']);
    unset($_SESSION['reauth_error']);
    
    // Update last activity
    updateLastActivity();
    
    // Redirect to original page
    $redirectUrl = $_SESSION['reauth_redirect'] ?? BASE_URL;
    unset($_SESSION['reauth_redirect']);
    
    // Log successful re-authentication
    logActivity('Re-authentication', 'User successfully re-authenticated');
    
    redirect($redirectUrl);
} else {
    // Re-authentication failed
    $_SESSION['reauth_error'] = 'Invalid password. Please try again.';
    
    // Log failed re-authentication attempt
    logActivity('Re-authentication Failed', 'Failed re-authentication attempt');
    
    redirect(BASE_URL . '/auth/reauth.php');
}
?>
