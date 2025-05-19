<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please login to manage your wishlist']);
        exit();
    }
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle AJAX requests for adding/removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    try {
        if (!isset($_POST['product_id'])) {
            throw new Exception('Product ID is required');
        }

        $productId = (int)$_POST['product_id'];
        
        // Verify product exists
        $product = getProductById($productId);
        if (!$product) {
            throw new Exception('Product not found');
        }

        switch ($_POST['action']) {
            case 'remove':
                if (removeFromWishlist($userId, $productId)) {
                    $response['success'] = true;
                    $response['message'] = 'Product removed from wishlist';
                } else {
                    throw new Exception('Failed to remove product from wishlist');
                }
                break;

            case 'add':
                if (addToWishlist($userId, $productId)) {
                    $response['success'] = true;
                    $response['message'] = 'Product added to wishlist';
                } else {
                    throw new Exception('Product is already in your wishlist');
                }
                break;
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    echo json_encode($response);
    exit();
}

// Get wishlist items
$wishlistItems = getWishlistItems($userId);

// Set page title
$pageTitle = "My Wishlist - " . SITE_NAME;

include 'includes/header.php';
?>

<div class="main-rounded-container">
    <section class="py-5">
        <div class="container">
            <div class="wishlist-header mb-4">
                <h1 class="display-6 fw-bold mb-1">My Wishlist</h1>
                <p class="text-muted">Manage your saved items and add them to cart when you're ready to purchase.</p>
            </div>

            <?php if (empty($wishlistItems)): ?>
                <div class="text-center py-5">
                    <div class="empty-wishlist mb-4">
                        <i class="fas fa-heart fa-3x text-muted"></i>
                    </div>
                    <h3 class="h4 mb-3">Your wishlist is empty</h3>
                    <p class="text-muted mb-4">Start adding items to your wishlist to save them for later.</p>
                    <a href="shop.php" class="btn btn-primary">Browse Products</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($wishlistItems as $item): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm rounded-4 border-0">
                                <div class="position-relative">
                                    <img src="<?php echo htmlspecialchars(getProductImageUrl($item)); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                         class="card-img-top rounded-top-4"
                                         style="height: 200px; object-fit: cover;">
                                    <button type="button" 
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-3 rounded-circle remove-wishlist"
                                            data-product-id="<?php echo $item['product_id']; ?>"
                                            title="Remove from wishlist">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title mb-2"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <div class="text-primary fw-bold mb-3">₱<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="d-flex gap-2">
                                        <button type="button" 
                                                class="btn btn-primary flex-grow-1 add-to-cart"
                                                data-product-id="<?php echo $item['product_id']; ?>"
                                                <?php echo $item['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            <?php echo $item['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true" id="wishlistToast">
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-heart me-2"></i>
                <span id="wishlistToastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
.empty-wishlist {
    color: #e9ecef;
    margin-bottom: 1.5rem;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.remove-wishlist {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.8;
}

.remove-wishlist:hover {
    opacity: 1;
}

.toast {
    min-width: 300px;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast.bg-primary {
    background-color: #4a90e2 !important;
}

.toast-body {
    font-size: 0.95rem;
    font-weight: 500;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wishlistToast = new bootstrap.Toast(document.getElementById('wishlistToast'));
    const toastMessage = document.getElementById('wishlistToastMessage');

    // Handle remove from wishlist
    document.querySelectorAll('.remove-wishlist').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the card from the view
                    const card = this.closest('.col-12');
                    card.remove();
                    
                    // Show success message
                    toastMessage.textContent = 'Product removed from wishlist';
                    wishlistToast.show();
                    
                    // If no items left, reload the page to show empty state
                    if (document.querySelectorAll('.col-12').length === 0) {
                        location.reload();
                    }
                } else {
                    toastMessage.textContent = data.message || 'Error removing from wishlist';
                    wishlistToast.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastMessage.textContent = 'An error occurred while updating the wishlist';
                wishlistToast.show();
            });
        });
    });

    // Handle add to cart
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `action=add&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count in header
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        const currentCount = parseInt(cartBadge.textContent) || 0;
                        cartBadge.textContent = currentCount + 1;
                    }
                    
                    // Show success message
                    toastMessage.textContent = 'Product added to cart successfully!';
                    wishlistToast.show();
                } else {
                    toastMessage.textContent = data.message || 'Error adding product to cart';
                    wishlistToast.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastMessage.textContent = 'An error occurred while adding the product to cart';
                wishlistToast.show();
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 