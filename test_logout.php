<?php
/**
 * Test Logout Functionality
 * This script tests the logout redirect paths
 */
require_once 'config/config.php';

echo "<h2>Logout Test</h2>";

echo "<h3>Current Configuration:</h3>";
echo "<p>BASE_URL: " . BASE_URL . "</p>";

echo "<h3>Expected Logout Behavior:</h3>";
echo "<ul>";
echo "<li>When logout() is called, should redirect to: " . BASE_URL . "/auth/login.php</li>";
echo "<li>When requireAuth() redirects, should go to: " . BASE_URL . "/auth/login.php</li>";
echo "<li>When unauthorized, should go to: " . BASE_URL . "/auth/unauthorized.php</li>";
echo "</ul>";

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>Currently logged in as: " . $user['full_name'] . " (" . getRoleName($user['role_id']) . ")</p>";
    
    echo "<h3>Test Logout:</h3>";
    echo "<p><a href='" . BASE_URL . "/auth/logout.php' class='btn btn-danger'>Test Logout</a></p>";
    echo "<p>This should redirect to: " . BASE_URL . "/auth/login.php</p>";
    
} else {
    echo "<p>Not logged in</p>";
    echo "<p><a href='auth/login.php'>Login to test logout</a></p>";
}

echo "<hr>";
echo "<h3>Direct Links Test:</h3>";
echo "<ul>";
echo "<li><a href='" . BASE_URL . "/auth/login.php'>Login Page</a></li>";
echo "<li><a href='" . BASE_URL . "/auth/logout.php'>Logout Direct</a></li>";
echo "<li><a href='" . BASE_URL . "/auth/unauthorized.php'>Unauthorized Page</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Debug Information:</h3>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Script Path: " . __FILE__ . "</p>";
echo "<p>Working Directory: " . getcwd() . "</p>";
?>
