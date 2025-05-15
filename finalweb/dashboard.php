<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user data
$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get recent orders
$recentOrders = getUserRecentOrders($userId, 3);

include 'includes/header.php';
?>

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($user['full_name']) ?></h1>
            <p>Here's what's happening with your account.</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Sidebar Navigation -->
            <div class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <img src="images/default-profile.jpg" alt="Profile Image">
                    </div>
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
                
                <nav class="dashboard-nav">
                    <ul>
                        <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li><a href="account-settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="dashboard-content">
                <div class="dashboard-cards">
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="card-info">
                            <h3>Total Orders</h3>
                            <p><?= countUserOrders($userId) ?></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="card-info">
                            <h3>Orders in Transit</h3>
                            <p><?= countUserOrdersByStatus($userId, 'shipped') ?></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="card-info">
                            <h3>Completed Orders</h3>
                            <p><?= countUserOrdersByStatus($userId, 'delivered') ?></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="card-info">
                            <h3>Reviews</h3>
                            <p><?= countUserReviews($userId) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="recent-orders">
                    <h2>Recent Orders</h2>
                    
                    <?php if (empty($recentOrders)): ?>
                        <p>You haven't placed any orders yet. <a href="shop.php">Start shopping</a>!</p>
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
                                    <?php foreach ($recentOrders as $order): ?>
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
                                                <a href="order-details.php?id=<?= $order['order_id'] ?>" class="btn btn-sm">View</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="view-all">
                            <a href="orders.php" class="btn">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recently Viewed Products -->
                <div class="recently-viewed">
                    <h2>Recently Viewed</h2>
                    <div class="product-grid">
                        <?php 
                        $recentlyViewed = getRecentlyViewedProducts($userId, 4);
                        foreach ($recentlyViewed as $product): 
                        ?>
                            <?php include 'includes/product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>