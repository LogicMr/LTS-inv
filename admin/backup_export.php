<?php
/**
 * Database Backup Export
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

if ($action === 'export' || $action === 'quick_backup') {
    try {
        // Get all table names
        $tables = [];
        $result = $pdo->query("SHOW TABLES");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        $backup = '';
        $backup .= "-- Inventory Management System Database Backup\n";
        $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "-- Database: " . DB_NAME . "\n";
        $backup .= "-- Compatible with MySQL 5.7+\n\n";

        // Add database creation and selection
        $backup .= "-- Create database\n";
        $backup .= "CREATE DATABASE IF NOT EXISTS " . DB_NAME . ";\n";
        $backup .= "USE " . DB_NAME . ";\n\n";
        
        foreach ($tables as $table) {
            // Get table structure
            $result = $pdo->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch(PDO::FETCH_NUM);
            $backup .= "-- Table structure for `$table`\n";
            
            // Add IF NOT EXISTS to CREATE TABLE statement
            $createTable = $row[1];
            $createTable = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $createTable);
            $backup .= $createTable . ";\n\n";
            
            // Get table data
            $result = $pdo->query("SELECT * FROM `$table`");
            $columnCount = $result->columnCount();
            
            if ($result->rowCount() > 0) {
                $backup .= "-- Data for `$table`\n";
                $backup .= "INSERT INTO `$table` VALUES\n";
                
                $values = [];
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $valueList = [];
                    foreach ($row as $key => $value) {
                        if ($value === null) {
                            $valueList[] = 'NULL';
                        } else {
                            $valueList[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $valueList) . ')';
                }
                
                $backup .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $backup .= "-- End of backup\n";
        
        if ($action === 'export') {
            // Download backup file
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($backup));
            echo $backup;
            exit;
        } else {
            // Save quick backup to server
            $backupDir = __DIR__ . '/../backups';
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $quickBackupFile = $backupDir . '/quick_backup.sql';
            if (file_put_contents($quickBackupFile, $backup)) {
                $_SESSION['success'] = 'Quick backup created successfully!';
            } else {
                $_SESSION['error'] = 'Failed to create quick backup!';
            }
            
            header('Location: ' . BASE_URL . '/admin/dashboard.php');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = 'Backup failed: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'Invalid action!';
    header('Location: ' . BASE_URL . '/admin/dashboard.php');
    exit;
}
?>
