<?php
/**
 * Get Purchase Items (AJAX)
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Manager');

header('Content-Type: application/json');

$purchaseId = intval($_GET['id'] ?? 0);

if ($purchaseId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid purchase ID']);
    exit;
}

$sql = "SELECT pi.*, p.name as product_name 
        FROM purchase_items pi 
        JOIN products p ON pi.product_id = p.id 
        WHERE pi.purchase_id = ? 
        ORDER BY pi.id";

$items = fetchAll($sql, [$purchaseId]);

echo json_encode(['items' => $items]);
?>
