<?php
/**
 * Login Page
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/settings.php'; // Include settings functions
require_once __DIR__ . '/../includes/logo.php';

// Don't redirect if already logged in - allow access to login form
// User can choose to logout or continue to dashboard

$error = '';

// Check if user is already logged in
$alreadyLoggedIn = false;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
    $alreadyLoggedIn = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } elseif (login($username, $password)) {
        // Debug: Log successful login
        error_log("Login successful for user: $username, BASE_URL: " . BASE_URL);
        
        // Redirect to intended page or dashboard
        $redirectUrl = $_SESSION['redirect_url'] ?? BASE_URL . '/' . getUserDashboard();
        unset($_SESSION['redirect_url']);
        
        error_log("Redirecting to: $redirectUrl");
        redirect($redirectUrl);
    } else {
        $error = 'Invalid username or password';
        error_log("Login failed for user: $username");
    }
}
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
    <title>Login - <?php echo getSetting('store_name', APP_NAME); ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Simple Background Color -->
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e8ba3 100%);
        }
        
        /* Light Blue Form Styling */
        .card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(173, 216, 230, 0.3);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(173, 216, 230, 0.5);
            color: #333;
            border-radius: 8px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.95);
            border-color: #87CEEB;
            box-shadow: 0 0 0 2px rgba(135, 206, 235, 0.25);
        }
        
        .input-group-text {
            background: rgba(135, 206, 235, 0.2);
            border: 1px solid rgba(173, 216, 230, 0.5);
            color: #4682B4;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #87CEEB 0%, #4682B4 100%);
            border: none;
            border-radius: 8px;
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4682B4 0%, #87CEEB 100%);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
            border: 1px solid rgba(23, 162, 184, 0.2);
        }
        
        .text-muted {
            color: #6c757d !important;
        }
        
        .text-primary {
            color: #4682B4 !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <?php 
                            $logo = getSetting('logo_path', '');
                            if (!empty($logo) && file_exists(__DIR__ . '/../' . $logo)): 
                            ?>
                                <img src="<?php echo BASE_URL . '/' . $logo; ?>" 
                                     alt="<?php echo getSetting('store_name', APP_NAME); ?>" 
                                     class="mb-3" 
                                     style="max-width: 200px; max-height: 80px; object-fit: contain;">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="font-size: 3rem; color: var(--primary-color);"></i>
                            <?php endif; ?>
                            <h2 class="text-primary"><?php echo getSetting('store_name', APP_NAME); ?></h2>
                            <p class="text-muted"><?php echo getSetting('store_description', 'Inventory Management System'); ?></p>
                        </div>
                        
                        <?php if ($alreadyLoggedIn): ?>
                            <div class="alert alert-info" role="alert">
                                <strong>Already Logged In</strong><br>
                                You are currently logged in as <strong><?php echo htmlspecialchars($currentUser['full_name'] ?? 'Unknown'); ?></strong> 
                                (<?php echo getRoleName($currentUser['role_id'] ?? 0); ?>)
                            </div>
                            
                            <!-- Password Verification Section -->
                            <div class="card border-info mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-shield-check me-2"></i>
                                        Security Verification
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">
                                        For your security, please verify your password to continue or access different functions.
                                    </p>
                                    
                                    <?php if (isset($_SESSION['verify_error'])): ?>
                                        <div class="alert alert-danger" role="alert">
                                            <i class="bi bi-x-circle me-2"></i>
                                            <?php echo htmlspecialchars($_SESSION['verify_error']); ?>
                                        </div>
                                        <?php unset($_SESSION['verify_error']); ?>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="verify_password.php">
                                        <div class="mb-3">
                                            <label for="verify_username" class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-person"></i>
                                                </span>
                                                <input type="text" class="form-control" id="verify_username" 
                                                       value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>" 
                                                       readonly>
                                            </div>
                                            <small class="text-muted">Your current username</small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="verify_password" class="form-label">Verify Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                                <input type="password" class="form-control" id="verify_password" 
                                                       name="verify_password" required autofocus>
                                                <button class="btn btn-outline-secondary" type="button" id="toggleVerifyPassword">
                                                    <i class="bi bi-eye" id="verifyEyeIcon"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted">Enter your password to verify identity</small>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-info">
                                                <i class="bi bi-shield-check me-2"></i>
                                                Verify & Continue
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Logout
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$alreadyLoggedIn): ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <div class="text-center">
                            
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo getSetting('store_name', APP_NAME); ?> - 
                        <?php echo getSetting('store_description', 'Inventory Management System'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility for verification form
        document.getElementById('toggleVerifyPassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('verify_password');
            const eyeIcon = document.getElementById('verifyEyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });
        
        // Focus on verification password field
        document.getElementById('verify_password')?.focus();
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
