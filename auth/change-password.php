<?php
/**
 * Change Password
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();

$pageTitle = 'Change Password';

// Get current user
$currentUser = getCurrentUser();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Get user with current password
    $sql = "SELECT password FROM users WHERE id = ?";
    $user = fetchRow($sql, [$currentUser['id']]);
    
    $errors = [];
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    // Validate new password
    if (strlen($new_password) < 8) {
        $errors[] = "New password must be at least 8 characters long";
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match";
    }
    
    if (empty($errors)) {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        executeNonQuery($update_sql, [$hashed_password, $currentUser['id']]);
        
        $_SESSION['flash_message'] = 'Password changed successfully';
        $_SESSION['flash_type'] = 'success';
        
        header('Location: ' . BASE_URL . '/auth/profile.php');
        exit;
    } else {
        $_SESSION['flash_message'] = implode('<br>', $errors);
        $_SESSION['flash_type'] = 'danger';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1>Change Password</h1>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                        </div>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6>Password Requirements:</h6>
                        <ul class="mb-0">
                            <li>At least 8 characters long</li>
                            <li>Should include uppercase and lowercase letters</li>
                            <li>Should include numbers</li>
                            <li>Avoid common passwords</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                        <a href="<?php echo BASE_URL; ?>/auth/profile.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('confirm_password').addEventListener('input', function() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = this.value;
    
    if (newPassword !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
