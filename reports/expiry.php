<?php
/**
 * Expiry Reports
 * Inventory Management System
 */
require_once __DIR__ . '/../includes/header.php';
requireAuth();
requireMinRole('Manager');

$pageTitle = 'Expiry Reports';

// Get filters
$category_id = $_GET['category_id'] ?? '';
$expiry_filter = $_GET['expiry_filter'] ?? 'all'; // all, expired, near_expiry

// Build query
$where_conditions = [];
$params = [];

if ($category_id) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

switch ($expiry_filter) {
    case 'expired':
        $where_conditions[] = "p.expiry_date < CURDATE()";
        break;
    case 'near_expiry':
        $where_conditions[] = "p.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        break;
    case 'all':
    default:
        $where_conditions[] = "p.expiry_date IS NOT NULL";
        break;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get products
$sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
               DATEDIFF(p.expiry_date, CURDATE()) as days_until_expiry
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        $where_clause
        ORDER BY p.expiry_date ASC";

$products = fetchAll($sql, $params);

// Get summary
$expired_count = 0;
$near_expiry_count = 0;
$total_value = 0;

foreach ($products as $product) {
    if ($product['days_until_expiry'] < 0) {
        $expired_count++;
    } elseif ($product['days_until_expiry'] <= 30) {
        $near_expiry_count++;
    }
    $total_value += $product['quantity_in_stock'] * $product['selling_price'];
}

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name");
?>

<div class="row">
    <div class="col-12">
        <h1>Expiry Reports</h1>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category_id == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Expiry Filter</label>
                <select name="expiry_filter" class="form-select">
                    <option value="all" <?php echo $expiry_filter == 'all' ? 'selected' : ''; ?>>All Products with Expiry</option>
                    <option value="expired" <?php echo $expiry_filter == 'expired' ? 'selected' : ''; ?>>Expired Only</option>
                    <option value="near_expiry" <?php echo $expiry_filter == 'near_expiry' ? 'selected' : ''; ?>>Near Expiry (30 days)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h4><?php echo $expired_count; ?></h4>
                <p class="mb-0">Expired Products</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h4><?php echo $near_expiry_count; ?></h4>
                <p class="mb-0">Near Expiry (30 days)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h4><?php echo count($products); ?></h4>
                <p class="mb-0">Total with Expiry</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4><?php echo formatCurrency($total_value); ?></h4>
                <p class="mb-0">Total Value</p>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Batch Number</th>
                        <th>Stock</th>
                        <th>Expiry Date</th>
                        <th>Days Until Expiry</th>
                        <th>Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td><?php echo $product['batch_number'] ?: 'N/A'; ?></td>
                        <td><?php echo $product['quantity_in_stock']; ?></td>
                        <td><?php echo $product['expiry_date'] ? formatDate($product['expiry_date']) : 'N/A'; ?></td>
                        <td>
                            <?php if ($product['days_until_expiry'] < 0): ?>
                                <span class="badge bg-danger"><?php echo abs($product['days_until_expiry']); ?> days ago</span>
                            <?php elseif ($product['days_until_expiry'] <= 30): ?>
                                <span class="badge bg-warning"><?php echo $product['days_until_expiry']; ?> days</span>
                            <?php else: ?>
                                <span class="badge bg-success"><?php echo $product['days_until_expiry']; ?> days</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatCurrency($product['quantity_in_stock'] * $product['selling_price']); ?></td>
                        <td>
                            <?php if ($product['days_until_expiry'] < 0): ?>
                                <span class="badge bg-danger">Expired</span>
                            <?php elseif ($product['days_until_expiry'] <= 30): ?>
                                <span class="badge bg-warning">Near Expiry</span>
                            <?php else: ?>
                                <span class="badge bg-success">Good</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
