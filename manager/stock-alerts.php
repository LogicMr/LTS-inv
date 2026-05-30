<?php
/**
 * Stock Alerts Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Manager');

$pageTitle = 'Stock Alerts';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$alertType = cleanInput($_GET['alert_type'] ?? '');
$category = cleanInput($_GET['category'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));

// Build query based on alert type
$where = ['p.is_active = 1'];
$params = [];

switch ($alertType) {
    case 'low_stock':
        $where[] = 'p.quantity_in_stock <= p.reorder_level';
        break;
    case 'expired':
        $where[] = 'p.expiry_date < CURRENT_DATE';
        break;
    case 'near_expiry':
        $where[] = 'p.expiry_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)';
        break;
    case 'out_of_stock':
        $where[] = 'p.quantity_in_stock = 0';
        break;
}

if (!empty($category)) {
    $where[] = 'p.category_id = ?';
    $params[] = $category;
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM products p $whereClause";
$totalResult = fetchRow($countSql, $params);
$totalItems = $totalResult['total'];

// Get pagination
$pagination = getPagination($totalItems, ITEMS_PER_PAGE, $page);

// Get alerts
$sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
        CASE 
            WHEN p.quantity_in_stock = 0 THEN 'Out of Stock'
            WHEN p.quantity_in_stock <= p.reorder_level THEN 'Low Stock'
            WHEN p.expiry_date < CURRENT_DATE THEN 'Expired'
            WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) THEN 'Expires in 7 days'
            WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) THEN 'Expires in 30 days'
            ELSE 'Normal'
        END as alert_status,
        DATEDIFF(p.expiry_date, CURRENT_DATE) as days_until_expiry
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        $whereClause
        ORDER BY 
            CASE 
                WHEN p.quantity_in_stock = 0 THEN 1
                WHEN p.expiry_date < CURRENT_DATE THEN 2
                WHEN p.quantity_in_stock <= p.reorder_level THEN 3
                WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) THEN 4
                ELSE 5
            END,
            p.name ASC
        LIMIT {$pagination['offset']}, {$pagination['items_per_page']}";

$alerts = fetchAll($sql, $params);

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name ASC");

// Get alert counts for summary
$lowStockCount = fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND quantity_in_stock <= reorder_level AND quantity_in_stock > 0")['count'];
$outOfStockCount = fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND quantity_in_stock = 0")['count'];
$expiredCount = fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND expiry_date < CURRENT_DATE")['count'];
$nearExpiryCount = fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND expiry_date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)")['count'];
?>

<!-- Alert Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $outOfStockCount; ?></h4>
                        <p class="card-text">Out of Stock</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $lowStockCount; ?></h4>
                        <p class="card-text">Low Stock</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $expiredCount; ?></h4>
                        <p class="card-text">Expired</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-x-octagon" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $nearExpiryCount; ?></h4>
                        <p class="card-text">Near Expiry</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label for="alert_type" class="form-label">Alert Type</label>
                <select class="form-select" id="alert_type" name="alert_type">
                    <option value="">All Alerts</option>
                    <option value="out_of_stock" <?php echo $alertType === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                    <option value="low_stock" <?php echo $alertType === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="expired" <?php echo $alertType === 'expired' ? 'selected' : ''; ?>>Expired</option>
                    <option value="near_expiry" <?php echo $alertType === 'near_expiry' ? 'selected' : ''; ?>>Near Expiry</option>
                </select>
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
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="stock-alerts.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Alerts Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Stock Alerts</h5>
        <div>
            <button class="btn btn-sm btn-outline-success" onclick="exportAlerts()">
                <i class="bi bi-download"></i> Export CSV
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($alerts)): ?>
            <div class="text-center py-4">
                <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No stock alerts found</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Current Stock</th>
                            <th>Reorder Level</th>
                            <th>Selling Price</th>
                            <th>Expiry Date</th>
                            <th>Alert Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($alert['name']); ?></strong>
                                    <?php if ($alert['batch_number']): ?>
                                        <br><small class="text-muted">Batch: <?php echo htmlspecialchars($alert['batch_number']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($alert['category_name']); ?></td>
                                <td><?php echo $alert['supplier_name'] ? htmlspecialchars($alert['supplier_name']) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($alert['quantity_in_stock'] == 0): ?>
                                        <span class="badge bg-danger">0</span>
                                    <?php elseif ($alert['quantity_in_stock'] <= $alert['reorder_level']): ?>
                                        <span class="badge bg-warning"><?php echo $alert['quantity_in_stock']; ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?php echo $alert['quantity_in_stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $alert['reorder_level']; ?></td>
                                <td><?php echo formatCurrency($alert['selling_price']); ?></td>
                                <td>
                                    <?php if ($alert['expiry_date']): ?>
                                        <?php 
                                        $daysUntil = $alert['days_until_expiry'];
                                        $expiryClass = '';
                                        if ($daysUntil < 0) {
                                            $expiryClass = 'text-danger';
                                        } elseif ($daysUntil <= 7) {
                                            $expiryClass = 'text-warning';
                                        } elseif ($daysUntil <= 30) {
                                            $expiryClass = 'text-info';
                                        }
                                        ?>
                                        <span class="<?php echo $expiryClass; ?>">
                                            <?php echo formatDate($alert['expiry_date']); ?>
                                            <?php if ($daysUntil < 0): ?>
                                                <br><small class="text-danger">Expired <?php echo abs($daysUntil); ?> days ago</small>
                                            <?php elseif ($daysUntil <= 30): ?>
                                                <br><small class="text-warning">Expires in <?php echo $daysUntil; ?> days</small>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo getAlertBadge($alert['alert_status']); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewProductDetails(<?php echo $alert['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($alert['quantity_in_stock'] > 0): ?>
                                            <button class="btn btn-outline-success" onclick="createPurchaseForProduct(<?php echo $alert['id']; ?>)">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <?php echo buildPagination($pagination, 'stock-alerts.php?' . http_build_query(['alert_type' => $alertType, 'category' => $category])); ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="productDetailsBody">
                <!-- Content will be populated via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
function viewProductDetails(productId) {
    fetch('<?php echo BASE_URL; ?>/manager/get_product.php?id=' + productId)
        .then(response => response.json())
        .then(data => {
            const modalBody = document.getElementById('productDetailsBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${data.name}</td></tr>
                            <tr><td><strong>Category:</strong></td><td>${data.category_name || 'N/A'}</td></tr>
                            <tr><td><strong>Supplier:</strong></td><td>${data.supplier_name || 'N/A'}</td></tr>
                            <tr><td><strong>Barcode:</strong></td><td>${data.barcode || 'N/A'}</td></tr>
                            <tr><td><strong>Batch Number:</strong></td><td>${data.batch_number || 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Stock & Pricing</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Current Stock:</strong></td><td>${data.quantity_in_stock}</td></tr>
                            <tr><td><strong>Reorder Level:</strong></td><td>${data.reorder_level}</td></tr>
                            <tr><td><strong>Cost Price:</strong></td><td>$${data.cost_price}</td></tr>
                            <tr><td><strong>Selling Price:</strong></td><td>$${data.selling_price}</td></tr>
                            <tr><td><strong>Expiry Date:</strong></td><td>${data.expiry_date || 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                ${data.description ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Description</h6>
                        <p>${data.description}</p>
                    </div>
                </div>
                ` : ''}
            `;
            new bootstrap.Modal(document.getElementById('productDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading product details');
        });
}

function createPurchaseForProduct(productId) {
    // Redirect to purchases page with pre-selected product
    window.location.href = 'purchases.php?product_id=' + productId;
}

function exportAlerts() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'csv');
    window.open(url.toString(), '_blank');
}
</script>

<?php 
// Handle CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportData = [];
    $headers = ['Product Name', 'Category', 'Supplier', 'Current Stock', 'Reorder Level', 'Cost Price', 'Selling Price', 'Expiry Date', 'Alert Status', 'Batch Number'];
    
    foreach ($alerts as $alert) {
        $exportData[] = [
            $alert['name'],
            $alert['category_name'],
            $alert['supplier_name'] ?? 'N/A',
            $alert['quantity_in_stock'],
            $alert['reorder_level'],
            $alert['cost_price'],
            $alert['selling_price'],
            $alert['expiry_date'] ?? 'N/A',
            $alert['alert_status'],
            $alert['batch_number'] ?? 'N/A'
        ];
    }
    
    exportToCSV($exportData, 'stock_alerts_' . date('Y-m-d') . '.csv', $headers);
}
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
