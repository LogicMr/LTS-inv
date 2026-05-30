<?php
/**
 * Test Fixed AJAX and JavaScript
 * This script tests the fixed AJAX endpoint and JavaScript issues
 */
require_once 'config/config.php';

echo "<h2>Test Fixed AJAX and JavaScript</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
$userRole = getRoleName($user['role_id']);
echo "<p>Logged in as: {$user['full_name']} ($userRole)</p>";

// Test 1: Check if get_product.php returns clean JSON
echo "<h3>Test 1: Clean JSON Response</h3>";

$testProduct = fetchRow("SELECT id, name FROM products LIMIT 1");

if ($testProduct) {
    $productId = $testProduct['id'];
    echo "<p>Testing product ID: $productId ({$testProduct['name']})</p>";
    
    // Test the endpoint by including it (simulating AJAX)
    echo "<h4>Direct Test:</h4>";
    
    // Save current output buffer
    ob_start();
    
    // Simulate the AJAX request
    $_GET['id'] = $productId;
    
    try {
        include 'manager/get_product.php';
        $output = ob_get_contents();
        
        // Clean output buffer
        ob_end_clean();
        
        // Check if output is valid JSON
        $jsonData = json_decode($output, true);
        
        if ($jsonData !== null) {
            echo "<p style='color: green;'>✅ Valid JSON returned</p>";
            echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ Invalid JSON returned</p>";
            echo "<p>Output: " . htmlspecialchars(substr($output, 0, 200)) . "...</p>";
        }
        
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    // Reset $_GET
    unset($_GET['id']);
    
} else {
    echo "<p>No products found to test</p>";
}

// Test 2: JavaScript Debug Info
echo "<h3>Test 2: JavaScript Debug</h3>";
echo "<p>Common JavaScript issues and fixes:</p>";
echo "<ul>";
echo "<li><strong>Cannot set properties of null:</strong> Element doesn't exist when script runs</li>";
echo "<li><strong>Unexpected token '<':</strong> HTML returned instead of JSON</li>";
echo "<li><strong>Bootstrap modal errors:</strong> Bootstrap not loaded properly</li>";
echo "</ul>";

echo "<h4>Fixed Issues:</h4>";
echo "<ul>";
echo "<li>✅ Added null checks for DOM elements</li>";
echo "<li>✅ Added setTimeout for DOM readiness</li>";
echo "<li>✅ Added output buffering to prevent HTML leakage</li>";
echo "<li>✅ Added proper error handling</li>";
echo "</ul>";

// Test 3: Manual JavaScript Test
echo "<h3>Test 3: Manual JavaScript Test</h3>";
echo "<p>Open browser console on products page and run:</p>";
echo "<pre>";
echo "// Test 1: Check if elements exist
console.log('edit_product_id:', document.getElementById('edit_product_id'));
console.log('edit_currency_id:', document.getElementById('edit_currency_id'));

// Test 2: Test AJAX call
fetch('" . BASE_URL . "/manager/get_product.php?id=" . ($testProduct['id'] ?? 1) . "')
  .then(response => {
    console.log('Response status:', response.status);
    return response.json();
  })
  .then(data => {
    console.log('Product data:', data);
    console.log('Currency ID:', data.currency_id);
  })
  .catch(error => {
    console.error('AJAX Error:', error);
  });
</pre>";

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Test the AJAX endpoint above (should return clean JSON)</li>";
echo "<li>Go to <a href='manager/products.php'>Products Page</a></li>";
echo "<li>Click Edit button - should work without errors</li>";
echo "<li>Check console - should show product data, not errors</li>";
echo "</ol>";

echo "<p><strong>If still getting errors:</strong></p>";
echo "<ul>";
echo "<li>Check browser console for specific error messages</li>";
echo "<li>Verify Bootstrap is loading (no 404 errors)</li>";
echo "<li>Test the direct AJAX endpoint link above</li>";
echo "</ul>";
?>
