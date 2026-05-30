<?php
/**
 * Database Backup Import
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

$action = $_POST['action'] ?? '';

if ($action === 'import') {
    try {
        // Check if file was uploaded
        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Please select a backup file to import.');
        }
        
        $file = $_FILES['backup_file'];
        $filename = $file['tmp_name'];
        
        // Validate file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'sql') {
            throw new Exception('Only SQL backup files are allowed.');
        }
        
        // Check file size (max 50MB)
        if ($file['size'] > 50 * 1024 * 1024) {
            throw new Exception('Backup file is too large. Maximum size is 50MB.');
        }
        
        // Read backup file
        $backupContent = file_get_contents($filename);
        if ($backupContent === false) {
            throw new Exception('Failed to read backup file.');
        }
        
        // Basic validation
        if (strlen($backupContent) < 100) {
            throw new Exception('Backup file appears to be empty or invalid.');
        }
        
        // Disable foreign key checks temporarily
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Split SQL statements
            $statements = array_filter(array_map('trim', explode(';', $backupContent)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                    } catch (PDOException $e) {
                        // Log error but continue with other statements
                        error_log("Import error: " . $e->getMessage());
                    }
                }
            }
            
            // Commit transaction
            $pdo->commit();
            
            // Re-enable foreign key checks
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            
            $_SESSION['success'] = 'Database backup imported successfully!';
            
        } catch (Exception $e) {
            // Rollback on error
            $pdo->rollBack();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            throw $e;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Import failed: ' . $e->getMessage();
    }
    
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
} else {
    $_SESSION['error'] = 'Invalid action!';
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}
?>
