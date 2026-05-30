<?php
/**
 * Logout Script
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

// Log the logout activity
if (isLoggedIn()) {
    logActivity('Logout', 'User logged out', $_SESSION['user_id']);
}

// Destroy session and redirect
logout();
?>
