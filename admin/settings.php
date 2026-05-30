<?php
/**
 * System Settings
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/settings.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'System Settings';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_settings') {
        // Debug database connection
        if (!$pdo) {
            $_SESSION['flash_message'] = 'Database connection error. Please try again.';
            $_SESSION['flash_type'] = 'danger';
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // General settings
        $generalSettings = [
            'store_name' => $_POST['store_name'] ?? '',
            'default_currency' => $_POST['default_currency'] ?? '1',
            'items_per_page' => $_POST['items_per_page'] ?? '20',
            'company_address' => $_POST['company_address'] ?? '',
            'company_phone' => $_POST['company_phone'] ?? '',
            'company_email' => $_POST['company_email'] ?? '',
            'tax_rate' => $_POST['tax_rate'] ?? '0',
            'enable_tax' => isset($_POST['enable_tax']) ? '1' : '0',
            'timezone' => $_POST['timezone'] ?? 'UTC',
            'date_format' => $_POST['date_format'] ?? 'Y-m-d',
            'time_format' => $_POST['time_format'] ?? 'H:i:s',
            'decimal_places' => $_POST['decimal_places'] ?? '2',
            'thousands_separator' => $_POST['thousands_separator'] ?? ',',
            'decimal_separator' => $_POST['decimal_separator'] ?? '.'
        ];
        
        // Receipt settings
        $receiptSettings = [
            'receipt_header' => $_POST['receipt_header'] ?? 'Thank you for your business!',
            'receipt_footer' => $_POST['receipt_footer'] ?? 'Visit us again!'
        ];
        
        // Inventory settings
        $inventorySettings = [
            'low_stock_threshold' => $_POST['low_stock_threshold'] ?? '10',
            'expiry_warning_days' => $_POST['expiry_warning_days'] ?? '30'
        ];
        
        // Notification settings
        $notificationSettings = [
            'enable_email_notifications' => isset($_POST['enable_email_notifications']) ? '1' : '0'
        ];
        
        // Backup settings
        $backupSettings = [
            'backup_retention_days' => $_POST['backup_retention_days'] ?? '30',
            'enable_auto_backup' => isset($_POST['enable_auto_backup']) ? '1' : '0'
        ];
        
        // Update all settings
        $allSettings = array_merge($generalSettings, $receiptSettings, $inventorySettings, $notificationSettings, $backupSettings);
        
        $success = true;
        $updateErrors = [];
        
        foreach ($allSettings as $key => $value) {
            if (!updateSetting($key, $value)) {
                $success = false;
                $updateErrors[] = "Failed to update setting: $key";
                break;
            }
        }
        
        if ($success) {
            $_SESSION['flash_message'] = 'Settings updated successfully!';
            $_SESSION['flash_type'] = 'success';
            logActivity('Settings Updated', 'System settings were modified');
            
            // Redirect to prevent form resubmission and refresh data
            header('Location: ' . $_SERVER['PHP_SELF'] . '?updated=' . time());
            exit;
        } else {
            $_SESSION['flash_message'] = 'Error updating settings: ' . implode(', ', $updateErrors);
            $_SESSION['flash_type'] = 'danger';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Handle sales erasure
    elseif ($action === 'erase_sales') {
        $adminPassword = $_POST['admin_password'] ?? '';
        
        if (empty($adminPassword)) {
            $_SESSION['flash_message'] = 'Admin password is required';
            $_SESSION['flash_type'] = 'danger';
        } else {
            // Verify admin password - fetch user with password hash from database
            $currentUser = getCurrentUser();
            if ($currentUser) {
                try {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$currentUser['id']]);
                    $userWithPassword = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userWithPassword && password_verify($adminPassword, $userWithPassword['password'])) {
                try {
                    // Delete all sales records
                    $pdo->beginTransaction();
                    
                    // Delete sales items first (foreign key constraint)
                    $pdo->exec("DELETE FROM sale_items");
                    
                    // Delete sales records
                    $deletedCount = $pdo->exec("DELETE FROM sales");
                    
                    $pdo->commit();
                    
                    $_SESSION['flash_message'] = "Successfully erased {$deletedCount} sales records";
                    $_SESSION['flash_type'] = 'success';
                    
                    logActivity('Sales Records Erased', "Admin erased {$deletedCount} sales records");
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $_SESSION['flash_message'] = 'Error erasing sales records: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                }
                    } else {
                        $_SESSION['flash_message'] = 'Invalid admin password';
                        $_SESSION['flash_type'] = 'danger';
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_message'] = 'Database error: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = 'User not found';
                $_SESSION['flash_type'] = 'danger';
            }
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Handle database reset
    elseif ($action === 'reset_database') {
        $adminPassword = $_POST['reset_admin_password'] ?? '';
        
        if (empty($adminPassword)) {
            $_SESSION['flash_message'] = 'Admin password is required';
            $_SESSION['flash_type'] = 'danger';
        } else {
            // Verify admin password - fetch user with password hash from database
            $currentUser = getCurrentUser();
            if ($currentUser) {
                try {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$currentUser['id']]);
                    $userWithPassword = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userWithPassword && password_verify($adminPassword, $userWithPassword['password'])) {
                        try {
                            $pdo->beginTransaction();
                            
                            // Delete all data in correct order to respect foreign key constraints
                            // Target the exact tables in your database
                            $tablesToDelete = [
                                'sale_items',      // Delete sale items first (child table)
                                'sales',           // Then sales (parent table)
                                'products',        // Then products (main table)
                                'activity_logs',   // Then activity logs
                                'customers'        // Finally customers
                            ];
                            
                            // Handle special tables that are views or have restrictions
                            $specialTables = [
                                'sales_summary' => 'TRUNCATE TABLE sales_summary',  // Use TRUNCATE for non-updatable tables
                                'stock_alerts' => 'DROP VIEW IF EXISTS stock_alerts'  // Drop and recreate view
                            ];
                            
                            $deletedCounts = [];
                            
                            // Handle regular tables first
                            foreach ($tablesToDelete as $table) {
                                try {
                                    // Check if table exists using direct query
                                    $result = $pdo->query("SHOW TABLES LIKE '$table'");
                                    if ($result->rowCount() > 0) {
                                        $count = $pdo->exec("DELETE FROM $table");
                                        $deletedCounts[$table] = $count;
                                    } else {
                                        $deletedCounts[$table] = 'Table not found';
                                    }
                                } catch (Exception $e) {
                                    // Table doesn't exist or other error, skip it
                                    $deletedCounts[$table] = 'Error: ' . $e->getMessage();
                                }
                            }
                            
                            // Handle special tables
                            foreach ($specialTables as $table => $command) {
                                try {
                                    // Check if table/view exists
                                    $result = $pdo->query("SHOW TABLES LIKE '$table'");
                                    if ($result->rowCount() > 0) {
                                        $pdo->exec($command);
                                        $deletedCounts[$table] = 'Cleared/Recreated';
                                        
                                        // Recreate stock_alerts view if it was dropped
                                        if ($table === 'stock_alerts') {
                                            $pdo->exec("
                                                CREATE VIEW stock_alerts AS
                                                SELECT 
                                                    p.id,
                                                    p.name,
                                                    p.quantity_in_stock,
                                                    p.reorder_level,
                                                    p.expiry_date,
                                                    c.name as category_name,
                                                    CASE 
                                                        WHEN p.quantity_in_stock <= p.reorder_level THEN 'Low Stock'
                                                        WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) THEN 'Expiring Soon'
                                                        ELSE 'OK'
                                                    END as alert_status
                                                FROM products p
                                                LEFT JOIN categories c ON p.category_id = c.id
                                                WHERE p.is_active = 1
                                                AND (p.quantity_in_stock <= p.reorder_level OR p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY))
                                            ");
                                            $deletedCounts[$table] = 'Recreated view';
                                        }
                                    } else {
                                        $deletedCounts[$table] = 'Table not found';
                                    }
                                } catch (Exception $e) {
                                    $deletedCounts[$table] = 'Error: ' . $e->getMessage();
                                }
                            }
                            
                            // Recreate essential currency data (TSh) - use INSERT IGNORE to avoid duplicates
                            $pdo->exec("INSERT IGNORE INTO currencies (id, code, name, symbol, exchange_rate, is_active, is_default, created_at, updated_at) 
                                       VALUES (1, 'TZS', 'Tanzanian Shilling', 'TSh', 1.000000, TRUE, TRUE, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP())");
                            
                            // Ensure TSh is set as default and active
                            $pdo->exec("UPDATE currencies SET is_default = TRUE, is_active = TRUE WHERE code = 'TZS'");
                            
                            $pdo->commit();
                            
                            $_SESSION['flash_message'] = "Database reset completed. Deleted records: " . implode(', ', array_map(function($table, $count) {
                                return "$table ($count)";
                            }, array_keys($deletedCounts), $deletedCounts));
                            $_SESSION['flash_type'] = 'success';
                            
                            logActivity('Database Reset', "Admin performed complete database reset");
                            
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            $_SESSION['flash_message'] = 'Error resetting database: ' . $e->getMessage();
                            $_SESSION['flash_type'] = 'danger';
                        }
                    } else {
                        $_SESSION['flash_message'] = 'Invalid admin password';
                        $_SESSION['flash_type'] = 'danger';
                    }
                } catch (Exception $e) {
                    $_SESSION['flash_message'] = 'Database error: ' . $e->getMessage();
                    $_SESSION['flash_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = 'User not found';
                $_SESSION['flash_type'] = 'danger';
            }
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Check for update parameter to refresh data
if (isset($_GET['updated'])) {
    // Clear the update parameter and reload fresh data
    header('Location: ' . strtok($_SERVER['PHP_SELF'], '?') . '?');
    exit;
}

// Get current settings
$generalSettings = getSettingsByGroup('general');
$receiptSettings = getSettingsByGroup('receipt');
$inventorySettings = getSettingsByGroup('inventory');
$notificationSettings = getSettingsByGroup('notifications');
$backupSettings = getSettingsByGroup('backup');

// Get currencies for dropdown
$currencies = fetchAll("SELECT id, code, name, symbol FROM currencies WHERE is_active = 1 ORDER BY code ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>System Settings</h1>
            <div>
                <span class="badge bg-success me-2">Administrator</span>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- General Settings -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-gear me-2"></i>
                    General Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="store_name" class="form-label">Store Name</label>
                                <input type="text" class="form-control" id="store_name" name="store_name" 
                                       value="<?php echo htmlspecialchars($generalSettings['store_name'] ?? APP_NAME); ?>" 
                                       required>
                                <small class="text-muted">Business name displayed throughout system</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="default_currency" class="form-label">Default Currency</label>
                                <select class="form-select" id="default_currency" name="default_currency" required>
                                    <?php foreach ($currencies as $currency): ?>
                                        <option value="<?php echo $currency['id']; ?>" 
                                                <?php echo ($generalSettings['default_currency'] ?? 1) == $currency['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($currency['code']); ?> - <?php echo htmlspecialchars($currency['name']); ?> (<?php echo htmlspecialchars($currency['symbol']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Default currency for new records</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="items_per_page" class="form-label">Items Per Page</label>
                                <input type="number" class="form-control" id="items_per_page" name="items_per_page" 
                                       value="<?php echo htmlspecialchars($generalSettings['items_per_page'] ?? 20); ?>" 
                                       min="5" max="100" required>
                                <small class="text-muted">Number of items in lists</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <?php
                                    $timezones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo', 'Africa/Nairobi'];
                                    foreach ($timezones as $tz) {
                                        echo '<option value="' . $tz . '"' . (($generalSettings['timezone'] ?? 'UTC') === $tz ? 'selected' : '') . '>' . $tz . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted">System timezone</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="date_format" class="form-label">Date Format</label>
                                <select class="form-select" id="date_format" name="date_format">
                                    <?php
                                    $dateFormats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd.m.Y'];
                                    foreach ($dateFormats as $format) {
                                        echo '<option value="' . $format . '"' . (($generalSettings['date_format'] ?? 'Y-m-d') === $format ? 'selected' : '') . '>' . date($format) . '</option>';
                                    }
                                    ?>
                                </select>
                                <small class="text-muted">Date display format</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Company Address</label>
                                <textarea class="form-control" id="company_address" name="company_address" rows="3"><?php echo htmlspecialchars($generalSettings['company_address'] ?? ''); ?></textarea>
                                <small class="text-muted">Address for receipts and documents</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="company_phone" class="form-label">Company Phone</label>
                                <input type="tel" class="form-control" id="company_phone" name="company_phone" 
                                       value="<?php echo htmlspecialchars($generalSettings['company_phone'] ?? ''); ?>">
                                <small class="text-muted">Phone for receipts</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="company_email" class="form-label">Company Email</label>
                                <input type="email" class="form-control" id="company_email" name="company_email" 
                                       value="<?php echo htmlspecialchars($generalSettings['company_email'] ?? ''); ?>">
                                <small class="text-muted">Email for receipts</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control" id="tax_rate" name="tax_rate" 
                                       value="<?php echo htmlspecialchars($generalSettings['tax_rate'] ?? 0); ?>" 
                                       min="0" max="100" step="0.01">
                                <small class="text-muted">Tax percentage</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="enable_tax" name="enable_tax" 
                                           <?php echo ($generalSettings['enable_tax'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_tax">Enable Tax</label>
                                </div>
                                <small class="text-muted">Apply tax on sales</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="decimal_places" class="form-label">Decimal Places</label>
                                <input type="number" class="form-control" id="decimal_places" name="decimal_places" 
                                       value="<?php echo htmlspecialchars($generalSettings['decimal_places'] ?? 2); ?>" 
                                       min="0" max="4">
                                <small class="text-muted">Currency decimal places</small>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="thousands_separator" class="form-label">Thousands Separator</label>
                                <input type="text" class="form-control" id="thousands_separator" name="thousands_separator" 
                                       value="<?php echo htmlspecialchars($generalSettings['thousands_separator'] ?? ','); ?>" 
                                       maxlength="1">
                                <small class="text-muted">Number formatting</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Settings -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-receipt me-2"></i>
                    Receipt Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <label for="receipt_header" class="form-label">Receipt Header</label>
                        <textarea class="form-control" id="receipt_header" name="receipt_header" rows="3"><?php echo htmlspecialchars($receiptSettings['receipt_header'] ?? 'Thank you for your business!'); ?></textarea>
                        <small class="text-muted">Text printed at top of receipts</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="receipt_footer" class="form-label">Receipt Footer</label>
                        <textarea class="form-control" id="receipt_footer" name="receipt_footer" rows="3"><?php echo htmlspecialchars($receiptSettings['receipt_footer'] ?? 'Visit us again!'); ?></textarea>
                        <small class="text-muted">Text printed at bottom of receipts</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save me-2"></i>
                            Save Receipt Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Inventory Settings -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-box-seam me-2"></i>
                    Inventory Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" 
                               value="<?php echo htmlspecialchars($inventorySettings['low_stock_threshold'] ?? 10); ?>" 
                               min="0" required>
                        <small class="text-muted">Alert when stock falls below this quantity</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiry_warning_days" class="form-label">Expiry Warning Days</label>
                        <input type="number" class="form-control" id="expiry_warning_days" name="expiry_warning_days" 
                               value="<?php echo htmlspecialchars($inventorySettings['expiry_warning_days'] ?? 30); ?>" 
                               min="1" required>
                        <small class="text-muted">Warn when products expire within this many days</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-check me-2"></i>
                            Save Inventory Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Notification Settings -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bell me-2"></i>
                    Notification Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="enable_email_notifications" name="enable_email_notifications" 
                                   <?php echo ($notificationSettings['enable_email_notifications'] ?? false) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_email_notifications">Enable Email Notifications</label>
                        </div>
                        <small class="text-muted">Send email notifications for system events</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-save me-2"></i>
                            Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Backup Settings -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Backup Settings
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <label for="backup_retention_days" class="form-label">Backup Retention Days</label>
                        <input type="number" class="form-control" id="backup_retention_days" name="backup_retention_days" 
                               value="<?php echo htmlspecialchars($backupSettings['backup_retention_days'] ?? 30); ?>" 
                               min="1" required>
                        <small class="text-muted">Number of days to keep backups</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="enable_auto_backup" name="enable_auto_backup" 
                                   <?php echo ($backupSettings['enable_auto_backup'] ?? false) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="enable_auto_backup">Enable Auto Backup</label>
                        </div>
                        <small class="text-muted">Automatically backup database daily</small>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-save me-2"></i>
                            Save Backup Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Logo Management Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">📷 Logo Management</h5>
                <a href="<?php echo BASE_URL; ?>/admin/logo.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-image"></i> Advanced Logo Settings
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Current Logo</label>
                            <div class="d-flex align-items-center">
                                <?php if (hasLogo()): ?>
                                    <img src="<?php echo getLogoUrl(); ?>" alt="Current Logo" 
                                         style="max-width: 100px; max-height: 60px; border: 1px solid #ddd; border-radius: 4px;">
                                    <span class="ms-3">
                                        <small class="text-success">✅ Logo uploaded</small><br>
                                        <a href="<?php echo BASE_URL; ?>/admin/logo.php?action=delete" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to remove the logo?')">
                                            <i class="bi bi-trash"></i> Remove
                                        </a>
                                    </span>
                                <?php else: ?>
                                    <div class="text-center p-3 border border-dashed rounded" style="min-height: 80px;">
                                        <i class="bi bi-image text-muted" style="font-size: 2rem;"></i><br>
                                        <small class="text-muted">No logo uploaded</small>
                                    </div>
                                    <span class="ms-3">
                                        <a href="<?php echo BASE_URL; ?>/admin/logo.php" class="btn btn-primary">
                                            <i class="bi bi-upload"></i> Upload Logo
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted">Logo displayed in header and on receipts. Max size: <?php echo (LOGO_MAX_SIZE / 1024 / 1024); ?>MB</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Management Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">⚠️ Data Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Erase Daily Sales Records</label>
                                <p class="text-muted">
                                    <strong>⚠️ Warning:</strong> This action will permanently delete all daily sales records from the database. 
                                    This action cannot be undone and will affect all sales reports and analytics.
                                </p>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Before proceeding:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Make sure you have backed up important data</li>
                                        <li>Confirm this is necessary for your business operations</li>
                                        <li>Understand this cannot be undone</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password Confirmation</label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" 
                                       placeholder="Enter your admin password" required>
                                <small class="text-muted">Enter your password to confirm this action</small>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-danger" id="eraseSalesBtn" onclick="confirmEraseSales()">
                                    <i class="bi bi-trash"></i> Erase Daily Sales Records
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Database Reset Section -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="bi bi-database-x"></i> Database Reset
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Complete Database Reset</label>
                    <p class="text-muted">
                        <strong>⚠️ EXTREME WARNING:</strong> This action will permanently delete ALL stock and sales records from the database, 
                        including products, purchases, sales, inventory data, and related records. Only user accounts and system settings will be preserved.
                        This action cannot be undone and will reset your entire inventory system to a clean state.
                    </p>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>This will delete:</strong>
                        <ul class="mb-0 mt-2">
                            <li>All products and inventory records</li>
                            <li>All sales records and sale items</li>
                            <li>All sales summary data</li>
                            <li>All stock alerts</li>
                            <li>All customer records</li>
                            <li>All activity logs</li>
                            <li>All currency data (will be recreated)</li>
                        </ul>
                        <hr class="my-2">
                        <strong>This will preserve:</strong>
                        <ul class="mb-0 mt-2">
                            <li>All user accounts and roles</li>
                            <li>System settings and configurations</li>
                            <li>Database structure (tables will remain)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="reset_admin_password" class="form-label">Admin Password Confirmation</label>
                    <input type="password" class="form-control" id="reset_admin_password" name="reset_admin_password" 
                           placeholder="Enter your admin password" required>
                    <small class="text-muted">Enter your password to confirm this action</small>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-danger" id="resetDbBtn" onclick="confirmDatabaseReset()">
                        <i class="bi bi-database-x"></i> Reset Database
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Sales Erasure -->
<script>
function confirmEraseSales() {
    const password = document.getElementById('admin_password').value;
    
    if (!password) {
        alert('Please enter your admin password to confirm this action.');
        return;
    }
    
    const confirmMessage = `⚠️ DANGER: This will permanently delete ALL sales records!\n\n` +
        `This action cannot be undone and will affect:\n` +
        `• All sales reports and analytics\n` +
        `• Daily revenue tracking\n` +
        `• Product sales history\n` +
        `• Customer purchase records\n\n` +
        `Are you absolutely sure you want to proceed?`;
    
    if (confirm(confirmMessage)) {
        const finalConfirm = confirm(`Final confirmation: Type "ERASE" to confirm deletion of all sales records.`);
        
        if (finalConfirm) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'erase_sales';
            form.appendChild(actionInput);
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'admin_password';
            passwordInput.value = password;
            form.appendChild(passwordInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}

function confirmDatabaseReset() {
    const password = document.getElementById('reset_admin_password').value;
    
    if (!password) {
        alert('Please enter your admin password to confirm this action.');
        return;
    }
    
    const confirmMessage = `⚠️ EXTREME DANGER: This will permanently delete ALL stock and sales records!\n\n` +
        `This action cannot be undone and will completely reset your inventory system.\n\n` +
        `This will delete:\n` +
        `• All products and inventory records\n` +
        `• All sales records and sale items\n` +
        `• All sales summary data\n` +
        `• All stock alerts\n` +
        `• All customer records\n` +
        `• All activity logs\n` +
        `• All currency data (will be recreated)\n\n` +
        `This will preserve:\n` +
        `• All user accounts and roles\n` +
        `• System settings and configurations\n` +
        `• Database structure (tables will remain)\n\n` +
        `Are you absolutely sure you want to proceed?`;
    
    if (confirm(confirmMessage)) {
        const finalConfirm = confirm(`FINAL WARNING: This will reset your entire inventory system to a clean state!\n\n` +
            `Type "RESET" to confirm complete database reset.`);
        
        if (finalConfirm) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = window.location.href;
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'reset_database';
            form.appendChild(actionInput);
            
            const passwordInput = document.createElement('input');
            passwordInput.type = 'hidden';
            passwordInput.name = 'reset_admin_password';
            passwordInput.value = password;
            form.appendChild(passwordInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
