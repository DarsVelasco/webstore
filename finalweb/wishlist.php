<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$wishlistItems = getWishlistItems($userId);

// Handle AJAX requests for adding/removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false];

    if ($_POST['action'] === 'remove' && isset($_POST['product_id'])) {
        $productId = $_POST['product_id'];
        if (removeFromWishlist($userId, $productId)) {
            $response['success'] = true;
        }
    } elseif ($_POST['action'] === 'add' && isset($_POST['product_id'])) {
        $productId = $_POST['product_id'];
        if (addToWishlist($userId, $productId)) {
            $response['success'] = true;
        }
    }

    echo json_encode($response);
    exit();
}

// Get user information
$user = getUserById($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .wishlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .wishlist-item {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            position: relative;
        }

        .wishlist-item:hover {
            transform: translateY(-5px);
        }

        .wishlist-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .wishlist-item-content {
            padding: 15px;
        }

        .wishlist-item-title {
            font-size: 1.1em;
            margin: 0 0 10px 0;
            color: #2c3e50;
        }

        .wishlist-item-price {
            font-size: 1.2em;
            color: #4a90e2;
            font-weight: bold;
            margin: 10px 0;
        }

        .wishlist-item-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .remove-wishlist {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .remove-wishlist:hover {
            background: #ff4444;
            color: white;
        }

        .empty-wishlist {
            text-align: center;
            padding: 50px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="wishlist-container">
        <div class="wishlist-header">
            <h1>My Wishlist</h1>
            <span><?php echo count($wishlistItems); ?> items</span>
        </div>

        <?php if (empty($wishlistItems)): ?>
            <div class="empty-wishlist">
                <h2>Your wishlist is empty</h2>
                <p>Browse our products and add items to your wishlist!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): ?>
                    <div class="wishlist-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <img src="<?php echo htmlspecialchars(getProductImageUrl($item)); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <button class="remove-wishlist" title="Remove from wishlist">×</button>
                        <div class="wishlist-item-content">
                            <h3 class="wishlist-item-title"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="wishlist-item-price">$<?php echo number_format($item['price'], 2); ?></div>
                            <div class="wishlist-item-actions">
                                <a href="products.php?id=<?php echo $item['product_id']; ?>" class="btn btn-secondary">View Details</a>
                                <button class="btn btn-primary add-to-cart" data-product-id="<?php echo $item['product_id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle remove from wishlist
            document.querySelectorAll('.remove-wishlist').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.closest('.wishlist-item').dataset.productId;
                    removeFromWishlist(productId);
                });
            });

            // Handle add to cart
            document.querySelectorAll('.add-to-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.productId;
                    addToCart(productId);
                });
            });

            function removeFromWishlist(productId) {
                fetch('wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const item = document.querySelector(`.wishlist-item[data-product-id="${productId}"]`);
                        item.remove();
                        
                        // Update count
                        const count = document.querySelectorAll('.wishlist-item').length;
                        document.querySelector('.wishlist-header span').textContent = `${count} items`;
                        
                        // Show empty state if no items left
                        if (count === 0) {
                            location.reload();
                        }
                    }
                });
            }

            function addToCart(productId) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&product_id=${productId}&quantity=1`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Product added to cart successfully!');
                    } else {
                        alert(data.message || 'Error adding product to cart');
                    }
                });
            }
        });
    </script>
</body>
</html> 