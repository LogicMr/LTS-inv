<?php
/**
 * Products Management - Fixed Version
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/currency.php';

requireRole('Manager');

$pageTitle = 'Products Management';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $required = ['name', 'category_id', 'cost_price', 'selling_price', 'quantity_in_stock', 'reorder_level', 'currency_id'];
        $data = $_POST;
        $errors = validateRequired($required, $data);
        
        if (empty($errors)) {
            $sql = "INSERT INTO products (name, category_id, barcode, cost_price, selling_price, quantity_in_stock, 
                    reorder_level, supplier_id, expiry_date, batch_number, description, currency_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['name'],
                $data['category_id'],
                $data['barcode'] ?? null,
                $data['cost_price'],
                $data['selling_price'],
                $data['quantity_in_stock'],
                $data['reorder_level'],
                $data['supplier_id'] ?? null,
                $data['expiry_date'] ?? null,
                $data['batch_number'] ?? null,
                $data['description'] ?? null,
                $data['currency_id']
            ];
            
            if (executeNonQuery($sql, $params)) {
                $_SESSION['flash_message'] = 'Product added successfully';
                $_SESSION['flash_type'] = 'success';
                logActivity('Product Added', 'Added product: ' . $data['name']);
            } else {
                $_SESSION['flash_message'] = 'Error adding product';
                $_SESSION['flash_type'] = 'danger';
            }
        } else {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'danger';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    elseif ($action === 'edit') {
        $required = ['id', 'name', 'category_id', 'cost_price', 'selling_price', 'quantity_in_stock', 'reorder_level', 'currency_id'];
        $data = $_POST;
        $errors = validateRequired($required, $data);
        
        if (empty($errors)) {
            $sql = "UPDATE products SET name = ?, category_id = ?, barcode = ?, cost_price = ?, selling_price = ?, 
                    quantity_in_stock = ?, reorder_level = ?, supplier_id = ?, expiry_date = ?, batch_number = ?, 
                    description = ?, currency_id = ?, updated_at = NOW() WHERE id = ?";
            
            $params = [
                $data['name'],
                $data['category_id'],
                $data['barcode'] ?? null,
                $data['cost_price'],
                $data['selling_price'],
                $data['quantity_in_stock'],
                $data['reorder_level'],
                $data['supplier_id'] ?? null,
                $data['expiry_date'] ?? null,
                $data['batch_number'] ?? null,
                $data['description'] ?? null,
                $data['currency_id'],
                $data['id']
            ];
            
            if (executeNonQuery($sql, $params)) {
                $_SESSION['flash_message'] = 'Product updated successfully';
                $_SESSION['flash_type'] = 'success';
                logActivity('Product Updated', 'Updated product: ' . $data['name']);
            } else {
                $_SESSION['flash_message'] = 'Error updating product';
                $_SESSION['flash_type'] = 'danger';
            }
        } else {
            $_SESSION['flash_message'] = implode('<br>', $errors);
            $_SESSION['flash_type'] = 'danger';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    elseif ($action === 'delete') {
        $productId = (int)$_POST['id'];
        
        // Check if product has sales or purchases
        $hasTransactions = fetchRow("SELECT COUNT(*) as count FROM sale_items WHERE product_id = ?", [$productId])['count'] > 0 ||
                           fetchRow("SELECT COUNT(*) as count FROM purchase_items WHERE product_id = ?", [$productId])['count'] > 0;
        
        if ($hasTransactions) {
            // Deactivate instead of delete
            if (executeNonQuery("UPDATE products SET is_active = 0 WHERE id = ?", [$productId])) {
                $_SESSION['flash_message'] = 'Product deactivated (has transaction history)';
                $_SESSION['flash_type'] = 'warning';
                logActivity('Product Deactivated', 'Deactivated product ID: ' . $productId);
            }
        } else {
            // Safe to delete
            if (executeNonQuery("DELETE FROM products WHERE id = ?", [$productId])) {
                $_SESSION['flash_message'] = 'Product deleted successfully';
                $_SESSION['flash_type'] = 'success';
                logActivity('Product Deleted', 'Deleted product ID: ' . $productId);
            }
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get search and filter parameters
$search = cleanInput($_GET['search'] ?? '');
$category = cleanInput($_GET['category'] ?? '');
$supplier = cleanInput($_GET['supplier'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

// Build query
$where = ['p.is_active = 1'];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.barcode LIKE ? OR p.batch_number LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}

if (!empty($supplier)) {
    $where[] = "p.supplier_id = ?";
    $params[] = $supplier;
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
$totalResult = fetchRow($countSql, $params);
$totalItems = $totalResult['total'];

// Get pagination
$pagination = getPagination($totalItems, ITEMS_PER_PAGE, $page);

// Get products
$sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
        CASE 
            WHEN p.quantity_in_stock <= p.reorder_level THEN 'Low Stock'
            WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) THEN 'Near Expiry'
            WHEN p.expiry_date < CURRENT_DATE THEN 'Expired'
            ELSE 'Normal'
        END as alert_status
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        $whereClause
        ORDER BY p.name ASC
        LIMIT {$pagination['offset']}, {$pagination['items_per_page']}";

$products = fetchAll($sql, $params);

// Get categories and suppliers for filters
$categories = fetchAll("SELECT * FROM categories ORDER BY name ASC");
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/manager/dashboard.php">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text">
                <strong>Products Management</strong>
            </span>
        </div>
    </div>
</nav>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products Management</h1>
            <div>
                <span class="badge bg-success me-2">Manager</span>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['flash_message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php 
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    endif; 
?>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products...">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-outline-secondary">Clear</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Products List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Products List</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus"></i> Add Product
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Barcode</th>
                                <th>Stock</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <?php if ($product['batch_number']): ?>
                                            <br><small class="text-muted">Batch: <?php echo htmlspecialchars($product['batch_number']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo $product['barcode'] ? htmlspecialchars($product['barcode']) : 'N/A'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $product['quantity_in_stock'] <= $product['reorder_level'] ? 'bg-warning' : 'bg-success'; ?>">
                                            <?php echo $product['quantity_in_stock']; ?>
                                        </span>
                                    </td>
                                    <td>TSh <?php echo number_format($product['cost_price'], 2); ?></td>
                                    <td>TSh <?php echo number_format($product['selling_price'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $expiryClass = '';
                                        if (isExpired($product['expiry_date'])) {
                                            $expiryClass = 'text-danger';
                                        } elseif (isNearExpiry($product['expiry_date'])) {
                                            $expiryClass = 'text-warning';
                                        }
                                        ?>
                                        <span class="<?php echo $expiryClass; ?>">
                                            <?php echo formatDate($product['expiry_date']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo getAlertBadge($product['alert_status']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" onclick="editProductSimple(<?php echo $product['id']; ?>)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteProductSimple(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                                <i class="bi bi-trash"></i> Delete
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
                    <nav>
                        <ul class="pagination">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&supplier=<?php echo $supplier; ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&supplier=<?php echo $supplier; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&supplier=<?php echo $supplier; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Product Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Barcode</label>
                                <input type="text" class="form-control" id="barcode" name="barcode">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="batch_number" class="form-label">Batch Number</label>
                                <input type="text" class="form-control" id="batch_number" name="batch_number">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="expiry_date" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control" id="expiry_date" name="expiry_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cost_price" class="form-label">Cost Price *</label>
                                <input type="number" step="0.01" class="form-control" id="cost_price" name="cost_price" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="selling_price" class="form-label">Selling Price *</label>
                                <input type="number" step="0.01" class="form-control" id="selling_price" name="selling_price" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="currency_id" class="form-label">Currency</label>
                                <input type="text" class="form-control" value="TSh - Tanzanian Shilling (Default)" readonly>
                                <input type="hidden" id="currency_id" name="currency_id" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="quantity_in_stock" class="form-label">Initial Stock *</label>
                                <input type="number" class="form-control" id="quantity_in_stock" name="quantity_in_stock" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="reorder_level" class="form-label">Reorder Level *</label>
                                <input type="number" class="form-control" id="reorder_level" name="reorder_level" value="10" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_product_id" name="id">
                <div class="modal-body" id="editModalBody">
                    <!-- Content will be populated via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteProductName"></span>"?</p>
                <p class="text-warning">Note: Products with transaction history will be deactivated instead of deleted.</p>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="delete_product_id" name="id">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Simple JavaScript without complex templates -->
<script>
// Simple modal functions
function editProductSimple(productId) {
    console.log('Editing product:', productId);
    
    // Fetch product data
    fetch('<?php echo BASE_URL; ?>/manager/get_product.php?id=' + productId)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Product data:', data);
            
            if (data.error) {
                alert('Error loading product: ' + data.error);
                return;
            }
            
            // Populate edit form
            populateEditForm(data);
            
            // Show modal
            showModal('editProductModal');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading product data: ' + error.message);
        });
}

function populateEditForm(data) {
    var form = document.querySelector('#editProductModal form');
    
    // Set product ID
    document.getElementById('edit_product_id').value = data.id;
    
    // Build form HTML
    var formHTML = '<div class="row">' +
        '<div class="col-md-6">' +
            '<div class="mb-3">' +
                '<label class="form-label">Product Name *</label>' +
                '<input type="text" class="form-control" name="name" value="' + (data.name || '') + '" required>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-6">' +
            '<div class="mb-3">' +
                '<label class="form-label">Category *</label>' +
                '<select class="form-select" name="category_id" required>' +
                    '<?php foreach ($categories as $cat): ?>' +
                        '<option value="<?php echo $cat['id']; ?>" ' + (data.category_id == <?php echo $cat['id']; ?> ? 'selected' : '') + '><?php echo htmlspecialchars($cat['name']); ?></option>' +
                    '<?php endforeach; ?>' +
                '</select>' +
            '</div>' +
        '</div>' +
    '</div>' +
    
    '<div class="row">' +
        '<div class="col-md-4">' +
            '<div class="mb-3">' +
                '<label class="form-label">Barcode</label>' +
                '<input type="text" class="form-control" name="barcode" value="' + (data.barcode || '') + '">' +
            '</div>' +
        '</div>' +
        '<div class="col-md-4">' +
            '<div class="mb-3">' +
                '<label class="form-label">Batch Number</label>' +
                '<input type="text" class="form-control" name="batch_number" value="' + (data.batch_number || '') + '">' +
            '</div>' +
        '</div>' +
        '<div class="col-md-4">' +
            '<div class="mb-3">' +
                '<label class="form-label">Expiry Date</label>' +
                '<input type="date" class="form-control" name="expiry_date" value="' + (data.expiry_date || '') + '">' +
            '</div>' +
        '</div>' +
    '</div>' +
    
    '<div class="row">' +
        '<div class="col-md-3">' +
            '<div class="mb-3">' +
                '<label class="form-label">Cost Price *</label>' +
                '<input type="number" step="0.01" class="form-control" name="cost_price" value="' + (data.cost_price || '') + '" required>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-3">' +
            '<div class="mb-3">' +
                '<label class="form-label">Selling Price *</label>' +
                '<input type="number" step="0.01" class="form-control" name="selling_price" value="' + (data.selling_price || '') + '" required>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-3">' +
            '<div class="mb-3">' +
                '<label class="form-label">Currency *</label>' +
                '<select class="form-select" name="currency_id" required>' +
                    '<?php foreach (getCurrencies() as $cur): ?>' +
                        '<option value="<?php echo $cur['id']; ?>" ' + (data.currency_id == <?php echo $cur['id']; ?> ? 'selected' : '') + '><?php echo $cur['code']; ?> - <?php echo $cur['name']; ?><?php if ($cur['is_default']) echo ' (Default)'; ?></option>' +
                    '<?php endforeach; ?>' +
                '</select>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-3">' +
            '<div class="mb-3">' +
                '<label class="form-label">Stock Quantity *</label>' +
                '<input type="number" class="form-control" name="quantity_in_stock" value="' + (data.quantity_in_stock || '') + '" required>' +
            '</div>' +
        '</div>' +
    '</div>' +
    
    '<div class="row">' +
        '<div class="col-md-3">' +
            '<div class="mb-3">' +
                '<label class="form-label">Reorder Level *</label>' +
                '<input type="number" class="form-control" name="reorder_level" value="' + (data.reorder_level || '') + '" required>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-4">' +
            '<div class="mb-3">' +
                '<label class="form-label">Supplier</label>' +
                '<select class="form-select" name="supplier_id">' +
                    '<option value="">Select Supplier</option>' +
                    '<?php foreach ($suppliers as $sup): ?>' +
                        '<option value="<?php echo $sup['id']; ?>" ' + (data.supplier_id == <?php echo $sup['id']; ?> ? 'selected' : '') + '><?php echo htmlspecialchars($sup['name']); ?></option>' +
                    '<?php endforeach; ?>' +
                '</select>' +
            '</div>' +
        '</div>' +
        '<div class="col-md-5">' +
            '<div class="mb-3">' +
                '<label class="form-label">Description</label>' +
                '<textarea class="form-control" name="description" rows="2">' + (data.description || '') + '</textarea>' +
            '</div>' +
        '</div>' +
    '</div>';
    
    document.getElementById('editModalBody').innerHTML = formHTML;
}

function deleteProductSimple(productId, productName) {
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('delete_product_id').value = productId;
    showModal('deleteModal');
}

// Simple modal function that works with or without Bootstrap
function showModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        modal.classList.add('show');
        
        // Add backdrop
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modal-backdrop';
        document.body.appendChild(backdrop);
        
        // Handle close
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
    }
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
