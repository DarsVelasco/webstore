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
$shipping = 125.00; // Flat rate shipping
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
        
        // Get database connection
        $conn = getDBConnection();
        
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
            
            // Calculate and update the final order total
            $orderTotal = calculateOrderTotal($orderId);
            $stmt = $conn->prepare("UPDATE orders SET total_amount = ? WHERE order_id = ?");
            $stmt->bind_param("di", $orderTotal, $orderId);
            $stmt->execute();
            error_log("Order total updated to: " . $orderTotal);
            
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

<section class="checkout-section py-5">
    <div class="container">
        <div class="checkout-header text-center mb-5">
            <h1 class="display-5 fw-bold mb-3">Checkout</h1>
            <div class="checkout-steps d-flex justify-content-center gap-4">
                <div class="step active d-flex align-items-center gap-2">
                    <span class="step-number">1</span>
                    <span class="step-text">Shipping</span>
                </div>
                <div class="step d-flex align-items-center gap-2">
                    <span class="step-number">2</span>
                    <span class="step-text">Payment</span>
                </div>
                <div class="step d-flex align-items-center gap-2">
                    <span class="step-number">3</span>
                    <span class="step-text">Confirmation</span>
                </div>
            </div>
        </div>
        
        <?php if (isset($errorMessages['general'])): ?>
            <div class="alert alert-danger rounded-4 shadow-sm mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $errorMessages['general'] ?>
            </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm rounded-4 border-0">
                    <div class="card-body p-4">
                        <form method="post" action="checkout.php">
                            <div class="form-section mb-4">
                                <h2 class="h4 fw-bold mb-4">Shipping Information</h2>
                                
                                <div class="form-group mb-3">
                                    <label for="full_name" class="form-label fw-semibold">Full Name*</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control form-control-lg rounded-3" 
                                           value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : htmlspecialchars($user['full_name']) ?>" required>
                                    <?php if (isset($errorMessages['full_name'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errorMessages['full_name'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address*</label>
                                    <input type="email" name="email" id="email" class="form-control form-control-lg rounded-3" 
                                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user['email']) ?>" required>
                                    <?php if (isset($errorMessages['email'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errorMessages['email'] ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-control form-control-lg rounded-3" 
                                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user['phone']) ?>">
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="address" class="form-label fw-semibold">Shipping Address*</label>
                                    <textarea name="address" id="address" rows="3" class="form-control form-control-lg rounded-3" required><?= isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($user['address']) ?></textarea>
                                    <?php if (isset($errorMessages['address'])): ?>
                                        <div class="invalid-feedback d-block"><?= $errorMessages['address'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h2 class="h4 fw-bold mb-4">Payment Method</h2>
                                
                                <div class="payment-methods">
                                    <div class="payment-method mb-3">
                                        <input type="radio" class="btn-check" name="payment_method" id="credit_card" value="credit_card" 
                                               <?= (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') || !isset($_POST['payment_method']) ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2" for="credit_card">
                                            <i class="fab fa-cc-visa fa-lg"></i>
                                            <i class="fab fa-cc-mastercard fa-lg"></i>
                                            <i class="fab fa-cc-amex fa-lg"></i>
                                            <span>Credit Card</span>
                                        </label>
                                    </div>
                                    
                                    <div class="payment-method mb-3">
                                        <input type="radio" class="btn-check" name="payment_method" id="paypal" value="paypal" 
                                               <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'paypal' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2" for="paypal">
                                            <i class="fab fa-paypal fa-lg"></i>
                                            <span>PayPal</span>
                                        </label>
                                    </div>
                                    
                                    <div class="payment-method mb-3">
                                        <input type="radio" class="btn-check" name="payment_method" id="cod" value="cod" 
                                               <?= isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-primary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2" for="cod">
                                            <i class="fas fa-money-bill-wave fa-lg"></i>
                                            <span>Cash on Delivery</span>
                                        </label>
                                    </div>
                                </div>
                                <?php if (isset($errorMessages['payment_method'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['payment_method'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions mt-4 d-flex gap-3">
                                <a href="cart.php" class="btn btn-outline-primary flex-grow-1 py-3 rounded-3">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                                </a>
                                <button type="submit" class="btn btn-primary flex-grow-1 py-3 rounded-3">
                                    <i class="fas fa-lock me-2"></i>Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm rounded-4 border-0 sticky-top" style="top: 2rem;">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-4">Order Summary</h2>
                        
                        <div class="order-items mb-4">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="order-item d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                                    <div class="item-image" style="width: 60px; height: 60px;">
                                        <img src="<?= htmlspecialchars(getProductImageUrl($item)) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="w-100 h-100 object-fit-cover rounded-3">
                                    </div>
                                    <div class="item-details flex-grow-1">
                                        <h3 class="h6 fw-semibold mb-1"><?= htmlspecialchars($item['name']) ?></h3>
                                        <p class="text-muted small mb-0">Qty: <?= $item['quantity'] ?></p>
                                    </div>
                                    <div class="item-price fw-semibold">
                                        ₱<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-totals">
                            <div class="total-row d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold">₱<?= number_format($subtotal, 2) ?></span>
                            </div>
                            
                            <div class="total-row d-flex justify-content-between mb-3">
                                <span class="text-muted">Shipping:</span>
                                <span class="fw-semibold">₱<?= number_format($shipping, 2) ?></span>
                            </div>
                            
                            <div class="total-row d-flex justify-content-between mb-4 pt-3 border-top">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-5 text-primary">₱<?= number_format($total, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.checkout-section {
    background-color: var(--light-bg);
}

.step {
    color: var(--text-muted);
    font-weight: 500;
}

.step.active {
    color: var(--primary-color);
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.step.active .step-number {
    background-color: var(--primary-color);
    color: white;
}

.btn-check:checked + .btn-outline-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.25rem rgba(var(--primary-rgb), 0.25);
}

.invalid-feedback {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>

<?php include 'includes/footer.php'; ?>