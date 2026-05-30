<?php
/**
 * Password Verification for Logged-in Users
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php');
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/auth/login.php');
}

$password = $_POST['verify_password'] ?? '';

if (empty($password)) {
    $_SESSION['verify_error'] = 'Password is required for verification';
    redirect(BASE_URL . '/auth/login.php');
}

// Verify the password
if (verifyReauth($password)) {
    // Password verification successful
    unset($_SESSION['verify_error']);
    
    // Update last activity
    updateLastActivity();
    
    // Log successful verification
    logActivity('Password Verification', 'User successfully verified password while logged in');
    
    // Redirect to dashboard
    redirect(BASE_URL . '/' . getUserDashboard());
} else {
    // Password verification failed
    $_SESSION['verify_error'] = 'Invalid password. Please try again.';
    
    // Log failed verification attempt
    logActivity('Password Verification Failed', 'Failed password verification attempt while logged in');
    
    redirect(BASE_URL . '/auth/login.php');
}
?>
