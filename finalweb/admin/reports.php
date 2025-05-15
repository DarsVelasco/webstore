<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

if (!isAdmin()) {
    header("Location: login.php");
    exit();
}

// Get various statistics
$totalRevenue = getTotalRevenue();
$totalOrders = getTotalOrders();
$totalProducts = getTotalProducts();
$totalUsers = getTotalUsers();

// Get sales data for the last 30 days
$salesData = getSalesData(30);

// Get low stock products
$lowStockProducts = getLowStockProducts(5);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <h1>Reports & Analytics</h1>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-body">
                <h5>Total Revenue</h5>
                <h2>$<?= number_format($totalRevenue, 2) ?></h2>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>Total Orders</h5>
                <h2><?= number_format($totalOrders) ?></h2>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>Total Products</h5>
                <h2><?= number_format($totalProducts) ?></h2>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5>Total Customers</h5>
                <h2><?= number_format($totalUsers) ?></h2>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Sales Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sales Trend (Last 30 Days)</h5>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Alert</h5>
                    <div class="low-stock-list">
                        <?php if (empty($lowStockProducts)): ?>
                            <p class="text-success">All products are well stocked!</p>
                        <?php else: ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <div class="low-stock-item">
                                    <div class="product-info">
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <span class="stock-count <?= $product['stock'] === 0 ? 'out-of-stock' : '' ?>">
                                            <?= $product['stock'] ?> units left
                                        </span>
                                    </div>
                                    <a href="product-form.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-warning">
                                        Update Stock
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Recent Orders</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recentOrders = getRecentOrders(5);
                        foreach ($recentOrders as $order): 
                        ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['full_name']) ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-body {
    padding: 20px;
}

.card h5 {
    color: #6c757d;
    margin-bottom: 10px;
    font-size: 14px;
}

.card h2 {
    color: #2c3e50;
    margin: 0;
    font-size: 24px;
}

.low-stock-list {
    max-height: 400px;
    overflow-y: auto;
}

.low-stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.low-stock-item:last-child {
    border-bottom: none;
}

.product-info {
    display: flex;
    flex-direction: column;
}

.stock-count {
    font-size: 12px;
    color: #666;
}

.stock-count.out-of-stock {
    color: #dc3545;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: 500;
}

.table {
    margin-bottom: 0;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare sales data for the chart
const salesData = <?= json_encode($salesData) ?>;
const dates = salesData.map(item => item.date);
const totals = salesData.map(item => item.total);

// Create the sales chart
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: dates,
        datasets: [{
            label: 'Daily Sales',
            data: totals,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value;
                    }
                }
            }
        }
    }
});
</script>

<?php
// Helper function to get appropriate badge color for order status
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

include 'includes/footer.php';
?> 