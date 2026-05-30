<?php
/**
 * Application Configuration
 * Inventory Management System
 */

// Start session with custom path and cross-device settings
if (session_status() === PHP_SESSION_NONE) {
    // Create custom session directory if it doesn't exist
    $sessionPath = __DIR__ . '/../sessions';
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    
    // Configure session for cross-device and production access
    session_save_path($sessionPath);
    
    // Detect HTTPS for production environments
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    
    // Set session cookie parameters for cross-device and production access
    $sessionCookieParams = session_get_cookie_params();
    $sessionCookieParams['lifetime'] = 86400; // 24 hours
    $sessionCookieParams['path'] = '/LTS/';
    $sessionCookieParams['domain'] = ''; // Auto-detect domain
    $sessionCookieParams['secure'] = $isSecure; // Auto-detect HTTPS
    $sessionCookieParams['httponly'] = true; // Prevent JavaScript access
    $sessionCookieParams['samesite'] = 'Lax'; // Allow cross-site requests
    
    session_set_cookie_params(
        $sessionCookieParams['lifetime'],
        $sessionCookieParams['path'],
        $sessionCookieParams['domain'],
        $sessionCookieParams['secure'],
        $sessionCookieParams['httponly']
    );
    
    session_start();
}

// Environment detection and error reporting
$isProduction = (!in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) && 
                !str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.local') && 
                !str_ends_with($_SERVER['HTTP_HOST'] ?? '', '.dev'));

if ($isProduction) {
    // Production settings
    error_reporting(0); // Disable all errors in production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/error.log');
    
    // Create logs directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0777, true);
    }
} else {
    // Development settings
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Application settings
define('APP_NAME', 'Inventory Management System');
define('APP_VERSION', '1.0.0');

// Dynamic BASE_URL for localhost, IP access, and production hosting
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
            (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/LTS');

define('APP_PATH', __DIR__ . '/..');

// Security settings
define('HASH_COST', 10);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Pagination settings
define('ITEMS_PER_PAGE', 20);

// Date/time settings
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y');
define('DISPLAY_DATETIME_FORMAT', 'd/m/Y H:i:s');

// Auto-detect system timezone
if (!ini_get('date.timezone')) {
    // Try to detect system timezone
    $systemTimezone = date_default_timezone_get();
    if ($systemTimezone === 'UTC') {
        // If system timezone is UTC, try to detect from system
        $timezone = exec('timedatectl status 2>/dev/null | grep "Time zone" | cut -d: -f2 | xargs');
        if (empty($timezone)) {
            $timezone = exec('cat /etc/timezone 2>/dev/null');
        }
        if (empty($timezone)) {
            $timezone = 'UTC'; // Fallback
        }
        date_default_timezone_set($timezone);
    }
}

// Currency settings
define('CURRENCY', '$');
define('DECIMAL_PLACES', 2);

// File upload settings
define('MAX_FILE_SIZE', 2097152); // 2MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Logo settings
define('LOGO_PATH', '/assets/uploads/logo/');
define('LOGO_MAX_SIZE', 5242880); // 5MB for logo
define('LOGO_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Auto-include helper functions
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get system timezone setting from database or use detected
function getSystemTimezone() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'timezone'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result && !empty($result['setting_value'])) {
            return $result['setting_value'];
        }
    } catch (Exception $e) {
        // Fallback to system timezone
    }
    return date_default_timezone_get();
}

// Set system timezone from settings
$systemTimezone = getSystemTimezone();
if ($systemTimezone) {
    date_default_timezone_set($systemTimezone);
}
?>
