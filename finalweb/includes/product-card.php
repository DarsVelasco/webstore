<!-- Product Card Component -->
<div class="product-card">
    <a href="product.php?id=<?php echo htmlspecialchars($product['product_id']); ?>" class="product-link">
        <div class="product-image">
            <img src="<?php echo htmlspecialchars(getProductImageUrl($product)); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                 onerror="this.src='uploads/products/default-product.jpg'">
            <?php if (isLoggedIn()): ?>
                <button type="button" 
                        class="wishlist-toggle <?php echo isInWishlist($_SESSION['user_id'], $product['product_id']) ? 'in-wishlist' : ''; ?>"
                        data-product-id="<?php echo $product['product_id']; ?>"
                        title="Add to Wishlist">
                    ♥
                </button>
            <?php endif; ?>
        </div>
        <div class="product-info">
            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
            <?php if (isset($product['category_name'])): ?>
                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
            <?php endif; ?>
            <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
            <div class="product-stock <?php echo ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                <?php echo ($product['stock'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
            </div>
        </div>
    </a>
    <div class="product-actions">
        <?php if ($product['stock'] > 0): ?>
            <form action="cart.php" method="post" class="add-to-cart-form">
                <div class="quantity-input">
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="form-control">
                </div>
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['product_id']); ?>">
                <input type="hidden" name="action" value="add">
                <button type="submit" class="btn btn-primary add-to-cart">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.product-image {
    position: relative;
    width: 100%;
    padding-top: 100%; /* 1:1 Aspect Ratio */
    overflow: hidden;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.wishlist-toggle {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    color: #ccc;
    transition: all 0.2s ease;
    z-index: 2;
}

.wishlist-toggle:hover {
    background: #fff;
    transform: scale(1.1);
}

.wishlist-toggle.in-wishlist {
    color: #ff4444;
}

.product-actions {
    padding: 10px;
}

.product-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    overflow: hidden;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.product-info {
    padding: 15px;
}

.product-title {
    font-size: 1.1em;
    margin: 0 0 10px 0;
    color: #2c3e50;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-category {
    color: #666;
    font-size: 0.9em;
    margin-bottom: 8px;
}

.product-price {
    font-size: 1.2em;
    color: #4a90e2;
    font-weight: bold;
    margin: 10px 0;
}

.product-stock {
    font-size: 0.9em;
    margin-top: 5px;
}

.product-stock.in-stock {
    color: #28a745;
}

.product-stock.out-of-stock {
    color: #dc3545;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const productId = this.dataset.productId;
            const isInWishlist = this.classList.contains('in-wishlist');
            
            fetch('wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=${isInWishlist ? 'remove' : 'add'}&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('in-wishlist');
                }
            });
        });
    });
});
</script>
