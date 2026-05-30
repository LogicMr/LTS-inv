<?php
/**
 * Suppliers Management
 * Inventory Management System
 */
require_once __DIR__ . '/../config/config.php';
requireAuth();
requireRole('Admin');

$pageTitle = 'Suppliers Management';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = cleanInput($_POST['name']);
        $contact_person = cleanInput($_POST['contact_person']);
        $phone = cleanInput($_POST['phone']);
        $email = cleanInput($_POST['email']);
        $address = cleanInput($_POST['address']);
        
        // Validate
        if (empty($name)) {
            $_SESSION['flash_message'] = 'Supplier name is required';
            $_SESSION['flash_type'] = 'danger';
        } else {
            $sql = "INSERT INTO suppliers (name, contact_person, phone, email, address) 
                    VALUES (?, ?, ?, ?, ?)";
            executeNonQuery($sql, [$name, $contact_person, $phone, $email, $address]);
            
            $_SESSION['flash_message'] = 'Supplier added successfully';
            $_SESSION['flash_type'] = 'success';
        }
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get suppliers list
$suppliers = fetchAll("SELECT * FROM suppliers ORDER BY name");

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="bi bi-plus-circle"></i> Add Supplier
            </button>
        </div>
    </div>
</div>

<!-- Suppliers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Supplier Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td><?php echo $supplier['name']; ?></td>
                        <td><?php echo $supplier['contact_person'] ?: '-'; ?></td>
                        <td><?php echo $supplier['phone'] ?: '-'; ?></td>
                        <td><?php echo $supplier['email'] ?: '-'; ?></td>
                        <td><?php echo $supplier['address'] ?: '-'; ?></td>
                        <td><?php echo formatDate($supplier['created_at']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i>
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

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Supplier Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
