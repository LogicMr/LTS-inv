<?php
/**
 * Debug Login Script
 * Access this file in browser to test login functionality
 */

// Include required files
require_once 'config/config.php';

echo "<h2>Login Debug Tool</h2>";

// Test 1: Check database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = getConnection();
    echo "<span style='color: green;'>✓ Database connection successful</span><br>";
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</span><br>";
}

// Test 2: Check if admin user exists
echo "<h3>2. Admin User Check</h3>";
$sql = "SELECT * FROM users WHERE username = 'admin'";
$admin = fetchRow($sql);
if ($admin) {
    echo "<span style='color: green;'>✓ Admin user found</span><br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Full Name: " . $admin['full_name'] . "<br>";
    echo "Role ID: " . $admin['role_id'] . "<br>";
    echo "Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "<br>";
    echo "Password Hash: " . substr($admin['password'], 0, 20) . "...<br>";
} else {
    echo "<span style='color: red;'>✗ Admin user not found</span><br>";
}

// Test 3: Password verification
echo "<h3>3. Password Verification Test</h3>";
$testPasswords = ['admin123', 'admin', 'password'];
foreach ($testPasswords as $pwd) {
    if ($admin && password_verify($pwd, $admin['password'])) {
        echo "<span style='color: green;'>✓ Password '$pwd' - VERIFICATION SUCCESS</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Password '$pwd' - VERIFICATION FAILED</span><br>";
    }
}

// Test 4: Create new hash for admin123
echo "<h3>4. Generate New Hash</h3>";
$newHash = password_hash('admin123', PASSWORD_DEFAULT, ['cost' => 10]);
echo "New hash for 'admin123': " . $newHash . "<br>";
if (password_verify('admin123', $newHash)) {
    echo "<span style='color: green;'>✓ New hash verification successful</span><br>";
} else {
    echo "<span style='color: red;'>✗ New hash verification failed</span><br>";
}

// Test 5: SQL Update statement
echo "<h3>5. SQL Update Statement</h3>";
echo "Run this in phpMyAdmin to fix the password:<br>";
echo "<code style='background: #f0f0f0; padding: 5px;'>";
echo "UPDATE users SET password = '$newHash' WHERE username = 'admin';";
echo "</code><br>";

echo "<hr>";
echo "<h3>Quick Fix Options:</h3>";
echo "1. Run the SQL statement above in phpMyAdmin<br>";
echo "2. Re-import the database.sql file (it has been updated)<br>";
echo "3. Use the fix_admin_password_final.sql file<br>";

echo "<hr>";
echo "<a href='auth/login.php'>Go to Login Page</a>";
?>
