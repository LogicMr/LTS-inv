<?php
/**
 * Point of Sale (POS) System
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/currency.php';

requireRole('Cashier');

$pageTitle = 'Point of Sale';
$bodyClass = 'cashier-pos'; // Add this line
require_once __DIR__ . '/../includes/header.php';

// Get products for POS
$products = fetchAll("SELECT p.*, c.name as category_name, p.description as product_description
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     WHERE p.is_active = 1 AND p.quantity_in_stock > 0
                     ORDER BY c.name, p.name");

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories ORDER BY name ASC");
?>

<style>
.pos-container {
    display: flex;
    gap: 20px;
    height: calc(100vh - 250px);
    margin-bottom: 50px;
}

.pos-products {
    flex: 1;
    overflow-y: auto;
}

.pos-cart {
    width: 400px;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

/* POS-specific footer styling */
body.cashier-pos footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: -1;
    margin-top: 0 !important;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
}

.product-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}

.product-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.product-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
}

.product-name {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 14px;
}

.product-price {
    color: #28a745;
    font-size: 16px;
    font-weight: bold;
}

.product-stock {
    font-size: 12px;
    color: #666;
}

.product-description {
    font-size: 11px;
    color: #007bff;
    margin-top: 5px;
    cursor: help;
    display: flex;
    align-items: center;
    gap: 3px;
}

.product-description:hover {
    color: #0056b3;
    text-decoration: underline;
}

.product-card:hover {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.cart-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 4px;
    margin-bottom: 10px;
}

.cart-item-info {
    flex: 1;
}

.cart-item-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    cursor: pointer;
}

.quantity-display {
    min-width: 40px;
    text-align: center;
    font-weight: bold;
}

.pos-summary {
    border-top: 2px solid #dee2e6;
    padding-top: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.price-edit-container {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
}

.price-edit-input {
    width: 80px;
    padding: 2px 5px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
    text-align: right;
}

.price-edit-input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.price-edit-input.price-modified {
    background-color: #fff3cd;
    border-color: #ffc107;
    font-weight: bold;
}

.price-modified-indicator {
    font-size: 12px;
    color: #856404;
    margin-left: 5px;
}

.btn-xs {
    padding: 2px 4px;
    font-size: 10px;
    line-height: 1;
    border-radius: 2px;
    margin-left: 2px;
}

.btn-outline-warning {
    border: 1px solid #ffc107;
    color: #856404;
    background-color: transparent;
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    color: #212529;
}

.summary-row.total {
    font-size: 18px;
    font-weight: bold;
    color: #007bff;
}

.payment-section {
    margin-top: 20px;
}

.payment-methods {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.payment-methods label {
    flex: 1;
    text-align: center;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-methods input[type="radio"] {
    display: none;
}

.payment-methods input[type="radio"]:checked + label {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.pos-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.pos-actions button {
    flex: 1;
}

.category-filter {
    margin-bottom: 20px;
}

.search-box {
    margin-bottom: 20px;
}

.search-hint {
    margin-top: 5px;
    font-size: 11px;
}

.product-card:hover {
    background: #ffc107;
    color: #212529;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
}

.low-stock-badge {
    background: #ffc107;
    color: #212529;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    margin-left: 5px;
}
</style>

<div class="pos-container">
    <!-- Products Section -->
    <div class="pos-products">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" class="form-control" id="productSearch" 
                           placeholder="Search products or scan barcode..." 
                           autocomplete="off">
                    <div class="search-hint">
                        <small class="text-muted">
                            <i class="bi bi-upc-scan"></i> Type product name or scan barcode
                        </small>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="category-filter">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Product Grid -->
                <div class="product-grid" id="productGrid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" 
                             data-product-id="<?php echo $product['id']; ?>"
                             data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                             data-product-price="<?php echo $product['selling_price']; ?>"
                             data-product-currency-symbol="TSh"
                             data-product-stock="<?php echo $product['quantity_in_stock']; ?>"
                             data-category-id="<?php echo $product['category_id']; ?>"
                             data-product-description="<?php echo htmlspecialchars($product['description'] ?? ''); ?>"
                             data-product-barcode="<?php echo htmlspecialchars($product['barcode'] ?? ''); ?>"
                             data-product-batch="<?php echo htmlspecialchars($product['batch_number'] ?? ''); ?>"
                             onclick="addToCart(this)">
                            <div class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                                <?php if ($product['quantity_in_stock'] <= $product['reorder_level']): ?>
                                    <span class="low-stock-badge">Low Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-price">TSh <?php echo number_format($product['selling_price'], 2); ?></div>
                            <div class="product-stock">Stock: <?php echo $product['quantity_in_stock']; ?></div>
                            <?php if (!empty($product['description'])): ?>
                                <div class="product-description" onclick="showProductDescription(event, this)" title="Click to view full description">
                                    <i class="bi bi-info-circle"></i> Description available
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Section -->
    <div class="pos-cart">
        <h5>Shopping Cart</h5>
        
        <div class="cart-items" id="cartItems">
            <!-- Cart items will be added here dynamically -->
            <div class="text-center text-muted py-4">
                <i class="bi bi-cart" style="font-size: 2rem;"></i>
                <p>Your cart is empty</p>
            </div>
        </div>
        
        <div class="pos-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="subtotal">TSh 0.00</span>
            </div>
            <div class="summary-row">
                <span>Discount:</span>
                <span id="discount">TSh 0.00</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="total">TSh 0.00</span>
            </div>
            <div class="summary-row" id="currencyIndicator" style="display: none; font-size: 12px; color: #666;">
                <span>Prices shown in: <strong id="currencyName">-</strong></span>
            </div>
        </div>
        
        <div class="payment-section">
            <div class="mb-3">
                <label for="discountAmount" class="form-label">Discount Amount</label>
                <input type="number" class="form-control" id="discountAmount" step="0.01" min="0" value="0" onchange="updateTotals()">
            </div>
            
            <div class="payment-methods">
                <input type="radio" name="paymentMethod" id="paymentCash" value="Cash" checked>
                <label for="paymentCash">Cash</label>
                
                <input type="radio" name="paymentMethod" id="paymentCard" value="Card">
                <label for="paymentCard">Card</label>
                
                <input type="radio" name="paymentMethod" id="paymentOther" value="Other">
                <label for="paymentOther">Other</label>
            </div>
            
            <div class="mb-3">
                <label for="customerName" class="form-label">Customer Name (Optional)</label>
                <input type="text" class="form-control" id="customerName" placeholder="Enter customer name">
            </div>
            
            <div class="mb-3">
                <label for="saleNotes" class="form-label">Notes (Optional)</label>
                <textarea class="form-control" id="saleNotes" rows="2" placeholder="Add notes..."></textarea>
            </div>
        </div>
        
        <div class="pos-actions">
            <button class="btn btn-secondary" onclick="clearCart()">Clear Cart</button>
            <button class="btn btn-success" onclick="processSale()">Complete Sale</button>
        </div>
    </div>
</div>

<script>
// Currency mapping from PHP
const currencies = <?php 
    $currencies = getCurrencies();
    $currencyMap = [];
    foreach ($currencies as $currency) {
        $currencyMap[$currency['id']] = [
            'code' => $currency['code'],
            'symbol' => $currency['symbol']
        ];
    }
    echo json_encode($currencyMap);
?>;

// JavaScript formatCurrency function
function formatCurrency(amount, currencyId = null) {
    if (currencyId && currencies[currencyId]) {
        const currency = currencies[currencyId];
        const formattedAmount = amount.toFixed(2);
        
        // Some currencies put symbol before, some after
        const symbolBefore = ['$', '€', '£', '¥', '₹', 'R'];
        
        if (symbolBefore.includes(currency.symbol)) {
            return currency.symbol + formattedAmount;
        } else {
            return formattedAmount + ' ' + currency.symbol;
        }
    }
    
    // Default fallback
    return '$' + amount.toFixed(2);
}

let cart = [];
let cartItemIdCounter = 0;

// Search functionality
document.getElementById('productSearch').addEventListener('input', function() {
    performSearch(this.value.toLowerCase().trim());
});

// Support Enter key for barcode scanning
document.getElementById('productSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const searchTerm = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.product-card');
        
        // Look for exact barcode match
        let exactMatch = null;
        cards.forEach(card => {
            const productBarcode = card.dataset.productBarcode?.toLowerCase() || '';
            if (productBarcode === searchTerm) {
                exactMatch = card;
            }
        });
        
        if (exactMatch) {
            // Auto-add exact barcode match
            addToCart(exactMatch.querySelector('.product-name').parentElement);
            this.value = ''; // Clear search for next scan
        }
    }
});

function performSearch(searchTerm) {
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        const productName = card.dataset.productName.toLowerCase();
        const productBarcode = card.dataset.productBarcode?.toLowerCase() || '';
        const productBatch = card.dataset.productBatch?.toLowerCase() || '';
        
        // Reset styles
        card.style.border = '';
        card.style.boxShadow = '';
        
        // Search by name, barcode, or batch number
        if (searchTerm === '') {
            card.style.display = 'block';
        } else if (productName.includes(searchTerm) || 
                   productBarcode.includes(searchTerm) || 
                   productBatch.includes(searchTerm)) {
            card.style.display = 'block';
            
            // Highlight exact barcode match
            if (productBarcode === searchTerm) {
                card.style.border = '2px solid #28a745';
                card.style.boxShadow = '0 0 10px rgba(40, 167, 69, 0.3)';
            }
        } else {
            card.style.display = 'none';
        }
    });
}

// Category filter
document.getElementById('categoryFilter').addEventListener('change', function() {
    const categoryId = this.value;
    const cards = document.querySelectorAll('.product-card');
    
    cards.forEach(card => {
        if (!categoryId || card.dataset.categoryId === categoryId) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

function addToCart(element) {
    const productId = element.dataset.productId;
    const productName = element.dataset.productName;
    const productPrice = parseFloat(element.dataset.productPrice);
    const productCurrencySymbol = element.dataset.productCurrencySymbol;
    const productStock = parseInt(element.dataset.productStock);
    
    if (productStock <= 0) {
        alert('This product is out of stock');
        return;
    }
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        if (existingItem.quantity >= productStock) {
            alert('Cannot exceed available stock');
            return;
        }
        existingItem.quantity += 1;
        existingItem.subtotal = existingItem.quantity * existingItem.price;
    } else {
        cart.push({
            id: cartItemIdCounter++,
            productId: productId,
            productName: productName,
            price: productPrice,
            originalPrice: productPrice, // Store original price
            currencySymbol: 'TSh',
            quantity: 1,
            subtotal: productPrice,
            availableStock: productStock
        });
    }
    
    renderCart();
    updateTotals();
}

function removeFromCart(itemId) {
    cart = cart.filter(item => item.id !== itemId);
    renderCart();
    updateTotals();
}

function updateQuantity(itemId, change) {
    const item = cart.find(item => item.id === itemId);
    if (!item) return;
    
    const newQuantity = item.quantity + change;
    
    if (newQuantity <= 0) {
        removeFromCart(itemId);
    } else if (newQuantity <= item.availableStock) {
        item.quantity = newQuantity;
        item.subtotal = newQuantity * item.price;
        renderCart();
        updateTotals();
    } else {
        alert('Cannot exceed available stock');
    }
}

function updateItemPrice(itemId, newPrice) {
    const item = cart.find(item => item.id === itemId);
    if (!item) return;
    
    const price = parseFloat(newPrice);
    if (isNaN(price) || price < 0) {
        alert('Please enter a valid price');
        renderCart(); // Reset to original price
        return;
    }
    
    item.price = price;
    item.subtotal = item.quantity * price;
    renderCart();
    updateTotals();
}

function resetItemPrice(itemId) {
    const item = cart.find(item => item.id === itemId);
    if (!item) return;
    
    item.price = item.originalPrice;
    item.subtotal = item.quantity * item.originalPrice;
    renderCart();
    updateTotals();
}

function renderCart() {
    const cartItemsDiv = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        cartItemsDiv.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-cart" style="font-size: 2rem;"></i>
                <p>Your cart is empty</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    cart.forEach(item => {
        const priceModified = item.price !== item.originalPrice;
        html += `
            <div class="cart-item">
                <div class="cart-item-info">
                    <div>${item.productName}</div>
                    <div class="price-edit-container">
                        <small>Price:</small>
                        <input type="number" 
                               class="price-edit-input ${priceModified ? 'price-modified' : ''}" 
                               value="${item.price.toFixed(2)}" 
                               step="0.01" 
                               min="0"
                               onchange="updateItemPrice(${item.id}, this.value)"
                               onclick="this.select()">
                        <small>${item.currencySymbol} each</small>
                        ${priceModified ? '<span class="price-modified-indicator">📝</span>' : ''}
                        ${priceModified ? `<button class="btn btn-xs btn-outline-warning" onclick="resetItemPrice(${item.id})" title="Reset to original price">↺</button>` : ''}
                    </div>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                        <span class="quantity-display">${item.quantity}</span>
                        <button class="quantity-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                    </div>
                    <span>TSh ${item.subtotal.toFixed(2)}</span>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    cartItemsDiv.innerHTML = html;
}

function updateTotals() {
    // Calculate subtotal
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    
    // Hide currency indicator since we only use TSh
    document.getElementById('currencyIndicator').style.display = 'none';
    
    // Display totals with TSh
    document.getElementById('subtotal').textContent = 'TSh ' + subtotal.toFixed(2);
    document.getElementById('discount').textContent = 'TSh ' + discount.toFixed(2);
    document.getElementById('total').textContent = 'TSh ' + total.toFixed(2);
}

function clearCart() {
    if (cart.length === 0) return;
    
    cart = [];
    renderCart();
    updateTotals();
    document.getElementById('discountAmount').value = 0;
}

function processSale() {
    if (cart.length === 0) {
        alert('Your cart is empty');
        return;
    }
    
    const subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    
    if (total <= 0) {
        alert('Total amount must be greater than 0');
        return;
    }
    
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
    const customerName = document.getElementById('customerName').value;
    const saleNotes = document.getElementById('saleNotes').value;
    
    // Prepare sale data
    const saleData = {
        items: cart.map(item => ({
            product_id: item.productId,
            quantity: item.quantity,
            selling_price: item.price,
            subtotal: item.subtotal
        })),
        total_amount: subtotal,
        discount_amount: discount,
        final_amount: total,
        payment_method: paymentMethod,
        customer_name: customerName,
        notes: saleNotes
    };
    
    // Send sale data to server
    fetch('<?php echo BASE_URL; ?>/cashier/process_sale.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(saleData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSaleSuccessPopup(data.sale_id);
            clearCart();
        } else {
            alert('Error processing sale: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error processing sale');
    });
}

// Professional Sale Success Popup
function showSaleSuccessPopup(saleId) {
    // Create popup container
    const popup = document.createElement('div');
    popup.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        z-index: 10000;
        min-width: 400px;
        text-align: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        animation: slideIn 0.3s ease-out;
    `;
    
    popup.innerHTML = `
        <div style="display: flex; align-items: center; margin-bottom: 20px;">
            <div style="width: 60px; height: 60px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #28a745;">
                    <path d="M20 6L9 17l-5-5L4 6l7 7 7 7"/>
                    <path d="M22 12h-4"/>
                    <path d="M7 12H3"/>
                    <path d="M12 22v-4"/>
                    <path d="M12 6v2"/>
                    <path d="m16 16-3 4 4m0 0-4 4-4"/>
                </svg>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 24px; font-weight: 600;">Sale Completed Successfully!</h2>
                <p style="margin: 5px 0; font-size: 16px; opacity: 0.9;">Transaction processed successfully</p>
            </div>
        </div>
        
        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #ffd700; font-weight: 600;">Sale ID:</span>
                <span style="font-weight: 600;">#${String(saleId).padStart(6, '0')}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="color: #ffd700; font-weight: 600;">Status:</span>
                <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">COMPLETED</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #ffd700; font-weight: 600;">Time:</span>
                <span>${new Date().toLocaleString()}</span>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="printReceiptFromPopup(${saleId})" style="background: linear-gradient(135deg, #87CEEB 0%, #4682B4 100%); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2H4a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0 0-2-2z"/>
                    <path d="M14 2H6"/>
                    <path d="M12 10v6"/>
                    <path d="M16 14h-4"/>
                    <path d="M8 14H4"/>
                </svg>
                Print Receipt
            </button>
            <button onclick="continueSaleFromPopup()" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(173, 216, 230, 0.5); padding: 12px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                Continue
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(popup);
    
    // Don't auto-close - let user decide when to close
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
    `;
    document.head.appendChild(style);
}

function printReceiptFromPopup(saleId) {
    printReceipt(saleId);
}

function continueSaleFromPopup() {
    // Close popup
    closeSalePopup();
    
    // Complete the sale and refresh page
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

function printReceipt(saleId) {
    window.open('<?php echo BASE_URL; ?>/cashier/print_receipt.php?id=' + saleId, '_blank');
}

function showProductDescription(event, element) {
    event.stopPropagation(); // Prevent adding to cart
    
    const productCard = element.closest('.product-card');
    const productName = productCard.dataset.productName;
    const productDescription = productCard.dataset.productDescription;
    
    if (!productDescription) return;
    
    // Create popup
    const popup = document.createElement('div');
    popup.id = 'productDescriptionPopup'; // Add ID for easy targeting
    popup.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        color: #333;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 10000;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        animation: slideIn 0.3s ease-out;
        border: 2px solid #007bff;
    `;
    
    popup.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0; color: #007bff; font-size: 18px;">
                <i class="bi bi-box"></i> ${productName}
            </h3>
            <button onclick="closeProductDescriptionPopup()" 
                    style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">×</button>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
            <h4 style="margin: 0 0 10px 0; color: #495057; font-size: 14px;">Product Description:</h4>
            <p style="margin: 0; line-height: 1.5; color: #333;">${productDescription}</p>
        </div>
        <div style="margin-top: 15px; text-align: center;">
            <button onclick="closeProductDescriptionPopup()" 
                    style="background: #007bff; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; font-weight: 600;">
                Close
            </button>
        </div>
    `;
    
    document.body.appendChild(popup);
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
    `;
    document.head.appendChild(style);
}

function closeProductDescriptionPopup() {
    const popup = document.getElementById('productDescriptionPopup');
    if (popup) {
        popup.style.animation = 'slideIn 0.3s ease-in reverse';
        setTimeout(() => {
            document.body.removeChild(popup);
        }, 300);
    }
}

function closeSalePopup() {
    const popup = document.querySelector('div[style*="position: fixed"]');
    if (popup) {
        popup.style.animation = 'slideIn 0.3s ease-in reverse';
        setTimeout(() => {
            document.body.removeChild(popup);
        }, 300);
    }
}

// Initialize
updateTotals();
</script>

<!-- Back to Dashboard Button -->
<div style="text-align: center; margin: 20px 0;">
    <a href="<?php echo BASE_URL; ?>/cashier/dashboard.php" class="btn btn-secondary btn-lg">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
