<?php
/**
 * Manager Dashboard
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAuth();
requireRole('Manager');

$pageTitle = 'Manager Dashboard';
require_once __DIR__ . '/../includes/header.php';

// Get current user data
$currentUser = getCurrentUser();

// Get dashboard statistics
$stats = [
    'total_sales' => fetchRow("SELECT COUNT(*) as count FROM sales"),
    'total_products' => fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1"),
    'total_customers' => fetchRow("SELECT COUNT(*) as count FROM customers"),
    'low_stock_products' => fetchRow("SELECT COUNT(*) as count FROM products WHERE quantity_in_stock <= reorder_level AND is_active = 1"),
    'total_revenue' => fetchRow("SELECT COALESCE(SUM(final_amount), 0) as total FROM sales"),
    'today_sales' => fetchRow("SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURRENT_DATE()"),
    'today_revenue' => fetchRow("SELECT COALESCE(SUM(final_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURRENT_DATE()"),
];

// Get today's sales for this manager
$todaySales = fetchAll("SELECT s.*, u.full_name as cashier_name, s.customer_name as customer_name FROM sales s 
                    JOIN users u ON s.created_by = u.id 
                    WHERE DATE(s.sale_date) = CURRENT_DATE() 
                    ORDER BY s.sale_date DESC LIMIT 10");

// Get low stock items for this manager
$lowStockItems = fetchAll("SELECT p.name, p.quantity_in_stock, p.reorder_level, p.cost_price FROM products p 
                        WHERE p.quantity_in_stock <= p.reorder_level AND p.is_active = 1 
                        ORDER BY p.quantity_in_stock ASC LIMIT 5");

// Get top selling products for this manager
$topProducts = fetchAll("SELECT p.name, COUNT(si.quantity) as sold_count, SUM(si.quantity) as total_sold FROM sale_items si 
                        JOIN products p ON si.product_id = p.id 
                        JOIN sales s ON si.sale_id = s.id 
                        GROUP BY p.id 
                        ORDER BY total_sold DESC 
                        LIMIT 5");

// Get recent sales
$recentSales = fetchAll("SELECT s.*, u.full_name as cashier_name, s.customer_name as customer_name FROM sales s 
                    JOIN users u ON s.created_by = u.id 
                    ORDER BY s.sale_date DESC LIMIT 5");
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Manager Dashboard</h1>
            <div class="text-muted">
                <small>Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>!</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Stats -->
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h4><?php echo number_format($stats['total_sales']['count']); ?></h4>
                            <small>Total Sales</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h4><?php echo number_format($stats['total_products']['count']); ?></h4>
                            <small>Total Products</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h4><?php echo $stats['total_customers']['count']; ?></h4>
                            <small>Total Customers</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="text-center">
                            <h4><?php echo $stats['low_stock_products']['count']; ?></h4>
                            <small>Low Stock Items</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue Overview -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Revenue Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Total Revenue</h3>
                        <h2>TSh <?php echo number_format($stats['total_revenue']['total'], 2); ?></h2>
                    </div>
                    <div class="col-md-6">
                        <h3>Today's Revenue</h3>
                        <h2>TSh <?php echo number_format($stats['today_revenue']['total'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Sales -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Sales</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Cashier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get recent sales
                            $recentSales = fetchAll("SELECT s.*, u.full_name as cashier_name, s.customer_name as customer_name FROM sales s 
                                JOIN users u ON s.created_by = u.id 
                                ORDER BY s.sale_date DESC LIMIT 5");
                            
                            foreach ($recentSales as $sale):
                            ?>
                            <tr>
                                <td>#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo formatDateTime($sale['sale_date']); ?></td>
                                <td><?php echo htmlspecialchars($sale['customer_name'] ?? 'Walk-in'); ?></td>
                                <td>TSh <?php echo number_format($sale['final_amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($sale['cashier_name']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/reports/sales.php" class="btn btn-primary w-100 py-3">
                            <i class="bi bi-graph-up"></i><br>
                            <strong>Sales Reports</strong>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/reports/inventory.php" class="btn btn-info w-100 py-3">
                            <i class="bi bi-box-seam"></i><br>
                            <strong>Inventory Reports</strong>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/manager/products.php" class="btn btn-success w-100 py-3">
                            <i class="bi bi-box"></i><br>
                            <strong>Products</strong>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="<?php echo BASE_URL; ?>/manager/stock_reorder.php" class="btn btn-warning w-100 py-3">
                            <i class="bi bi-arrow-repeat"></i><br>
                            <strong>Stock Reorder</strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
