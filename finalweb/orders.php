<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Get all orders for the user
$orders = getUserOrders($userId);

include 'includes/header.php';
?>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>My Orders</h1>
            <p>View your order history and track current orders.</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Sidebar Navigation -->
            <div class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <img src="images/default-profile.jpg" alt="Profile Image">
                    </div>
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($_SESSION['full_name']) ?></h3>
                        <p><?= htmlspecialchars($_SESSION['email']) ?></p>
                    </div>
                </div>
                
                <nav class="dashboard-nav">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li class="active"><a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><a href="account-settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="dashboard-content">
                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h2>No Orders Yet</h2>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                        <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['order_id'] ?></td>
                                        <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                        <td><?= countOrderItems($order['order_id']) ?></td>
                                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                        <td>
                                            <span class="status-badge <?= $order['status'] ?>">
                                                <?= ucfirst($order['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm">View Details</a>
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
</section>

<?php include 'includes/footer.php'; ?>