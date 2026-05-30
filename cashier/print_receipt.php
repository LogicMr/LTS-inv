<?php
/**
 * Print Receipt
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/logo.php';
require_once __DIR__ . '/../includes/settings.php'; // Include settings functions
requireRole('Cashier');

// Get sale ID from URL
$saleId = $_GET['id'] ?? null;

if (!$saleId) {
    die('Sale ID is required');
}

// Get sale details with currency information (no customer join)
$sale = fetchRow("SELECT s.*, cur.code as currency_code, cur.symbol as currency_symbol
                   FROM sales s 
                   LEFT JOIN currencies cur ON s.currency_id = cur.id
                   WHERE s.id = ?", [$saleId]);

if (!$sale) {
    die('Sale not found');
}

// Get sale items with currency info
$items = fetchAll("SELECT si.*, p.name as product_name, p.barcode, cur.code as currency_code, cur.symbol as currency_symbol
                    FROM sale_items si 
                    JOIN products p ON si.product_id = p.id 
                    LEFT JOIN currencies cur ON si.currency_id = cur.id
                    WHERE si.sale_id = ? 
                    ORDER BY si.id ASC", [$saleId]);

// Get currency info for display
$currency = fetchRow("SELECT * FROM currencies WHERE id = ?", [$sale['currency_id'] ?? 1]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt #<?php echo $sale['id']; ?></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 20px;
            background: white;
        }
        
        .receipt-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .receipt-header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .receipt-info {
            margin-bottom: 20px;
        }
        
        .receipt-info div {
            margin-bottom: 5px;
        }
        
        .receipt-items {
            margin-bottom: 20px;
        }
        
        .receipt-items table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .receipt-items th,
        .receipt-items td {
            border-bottom: 1px solid #ddd;
            padding: 8px 4px;
            text-align: left;
        }
        
        .receipt-items th {
            font-weight: bold;
            border-bottom: 2px solid #333;
        }
        
        .receipt-items .quantity {
            text-align: center;
        }
        
        .receipt-items .price {
            text-align: right;
        }
        
        .receipt-totals {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        
        .receipt-totals div {
            margin-bottom: 5px;
            text-align: right;
        }
        
        .receipt-totals .total {
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 12px;
            color: #666;
        }
        
        .no-print {
            display: none;
        }
        
        @media print {
            body { padding: 0; }
            .receipt { border: none; box-shadow: none; }
            .no-print { display: none; }
        }
        
        .btn {
            padding: 8px 16px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .me-2 {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <?php if (hasLogo()): ?>
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="<?php echo getLogoUrl(); ?>" alt="<?php echo APP_NAME; ?>" 
                         style="max-width: 150px; max-height: 60px; border-radius: 4px;">
                </div>
            <?php endif; ?>
            <h2><?php echo getSetting('store_name', APP_NAME); ?></h2>
            <p>RECEIPT</p>
            <p>#<?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>
        
        <!-- Sale Information -->
        <div class="receipt-info">
            <div><strong>Date:</strong> <?php echo formatDate($sale['sale_date'], 'Y-m-d H:i:s'); ?></div>
            <div><strong>Cashier:</strong> <?php echo htmlspecialchars($sale['cashier_name'] ?? 'System'); ?></div>
            <div><strong>Payment:</strong> <?php echo htmlspecialchars($sale['payment_method']); ?></div>
        </div>
        
        <!-- Sale Items -->
        <div class="receipt-items">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="quantity">Qty</th>
                        <th class="price">Price</th>
                        <th class="price">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td class="quantity"><?php echo $item['quantity']; ?></td>
                            <td class="price"><?php echo $item['currency_symbol']; ?><?php echo number_format($item['selling_price'], 2); ?></td>
                            <td class="price"><?php echo $item['currency_symbol']; ?><?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="receipt-totals">
            <div><strong>Subtotal:</strong> <?php echo $sale['currency_symbol']; ?><?php echo number_format($sale['total_amount'], 2); ?></div>
            <?php if ($sale['discount_amount'] > 0): ?>
                <div><strong>Discount:</strong> -<?php echo $sale['currency_symbol']; ?><?php echo number_format($sale['discount_amount'], 2); ?></div>
            <?php endif; ?>
            <div class="total"><strong>TOTAL:</strong> <?php echo $sale['currency_symbol']; ?><?php echo number_format($sale['final_amount'], 2); ?></div>
        </div>
        
        <!-- Footer -->
        <div class="receipt-footer">
            <p><?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></p>
            <p>Thank you for your business!</p>
            <?php if ($sale['notes']): ?>
                <p><strong>Notes:</strong> <?php echo htmlspecialchars($sale['notes']); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Print Button -->
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" class="btn btn-primary me-2">Print Receipt</button>
            <button onclick="window.close(); returnToPOS();" class="btn btn-secondary">Return to POS</button>
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Return to POS function
        function returnToPOS() {
            // Close receipt window and refresh POS
            if (window.opener) {
                // Refresh the parent POS window
                window.opener.location.reload();
            }
            window.close();
        }
        
        // Handle after print
        window.addEventListener('afterprint', function() {
            setTimeout(function() {
                returnToPOS();
            }, 1000);
        });
    </script>
</body>
</html>
