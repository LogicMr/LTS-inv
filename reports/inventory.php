<?php
/**
 * Inventory Reports
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

requireRole('Manager');

// Handle HTML export BEFORE any HTML output
if (isset($_GET['export']) && $_GET['export'] === 'html') {
    // Get report data (reuse same logic as main page)
    $reportType = cleanInput($_GET['report_type'] ?? 'current');
    $category = cleanInput($_GET['category'] ?? '');
    $supplier = cleanInput($_GET['supplier'] ?? '');
    
    // Build query (reuse same logic as main page)
    $where = ["p.is_active = 1"];
    $params = [];
    
    if (!empty($category)) {
        $where[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    if (!empty($supplier)) {
        $where[] = "p.supplier_id = ?";
        $params[] = $supplier;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $where);
    
    // Get report data based on type
    switch ($reportType) {
        case 'current':
            $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                    (p.quantity_in_stock * p.selling_price) as stock_value
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    $whereClause
                    ORDER BY p.name ASC";
            break;
        case 'low_stock':
            $where[] = "p.quantity_in_stock <= p.reorder_level";
            $whereClause = "WHERE " . implode(" AND ", $where);
            $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                    (p.quantity_in_stock * p.selling_price) as stock_value
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    $whereClause
                    ORDER BY p.quantity_in_stock ASC";
            break;
        case 'expiry':
            $where[] = "p.expiry_date IS NOT NULL AND p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY)";
            $whereClause = "WHERE " . implode(" AND ", $where);
            $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                    DATEDIFF(p.expiry_date, CURRENT_DATE) as days_until_expiry,
                    (p.quantity_in_stock * p.selling_price) as stock_value
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    $whereClause
                    ORDER BY p.expiry_date ASC";
            break;
        case 'valuation':
            $sql = "SELECT c.name as category_name,
                    COUNT(p.id) as product_count,
                    SUM(p.quantity_in_stock) as total_quantity,
                    SUM(p.quantity_in_stock * p.cost_price) as total_cost_value,
                    SUM(p.quantity_in_stock * p.selling_price) as total_sell_value,
                    SUM(p.quantity_in_stock * (p.selling_price - p.cost_price)) as potential_profit
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    $whereClause
                    GROUP BY c.id, c.name
                    ORDER BY total_sell_value DESC";
            break;
        default:
            $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                    (p.quantity_in_stock * p.selling_price) as stock_value
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN suppliers s ON p.supplier_id = s.id
                    $whereClause
                    ORDER BY p.name ASC";
            break;
    }
    
    $reportData = fetchAll($sql, $params);
    
    // Calculate summary statistics for export
    $totalStockCount = 0;
    $stockValuesByCurrency = [];
    
    foreach ($reportData as $row) {
        if ($reportType !== 'valuation') {
            $totalStockCount += $row['quantity_in_stock'] ?? 0;
            $currency = $row['currency_symbol'] ?? '$';
            if (!isset($stockValuesByCurrency[$currency])) {
                $stockValuesByCurrency[$currency] = 0;
            }
            $stockValuesByCurrency[$currency] += $row['stock_value'] ?? 0;
        } else {
            $totalStockCount += $row['total_quantity'] ?? 0;
            $currency = $row['currency_symbol'] ?? '$';
            if (!isset($stockValuesByCurrency[$currency])) {
                $stockValuesByCurrency[$currency] = 0;
            }
            $stockValuesByCurrency[$currency] += $row['total_sell_value'] ?? 0;
        }
    }
    
    // Format stock values for display
    $formattedStockValues = [];
    foreach ($stockValuesByCurrency as $currency => $value) {
        $formattedStockValues[] = $currency . number_format($value, 2);
    }
    $totalStockValueDisplay = implode(' + ', $formattedStockValues);
    
    // Generate HTML report
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Report - ' . ucfirst($reportType) . '</title>
    <style>
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }
        .header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }
        .header h1 { 
            margin: 0; 
            font-size: 2.5em; 
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        .header .subtitle { 
            margin: 10px 0 0 0; 
            font-size: 1.2em; 
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        .header .meta {
            margin-top: 20px;
            font-size: 0.9em;
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }
        .instructions {
            background: #e3f2fd;
            border: 1px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            margin: 30px;
            text-align: center;
        }
        .instructions h4 {
            color: #007bff;
            margin: 0 0 15px 0;
            font-size: 1.1em;
        }
        .instructions ol {
            text-align: left;
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
            color: #495057;
        }
        .return-button {
            background: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 20px 30px;
            transition: background-color 0.3s;
        }
        .return-button:hover {
            background: #0056b3;
            color: white;
            text-decoration: none;
        }
        .summary { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 30px;
            margin: 30px;
            border-radius: 10px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .summary-item {
            text-align: center;
            min-width: 150px;
        }
        .summary-item h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
            font-weight: bold;
        }
        .summary-item p {
            margin: 0;
            font-size: 0.9em;
            opacity: 0.9;
        }
        .content { 
            padding: 0 30px 30px 30px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        th { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white; 
            padding: 15px 12px; 
            text-align: left; 
            font-weight: 600;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td { 
            padding: 15px 12px; 
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        tr:nth-child(even) { background-color: #f8f9fa; }
        tr:hover { background-color: #e3f2fd; }
        .status-normal { 
            color: #28a745; 
            font-weight: bold;
            background: #d4edda;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-low { 
            color: #ffc107; 
            font-weight: bold;
            background: #fff3cd;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-out { 
            color: #dc3545; 
            font-weight: bold;
            background: #f8d7da;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-expired { 
            color: #6f42c1; 
            font-weight: bold;
            background: #e7e3f4;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-near-expiry { 
            color: #fd7e14; 
            font-weight: bold;
            background: #ffe8d6;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 0.8em;
            border-top: 1px solid #f0f0f0;
            margin: 0 30px;
        }
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-style: italic;
        }
        @media print {
            body { background: white; }
            .container { box-shadow: none; }
            .header { background: #007bff !important; -webkit-print-color-adjust: exact; }
            .summary { background: #007bff !important; -webkit-print-color-adjust: exact; }
            .instructions, .return-button { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>LTS Inventory Management System</h1>
            <div class="subtitle">Inventory Report - ' . ucfirst($reportType) . '</div>
            <div class="meta">
                Generated: ' . date('Y-m-d H:i:s') . ' | 
                System Time: ' . date('Y-m-d H:i:s T') . '
            </div>
        </div>
        
        <div class="summary">
            <div class="summary-item">
                <h3>' . number_format($totalStockCount) . '</h3>
                <p>Total Stock Count</p>
            </div>
            <div class="summary-item">
                <h3 style="font-size: 1.2em;">' . $totalStockValueDisplay . '</h3>
                <p>Total Stock Value</p>
            </div>
            <div class="summary-item">
                <h3>' . count($reportData) . '</h3>
                <p>Total ' . ($reportType === 'valuation' ? 'Categories' : 'Products') . '</p>
            </div>
        </div>
        
        <div class="instructions">
            <h4><i class="bi bi-printer"></i> Printing Instructions</h4>
            <ol>
                <li>Click the "Print" button in your browser (Ctrl+P or Cmd+P)</li>
                <li>Select "Save as PDF" as the destination</li>
                <li>Choose "A4" paper size for best results</li>
                <li>Enable "Background graphics" for colors to appear in PDF</li>
                <li>Click "Save" to generate your PDF report</li>
            </ol>
        </div>
        
        <a href="' . BASE_URL . '/manager/dashboard.php" class="return-button">
            <i class="bi bi-arrow-left"></i> Return to Dashboard
        </a>
        
        <div class="content">';
    
    if (!empty($reportData)) {
        echo '<table>
            <thead>
                <tr>';
        
        // Dynamic headers based on report type
        if ($reportType === 'valuation') {
            echo '<th>Category</th><th>Product Count</th><th>Total Quantity</th><th>Cost Value</th><th>Sell Value</th><th>Potential Profit</th>';
        } else {
            echo '<th>Product</th><th>Category</th><th>Current Stock</th><th>Cost Price</th><th>Selling Price</th><th>Stock Value</th><th>Status</th>';
        }
        
        echo '</tr>
            </thead>
            <tbody>';
        
        foreach ($reportData as $row) {
            echo '<tr>';
            
            if ($reportType === 'valuation') {
                echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                echo '<td>' . $row['product_count'] . '</td>';
                echo '<td>' . $row['total_quantity'] . '</td>';
                echo '<td>TSh ' . number_format($row['total_cost_value'], 2) . '</td>';
                echo '<td>TSh ' . number_format($row['total_sell_value'], 2) . '</td>';
                echo '<td>TSh ' . number_format($row['potential_profit'], 2) . '</td>';
            } else {
                // Determine status
                $status = 'Normal';
                $statusClass = 'status-normal';
                if ($row['quantity_in_stock'] == 0) {
                    $status = 'Out of Stock';
                    $statusClass = 'status-out';
                } elseif ($row['quantity_in_stock'] <= $row['reorder_level']) {
                    $status = 'Low Stock';
                    $statusClass = 'status-low';
                } elseif (isset($row['expiry_date']) && $row['expiry_date'] && $row['expiry_date'] < date('Y-m-d')) {
                    $status = 'Expired';
                    $statusClass = 'status-expired';
                } elseif (isset($row['expiry_date']) && $row['expiry_date'] && $row['expiry_date'] <= date('Y-m-d', strtotime('+30 days'))) {
                    $status = 'Near Expiry';
                    $statusClass = 'status-near-expiry';
                }
                
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['category_name']) . '</td>';
                echo '<td>' . $row['quantity_in_stock'] . '</td>';
                echo '<td>TSh ' . number_format($row['cost_price'], 2) . '</td>';
                echo '<td>TSh ' . number_format($row['selling_price'], 2) . '</td>';
                echo '<td>TSh ' . number_format($row['stock_value'], 2) . '</td>';
                echo '<td class="' . $statusClass . '">' . $status . '</td>';
            }
            
            echo '</tr>';
        }
        
        echo '</tbody>
        </table>';
    } else {
        echo '<div class="no-data">
            <h3>No Data Available</h3>
            <p>No inventory data found for the selected criteria.</p>
        </div>';
    }
    
    echo '</div>
        
        <div class="footer">
            <p>&copy; 2026 LTS Inventory Management System | Professional Inventory Reporting</p>
            <p>Page generated on ' . date('Y-m-d H:i:s') . ' | Report ID: INV-' . strtoupper(uniqid()) . '</p>
        </div>
    </div>
</body>
</html>';
    
    exit; // Stop execution after export
}

$pageTitle = 'Inventory Reports';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$reportType = cleanInput($_GET['report_type'] ?? 'current');
$category = cleanInput($_GET['category'] ?? '');
$supplier = cleanInput($_GET['supplier'] ?? '');

// Build base query
$where = ["p.is_active = 1"];
$params = [];

if (!empty($category)) {
    $where[] = "p.category_id = ?";
    $params[] = $category;
}

if (!empty($supplier)) {
    $where[] = "p.supplier_id = ?";
    $params[] = $supplier;
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Get report data based on type
switch ($reportType) {
    case 'current':
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                cur.symbol as currency_symbol, cur.code as currency_code,
                (p.quantity_in_stock * p.selling_price) as stock_value
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN currencies cur ON p.currency_id = cur.id
                $whereClause
                ORDER BY p.name ASC";
        break;
        
    case 'low_stock':
        $where[] = "p.quantity_in_stock <= p.reorder_level";
        $whereClause = "WHERE " . implode(" AND ", $where);
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                cur.symbol as currency_symbol, cur.code as currency_code,
                (p.quantity_in_stock * p.selling_price) as stock_value
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN currencies cur ON p.currency_id = cur.id
                $whereClause
                ORDER BY p.quantity_in_stock ASC";
        break;
        
    case 'expiry':
        $where[] = "p.expiry_date IS NOT NULL AND p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 90 DAY)";
        $whereClause = "WHERE " . implode(" AND ", $where);
        $sql = "SELECT p.*, c.name as category_name, s.name as supplier_name,
                cur.symbol as currency_symbol, cur.code as currency_code,
                DATEDIFF(p.expiry_date, CURRENT_DATE) as days_until_expiry,
                (p.quantity_in_stock * p.selling_price) as stock_value
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                LEFT JOIN currencies cur ON p.currency_id = cur.id
                $whereClause
                ORDER BY p.expiry_date ASC";
        break;
        
    case 'valuation':
        $sql = "SELECT c.name as category_name,
                COUNT(p.id) as product_count,
                SUM(p.quantity_in_stock) as total_quantity,
                SUM(p.quantity_in_stock * p.cost_price) as total_cost_value,
                SUM(p.quantity_in_stock * p.selling_price) as total_sell_value,
                SUM(p.quantity_in_stock * (p.selling_price - p.cost_price)) as potential_profit,
                p.currency_id, cur.symbol as currency_symbol, cur.code as currency_code
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN currencies cur ON p.currency_id = cur.id
                $whereClause
                GROUP BY c.id, c.name, p.currency_id, cur.symbol, cur.code
                ORDER BY total_sell_value DESC";
        break;
        
    case 'movement':
        $dateFrom = cleanInput($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
        $dateTo = cleanInput($_GET['date_to'] ?? date('Y-m-d'));
        
        $sql = "SELECT p.name, c.name as category_name,
                COALESCE(SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN pi.quantity ELSE 0 END), 0) as total_purchased,
                COALESCE(SUM(CASE WHEN si.sale_id IS NOT NULL THEN si.quantity ELSE 0 END), 0) as total_sold,
                (COALESCE(SUM(CASE WHEN pi.purchase_id IS NOT NULL THEN pi.quantity ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN si.sale_id IS NOT NULL THEN si.quantity ELSE 0 END), 0)) as net_movement,
                p.quantity_in_stock as current_stock,
                cur.symbol as currency_symbol, cur.code as currency_code
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN purchase_items pi ON p.id = pi.product_id
                LEFT JOIN purchases pur ON pi.purchase_id = pur.id AND pur.purchase_date BETWEEN ? AND ?
                LEFT JOIN sale_items si ON p.id = si.product_id
                LEFT JOIN sales s ON si.sale_id = s.id AND DATE(s.sale_date) BETWEEN ? AND ?
                LEFT JOIN currencies cur ON p.currency_id = cur.id
                " . str_replace('p.', '', $whereClause) . "
                GROUP BY p.id, p.name, c.name, p.quantity_in_stock, cur.symbol, cur.code
                HAVING total_purchased > 0 OR total_sold > 0
                ORDER BY net_movement DESC";
        
        $params[] = $dateFrom;
        $params[] = $dateTo;
        $params[] = $dateFrom;
        $params[] = $dateTo;
        break;
}

$reportData = fetchAll($sql, $params);

// Get categories and suppliers for filters
$categories = fetchAll("SELECT * FROM categories ORDER BY name ASC");
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

// Calculate summary statistics
$summary = [];
if ($reportType === 'current') {
    $summary['total_products'] = count($reportData);
    $summary['total_quantity'] = array_sum(array_column($reportData, 'quantity_in_stock'));
    
    // Group stock values by currency
    $stock_values_by_currency = [];
    foreach ($reportData as $product) {
        $currency = $product['currency_symbol'] ?? '$';
        if (!isset($stock_values_by_currency[$currency])) {
            $stock_values_by_currency[$currency] = 0;
        }
        $stock_values_by_currency[$currency] += $product['stock_value'];
    }
    
    // Format the stock values by currency
    $formatted_stock_values = [];
    foreach ($stock_values_by_currency as $currency => $value) {
        $formatted_stock_values[] = $currency . number_format($value, 2);
    }
    $summary['total_sell_value_formatted'] = implode(' + ', $formatted_stock_values);
    $summary['stock_values_by_currency'] = $stock_values_by_currency;
    
    $summary['low_stock_count'] = 0;
    foreach ($reportData as $product) {
        if ($product['quantity_in_stock'] <= $product['reorder_level']) {
            $summary['low_stock_count']++;
        }
    }
}
?>

<!-- Report Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-2">
                <label for="report_type" class="form-label">Report Type</label>
                <select class="form-select" id="report_type" name="report_type">
                    <option value="current" <?php echo $reportType === 'current' ? 'selected' : ''; ?>>Current Stock</option>
                    <option value="low_stock" <?php echo $reportType === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                    <option value="expiry" <?php echo $reportType === 'expiry' ? 'selected' : ''; ?>>Expiry Report</option>
                    <option value="valuation" <?php echo $reportType === 'valuation' ? 'selected' : ''; ?>>Stock Valuation</option>
                    <option value="movement" <?php echo $reportType === 'movement' ? 'selected' : ''; ?>>Stock Movement</option>
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
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
            <?php if ($reportType === 'movement'): ?>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo ?? ''; ?>">
                </div>
            <?php endif; ?>
            <div class="col-md-<?php echo $reportType === 'movement' ? '2' : '6'; ?> d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Generate Report</button>
                <a href="?export=html&<?php echo http_build_query($_GET); ?>" class="btn btn-success me-2">Generate PDF</a>
                <a href="inventory.php" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards (for current stock) -->
<?php if ($reportType === 'current' && !empty($summary)): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4><?php echo $summary['total_products']; ?></h4>
                    <p class="card-text">Total Products</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4><?php echo $summary['total_quantity']; ?></h4>
                    <p class="card-text">Total Quantity</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h4 style="font-size: 0.9rem;"><?php echo $summary['total_sell_value_formatted']; ?></h4>
                    <p class="card-text">Stock Value</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h4><?php echo $summary['low_stock_count']; ?></h4>
                    <p class="card-text">Low Stock Items</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Report Content -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <?php 
            switch ($reportType) {
                case 'current': echo 'Current Inventory Report'; break;
                case 'low_stock': echo 'Low Stock Report'; break;
                case 'expiry': echo 'Expiry Report'; break;
                case 'valuation': echo 'Stock Valuation by Category'; break;
                case 'movement': echo 'Stock Movement Report'; break;
            }
            ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($reportData)): ?>
            <div class="text-center py-4">
                <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No data found for the selected criteria</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <?php if ($reportType === 'valuation'): ?>
                            <tr>
                                <th>Category</th>
                                <th>Products</th>
                                <th>Total Quantity</th>
                                <th>Cost Value</th>
                                <th>Sell Value</th>
                                <th>Potential Profit</th>
                            </tr>
                        <?php elseif ($reportType === 'movement'): ?>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Purchased</th>
                                <th>Sold</th>
                                <th>Net Movement</th>
                                <th>Current Stock</th>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <?php if ($reportType === 'expiry'): ?>
                                    <th>Expiry Date</th>
                                    <th>Days Until Expiry</th>
                                <?php else: ?>
                                    <th>Stock Value</th>
                                <?php endif; ?>
                                <th>Status</th>
                            </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <?php if ($reportType === 'valuation'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td><?php echo $row['product_count']; ?></td>
                                    <td><?php echo $row['total_quantity']; ?></td>
                                    <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['total_cost_value'], 2); ?></td>
                                    <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['total_sell_value'], 2); ?></td>
                                    <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['potential_profit'], 2); ?></td>
                                </tr>
                            <?php elseif ($reportType === 'movement'): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td><?php echo $row['total_purchased']; ?></td>
                                    <td><?php echo $row['total_sold']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $row['net_movement'] >= 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $row['net_movement']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['current_stock']; ?></td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        <?php if ($row['batch_number']): ?>
                                            <br><small class="text-muted">Batch: <?php echo htmlspecialchars($row['batch_number']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td><?php echo $row['supplier_name'] ? htmlspecialchars($row['supplier_name']) : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($row['quantity_in_stock'] == 0): ?>
                                            <span class="badge bg-danger">0</span>
                                        <?php elseif ($row['quantity_in_stock'] <= $row['reorder_level']): ?>
                                            <span class="badge bg-warning"><?php echo $row['quantity_in_stock']; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><?php echo $row['quantity_in_stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $row['reorder_level']; ?></td>
                                    <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['cost_price'], 2); ?></td>
                                    <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['selling_price'], 2); ?></td>
                                    <?php if ($reportType === 'expiry'): ?>
                                        <td><?php echo formatDate($row['expiry_date']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $row['days_until_expiry'] < 0 ? 'bg-danger' : ($row['days_until_expiry'] <= 30 ? 'bg-warning' : 'bg-info'); ?>">
                                                <?php echo $row['days_until_expiry']; ?> days
                                            </span>
                                        </td>
                                    <?php else: ?>
                                        <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['stock_value'], 2); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <?php
                                        $status = 'Normal';
                                        if ($row['quantity_in_stock'] == 0) $status = 'Out of Stock';
                                        elseif ($row['quantity_in_stock'] <= $row['reorder_level']) $status = 'Low Stock';
                                        elseif ($row['expiry_date'] && $row['expiry_date'] < date('Y-m-d')) $status = 'Expired';
                                        elseif ($row['expiry_date'] && $row['expiry_date'] <= date('Y-m-d', strtotime('+30 days'))) $status = 'Near Expiry';
                                        echo getAlertBadge($status);
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Return to Dashboard Button -->
<div class="text-center mt-4 mb-4">
    <a href="<?php echo BASE_URL; ?>/manager/dashboard.php" class="btn btn-secondary btn-lg">
        <i class="bi bi-arrow-left"></i> Return to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
