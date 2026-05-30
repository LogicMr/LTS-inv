<?php
/**
 * Test Navigation
 * This script helps verify the navigation and logout functionality
 */
require_once 'config/config.php';

echo "<h2>Navigation Test</h2>";

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>Logged in as: <strong>" . $user['full_name'] . "</strong> (" . getRoleName($user['role_id']) . ")</p>";
    
    echo "<h3>Logout Options:</h3>";
    echo "<ul>";
    echo "<li><a href='" . BASE_URL . "/auth/logout.php'>Main Logout Link</a></li>";
    echo "<li><a href='auth/logout.php'>Relative Logout Link</a></li>";
    echo "</ul>";
    
    echo "<h3>Dashboard Navigation:</h3>";
    echo "<ul>";
    echo "<li><a href='" . BASE_URL . "/admin/dashboard.php'>Admin Dashboard</a></li>";
    echo "<li><a href='" . BASE_URL . "/manager/dashboard.php'>Manager Dashboard</a></li>";
    echo "<li><a href='" . BASE_URL . "/cashier/dashboard.php'>Cashier Dashboard</a></li>";
    echo "</ul>";
    
    echo "<h3>Your Dashboard:</h3>";
    $dashboard = BASE_URL . '/' . getUserDashboard();
    echo "<p><a href='$dashboard'>$dashboard</a></p>";
    
} else {
    echo "<p>Not logged in</p>";
    echo "<p><a href='auth/login.php'>Login</a></p>";
}

echo "<hr>";
echo "<h3>Header Navigation Check:</h3>";
echo "<p>The header should include:</p>";
echo "<ul>";
echo "<li>Navigation menu based on role</li>";
echo "<li>User dropdown with Profile, Change Password, Logout</li>";
echo "<li>Bootstrap Icons support</li>";
echo "</ul>";

echo "<p><a href='index.php'>Test Root URL</a></p>";
echo "<p><a href='clear_session.php'>Clear Session</a></p>";
?>
