<?php
/**
 * Check Purchase Items Database Structure
 */
require_once __DIR__ . '/config/config.php';

echo "=== Purchase Items Table Structure ===\n";
$purchaseItems = fetchAll("DESCRIBE purchase_items");
foreach ($purchaseItems as $column) {
    echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
}

echo "\n=== Purchases Table Structure ===\n";
$purchases = fetchAll("DESCRIBE purchases");
foreach ($purchases as $column) {
    echo "- {$column['Field']} ({$column['Type']}) {$column['Null']} {$column['Key']}\n";
}

echo "\n=== Recent Purchase Items Sample ===\n";
$recentItems = fetchAll("SELECT * FROM purchase_items LIMIT 3");
foreach ($recentItems as $item) {
    echo "ID: {$item['id']}, Product: {$item['product_id']}, Barcode: " . ($item['barcode'] ?? 'NULL') . ", Currency: " . ($item['currency_id'] ?? 'NULL') . "\n";
}

echo "\n=== Check if columns exist ===\n";
$barcodeExists = fetchRow("SHOW COLUMNS FROM purchase_items LIKE 'barcode'");
$currencyExists = fetchRow("SHOW COLUMNS FROM purchase_items LIKE 'currency_id'");

echo "Barcode column exists: " . ($barcodeExists ? "YES" : "NO") . "\n";
echo "Currency column exists: " . ($currencyExists ? "YES" : "NO") . "\n";

if (!$barcodeExists || !$currencyExists) {
    echo "\n=== Database Update Needed ===\n";
    echo "You need to add missing columns to purchase_items table:\n";
    
    if (!$barcodeExists) {
        echo "- ADD barcode column\n";
    }
    
    if (!$currencyExists) {
        echo "- ADD currency_id column\n";
    }
} else {
    echo "\n=== Database Structure OK ===\n";
    echo "All required columns exist for new purchase functionality!\n";
}
?>
