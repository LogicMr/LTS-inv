<?php
/**
 * Flash Messages Display
 * Inventory Management System
 */

// Display flash messages if they exist
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'info';
    
    // Clear the flash message after displaying
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
    
    // Map types to Bootstrap classes
    $alertClass = [
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        'primary' => 'alert-primary',
        'secondary' => 'alert-secondary'
    ];
    
    $class = $alertClass[$type] ?? 'alert-info';
    $icon = [
        'success' => 'bi-check-circle-fill',
        'danger' => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-triangle-fill',
        'info' => 'bi-info-circle-fill',
        'primary' => 'bi-info-circle-fill',
        'secondary' => 'bi-info-circle-fill'
    ];
    
    $iconClass = $icon[$type] ?? 'bi-info-circle-fill';
    
    echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
    echo '<i class="bi ' . $iconClass . ' me-2"></i>';
    echo htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    
    // Auto-dismiss after 5 seconds for success messages
    if ($type === 'success') {
        echo '<script>
            setTimeout(function() {
                var alert = document.querySelector(".alert-success");
                if (alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        </script>';
    }
}
?>
