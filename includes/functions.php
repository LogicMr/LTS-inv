<?php
/**
 * Helper Functions
 * Inventory Management System
 */

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null,
            'role_id' => $_SESSION['role_id'] ?? null,
            'role_name' => $_SESSION['role_name'] ?? null,
            'login_time' => $_SESSION['login_time'] ?? null
        ];
    }
    return null;
}

// Format currency with TSh support (simplified for TSh-only system)
function formatCurrency($amount, $currencyId = null) {
    return 'TSh ' . number_format($amount, 2);
}

// Format date
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = DISPLAY_DATETIME_FORMAT) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

// Clean input data
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate required fields
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate numeric
function validateNumeric($value) {
    return is_numeric($value) && $value >= 0;
}

// Generate random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Calculate days until expiry
function daysUntilExpiry($expiryDate) {
    if (empty($expiryDate) || $expiryDate === '0000-00-00') {
        return null;
    }
    
    $expiry = new DateTime($expiryDate);
    $today = new DateTime();
    $diff = $today->diff($expiry);
    
    return $diff->days;
}

// Check if product is expired
function isExpired($expiryDate) {
    $days = daysUntilExpiry($expiryDate);
    return $days !== null && $days < 0;
}

// Check if product is near expiry (within 30 days)
function isNearExpiry($expiryDate, $days = 30) {
    $daysUntil = daysUntilExpiry($expiryDate);
    return $daysUntil !== null && $daysUntil >= 0 && $daysUntil <= $days;
}

// Get alert badge HTML
function getAlertBadge($status) {
    $badges = [
        'Low Stock' => '<span class="badge badge-warning">Low Stock</span>',
        'Near Expiry' => '<span class="badge badge-info">Near Expiry</span>',
        'Expired' => '<span class="badge badge-danger">Expired</span>',
        'Normal' => '<span class="badge badge-success">Normal</span>'
    ];
    
    return $badges[$status] ?? '';
}

// Pagination helper
function getPagination($totalItems, $itemsPerPage = ITEMS_PER_PAGE, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
        'prev_page' => $currentPage - 1,
        'next_page' => $currentPage + 1
    ];
}

// Build pagination HTML
function buildPagination($pagination, $baseUrl) {
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['prev_page'] . '">Previous</a></li>';
    }
    
    $startPage = max(1, $pagination['current_page'] - 2);
    $endPage = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $active = $i == $pagination['current_page'] ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    if ($endPage < $pagination['total_pages']) {
        if ($endPage < $pagination['total_pages'] - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '">' . $pagination['total_pages'] . '</a></li>';
    }
    
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['next_page'] . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

// Export to CSV
function exportToCSV($data, $filename, $headers = []) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8
    fwrite($output, "\xEF\xBB\xBF");
    
    // Write headers
    if (!empty($headers)) {
        fputcsv($output, $headers);
    }
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Log activity
function logActivity($action, $description, $userId = null) {
    if ($userId === null && isLoggedIn()) {
        $userId = $_SESSION['user_id'];
    }
    
    $sql = "INSERT INTO activity_logs (user_id, action, description, created_at) VALUES (?, ?, ?, NOW())";
    executeNonQuery($sql, [$userId, $action, $description]);
}

// Get user role name
function getRoleName($roleId) {
    if (!$roleId || $roleId === 0) {
        return 'Unknown';
    }
    
    $sql = "SELECT name FROM roles WHERE id = ?";
    $role = fetchRow($sql, [$roleId]);
    return $role ? $role['name'] : 'Unknown';
}

// Check if user has permission
function hasPermission($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    $userRole = getRoleName($user['role_id']);
    
    // Admin has all permissions
    if ($userRole === 'Admin') {
        return true;
    }
    
    // Role hierarchy
    $roleHierarchy = [
        'Admin' => 3,
        'Manager' => 2,
        'Cashier' => 1
    ];
    
    return $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

// Sanitize filename
function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

// Logout function
function logout() {
    // Destroy all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

// Login function
function login($username, $password) {
    global $pdo;
    
    try {
        error_log("Attempting login for username: $username");
        
        $stmt = $pdo->prepare("SELECT u.*, r.name as role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? AND u.is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        error_log("User found: " . ($user ? "yes" : "no"));
        
        if ($user && password_verify($password, $user['password'])) {
            error_log("Password verified successfully");
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['role_name'] = $user['role_name'];
            $_SESSION['login_time'] = time();
            
            error_log("Session variables set: " . json_encode($_SESSION));
            
            // Log the login activity
            logActivity('Login', 'User logged in successfully', $user['id']);
            
            return true;
        }
        
        error_log("Password verification failed");
        
        // Log failed login attempt
        logActivity('Login Failed', 'Failed login attempt for username: ' . $username, null);
        
        return false;
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

// Get user dashboard based on role
function getUserDashboard() {
    if (!isLoggedIn()) {
        return 'auth/login.php';
    }
    
    $user = getCurrentUser();
    $roleId = $user['role_id'];
    
    // Redirect based on role
    switch ($roleId) {
        case 1: // Admin
            return 'admin/dashboard.php';
        case 2: // Manager
            return 'manager/dashboard.php';
        case 3: // Cashier
            return 'cashier/dashboard.php';
        default:
            return 'auth/login.php';
    }
}

// Verify user password for re-authentication
function verifyReauth($password) {
    global $pdo;
    
    if (!isLoggedIn()) {
        return false;
    }
    
    try {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Verify reauth error: " . $e->getMessage());
        return false;
    }
}

// Update last activity timestamp
function updateLastActivity() {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    
    try {
        $userId = $_SESSION['user_id'];
        $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Update last activity error: " . $e->getMessage());
        return false;
    }
}

// Require authentication - redirect to login if not logged in
function requireAuth() {
    if (!isLoggedIn()) {
        // Store the requested URL for redirect after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redirect to login page
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit();
    }
}

// Check if user can access specific module
function canAccessModule($module) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user = getCurrentUser();
    $roleId = $user['role_id'];
    
    // Define module permissions - Admin and Manager access
    $modulePermissions = [
        'admin' => [1], // Admin only
        'reports' => [1, 2], // Admin, Manager
        'products' => [1, 2], // Admin, Manager (stock management)
        'purchases' => [1, 2], // Admin, Manager (stock management)
        'customers' => [1, 2], // Admin, Manager
        'suppliers' => [1, 2], // Admin, Manager (stock management)
        'categories' => [1, 2], // Admin, Manager
        'users' => [1], // Admin only
        'settings' => [1], // Admin only
        'backup' => [1], // Admin only
        'notifications' => [1, 2], // Admin, Manager
        'pos' => [1], // Admin only
        'dashboard' => [1, 2], // Admin, Manager dashboards
    ];
    
    return isset($modulePermissions[$module]) && in_array($roleId, $modulePermissions[$module]);
}

// Require specific role to access page
function requireRole($requiredRole) {
    if (!isLoggedIn()) {
        requireAuth(); // This will handle the redirect
    }
    
    $user = getCurrentUser();
    $userRole = $user['role_id'];
    
    // Get role ID from role name
    $roleIds = [
        'Admin' => 1,
        'Manager' => 2,
        'Cashier' => 3
    ];
    
    $requiredRoleId = $roleIds[$requiredRole] ?? null;
    
    if ($requiredRoleId === null) {
        // Invalid role name
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>Access Denied</h1>';
        echo '<p>Invalid role requirement.</p>';
        exit();
    }
    
    // Check role-based access
    if ($requiredRole === 'Admin' && $userRole !== 1) {
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>Access Denied</h1>';
        echo '<p>Only Admin can access this page.</p>';
        echo '<p>Required role: ' . htmlspecialchars($requiredRole) . '</p>';
        echo '<p>Your role: ' . getRoleName($userRole) . '</p>';
        exit();
    }
    
    if ($requiredRole === 'Manager' && $userRole !== 2) {
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>Access Denied</h1>';
        echo '<p>Only Manager can access this page.</p>';
        echo '<p>Required role: ' . htmlspecialchars($requiredRole) . '</p>';
        echo '<p>Your role: ' . getRoleName($userRole) . '</p>';
        exit();
    }
}

// Additional Database Helper Functions (not in database.php)
function insert($table, $data) {
    global $pdo;
    try {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Database error in insert: " . $e->getMessage());
        return false;
    }
}

function update($table, $data, $where, $params = []) {
    global $pdo;
    try {
        $setClause = [];
        foreach ($data as $column => $value) {
            $setClause[] = "$column = ?";
        }
        $setClause = implode(', ', $setClause);
        $sql = "UPDATE $table SET $setClause WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(array_values($data), $params));
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log("Database error in update: " . $e->getMessage());
        return false;
    }
}

function getDashboardUrl() {
    $user = getCurrentUser();
    if (!$user) {
        return BASE_URL . '/auth/login.php';
    }
    
    switch ($user['role_id']) {
        case 1: // Admin
            return BASE_URL . '/admin/dashboard.php';
        case 2: // Manager
            return BASE_URL . '/manager/dashboard.php';
        case 3: // Cashier
            return BASE_URL . '/cashier/dashboard.php';
        default:
            return BASE_URL . '/auth/login.php';
    }
}
?>
