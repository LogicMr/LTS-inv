<?php
/**
 * Re-authentication Page
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php');
}

// Check if re-authentication is actually required
if (!($_SESSION['reauth_required'] ?? false)) {
    redirect(BASE_URL . '/auth/login.php');
}

$pageTitle = 'Security Check - Re-authentication';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reauth-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        .security-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .alert-security {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .btn-reauth {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 500;
        }
        .btn-reauth:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .countdown {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="reauth-container">
        <div class="text-center">
            <i class="bi bi-shield-check security-icon"></i>
            <h2 class="mb-3">Security Check</h2>
            
            <div class="alert-security">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>For your security</strong>, please verify your identity to continue.
            </div>
            
            <?php if (isset($_SESSION['reauth_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-x-circle me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['reauth_error']); ?>
                </div>
                <?php unset($_SESSION['reauth_error']); ?>
            <?php endif; ?>
            
            <form method="POST" action="process_reauth.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" id="username" 
                               value="<?php echo htmlspecialchars(getCurrentUser()['username']); ?>" 
                               readonly>
                    </div>
                    <small class="text-muted">Your username for verification</small>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" 
                               name="password" required autofocus>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <small class="text-muted">Enter your password to continue</small>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-reauth">
                        <i class="bi bi-shield-check me-2"></i>
                        Verify & Continue
                    </button>
                    
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" 
                       class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Logout
                    </a>
                </div>
                
                <div class="countdown">
                    <i class="bi bi-clock me-1"></i>
                    Session will expire in <span id="countdown">15:00</span>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'bi bi-eye';
            }
        });
        
        // Countdown timer
        let timeLeft = 15 * 60; // 15 minutes in seconds
        
        function updateCountdown() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            document.getElementById('countdown').textContent = display;
            
            if (timeLeft <= 0) {
                // Redirect to login when time expires
                window.location.href = '<?php echo BASE_URL; ?>/auth/logout.php';
            }
            
            timeLeft--;
        }
        
        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call
        
        // Focus on password field
        document.getElementById('password').focus();
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html>
