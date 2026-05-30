<?php
/**
 * Clear Session Test
 * This script completely clears the session for testing
 */
session_start();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

echo "<h2>Session Cleared</h2>";
echo "<p>All session data has been destroyed.</p>";
echo "<p><a href='index.php'>Test Root URL</a> - Should go to login page</p>";
echo "<p><a href='auth/login.php'>Go to Login Page</a></p>";
?>
