<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get user ID from URL
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Get user details
$user = getUserById($userId);
if (!$user) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: users.php");
    exit();
}

// Get user's orders with pagination
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 10;
$orders = getUserOrders($userId, $currentPage, $itemsPerPage);
$totalOrders = getTotalUserOrders($userId);
$totalPages = ceil($totalOrders / $itemsPerPage);

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Orders for <?= htmlspecialchars($user['full_name']) ?></h1>
            <a href="users.php" class="btn btn-secondary">Back to Users</a>
        </div>

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
                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">No orders found for this user.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Items</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?= $order['order_id'] ?></td>
                                        <td><?= date('M j, Y H:i', strtotime($order['created_at'] ?? $order['order_date'] ?? 'now')) ?></td>
                                        <td>₱<?= number_format($order['total'] ?? $order['total_amount'] ?? 0, 2) ?></td>
                                        <td>
                                            <span class="badge bg-<?= getStatusColor($order['status']) ?>">
                                                <?= ucfirst($order['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td><?= $order['total_items'] ?? 0 ?> items</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="order-details.php?id=<?= $order['order_id'] ?>" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
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
                                        <a class="page-link" href="?user_id=<?= $userId ?>&page=<?= $currentPage - 1 ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?user_id=<?= $userId ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?user_id=<?= $userId ?>&page=<?= $currentPage + 1 ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.content-wrapper {
    padding: 20px;
    min-width: 320px; /* Minimum width to prevent content squishing */
    overflow-x: auto; /* Allow horizontal scroll if needed */
}

.card {
    margin-top: 20px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    min-width: 320px; /* Minimum card width */
}

.table-responsive {
    min-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}

.table {
    width: 100%;
    min-width: 800px; /* Minimum table width to prevent squishing */
}

.table th {
    background-color: #f8f9fa;
    white-space: nowrap; /* Prevent header text wrapping */
    padding: 12px 8px;
}

.table td {
    padding: 12px 8px;
    vertical-align: middle;
}

.btn-group {
    gap: 5px;
    flex-wrap: nowrap; /* Prevent button wrapping */
    display: flex;
    align-items: center;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    white-space: nowrap;
}

/* Make badges more readable at different zoom levels */
.badge {
    padding: 0.5em 0.75em;
    font-size: 0.875rem;
    white-space: nowrap;
    min-width: 80px;
    text-align: center;
}

/* Responsive container adjustments */
.container {
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
    max-width: 100%;
}

/* Improved pagination responsiveness */
.pagination {
    flex-wrap: wrap;
    justify-content: center;
    gap: 5px;
}

.page-link {
    padding: 0.375rem 0.75rem;
    min-width: 38px;
    text-align: center;
}

/* Responsive text handling */
.table td, .table th {
    min-width: 100px; /* Minimum width for cells */
}

.table td:first-child, 
.table th:first-child {
    min-width: 80px; /* Order ID column */
}

.table td:nth-child(2), 
.table th:nth-child(2) {
    min-width: 120px; /* Date column */
}

.table td:nth-child(3), 
.table th:nth-child(3) {
    min-width: 100px; /* Total column */
}

.table td:nth-child(4), 
.table th:nth-child(4) {
    min-width: 100px; /* Status column */
}

.table td:nth-child(5), 
.table th:nth-child(5) {
    min-width: 80px; /* Items column */
}

.table td:last-child, 
.table th:last-child {
    min-width: 100px; /* Actions column */
}

/* Media queries for better zoom support */
@media screen and (max-width: 1200px) {
    .container {
        padding-right: 10px;
        padding-left: 10px;
    }
}

@media screen and (max-width: 768px) {
    .btn-group {
        gap: 3px;
    }
    
    .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.8rem;
    }
    
    .table th, .table td {
        padding: 8px 6px;
    }
}

/* Ensure text remains readable at different zoom levels */
@media screen and (max-width: 480px) {
    .content-wrapper {
        padding: 10px;
    }
    
    h1 {
        font-size: 1.5rem;
    }
    
    .table {
        font-size: 0.9rem;
    }
}

/* High DPI screen support */
@media screen and (-webkit-min-device-pixel-ratio: 2), 
       screen and (min-resolution: 192dpi) {
    .table, .btn, .badge {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
}
</style>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>

<?php include 'includes/footer.php'; ?> 