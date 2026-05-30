<?php
/**
 * Logo Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'Logo Management';

// Handle logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'upload_logo') {
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileTmpName = $file['tmp_name'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validate file
            $errors = [];
            
            if (!in_array($fileType, LOGO_ALLOWED_TYPES)) {
                $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', LOGO_ALLOWED_TYPES);
            }
            
            if ($fileSize > LOGO_MAX_SIZE) {
                $errors[] = 'File too large. Maximum size: ' . (LOGO_MAX_SIZE / 1024 / 1024) . 'MB';
            }
            
            if (empty($errors)) {
                // Create upload directory if it doesn't exist
                $uploadDir = __DIR__ . '/../assets/uploads/logo';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Remove existing logo files
                $existingFiles = glob($uploadDir . '/*');
                foreach ($existingFiles as $existingFile) {
                    if (is_file($existingFile)) {
                        unlink($existingFile);
                    }
                }
                
                // Generate unique filename
                $newFileName = 'logo.' . $fileType;
                $uploadPath = $uploadDir . '/' . $newFileName;
                
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    // Save logo path to settings (you could save this to database)
                    $_SESSION['flash_message'] = 'Logo uploaded successfully!';
                    $_SESSION['flash_type'] = 'success';
                    logActivity('Logo Upload', 'New logo uploaded: ' . $newFileName);
                } else {
                    $_SESSION['flash_message'] = 'Failed to upload logo. Please try again.';
                    $_SESSION['flash_type'] = 'danger';
                }
            } else {
                $_SESSION['flash_message'] = implode('<br>', $errors);
                $_SESSION['flash_type'] = 'danger';
            }
        } else {
            $_SESSION['flash_message'] = 'Please select a file to upload.';
            $_SESSION['flash_type'] = 'danger';
        }
    } elseif ($action === 'remove_logo') {
        $uploadDir = __DIR__ . '/../assets/uploads/logo';
        $logoFiles = glob($uploadDir . '/logo.*');
        
        foreach ($logoFiles as $logoFile) {
            if (is_file($logoFile)) {
                unlink($logoFile);
            }
        }
        
        $_SESSION['flash_message'] = 'Logo removed successfully!';
        $_SESSION['flash_type'] = 'success';
        logActivity('Logo Removal', 'System logo removed');
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get current logo
$logoPath = null;
$logoFiles = glob(__DIR__ . '/../assets/uploads/logo/logo.*');
if (!empty($logoFiles)) {
    $logoPath = BASE_URL . '/assets/uploads/logo/' . basename($logoFiles[0]);
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-success me-2">Administrator</span>
                <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-image me-2"></i>
                    Current Logo
                </h5>
            </div>
            <div class="card-body text-center">
                <?php if ($logoPath): ?>
                    <div class="mb-3">
                        <img src="<?php echo $logoPath; ?>" alt="System Logo" 
                             class="img-fluid" style="max-height: 150px; border: 1px solid #ddd; padding: 10px; border-radius: 8px;">
                    </div>
                    <p class="text-muted small">
                        <strong>Current Logo:</strong> <?php echo basename($logoFiles[0]); ?><br>
                        <strong>File Size:</strong> <?php echo number_format(filesize($logoFiles[0]) / 1024, 2); ?> KB<br>
                        <strong>Upload Date:</strong> <?php echo date('Y-m-d H:i:s', filemtime($logoFiles[0])); ?>
                    </p>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="remove_logo">
                        <button type="submit" class="btn btn-danger" 
                                onclick="return confirm('Are you sure you want to remove the current logo?')">
                            <i class="bi bi-trash me-2"></i>
                            Remove Logo
                        </button>
                    </form>
                <?php else: ?>
                    <div class="mb-3">
                        <i class="bi bi-image" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    <p class="text-muted">No logo uploaded yet.</p>
                    <p class="text-muted small">Upload a logo to display it throughout the system and on receipts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-upload me-2"></i>
                    Upload New Logo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_logo">
                    
                    <div class="mb-3">
                        <label for="logo" class="form-label">Select Logo Image</label>
                        <div class="input-group">
                            <input type="file" class="form-control" id="logo" name="logo" 
                                   accept="image/*" required>
                            <span class="input-group-text">
                                <i class="bi bi-image"></i>
                            </span>
                        </div>
                        <small class="text-muted">
                            Allowed formats: <?php echo implode(', ', LOGO_ALLOWED_TYPES); ?><br>
                            Maximum size: <?php echo (LOGO_MAX_SIZE / 1024 / 1024); ?>MB
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Logo Guidelines:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Use high-quality images for best results</li>
                                <li>Square or rectangular images work best</li>
                                <li>Recommended size: 200x100 to 400x200 pixels</li>
                                <li>Logo will appear in header and on receipts</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-2"></i>
                            Upload Logo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Logo Display Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Where Logo Appears:</h6>
                        <ul>
                            <li><i class="bi bi-check-circle text-success me-2"></i>System Header (all pages)</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Sales Receipts</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Reports (if enabled)</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>Login Page</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Technical Details:</h6>
                        <ul>
                            <li><strong>Storage Path:</strong> <code>/assets/uploads/logo/</code></li>
                            <li><strong>File Naming:</strong> <code>logo.[extension]</code></li>
                            <li><strong>Supported Formats:</strong> <?php echo implode(', ', LOGO_ALLOWED_TYPES); ?></li>
                            <li><strong>Max File Size:</strong> <?php echo (LOGO_MAX_SIZE / 1024 / 1024); ?>MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
