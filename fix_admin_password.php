<?php
/**
 * Fix Admin Password Properly
 * This script will update the admin password with correct hash
 */

require_once 'config/config.php';

try {
    // Generate correct hash for admin123
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    
    echo "Generated hash: " . $hash . "<br>";
    
    // Update database
    $sql = "UPDATE users SET password = ? WHERE username = 'admin'";
    $result = executeNonQuery($sql, [$hash]);
    
    if ($result) {
        echo "<span style='color: green;'>✓ Admin password updated successfully!</span><br>";
        
        // Verify the update
        $verifySql = "SELECT password FROM users WHERE username = 'admin'";
        $user = fetchRow($verifySql);
        
        if ($user && password_verify($password, $user['password'])) {
            echo "<span style='color: green;'>✓ Password verification: SUCCESS</span><br>";
        } else {
            echo "<span style='color: red;'>✗ Password verification: FAILED</span><br>";
        }
    } else {
        echo "<span style='color: red;'>✗ Failed to update password</span><br>";
    }
    
    echo "<br><a href='auth/login.php'>Test Login</a> | <a href='temp_login.php'>Temporary Login</a>";
    
} catch (Exception $e) {
    echo "<span style='color: red;'>Error: " . $e->getMessage() . "</span><br>";
}
?>
