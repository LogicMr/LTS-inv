<?php
/**
 * Fix Activity Logs Table
 * This script creates the missing activity_logs table
 */
require_once 'config/config.php';

echo "<h2>Fix Activity Logs Table</h2>";

try {
    // Check if table exists
    $checkSql = "SHOW TABLES LIKE 'activity_logs'";
    $tableExists = fetchRow($checkSql);
    
    if (!$tableExists) {
        echo "<p style='color: orange;'>⚠ Activity logs table not found. Creating table...</p>";
        
        // Create the table
        $createSql = "CREATE TABLE activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        executeNonQuery($createSql);
        
        echo "<p style='color: green;'>✓ Activity logs table created successfully!</p>";
        
        // Test the table
        $testSql = "SHOW TABLES LIKE 'activity_logs'";
        $testResult = fetchRow($testSql);
        
        if ($testResult) {
            echo "<p style='color: green;'>✓ Table verification: SUCCESS</p>";
            
            // Test logActivity function
            if (isLoggedIn()) {
                logActivity('System Test', 'Activity logs table creation test');
                echo "<p style='color: green;'>✓ logActivity function test: SUCCESS</p>";
            } else {
                echo "<p style='color: blue;'>ℹ logActivity function test: Skipped (not logged in)</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Table verification: FAILED</p>";
        }
        
    } else {
        echo "<p style='color: green;'>✓ Activity logs table already exists</p>";
    }
    
    echo "<hr>";
    echo "<h3>Next Steps:</h3>";
    echo "<p><a href='index.php'>Test Root URL</a></p>";
    echo "<p><a href='auth/login.php'>Go to Login</a></p>";
    echo "<p><a href='clear_session.php'>Clear Session</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
