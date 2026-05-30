-- Inventory Management System Database
-- Compatible with MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (name, description) VALUES 
('Admin', 'System administrator with full access'),
('Manager', 'Store manager/pharmacist with inventory management access'),
('Cashier', 'Cashier with sales access only');

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Suppliers table
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Drugs', 'Pharmaceutical products and medicines'),
('Groceries', 'Food items and household essentials'),
('Beverages', 'Drinks and liquids'),
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and textiles'),
('Other', 'Miscellaneous items');

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    barcode VARCHAR(50),
    cost_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity_in_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    supplier_id INT,
    expiry_date DATE,
    batch_number VARCHAR(50),
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- Purchases table
CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    purchase_date DATE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Purchase items table
CREATE TABLE purchase_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    cost_price DECIMAL(10,2) NOT NULL,
    expiry_date DATE,
    batch_number VARCHAR(50),
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Sales table
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    customer_name VARCHAR(100),
    notes TEXT,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Sale items table
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Activity logs table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, full_name, email, role_id) VALUES 
('admin', '$2y$10$E8ZzZzZzZzZzZzZzZzZzZO9Q5v5h5h5h5h5h5h5h5h5h5h5h5h5h5h5h5h5', 'System Administrator', 'admin@inventory.com', 1);

-- Insert sample suppliers
INSERT INTO suppliers (name, contact_person, phone, email, address) VALUES 
('MedSupply Corp', 'John Smith', '+1234567890', 'john@medsupply.com', '123 Pharma St, City'),
('Fresh Foods Ltd', 'Mary Johnson', '+1234567891', 'mary@freshfoods.com', '456 Market Ave, City'),
('Tech Distributors', 'Bob Wilson', '+1234567892', 'bob@techdist.com', '789 Tech Blvd, City');

-- Insert sample products
INSERT INTO products (name, category_id, barcode, cost_price, selling_price, quantity_in_stock, reorder_level, supplier_id, expiry_date, batch_number, description) VALUES 
('Paracetamol 500mg', 1, '1234567890123', 2.50, 5.00, 100, 20, 1, '2024-12-31', 'BATCH001', 'Pain relief medication'),
('Amoxicillin 500mg', 1, '1234567890124', 8.00, 15.00, 50, 15, 1, '2024-10-31', 'BATCH002', 'Antibiotic medication'),
('Rice 5kg', 2, '1234567890125', 10.00, 15.00, 30, 10, 2, '2025-06-30', 'BATCH003', 'Premium quality rice'),
('Bread', 2, '1234567890126', 1.50, 2.50, 20, 5, 2, '2024-02-10', 'BATCH004', 'Fresh bread'),
('Coca-Cola 1L', 3, '1234567890127', 1.00, 2.00, 40, 10, 2, '2024-08-31', 'BATCH005', 'Soft drink'),
('Orange Juice 1L', 3, '1234567890128', 1.20, 2.50, 25, 8, 2, '2024-07-31', 'BATCH006', 'Fresh orange juice');

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_supplier ON products(supplier_id);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_expiry ON products(expiry_date);
CREATE INDEX idx_purchases_supplier ON purchases(supplier_id);
CREATE INDEX idx_purchases_date ON purchases(purchase_date);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sale_items_product ON sale_items(product_id);
CREATE INDEX idx_purchase_items_product ON purchase_items(product_id);

-- Create view for current stock with alerts
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
        WHEN p.expiry_date <= DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY) THEN 'Near Expiry'
        WHEN p.expiry_date < CURRENT_DATE THEN 'Expired'
        ELSE 'Normal'
    END as alert_status
FROM products p
JOIN categories c ON p.category_id = c.id
WHERE p.is_active = TRUE;

-- Create view for sales summary
CREATE VIEW sales_summary AS
SELECT 
    DATE(sale_date) as sale_date,
    COUNT(*) as total_sales,
    SUM(total_amount) as total_revenue,
    SUM(final_amount) as net_revenue
FROM sales
GROUP BY DATE(sale_date);

-- Create view for purchase summary
CREATE VIEW purchase_summary AS
SELECT 
    purchase_date,
    COUNT(*) as total_purchases,
    SUM(total_amount) as total_cost
FROM purchases
GROUP BY purchase_date;
