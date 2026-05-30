<?php
/**
 * Direct Products Page Test
 * This script checks the actual products page for issues
 */
require_once 'config/config.php';

echo "<h2>Direct Products Page Test</h2>";

if (!isLoggedIn()) {
    echo "<p>Please <a href='auth/login.php'>login</a> first</p>";
    exit;
}

$user = getCurrentUser();
$userRole = getRoleName($user['role_id']);
echo "<p>Logged in as: {$user['full_name']} ($userRole)</p>";

// Test 1: Check if products page loads without errors
echo "<h3>Test 1: Products Page Loading</h3>";

echo "<h4>Checking required files:</h4>";
$requiredFiles = [
    'manager/products.php',
    'includes/header.php', 
    'includes/footer.php',
    'includes/currency.php',
    'manager/get_product.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<p>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

// Test 2: Check products data
echo "<h3>Test 2: Products Data</h3>";

try {
    $products = fetchAll("SELECT p.*, cur.code as currency_code FROM products p LEFT JOIN currencies cur ON p.currency_id = cur.id LIMIT 5");
    
    if (count($products) > 0) {
        echo "<p>✅ Found " . count($products) . " products</p>";
        
        foreach ($products as $product) {
            echo "<p>- Product ID {$product['id']}: " . htmlspecialchars($product['name']) . " ({$product['currency_code']})</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ No products found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error fetching products: " . $e->getMessage() . "</p>";
}

// Test 3: Check JavaScript functions
echo "<h3>Test 3: JavaScript Functions Check</h3>";

echo "<h4>Required JavaScript elements:</h4>";
$requiredElements = [
    'editProductModal' => 'Edit modal container',
    'deleteModal' => 'Delete modal container',
    'editModalBody' => 'Edit modal body',
    'deleteProductName' => 'Delete product name span',
    'edit_product_id' => 'Edit product ID input',
    'delete_product_id' => 'Delete product ID input'
];

echo "<p>These elements should exist on the products page:</p>";
foreach ($requiredElements as $element => $description) {
    echo "<p>- $element: $description</p>";
}

// Test 4: Check Bootstrap
echo "<h3>Test 4: Bootstrap Check</h3>";

echo "<h4>Bootstrap requirements:</h4>";
echo "<p>- Bootstrap CSS should be loaded in header</p>";
echo "<p>- Bootstrap JS should be loaded in footer</p>";
echo "<p>- No 404 errors for Bootstrap files</p>";

// Test 5: Create a minimal working version
echo "<h3>Test 5: Minimal Working Test</h3>";

if (count($products) > 0) {
    $testProduct = $products[0];
    
    echo "<h4>Test with Product: " . htmlspecialchars($testProduct['name']) . "</h4>";
    
    echo "<h5>Test Edit Function:</h5>";
    echo "<button onclick='testEdit(" . $testProduct['id'] . ")'>Test Edit Button</button>";
    echo "<div id='editTestResult'></div>";
    
    echo "<h5>Test Delete Function:</h5>";
    echo "<button onclick='testDelete(" . $testProduct['id'] . ", \"" . htmlspecialchars($testProduct['name']) . "\")'>Test Delete Button</button>";
    echo "<div id='deleteTestResult'></div>";
    
    // Add JavaScript for testing
    echo "
    <script>
    function testEdit(productId) {
        document.getElementById('editTestResult').innerHTML = 'Testing edit...';
        
        fetch('" . BASE_URL . "/manager/get_product.php?id=' + productId)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Product data:', data);
                document.getElementById('editTestResult').innerHTML = 
                    '<p style=\"color: green;\">✅ Edit test successful!</p>' +
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                console.error('Edit test error:', error);
                document.getElementById('editTestResult').innerHTML = 
                    '<p style=\"color: red;\">❌ Edit test failed: ' + error.message + '</p>';
            });
    }
    
    function testDelete(productId, productName) {
        document.getElementById('deleteTestResult').innerHTML = 'Testing delete...';
        
        // Simulate delete form submission
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', productId);
        
        fetch('" . BASE_URL . "/manager/products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Delete response:', text);
            document.getElementById('deleteTestResult').innerHTML = 
                '<p style=\"color: green;\">✅ Delete test completed!</p>' +
                '<p>Response: ' + text.substring(0, 200) + '...</p>';
        })
        .catch(error => {
            console.error('Delete test error:', error);
            document.getElementById('deleteTestResult').innerHTML = 
                '<p style=\"color: red;\">❌ Delete test failed: ' + error.message + '</p>';
        });
    }
    </script>";
}

echo "<hr>";
echo "<h3>Manual Debugging Steps:</h3>";
echo "<ol>";
echo "<li>Open <a href='manager/products.php' target='_blank'>Products Page</a></li>";
echo "<li>Press F12 to open developer tools</li>";
echo "<li>Go to Console tab</li>";
echo "<li>Click Edit button on a product</li>";
echo "<li>Click Delete button on a product</li>";
echo "<li>Report any console errors you see</li>";
echo "</ol>";

echo "<h3>What to Check:</h3>";
echo "<ul>";
echo "<li>Any JavaScript errors in console (red text)</li>";
echo "<li>Any failed network requests (Network tab)</li>";
echo "<li>Bootstrap modals opening correctly</li>";
echo "<li>AJAX requests returning JSON (not HTML)</li>";
echo "</ul>";

echo "<p><strong>If buttons don't work at all:</strong></p>";
echo "<ul>";
echo "<li>Check if JavaScript is enabled</li>";
echo "<li>Check for Bootstrap loading errors</li>";
echo "<li>Verify no syntax errors in page source</li>";
echo "</ul>";
?>
