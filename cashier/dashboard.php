<?php
/**
 * Cashier Dashboard
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Cashier');

//$pageTitle = 'Cashier Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Get today's sales summary
$todaySales = fetchRow("SELECT COUNT(*) as total_sales, SUM(s.total_amount) as total_revenue, SUM(s.final_amount) as net_revenue
                       FROM sales s
                       WHERE DATE(s.sale_date) = CURRENT_DATE()");

// Get recent sales
$recentSales = fetchAll("SELECT s.*, u.full_name as created_by_name
                        FROM sales s 
                        JOIN users u ON s.created_by = u.id 
                        ORDER BY s.sale_date DESC LIMIT 10");

// Get top selling products today
$topProducts = fetchAll("SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.subtotal) as total_revenue
                        FROM products p
                        JOIN sale_items si ON p.id = si.product_id
                        JOIN sales s ON si.sale_id = s.id
                        WHERE DATE(s.sale_date) = CURRENT_DATE()
                        GROUP BY p.id, p.name
                        ORDER BY total_sold DESC
                        LIMIT 5");

// Get available products count
$availableProducts = fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND quantity_in_stock > 0")['count'];

// Get low stock products (cashier can see but not edit)
$lowStockProducts = fetchAll("SELECT p.name, p.quantity_in_stock, p.reorder_level, c.name as category_name
                             FROM products p
                             JOIN categories c ON p.category_id = c.id
                             WHERE p.is_active = 1 AND p.quantity_in_stock <= p.reorder_level
                             ORDER BY p.quantity_in_stock ASC
                             LIMIT 5");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Cashier Dashboard</h1>
            <div>
                <span class="badge bg-success me-2">Cashier</span>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo $todaySales['total_sales'] ?? 0; ?></h4>
                        <p class="card-text">Today's Sales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-check" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">TSh <?php echo number_format($todaySales['net_revenue'] ?? 0, 2); ?></h4>
                        <p class="card-text">Today's Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
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
                        <h4 class="card-title"><?php echo $availableProducts; ?></h4>
                        <p class="card-text">Available Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box" style="font-size: 2rem;"></i>
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
                        <h4 class="card-title"><?php echo count($lowStockProducts); ?></h4>
                        <p class="card-text">Low Stock Items</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Sales Summary -->
<?php 
$todaySales = fetchRow("SELECT COUNT(*) as sales_count, SUM(final_amount) as total_amount
                        FROM sales 
                        WHERE DATE(sale_date) = CURRENT_DATE()");
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-header">
                <h5 class="mb-0">Today's Sales Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4>TSh</h4>
                            <h5>TSh <?php echo number_format($todaySales['total_amount'] ?? 0, 2); ?></h5>
                            <small class="text-muted"><?php echo $todaySales['sales_count']; ?> sales</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <a href="pos.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-cart-plus"></i><br>
                            New Sale
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="products.php" class="btn btn-info btn-lg w-100">
                            <i class="bi bi-search"></i><br>
                            View Products
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="../auth/logout.php" class="btn btn-outline-danger btn-lg w-100">
                            <i class="bi bi-box-arrow-right"></i><br>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Recent Sales -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Sales</h5>
                <a href="../reports/sales.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentSales)): ?>
                    <p class="text-muted text-center">No sales today</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Total</th>
                                    <th>Discount</th>
                                    <th>Final Amount</th>
                                    <th>Payment</th>
                                    <th>Customer</th>
                                    <th>Cashier</th>
                                    <th>Currency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSales as $sale): ?>
                                    <tr>
                                        <td><?php echo formatDateTime($sale['sale_date'], 'H:i'); ?></td>
                                        <td><?php echo formatCurrency($sale['total_amount'], $sale['currency_id'] ?? null); ?></td>
                                        <td><?php echo formatCurrency($sale['discount_amount'], $sale['currency_id'] ?? null); ?></td>
                                        <td><strong><?php echo formatCurrency($sale['final_amount'], $sale['currency_id'] ?? null); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($sale['payment_method']); ?></span>
                                        </td>
                                        <td><?php echo $sale['customer_name'] ? htmlspecialchars($sale['customer_name']) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($sale['created_by_name']); ?></td>
                                        <?php if (!empty($sale['currency_symbol'])): ?>
                                            <td><small class="text-muted"><?php echo htmlspecialchars($sale['currency_symbol']); ?></small></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Top Products -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Products Today</h5>
            </div>
            <div class="card-body">
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted text-center">No sales today</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Units</th>
                                    <th>Revenue</th>
                                    <th>Currency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td><?php echo formatCurrency($product['total_revenue'], $product['currency_id'] ?? null); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($product['currency_symbol'] ?? 'N/A'); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<?php if (!empty($lowStockProducts)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="bi bi-exclamation-triangle"></i> Low Stock Alert
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">The following products are running low on stock. Please inform the manager.</p>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                        <td>
                                            <span class="badge bg-warning"><?php echo $product['quantity_in_stock']; ?></span>
                                        </td>
                                        <td><?php echo $product['reorder_level']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
