<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verify this order belongs to the current user
$order = getUserOrder($userId, $orderId);
if (!$order) {
    header("Location: orders.php");
    exit();
}

// Get order items
$orderItems = getOrderItems($orderId);

include 'includes/header.php';
?>

<section class="thank-you-section py-5">
    <div class="container">
        <div class="thank-you-content">
            <div class="success-message mb-4">
                <i class="fas fa-receipt"></i>
                <h1 class="h3 fw-bold">Order Details</h1>
                <p>Order ID: #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></p>
                <p>Status: <span class="badge bg-<?= getStatusColor($order['status']) ?>"><?= ucfirst($order['status']) ?></span></p>
            </div>

            <div class="order-summary mb-4">
                <h2 class="h5 fw-bold mb-3">Order Summary</h2>
                <div class="order-items mb-3">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="order-item d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                        <div class="item-image" style="width: 60px; height: 60px;">
                            <img src="<?php echo htmlspecialchars(getProductImageUrl($item)); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-100 h-100 object-fit-cover rounded-3">
                        </div>
                        <div class="item-details flex-grow-1">
                            <h3 class="h6 fw-semibold mb-1"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-muted small mb-0">Qty: <?php echo $item['quantity']; ?></p>
                        </div>
                        <div class="item-price fw-semibold">
                            ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="order-total bg-light rounded-3 p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">Total Amount:</span>
                        <span class="fw-bold text-primary">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="shipping-info mb-4">
                <h3 class="h6 fw-bold mb-2">Shipping Information</h3>
                <p class="mb-1"><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                <p class="mb-0 text-muted">Payment Method: <span class="fw-semibold"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['payment_method']))); ?></span></p>
            </div>

            <div class="next-steps mt-4">
                <a href="orders.php" class="btn btn-outline-primary">Back to Orders</a>
                <a href="shop.php" class="btn btn-primary ms-2">Continue Shopping</a>
            </div>
        </div>
    </div>
</section>

<style>
.thank-you-section {
    padding: 4rem 0;
    background-color: #f8f9fa;
}

.thank-you-content {
    max-width: 800px;
    margin: 0 auto;
    background: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.07);
}

.success-message {
    text-align: center;
    margin-bottom: 2rem;
}

.success-message i {
    font-size: 2.5rem;
    color: #0d6efd;
    margin-bottom: 0.5rem;
}

.order-summary {
    margin-bottom: 2rem;
}

.order-items {
    margin: 1.5rem 0;
}

.order-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.item-image {
    width: 60px;
    height: 60px;
    margin-right: 1rem;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.item-details h3 {
    margin: 0 0 0.25rem 0;
    font-size: 1rem;
}

.order-total {
    margin: 1rem 0 0 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.shipping-info {
    margin: 1.5rem 0 0 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
}
</style>

<?php include 'includes/footer.php'; ?> 