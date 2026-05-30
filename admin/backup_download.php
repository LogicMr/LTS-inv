<?php
/**
 * Secure Backup Download
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireAuth();
requireRole('Admin');

// Ensure database connection is available
if (!isset($pdo) || $pdo === null) {
    // Try to initialize database connection
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$filename = $_GET['file'] ?? '';

// Security checks
if (empty($filename)) {
    http_response_code(400);
    exit('Invalid request');
}

// Only allow .sql files
if (!preg_match('/^[a-zA-Z0-9_-]+\.sql$/', $filename)) {
    http_response_code(400);
    exit('Invalid filename');
}

$backupDir = __DIR__ . '/../backups';
$filepath = $backupDir . '/' . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    http_response_code(404);
    exit('File not found');
}

// Check if it's actually in the backup directory
$realPath = realpath($filepath);
$backupDirPath = realpath($backupDir);
if (strpos($realPath, $backupDirPath) !== 0) {
    http_response_code(403);
    exit('Access denied');
}

// Download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

readfile($filepath);
exit;
?>
