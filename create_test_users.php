<?php
/**
 * Create Test Users
 * This script creates test users for different roles
 */
require_once 'config/config.php';

echo "<h2>Create Test Users</h2>";

$testUsers = [
    [
        'username' => 'manager',
        'password' => 'manager123',
        'full_name' => 'Test Manager',
        'email' => 'manager@test.com',
        'role_name' => 'Manager'
    ],
    [
        'username' => 'cashier',
        'password' => 'cashier123',
        'full_name' => 'Test Cashier',
        'email' => 'cashier@test.com',
        'role_name' => 'Cashier'
    ]
];

foreach ($testUsers as $userData) {
    // Check if user exists
    $existing = fetchRow("SELECT id FROM users WHERE username = ?", [$userData['username']]);
    
    if (!$existing) {
        // Get role ID
        $role = fetchRow("SELECT id FROM roles WHERE name = ?", [$userData['role_name']]);
        
        if ($role) {
            // Create user
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            
            $sql = "INSERT INTO users (username, password, full_name, email, role_id) 
                    VALUES (?, ?, ?, ?, ?)";
            
            executeNonQuery($sql, [
                $userData['username'],
                $hashedPassword,
                $userData['full_name'],
                $userData['email'],
                $role['id']
            ]);
            
            echo "<p style='color: green;'>✓ Created user: {$userData['username']} ({$userData['role_name']})</p>";
            echo "<p>Password: {$userData['password']}</p>";
        } else {
            echo "<p style='color: red;'>✗ Role '{$userData['role_name']}' not found</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ User '{$userData['username']}' already exists</p>";
    }
}

echo "<hr>";
echo "<h3>Test Login Credentials:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Username</th><th>Password</th><th>Role</th><th>Expected Dashboard</th></tr>";

foreach ($testUsers as $userData) {
    $dashboardPath = '';
    switch ($userData['role_name']) {
        case 'Admin':
            $dashboardPath = 'admin/dashboard.php';
            break;
        case 'Manager':
            $dashboardPath = 'manager/dashboard.php';
            break;
        case 'Cashier':
            $dashboardPath = 'cashier/dashboard.php';
            break;
    }
    
    echo "<tr>";
    echo "<td>{$userData['username']}</td>";
    echo "<td>{$userData['password']}</td>";
    echo "<td>{$userData['role_name']}</td>";
    echo "<td>$dashboardPath</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><a href='index.php'>Test Root URL Routing</a></p>";
echo "<p><a href='auth/login.php'>Go to Login</a></p>";
echo "<p><a href='test_routing.php'>Test Routing Debug</a></p>";
?>
