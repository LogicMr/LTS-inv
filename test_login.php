<?php
/**
 * Test Login Script
 * This will help debug the login issue
 */

echo "<h2>Login Test</h2>";

// Test password verification with known hash
$testHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$password = 'admin123';

echo "Testing password: " . $password . "<br>";
echo "Against hash: " . $testHash . "<br>";

if (password_verify($password, $testHash)) {
    echo "<span style='color: green;'>VERIFICATION SUCCESS</span><br>";
} else {
    echo "<span style='color: red;'>VERIFICATION FAILED</span><br>";
}

// Generate new hash
$newHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
echo "<br>New hash for 'admin123': " . $newHash . "<br>";

if (password_verify($password, $newHash)) {
    echo "<span style='color: green;'>NEW HASH VERIFICATION SUCCESS</span><br>";
} else {
    echo "<span style='color: red;'>NEW HASH VERIFICATION FAILED</span><br>";
}

// Test different passwords
$testPasswords = ['admin', 'admin123', 'password', '123456'];
echo "<br>Testing multiple passwords against original hash:<br>";

foreach ($testPasswords as $pwd) {
    if (password_verify($pwd, $testHash)) {
        echo "<span style='color: green;'>" . $pwd . " - SUCCESS</span><br>";
    } else {
        echo "<span style='color: red;'>" . $pwd . " - FAILED</span><br>";
    }
}
?>
