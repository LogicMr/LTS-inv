<?php
/**
 * Roles Management
 * Inventory Management System
 */
require_once __DIR__ . '/../includes/header.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'Roles Management';

// Get roles list
$roles = fetchAll("SELECT * FROM roles ORDER BY id");
?>

<div class="row">
    <div class="col-12">
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary"><?php echo $role['name']; ?></span>
                        </td>
                        <td><?php echo $role['description']; ?></td>
                        <td><?php echo formatDate($role['created_at']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" disabled>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" disabled>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-info mt-3">
            <h6>Role Permissions:</h6>
            <ul class="mb-0">
                <li><strong>Admin:</strong> Full system access including user management</li>
                <li><strong>Manager:</strong> Product, purchase, and sales management</li>
                <li><strong>Cashier:</strong> Point of sale and basic sales functions</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
