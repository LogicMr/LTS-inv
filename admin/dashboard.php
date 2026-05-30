<?php
/**
 * Admin Dashboard
 * Inventory Management System
 */
require_once __DIR__ . '/../includes/header.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'Admin Dashboard';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <div>
                <span class="badge bg-success me-2">Administrator</span>
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
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo fetchRow("SELECT COUNT(*) as count FROM users")['count']; ?></h4>
                        <p class="card-text">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?php echo fetchRow("SELECT COUNT(*) as count FROM products")['count']; ?></h4>
                        <p class="card-text">Total Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-box fs-1"></i>
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
                        <h4 class="card-title"><?php echo fetchRow("SELECT COUNT(*) as count FROM suppliers")['count']; ?></h4>
                        <p class="card-text">Suppliers</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-truck fs-1"></i>
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
                        <h4 class="card-title"><?php echo fetchRow("SELECT COUNT(*) as count FROM sales WHERE DATE(sale_date) = CURRENT_DATE()")['count']; ?></h4>
                        <p class="card-text">Today's Sales</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">TSh <?php echo number_format(fetchRow("SELECT COALESCE(SUM(final_amount), 0) as total FROM sales")['total'], 2); ?></h4>
                        <p class="card-text">Total Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash-stack fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">TSh <?php echo number_format(fetchRow("SELECT COALESCE(SUM(final_amount), 0) as total FROM sales WHERE DATE(sale_date) = CURRENT_DATE()")['total'], 2); ?></h4>
                        <p class="card-text">Today's Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-graph-up fs-1"></i>
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
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/users.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus"></i> Add User
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/suppliers.php" class="btn btn-outline-success w-100">
                            <i class="bi bi-truck"></i> Add Supplier
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/categories.php" class="btn btn-outline-warning w-100">
                            <i class="bi bi-tags"></i> Add Category
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?php echo BASE_URL; ?>/admin/logo.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-image"></i> Manage Logo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Backup Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Database Backup Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Create New Backup</h6>
                            <p class="text-muted small">Download complete database backup</p>
                            <form method="POST" action="backup_export.php">
                                <input type="hidden" name="action" value="export">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-download"></i> Export Backup
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Import Backup</h6>
                            <p class="text-muted small">Restore database from backup file</p>
                            <form method="POST" action="backup_import.php" enctype="multipart/form-data" class="mt-2">
                                <input type="hidden" name="action" value="import">
                                <div class="mb-2">
                                    <input type="file" name="backup_file" class="form-control" accept=".sql" required>
                                </div>
                                <button type="submit" class="btn btn-warning" onclick="return confirm('WARNING: This will replace all current data. Are you sure?')">
                                    <i class="bi bi-upload"></i> Import Backup
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h6>Quick Backup</h6>
                            <p class="text-muted small">Instant backup to server</p>
                            <form method="POST" action="backup_export.php">
                                <input type="hidden" name="action" value="quick_backup">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-shield-check"></i> Quick Backup
                                </button>
                            </form>
                            <?php if (file_exists(__DIR__ . '/../backups/quick_backup.sql')): ?>
                                <small class="text-success d-block mt-2">
                                    <i class="bi bi-check-circle"></i> Quick backup available
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Important:</strong> Always create a backup before making major changes. 
                    Importing a backup will replace all existing data.
                </div>
                
                <!-- Backup History -->
                <div class="mt-4">
                    <h6>Recent Backups</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Backup Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $backupDir = __DIR__ . '/../backups';
                                $backups = [];
                                
                                // Check for quick backup
                                if (file_exists($backupDir . '/quick_backup.sql')) {
                                    $backups[] = [
                                        'type' => 'Quick Backup',
                                        'date' => date('Y-m-d H:i:s', filemtime($backupDir . '/quick_backup.sql')),
                                        'file' => 'quick_backup.sql',
                                        'size' => filesize($backupDir . '/quick_backup.sql')
                                    ];
                                }
                                
                                // Check for other backup files
                                if (is_dir($backupDir)) {
                                    $files = glob($backupDir . '/backup_*.sql');
                                    foreach ($files as $file) {
                                        $backups[] = [
                                            'type' => 'Manual Backup',
                                            'date' => date('Y-m-d H:i:s', filemtime($file)),
                                            'file' => basename($file),
                                            'size' => filesize($file)
                                        ];
                                    }
                                }
                                
                                // Sort by date
                                usort($backups, function($a, $b) {
                                    return strtotime($b['date']) - strtotime($a['date']);
                                });
                                
                                // Display only last 5 backups
                                $backups = array_slice($backups, 0, 5);
                                
                                foreach ($backups as $backup): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($backup['type']); ?></td>
                                        <td><?php echo $backup['date']; ?></td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Available
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($backup['type'] === 'Quick Backup'): ?>
                                                <small class="text-muted">Server backup</small>
                                            <?php else: ?>
                                                <a href="backup_download.php?file=<?php echo urlencode($backup['file']); ?>" 
                                                   class="btn btn-sm btn-outline-primary" download>
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($backups)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            No backups found. Create your first backup above.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Users</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recentUsers = fetchAll("SELECT u.*, r.name as role_name 
                                                   FROM users u 
                                                   JOIN roles r ON u.role_id = r.id 
                                                   ORDER BY u.last_login DESC 
                                                   LIMIT 5");
                            foreach ($recentUsers as $user):
                            ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['full_name']; ?></td>
                                <td><span class="badge bg-secondary"><?php echo $user['role_name']; ?></span></td>
                                <td><?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">System Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p><strong>Database:</strong> MySQL</p>
                        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                        <p><strong>System Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    <div class="col-6">
                        <p><strong>Low Stock Items:</strong> 
                            <span class="badge bg-warning">
                                <?php echo fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND quantity_in_stock <= reorder_level")['count']; ?>
                            </span>
                        </p>
                        <p><strong>Expired Items:</strong> 
                            <span class="badge bg-danger">
                                <?php echo fetchRow("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND expiry_date < CURRENT_DATE")['count']; ?>
                            </span>
                        </p>
                        <p><strong>Total Sales Today:</strong> 
                            <strong>
                                <?php 
                                $todaySales = fetchRow("SELECT COUNT(*) as sales_count, COALESCE(SUM(final_amount), 0) as total, currency_id FROM sales WHERE DATE(sale_date) = CURDATE() GROUP BY currency_id ORDER BY total DESC LIMIT 1");
                                if ($todaySales) {
                                    echo formatCurrency($todaySales['total'], $todaySales['currency_id'] ?? null);
                                } else {
                                    echo formatCurrency(0);
                                }
                                ?>
                            </strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
