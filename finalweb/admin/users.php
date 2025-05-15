<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';


// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        
        if ($userId === (int)$_SESSION['user_id'] && $_POST['action'] === 'delete') {
            $_SESSION['error'] = "You cannot delete your own account.";
        } else {
            switch ($_POST['action']) {
                case 'update_role':
                    if (updateUserRole($userId, $_POST['role'])) {
                        $_SESSION['success'] = "User role updated successfully.";
                    } else {
                        $_SESSION['error'] = "Failed to update user role.";
                    }
                    break;
                case 'delete':
                    if (deleteUser($userId)) {
                        $_SESSION['success'] = "User deleted successfully.";
                    } else {
                        $_SESSION['error'] = "Failed to delete user.";
                    }
                    break;
            }
        }
        
        // Redirect to prevent form resubmission
        header("Location: users.php");
        exit();
    }
}

// Get all users with pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$users = getAllUsers($currentPage, $itemsPerPage);
$totalUsers = getTotalUsers();
$totalPages = ceil($totalUsers / $itemsPerPage);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <h1>User Management</h1>
    
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
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            $isAdmin = $user['role'] === 'admin';
                        ?>
                            <tr class="<?= $isAdmin ? 'admin-row' : '' ?>">
                                <td><?= $user['user_id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($user['full_name']) ?>
                                    <?php if ($isAdmin): ?>
                                        <span class="badge bg-primary ms-2">Admin</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <form method="post" class="role-form">
                                        <input type="hidden" name="action" value="update_role">
                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                        <select name="role" class="form-select form-select-sm <?= $isAdmin ? 'admin-select' : '' ?>" 
                                                onchange="if(confirm('Are you sure you want to change this user\'s role?')) { this.form.submit(); } else { this.value='<?= $user['role'] ?>'; }"
                                                <?= $user['user_id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                            <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </form>
                                </td>
                                <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($isAdmin): ?>
                                            <a href="admin-permissions.php?id=<?= $user['user_id'] ?>" 
                                               class="btn btn-sm btn-warning" 
                                               data-bs-toggle="tooltip" 
                                               title="Manage Permissions">
                                                <i class="fas fa-key"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="view-orders.php?user_id=<?= $user['user_id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           data-bs-toggle="tooltip" 
                                           title="View Orders">
                                            <i class="fas fa-shopping-bag"></i>
                                        </a>
                                        <a href="view-profile.php?id=<?= $user['user_id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="View Profile">
                                            <i class="fas fa-user"></i>
                                        </a>
                                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                            <form method="post" class="d-inline delete-form">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete User"
                                                        onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="users.php?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="users.php?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="users.php?page=<?= $currentPage + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-body {
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
}

.action-buttons .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    padding: 0;
    margin: 0 2px;
    border-radius: 4px;
    transition: all 0.2s ease-in-out;
    position: relative;
    border: none;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.action-buttons .btn i {
    font-size: 14px;
}

.action-buttons form {
    margin: 0;
    display: inline-block;
}

.role-form {
    margin: 0;
}

.form-select-sm {
    padding: 0.25rem 2rem 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert {
    margin-bottom: 20px;
}

/* Button Colors */
.btn-warning {
    background-color: #ffc107;
    color: #000;
}

.btn-warning:hover {
    background-color: #ffca2c;
    color: #000;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.btn-info:hover {
    background-color: #138496;
    color: white;
}

.btn-primary {
    background-color: #0d6efd;
    color: white;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    color: white;
}

/* Hover effects */
.btn:hover {
    transform: translateY(-1px);
    transition: transform 0.2s;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.03);
}

/* Table Styles */
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    color: #495057;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .action-buttons {
        display: flex;
        gap: 4px;
    }
    
    .action-buttons .btn {
        width: 28px;
        height: 28px;
    }
    
    .action-buttons .btn i {
        font-size: 12px;
    }
    
    .badge {
        font-size: 0.7rem;
    }
}

/* Admin-specific styles */
.admin-row {
    background-color: rgba(13, 110, 253, 0.05) !important;
}

.admin-row:hover {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.admin-select {
    border-color: #0d6efd;
    font-weight: 500;
}

.badge {
    font-size: 0.75rem;
    padding: 0.25em 0.6em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>

<?php include 'includes/footer.php'; ?>