<?php
/**
 * Logo Functions
 * Inventory Management System
 */

/**
 * Get current logo URL
 */
function getLogoUrl() {
    $logoFiles = glob(__DIR__ . '/../assets/uploads/logo/logo.*');
    if (!empty($logoFiles)) {
        return BASE_URL . '/assets/uploads/logo/' . basename($logoFiles[0]);
    }
    return null;
}

/**
 * Get logo file info
 */
function getLogoInfo() {
    $logoFiles = glob(__DIR__ . '/../assets/uploads/logo/logo.*');
    if (!empty($logoFiles)) {
        $logoFile = $logoFiles[0];
        return [
            'path' => BASE_URL . '/assets/uploads/logo/' . basename($logoFile),
            'filename' => basename($logoFile),
            'size' => filesize($logoFile),
            'modified' => filemtime($logoFile)
        ];
    }
    return null;
}

/**
 * Check if logo exists
 */
function hasLogo() {
    $logoFiles = glob(__DIR__ . '/../assets/uploads/logo/logo.*');
    return !empty($logoFiles);
}
?>
