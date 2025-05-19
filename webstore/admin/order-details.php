<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Check if user is logged in and is admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Get order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$orderId) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: orders.php");
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get order details
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        $_SESSION['error'] = "Order not found";
        header("Location: orders.php");
        exit();
    }
    
    // Get order items
    $stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.price as unit_price
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    include 'includes/header.php';
} catch (Exception $e) {
    error_log('Error in order-details.php: ' . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching order details";
    header("Location: orders.php");
    exit();
}
?>

<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Order Details #<?= $orderId ?></h1>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    <div class="order-details">
        <div class="order-info">
            <div class="order-info-item">
                <h6>Order ID</h6>
                <p>#<?= $order['order_id'] ?></p>
            </div>
            <div class="order-info-item">
                <h6>Order Date</h6>
                <p><?= date('F j, Y', strtotime($order['order_date'])) ?></p>
            </div>
            <div class="order-info-item">
                <h6>Status</h6>
                <p><span class="status-badge <?= $order['status'] ?>"><?= ucfirst($order['status']) ?></span></p>
            </div>
            <div class="order-info-item">
                <h6>Customer Name</h6>
                <p><?= htmlspecialchars($order['full_name']) ?></p>
            </div>
            <div class="order-info-item">
                <h6>Email</h6>
                <p><?= htmlspecialchars($order['email']) ?></p>
            </div>
            <?php if ($order['phone']): ?>
            <div class="order-info-item">
                <h6>Phone</h6>
                <p><?= htmlspecialchars($order['phone']) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover order-items">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">₱<?= number_format($item['unit_price'], 2) ?></td>
                                    <td class="text-end">₱<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">₱<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                            <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                <td class="text-end">₱<?= number_format($order['tax_amount'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (isset($order['shipping_amount']) && $order['shipping_amount'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                <td class="text-end">₱<?= number_format($order['shipping_amount'], 2) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <?php if (isset($order['notes']) && !empty($order['notes'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.order-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.order-info-item {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 6px;
}

.order-info-item h6 {
    color: #6c757d;
    font-size: 0.8rem;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.order-info-item p {
    margin: 0;
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 500;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.processing { background: #cce5ff; color: #004085; }
.status-badge.shipped { background: #e8f5e9; color: #2e7d32; }
.status-badge.delivered { background: #d4edda; color: #155724; }
.status-badge.cancelled { background: #f8d7da; color: #721c24; }

.card {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    border: none;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

.table tfoot {
    background: #f8f9fa;
}

.table tfoot td {
    padding: 0.75rem 1rem;
}
</style>

<?php include 'includes/footer.php'; ?> 