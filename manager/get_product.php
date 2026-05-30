<?php
/**
 * Get Product Data (AJAX) - Clean Version
 * Inventory Management System
 */
// Start output buffering to catch any unwanted output
ob_start();

try {
    require_once __DIR__ . '/../config/config.php';
    
    // Clean any output that might have been generated
    ob_clean();
    
    // Handle authentication for AJAX requests
    if (!isLoggedIn()) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Not authenticated']);
        exit;
    }

    $user = getCurrentUser();
    $userRole = getRoleName($user['role_id']);

    // Check if user has required role (Admin or Manager)
    if ($userRole !== 'Admin' && $userRole !== 'Manager') {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit;
    }

    header('Content-Type: application/json');

    $productId = intval($_GET['id'] ?? 0);

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid product ID']);
        exit;
    }

    $sql = "SELECT * FROM products WHERE id = ?";
    $product = fetchRow($sql, [$productId]);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Ensure clean JSON output
    ob_clean();
    echo json_encode($product);
    exit;

} catch (Exception $e) {
    // Clean any output and return error
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}
?>
