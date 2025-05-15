<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=checkout");
    exit();
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get cart items
$cartItems = getCartItems($userId);

// Redirect if cart is empty
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

$subtotal = calculateCartSubtotal($userId);
$shipping = 5.00; // Flat rate shipping for example
$total = $subtotal + $shipping;

$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Starting checkout process...");
    
    // Validate form data
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $paymentMethod = trim($_POST['payment_method']);
    
    error_log("Form data received - Name: $fullName, Email: $email, Address: " . substr($address, 0, 20) . "...");
    
    // Verify cart is not empty
    $cartItems = getCartItems($userId);
    if (empty($cartItems)) {
        $errorMessages['general'] = 'Your cart is empty.';
        error_log("Cart is empty for user: $userId");
    }
    
    // Verify stock for all items
    foreach ($cartItems as $item) {
        error_log("Checking stock for product {$item['product_id']}: Requested: {$item['quantity']}, Available: {$item['stock']}");
        if ($item['quantity'] > $item['stock']) {
            $errorMessages['general'] = "Sorry, {$item['name']} only has {$item['stock']} items in stock.";
            error_log("Stock not available for product {$item['product_id']}");
            break;
        }
    }
    
    if (empty($fullName)) {
        $errorMessages['full_name'] = 'Full name is required.';
    }
    
    if (empty($email)) {
        $errorMessages['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages['email'] = 'Please enter a valid email address.';
    }
    
    if (empty($address)) {
        $errorMessages['address'] = 'Address is required.';
    }
    
    if (empty($paymentMethod)) {
        $errorMessages['payment_method'] = 'Payment method is required.';
    }
    
    error_log("Validation complete. Error count: " . count($errorMessages));
    
    // If no errors, process order
    if (empty($errorMessages)) {
        error_log("Starting transaction for user: $userId");
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $orderId = createOrder($userId, $total, $address, $paymentMethod);
            error_log("Order created with ID: " . $orderId);
            
            // Add order items
            foreach ($cartItems as $item) {
                error_log("Processing item: " . $item['product_id'] . " - Quantity: " . $item['quantity'] . " - Stock: " . $item['stock']);
                
                // Verify stock one more time
                $product = getProductById($item['product_id']);
                if ($product['stock'] < $item['quantity']) {
                    throw new Exception("Not enough stock for product: " . $item['name']);
                }
                
                addOrderItem($orderId, $item['product_id'], $item['quantity'], $item['price']);
                error_log("Order item added");
                
                // Update product stock
                updateProductStock($item['product_id'], -$item['quantity']);
                error_log("Stock updated for product: " . $item['product_id']);
                
                // Log inventory change
                logInventoryChange($item['product_id'], 'sold', $item['quantity'], $userId);
                error_log("Inventory change logged");
            }
            
            // Clear cart
            clearCart($userId);
            error_log("Cart cleared for user: $userId");
            
            // Commit transaction
            $conn->commit();
            error_log("Transaction committed successfully");
            
            // Redirect to thank you page
            header("Location: thank-you.php?order_id=$orderId");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            error_log("Checkout Error: " . $e->getMessage() . "\nStack trace: " . $e->getTraceAsString());
            $errorMessages['general'] = 'There was an error processing your order. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="checkout-section">
    <div class="container">
        <div class="checkout-header">
            <h1>Checkout</h1>
            <div class="checkout-steps">
                <div class="step active">1. Shipping</div>
                <div class="step">2. Payment</div>
                <div class="step">3. Confirmation</div>
            </div>
        </div>
        
        <?php if (isset($errorMessages['general'])): ?>
            <div class="alert alert-danger"><?= $errorMessages['general'] ?></div>
        <?php endif; ?>
        
        <div class="checkout-grid">
            <div class="checkout-form">
                <form method="post" action="checkout.php">
                    <div class="form-section">
                        <h2>Shipping Information</h2>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name*</label>
                            <input type="text" name="full_name" id="full_name" 
                                   value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : htmlspecialchars($user['full_name']) ?>" required>
                            <?php if (isset($errorMessages['full_name'])): ?>
                                <span class="error-message"><?= $errorMessages['full_name'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address*</label>
                            <input type="email" name="email" id="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user['email']) ?>" required>
                            <?php if (isset($errorMessages['email'])): ?>
                                <span class="error-message"><?= $errorMessages['email'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" 
                                   value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user['phone']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Shipping Address*</label>
                            <textarea name="address" id="address" rows="3" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($user['address']) ?></textarea>
                            <?php if (isset($errorMessages['address'])): ?>
                                <span class="error-message"><?= $errorMessages['address'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Payment Method</h2>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="credit_card" value="credit_card" 
                                       <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') || !isset($_POST['payment_method']) ? 'checked' : '' ?> required>
                                <label for="credit_card">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                    Credit Card
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="paypal" value="paypal" 
                                       <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'paypal' ? 'checked' : '' ?>>
                                <label for="paypal">
                                    <i class="fab fa-paypal"></i>
                                    PayPal
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" name="payment_method" id="cod" value="cod" 
                                       <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod' ? 'checked' : '' ?>>
                                <label for="cod">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Cash on Delivery
                                </label>
                            </div>
                        </div>
                        <?php if (isset($errorMessages['payment_method'])): ?>
                            <span class="error-message"><?= $errorMessages['payment_method'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <a href="cart.php" class="btn btn-outline">Back to Cart</a>
                        <button type="submit" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
            
            <div class="order-summary">
                <h2>Your Order</h2>
                
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="item-details">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <p>Qty: <?= $item['quantity'] ?></p>
                            </div>
                            <div class="item-price">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>$<?= number_format($subtotal, 2) ?></span>
                    </div>
                    
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span>$<?= number_format($shipping, 2) ?></span>
                    </div>
                    
                    <div class="total-row grand-total">
                        <span>Total:</span>
                        <span>$<?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>