<?php
/**
 * Sales Reports
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('Manager');

// Helper function to calculate profit for a sale
function calculateSaleProfit($saleId) {
    global $pdo;
    try {
        $sql = "SELECT SUM((si.unit_price - si.cost_price) * si.quantity) as profit 
                FROM sale_items si 
                WHERE si.sale_id = ?";
        $result = fetchRow($sql, [$saleId]);
        return $result['profit'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Helper function to get total quantity sold for a sale
function getTotalQuantitySold($saleId) {
    global $pdo;
    try {
        $sql = "SELECT SUM(si.quantity) as total_quantity 
                FROM sale_items si 
                WHERE si.sale_id = ?";
        $result = fetchRow($sql, [$saleId]);
        return $result['total_quantity'] ?? 0;
    } catch (Exception $e) {
        return 0;
    }
}

// Handle HTML export BEFORE any HTML output
if (isset($_GET['export']) && $_GET['export'] === 'html') {
    // Get report data (reuse same logic as main page)
    $reportType = cleanInput($_GET['report_type'] ?? 'daily');
    $dateFrom = cleanInput($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
    $dateTo = cleanInput($_GET['date_to'] ?? date('Y-m-d'));
    $paymentMethod = cleanInput($_GET['payment_method'] ?? '');
    
    // Debug: Log export attempt
    error_log("HTML Export requested for report type: " . $reportType);
    
    // Build query (reuse same logic as main page)
    $where = ["1=1"];
    $params = [];
    
    if (!empty($dateFrom)) {
        $where[] = "DATE(s.sale_date) >= ?";
        $params[] = $dateFrom;
    }
    
    if (!empty($dateTo)) {
        $where[] = "DATE(s.sale_date) <= ?";
        $params[] = $dateTo;
    }
    
    if (!empty($paymentMethod)) {
        $where[] = "s.payment_method = ?";
        $params[] = $paymentMethod;
    }
    
    $whereClause = "WHERE " . implode(" AND ", $where);
    
    // Get report data based on type
    switch ($reportType) {
        case 'daily':
            $sql = "SELECT DATE(s.sale_date) as report_date, 
                    COUNT(*) as total_sales, 
                    SUM(s.total_amount) as gross_revenue,
                    SUM(s.discount_amount) as total_discount,
                    SUM(s.final_amount) as net_revenue,
                    AVG(s.final_amount) as avg_sale_value,
                    'TSh' as currency_symbol
                    FROM sales s 
                    $whereClause
                    GROUP BY DATE(s.sale_date)
                    ORDER BY report_date DESC";
            break;
        case 'monthly':
            $sql = "SELECT DATE_FORMAT(s.sale_date, '%Y-%m') as report_date, 
                    COUNT(*) as total_sales, 
                    SUM(s.total_amount) as gross_revenue,
                    SUM(s.discount_amount) as total_discount,
                    SUM(s.final_amount) as net_revenue,
                    AVG(s.final_amount) as avg_sale_value,
                    'TSh' as currency_symbol
                    FROM sales s 
                    $whereClause
                    GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
                    ORDER BY report_date DESC";
            break;
        case 'summary':
            $sql = "SELECT COUNT(*) as total_sales, 
                    SUM(s.total_amount) as gross_revenue,
                    SUM(s.discount_amount) as total_discount,
                    SUM(s.final_amount) as net_revenue,
                    AVG(s.final_amount) as avg_sale_value,
                    MIN(s.sale_date) as first_sale,
                    MAX(s.sale_date) as last_sale
                    FROM sales s 
                    $whereClause";
            break;
        case 'detailed':
            $sql = "SELECT s.*, u.full_name as created_by_name,
                    si.product_id, si.quantity as item_quantity, si.unit_price, si.cost_price, si.discount_amount as item_discount,
                    p.name as product_name, p.code as product_code,
                    (si.unit_price - si.cost_price) * si.quantity as item_profit,
                    (si.unit_price * si.quantity) as item_total,
                    ((si.unit_price * si.quantity) - si.discount_amount) as item_final
                    FROM sales s 
                    JOIN users u ON s.created_by = u.id 
                    LEFT JOIN sale_items si ON s.id = si.sale_id 
                    LEFT JOIN products p ON si.product_id = p.id 
                    $whereClause
                    ORDER BY s.sale_date DESC, si.id
                    LIMIT 200";
            break;
    }
    
    $reportData = fetchAll($sql, $params);
    
    // Generate enhanced HTML report with better design
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report - ' . ucfirst($reportType) . '</title>
    <style>
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f8f9fa;
        }
        .header { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
            color: white; 
            padding: 30px; 
            margin-bottom: 30px; 
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header h1 { margin: 0; font-size: 2.5em; }
        .header p { margin: 5px 0; opacity: 0.9; }
        .currency-section { 
            margin-bottom: 40px; 
            border: 2px solid #e9ecef; 
            border-radius: 10px; 
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        .currency-header { 
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white; 
            padding: 20px; 
            font-weight: bold; 
            font-size: 1.3em;
            text-align: center;
        }
        .sales-container {
            padding: 20px;
        }
        .sale-group {
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        .sale-header {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        .sale-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .products-table th, 
        .products-table td { 
            border: 1px solid #dee2e6; 
            padding: 12px; 
            text-align: left; 
        }
        .products-table th { 
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            font-weight: 600;
        }
        .products-table tr:nth-child(even) { background-color: #f8f9fa; }
        .profit-positive { color: #28a745; font-weight: bold; }
        .profit-negative { color: #dc3545; font-weight: bold; }
        .currency-summary { 
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 25px; 
            border-radius: 8px; 
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
        }
        .overall-summary {
            background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            box-shadow: 0 6px 20px rgba(52, 58, 64, 0.3);
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .summary-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }
        .summary-item strong {
            display: block;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .instructions h3 {
            color: #856404;
            margin-top: 0;
        }
        .return-btn {
            display: inline-block;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
        }
        .return-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #343a40 100%);
        }
        @media print {
            .return-btn, .instructions { display: none; }
            body { padding: 10px; }
            .header { margin-bottom: 20px; }
            .currency-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Sales Report</h1>
        <p><strong>Report Type:</strong> ' . ucfirst($reportType) . '</p>
        <p><strong>Period:</strong> ' . formatDate($dateFrom) . ' to ' . formatDate($dateTo) . '</p>
        <p><strong>Generated:</strong> ' . date('Y-m-d H:i:s') . '</p>
        <p><strong>Generated By:</strong> ' . getCurrentUser()['full_name'] . ' (' . getRoleName(getCurrentUser()['role_id']) . ')</p>
    </div>';
    
    if (!empty($reportData)) {
        // Handle different report types
        if ($reportType === 'detailed') {
            // Group data by TSh currency for detailed report
            $currencyGroups = [];
            foreach ($reportData as $row) {
                $currencyId = 1;
                $currencySymbol = 'TSh';
                $saleId = $row['id'];
                
                if (!isset($currencyGroups[$currencyId])) {
                    $currencyGroups[$currencyId] = [
                        'symbol' => $currencySymbol,
                        'sales' => [],
                        'totals' => [
                            'total_sales' => 0,
                            'gross_revenue' => 0,
                            'total_discount' => 0,
                            'net_revenue' => 0,
                            'total_profit' => 0,
                            'total_quantity' => 0
                        ]
                    ];
                }
                
                // Group items by sale
                if (!isset($currencyGroups[$currencyId]['sales'][$saleId])) {
                    $currencyGroups[$currencyId]['sales'][$saleId] = [
                        'sale_info' => [
                            'id' => $row['id'],
                            'sale_date' => $row['sale_date'],
                            'customer_name' => $row['customer_name'],
                            'payment_method' => $row['payment_method'],
                            'created_by_name' => $row['created_by_name'],
                            'total_amount' => $row['total_amount'],
                            'discount_amount' => $row['discount_amount'],
                            'final_amount' => $row['final_amount']
                        ],
                        'items' => []
                    ];
                    $currencyGroups[$currencyId]['totals']['total_sales']++;
                }
                
                // Add item to sale
                $currencyGroups[$currencyId]['sales'][$saleId]['items'][] = [
                    'product_name' => $row['product_name'],
                    'product_code' => $row['product_code'],
                    'quantity' => $row['item_quantity'],
                    'unit_price' => $row['unit_price'],
                    'cost_price' => $row['cost_price'],
                    'item_total' => $row['item_total'],
                    'item_discount' => $row['item_discount'],
                    'item_final' => $row['item_final'],
                    'item_profit' => $row['item_profit']
                ];
                
                // Update totals
                $currencyGroups[$currencyId]['totals']['gross_revenue'] += $row['item_total'];
                $currencyGroups[$currencyId]['totals']['total_discount'] += $row['item_discount'];
                $currencyGroups[$currencyId]['totals']['net_revenue'] += $row['item_final'];
                $currencyGroups[$currencyId]['totals']['total_profit'] += $row['item_profit'];
                $currencyGroups[$currencyId]['totals']['total_quantity'] += $row['item_quantity'];
            }
            
            // Display detailed report with product breakdown
            foreach ($currencyGroups as $currencyId => $currencyGroup) {
                echo '<div class="currency-section">
                    <div class="currency-header">
                        💰 Currency: TSh - ' . count($currencyGroup['sales']) . ' Sales
                    </div>
                    <div class="sales-container">';
                
                foreach ($currencyGroup['sales'] as $saleId => $saleData) {
                    $saleInfo = $saleData['sale_info'];
                    $items = $saleData['items'];
                    
                    echo '<div class="sale-group">
                        <div class="sale-header">
                            <div class="sale-info">
                                <div>
                                    <strong>Sale #' . str_pad($saleInfo['id'] ?? 0, 6, '0', STR_PAD_LEFT) . '</strong> | 
                                    ' . formatDateTime($saleInfo['sale_date'] ?? '') . ' | 
                                    Customer: ' . htmlspecialchars($saleInfo['customer_name'] ?? 'Walk-in') . ' |
                                    Payment: ' . htmlspecialchars($saleInfo['payment_method'] ?? 'N/A') . ' |
                                    Cashier: ' . htmlspecialchars($saleInfo['created_by_name'] ?? 'N/A') . '
                                </div>
                                <div>
                                    Total: TSh ' . number_format($saleInfo['final_amount'] ?? 0, 2) . '
                                </div>
                            </div>
                        </div>
                        
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Product Code</th>
                                    <th>Quantity Sold</th>
                                    <th>Cost Price</th>
                                    <th>Selling Price</th>
                                    <th>Total Cost</th>
                                    <th>Net Revenue</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    $saleTotalCost = 0;
                    $saleTotalRevenue = 0;
                    $saleTotalProfit = 0;
                    
                    foreach ($items as $item) {
                        $totalCost = ($item['cost_price'] ?? 0) * ($item['quantity'] ?? 0);
                        $netRevenue = $item['item_final'] ?? 0;
                        $profit = $item['item_profit'] ?? 0;
                        $profitClass = $profit >= 0 ? 'profit-positive' : 'profit-negative';
                        
                        $saleTotalCost += $totalCost;
                        $saleTotalRevenue += $netRevenue;
                        $saleTotalProfit += $profit;
                        
                        echo '<tr>
                            <td><strong>' . htmlspecialchars($item['product_name'] ?? 'N/A') . '</strong></td>
                            <td>' . htmlspecialchars($item['product_code'] ?? 'N/A') . '</td>
                            <td>' . number_format($item['quantity'] ?? 0) . '</td>
                            <td>TSh ' . number_format($item['cost_price'] ?? 0, 2) . '</td>
                            <td>TSh ' . number_format($item['unit_price'] ?? 0, 2) . '</td>
                            <td>TSh ' . number_format($totalCost, 2) . '</td>
                            <td>TSh ' . number_format($netRevenue, 2) . '</td>
                            <td class="' . $profitClass . '"><strong>TSh ' . number_format($profit, 2) . '</strong></td>
                        </tr>';
                    }
                    
                    echo '</tbody>
                            <tfoot>
                                <tr style="background: #f8f9fa; font-weight: bold;">
                                    <td colspan="5">Sale Totals</td>
                                    <td>TSh ' . number_format($saleTotalCost, 2) . '</td>
                                    <td>TSh ' . number_format($saleTotalRevenue, 2) . '</td>
                                    <td class="' . ($saleTotalProfit >= 0 ? 'profit-positive' : 'profit-negative') . '">TSh ' . number_format($saleTotalProfit, 2) . '</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>';
                }
                
                // Currency summary
                echo '<div class="currency-summary">
                    <h3>📊 Currency Summary - TSh</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <strong>' . number_format($currencyGroup['totals']['total_sales']) . '</strong>
                            Total Sales
                        </div>
                        <div class="summary-item">
                            <strong>' . number_format($currencyGroup['totals']['total_quantity']) . '</strong>
                            Total Items Sold
                        </div>
                        <div class="summary-item">
                            <strong>TSh ' . number_format($currencyGroup['totals']['gross_revenue'], 2) . '</strong>
                            Gross Revenue
                        </div>
                        <div class="summary-item">
                            <strong>TSh ' . number_format($currencyGroup['totals']['net_revenue'], 2) . '</strong>
                            Net Revenue
                        </div>
                        <div class="summary-item">
                            <strong>TSh ' . number_format($currencyGroup['totals']['total_profit'], 2) . '</strong>
                            Total Profit
                        </div>
                    </div>
                </div>
                </div>
                </div>';
            }
            
            // Overall summary
            echo '<div class="overall-summary">
                <h2>🎯 Overall Profit Summary (TSh)</h2>
                <div class="summary-grid">';
            
            $overallTotals = [
                'total_sales' => 0,
                'gross_revenue' => 0,
                'total_discount' => 0,
                'net_revenue' => 0,
                'total_profit' => 0,
                'total_quantity' => 0
            ];
            
            foreach ($currencyGroups as $currencyGroup) {
                $overallTotals['total_sales'] += $currencyGroup['totals']['total_sales'];
                $overallTotals['gross_revenue'] += $currencyGroup['totals']['gross_revenue'];
                $overallTotals['total_discount'] += $currencyGroup['totals']['total_discount'];
                $overallTotals['net_revenue'] += $currencyGroup['totals']['net_revenue'];
                $overallTotals['total_profit'] += $currencyGroup['totals']['total_profit'];
                $overallTotals['total_quantity'] += $currencyGroup['totals']['total_quantity'];
            }
            
            echo '<div class="summary-item">
                    <strong>' . number_format($overallTotals['total_sales']) . '</strong>
                    Total Sales
                </div>
                <div class="summary-item">
                    <strong>' . number_format($overallTotals['total_quantity']) . '</strong>
                    Total Items Sold
                </div>
                <div class="summary-item">
                    <strong>TSh ' . number_format($overallTotals['gross_revenue'], 2) . '</strong>
                    Gross Revenue
                </div>
                <div class="summary-item">
                    <strong>TSh ' . number_format($overallTotals['net_revenue'], 2) . '</strong>
                    Net Revenue
                </div>
                <div class="summary-item">
                    <strong>TSh ' . number_format($overallTotals['total_profit'], 2) . '</strong>
                    Total Profit
                </div>';
            
            // Add currency breakdown
            echo '</div>
                <h3 style="margin-top: 30px; margin-bottom: 15px;">💰 Profit by Currency</h3>
                <div class="summary-grid">';
            
            foreach ($currencyGroups as $currencyId => $currencyGroup) {
                echo '<div class="summary-item">
                        <strong>' . $currencyGroup['symbol'] . number_format($currencyGroup['totals']['total_profit'], 2) . '</strong>
                        Profit (' . htmlspecialchars($currencyGroup['symbol']) . ')
                    </div>';
            }
            
            echo '</div>
                </div>';
        } else {
            // Handle daily, monthly, and summary reports with recent sales format
            echo '<div class="currency-section">
                <div class="currency-header">
                    📈 Sales Summary Report
                </div>
                <div class="sales-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Sold</th>
                                <th>Total Revenue</th>
                                <th>Total Profit</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            // Get product sales data for summary
            $summarySalesSql = "SELECT 
                                p.name as product_name,
                                SUM(si.quantity) as total_sold,
                                SUM(si.subtotal) as total_revenue,
                                SUM(si.subtotal) as total_profit,
                                'TSh' as currency_symbol
                                FROM sale_items si
                                JOIN sales s ON si.sale_id = s.id
                                JOIN products p ON si.product_id = p.id
                                $whereClause
                                GROUP BY p.id, p.name
                                ORDER BY total_sold DESC";
            $summarySalesData = fetchAll($summarySalesSql);
            
            foreach ($summarySalesData as $row) {
                $profitClass = $row['total_profit'] >= 0 ? 'profit-positive' : 'profit-negative';
                echo '<tr>
                    <td><strong>' . htmlspecialchars($row['product_name']) . '</strong></td>
                    <td>' . number_format($row['total_sold']) . '</td>
                    <td>TSh ' . number_format($row['total_revenue'], 2) . '</td>
                    <td class="' . $profitClass . '"><strong>TSh ' . number_format($row['total_profit'], 2) . '</strong></td>
                </tr>';
            }
            
            echo '</tbody>
                    </table>
                </div>
            </div>';
        }
        
    } else {
        echo '<div class="currency-section">
            <div class="currency-header">
                ⚠️ No Data Found
            </div>
            <div class="sales-container">
                <div style="text-align: center; padding: 40px;">
                    <h3>No sales data found</h3>
                    <p>No sales data found for the selected period and criteria.</p>
                </div>
            </div>
        </div>';
    }
    
    // Add recent sales section to export
    echo '<div class="sales-container">
        <div class="currency-header">
            📈 Recent Sales - Products Sold (Last 30 Days)
        </div>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Sold</th>
                    <th>Total Revenue</th>
                    <th>Total Profit</th>
                </tr>
            </thead>
            <tbody>';
    
    // Get recent sales data for export
    $recentSalesExportSql = "SELECT 
                        p.name as product_name,
                        SUM(si.quantity) as total_sold,
                        SUM(si.subtotal) as total_revenue,
                        SUM(si.subtotal) as total_profit
                        FROM sale_items si
                        JOIN sales s ON si.sale_id = s.id
                        JOIN products p ON si.product_id = p.id
                        WHERE DATE(s.sale_date) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                        GROUP BY p.id, p.name
                        ORDER BY total_sold DESC
                        LIMIT 10";
    $recentSalesExportData = fetchAll($recentSalesExportSql);
    
    foreach ($recentSalesExportData as $row) {
        $profitClass = $row['total_profit'] >= 0 ? 'profit-positive' : 'profit-negative';
        echo '<tr>
            <td><strong>' . htmlspecialchars($row['product_name']) . '</strong></td>
            <td>' . number_format($row['total_sold']) . '</td>
            <td>TSh ' . number_format($row['total_revenue'], 2) . '</td>
            <td class="' . $profitClass . '"><strong>TSh ' . number_format($row['total_profit'], 2) . '</strong></td>
        </tr>';
    }
    
    echo '</tbody>
        </table>
    </div>';
    
    // Calculate and display overall profit
    $overallProfit = 0;
    foreach ($recentSalesExportData as $row) {
        $overallProfit += $row['total_profit'];
    }
    
    echo '<div class="overall-summary">
        <h2>🎯 Overall Profit Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>TSh ' . number_format($overallProfit, 2) . '</strong>
                Total Profit (All Products)
            </div>
        </div>
    </div>';
    
    // Add instructions section
    echo '<div class="instructions">
        <h3>📋 How to Convert to PDF</h3>
        <ol>
            <li><strong>Print Dialog:</strong> Press <kbd>Ctrl+P</kbd> (Windows) or <kbd>Cmd+P</kbd> (Mac)</li>
            <li><strong>Destination:</strong> Select "Save as PDF" or "Microsoft Print to PDF"</li>
            <li><strong>Settings:</strong> Choose "Landscape" orientation for better layout</li>
            <li><strong>Margins:</strong> Set to "None" or "Minimal" for full content</li>
            <li><strong>Save:</strong> Click "Save" and choose your location</li>
        </ol>
        <p><strong>💡 Tip:</strong> For best results, use Chrome or Firefox browser.</p>
    </div>';
    
    // Add return button
    echo '<div style="text-align: center;">
        <a href="' . BASE_URL . '/reports/sales.php" class="return-btn">
            ← Return to Dashboard
        </a>
    </div>';
    
    echo '</body>
    </html>';
    
    exit; // Stop execution after export
}

$pageTitle = 'Sales Reports';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$reportType = cleanInput($_GET['report_type'] ?? 'daily');
$dateFrom = cleanInput($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
$dateTo = cleanInput($_GET['date_to'] ?? date('Y-m-d'));
$paymentMethod = cleanInput($_GET['payment_method'] ?? '');

// Get user role for dashboard navigation
$currentUser = getCurrentUser();
$roleId = $currentUser['role_id'];
$dashboardUrl = '';

// Determine dashboard URL based on role
switch ($roleId) {
    case 1: // Admin
        $dashboardUrl = BASE_URL . '/admin/dashboard.php';
        break;
    case 2: // Manager
        $dashboardUrl = BASE_URL . '/manager/dashboard.php';
        break;
    case 3: // Cashier
        $dashboardUrl = BASE_URL . '/cashier/dashboard.php';
        break;
    default:
        $dashboardUrl = BASE_URL . '/auth/login.php';
}

// Build base query
$where = ["1=1"];
$params = [];

if (!empty($dateFrom)) {
    $where[] = "DATE(s.sale_date) >= ?";
    $params[] = $dateFrom;
}

if (!empty($dateTo)) {
    $where[] = "DATE(s.sale_date) <= ?";
    $params[] = $dateTo;
}

if (!empty($paymentMethod)) {
    $where[] = "s.payment_method = ?";
    $params[] = $paymentMethod;
}

$whereClause = "WHERE " . implode(" AND ", $where);

// Get report data based on type
switch ($reportType) {
    case 'daily':
        $sql = "SELECT DATE(s.sale_date) as report_date, 
                COUNT(*) as total_sales, 
                SUM(s.total_amount) as gross_revenue,
                SUM(s.final_amount) as net_revenue,
                AVG(s.final_amount) as avg_sale_value,
                'TSh' as currency_symbol
                FROM sales s 
                $whereClause
                GROUP BY DATE(s.sale_date)
                ORDER BY report_date DESC";
        break;
    case 'monthly':
        $sql = "SELECT DATE_FORMAT(s.sale_date, '%Y-%m') as report_date, 
                COUNT(*) as total_sales, 
                SUM(s.total_amount) as gross_revenue,
                SUM(s.final_amount) as net_revenue,
                AVG(s.final_amount) as avg_sale_value,
                'TSh' as currency_symbol
                FROM sales s 
                $whereClause
                GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
                ORDER BY report_date DESC";
        break;
    case 'summary':
        $sql = "SELECT COUNT(*) as total_sales, 
                SUM(s.total_amount) as gross_revenue,
                SUM(s.final_amount) as net_revenue,
                AVG(s.final_amount) as avg_sale_value,
                MIN(s.sale_date) as first_sale,
                MAX(s.sale_date) as last_sale
                FROM sales s 
                $whereClause";
        break;
    case 'detailed':
        $sql = "SELECT s.*, u.full_name as created_by_name, 'TSh' as currency_symbol,
                GROUP_CONCAT(CONCAT(p.name, ' (', si.quantity, ')') SEPARATOR ', ') as items_summary
                FROM sales s 
                JOIN users u ON s.created_by = u.id 
                LEFT JOIN sale_items si ON s.id = si.sale_id 
                LEFT JOIN products p ON si.product_id = p.id 
                $whereClause
                GROUP BY s.id 
                ORDER BY s.sale_date DESC 
                LIMIT 100";
        break;
}

$reportData = fetchAll($sql, $params);

// Get top selling products
$topProductsSql = "SELECT p.name, SUM(si.quantity) as total_sold, SUM(si.subtotal) as total_revenue, 'TSh' as currency_symbol
                  FROM products p 
                  JOIN sale_items si ON p.id = si.product_id 
                  JOIN sales s ON si.sale_id = s.id 
                  " . str_replace('s.', '', $whereClause) . " 
                  GROUP BY p.id, p.name
                  ORDER BY total_sold DESC 
                  LIMIT 10";
$topProducts = fetchAll($topProductsSql, $params);

// Get payment method breakdown with currency info
$paymentSql = "SELECT payment_method, COUNT(*) as count, SUM(final_amount) as total, 'TSh' as currency_symbol
              FROM sales s 
              $whereClause
              GROUP BY payment_method 
              ORDER BY total DESC";
$paymentData = fetchAll($paymentSql, $params);

// Get cashier performance with currency info
$cashierSql = "SELECT u.full_name, COUNT(s.id) as sales_count, SUM(s.final_amount) as total_revenue, 'TSh' as currency_symbol
              FROM users u 
              LEFT JOIN sales s ON u.id = s.created_by 
              " . str_replace('s.', '', $whereClause) . " 
              AND u.role_id = 3 
              GROUP BY u.id, u.full_name 
              ORDER BY total_revenue DESC";
$cashierData = fetchAll($cashierSql, $params);
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $dashboardUrl; ?>">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <div class="navbar-nav ms-auto">
            <span class="navbar-text">
                <strong>Sales Reports</strong>
            </span>
        </div>
    </div>
</nav>

<!-- Report Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-2">
                <label for="report_type" class="form-label">Report Type</label>
                <select class="form-select" id="report_type" name="report_type">
                    <option value="daily" <?php echo $reportType === 'daily' ? 'selected' : ''; ?>>Daily Sales</option>
                    <option value="monthly" <?php echo $reportType === 'monthly' ? 'selected' : ''; ?>>Monthly Sales</option>
                    <option value="summary" <?php echo $reportType === 'summary' ? 'selected' : ''; ?>>Summary</option>
                    <option value="detailed" <?php echo $reportType === 'detailed' ? 'selected' : ''; ?>>Detailed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $dateFrom; ?>">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $dateTo; ?>">
            </div>
            <div class="col-md-2">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="">All Methods</option>
                    <option value="Cash" <?php echo $paymentMethod === 'Cash' ? 'selected' : ''; ?>>Cash</option>
                    <option value="Card" <?php echo $paymentMethod === 'Card' ? 'selected' : ''; ?>>Card</option>
                    <option value="Mobile" <?php echo $paymentMethod === 'Mobile' ? 'selected' : ''; ?>>Mobile</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <a href="?export=html&<?php echo http_build_query($_GET); ?>" class="btn btn-success">Export PDF</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Current System Time Display -->
<div class="alert alert-info">
    <strong>Current System Time:</strong> <?php echo date('Y-m-d H:i:s T'); ?><br>
    <strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?>
</div>

<?php if (!empty($topProducts)): ?>
    <!-- Top Selling Products -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Top Selling Products</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['total_sold']; ?></td>
                                <td><?php echo number_format($product['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($paymentData)): ?>
    <!-- Payment Methods -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Payment Methods</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Count</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentData as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_method']); ?></td>
                                <td><?php echo $payment['count']; ?></td>
                                <td><?php echo number_format($payment['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($cashierData)): ?>
    <!-- Cashier Performance -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Cashier Performance</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Cashier Name</th>
                            <th>Sales Count</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cashierData as $cashier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cashier['full_name']); ?></td>
                                <td><?php echo $cashier['sales_count']; ?></td>
                                <td><?php echo number_format($cashier['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Recent Sales -->
<?php
// Get recent sales with product details
$recentSalesSql = "SELECT 
                    p.name as product_name,
                    SUM(si.quantity) as total_sold,
                    SUM(si.subtotal) as total_revenue,
                    SUM(si.subtotal) as total_profit,
                    'TSh' as currency_symbol
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    JOIN products p ON si.product_id = p.id
                    WHERE DATE(s.sale_date) >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    GROUP BY p.id, p.name
                    ORDER BY total_sold DESC
                    LIMIT 10";
$recentSalesData = fetchAll($recentSalesSql);
?>

<?php if (!empty($recentSalesData)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Recent Sales - Products Sold</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Sold</th>
                            <th>Total Revenue</th>
                            <th>Total Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSalesData as $row): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                                <td><?php echo number_format($row['total_sold']); ?></td>
                                <td><?php echo $row['currency_symbol']; ?><?php echo number_format($row['total_revenue'], 2); ?></td>
                                <td class="<?php echo $row['total_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <strong><?php echo $row['currency_symbol']; ?><?php echo number_format($row['total_profit'], 2); ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
