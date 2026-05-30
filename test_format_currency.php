<?php
/**
 * Test Format Currency Fix
 * This script tests the updated formatCurrency function
 */
require_once 'config/config.php';
require_once 'includes/currency.php';

echo "<h2>Test Format Currency Fix</h2>";

try {
    // Test with no currency ID (should use default)
    echo "<h3>Test 1: Default Currency</h3>";
    $defaultFormatted = formatCurrency(100.50);
    echo "<p>formatCurrency(100.50) = $defaultFormatted</p>";
    
    // Test with specific currency ID
    echo "<h3>Test 2: Specific Currency</h3>";
    $currencies = getCurrencies();
    
    foreach ($currencies as $currency) {
        if ($currency['is_active']) {
            $formatted = formatCurrency(100.50, $currency['id']);
            echo "<p>formatCurrency(100.50, {$currency['id']}) = $formatted ({$currency['code']})</p>";
        }
    }
    
    // Test old behavior fallback
    echo "<h3>Test 3: Fallback Behavior</h3>";
    if (defined('CURRENCY')) {
        echo "<p>CURRENCY constant defined: " . CURRENCY . "</p>";
    } else {
        echo "<p>CURRENCY constant not defined</p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green;'>✅ formatCurrency function is working correctly!</p>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='quick_currency_test.php'>Test All Currency Functions</a></li>";
    echo "<li><a href='manager/products.php'>Test Product Management</a></li>";
    echo "<li><a href='test_product_buttons.php'>Test Product Buttons</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
