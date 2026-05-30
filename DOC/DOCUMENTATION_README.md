# LTS Inventory Management System

A comprehensive web-based inventory and sales management system for small retail shops, pharmacies, and mini-supermarkets.

## 🚀 Quick Start

### Default Credentials
- **Username**: `admin`
- **Password**: `admin123`
- **URL**: `http://localhost/LTS/`

### 🆕 Latest Features (Version 1.0.0+)

#### **🎨 Enhanced Reporting System**
- **Professional PDF Export**: Generate branded reports with system name and logo
- **Multi-Currency Reports**: Separate totals by currency type ($X + €Y + £Z)
- **Summary Statistics**: Total stock count, value, and product counts
- **Print Instructions**: Step-by-step PDF generation guide
- **Dynamic Styling**: System-consistent colors and modern design

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
- ✅ **Multi-User System**: Admin, Manager, Cashier roles with role-based access
- ✅ **Product Management**: Complete inventory with barcode support and multi-currency
- ✅ **Point of Sale**: Modern POS with multi-currency support and touch interface
- ✅ **Stock Management**: Real-time tracking with low stock and expiry alerts
- ✅ **Purchase Management**: Supplier and order tracking with multi-currency
- ✅ **Advanced Reports**: PDF and CSV export with multi-currency support
- ✅ **Backup System**: Automated backup and recovery
- ✅ **Mobile Responsive**: Works on all devices with cross-device login
- ✅ **Production Ready**: Works on any hosting provider with zero configuration

## 📋 User Roles

| Role | Access | Key Features |
|-------|---------|---------------|
| **Admin** | Full system access | User management, settings, backup |
| **Manager** | Inventory & reports | Products, purchases, stock alerts |
| **Cashier** | Sales only | Point of sale, product viewing |

## 🗂️ Module Access

```
/admin/          # Administrator dashboard
├── dashboard.php    # System overview & backup management
├── users.php        # User management
├── suppliers.php    # Supplier management
├── categories.php   # Category management
└── settings.php     # System settings

/manager/         # Manager dashboard
├── dashboard.php    # Manager overview
├── products.php     # Product management
├── purchases.php    # Purchase orders
└── stock-alerts.php # Stock alerts

/cashier/         # Cashier interface
├── dashboard.php    # Sales dashboard
├── pos.php          # Point of sale
└── products.php     # Product viewing

/reports/          # Reports
├── sales.php        # Sales reports
└── (other reports) # Additional reports
```

## 💾 Backup System

### Backup Types
- **Quick Backup**: Instant server backup (`/backups/quick_backup.sql`)
- **Export Backup**: Download complete database backup
- **Import Backup**: Restore from SQL file
- **Backup History**: View recent backups

### Access Backup
1. Go to **Admin Dashboard**
2. Find **"Database Backup Management"** section
3. Choose backup type and execute

## 🔧 Configuration

### Database Settings (`config/config.php`)
```php
define('BASE_URL', 'http://localhost/LTS');
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_system');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### Currency Support
- Multi-currency transactions
- Automatic currency detection
- Proper symbol display
- Exchange rate tracking

## 🛡️ Security Features

- **Password Hashing**: Bcrypt encryption
- **Session Management**: Secure sessions
- **SQL Injection Protection**: Prepared statements
- **Role-Based Access**: Module-level permissions
- **Input Validation**: XSS protection

## 📊 Reporting

### Sales Reports
- **Daily Reports**: Sales by date
- **Monthly Reports**: Monthly summaries
- **Detailed Reports**: Transaction details
- **Multi-Currency**: Reports in transaction currencies
- **CSV Export**: Download for analysis

### Key Metrics
- Total sales by currency
- Profit margins
- Product performance
- Payment method breakdown
- Cashier performance

## 🎯 Key Modules

### Product Management
- **CRUD Operations**: Create, Read, Update, Delete
- **Multi-Currency**: Price in different currencies
- **Stock Tracking**: Real-time inventory
- **Barcode Support**: Scan and search
- **Expiry Tracking**: Monitor expiration dates

### Point of Sale
- **Product Search**: Name, barcode, category
- **Cart Management**: Add, edit, remove items
- **Price Override**: Special pricing situations
- **Multi-Payment**: Cash, card, mobile money
- **Receipt Printing**: Automatic receipt generation
- **Real-time Stock**: Instant inventory updates

## 🔄 Data Flow

```
Product Entry → Stock Management → Point of Sale → Reports
     ↓                ↓                ↓           ↓
   Database ←─── Inventory Updates ←─── Sales Data ←─── Analytics
```

## 📱 Mobile Responsive

- **Bootstrap 5**: Mobile-first design
- **Touch-Friendly**: Large buttons and controls
- **Offline Capability**: Basic functionality without internet
- **Cross-Browser**: Chrome, Firefox, Safari, Edge

## 🚨 Troubleshooting

### Common Issues
1. **Login Problems**: Check database connection
2. **Permission Errors**: Verify user roles
3. **Backup Issues**: Check directory permissions
4. **Performance**: Optimize database indexes

### Debug Mode
Enable in `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 📞 Support

### Documentation
- **Full Documentation**: `SYSTEM_DOCUMENTATION.md`
- **Database Schema**: `database.sql`
- **API Examples**: In documentation

### Quick Help
- **Reset Admin**: Contact system administrator
- **Database Issues**: Check MySQL/MariaDB service
- **Performance**: Clear browser cache, restart server

---

## 🎉 Installation

1. **Requirements**: PHP 8.0+, MySQL 5.7+, Apache/Nginx
2. **Setup**: Extract files, configure database
3. **Access**: Navigate to `http://localhost/LTS/`
4. **Login**: Use `admin/admin123`

---

*Version: 1.0.0*  
*Last Updated: February 17, 2026*  
*Documentation: Complete system documentation available*

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Apache web server (XAMPP/WAMP/LAMP stack recommended)
- Modern web browser (Chrome, Firefox, Safari, Edge)

## Installation

### Step 1: Download and Setup

1. Download the project files to your web server directory (e.g., `htdocs/LTS/`)
2. Ensure all files are properly placed in the directory structure

### Step 2: Database Setup

1. Open phpMyAdmin or your MySQL client
2. Create a new database named `inventory_system`
3. Import the `database.sql` file located in the project root
4. Verify all tables and views are created successfully

### Step 3: Configuration

1. Navigate to the `config/` directory
2. Open `database.php` and update the database connection settings if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'inventory_system');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
3. Review `config.php` for additional settings (session timeout, pagination, etc.)

### Step 4: Access the System

1. Start your Apache server
2. Open your browser and navigate to: `http://localhost/LTS/`
3. Login with default credentials:
   - Username: `admin`
   - Password: `admin123`

### Step 5: Initial Setup

1. Change the default admin password immediately
2. Create user accounts for your staff
3. Add your suppliers and product categories
4. Start adding products to your inventory

## Directory Structure

```
LTS/
├── admin/                  # Admin-specific pages
├── assets/                 # Static assets
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Images and icons
├── auth/                   # Authentication pages
├── cashier/                # Cashier-specific pages
├── config/                 # Configuration files
├── includes/               # Common includes (header, footer, functions)
├── manager/                # Manager-specific pages
├── reports/                # Reporting pages
├── database.sql            # Database schema and initial data
├── index.php              # Entry point and redirect
└── README.md              # This file
```

## User Roles and Permissions

### Admin
- Full system access
- User management (create, edit, delete users)
- Role management
- System settings
- View all reports
- Access to all modules

### Manager / Pharmacist
- Product management (CRUD)
- Purchase management
- Stock alerts management
- Sales reports
- Inventory reports
- Cannot manage users or system settings

### Cashier
- Point of Sale (POS) access
- Product viewing (read-only)
- Basic sales reports
- Cannot modify inventory or access admin functions

## Key Modules

### Product Management
- Add, edit, delete products
- Track barcode, cost price, selling price
- Stock level monitoring
- Expiry date tracking
- Batch number management
- Category organization

### Purchase Management
- Record stock-in purchases
- Update inventory automatically
- Track purchase items with cost
- Supplier management
- Batch and expiry tracking

### Point of Sale (POS)
- Fast and intuitive interface
- Real-time stock checking
- Cart management
- Multiple payment methods
- Discount support
- Receipt printing capability

### Stock Alerts
- Low stock notifications
- Out-of-stock tracking
- Expiry date alerts
- Near-expiry warnings
- Export alerts to CSV

### Reporting
- Sales reports (daily, monthly, summary)
- Purchase reports
- Inventory valuation
- Stock movement reports
- Expiry reports
- Export to CSV functionality

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements for SQL queries
- Session-based authentication
- Role-based access control
- Input validation and sanitization
- SQL injection prevention
- XSS protection

## Browser Compatibility

- Google Chrome 80+
- Mozilla Firefox 75+
- Safari 13+
- Microsoft Edge 80+

## Support and Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check MySQL server is running
   - Verify database credentials in `config/database.php`
   - Ensure database `inventory_system` exists

2. **Blank Pages**
   - Check PHP error logs
   - Ensure all files have correct permissions
   - Verify `.htaccess` if present

3. **Login Issues**
   - Clear browser cookies and cache
   - Check session settings in `config.php`
   - Verify user exists in database

### Error Reporting

For development, error reporting is enabled in `config.php`. For production, set:

```php
error_reporting(0);
ini_set('display_errors', 0);
```

## Customization

### Adding New Fields
1. Update database schema
2. Modify relevant PHP files
3. Update forms and displays

### Changing Theme
1. Edit `assets/css/style.css`
2. Modify color schemes and layouts
3. Test responsive behavior

### Adding Reports
1. Create new PHP file in `reports/` directory
2. Follow existing report structure
3. Add navigation link in header

## Performance Optimization

- Use database indexes for frequently queried columns
- Implement caching for reports
- Optimize images and assets
- Consider database partitioning for large datasets

## Backup and Recovery

### Database Backup
```sql
mysqldump -u root -p inventory_system > backup.sql
```

### File Backup
Regular backup of:
- Database dumps
- Configuration files
- Uploaded assets (if any)

## Version History

- **v1.0.0**: Initial release with core functionality
  - User authentication and roles
  - Product management
  - Purchase and sales modules
  - Basic reporting
  - Stock alerts

## License

This project is proprietary software. Usage rights are granted to the purchasing entity only.

## Contact

For support and inquiries, please contact your system administrator or the development team.

---

**Important Notes:**
- Change default passwords immediately after installation
- Regular database backups are recommended
- Keep the system updated with security patches
- Train staff on proper usage procedures
