<?php
/**
 * User Profile
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$pageTitle = 'My Profile';

// Get current user
$currentUser = getCurrentUser();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = cleanInput($_POST['full_name']);
    $email = cleanInput($_POST['email']);
    $phone = cleanInput($_POST['phone']);
    
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
    executeNonQuery($sql, [$full_name, $email, $phone, $currentUser['id']]);
    
    // Update session
    $_SESSION['user']['full_name'] = $full_name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['user']['phone'] = $phone;
    
    $_SESSION['flash_message'] = 'Profile updated successfully';
    $_SESSION['flash_type'] = 'success';
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1>My Profile</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?php echo $currentUser['username']; ?>" readonly>
                        <small class="form-text text-muted">Username cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $currentUser['full_name']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $currentUser['email'] ?: ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo $currentUser['phone'] ?: ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input type="text" class="form-control" value="<?php echo getRoleName($currentUser['role_id']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <input type="text" class="form-control" value="<?php echo formatDate($currentUser['created_at']); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Last Login</label>
                        <input type="text" class="form-control" value="<?php echo $currentUser['last_login'] ? formatDateTime($currentUser['last_login']) : 'Never'; ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="<?php echo BASE_URL; ?>/auth/change-password.php" class="btn btn-outline-secondary">Change Password</a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>/auth/change-password.php" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> Change Password
                    </a>
                    <a href="<?php echo BASE_URL; ?>/<?php echo getUserDashboard(); ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">System Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Application:</strong> <?php echo getSetting('store_name', APP_NAME); ?></p>
                <p><strong>Version:</strong> <?php echo APP_VERSION; ?></p>
                <p><strong>User Role:</strong> <?php echo getRoleName($currentUser['role_id']); ?></p>
                <p><strong>Session Timeout:</strong> <?php echo SESSION_TIMEOUT / 60; ?> minutes</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
