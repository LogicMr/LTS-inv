<?php
/**
 * Currency Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/currency.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'Currency Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_rate') {
        $currencyId = (int)$_POST['currency_id'];
        $newRate = (float)$_POST['exchange_rate'];
        
        if ($newRate > 0) {
            updateExchangeRate($currencyId, $newRate);
            $_SESSION['flash_message'] = 'Exchange rate updated successfully';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Exchange rate must be greater than 0';
            $_SESSION['flash_type'] = 'danger';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($action === 'set_default') {
        $currencyId = (int)$_POST['currency_id'];
        setDefaultCurrency($currencyId);
        
        $_SESSION['flash_message'] = 'Default currency updated successfully';
        $_SESSION['flash_type'] = 'success';
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if ($action === 'toggle_status') {
        $currencyId = (int)$_POST['currency_id'];
        $currency = getCurrency($currencyId);
        
        if ($currency && !$currency['is_default']) {
            $newStatus = !$currency['is_active'];
            executeNonQuery("UPDATE currencies SET is_active = ? WHERE id = ?", [$newStatus, $currencyId]);
            
            $statusText = $newStatus ? 'activated' : 'deactivated';
            $_SESSION['flash_message'] = "Currency {$statusText} successfully";
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Cannot deactivate default currency';
            $_SESSION['flash_type'] = 'danger';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Get all currencies
$currencies = getCurrencies();
$defaultCurrency = getDefaultCurrency();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-success me-2">Administrator</span>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Available Currencies</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th>Exchange Rate</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currencies as $currency): ?>
                                <tr>
                                    <td><strong><?php echo $currency['code']; ?></strong></td>
                                    <td><?php echo $currency['name']; ?></td>
                                    <td><?php echo $currency['symbol']; ?></td>
                                    <td>
                                        <?php if ($currency['is_default']): ?>
                                            <span class="badge bg-primary">1.000000 (Base)</span>
                                        <?php else: ?>
                                            <form method="post" class="d-inline" style="display: inline-block;">
                                                <input type="hidden" name="action" value="update_rate">
                                                <input type="hidden" name="currency_id" value="<?php echo $currency['id']; ?>">
                                                <input type="number" 
                                                       name="exchange_rate" 
                                                       value="<?php echo number_format($currency['exchange_rate'], 6); ?>" 
                                                       step="0.000001" 
                                                       min="0.000001"
                                                       class="form-control form-control-sm d-inline-block" 
                                                       style="width: 120px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary ms-1">Update</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($currency['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($currency['is_default']): ?>
                                            <span class="badge bg-warning">Default</span>
                                        <?php else: ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="set_default">
                                                <input type="hidden" name="currency_id" value="<?php echo $currency['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning">Set Default</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$currency['is_default']): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="currency_id" value="<?php echo $currency['id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $currency['is_active'] ? 'btn-outline-secondary' : 'btn-outline-success'; ?>">
                                                    <?php echo $currency['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Currency Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Default Currency:</strong> <?php echo $defaultCurrency['code']; ?> (<?php echo $defaultCurrency['name']; ?>)</p>
                <p><strong>Total Currencies:</strong> <?php echo count($currencies); ?></p>
                <p><strong>Active Currencies:</strong> <?php echo count(array_filter($currencies, fn($c) => $c['is_active'])); ?></p>
                
                <hr>
                
                <h6>How it works:</h6>
                <ul>
                    <li>All exchange rates are relative to the default currency (USD)</li>
                    <li>Products can be priced in any active currency</li>
                    <li>Sales and purchases record the currency and exchange rate used</li>
                    <li>Reports can convert all amounts to default currency for comparison</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Exchange Rate Tips</h5>
            </div>
            <div class="card-body">
                <h6>Common Exchange Rates (approximate):</h6>
                <ul>
                    <li><strong>USD:</strong> 1.000000 (base currency)</li>
                    <li><strong>TZS:</strong> ~2,315 TSh per USD</li>
                    <li><strong>KES:</strong> ~130.5 KSh per USD</li>
                    <li><strong>UGX:</strong> ~3,725 UGX per USD</li>
                    <li><strong>EUR:</strong> ~0.85 EUR per USD</li>
                    <li><strong>GBP:</strong> ~0.73 GBP per USD</li>
                </ul>
                
                <hr>
                
                <p class="text-muted">
                    <small>
                        <strong>Note:</strong> Exchange rates should be updated regularly for accurate reporting.
                        You can get current rates from financial websites or your bank.
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
