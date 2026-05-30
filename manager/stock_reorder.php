<?php
/**
 * Stock Reorder Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();
requireRole('Manager');

$pageTitle = 'Stock Reorder';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_reorder') {
        $product_id = $_POST['product_id'] ?? '';
        $quantity = $_POST['quantity'] ?? '';
        $cost_price = $_POST['cost_price'] ?? '';
        $supplier_id = $_POST['supplier_id'] ?? '';
        $expiry_date = $_POST['expiry_date'] ?? '';
        $batch_number = $_POST['batch_number'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validate inputs
        if (empty($product_id) || empty($quantity) || empty($cost_price)) {
            $_SESSION['flash_message'] = 'Product, quantity, and cost price are required';
            $_SESSION['flash_type'] = 'danger';
        } elseif (!is_numeric($quantity) || $quantity <= 0) {
            $_SESSION['flash_message'] = 'Quantity must be a positive number';
            $_SESSION['flash_type'] = 'danger';
        } elseif (!is_numeric($cost_price) || $cost_price <= 0) {
            $_SESSION['flash_message'] = 'Cost price must be a positive number';
            $_SESSION['flash_type'] = 'danger';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Get product details
                $product = fetchRow("SELECT * FROM products WHERE id = ?", [$product_id]);
                if (!$product) {
                    throw new Exception('Product not found');
                }
                
                // Create purchase record
                $purchaseData = [
                    'supplier_id' => $supplier_id,
                    'total_amount' => $quantity * $cost_price,
                    'payment_method' => 'cash',
                    'notes' => $notes,
                    'created_by' => getCurrentUser()['id']
                ];
                
                $purchase_id = insert('purchases', $purchaseData);
                
                // Create purchase item
                $purchaseItemData = [
                    'purchase_id' => $purchase_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'unit_price' => $cost_price
                ];
                
                insert('purchase_items', $purchaseItemData);
                
                // Update product stock
                $new_quantity = $product['quantity_in_stock'] + $quantity;
                $updateData = [
                    'quantity_in_stock' => $new_quantity,
                    'cost_price' => $cost_price, // Update cost price with new purchase price
                    'supplier_id' => $supplier_id,
                    'expiry_date' => $expiry_date ?: $product['expiry_date'],
                    'batch_number' => $batch_number ?: $product['batch_number'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                update('products', $updateData, 'id = ?', [$product_id]);
                
                // Log activity
                logActivity('Stock Reordered', "Reordered {$quantity} units of {$product['name']} (Purchase ID: {$purchase_id})");
                
                $pdo->commit();
                
                $_SESSION['flash_message'] = "Successfully reordered {$quantity} units of {$product['name']}. Stock updated to {$new_quantity} units.";
                $_SESSION['flash_type'] = 'success';
                
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['flash_message'] = 'Error reordering stock: ' . $e->getMessage();
                $_SESSION['flash_type'] = 'danger';
            }
        }
    }
}

// Get products with low stock or out of stock
$lowStockProducts = fetchAll("
    SELECT p.*, c.name as category_name, s.name as supplier_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    WHERE p.is_active = 1
    AND (p.quantity_in_stock <= p.reorder_level OR p.quantity_in_stock = 0)
    ORDER BY p.quantity_in_stock ASC, p.name ASC
");

// Get all products for dropdown
$allProducts = fetchAll("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY c.name, p.name ASC
");

// Get suppliers
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-arrow-repeat"></i> Stock Reorder</h1>
        <a href="<?php echo getDashboardUrl(); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php include __DIR__ . '/../includes/flash_messages.php'; ?>

    <!-- Low Stock Alert -->
    <?php if (!empty($lowStockProducts)): ?>
    <div class="alert alert-warning">
        <h5><i class="bi bi-exclamation-triangle"></i> Low Stock Alert</h5>
        <p class="mb-2">The following products need to be reordered:</p>
        <div class="row">
            <?php foreach ($lowStockProducts as $product): ?>
            <div class="col-md-6 col-lg-4 mb-2">
                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                    <div>
                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                        <br>
                        <small class="text-muted">
                            Stock: <?php echo $product['quantity_in_stock']; ?> / 
                            Reorder: <?php echo $product['reorder_level']; ?>
                        </small>
                    </div>
                    <span class="badge bg-<?php echo $product['quantity_in_stock'] == 0 ? 'danger' : 'warning'; ?>">
                        <?php echo $product['quantity_in_stock'] == 0 ? 'Out of Stock' : 'Low Stock'; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Reorder Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add Reorder Stock</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_reorder">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="product_id" class="form-label">Product *</label>
                                    <select class="form-select" id="product_id" name="product_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($allProducts as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" 
                                                data-current-stock="<?php echo $product['quantity_in_stock']; ?>"
                                                data-current-cost="<?php echo $product['cost_price']; ?>"
                                                data-category="<?php echo htmlspecialchars($product['category_name']); ?>">
                                            <?php echo htmlspecialchars($product['category_name'] . ' - ' . $product['name']); ?>
                                            (Current Stock: <?php echo $product['quantity_in_stock']; ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Quantity to Add *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           min="1" required placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cost_price" class="form-label">Cost Price per Unit *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">TSh</span>
                                        <input type="number" class="form-control" id="cost_price" name="cost_price" 
                                               step="0.01" min="0" required placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="supplier_id" class="form-label">Supplier</label>
                                    <select class="form-select" id="supplier_id" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>">
                                            <?php echo htmlspecialchars($supplier['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiry_date" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_number" class="form-label">Batch Number</label>
                                    <input type="text" class="form-control" id="batch_number" name="batch_number" 
                                           placeholder="Enter batch number">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Enter any notes about this reorder"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Reorder Stock
                            </button>
                            <a href="<?php echo getDashboardUrl(); ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Product Info</h5>
                </div>
                <div class="card-body">
                    <div id="product-info">
                        <p class="text-muted">Select a product to view details</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Cost Summary</h5>
                </div>
                <div class="card-body">
                    <div id="cost-summary">
                        <p class="text-muted">Enter quantity and cost price to see summary</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity');
    const costPriceInput = document.getElementById('cost_price');
    const productInfo = document.getElementById('product-info');
    const costSummary = document.getElementById('cost-summary');

    function updateProductInfo() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        
        if (productSelect.value) {
            const currentStock = selectedOption.dataset.currentStock;
            const currentCost = selectedOption.dataset.currentCost;
            const category = selectedOption.dataset.category;
            const productName = selectedOption.textContent.split(' (Current Stock:')[0];
            
            productInfo.innerHTML = `
                <h6>${productName}</h6>
                <p><strong>Category:</strong> ${category}</p>
                <p><strong>Current Stock:</strong> ${currentStock} units</p>
                <p><strong>Current Cost:</strong> TSh ${parseFloat(currentCost).toFixed(2)}</p>
            `;
            
            // Pre-fill current cost price
            if (!costPriceInput.value) {
                costPriceInput.value = parseFloat(currentCost).toFixed(2);
            }
        } else {
            productInfo.innerHTML = '<p class="text-muted">Select a product to view details</p>';
        }
        
        updateCostSummary();
    }

    function updateCostSummary() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const costPrice = parseFloat(costPriceInput.value) || 0;
        const totalCost = quantity * costPrice;
        
        if (quantity > 0 && costPrice > 0) {
            costSummary.innerHTML = `
                <div class="mb-2">
                    <small class="text-muted">Quantity:</small><br>
                    <strong>${quantity} units</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Cost per Unit:</small><br>
                    <strong>TSh ${costPrice.toFixed(2)}</strong>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Total Cost:</small><br>
                    <strong class="text-primary">TSh ${totalCost.toFixed(2)}</strong>
                </div>
            `;
        } else {
            costSummary.innerHTML = '<p class="text-muted">Enter quantity and cost price to see summary</p>';
        }
    }

    productSelect.addEventListener('change', updateProductInfo);
    quantityInput.addEventListener('input', updateCostSummary);
    costPriceInput.addEventListener('input', updateCostSummary);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
