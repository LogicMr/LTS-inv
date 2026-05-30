# LTS Inventory Management System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Installation](#installation)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Module Documentation](#module-documentation)
5. [Database Schema](#database-schema)
6. [API Documentation](#api-documentation)
7. [Security Features](#security-features)
8. [Backup & Recovery](#backup--recovery)
9. [Troubleshooting](#troubleshooting)
10. [Development Guide](#development-guide)
11. [Recent Updates & Features](#recent-updates--features)
12. [Production Deployment](#production-deployment)

---

## System Overview

### Description
The LTS Inventory Management System is a comprehensive web-based application designed for small retail shops, pharmacies, and mini-supermarkets. Built with pure PHP, MySQL, HTML, CSS, and JavaScript, it provides complete inventory control, sales management, and reporting capabilities with advanced multi-currency support and mobile responsiveness.

### 🆕 Latest Features (Version 1.0.0+)

#### **🎨 Enhanced Reporting System**
- **Professional PDF Export**: Generate branded reports with system name and styling
- **Multi-Currency Reports**: Separate totals by currency type ($X + €Y + £Z)
- **Summary Statistics**: Total stock count, value, and product counts
- **Print Instructions**: Step-by-step PDF generation guide
- **Dynamic Styling**: System-consistent colors and modern gradient design

#### **📱 Mobile & Cross-Device Support**
- **Responsive Design**: Works perfectly on smartphones, tablets, and desktops
- **Cross-Device Sessions**: Login works across multiple devices
- **Touch Interface**: Mobile-optimized user interface
- **Progressive Web App**: Can be installed on mobile devices

#### **🌐 Production-Ready Configuration**
- **Dynamic URL Detection**: Works with any domain or hosting provider
- **Environment Detection**: Auto-configures for production vs development
- **SSL Auto-Detection**: Secure cookies when HTTPS is available
- **Zero-Configuration**: Upload and use immediately on any hosting

#### **💰 Advanced Multi-Currency System**
- **Currency Management**: Add unlimited currencies (USD, EUR, GBP, etc.)
- **Product Pricing**: Different currencies per product
- **Report Separation**: Currency-separated totals in all reports
- **Exchange Rate Ready**: Infrastructure for currency conversion

### Key Features
- **Multi-User System**: Role-based access control (Admin, Manager, Cashier)
- **Product Management**: Complete product lifecycle with barcode support and multi-currency
- **Stock Management**: Real-time stock tracking with low stock and expiry alerts
- **Point of Sale (POS)**: Modern, intuitive sales interface with multi-currency
- **Purchase Management**: Supplier and purchase order tracking
- **Multi-Currency Support**: Handle transactions in multiple currencies
- **Advanced Reporting**: PDF and CSV export with multi-currency support
- **Backup System**: Automated backup and recovery
- **Mobile Responsive**: Works on all devices with cross-device login
- **Production Ready**: Works on any hosting provider with zero configuration

### Technical Stack
- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.x for responsive design
- **Security**: Password hashing, prepared statements, session management
- **Session Management**: Custom session path, cross-device compatible
- **URL Handling**: Dynamic BASE_URL detection for any hosting environment

---

## Installation

### Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP Extensions**: PDO, MySQLi, GD, cURL, JSON

### Installation Steps

1. **Download Files**
   ```bash
   git clone <repository-url>
   # or extract ZIP file to web directory
   ```

2. **Configure Database**
   ```sql
   CREATE DATABASE inventory_system;
   -- Import database.sql file
   ```

3. **Update Configuration**
   ```php
   // config/config.php
   define('BASE_URL', 'http://your-domain.com/path');
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'inventory_system');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   ```

4. **Set Permissions**
   ```bash
   chmod 755 /path/to/system
   chmod 777 /path/to/system/backups
   ```

5. **Access System**
   - URL: `http://your-domain.com/path/`
   - Default Admin: `admin/admin123`

---

## User Roles & Permissions

### Role Hierarchy

#### Administrator (Admin)
- **Full System Access**: All modules and functions
- **User Management**: Create, edit, delete users
- **System Settings**: Configure system parameters
- **Backup Management**: Create, download, import backups
- **Database Operations**: Full database access

#### Manager
- **Product Management**: Add, edit, delete products
- **Purchase Management**: Create and manage purchase orders
- **Stock Management**: View stock alerts, manage inventory
- **Sales Reports**: Access all sales reports and analytics
- **Supplier Management**: Manage supplier information

#### Cashier
- **Point of Sale**: Process sales transactions
- **Product Viewing**: View product information and stock levels
- **Daily Sales**: View own sales dashboard
- **Receipt Printing**: Generate and print sales receipts

### Access Control Matrix

| Module | Admin | Manager | Cashier |
|---------|--------|----------|----------|
| Dashboard | ✅ | ✅ | ✅ |
| Users | ✅ | ❌ | ❌ |
| Products | ✅ | ✅ | 👁 |
| Purchases | ✅ | ✅ | ❌ |
| Sales Reports | ✅ | ✅ | ❌ |
| POS | ✅ | ❌ | ✅ |
| Stock Alerts | ✅ | ✅ | ❌ |
| Settings | ✅ | ❌ | ❌ |
| Backup | ✅ | ❌ | ❌ |

*👁 = View-only access*

---

## Module Documentation

### 1. Authentication Module (`/auth/`)

#### Login System
- **File**: `auth/login.php`
- **Features**: Session-based authentication, password hashing
- **Security**: Brute force protection, session timeout

#### Session Management
- **Duration**: 1 hour (configurable)
- **Security**: Regeneration on login, secure cookie handling
- **Logout**: Complete session destruction

### 2. User Management (`/admin/users.php`)

#### User CRUD Operations
- **Create User**: Add new system users with role assignment
- **Edit User**: Modify user details and permissions
- **Delete User**: Remove users (with transaction checking)
- **Role Assignment**: Assign appropriate system roles

#### Password Security
- **Hashing**: Bcrypt with cost factor 10
- **Password Reset**: Secure password reset functionality
- **Password Policy**: Minimum length requirements

### 3. Product Management (`/manager/products.php`)

#### Product Information
- **Basic Details**: Name, description, category
- **Pricing**: Cost price, selling price, currency support
- **Inventory**: Stock quantity, reorder levels
- **Tracking**: Barcode, batch numbers, expiry dates
- **Supplier**: Link to supplier information

#### Product Features
- **Multi-Currency**: Support for different currencies
- **Stock Alerts**: Automatic low stock notifications
- **Expiry Tracking**: Monitor product expiration dates
- **Barcode Support**: Scan and search by barcode

### 4. Point of Sale (`/cashier/pos.php`)

#### Sales Process
- **Product Selection**: Search, scan, or browse products
- **Cart Management**: Add, modify, remove items
- **Price Editing**: Override prices for special cases
- **Payment Processing**: Multiple payment methods
- **Receipt Generation**: Automatic receipt creation

#### POS Features
- **Real-time Stock**: Update inventory on sale
- **Multi-Currency**: Handle transactions in different currencies
- **Customer Management**: Optional customer assignment
- **Discount Support**: Apply discounts to sales
- **Quick Actions**: Fast product addition shortcuts

### 5. Purchase Management (`/manager/purchases.php`)

#### Purchase Orders
- **Supplier Selection**: Choose from registered suppliers
- **Product Ordering**: Add products to purchase order
- **Quantity Management**: Track ordered vs received quantities
- **Price Tracking**: Record purchase prices

#### Purchase Features
- **Stock Updates**: Automatic inventory increase
- **Cost Tracking**: Update product cost prices
- **Batch Management**: Track purchase batches
- **Supplier History**: Maintain supplier records

### 6. Reporting System (`/reports/`)

#### Sales Reports
- **Daily Reports**: Sales by day with currency breakdown
- **Monthly Reports**: Monthly sales summaries
- **Detailed Reports**: Individual transaction details
- **Summary Reports**: Overall business metrics

#### Report Features
- **Multi-Currency**: Reports in transaction currencies
- **CSV Export**: Download reports for external analysis
- **Date Filtering**: Custom date range reports
- **Payment Analysis**: Breakdown by payment methods

### 7. Backup System (`/admin/backup_*.php`)

#### Backup Types
- **Quick Backup**: Instant server backup creation
- **Export Backup**: Download complete database backup
- **Import Backup**: Restore from backup file
- **Backup History**: Track recent backups

#### Backup Features
- **Complete Database**: All tables and data included
- **Fresh Install Support**: CREATE DATABASE IF NOT EXISTS
- **Safe Import**: Transaction-based restoration
- **Security**: Admin-only access, file validation

---

## Database Schema

### Core Tables

#### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role_id INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Products Table
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    barcode VARCHAR(100),
    cost_price DECIMAL(10,2) NOT NULL,
    selling_price DECIMAL(10,2) NOT NULL,
    quantity_in_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 0,
    category_id INT,
    supplier_id INT,
    currency_id INT,
    expiry_date DATE,
    batch_number VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Sales Table
```sql
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    customer_name VARCHAR(100),
    notes TEXT,
    currency_id INT,
    created_by INT NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Sale Items Table
```sql
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    currency_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Currencies Table
```sql
CREATE TABLE currencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    exchange_rate DECIMAL(10,4) DEFAULT 1.0000,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Relationships
- **Users → Roles**: Many-to-one relationship
- **Products → Categories**: Many-to-one relationship
- **Products → Suppliers**: Many-to-one relationship
- **Products → Currencies**: Many-to-one relationship
- **Sales → Users**: Many-to-one relationship (created_by)
- **Sales → Currencies**: Many-to-one relationship
- **Sale Items → Sales**: Many-to-one relationship
- **Sale Items → Products**: Many-to-one relationship

---

## API Documentation

### Authentication Endpoints

#### POST `/auth/login.php`
```json
{
    "username": "string",
    "password": "string"
}
```

**Response**:
```json
{
    "success": true,
    "user": {
        "id": 1,
        "username": "admin",
        "full_name": "Administrator",
        "role": "Admin"
    }
}
```

#### POST `/auth/logout.php`
- **Purpose**: Terminate user session
- **Response**: Redirect to login page

### Product Endpoints

#### GET `/manager/products.php`
- **Purpose**: Retrieve products with filtering
- **Parameters**:
  - `search`: Search term for products
  - `category`: Filter by category ID
  - `page`: Pagination page number

#### POST `/manager/products.php`
- **Purpose**: CRUD operations on products
- **Actions**:
  - `add`: Create new product
  - `edit`: Update existing product
  - `delete`: Deactivate/delete product

### Sales Endpoints

#### POST `/cashier/process_sale.php`
```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2,
            "unit_price": 25.00,
            "subtotal": 50.00
        }
    ],
    "total_amount": 50.00,
    "discount_amount": 0,
    "final_amount": 50.00,
    "payment_method": "Cash",
    "currency_id": 1
}
```

---

## Security Features

### Authentication Security
- **Password Hashing**: Bcrypt with cost factor 10
- **Session Management**: Secure session configuration
- **CSRF Protection**: Token-based form validation
- **Brute Force Protection**: Login attempt limiting

### Input Validation
- **SQL Injection Prevention**: Prepared statements only
- **XSS Protection**: Output encoding and sanitization
- **File Upload Security**: Type validation and size limits
- **Input Sanitization**: Custom sanitization functions

### Access Control
- **Role-Based Access**: Module-level permission system
- **Session Validation**: Active session verification
- **Page Protection**: Role checks on sensitive pages
- **Navigation Security**: Context-aware menu display

### Data Protection
- **Backup Encryption**: Secure backup storage
- **Error Handling**: Secure error message display
- **Logging**: Comprehensive activity logging
- **Data Validation**: Server-side validation rules

---

## Backup & Recovery

### Backup Types

#### Quick Backup
- **Location**: Server storage (`/backups/quick_backup.sql`)
- **Trigger**: Manual button click
- **Content**: Complete database with structure and data
- **Format**: SQL with CREATE DATABASE IF NOT EXISTS

#### Export Backup
- **Location**: Client download
- **Format**: Timestamped SQL file
- **Content**: Complete database backup
- **Features**: Automatic file download

#### Import Backup
- **Validation**: File type and size validation
- **Process**: Transaction-based restoration
- **Safety**: Rollback on errors
- **Confirmation**: Warning before data replacement

### Backup File Structure
```sql
-- Inventory Management System Database Backup
-- Generated: 2025-02-17 14:30:00
-- Database: inventory_system
-- Compatible with MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- Table structure for `products`
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  -- ... other columns
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4;

-- Data for `products`
INSERT INTO `products` VALUES
(1, 'Product Name', 15.00, ...),
(2, 'Another Product', 20.00, ...);
```

### Recovery Procedures
1. **Access Backup Section**: Admin dashboard → Backup Management
2. **Choose Backup Type**: Quick backup or file import
3. **Execute Recovery**: Follow import wizard with confirmations
4. **Verify Restoration**: Check data integrity after import

---

## Troubleshooting

### Common Issues

#### Login Problems
**Issue**: Cannot login with correct credentials
**Solutions**:
- Check database connection in `config/config.php`
- Verify user account is active
- Clear browser cookies and cache
- Check PHP session configuration

#### Database Connection Errors
**Issue**: "Connection failed" messages
**Solutions**:
- Verify database server is running
- Check database credentials in config
- Test database user permissions
- Ensure PDO MySQL extension is enabled

#### Permission Issues
**Issue**: "Access denied" errors
**Solutions**:
- Verify user role assignments
- Check module access permissions
- Clear session and re-login
- Review role configuration

#### Performance Issues
**Issue**: Slow page loading
**Solutions**:
- Optimize database indexes
- Check server resources
- Enable PHP OPcache
- Review database query performance

### Debug Mode
Enable debugging by modifying `config/config.php`:
```php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable debug mode
define('DEBUG_MODE', true);
```

### Log Files
- **Application Logs**: `/logs/application.log`
- **Error Logs**: `/logs/error.log`
- **Access Logs**: Database `activity_logs` table
- **Backup Logs`: `/logs/backup.log`

---

## Development Guide

### Code Structure
```
/
├── admin/           # Administrator modules
├── manager/          # Manager modules
├── cashier/          # Cashier modules
├── reports/           # Reporting modules
├── auth/              # Authentication
├── includes/          # Shared components
├── config/            # Configuration files
└── backups/           # Backup storage
```

### Adding New Modules

#### 1. Create Module File
```php
<?php
/**
 * New Module
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();
requireRole('Manager'); // or 'Admin', 'Cashier'

$pageTitle = 'New Module';
require_once __DIR__ . '/../includes/header.php';

// Module logic here

require_once __DIR__ . '/../includes/footer.php';
?>
```

#### 2. Update Navigation
Add new module to `includes/header.php`:
```php
<?php if (canAccessModule('new_module')): ?>
    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/new_module/index.php">New Module</a></li>
<?php endif; ?>
```

#### 3. Add Permissions
Update role permissions in access control system.

### Database Conventions

#### Naming Conventions
- **Tables**: lowercase with underscores (`users`, `sale_items`)
- **Columns**: lowercase with underscores (`first_name`, `created_at`)
- **Primary Keys**: `id` (auto-increment)
- **Foreign Keys**: `{table}_id` (`user_id`, `product_id`)

#### Indexing Strategy
```sql
-- Primary indexes
PRIMARY KEY (`id`)

-- Foreign key indexes
KEY `user_id` (`user_id`),
KEY `product_id` (`product_id`)

-- Search indexes
KEY `idx_products_name` (`name`),
KEY `idx_products_barcode` (`barcode`)
```

### Security Best Practices

#### Input Validation
```php
function cleanInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateRequired($required, $data) {
    $errors = [];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }
    return $errors;
}
```

#### Database Security
```php
// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// Never concatenate user input
// WRONG: "SELECT * FROM users WHERE id = " . $userId
// RIGHT: Use prepared statements
```

### Performance Optimization

#### Database Optimization
```sql
-- Add indexes for performance
ALTER TABLE products ADD INDEX idx_products_name (name);
ALTER TABLE sales ADD INDEX idx_sales_date (sale_date);
ALTER TABLE sale_items ADD INDEX idx_sale_items_product (product_id);
```

#### Caching Strategy
```php
// Simple output caching
$cacheKey = 'products_' . md5(serialize($params));
if (apcu_exists($cacheKey)) {
    $products = unserialize(apcu_fetch($cacheKey));
} else {
    $products = fetchAll($sql, $params);
    apcu_store($cacheKey, serialize($products), 300);
}
```

---

## Version History

### Version 1.0.0 (Current)
- Multi-user role-based system
- Complete product management
- Point of sale system
- Multi-currency support
- Backup and recovery system
- Responsive Bootstrap interface

### Planned Features
- **Mobile App**: Native mobile application
- **API Integration**: RESTful API for third-party integration
- **Advanced Reporting**: Business intelligence features
- **Multi-Store Support**: Manage multiple store locations
- **Cloud Backup**: Automated cloud backup integration

---

## Support & Maintenance

### Regular Maintenance Tasks
1. **Database Optimization**: Monthly index rebuilding
2. **Backup Verification**: Weekly backup integrity checks
3. **Log Rotation**: Monthly log file cleanup
4. **Security Updates**: Apply security patches promptly
5. **Performance Monitoring**: Regular performance analysis

### Contact Information
- **Documentation**: This file
- **Configuration**: `config/config.php`
- **Database**: `database.sql` (initial schema)
- **Logs**: `/logs/` directory

### Best Practices
- **Regular Backups**: Daily automated backups
- **Access Monitoring**: Review user activity logs
- **Security Updates**: Keep system updated
- **Performance Tuning**: Regular optimization
- **User Training**: Proper user education

---

*Last Updated: February 17, 2026*
*Version: 1.0.0*
*Documentation Version: 1.0*
