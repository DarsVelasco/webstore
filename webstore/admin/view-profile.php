<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Check if user is logged in and is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$userId) {
    $_SESSION['error'] = "Invalid user ID";
    header("Location: users.php");
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get user details
    $stmt = $conn->prepare("
        SELECT u.*, 
               COUNT(o.order_id) as total_orders,
               COALESCE(SUM(o.total_amount), 0) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.user_id = o.user_id
        WHERE u.user_id = ?
        GROUP BY u.user_id
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header("Location: users.php");
        exit();
    }
    
    // Get user's recent orders
    $stmt = $conn->prepare("
        SELECT o.*, COUNT(oi.order_item_id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_date DESC
        LIMIT 5
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $recentOrders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    include 'includes/header.php';
} catch (Exception $e) {
    error_log('Error in view-profile.php: ' . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching user details";
    header("Location: users.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>User Profile</h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>

    <div class="row">
        <!-- User Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-placeholder mb-3">
                            <i class="fas fa-user fa-3x text-muted"></i>
                        </div>
                        <h4><?= htmlspecialchars($user['full_name']) ?></h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        <?php if ($user['phone']): ?>
                            <p class="text-muted"><?= htmlspecialchars($user['phone']) ?></p>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="user-details">
                        <div class="detail-item">
                            <span class="label">Role</span>
                            <span class="value">
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Customer</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Status</span>
                            <span class="value">
                                <?php if (isset($user['is_active']) && $user['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Member Since</span>
                            <span class="value"><?= date('F j, Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Total Orders</span>
                            <span class="value"><?= number_format($user['total_orders']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Total Spent</span>
                            <span class="value">₱<?= number_format($user['total_spent'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Card -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Orders</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-muted text-center mb-0">No orders found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>#<?= $order['order_id'] ?></td>
                                            <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                            <td><?= $order['item_count'] ?> items</td>
                                            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?= $order['order_id'] ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-placeholder {
    width: 100px;
    height: 100px;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.user-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.detail-item .label {
    color: #6c757d;
    font-size: 0.875rem;
}

.detail-item .value {
    font-weight: 500;
}

.badge {
    padding: 0.5em 0.75em;
}

.card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<?php include 'includes/footer.php'; ?> 