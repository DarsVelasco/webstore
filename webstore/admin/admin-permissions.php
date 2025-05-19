<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user details
$user = getUserById($userId);
if (!$user || $user['role'] !== 'admin') {
    $_SESSION['error'] = "Invalid user or user is not an admin.";
    header("Location: users.php");
    exit();
}

// Handle permission updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['permissions'])) {
        if (updateAdminPermissions($userId, $_POST['permissions'])) {
            $_SESSION['success'] = "Permissions updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update permissions.";
        }
    }
    header("Location: admin-permissions.php?id=" . $userId);
    exit();
}

// Get current permissions
$permissions = getAdminPermissions($userId);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="container">
        <h1>Manage Admin Permissions</h1>
        <h3>Admin: <?= htmlspecialchars($user['full_name']) ?></h3>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Product Management</h4>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_products" 
                                       <?= in_array('manage_products', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Products</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_categories" 
                                       <?= in_array('manage_categories', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Categories</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_inventory" 
                                       <?= in_array('manage_inventory', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Inventory</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Order Management</h4>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="view_orders" 
                                       <?= in_array('view_orders', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">View Orders</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_orders" 
                                       <?= in_array('manage_orders', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Orders</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h4>User Management</h4>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="view_users" 
                                       <?= in_array('view_users', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">View Users</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_users" 
                                       <?= in_array('manage_users', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Users</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h4>Reports</h4>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="view_reports" 
                                       <?= in_array('view_reports', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">View Reports</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="permissions[]" value="manage_reports" 
                                       <?= in_array('manage_reports', $permissions) ? 'checked' : '' ?>>
                                <label class="form-check-label">Manage Reports</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Permissions</button>
                        <a href="users.php" class="btn btn-secondary">Back to Users</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.content-wrapper {
    padding: 20px;
}
.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.form-check {
    margin-bottom: 10px;
}
h4 {
    margin-bottom: 15px;
    color: #333;
}
</style>

<?php include 'includes/footer.php'; ?> 