<?php
/**
 * Test Product Edit/Delete Functionality
 * This script tests the product management buttons
 */
require_once 'config/config.php';
require_once 'includes/currency.php';

echo "<h2>Test Product Edit/Delete</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
echo "<p>Logged in as: " . $user['full_name'] . " (" . getRoleName($user['role_id']) . ")</p>";

if (getRoleName($user['role_id']) !== 'Manager' && getRoleName($user['role_id']) !== 'Admin') {
    echo "<p>You need Manager or Admin role to manage products</p>";
    exit;
}

// Test get_product.php endpoint
echo "<h3>Testing get_product.php Endpoint:</h3>";
$products = fetchAll("SELECT id, name FROM products LIMIT 3");

if (count($products) > 0) {
    $testProduct = $products[0];
    echo "<p>Testing product ID: {$testProduct['id']} ({$testProduct['name']})</p>";
    
    // Simulate the AJAX call
    $productData = fetchRow("SELECT * FROM products WHERE id = ?", [$testProduct['id']]);
    
    if ($productData) {
        echo "<p style='color: green;'>✓ Product data found:</p>";
        echo "<ul>";
        echo "<li>Name: " . htmlspecialchars($productData['name']) . "</li>";
        echo "<li>Cost Price: " . formatCurrency($productData['cost_price'], $productData['currency_id']) . "</li>";
        echo "<li>Selling Price: " . formatCurrency($productData['selling_price'], $productData['currency_id']) . "</li>";
        echo "<li>Currency ID: {$productData['currency_id']}</li>";
        echo "<li>Stock: {$productData['quantity_in_stock']}</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Product data not found</p>";
    }
} else {
    echo "<p>No products found. <a href='manager/products.php'>Add some products first</a></p>";
}

// Test currency functions
echo "<h3>Testing Currency Functions:</h3>";
$currencies = getCurrencies();
echo "<p>Available currencies: " . count($currencies) . "</p>";

if (count($currencies) > 0) {
    echo "<p>Currency select options test:</p>";
    echo "<select>";
    foreach ($currencies as $currency) {
        $selected = $currency['is_default'] ? 'selected' : '';
        echo "<option value='{$currency['id']}' {$selected}>{$currency['code']} - {$currency['name']}" . ($currency['is_default'] ? ' (Default)' : '') . "</option>";
    }
    echo "</select>";
}

echo "<h3>Debug Information:</h3>";
echo "<ul>";
echo "<li><strong>get_product.php URL:</strong> " . BASE_URL . "/manager/get_product.php</li>";
echo "<li><strong>Products Page:</strong> <a href='manager/products.php'>manager/products.php</a></li>";
echo "<li><strong>JavaScript Functions:</strong> editProduct(), deleteProduct()</li>";
echo "<li><strong>Bootstrap Version:</strong> Should be 5.3.0+ for modal functionality</li>";
echo "</ul>";

echo "<h3>Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li>Open browser developer tools (F12)</li>";
echo "<li>Go to Console tab</li>";
echo "<li>Click Edit or Delete button on products page</li>";
echo "<li>Check for JavaScript errors in console</li>";
echo "<li>Check Network tab for AJAX requests</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='manager/products.php'>Go to Products Page</a></p>";
echo "<p><a href='test_currency.php'>Test Currency Functions</a></p>";
?>
