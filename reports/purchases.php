<?php
/**
 * Purchase Reports
 * Inventory Management System
 */
require_once __DIR__ . '/../includes/header.php';
requireAuth();
requireMinRole('Manager');

$pageTitle = 'Purchase Reports';

// Get filters
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-d');
$supplier_id = $_GET['supplier_id'] ?? '';

// Build query
$where_conditions = ["DATE(p.purchase_date) BETWEEN ? AND ?"];
$params = [$from_date, $to_date];

if ($supplier_id) {
    $where_conditions[] = "p.supplier_id = ?";
    $params[] = $supplier_id;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get purchases data
$sql = "SELECT p.*, s.name as supplier_name, COUNT(pi.id) as item_count,
               SUM(pi.quantity * pi.cost_price) as total_cost
        FROM purchases p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
        $where_clause
        GROUP BY p.id
        ORDER BY p.purchase_date DESC";

$purchases = fetchAll($sql, $params);

// Get summary
$summary_sql = "SELECT COUNT(*) as total_purchases,
                      SUM(COUNT(pi.id)) as total_items,
                      SUM(SUM(pi.quantity * pi.cost_price)) as total_amount
               FROM purchases p
               LEFT JOIN purchase_items pi ON p.id = pi.purchase_id
               $where_clause
               GROUP BY p.id";

$summary_result = fetchAll($summary_sql, $params);
$total_purchases = count($summary_result);
$total_amount = array_sum(array_column($summary_result, 'total_amount'));

// Get suppliers for filter
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name");
?>

<div class="row">
    <div class="col-12">
        <h1>Purchase Reports</h1>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="<?php echo $from_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="<?php echo $to_date; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_id" class="form-select">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo $supplier['id']; ?>" <?php echo $supplier_id == $supplier['id'] ? 'selected' : ''; ?>>
                        <?php echo $supplier['name']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
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
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h4><?php echo $total_purchases; ?></h4>
                <p class="mb-0">Total Purchases</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h4><?php echo formatCurrency($total_amount); ?></h4>
                <p class="mb-0">Total Amount</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h4><?php echo $total_purchases > 0 ? formatCurrency($total_amount / $total_purchases) : formatCurrency(0); ?></h4>
                <p class="mb-0">Average Purchase</p>
            </div>
        </div>
    </div>
</div>

<!-- Purchases Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice #</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                    <tr>
                        <td><?php echo formatDate($purchase['purchase_date']); ?></td>
                        <td><?php echo $purchase['invoice_number']; ?></td>
                        <td><?php echo $purchase['supplier_name'] ?: 'N/A'; ?></td>
                        <td><?php echo $purchase['item_count']; ?></td>
                        <td><?php echo formatCurrency($purchase['total_cost']); ?></td>
                        <td>
                            <span class="badge bg-success">Completed</span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
