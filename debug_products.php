<?php
/**
 * Debug Product Operations
 * This script helps debug product edit and delete issues
 */
require_once 'config/config.php';
require_once 'includes/currency.php';

echo "<h2>Debug Product Operations</h2>";

// Check if required functions exist
echo "<h3>Function Check:</h3>";
$functions = [
    'validateRequired' => function_exists('validateRequired'),
    'fetchAll' => function_exists('fetchAll'),
    'fetchRow' => function_exists('fetchRow'),
    'executeNonQuery' => function_exists('executeNonQuery'),
    'getCurrencies' => function_exists('getCurrencies'),
    'formatCurrency' => function_exists('formatCurrency')
];

foreach ($functions as $func => $exists) {
    echo "<p>$func: " . ($exists ? '✅' : '❌') . "</p>";
}

// Check database tables
echo "<h3>Database Tables Check:</h3>";
$tables = ['products', 'categories', 'suppliers', 'currencies'];
foreach ($tables as $table) {
    try {
        $result = fetchRow("SELECT COUNT(*) as count FROM $table");
        echo "<p>$table: ✅ ({$result['count']} records)</p>";
    } catch (Exception $e) {
        echo "<p>$table: ❌ (Error: " . $e->getMessage() . ")</p>";
    }
}

// Test product data
echo "<h3>Product Data Test:</h3>";
$products = fetchAll("SELECT p.*, cur.code as currency_code FROM products p LEFT JOIN currencies cur ON p.currency_id = cur.id LIMIT 3");

if (count($products) > 0) {
    foreach ($products as $product) {
        echo "<h4>Product: " . htmlspecialchars($product['name']) . "</h4>";
        echo "<ul>";
        echo "<li>ID: {$product['id']}</li>";
        echo "<li>Cost Price: " . formatCurrency($product['cost_price'], $product['currency_id']) . "</li>";
        echo "<li>Selling Price: " . formatCurrency($product['selling_price'], $product['currency_id']) . "</li>";
        echo "<li>Currency: {$product['currency_code']}</li>";
        echo "<li>Stock: {$product['quantity_in_stock']}</li>";
        echo "</ul>";
        
        // Test delete logic
        $hasTransactions = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$product['id']])['count'] > 0 ||
                           fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$product['id']])['count'] > 0;
        echo "<li>Has Transactions: " . ($hasTransactions ? 'Yes (will deactivate)' : 'No (can delete)') . "</li>";
        echo "</ul>";
    }
} else {
    echo "<p>No products found</p>";
}

// Test form submission simulation
echo "<h3>Form Submission Test:</h3>";
echo "<p>Simulating DELETE request for product ID 1:</p>";

$testProductId = 1;
$testProduct = fetchRow("SELECT * FROM products WHERE id = ?", [$testProductId]);

if ($testProduct) {
    echo "<p>Found product: " . htmlspecialchars($testProduct['name']) . "</p>";
    
    // Simulate the delete logic
    $hasTransactions = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$testProductId])['count'] > 0 ||
                       fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$testProductId])['count'] > 0;
    
    echo "<p>Has transactions: " . ($hasTransactions ? 'Yes' : 'No') . "</p>";
    
    if ($hasTransactions) {
        echo "<p>Would deactivate (UPDATE products SET is_active = 0)</p>";
    } else {
        echo "<p>Would delete (DELETE FROM products)</p>";
    }
} else {
    echo "<p>Product ID 1 not found</p>";
}

// Check for JavaScript errors
echo "<h3>JavaScript Debug Info:</h3>";
echo "<p>Check browser console (F12) for these errors:</p>";
echo "<ul>";
echo "<li>editProduct function errors</li>";
echo "<li>deleteProduct function errors</li>";
echo "<li>Bootstrap modal errors</li>";
echo "<li>AJAX request failures</li>";
echo "</ul>";

echo "<h3>Manual Test Links:</h3>";
echo "<ul>";
echo "<li><a href='manager/get_product.php?id=1' target='_blank'>Test get_product.php?id=1</a></li>";
echo "<li><a href='manager/products.php' target='_blank'>Products Page</a></li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Troubleshooting Steps:</h3>";
echo "<ol>";
echo "<li>Open Products page</li>";
echo "<li>Open browser console (F12)</li>";
echo "<li>Click Edit button - check for errors</li>";
echo "<li>Click Delete button - check for errors</li>";
echo "<li>Check Network tab for AJAX requests</li>";
echo "</ol>";
?>
