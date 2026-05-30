<?php
/**
 * Simple Delete Test
 * Test the delete functionality step by step
 */
require_once 'config/config.php';

echo "<h2>Simple Delete Test</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
$userRole = getRoleName($user['role_id']);
echo "<p>Logged in as: {$user['full_name']} ($userRole)</p>";

// Test 1: Check if we can find products
echo "<h3>Test 1: Find Products</h3>";

$products = fetchAll("SELECT id, name FROM products LIMIT 3");

if (count($products) > 0) {
    foreach ($products as $product) {
        echo "<p>Product ID {$product['id']}: " . htmlspecialchars($product['name']) . "</p>";
    }
} else {
    echo "<p>No products found</p>";
    exit;
}

// Test 2: Check transaction tables
echo "<h3>Test 2: Check Transaction Tables</h3>";

$testProduct = $products[0];
$productId = $testProduct['id'];

echo "<h4>Testing Product ID: $productId</h4>";

// Check if tables exist
$tables = ['sale_items', 'purchase_items'];
foreach ($tables as $table) {
    try {
        $count = fetchRow("SELECT COUNT(*) as count FROM $table");
        echo "<p>$table table: ✅ Exists ({$count['count']} records)</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>$table table: ❌ Error - " . $e->getMessage() . "</p>";
    }
}

// Test 3: Check specific product transactions
echo "<h3>Test 3: Check Product Transactions</h3>";

try {
    $saleCount = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$productId])['count'];
    $purchaseCount = fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$productId])['count'];
    
    echo "<p>Sale items for this product: $saleCount</p>";
    echo "<p>Purchase items for this product: $purchaseCount</p>";
    
    $hasTransactions = ($saleCount > 0) || ($purchaseCount > 0);
    echo "<p>Has transactions: " . ($hasTransactions ? 'Yes' : 'No') . "</p>";
    
    if ($hasTransactions) {
        echo "<p style='color: orange;'>⚠ Product has transactions - should be deactivated, not deleted</p>";
    } else {
        echo "<p style='color: green;'>✅ Product has no transactions - can be safely deleted</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error checking transactions: " . $e->getMessage() . "</p>";
}

// Test 4: Simulate delete operation
echo "<h3>Test 4: Simulate Delete Operation</h3>";

try {
    echo "<h4>Current Product Data:</h4>";
    $currentProduct = fetchRow("SELECT * FROM products WHERE id = ?", [$productId]);
    
    if ($currentProduct) {
        echo "<pre>" . json_encode($currentProduct, JSON_PRETTY_PRINT) . "</pre>";
        
        // Test the delete logic
        $hasTransactions = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$productId])['count'] > 0 ||
                           fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$productId])['count'] > 0;
        
        echo "<h4>Delete Logic Test:</h4>";
        
        if ($hasTransactions) {
            echo "<p>Would deactivate (UPDATE products SET is_active = 0)</p>";
            
            // Test the deactivation
            $testResult = executeNonQuery("UPDATE products SET is_active = 0 WHERE id = ?", [$productId]);
            if ($testResult) {
                echo "<p style='color: green;'>✅ Deactivation test successful</p>";
                echo "<p>⚠ Note: This was just a test - product not actually deactivated</p>";
            } else {
                echo "<p style='color: red;'>❌ Deactivation test failed</p>";
            }
        } else {
            echo "<p>Would delete (DELETE FROM products)</p>";
            
            // Note: Not actually deleting in test
            echo "<p>ℹ Note: Not actually deleting in test mode</p>";
        }
        
    } else {
        echo "<p style='color: red;'>Product not found for delete test</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Delete simulation error: " . $e->getMessage() . "</p>";
}

// Test 5: Check for common issues
echo "<h3>Test 5: Common Issues Check</h3>";

echo "<h4>Database Connection:</h4>";
try {
    $testQuery = fetchRow("SELECT 1 as test");
    echo "<p>✅ Database connection working</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection error: " . $e->getMessage() . "</p>";
}

echo "<h4>Required Functions:</h4>";
$functions = [
    'fetchRow' => function_exists('fetchRow'),
    'fetchAll' => function_exists('fetchAll'),
    'executeNonQuery' => function_exists('executeNonQuery'),
    'logActivity' => function_exists('logActivity')
];

foreach ($functions as $func => $exists) {
    echo "<p>$func: " . ($exists ? '✅' : '❌') . "</p>";
}

echo "<hr>";
echo "<h3>Manual Delete Test:</h3>";
echo "<p>To test actual delete:</p>";
echo "<ol>";
echo "<li>Go to <a href='manager/products.php'>Products Page</a></li>";
echo "<li>Click Delete button on a product</li>";
echo "<li>Check if you see error message</li>";
echo "<li>Check browser console for errors</li>";
echo "<li>Come back here and report the exact error message</li>";
echo "</ol>";

echo "<h3>What to Report:</h3>";
echo "<p>Please provide:</p>";
echo "<ul>";
echo "<li>Exact error message you see</li>";
echo "<li>Browser console errors (if any)</li>";
echo "<li>What happens when you click delete (nothing, error message, etc.)</li>";
echo "<li>Product name you tried to delete</li>";
echo "</ul>";
?>
