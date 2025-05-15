<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);
$recentOrders = getUserRecentOrders($userId, 3);

// Get dashboard statistics
$totalOrders = getTotalOrders();
$totalProducts = getTotalProducts();
$totalRevenue = getTotalRevenue();

// Get recent orders
$recentOrders = getRecentOrders(5);

// Get sales data for chart (last 30 days)
$salesData = getSalesData(30);

include 'includes/header.php';
?>

<h1>Dashboard</h1>

<div class="dashboard-cards">
    <div class="card">
        <div class="card-icon bg-primary">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="card-info">
            <h3>Total Orders</h3>
            <p><?= $totalOrders ?></p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-icon bg-success">
            <i class="fas fa-box-open"></i>
        </div>
        <div class="card-info">
            <h3>Total Products</h3>
            <p><?= $totalProducts ?></p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-icon bg-info">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="card-info">
            <h3>Total Revenue</h3>
            <p>$<?= number_format($totalRevenue, 2) ?></p>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <div class="chart-container">
        <h2>Sales Overview (Last 30 Days)</h2>
        <canvas id="salesChart"></canvas>
    </div>
    
    <div class="recent-orders">
        <h2>Recent Orders</h2>
        
        <div class="orders-table">
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><?= htmlspecialchars($order['full_name']) ?></td>
                            <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
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
    </div>
</div>

<script>
    // Sales chart using Chart.js
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($salesData, 'date')) ?>,
            datasets: [{
                label: 'Daily Sales',
                data: <?= json_encode(array_column($salesData, 'total')) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>