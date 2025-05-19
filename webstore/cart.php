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
            $response = ['success' => false, 'message' => ''];
            
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
                                    $response['message'] = "Some items couldn't be updated due to stock limitations.";
                                }
                            }
                        }
                        if (empty($response['message'])) {
                            $response['success'] = true;
                            $response['message'] = 'Cart updated successfully.';
                        }
                    }
                    break;

                case 'remove':
                    if (isset($_POST['cart_item_id'])) {
                        if (removeCartItem($_POST['cart_item_id'])) {
                            $response['success'] = true;
                            $response['message'] = 'Item removed from cart.';
                        } else {
                            $response['message'] = 'Failed to remove item from cart.';
                        }
                    }
                    break;

                case 'add':
                    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
                        $productId = (int)$_POST['product_id'];
                        $quantity = (int)$_POST['quantity'];
                        
                        // Validate quantity
                        if ($quantity <= 0) {
                            throw new Exception('Please enter a valid quantity.');
                        }
                        
                        // Check product stock
                        $product = getProductById($productId);
                        if (!$product) {
                            throw new Exception('Product not found.');
                        }
                        
                        if ($product['stock'] < $quantity) {
                            throw new Exception('Not enough stock available. Only ' . $product['stock'] . ' items left.');
                        }
                        
                        // Add to cart
                        if (addToCart($userId, $productId, $quantity)) {
                            $response['success'] = true;
                            $response['message'] = 'Item added to cart successfully.';
                        } else {
                            throw new Exception('Failed to add item to cart.');
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        
        // If it's an AJAX request, return JSON response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
        
        // For regular form submissions, set message variables
        $message = $response['message'];
        $messageType = $response['success'] ? 'success' : 'error';
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

<div class="main-rounded-container">
  <section class="text-center mb-4">
    <h1 class="mb-4">Your Cart</h1>
    <?php if (empty($cartItems)): ?>
      <div class="alert alert-info">Your cart is empty. <a href="shop.php" class="alert-link">Shop now</a>!</div>
    <?php else: ?>
      <div class="container">
        <div class="row g-4">
          <div class="col-12 col-lg-8">
            <div class="card shadow-sm rounded-4 mb-4">
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Total</th>
                        <th scope="col"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($cartItems as $item): ?>
                        <tr>
                          <td class="d-flex align-items-center gap-3">
                            <img src="<?php echo htmlspecialchars(getProductImageUrl($item)); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.7rem;">
                            <div>
                              <div class="fw-semibold"><?php echo htmlspecialchars($item['name']); ?></div>
                              <div class="text-muted small"><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></div>
                            </div>
                          </td>
                          <td class="fw-semibold">₱<?php echo number_format($item['price'], 2); ?></td>
                          <td>
                            <form action="cart.php" method="post" class="d-flex align-items-center gap-2">
                              <input type="number" name="quantity[<?php echo $item['cart_item_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control form-control-sm" style="width: 70px;">
                              <button type="submit" name="action" value="update" class="btn btn-outline-primary btn-sm">Update</button>
                            </form>
                          </td>
                          <td class="fw-semibold">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                          <td>
                            <form action="cart.php" method="post" class="remove-cart-item-form">
                              <input type="hidden" name="cart_item_id" value="<?php echo $item['cart_item_id']; ?>">
                              <button type="submit" name="action" value="remove" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                            </form>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 col-lg-4">
            <div class="card shadow-sm rounded-4 mb-4">
              <div class="card-body">
                <h4 class="mb-3">Summary</h4>
                <div class="d-flex justify-content-between mb-2">
                  <span>Subtotal</span>
                  <span class="fw-semibold" id="cart-subtotal">₱<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <span>Shipping</span>
                  <span class="fw-semibold" id="cart-shipping">₱125.00</span>
                </div>
                <div class="d-flex justify-content-between mb-4 fs-5">
                  <span class="fw-bold">Total</span>
                  <span class="fw-bold text-primary" id="cart-total">₱<?php echo number_format($subtotal + 125.00, 2); ?></span>
                </div>
                <a href="checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </section>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" id="cartRemoveToast">
    <div class="d-flex">
      <div class="toast-body">
        <i class="fa fa-trash me-2"></i>
        <span id="cartRemoveToastMessage">Item removed from cart.</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const cartRemoveToast = new bootstrap.Toast(document.getElementById('cartRemoveToast'));
  const cartRemoveToastMessage = document.getElementById('cartRemoveToastMessage');

  function recalculateCartTotals() {
    let subtotal = 0;
    document.querySelectorAll('tbody tr').forEach(row => {
      const totalCell = row.querySelector('td.fw-semibold:last-child');
      if (totalCell) {
        const totalText = totalCell.textContent.replace(/[^\d.]/g, '');
        subtotal += parseFloat(totalText) || 0;
      }
    });
    const shipping = 125.00;
    const total = subtotal > 0 ? subtotal + shipping : 0;
    document.getElementById('cart-subtotal').textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById('cart-total').textContent = `₱${total.toFixed(2)}`;
  }

  document.querySelectorAll('.remove-cart-item-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('action', 'remove');
      fetch('cart.php', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove the row from the table
          const row = this.closest('tr');
          row.remove();
          // Update totals
          recalculateCartTotals();
          // Show toast
          cartRemoveToastMessage.textContent = data.message || 'Item removed from cart.';
          cartRemoveToast.show();
          // If cart is empty, reload to show empty state
          if (document.querySelectorAll('tbody tr').length === 0) {
            location.reload();
          }
        } else {
          cartRemoveToastMessage.textContent = data.message || 'Failed to remove item from cart.';
          cartRemoveToast.show();
        }
      })
      .catch(error => {
        cartRemoveToastMessage.textContent = 'An error occurred while removing the item.';
        cartRemoveToast.show();
      });
    });
  });
});
</script>

<?php include 'includes/footer.php'; ?>