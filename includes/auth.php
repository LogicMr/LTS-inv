<?php
/**
 * Authentication Functions
 * Inventory Management System
 */

// Check if session is expired
function isSessionExpired() {
    if (!isLoggedIn()) {
        return true;
    }
    
    $loginTime = $_SESSION['login_time'] ?? 0;
    return (time() - $loginTime) > SESSION_TIMEOUT;
}

// Extend session
function extendSession() {
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
}

// Check if user needs to re-authenticate
function needsReauth() {
    // Don't require re-auth on login, logout, or reauth pages
    $excludedPages = ['/auth/login.php', '/auth/logout.php', '/auth/reauth.php'];
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    foreach ($excludedPages as $excluded) {
        if (str_ends_with($currentPath, $excluded)) {
            return false;
        }
    }
    
    // Check if last activity was too long ago (15 minutes)
    $lastActivity = $_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? 0;
    $reauthTimeout = 15 * 60; // 15 minutes
    
    // Check if page was refreshed or user returned after inactivity
    $timeSinceActivity = time() - $lastActivity;
    
    return $timeSinceActivity > $reauthTimeout;
}

// Require minimum role level
function requireMinRole($minRole) {
    requireAuth();
    
    $user = getCurrentUser();
    $userRole = getRoleName($user['role_id']);
    
    // Role hierarchy
    $roleHierarchy = [
        'Admin' => 3,
        'Manager' => 2,
        'Cashier' => 1
    ];
    
    if ($roleHierarchy[$userRole] < $roleHierarchy[$minRole]) {
        redirect(BASE_URL . '/auth/unauthorized.php');
    }
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
}

// Verify password strength
function verifyPasswordStrength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return $errors;
}

// Generate secure random password
function generateSecurePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}
?>
