# LTS Inventory Management System

A comprehensive web-based inventory and sales management system for small retail shops, pharmacies, and mini-supermarkets.

![LTS Inventory Management System](https://img.shields.io/badge/Version-1.0.0+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

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

/reports/         # Reporting system
├── sales.php        # Sales reports with PDF export
├── inventory.php    # Inventory reports with PDF export
└── dashboard.php    # Report overview

/auth/            # Authentication
├── login.php        # User login
├── logout.php       # User logout
└── reauth.php       # Session re-authentication
```

## 🛠️ Installation

### Local Development (XAMPP/WAMP)

1. **Extract Files**
   ```
   XAMPP: C:/xampp/htdocs/LTS/
   WAMP:  C:/wamp/www/LTS/
   MAMP:  /Applications/MAMP/htdocs/LTS/
   ```

2. **Database Setup**
   - Open phpMyAdmin
   - Create database: `inventory_system`
   - Import `database.sql`

3. **Access System**
   - URL: `http://localhost/LTS/`
   - Login: `admin` / `admin123`

### External Web Hosting

1. **Upload Files** to hosting (cPanel, Plesk, DirectAdmin)
2. **Create Database** through hosting control panel
3. **Import `database.sql`**
4. **Update Database Credentials** in `config/database.php`
5. **Access**: `https://yourdomain.com/LTS/`

**Zero Configuration Required** - System automatically detects hosting environment!

## 📖 Documentation

- **[Installation Guide](INSTALL.md)** - Detailed installation instructions
- **[System Documentation](Documentation/SYSTEM_DOCUMENTATION.md)** - Technical documentation
- **[Deployment Guide](DEPLOYMENT.md)** - Production deployment notes

## 🌐 Hosting Compatibility

**Works with ALL Hosting Providers:**
- ✅ **cPanel** (Bluehost, GoDaddy, HostGator)
- ✅ **Plesk** (SiteGround, A2 Hosting)
- ✅ **DirectAdmin** (InMotion Hosting)
- ✅ **VPS/Dedicated** (DigitalOcean, Vultr, Linode)
- ✅ **Cloud Platforms** (AWS, Google Cloud, Azure)

**Technical Requirements:**
- PHP 7.4+ (most hosts support this)
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- SSL Certificate (recommended for security)

## 🔧 Technical Stack

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Framework**: Bootstrap 5.x for responsive design
- **Security**: Password hashing, prepared statements, session management

## 🛡️ Security Features

- **Role-Based Access Control**: Admin, Manager, Cashier roles
- **Password Hashing**: Secure password storage
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Input sanitization
- **Session Security**: HTTP-only, SameSite cookies
- **Production Error Handling**: No sensitive info exposed

## 📱 Mobile Support

- **Responsive Design**: Works on all screen sizes
- **Cross-Device Login**: Same account across devices
- **Touch Interface**: Mobile-optimized controls
- **Progressive Web App**: Can be installed on mobile

## 🎨 Reporting Features

- **PDF Export**: Professional branded reports
- **Multi-Currency Reports**: Separate currency totals
- **Summary Statistics**: Stock counts and values
- **Sales Analytics**: Comprehensive sales data
- **Inventory Analysis**: Stock levels and movements
- **Export Options**: CSV and PDF formats

## 💰 Multi-Currency Support

- **Unlimited Currencies**: Add USD, EUR, GBP, etc.
- **Product Pricing**: Different currencies per product
- **Report Separation**: Currency-separated totals
- **Exchange Rate Ready**: Infrastructure for conversion

## 📞 Support

### Default Credentials
- **Admin**: admin / admin123
- **Database**: inventory_system (create during installation)

### Quick Links
- **Login**: `/auth/login.php`
- **Admin Dashboard**: `/admin/dashboard.php`
- **Manager Dashboard**: `/manager/dashboard.php`
- **Cashier Dashboard**: `/cashier/dashboard.php`

### Common Issues
- **500 Error**: Check PHP version (requires 7.4+)
- **Database Error**: Verify database credentials
- **Login Issues**: Clear browser cache, check sessions
- **Mobile Access**: System is mobile-optimized

## 🔄 Updates & Maintenance

### Version 1.0.0+ Features
- Enhanced PDF reporting with system branding
- Multi-currency support in all modules
- Mobile-responsive cross-device login
- Production-ready configuration
- Dynamic URL detection for any hosting
- Advanced security features

### Regular Maintenance
- Database backups recommended
- Log file monitoring
- Security updates
- Performance optimization

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Contact

For support and inquiries:
- Check the documentation files
- Review troubleshooting guides
- Verify system requirements

---

## 🎯 Installation Complete!

Your LTS Inventory Management System is ready for use! The system will automatically:
- Detect your hosting environment
- Configure secure sessions
- Handle multi-currency operations
- Provide mobile-responsive access
- Generate professional reports
- Maintain security best practices

**Next Steps**: Login, change the default password, and start configuring your inventory system!
