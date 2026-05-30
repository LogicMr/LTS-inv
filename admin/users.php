<?php
/**
 * Enhanced User Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'User Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $username = cleanInput($_POST['username']);
        $full_name = cleanInput($_POST['full_name']);
        $email = cleanInput($_POST['email']);
        $phone = cleanInput($_POST['phone']);
        $role_id = (int)$_POST['role_id'];
        $password = $_POST['password'];
        
        // Validate
        if (empty($username) || empty($full_name) || empty($password)) {
            $_SESSION['flash_message'] = 'Please fill all required fields';
            $_SESSION['flash_type'] = 'danger';
        } else {
            // Check if username exists
            $existing = fetchRow("SELECT id FROM users WHERE username = ?", [$username]);
            if ($existing) {
                $_SESSION['flash_message'] = 'Username already exists';
                $_SESSION['flash_type'] = 'danger';
            } else {
                // Insert user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
                $sql = "INSERT INTO users (username, password, full_name, email, phone, role_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                executeNonQuery($sql, [$username, $hashedPassword, $full_name, $email, $phone, $role_id]);
                
                $_SESSION['flash_message'] = 'User created successfully';
                $_SESSION['flash_type'] = 'success';
            }
        }
    }
    
    elseif ($action === 'edit') {
        $user_id = (int)$_POST['user_id'];
        $username = cleanInput($_POST['username']);
        $full_name = cleanInput($_POST['full_name']);
        $email = cleanInput($_POST['email']);
        $phone = cleanInput($_POST['phone']);
        $role_id = (int)$_POST['role_id'];
        
        // Validate
        if (empty($username) || empty($full_name)) {
            $_SESSION['flash_message'] = 'Please fill all required fields';
            $_SESSION['flash_type'] = 'danger';
        } else {
            // Check if username exists (excluding current user)
            $existing = fetchRow("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $user_id]);
            if ($existing) {
                $_SESSION['flash_message'] = 'Username already exists';
                $_SESSION['flash_type'] = 'danger';
            } else {
                // Update user
                $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role_id = ? 
                        WHERE id = ?";
                executeNonQuery($sql, [$username, $full_name, $email, $phone, $role_id, $user_id]);
                
                $_SESSION['flash_message'] = 'User updated successfully';
                $_SESSION['flash_type'] = 'success';
            }
        }
    }
    
    elseif ($action === 'change_password') {
        $user_id = (int)$_POST['user_id'];
        $new_password = $_POST['new_password'];
        
        if (empty($new_password)) {
            $_SESSION['flash_message'] = 'Please provide a new password';
            $_SESSION['flash_type'] = 'danger';
        } else {
            // Update password
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            executeNonQuery($sql, [$hashedPassword, $user_id]);
            
            $_SESSION['flash_message'] = 'Password changed successfully';
            $_SESSION['flash_type'] = 'success';
        }
    }
    
    elseif ($action === 'toggle_status') {
        $user_id = (int)$_POST['user_id'];
        $new_status = $_POST['is_active'] === '1' ? 1 : 0;
        
        $sql = "UPDATE users SET is_active = ? WHERE id = ?";
        executeNonQuery($sql, [$new_status, $user_id]);
        
        $status_text = $new_status ? 'activated' : 'deactivated';
        $_SESSION['flash_message'] = "User {$status_text} successfully";
        $_SESSION['flash_type'] = 'success';
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get users list
$users = fetchAll("SELECT u.*, r.name as role_name 
                   FROM users u 
                   JOIN roles r ON u.role_id = r.id 
                   ORDER BY u.created_at DESC");

// Get roles
$roles = fetchAll("SELECT * FROM roles ORDER BY name");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-plus-circle"></i> Add User
            </button>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email'] ?: '-'); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($user['role_name']); ?></span></td>
                        <td>
                            <?php if ($user['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo formatDate($user['created_at']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['email'] ?? ''); ?>', '<?php echo htmlspecialchars($user['phone'] ?? ''); ?>', <?php echo $user['role_id']; ?>)">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="changePassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class="bi bi-key"></i> Password
                                </button>
                                <button type="button" class="btn btn-outline-<?php echo $user['is_active'] ? 'danger' : 'success'; ?> btn-sm" onclick="toggleUserStatus(<?php echo $user['id']; ?>, <?php echo $user['is_active'] ? '0' : '1'; ?>)">
                                    <i class="bi bi-<?php echo $user['is_active'] ? 'x-circle' : 'check-circle'; ?>"></i> <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Username *</label>
                        <input type="text" id="edit_username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" id="edit_phone" name="phone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role *</label>
                        <select id="edit_role_id" name="role_id" class="form-select" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['id']; ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" id="password_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">User</label>
                        <input type="text" id="password_username" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password *</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit User Function
function editUser(userId, username, fullName, email, phone, roleId) {
    // Populate edit modal
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_full_name').value = fullName;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_phone').value = phone;
    document.getElementById('edit_role_id').value = roleId;
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

// Change Password Function
function changePassword(userId, username) {
    // Populate password modal
    document.getElementById('password_user_id').value = userId;
    document.getElementById('password_username').value = username;
    
    // Clear password fields
    document.querySelector('input[name="new_password"]').value = '';
    document.querySelector('input[name="confirm_password"]').value = '';
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

// Toggle User Status Function
function toggleUserStatus(userId, newStatus) {
    if (confirm('Are you sure you want to ' + (newStatus == 1 ? 'activate' : 'deactivate') + ' this user?')) {
        // Create form and submit
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'toggle_status';
        form.appendChild(actionInput);
        
        var userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        form.appendChild(userIdInput);
        
        var statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'is_active';
        statusInput.value = newStatus;
        form.appendChild(statusInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Form validation for password change
document.querySelector('form[action*="change_password"]').addEventListener('submit', function(e) {
    var newPassword = document.querySelector('input[name="new_password"]').value;
    var confirmPassword = document.querySelector('input[name="confirm_password"]').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters long!');
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
