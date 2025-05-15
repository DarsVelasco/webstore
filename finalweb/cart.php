<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php?redirect=cart");
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'update':
                    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
                        foreach ($_POST['quantity'] as $cartItemId => $quantity) {
                            $quantity = (int)$quantity;
                            if ($quantity > 0) {
                                // Get current cart item to check stock
                                $cartItem = getCartItem($cartItemId);
                                if ($cartItem && $cartItem['stock'] >= $quantity) {
                                    updateCartItem($cartItemId, $quantity);
                                } else {
                                    $message = "Some items couldn't be updated due to stock limitations.";
                                    $messageType = 'warning';
                                }
                            }
                        }
                        if (empty($message)) {
                            $message = 'Cart updated successfully.';
                            $messageType = 'success';
                        }
                    }
                    break;

                case 'remove':
                    if (isset($_POST['cart_item_id'])) {
                        removeCartItem($_POST['cart_item_id']);
                        $message = 'Item removed from cart.';
                        $messageType = 'success';
                    }
                    break;

                case 'add':
                    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                        $productId = (int)$_POST['product_id'];
                        $quantity = (int)$_POST['quantity'];
                        
                        // Validate quantity
                        if ($quantity <= 0) {
                            throw new Exception('Invalid quantity specified.');
                        }
                        
                        // Check product stock
                        $product = getProductById($productId);
                        if (!$product) {
                            throw new Exception('Product not found.');
                        }
                        
                        if ($product['stock'] < $quantity) {
                            throw new Exception('Not enough stock available.');
                        }
                        
                        // Add to cart
                        if (addToCart($userId, $productId, $quantity)) {
                            $message = 'Item added to cart successfully.';
                            $messageType = 'success';
                        } else {
                            throw new Exception('Failed to add item to cart.');
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get cart items
$cartItems = getCartItems($userId);
$subtotal = calculateCartSubtotal($userId);

// Set page title
$pageTitle = "Shopping Cart - " . SITE_NAME;

include 'includes/header.php';
?>

<!-- Add cart-specific CSS -->
<link rel="stylesheet" href="css/cart.css">

<!-- Add CSS for messages -->
<style>
.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 4px;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}
.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>

<section class="cart-section">
    <div class="container">
        <h1>Your Shopping Cart</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <div class="empty-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="post" action="cart.php" id="cartForm">
                <input type="hidden" name="action" value="update">
                
                <div class="cart-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td class="product-info">
                                        <div class="product-image">
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        </div>
                                        <div class="product-details">
                                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                                            <p>SKU: <?= $item['product_id'] ?></p>
                                        </div>
                                    </td>
                                    <td class="product-price">
                                        $<?= number_format($item['price'], 2) ?>
                                    </td>
                                    <td class="product-quantity">
                                        <input type="number" name="quantity[<?= $item['cart_item_id'] ?>]" 
                                               value="<?= $item['quantity'] ?>" 
                                               min="1" 
                                               max="<?= $item['stock'] ?>"
                                               class="quantity-input"
                                               data-price="<?= $item['price'] ?>"
                                               onchange="updateItemTotal(this)">
                                        <?php if ($item['stock'] < 5): ?>
                                            <small class="text-danger">Only <?= $item['stock'] ?> left in stock</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="product-total">
                                        $<span class="item-total"><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </td>
                                    <td class="product-remove">
                                        <form method="post" action="cart.php" class="remove-form" onsubmit="return confirm('Are you sure you want to remove this item?');">
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="cart-actions">
                    <div class="continue-shopping">
                        <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                    <div class="update-cart">
                        <button type="submit" class="btn btn-primary">Update Cart</button>
                    </div>
                </div>
            </form>
            
            <div class="cart-summary">
                <div class="summary-card">
                    <h2>Cart Summary</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>$<span id="subtotal"><?= number_format($subtotal, 2) ?></span></span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Calculated at checkout</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Estimated Total:</span>
                        <span>$<span id="total"><?= number_format($subtotal, 2) ?></span></span>
                    </div>
                    
                    <div class="checkout-btn">
                        <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Add JavaScript for real-time total updates -->
<script>
function updateItemTotal(input) {
    const quantity = parseInt(input.value);
    const price = parseFloat(input.dataset.price);
    const totalElement = input.closest('tr').querySelector('.item-total');
    
    if (quantity > 0) {
        const total = (quantity * price).toFixed(2);
        totalElement.textContent = total;
        updateCartTotals();
    }
}

function updateCartTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-total').forEach(element => {
        subtotal += parseFloat(element.textContent);
    });
    
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('total').textContent = subtotal.toFixed(2);
}

// Add form validation
document.getElementById('cartForm').addEventListener('submit', function(e) {
    const quantities = this.querySelectorAll('.quantity-input');
    let valid = true;
    
    quantities.forEach(input => {
        const quantity = parseInt(input.value);
        const max = parseInt(input.getAttribute('max'));
        
        if (quantity < 1 || quantity > max) {
            valid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Please check the quantities. They must be between 1 and the available stock.');
    }
});
</script>

<?php include 'includes/footer.php'; ?>