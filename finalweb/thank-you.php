<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Verify this order belongs to the current user
$order = getUserOrder($userId, $orderId);
if (!$order) {
    header("Location: index.php");
    exit();
}

// Get order items
$orderItems = getOrderItems($orderId);

include 'includes/header.php';
?>

<section class="thank-you-section">
    <div class="container">
        <div class="thank-you-content">
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <h1>Thank You for Your Order!</h1>
                <p>Your order has been successfully placed.</p>
                <p>Order ID: #<?php echo str_pad($orderId, 6, '0', STR_PAD_LEFT); ?></p>
            </div>

            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="order-items">
                    <?php foreach ($orderItems as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        </div>
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Quantity: <?php echo $item['quantity']; ?></p>
                            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-total">
                    <p>Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
                    <p>Status: <?php echo ucfirst($order['status']); ?></p>
                </div>

                <div class="shipping-info">
                    <h3>Shipping Information</h3>
                    <p><?php echo nl2br(htmlspecialchars($order['address'])); ?></p>
                </div>
            </div>

            <div class="next-steps">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                <a href="orders.php" class="btn btn-outline">View All Orders</a>
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
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.success-message {
    text-align: center;
    margin-bottom: 3rem;
}

.success-message i {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 1rem;
}

.success-message h1 {
    color: #333;
    margin-bottom: 1rem;
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
    padding: 1rem;
    border-bottom: 1px solid #eee;
}

.item-image {
    width: 80px;
    height: 80px;
    margin-right: 1rem;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}

.item-details h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
}

.order-total {
    margin: 1.5rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.shipping-info {
    margin: 1.5rem 0;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.next-steps {
    text-align: center;
    margin-top: 2rem;
}

.next-steps .btn {
    margin: 0 0.5rem;
}

.btn {
    display: inline-block;
    padding: 0.8rem 1.5rem;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.btn-outline {
    background-color: transparent;
    color: #007bff;
    border: 1px solid #007bff;
}

.btn-outline:hover {
    background-color: #007bff;
    color: white;
}
</style>

<?php include 'includes/footer.php'; ?>