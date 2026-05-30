/**
 * Custom JavaScript
 * Inventory Management System
 */

// Global variables
let cart = [];
let totalAmount = 0;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // POS specific initialization
    if (document.getElementById('pos-page')) {
        initializePOS();
    }
});

// POS Functions
function initializePOS() {
    // Clear cart on load
    cart = [];
    updateCartDisplay();
    
    // Setup event listeners
    document.getElementById('add-to-cart').addEventListener('click', addToCart);
    document.getElementById('process-sale').addEventListener('click', processSale);
    document.getElementById('clear-cart').addEventListener('click', clearCart);
    
    // Product search
    document.getElementById('product-search').addEventListener('input', searchProducts);
    
    // Category filter
    document.getElementById('category-filter').addEventListener('change', filterProducts);
}

function addToCart() {
    const productId = document.getElementById('product-id').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    
    if (!productId || quantity <= 0) {
        showAlert('Please select a product and enter quantity', 'warning');
        return;
    }
    
    // Get product details
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            if (product.stock < quantity) {
                showAlert('Insufficient stock available', 'danger');
                return;
            }
            
            // Check if product already in cart
            const existingItem = cart.find(item => item.product_id === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.push({
                    product_id: productId,
                    name: product.name,
                    price: product.selling_price,
                    quantity: quantity
                });
            }
            
            updateCartDisplay();
            clearProductSelection();
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error fetching product details', 'danger');
        });
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartBody = document.getElementById('cart-items');
    const totalElement = document.getElementById('total-amount');
    
    cartBody.innerHTML = '';
    totalAmount = 0;
    
    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        totalAmount += subtotal;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>${formatCurrency(item.price)}</td>
            <td>${item.quantity}</td>
            <td>${formatCurrency(subtotal)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${item.product_id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        cartBody.appendChild(row);
    });
    
    totalElement.textContent = formatCurrency(totalAmount);
}

function clearCart() {
    cart = [];
    updateCartDisplay();
}

function processSale() {
    if (cart.length === 0) {
        showAlert('Cart is empty', 'warning');
        return;
    }
    
    const paymentMethod = document.getElementById('payment-method').value;
    const discount = parseFloat(document.getElementById('discount').value) || 0;
    
    if (!paymentMethod) {
        showAlert('Please select payment method', 'warning');
        return;
    }
    
    const saleData = {
        items: cart,
        payment_method: paymentMethod,
        discount: discount,
        total_amount: totalAmount - discount
    };
    
    fetch('process_sale.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Sale completed successfully', 'success');
            clearCart();
            // Print receipt or redirect as needed
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        } else {
            showAlert(data.message || 'Error processing sale', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error processing sale', 'danger');
    });
}

function searchProducts() {
    const searchTerm = document.getElementById('product-search').value.toLowerCase();
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        const productName = product.querySelector('.product-name').textContent.toLowerCase();
        const productBarcode = product.querySelector('.product-barcode').textContent.toLowerCase();
        
        if (productName.includes(searchTerm) || productBarcode.includes(searchTerm)) {
            product.style.display = '';
        } else {
            product.style.display = 'none';
        }
    });
}

function filterProducts() {
    const category = document.getElementById('category-filter').value;
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        if (category === '' || product.dataset.category === category) {
            product.style.display = '';
        } else {
            product.style.display = 'none';
        }
    });
}

function selectProduct(productId, productName, price, stock) {
    document.getElementById('product-id').value = productId;
    document.getElementById('selected-product-name').textContent = productName;
    document.getElementById('selected-product-price').textContent = formatCurrency(price);
    document.getElementById('stock-info').textContent = `Stock: ${stock}`;
    document.getElementById('quantity').value = 1;
    document.getElementById('quantity').max = stock;
    document.getElementById('quantity').focus();
}

function clearProductSelection() {
    document.getElementById('product-id').value = '';
    document.getElementById('selected-product-name').textContent = '';
    document.getElementById('selected-product-price').textContent = '';
    document.getElementById('stock-info').textContent = '';
    document.getElementById('quantity').value = 1;
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Product Management Functions
function editProduct(productId) {
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            // Populate edit form
            document.getElementById('edit-product-id').value = product.id;
            document.getElementById('edit-product-name').value = product.name;
            document.getElementById('edit-product-category').value = product.category_id;
            document.getElementById('edit-product-barcode').value = product.barcode;
            document.getElementById('edit-product-cost').value = product.cost_price;
            document.getElementById('edit-product-selling').value = product.selling_price;
            document.getElementById('edit-product-stock').value = product.stock;
            document.getElementById('edit-product-reorder').value = product.reorder_level;
            document.getElementById('edit-product-supplier').value = product.supplier_id;
            document.getElementById('edit-product-expiry').value = product.expiry_date;
            document.getElementById('edit-product-batch').value = product.batch_number;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error fetching product details', 'danger');
        });
}

function deleteProduct(productId) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch('products.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Product deleted successfully', 'success');
                location.reload();
            } else {
                showAlert(data.message || 'Error deleting product', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting product', 'danger');
        });
    }
}

// Purchase Management Functions
function addPurchaseItem() {
    const productId = document.getElementById('product-id').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    const cost = parseFloat(document.getElementById('cost-price').value);
    
    if (!productId || quantity <= 0 || cost <= 0) {
        showAlert('Please fill all fields correctly', 'warning');
        return;
    }
    
    // Add to purchase items table
    const itemsTable = document.getElementById('purchase-items');
    const rowCount = itemsTable.rows.length;
    const row = itemsTable.insertRow(rowCount - 1); // Insert before total row
    
    row.innerHTML = `
        <td>
            <input type="hidden" name="items[${rowCount}][product_id]" value="${productId}">
            <span id="product-name-${rowCount}"></span>
        </td>
        <td><input type="number" name="items[${rowCount}][quantity]" value="${quantity}" class="form-control form-control-sm" onchange="updatePurchaseTotal()"></td>
        <td><input type="number" name="items[${rowCount}][cost_price]" value="${cost}" step="0.01" class="form-control form-control-sm" onchange="updatePurchaseTotal()"></td>
        <td><span id="subtotal-${rowCount}">${formatCurrency(quantity * cost)}</span></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removePurchaseItem(this)">×</button></td>
    `;
    
    // Fetch product name
    fetch(`get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(product => {
            document.getElementById(`product-name-${rowCount}`).textContent = product.name;
        });
    
    updatePurchaseTotal();
    clearPurchaseItemForm();
}

function removePurchaseItem(button) {
    const row = button.closest('tr');
    row.remove();
    updatePurchaseTotal();
}

function updatePurchaseTotal() {
    const itemsTable = document.getElementById('purchase-items');
    const rows = itemsTable.rows;
    let total = 0;
    
    for (let i = 0; i < rows.length - 1; i++) { // Exclude total row
        const quantity = parseFloat(rows[i].cells[1].querySelector('input').value) || 0;
        const cost = parseFloat(rows[i].cells[2].querySelector('input').value) || 0;
        total += quantity * cost;
        
        // Update subtotal
        rows[i].cells[3].querySelector('span').textContent = formatCurrency(quantity * cost);
    }
    
    // Update total row
    rows[rows.length - 1].cells[1].textContent = formatCurrency(total);
}

function clearPurchaseItemForm() {
    document.getElementById('product-id').value = '';
    document.getElementById('quantity').value = 1;
    document.getElementById('cost-price').value = '';
}

// Report Functions
function exportToCSV(reportType) {
    const form = document.getElementById('report-form');
    const formData = new FormData(form);
    formData.append('export', 'csv');
    formData.append('report_type', reportType);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${reportType}_report_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error exporting report', 'danger');
    });
}

// Dashboard auto-refresh toggle
function toggleAutoRefresh() {
    const checkbox = document.getElementById('auto-refresh');
    if (checkbox.checked) {
        // Start auto-refresh every 30 seconds
        window.refreshInterval = setInterval(() => {
            window.location.reload();
        }, 30000);
    } else {
        // Stop auto-refresh
        clearInterval(window.refreshInterval);
    }
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter to submit forms
    if (e.ctrlKey && e.key === 'Enter') {
        const activeElement = document.activeElement;
        if (activeElement.form) {
            activeElement.form.submit();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modal = bootstrap.Modal.getInstance(openModal);
            modal.hide();
        }
    }
});
