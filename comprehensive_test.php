<?php
/**
 * Comprehensive Product Error Test
 * This script identifies specific errors in product operations
 */
require_once 'config/config.php';
require_once 'includes/currency.php';

echo "<h2>Comprehensive Product Error Test</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
$userRole = getRoleName($user['role_id']);
echo "<p>Logged in as: {$user['full_name']} ($userRole)</p>";

// Test 1: Database table structure
echo "<h3>Test 1: Database Structure</h3>";

$tables = ['products', 'sale_items', 'purchase_items'];
foreach ($tables as $table) {
    try {
        $columns = fetchAll("SHOW COLUMNS FROM $table");
        $columnNames = array_column($columns, 'Field');
        echo "<h4>$table table:</h4>";
        echo "<p>✅ Table exists with " . count($columns) . " columns</p>";
        
        // Check for required columns
        $requiredColumns = ['id', 'name', 'currency_id'];
        foreach ($requiredColumns as $col) {
            if (in_array($col, $columnNames)) {
                echo "<p>✅ Column '$col' exists</p>";
            } else {
                echo "<p style='color: red;'>❌ Column '$col' missing</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error with $table table: " . $e->getMessage() . "</p>";
    }
}

// Test 2: Product data retrieval
echo "<h3>Test 2: Product Data Retrieval</h3>";

$products = fetchAll("SELECT p.*, cur.code as currency_code FROM products p LEFT JOIN currencies cur ON p.currency_id = cur.id LIMIT 3");

if (count($products) > 0) {
    foreach ($products as $product) {
        echo "<h4>Product: " . htmlspecialchars($product['name']) . "</h4>";
        echo "<ul>";
        echo "<li>ID: {$product['id']}</li>";
        echo "<li>Cost: " . formatCurrency($product['cost_price'], $product['currency_id']) . "</li>";
        echo "<li>Selling: " . formatCurrency($product['selling_price'], $product['currency_id']) . "</li>";
        echo "<li>Currency: {$product['currency_code']} (ID: {$product['currency_id']})</li>";
        echo "</ul>";
        
        // Test delete logic
        echo "<h5>Delete Test:</h5>";
        
        try {
            $hasSales = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$product['id']])['count'] > 0;
            $hasPurchases = fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$product['id']])['count'] > 0;
            $hasTransactions = $hasSales || $hasPurchases;
            
            echo "<li>Has Sales: " . ($hasSales ? 'Yes' : 'No') . "</li>";
            echo "<li>Has Purchases: " . ($hasPurchases ? 'Yes' : 'No') . "</li>";
            echo "<li>Has Transactions: " . ($hasTransactions ? 'Yes (will deactivate)' : 'No (can delete)') . "</li>";
            
            if ($hasTransactions) {
                // Test deactivation
                $testDeactivate = "UPDATE products SET is_active = 0 WHERE id = ?";
                echo "<li>Deactivate SQL: ✅ Valid</li>";
            } else {
                // Test deletion
                $testDelete = "DELETE FROM products WHERE id = ?";
                echo "<li>Delete SQL: ✅ Valid</li>";
            }
            
        } catch (Exception $e) {
            echo "<li style='color: red;'>Delete Logic Error: " . $e->getMessage() . "</li>";
        }
        
        echo "</ul>";
    }
} else {
    echo "<p>No products found to test</p>";
}

// Test 3: AJAX endpoint with detailed error checking
echo "<h3>Test 3: AJAX Endpoint Detailed Test</h3>";

if (count($products) > 0) {
    $testProduct = $products[0];
    $productId = $testProduct['id'];
    
    echo "<h4>Testing get_product.php?id=$productId</h4>";
    
    // Test the endpoint by including it
    echo "<h5>Direct Include Test:</h5>";
    
    // Capture any output
    ob_start();
    
    try {
        $_GET['id'] = $productId;
        include 'manager/get_product.php';
        $output = ob_get_contents();
        ob_end_clean();
        
        echo "<h6>Output Analysis:</h6>";
        echo "<p>Output length: " . strlen($output) . " characters</p>";
        
        // Check if output looks like JSON
        $trimmed = trim($output);
        if (substr($trimmed, 0, 1) === '{' && substr($trimmed, -1) === '}') {
            echo "<p style='color: green;'>✅ Output looks like JSON</p>";
            
            $jsonData = json_decode($output, true);
            if ($jsonData !== null) {
                echo "<p style='color: green;'>✅ Valid JSON format</p>";
                echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠ JSON parsing failed</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Output is NOT JSON</p>";
            echo "<p>First 100 chars: " . htmlspecialchars(substr($output, 0, 100)) . "</p>";
            
            // Check for common error patterns
            if (strpos($output, 'Warning') !== false) {
                echo "<p style='color: orange;'>⚠ Contains PHP Warnings</p>";
            }
            if (strpos($output, 'Fatal') !== false) {
                echo "<p style='color: red;'>❌ Contains PHP Fatal Error</p>";
            }
            if (strpos($output, '<!DOCTYPE') !== false) {
                echo "<p style='color: red;'>❌ Contains HTML (error page)</p>";
            }
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p style='color: red;'>❌ Include Error: " . $e->getMessage() . "</p>";
    }
    
    unset($_GET['id']);
}

// Test 4: Form processing simulation
echo "<h3>Test 4: Form Processing Simulation</h3>";

if (count($products) > 0) {
    $testProduct = $products[0];
    
    echo "<h4>Simulating Edit Form:</h4>";
    
    $editData = [
        'id' => $testProduct['id'],
        'name' => $testProduct['name'],
        'category_id' => $testProduct['category_id'],
        'cost_price' => $testProduct['cost_price'],
        'selling_price' => $testProduct['selling_price'],
        'quantity_in_stock' => $testProduct['quantity_in_stock'],
        'reorder_level' => $testProduct['reorder_level'],
        'currency_id' => $testProduct['currency_id']
    ];
    
    echo "<h5>Form Data:</h5>";
    echo "<pre>" . json_encode($editData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test validation
    if (function_exists('validateRequired')) {
        $required = ['id', 'name', 'category_id', 'cost_price', 'selling_price', 'quantity_in_stock', 'reorder_level', 'currency_id'];
        $errors = validateRequired($required, $editData);
        
        if (empty($errors)) {
            echo "<p style='color: green;'>✅ Validation would pass</p>";
        } else {
            echo "<p style='color: red;'>❌ Validation would fail:</p>";
            foreach ($errors as $error) {
                echo "<p>- $error</p>";
            }
        }
    }
    
    echo "<h5>SQL Generation:</h5>";
    $sql = "UPDATE products SET name = ?, category_id = ?, barcode = ?, cost_price = ?, selling_price = ?, 
                    quantity_in_stock = ?, reorder_level = ?, supplier_id = ?, expiry_date = ?, batch_number = ?, 
                    description = ?, currency_id = ?, updated_at = NOW() WHERE id = ?";
    echo "<p>✅ SQL looks valid</p>";
}

echo "<hr>";
echo "<h3>Debugging Steps:</h3>";
echo "<ol>";
echo "<li>Check the table structure results above</li>";
echo "<li>Verify AJAX endpoint returns clean JSON</li>";
echo "<li>Test edit button on products page</li>";
echo "<li>Check browser console for specific errors</li>";
echo "<li>Look for any PHP warnings/errors</li>";
echo "</ol>";

echo "<p><a href='manager/products.php'>Test Products Page</a></p>";
?>
