<?php
/**
 * Header Template
 * Inventory Management System
 */
require_once __DIR__ . '/logo.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/settings.php'; // Include settings functions
requireAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#1e3c72">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Blue Gradient Navbar Styling -->
    <style>
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        
        .navbar-brand:hover {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        
        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: rgba(255, 255, 255, 0.8) !important;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }
        
        .dropdown-menu {
            background: linear-gradient(135deg, #2a5298 0%, #7e8ba3 100%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .dropdown-item {
            color: white !important;
            transition: all 0.3s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white !important;
        }
        
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255%2c255%2c255%2c0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }
    </style>
    
    <!-- Local Bootstrap Fallback -->
    <noscript>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/bootstrap-local.css">
        <style>
            /* Fallback icons when Bootstrap Icons CDN is blocked */
            .bi-pencil::before { content: "✏️"; }
            .bi-trash::before { content: "🗑️"; }
            .bi-plus::before { content: "+"; }
            .bi-box-arrow-right::before { content: "🚪"; }
        </style>
    </noscript>
    
    <script>
        // Check if Bootstrap CSS loaded, if not use local fallback
        function checkBootstrapCSS() {
            const testElement = document.createElement('div');
            testElement.className = 'container';
            document.body.appendChild(testElement);
            const styles = window.getComputedStyle(testElement);
            const hasBootstrap = styles.maxWidth !== 'none';
            document.body.removeChild(testElement);
            
            if (!hasBootstrap) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '<?php echo BASE_URL; ?>/assets/css/bootstrap-local.css';
                document.head.appendChild(link);
                
                // Add fallback icons
                const iconStyle = document.createElement('style');
                iconStyle.textContent = `
                    .bi-pencil::before { content: "✏️"; margin-right: 4px; }
                    .bi-trash::before { content: "🗑️"; margin-right: 4px; }
                    .bi-plus::before { content: "+"; margin-right: 4px; }
                    .bi-box-arrow-right::before { content: "🚪"; margin-right: 4px; }
                `;
                document.head.appendChild(iconStyle);
            }
        }
        
        // Run check after page loads
        window.addEventListener('load', checkBootstrapCSS);
    </script>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/assets/favicon.ico">
</head>
<body<?php echo isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>">
                <?php if (hasLogo()): ?>
                    <img src="<?php echo getLogoUrl(); ?>" alt="<?php echo APP_NAME; ?>" 
                         style="height: 35px; margin-right: 10px; border-radius: 4px;">
                <?php endif; ?>
                <strong><?php echo getSetting('store_name', APP_NAME); ?></strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (canAccessModule('admin')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/users.php">Users</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/roles.php">Roles</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/suppliers.php">Suppliers</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/categories.php">Categories</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/settings.php">Settings</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (canAccessModule('manager')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="managerDropdown" role="button" data-bs-toggle="dropdown">
                                Manager
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/manager/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/manager/products.php">Products</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/manager/purchases.php">Purchases</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/manager/stock-alerts.php">Stock Alerts</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (canAccessModule('cashier') && !str_contains($_SERVER['REQUEST_URI'], '/manager/')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="cashierDropdown" role="button" data-bs-toggle="dropdown">
                                Cashier
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/cashier/dashboard.php">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/cashier/pos.php">Point of Sale</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/cashier/products.php">Products</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> 
                            <?php echo getCurrentUser()['full_name']; ?>
                            <small class="text-muted">(<?php echo getRoleName(getCurrentUser()['role_id']); ?>)</small>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/auth/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/auth/change-password.php">Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Activity Tracking Script -->
    <?php if (isLoggedIn()): ?>
    <script>
        // Track user activity for security
        let activityTimer;
        let lastActivityTime = Date.now();
        
        function updateActivity() {
            lastActivityTime = Date.now();
            
            // Send activity update to server (optional - can be implemented later)
            fetch('<?php echo BASE_URL; ?>/auth/update_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'activity=update'
            }).catch(() => {
                // Silently fail - user experience is priority
            });
        }
        
        function resetActivityTimer() {
            clearTimeout(activityTimer);
            updateActivity();
            
            // Set timer to update activity every 5 minutes
            activityTimer = setTimeout(resetActivityTimer, 5 * 60 * 1000);
        }
        
        // Track various user interactions
        const activityEvents = [
            'mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'
        ];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, function() {
                // Only update if at least 30 seconds have passed
                if (Date.now() - lastActivityTime > 30000) {
                    resetActivityTimer();
                }
            });
        });
        
        // Initial activity setup
        resetActivityTimer();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                // Page became visible - update activity
                resetActivityTimer();
            }
        });
        
        // Handle page unload
        window.addEventListener('beforeunload', function() {
            // Update activity before page unload
            navigator.sendBeacon('<?php echo BASE_URL; ?>/auth/update_activity.php', 'activity=unload');
        });
        
        // Detect page refresh vs new navigation
        if (performance.navigation.type === 1) {
            // Page was refreshed - update activity
            updateActivity();
        }
        
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
    </script>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container-fluid mt-4">
        <?php if (isset($pageTitle)): ?>
            <div class="row mb-4">
                <div class="col">
                    <h1><?php echo $pageTitle; ?></h1>
                </div>
            </div>
        <?php endif; ?>
