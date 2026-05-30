<?php
/**
 * Activity Update Endpoint
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    exit('Unauthorized');
}

// Update last activity time
updateLastActivity();

// Return success response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Activity updated']);
?>
