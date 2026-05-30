<?php
/**
 * Purchases Management (Stock-In)
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/currency.php';

requireRole('Manager');

$pageTitle = 'Purchase Management';

// Handle purchase creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create_purchase') {
    $supplierId = intval($_POST['supplier_id']);
    $purchaseDate = $_POST['purchase_date'];
    $notes = $_POST['notes'] ?? '';
    $items = $_POST['items'] ?? [];
    
    if (empty($supplierId) || empty($purchaseDate) || empty($items)) {
        $_SESSION['flash_message'] = 'Please fill all required fields and add at least one item';
        $_SESSION['flash_type'] = 'danger';
        redirect('manager/purchases.php');
    }
    
    try {
        // Start transaction
        $conn = getConnection();
        $conn->beginTransaction();
        
        // Create purchase record
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['quantity'] * $item['cost_price'];
        }
        
        $purchaseSql = "INSERT INTO purchases (supplier_id, purchase_date, total_amount, notes, created_by) 
                       VALUES (?, ?, ?, ?, ?)";
        $purchaseParams = [$supplierId, $purchaseDate, $totalAmount, $notes, $_SESSION['user_id']];
        
        $stmt = $conn->prepare($purchaseSql);
        $stmt->execute($purchaseParams);
        $purchaseId = $conn->lastInsertId();
        
        // Add purchase items and update stock
        foreach ($items as $item) {
            // Add purchase item
            $itemSql = "INSERT INTO purchase_items (purchase_id, product_id, quantity, cost_price, expiry_date, batch_number, subtotal) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
            $subtotal = $item['quantity'] * $item['cost_price'];
            $itemParams = [
                $purchaseId,
                $item['product_id'],
                $item['quantity'],
                $item['cost_price'],
                $item['expiry_date'] ?? null,
                $item['batch_number'] ?? null,
                $subtotal
            ];
            
            $stmt = $conn->prepare($itemSql);
            $stmt->execute($itemParams);
            
            // Update product stock
            $updateStockSql = "UPDATE products SET quantity_in_stock = quantity_in_stock + ?, 
                              cost_price = ?, expiry_date = ?, batch_number = ?, updated_at = NOW() 
                              WHERE id = ?";
            $updateParams = [
                $item['quantity'],
                $item['cost_price'],
                $item['expiry_date'] ?? null,
                $item['batch_number'] ?? null,
                $item['product_id']
            ];
            
            $stmt = $conn->prepare($updateStockSql);
            $stmt->execute($updateParams);
        }
        
        $conn->commit();
        
        $_SESSION['flash_message'] = 'Purchase recorded successfully';
        $_SESSION['flash_type'] = 'success';
        logActivity('Purchase Created', "Purchase ID: $purchaseId, Supplier: $supplierId");
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_message'] = 'Error recording purchase: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'danger';
    }
    
    redirect('manager/purchases.php');
}

require_once __DIR__ . '/../includes/header.php';

// Get search and filter parameters
$search = cleanInput($_GET['search'] ?? '');
$supplier = cleanInput($_GET['supplier'] ?? '');
$dateFrom = cleanInput($_GET['date_from'] ?? '');
$dateTo = cleanInput($_GET['date_to'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['1=1'];
$params = [];

if (!empty($search)) {
    $where[] = "(p.id LIKE ? OR sup.name LIKE ? OR p.notes LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($supplier)) {
    $where[] = "p.supplier_id = ?";
    $params[] = $supplier;
}

if (!empty($dateFrom)) {
    $where[] = "p.purchase_date >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "p.purchase_date <= ?";
    $params[] = $dateTo;
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM purchases p $whereClause";
$totalResult = fetchRow($countSql, $params);
$totalItems = $totalResult['total'];

// Get pagination
$pagination = getPagination($totalItems, ITEMS_PER_PAGE, $page);

// Get purchases
$sql = "SELECT p.*, sup.name as supplier_name, u.full_name as created_by_name
        FROM purchases p
        JOIN suppliers sup ON p.supplier_id = sup.id
        JOIN users u ON p.created_by = u.id
        $whereClause
        ORDER BY p.purchase_date DESC, p.created_at DESC
        LIMIT {$pagination['offset']}, {$pagination['items_per_page']}";

$purchases = fetchAll($sql, $params);

// Get suppliers for dropdown
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

// Get products for dropdown
$products = fetchAll("SELECT p.*, c.name as category_name 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.is_active = 1 
                     ORDER BY p.name ASC");
?>

<!-- Create Purchase Button -->
<div class="card mb-4">
    <div class="card-body">
        <button class="btn btn-primary" onclick="window.showCreatePurchaseModal();">
            <i class="bi bi-plus"></i> New Purchase
        </button>
        <button class="btn btn-success ms-2" onclick="
            var modal = document.getElementById('createPurchaseModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.add('show');
                
                // Add backdrop
                var backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                backdrop.id = 'modal-backdrop';
                document.body.appendChild(backdrop);
                
                // Add close handler to backdrop
                backdrop.onclick = function() {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    if (document.getElementById('modal-backdrop')) {
                        document.getElementById('modal-backdrop').remove();
                    }
                };
                
                // Add close handlers to close buttons
                var closeButtons = modal.querySelectorAll('[data-bs-dismiss=\"modal\"]');
                closeButtons.forEach(function(btn) {
                    btn.onclick = function() {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        if (document.getElementById('modal-backdrop')) {
                            document.getElementById('modal-backdrop').remove();
                        }
                    };
                });
                
                alert('Simple modal opened! Click X or backdrop to close.');
            } else {
                alert('Modal not found');
            }
        ">
            Simple Modal Test
        </button>
        <button class="btn btn-warning ms-2" onclick="
            // Direct test of showCreatePurchaseModal function
            if (typeof window.showCreatePurchaseModal === 'function') {
                alert('Function exists, calling it...');
                window.showCreatePurchaseModal();
            } else {
                alert('Function does not exist');
            }
        ">
            Test Function
        </button>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by ID, supplier, or notes...">
            </div>
            <div class="col-md-2">
                <label for="supplier" class="form-label">Supplier</label>
                <select class="form-select" id="supplier" name="supplier">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?php echo $sup['id']; ?>" <?php echo $supplier == $sup['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sup['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo htmlspecialchars($dateFrom); ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo htmlspecialchars($dateTo); ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <a href="purchases.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Purchases Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Purchase History</h5>
    </div>
    <div class="card-body">
        <?php if (empty($purchases)): ?>
            <div class="text-center py-4">
                <i class="bi bi-cart-plus text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No purchases found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Purchase ID</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Total Amount</th>
                            <th>Items</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($purchase['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo formatDate($purchase['purchase_date']); ?></td>
                                <td><?php echo htmlspecialchars($purchase['supplier_name']); ?></td>
                                <td>TSh <?php echo number_format($purchase['total_amount'], 2); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" onclick="viewPurchaseItems(<?php echo $purchase['id']; ?>)">
                                        View Items
                                    </button>
                                </td>
                                <td><?php echo htmlspecialchars($purchase['created_by_name']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewPurchaseDetails(<?php echo $purchase['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="printPurchase(<?php echo $purchase['id']; ?>)">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <?php echo buildPagination($pagination, 'purchases.php?' . http_build_query(['search' => $search, 'supplier' => $supplier, 'date_from' => $dateFrom, 'date_to' => $dateTo])); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Create Purchase Modal -->
<div class="modal fade" id="createPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="purchaseForm">
                <input type="hidden" name="action" value="create_purchase">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="supplier_id" class="form-label">Supplier *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" 
                                       placeholder="Type supplier name..." autocomplete="off">
                                <input type="hidden" id="supplier_id" name="supplier_id" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleSupplierSelect()">
                                    <i class="bi bi-list"></i>
                                </button>
                            </div>
                            <select class="form-select mt-2" id="supplier_select" style="display: none;">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?php echo $sup['id']; ?>" data-name="<?php echo htmlspecialchars($sup['name']); ?>">
                                        <?php echo htmlspecialchars($sup['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="purchase_date" class="form-label">Purchase Date *</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="notes" class="form-label">Notes</label>
                            <input type="text" class="form-control" id="notes" name="notes" 
                                   placeholder="Optional notes...">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Purchase Items</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="product_name" class="form-label">Product *</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="product_name" name="product_name" 
                                       placeholder="Type product name..." autocomplete="off">
                                <input type="hidden" id="product_id" name="product_id">
                                <button class="btn btn-outline-secondary" type="button" onclick="toggleProductSelect()">
                                    <i class="bi bi-list"></i>
                                </button>
                            </div>
                            <select class="form-select mt-2" id="product_select" style="display: none;">
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['id']; ?>" 
                                            data-cost="<?php echo $product['cost_price']; ?>"
                                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            data-barcode="<?php echo htmlspecialchars($product['barcode'] ?? ''); ?>"
                                            data-currency="<?php echo $product['currency_id']; ?>">
                                        <?php echo htmlspecialchars($product['name']); ?> - 
                                        <?php echo htmlspecialchars($product['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="item_barcode" class="form-label">Barcode</label>
                            <input type="text" class="form-control" id="item_barcode" name="barcode" 
                                   placeholder="Scan or enter barcode...">
                        </div>
                        <div class="col-md-2">
                            <label for="item_quantity" class="form-label">Quantity *</label>
                            <input type="number" class="form-control" id="item_quantity" min="1" value="1">
                        </div>
                        <div class="col-md-2">
                            <label for="item_cost_price" class="form-label">Cost Price *</label>
                            <input type="number" step="0.01" class="form-control" id="item_cost_price" min="0">
                        </div>
                        <div class="col-md-2">
                            <label for="item_currency" class="form-label">Currency *</label>
                            <select class="form-select" id="item_currency" name="currency_id" required>
                                <?php echo getCurrencySelectOptions(); ?>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label for="item_expiry_date" class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" id="item_expiry_date">
                        </div>
                    </div>
                        <div class="col-md-2">
                            <label for="item_batch_number" class="form-label">Batch Number</label>
                            <input type="text" class="form-control" id="item_batch_number">
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary mb-3" onclick="addPurchaseItem()">
                        <i class="bi bi-plus"></i> Add Item
                    </button>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Barcode</th>
                                    <th>Quantity</th>
                                    <th>Cost Price</th>
                                    <th>Subtotal</th>
                                    <th>Expiry Date</th>
                                    <th>Batch Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody">
                                <!-- Items will be added here dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th id="totalAmount">$0.00</th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Purchase Details Modal -->
<div class="modal fade" id="purchaseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="purchaseDetailsBody">
                <!-- Content will be populated via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Define functions immediately at the top
window.showCreatePurchaseModal = function() {
    console.log('New Purchase button clicked');
    
    if (typeof showModal === 'undefined') {
        console.error('showModal function is not defined');
        alert('Error: Modal function not available. Please refresh the page.');
        return;
    }
    
    try {
        showModal('createPurchaseModal');
        console.log('Modal opened successfully');
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening modal: ' + error.message);
    }
};

window.testModal = function() {
    console.log('Testing showModal directly');
    
    if (typeof showModal !== 'undefined') {
        showModal('createPurchaseModal');
    } else {
        alert('showModal is undefined');
    }
};

let purchaseItems = [];
let itemIdCounter = 0;

// Toggle supplier select dropdown
function toggleSupplierSelect() {
    const select = document.getElementById('supplier_select');
    const input = document.getElementById('supplier_name');
    
    if (select.style.display === 'none') {
        select.style.display = 'block';
        input.style.display = 'none';
    } else {
        select.style.display = 'none';
        input.style.display = 'block';
    }
}

// Toggle product select dropdown
function toggleProductSelect() {
    const select = document.getElementById('product_select');
    const input = document.getElementById('product_name');
    
    if (select.style.display === 'none') {
        select.style.display = 'block';
        input.style.display = 'none';
    } else {
        select.style.display = 'none';
        input.style.display = 'block';
    }
}

// Handle supplier selection from dropdown
document.getElementById('supplier_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('supplier_name').value = option.dataset.name;
        document.getElementById('supplier_id').value = option.value;
        toggleSupplierSelect(); // Hide dropdown after selection
    }
});

// Handle product selection from dropdown
document.getElementById('product_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('product_name').value = option.dataset.name;
        document.getElementById('product_id').value = option.value;
        document.getElementById('item_cost_price').value = option.dataset.cost;
        document.getElementById('item_barcode').value = option.dataset.barcode || '';
        document.getElementById('item_currency').value = option.dataset.currency || '';
        toggleProductSelect(); // Hide dropdown after selection
    }
});

// Auto-fill product by barcode
document.getElementById('item_barcode').addEventListener('blur', function() {
    const barcode = this.value.trim();
    if (barcode) {
        // Find product by barcode
        const products = <?php echo json_encode(array_map(function($p) { 
            return ['id' => $p['id'], 'name' => $p['name'], 'barcode' => $p['barcode'] ?? '', 'cost_price' => $p['cost_price'], 'currency_id' => $p['currency_id']]; 
        }, $products)); ?>;
        
        const product = products.find(p => p.barcode === barcode);
        if (product) {
            document.getElementById('product_name').value = product.name;
            document.getElementById('product_id').value = product.id;
            document.getElementById('item_cost_price').value = product.cost_price;
            document.getElementById('item_currency').value = product.currency_id;
        }
    }
});

// Auto-fill supplier by typing
document.getElementById('supplier_name').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const select = document.getElementById('supplier_select');
    const options = select.options;
    
    // Filter options based on input
    for (let i = 1; i < options.length; i++) {
        const option = options[i];
        const supplierName = option.dataset.name.toLowerCase();
        if (supplierName.includes(searchTerm)) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    }
    
    // Show dropdown if typing
    if (searchTerm.length > 0) {
        select.style.display = 'block';
        document.getElementById('supplier_name').style.display = 'block';
    }
});

// Auto-fill product by typing
document.getElementById('product_name').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const select = document.getElementById('product_select');
    const options = select.options;
    
    // Filter options based on input
    for (let i = 1; i < options.length; i++) {
        const option = options[i];
        const productName = option.dataset.name.toLowerCase();
        if (productName.includes(searchTerm)) {
            option.style.display = 'block';
        } else {
            option.style.display = 'none';
        }
    }
    
    // Show dropdown if typing
    if (searchTerm.length > 0) {
        select.style.display = 'block';
        document.getElementById('product_name').style.display = 'block';
    }
});

// Auto-fill cost price when product is selected (legacy support)
document.getElementById('product_select').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.value) {
        document.getElementById('item_cost_price').value = option.dataset.cost;
    }
});

function addPurchaseItem() {
    const productId = document.getElementById('product_id').value;
    const productName = document.getElementById('product_name').value;
    const quantity = document.getElementById('item_quantity').value;
    const costPrice = document.getElementById('item_cost_price').value;
    const expiryDate = document.getElementById('item_expiry_date').value;
    const batchNumber = document.getElementById('item_batch_number').value;
    const barcode = document.getElementById('item_barcode').value;
    const currencyId = document.getElementById('item_currency').value;
    
    if (!productId || !productName || !quantity || !costPrice || !currencyId) {
        alert('Please select a product and fill all required fields');
        return;
    }
    
    const subtotal = quantity * costPrice;
    
    const item = {
        id: itemIdCounter++,
        product_id: productId,
        product_name: productName,
        quantity: parseInt(quantity),
        cost_price: parseFloat(costPrice),
        expiry_date: expiryDate,
        batch_number: batchNumber,
        barcode: barcode,
        currency_id: currencyId,
        subtotal: subtotal
    };
    
    purchaseItems.push(item);
    renderItemsTable();
    
    // Clear form fields
    clearItemForm();
}

function clearItemForm() {
    document.getElementById('product_name').value = '';
    document.getElementById('product_id').value = '';
    document.getElementById('item_barcode').value = '';
    document.getElementById('item_quantity').value = '1';
    document.getElementById('item_cost_price').value = '';
    document.getElementById('item_currency').value = '';
    document.getElementById('item_expiry_date').value = '';
    document.getElementById('item_batch_number').value = '';
}

function removeItem(itemId) {
    purchaseItems = purchaseItems.filter(item => item.id !== itemId);
    renderItemsTable();
}

function renderItemsTable() {
    const tbody = document.getElementById('itemsTableBody');
    tbody.innerHTML = '';
    
    let total = 0;
    
    purchaseItems.forEach(item => {
        const row = tbody.insertRow();
        row.innerHTML = 
            '<td>' + item.product_name + '</td>' +
            '<td>' + (item.barcode || 'N/A') + '</td>' +
            '<td>' + item.quantity + '</td>' +
            '<td>' + getCurrencySymbol(item.currency_id) + item.cost_price.toFixed(2) + '</td>' +
            '<td>' + getCurrencySymbol(item.currency_id) + item.subtotal.toFixed(2) + '</td>' +
            '<td>' + (item.expiry_date || 'N/A') + '</td>' +
            '<td>' + (item.batch_number || 'N/A') + '</td>' +
            '<td>' +
                '<button class="btn btn-sm btn-outline-danger" onclick="removeItem(' + item.id + ')">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</td>';
        total += item.subtotal;
    });
    
    document.getElementById('totalAmount').textContent = formatCurrencyDisplay(total, getDefaultCurrency());
}

function clearItemForm() {
    document.getElementById('product_select').value = '';
    document.getElementById('item_quantity').value = '1';
    document.getElementById('item_cost_price').value = '';
    document.getElementById('item_expiry_date').value = '';
    document.getElementById('item_batch_number').value = '';
}

// Handle form submission
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    if (purchaseItems.length === 0) {
        e.preventDefault();
        alert('Please add at least one item to the purchase');
        return;
    }
    
    // Add items as hidden inputs
    purchaseItems.forEach((item, index) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'items[' + index + '][product_id]';
        input.value = item.product_id;
        this.appendChild(input);
        
        const input2 = document.createElement('input');
        input2.type = 'hidden';
        input2.name = 'items[' + index + '][quantity]';
        input2.value = item.quantity;
        this.appendChild(input2);
        
        const input3 = document.createElement('input');
        input3.type = 'hidden';
        input3.name = 'items[' + index + '][cost_price]';
        input3.value = item.cost_price;
        this.appendChild(input3);
        
        const input4 = document.createElement('input');
        input4.type = 'hidden';
        input4.name = 'items[' + index + '][expiry_date]';
        input4.value = item.expiry_date;
        this.appendChild(input4);
        
        const input5 = document.createElement('input');
        input5.type = 'hidden';
        input5.name = 'items[' + index + '][batch_number]';
        input5.value = item.batch_number;
        this.appendChild(input5);
    });
});

function viewPurchaseItems(purchaseId) {
    fetch('<?php echo BASE_URL; ?>/manager/get_purchase_items.php?id=' + purchaseId)
        .then(response => response.json())
        .then(data => {
            let html = '<h6>Purchase Items</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Product</th><th>Quantity</th><th>Cost Price</th><th>Subtotal</th><th>Expiry Date</th><th>Batch Number</th></tr></thead><tbody>';
            
            data.items.forEach(item => {
                html += '<tr>' +
                    '<td>' + item.product_name + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>$' + item.cost_price + '</td>' +
                    '<td>$' + item.subtotal + '</td>' +
                    '<td>' + (item.expiry_date || 'N/A') + '</td>' +
                    '<td>' + (item.batch_number || 'N/A') + '</td>' +
                '</tr>';
            });
            
            html += '</tbody></table></div>';
            document.getElementById('purchaseDetailsBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('purchaseDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading purchase items');
        });
}

function viewPurchaseDetails(purchaseId) {
    // Similar to viewPurchaseItems but with more details
    viewPurchaseItems(purchaseId);
}

function printPurchase(purchaseId) {
    window.open('<?php echo BASE_URL; ?>/manager/print_purchase.php?id=' + purchaseId, '_blank');
}

// Currency helper functions
function getCurrencySymbol(currencyId) {
    const currencies = <?php echo json_encode(array_map(function($c) { 
        return ['id' => $c['id'], 'symbol' => $c['symbol']]; 
    }, getCurrencies())); ?>;
    
    const currency = currencies.find(c => c.id == currencyId);
    return currency ? currency.symbol : getDefaultCurrencySymbol();
}

function getDefaultCurrencySymbol() {
    return '<?php echo getDefaultCurrencySymbol(); ?>';
}

function getDefaultCurrency() {
    return <?php echo getDefaultCurrency(); ?>;
}

function formatCurrencyDisplay(amount, currencyId) {
    const symbol = getCurrencySymbol(currencyId);
    return symbol + amount.toFixed(2);
}

// Modal fallback functions (same as products.php)
function showModal(modalId) {
    console.log('Opening modal:', modalId);
    
    var modal = document.getElementById(modalId);
    if (!modal) {
        console.error('Modal element not found:', modalId);
        alert('Error: Modal not found');
        return;
    }
    
    // Show modal
    modal.style.display = 'block';
    modal.classList.add('show');
    
    // Add backdrop
    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'modal-backdrop';
    document.body.appendChild(backdrop);
    
    // Handle close buttons
    var closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(function(btn) {
        btn.onclick = function() {
            hideModal(modalId);
        };
    });
    
    // Handle backdrop click
    backdrop.onclick = function() {
        hideModal(modalId);
    };
    
    console.log('Modal opened successfully');
}

function hideModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        modal.classList.remove('show');
        
        // Remove backdrop
        var backdrop = document.getElementById('modal-backdrop');
        if (backdrop) {
            backdrop.parentNode.removeChild(backdrop);
        }
    }
}

// Check for Bootstrap and use it if available
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Try to use Bootstrap if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        console.log('Using Bootstrap modals');
        window.showModal = function(modalId) {
            var modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        };
        window.hideModal = function(modalId) {
            var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
        };
    } else {
        console.log('Using custom modal implementation');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
