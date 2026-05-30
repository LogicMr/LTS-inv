<?php
/**
 * Products View (Cashier)
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Cashier');

$pageTitle = 'Products';
require_once __DIR__ . '/../includes/header.php';

// Get search and filter parameters
$search = cleanInput($_GET['search'] ?? '');
$category = cleanInput($_GET['category'] ?? '');
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

// Get categories for filters
$categories = fetchAll("SELECT * FROM categories ORDER BY name ASC");
?>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search Products</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Search by name, barcode, or batch...">
            </div>
            <div class="col-md-4">
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
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <a href="products.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Available Products</h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-4">
                <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No products found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Barcode</th>
                            <th>Stock</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Profit</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
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
                                    <?php if ($product['quantity_in_stock'] <= 0): ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php elseif ($product['quantity_in_stock'] <= $product['reorder_level']): ?>
                                        <span class="badge bg-warning"><?php echo $product['quantity_in_stock']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $product['quantity_in_stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-info">TSh <?php echo number_format($product['cost_price'], 2); ?></span>
                                </td>
                                <td>
                                    <strong>TSh <?php echo number_format($product['selling_price'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    $profit = $product['selling_price'] - $product['cost_price'];
                                    $profitMargin = $product['cost_price'] > 0 ? ($profit / $product['cost_price']) * 100 : 0;
                                    ?>
                                    <span class="text-success fw-bold">
                                        TSh <?php echo number_format($profit, 2); ?>
                                    </span>
                                    <br>
                                    <small class="text-muted"><?php echo number_format($profitMargin, 1); ?>%</small>
                                </td>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <?php echo buildPagination($pagination, 'products.php?' . http_build_query(['search' => $search, 'category' => $category])); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Sale Button -->
<div class="position-fixed bottom-0 end-0 p-3">
    <a href="pos.php" class="btn btn-success btn-lg rounded-circle shadow" style="width: 60px; height: 60px;">
        <i class="bi bi-cart-plus"></i>
    </a>
</div>

<!-- Back to Dashboard Button -->
<div style="text-align: center; margin: 20px 0;">
    <a href="<?php echo BASE_URL; ?>/cashier/dashboard.php" class="btn btn-secondary btn-lg">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
