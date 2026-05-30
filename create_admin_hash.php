<?php
/**
 * Create Correct Admin Hash
 */

// The password we want
$password = 'admin123';

// Create hash
$hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

// Output for manual update
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';";

// Also create a complete SQL file
$sql = "-- Update admin password to 'admin123'\n";
$sql .= "UPDATE users SET password = '$hash' WHERE username = 'admin';\n";
$sql .= "\n-- Verify update\n";
$sql .= "SELECT username, full_name FROM users WHERE username = 'admin';\n";

file_put_contents('fix_admin_password.sql', $sql);
echo "\n\nSQL file 'fix_admin_password.sql' created successfully!";
?>
