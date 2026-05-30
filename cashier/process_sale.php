<?php
/**
 * Process Sale (AJAX)
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Cashier');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!$data || !isset($data['items']) || empty($data['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid sale data']);
    exit;
}

try {
    $conn = getConnection();
    $conn->beginTransaction();
    
    // Create sale record with currency info (no customer dependency)
    $saleSql = "INSERT INTO sales (total_amount, discount_amount, final_amount, payment_method, notes, created_by, currency_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    // Get currency ID from first item (or default)
    $currencyId = 1; // Default to USD
    if (!empty($data['items'])) {
        $firstItem = $data['items'][0];
        // Get currency from product
        $product = fetchRow("SELECT currency_id FROM products WHERE id = ?", [$firstItem['product_id']]);
        if ($product && $product['currency_id']) {
            $currencyId = $product['currency_id'];
        }
    }
    
    $saleParams = [
        $data['total_amount'],
        $data['discount_amount'],
        $data['final_amount'],
        $data['payment_method'],
        $data['notes'] ?? null,
        $_SESSION['user_id'],
        $currencyId
    ];
    
    $stmt = $conn->prepare($saleSql);
    $stmt->execute($saleParams);
    $saleId = $conn->lastInsertId();
    
    // Add sale items and update stock
    foreach ($data['items'] as $item) {
        // Check stock availability
        $stockCheck = fetchRow("SELECT quantity_in_stock FROM products WHERE id = ? FOR UPDATE", [$item['product_id']]);
        
        if (!$stockCheck || $stockCheck['quantity_in_stock'] < $item['quantity']) {
            throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
        }
        
        // Add sale item with currency info
        $itemSql = "INSERT INTO sale_items (sale_id, product_id, quantity, selling_price, subtotal, currency_id) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        
        // Get currency for this specific product
        $productCurrency = fetchRow("SELECT currency_id FROM products WHERE id = ?", [$item['product_id']]);
        $itemCurrencyId = $productCurrency['currency_id'] ?? $currencyId;
        
        $itemParams = [
            $saleId,
            $item['product_id'],
            $item['quantity'],
            $item['selling_price'],
            $item['subtotal'],
            $itemCurrencyId
        ];
        
        $stmt = $conn->prepare($itemSql);
        $stmt->execute($itemParams);
        
        // Update product stock
        $updateStockSql = "UPDATE products SET quantity_in_stock = quantity_in_stock - ?, updated_at = NOW() 
                          WHERE id = ?";
        
        $updateParams = [$item['quantity'], $item['product_id']];
        
        $stmt = $conn->prepare($updateStockSql);
        $stmt->execute($updateParams);
    }
    
    $conn->commit();
    
    logActivity('Sale Completed', "Sale ID: $saleId, Amount: {$data['final_amount']}");
    
    echo json_encode(['success' => true, 'sale_id' => $saleId]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
