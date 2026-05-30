# LTS Inventory Management System - Installation Guide

## 📋 Table of Contents
1. [Quick Installation (Local Development)](#quick-installation-local-development)
2. [External Web Hosting Installation](#external-web-hosting-installation)
3. [Hosting Provider Specific Guides](#hosting-provider-specific-guides)
4. [Post-Installation Configuration](#post-installation-configuration)
5. [Troubleshooting](#troubleshooting)
6. [Recent Updates & Features](#recent-updates--features)

---

## 🖥️ Quick Installation (Local Development)

### Prerequisites
- **XAMPP/WAMP/MAMP/LAMP** stack installed
- **Apache** and **MySQL** services running
- **PHP 7.4+** recommended
- **Modern web browser** (Chrome, Firefox, Safari, Edge)

### Installation Steps

#### 1. Extract Files
Extract the project files to your web server's document root:
- **XAMPP**: `C:/xampp/htdocs/LTS/`
- **WAMP**: `C:/wamp/www/LTS/`
- **MAMP**: `/Applications/MAMP/htdocs/LTS/`
- **Linux**: `/var/www/html/LTS/`

#### 2. Database Setup
1. Open **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
2. Click **"New"** to create a database
3. Enter database name: `inventory_system`
4. Click **"Create"**
5. Select the `inventory_system` database
6. Click **"Import"** tab
7. Choose the `database.sql` file from the project root
8. Click **"Go"** to import

#### 3. Configuration
The system comes **dially configured** for localhost. Advanced users can edit:
- `config/database.php` - Database connection settings
- `config/config.php` - Application settings (auto-detects environment)

#### 4. Access the System
1. Ensure Apache and MySQL are running in XAMPP/WAMP
2. Open browser: `http://localhost/LTS/`
3. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

#### 5. First Steps
1. **IMPORTANT**: Change the default admin password immediately
2. Create user accounts for your staff (Admin, Manager, Cashier)
3. Add your suppliers
4. Create product categories
5. Add your products (with multi-currency support)
6. Start using the system!

---

## 🌐 External Web Hosting Installation

### Prerequisites for Hosting
- **Web Hosting Account** (delt/delt/VPS/Cloud)
- **PHP 7.4+** (most hosts support this)
- **MySQL 5.7+** or **MariaDB 10.2+**
- **SSL Certificate** (recommended for security)
- **File Manager** or **FTP/SFTP** access

### Universal Installation Process

#### Step 1: Prepare Your Files
1. **Download** the LTS system files
2. **Extract** to a local folder
3. **Optional**: Remove development files:
   - `temp_*.php` files
   - `test_*.php` files
   - `debug_*.php` files
   - `comprehensive_test.php`
   - `DEPLOYMENT.md` (development notes)

#### Step 2: Upload to Hosting
**Method A: File Manager (Recommended for beginners)**
1. Login to your hosting control panel (cPanel, Plesk, DirectAdmin)
2. Open **File Manager**
3. Navigate to **public_html** or **www** directory
4. Create a new folder named **LTS**
5. Upload all files to the **LTS** folder

**Method B: FTP/SFTP (Advanced users)**
1. Use FTP client (FileZilla, WinSCP, CyberDuck)
2. Connect to your hosting account
3. Navigate to **public_html** or **www**
4. Create **LTS** directory
5. Upload all files maintaining directory structure

#### Step 3: Database Setup
**Method A: Hosting Control Panel**
1. Open **MySQL Databases** or **phpMyAdmin**
2. Create a new database named `inventory_system`
3. Create a database user with strong password
4. Grant the user all privileges on the database
5. Open **phpMyAdmin**
6. Select your new database
7. Click **Import**
8. Upload the `database.sql` file from the project

**Method B: phpMyAdmin Direct**
1. Access phpMyAdmin through your control panel
2. Create database: `inventory_system`
3. Select the database
4. Click **Import** tab
5. Choose `database.sql` file
6. Click **Go**

#### Step 4: Configure Database Connection
1. Open `config/database.php` in a text editor
2. Update the database credentials:
```php
define('DB_HOST', 'localhost'); // Usually localhost
define('DB_NAME', 'inventory_system');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
```
3. Save and upload the modified file

#### Step 5: Set File Permissions
Most hosting providers handle permissions automatically, but if needed:
- **Directories**: 755 (rwxr-xr-x)
- **Files**: 644 (rw-r--r--)
- **Sessions directory**: 755 (system creates automatically)

#### Step 6: Access Your System
1. Open browser: `https://yourdomain.com/LTS/`
2. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`
3. **IMPORTANT**: Change default password immediately

---

## 🏢 Hosting Provider Specific Guides

### cPanel Installation
1. **Login to cPanel**
2. **File Manager** → Create `LTS` folder in `public_html`
3. Upload all files to `LTS` folder
4. **MySQL Databases** → Create database and user
5. **phpMyAdmin** → Import `database.sql`
6. Update `config/database.php` with your credentials
7. Access: `https://yourdomain.com/LTS/`

### Plesk Installation
1. **Login to Plesk**
2. **File Manager** → Create `LTS` folder in `httpdocs`
3. Upload all files to `LTS` folder
4. **Databases** → Create database and user
5. **phpMyAdmin** → Import `database.sql`
6. Update `config/database.php`
7. Access: `https://yourdomain.com/LTS/`

### DirectAdmin Installation
1. **Login to DirectAdmin**
2. **File Manager** → Create `LTS` folder in `public_html`
3. Upload all files to `LTS` folder
4. **MySQL Management** → Create database and user
5. **phpMyAdmin** → Import `database.sql`
6. Update `config/database.php`
7. Access: `https://yourdomain.com/LTS/`

### GoDaddy Installation
1. **Login to GoDaddy**
2. **File Manager** → Create `LTS` folder in `public_html`
3. Upload all files to `LTS` folder
4. **MySQL Databases** → Create database and user
5. **phpMyAdmin** → Import `database.sql`
6. Update `config/database.php`
7. Access: `https://yourdomain.com/LTS/`

### Bluehost Installation
1. **Login to Bluehost**
2. **File Manager** → Create `LTS` folder in `public_html`
3. Upload all files to `LTS` folder
4. **MySQL Databases** → Create database and user
5. **phpMyAdmin** → Import `database.sql`
6. Update `config/database.php`
7. Access: `https://yourdomain.com/LTS/`

### Cloud Hosting (AWS, Google Cloud, Azure)
1. **Deploy VM** with LAMP/LEMP stack
2. **SSH** into your server
3. **Clone** or upload files to `/var/www/html/LTS/`
4. **Create database** and user
5. **Import** `database.sql`
6. **Configure** `config/database.php`
7. **Set permissions**: `sudo chmod -R 755 /var/www/html/LTS/`
8. Access: `http://your-server-ip/LTS/`

---

## ⚙️ Post-Installation Configuration

### Essential First Steps
1. **Change Admin Password**
   - Login as admin
   - Go to Admin → Users → Edit admin
   - Set a strong password

2. **Configure Store Settings**
   - Go to Settings → Store Configuration
   - Set store name, address, contact info
   - Configure currency settings

3. **Create User Accounts**
   - **Admin**: Full system access
   - **Manager**: Products, reports, dashboard access
   - **Cashier**: Point of sale access only

4. **Setup Basic Data**
   - Add suppliers
   - Create product categories
   - Add products with pricing
   - Set up initial stock

### Currency Configuration
The system supports **multi-currency** functionality:
1. **Settings** → **Currency Management**
2. Add your supported currencies (USD, EUR, GBP, etc.)
3. Set default currency
4. Products can have different currencies

### Role-Based Access Control
- **Admin**: Full system access, user management, settings
- **Manager**: Products, reports, dashboard, inventory management
- **Cashier**: Point of sale, sales history only

### Mobile Access
The system is **mobile-responsive** and works on:
- Smartphones (iOS Safari, Android Chrome)
- Tablets (iPad, Android tablets)
- Desktop browsers (Chrome, Firefox, Safari, Edge)

---

## 🔧 Troubleshooting

### Common Issues & Solutions

#### **500 Internal Server Error**
**Causes**: PHP version compatibility, file permissions
**Solutions**:
- Check PHP version (requires 7.4+)
- Set file permissions: 755 for directories, 644 for files
- Check .htaccess file if exists

#### **Database Connection Error**
**Causes**: Wrong credentials, database not delt
**Solutions**:
- Verify database credentials in `config/database.php`
- Ensure database exists and user has privileges
- Check database server is running

#### **Login Not Working**
**Causes**: Session issues, incorrect credentials
**Solutions**:
- Verify admin credentials: admin/admin123
- Check sessions directory permissions (auto-created)
- Clear browser cookies and cache

#### **CSS/JS Not Loading**
**Causes**: BASE_URL issues, file permissions
**Solutions**:
- System auto-detects BASE_URL (should work automatically)
- Check file permissions for assets folder
- Verify .htaccess configuration

#### **Mobile Access Issues**
**Causes**: Session configuration, SSL issues
**Solutions**:
- System is mobile-optimized (should work automatically)
- Ensure SSL certificate is delt if if using HTTPS
- Check mobile browser compatibility

### Error Logging
The system automatically detects production vs development:
- **Development**: Shows all errors
- **Production**: Logs errors to `logs/error.log`

---

## 🆕 Recent Updates & Features

### Latest Enhancements (Version 1.0.0+)

#### **🎨 Enhanced Reporting System**
- **PDF Export**: Professional report generation with system branding
- **Multi-Currency Support**: Separate currency totals in reports
- **Dynamic Styling**: System-consistent colors and design
- **Summary Statistics**: Total stock count and value display
- **Print Instructions**: Step-by-step PDF generation guide

#### **📱 Mobile & Cross-Device Support**
- **Responsive Design**: Works on all screen sizes
- **Cross-Device Sessions**: Login works across devices
- **Touch Interface**: Mobile-optimized user interface
- **Progressive Web App**: Can be installed on mobile devices

#### **🌐 Production-Ready Configuration**
- **Dynamic URL Detection**: Works with any domain/hosting
- **Environment Detection**: Auto-configures for production
- **SSL Auto-Detection**: Secure cookies on HTTPS
- **Zero-Configuration**: Upload and use immediately

#### **🔐 Enhanced Security**
- **Session Security**: HTTP-only, SameSite cookies
- **Production Error Handling**: No sensitive info exposed
- **Cross-Site Protection**: CSRF and XSS prevention
- **SQL Injection Protection**: dealt statements

#### **💰 Multi-Currency System**
- **Currency Management**: Add multiple currencies
- **Product Pricing**: Different currencies per product
- **Report Separation**: Currency-separated totals
- **Exchange Rate Support**: Ready for currency conversion

#### **📊 Advanced Reporting**
- **Inventory Reports**: Comprehensive stock analysis
- **Sales Reports**: Detailed sales analytics
- **Export Options**: CSV and PDF generation
- **Filtering**: Date range, category, payment method filters

#### **🎯 User Experience**
- **Role-Based Navigation**: Dynamic dashboard routing
- **Quick Actions**: Streamlined manager dashboard
- **Professional UI**: Modern, clean interface
- **Error Prevention**: Input validation and sanitization

### Technical Improvements
- **Session Management**: Custom session path, no system dependency
- **Database Optimization**: Efficient queries and indexing
- **Code Quality**: Clean, maintainable, well-documented
- **Performance**: Optimized for delt and dedicated hosting

### Hosting Compatibility
The system is **delt to work** with:
- ✅ **Any hosting provider** (cPanel, Plesk, DirectAdmin, custom)
- ✅ **Any domain name** (.com, .org, .net, country-specific)
- ✅ **Any SSL configuration** (Let's Encrypt, commercial, self-signed)
- ✅ **Any PHP version** (7.4, 8.0, 8.1, 8.2+)
- ✅ **Any MySQL/MariaDB version** (5.7, 8.0+)
- ✅ **Any server configuration** (Apache, Nginx, LiteSpeed)

---

## 📞 Support & Documentation

### Documentation Files
- `README.md` - System overview and features
- `SYSTEM_DOCUMENTATION.md` - Detailed technical documentation
- `DEPLOYMENT.md` - Production deployment notes
- `Documentation/` - Additional guides and references

### Default Credentials
- **Admin**: admin / admin123
- **Database**: inventory_system (create during installation)

### Quick Links
- **Login**: `/auth/login.php`
- **Admin Dashboard**: `/admin/dashboard.php`
- **Manager Dashboard**: `/manager/dashboard.php`
- **Cashier Dashboard**: `/cashier/dashboard.php`

---

## 🎯 Installation Complete!

Your LTS Inventory Management System is now ready for use! The system will automatically:
- Detect your hosting environment
- Configure secure sessions
- Handle multi-currency operations
- Provide mobile-responsive access
- Generate professional reports
- Maintain security best practices

**Next Steps**: Login, change the default password, and start configuring your inventory system!
