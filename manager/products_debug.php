<?php
/**
 * Simple Products Page Debug
 * This creates a minimal working version to test edit/delete
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/currency.php';

requireRole('Manager');

$pageTitle = 'Products Management - Debug Version';

// Get products
$products = fetchAll("SELECT p.*, c.name as category_name, s.name as supplier_name, cur.code as currency_code, cur.symbol as currency_symbol
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN suppliers s ON p.supplier_id = s.id
                     LEFT JOIN currencies cur ON p.currency_id = cur.id
                     WHERE p.is_active = 1
                     ORDER BY p.name ASC
                     LIMIT 10");

$currencies = getCurrencies();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1>Products Management - Debug Version</h1>
        <p class="text-muted">This is a simplified version to test edit/delete functionality</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Products List (Debug)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Cost Price</th>
                                <th>Selling Price</th>
                                <th>Currency</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td><?php echo formatCurrency($product['cost_price'], $product['currency_id']); ?></td>
                                    <td><?php echo formatCurrency($product['selling_price'], $product['currency_id']); ?></td>
                                    <td><?php echo $product['currency_code']; ?></td>
                                    <td><?php echo $product['quantity_in_stock']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="testEdit(<?php echo $product['id']; ?>)">
                                            <i class="bi bi-pencil"></i> Test Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="testDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>')">
                                            <i class="bi bi-trash"></i> Test Delete
                                        </button>
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

<!-- Simple Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="editResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Simple Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Delete Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteProductName"></span>"?</p>
                <div id="deleteResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Confirm Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentProductId = null;

function testEdit(productId) {
    console.log('Testing edit for product ID:', productId);
    currentProductId = productId;
    
    document.getElementById('editResult').innerHTML = 'Loading product data...';
    
    fetch('<?php echo BASE_URL; ?>/manager/get_product.php?id=' + productId)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Product data:', data);
            
            if (data.error) {
                document.getElementById('editResult').innerHTML = 
                    '<div class="alert alert-danger">Error: ' + data.error + '</div>';
            } else {
                document.getElementById('editResult').innerHTML = 
                    '<div class="alert alert-success">' +
                    '<h6>Product Data Loaded:</h6>' +
                    '<pre>' + JSON.stringify(data, null, 2) + '</pre>' +
                    '</div>';
            }
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('editResult').innerHTML = 
                '<div class="alert alert-danger">Error: ' + error.message + '</div>';
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
}

function testDelete(productId, productName) {
    console.log('Testing delete for product ID:', productId, productName);
    currentProductId = productId;
    
    document.getElementById('deleteProductName').textContent = productName;
    document.getElementById('deleteResult').innerHTML = '';
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function confirmDelete() {
    console.log('Confirming delete for product ID:', currentProductId);
    
    document.getElementById('deleteResult').innerHTML = 'Processing delete...';
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', currentProductId);
    
    fetch('<?php echo BASE_URL; ?>/manager/products.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Delete response status:', response.status);
        return response.text();
    })
    .then(text => {
        console.log('Delete response:', text);
        
        document.getElementById('deleteResult').innerHTML = 
            '<div class="alert alert-info">' +
            '<h6>Delete Response:</h6>' +
            '<pre>' + text.substring(0, 500) + '...</pre>' +
            '</div>';
            
        // Refresh page after 2 seconds
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Delete error:', error);
        document.getElementById('deleteResult').innerHTML = 
            '<div class="alert alert-danger">Delete Error: ' + error.message + '</div>';
    });
}

// Test Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    console.log('Bootstrap available:', typeof bootstrap !== 'undefined');
    
    if (typeof bootstrap === 'undefined') {
        alert('Bootstrap is not loaded! Modals will not work.');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
