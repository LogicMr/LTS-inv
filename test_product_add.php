<?php
/**
 * Test Product Addition
 * This script tests the product addition functionality
 */
require_once 'config/config.php';

echo "<h2>Product Addition Test</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
echo "<p>Logged in as: " . $user['full_name'] . " (" . getRoleName($user['role_id']) . ")</p>";

if (getRoleName($user['role_id']) !== 'Manager' && getRoleName($user['role_id']) !== 'Admin') {
    echo "<p>You need Manager or Admin role to add products</p>";
    echo "<p><a href='clear_session.php'>Clear session and login as Manager</a></p>";
    exit;
}

echo "<h3>Test Product Addition:</h3>";
echo "<p><a href='manager/products.php'>Go to Products Management</a></p>";

echo "<h3>Required Data for Testing:</h3>";
echo "<ul>";
echo "<li>Categories: " . count(fetchAll("SELECT * FROM categories")) . " available</li>";
echo "<li>Suppliers: " . count(fetchAll("SELECT * FROM suppliers")) . " available</li>";
echo "</ul>";

if (count(fetchAll("SELECT * FROM categories")) === 0) {
    echo "<p style='color: orange;'>⚠ No categories found. <a href='admin/categories.php'>Add categories first</a></p>";
}

if (count(fetchAll("SELECT * FROM suppliers")) === 0) {
    echo "<p style='color: orange;'>⚠ No suppliers found. <a href='admin/suppliers.php'>Add suppliers first</a></p>";
}

echo "<h3>Test Product Data:</h3>";
echo "<form method='post' action='manager/products.php' target='_blank'>";
echo "<input type='hidden' name='action' value='add'>";
echo "<table border='1'>";
echo "<tr><td>Product Name:</td><td><input type='text' name='name' value='Test Product' required></td></tr>";
echo "<tr><td>Category:</td><td>";
echo "<select name='category_id'>";
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
foreach ($categories as $cat) {
    echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
}
echo "</select>";
echo "</td></tr>";
echo "<tr><td>Cost Price:</td><td><input type='number' name='cost_price' step='0.01' value='10.00' required></td></tr>";
echo "<tr><td>Selling Price:</td><td><input type='number' name='selling_price' step='0.01' value='15.00' required></td></tr>";
echo "<tr><td>Quantity:</td><td><input type='number' name='quantity_in_stock' value='100' required></td></tr>";
echo "<tr><td>Reorder Level:</td><td><input type='number' name='reorder_level' value='10' required></td></tr>";
echo "<tr><td>Barcode:</td><td><input type='text' name='barcode' value='TEST001'></td></tr>";
echo "<tr><td>Description:</td><td><textarea name='description'>Test product description</textarea></td></tr>";
echo "</table>";
echo "<button type='submit' class='btn btn-primary'>Add Product</button>";
echo "</form>";

echo "<hr>";
echo "<p><a href='test_logout.php'>Test Logout</a></p>";
echo "<p><a href='clear_session.php'>Clear Session</a></p>";
?>
