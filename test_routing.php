<?php
/**
 * Test Routing
 * This page helps debug the routing logic
 */
require_once 'config/config.php';

echo "<h2>Routing Test</h2>";

// Test if user is logged in
echo "<p>Logged in: " . (isLoggedIn() ? "Yes" : "No") . "</p>";

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo "<p>User ID: " . $user['id'] . "</p>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Role ID: " . $user['role_id'] . "</p>";
    
    $roleName = getRoleName($user['role_id']);
    echo "<p>Role Name: " . $roleName . "</p>";
    
    $dashboard = getUserDashboard();
    echo "<p>Dashboard URL: " . $dashboard . "</p>";
    
    echo "<br><h3>Navigation:</h3>";
    echo "<a href='$dashboard'>Go to Dashboard</a><br>";
    echo "<a href='auth/login.php'>Login Page</a><br>";
    echo "<a href='auth/logout.php'>Logout</a><br>";
} else {
    echo "<p><a href='auth/login.php'>Please Login</a></p>";
}

// Test role names
echo "<br><h3>All Roles in Database:</h3>";
$roles = fetchAll("SELECT * FROM roles");
foreach ($roles as $role) {
    echo "ID: " . $role['id'] . " - Name: " . $role['name'] . "<br>";
}
?>
