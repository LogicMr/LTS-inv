<?php
/**
 * Unauthorized Access Page
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

$pageTitle = 'Access Denied';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="bi bi-shield-exclamation text-danger" style="font-size: 4rem;"></i>
                </div>
                
                <h2 class="text-danger mb-3">Access Denied</h2>
                
                <p class="text-muted mb-4">
                    You don't have permission to access this page. Please contact your administrator if you believe this is an error.
                </p>
                
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">
                        <i class="bi bi-house"></i> Go to Dashboard
                    </a>
                    <a href="javascript:history.back()" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Go Back
                    </a>
                </div>
                
                <hr>
                
                <div class="text-start">
                    <h5>Your Current Role:</h5>
                    <p class="mb-1">
                        <strong><?php echo getCurrentUser()['full_name']; ?></strong><br>
                        <span class="badge bg-info"><?php echo getRoleName(getCurrentUser()['role_id']); ?></span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
