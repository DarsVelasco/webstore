<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

if (!isset($_GET['id'])) {
    header("Location: shop.php");
    exit();
}

$productId = (int)$_GET['id'];
$product = getProductById($productId);

if (!$product) {
    header("Location: shop.php");
    exit();
}

// Get related products
$relatedProducts = getRelatedProducts($product['category_id'], $productId, 4);

// Get product reviews
$reviews = getProductReviews($productId);

// Calculate average rating
$averageRating = calculateAverageRating($productId);

include 'includes/header.php';
?>

<style>
.product-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.product-images {
    position: relative;
}

.main-image {
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
    background: #f8f9fa;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.main-image:hover img {
    transform: scale(1.05);
}

.product-info {
    padding: 1rem;
}

.product-price {
    font-size: 1.5rem;
    color: #4a90e2;
    font-weight: bold;
    margin: 1rem 0;
}

.product-stock {
    margin: 1rem 0;
}

.product-stock .in-stock {
    color: #28a745;
}

.product-stock .out-of-stock {
    color: #dc3545;
}

.product-description {
    margin: 1.5rem 0;
}

.product-description h3 {
    margin-bottom: 0.5rem;
}

.quantity-selector {
    margin: 1rem 0;
}

.quantity-selector input {
    width: 80px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Product Details Section -->
<section class="product-details">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Product Images -->
            <div class="product-images">
                <div class="main-image">
                    <img src="<?= htmlspecialchars(getProductImageUrl($product)) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         onerror="this.src='uploads/products/default-product.jpg'">
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                
                <!-- Rating -->
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= ($i <= $averageRating) ? 'filled' : '' ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count">(<?= count($reviews) ?> reviews)</span>
                </div>
                
                <!-- Price -->
                <div class="product-price">
                    $<?= number_format($product['price'], 2) ?>
                </div>
                
                <!-- Stock Status -->
                <div class="product-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock">In Stock (<?= $product['stock'] ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                
                <!-- Description -->
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                
                <!-- Add to Cart Form -->
                <?php if ($product['stock'] > 0): ?>
                <form action="cart.php" method="post" class="add-to-cart-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                    
                    <div class="quantity-selector">
                        <label for="quantity">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" min="1" max="<?= $product['stock'] ?>" value="1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Product Reviews Section -->
<section class="product-reviews">
    <div class="container">
        <h2>Customer Reviews</h2>
        
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to review this product!</p>
        <?php else: ?>
            <div class="review-list">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-author"><?= htmlspecialchars($review['full_name']) ?></div>
                            <div class="review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= ($i <= $review['rating']) ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <div class="review-date"><?= date('F j, Y', strtotime($review['review_date'])) ?></div>
                        </div>
                        <div class="review-comment">
                            <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Add Review Form (only for logged in users who purchased the product) -->
        <?php if (isLoggedIn() && hasPurchasedProduct($_SESSION['user_id'], $productId)): ?>
            <div class="add-review">
                <h3>Write a Review</h3>
                <form action="submit-review.php" method="post">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    
                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <select name="rating" id="rating" required>
                            <option value="">Select Rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">Review:</label>
                        <textarea name="comment" id="comment" rows="5" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        <?php elseif (!isLoggedIn()): ?>
            <p><a href="login.php">Login</a> to leave a review (only customers who purchased this product can review).</p>
        <?php endif; ?>
    </div>
</section>

<!-- Related Products -->
<section class="related-products">
    <div class="container">
        <h2>You May Also Like</h2>
        <div class="product-grid">
            <?php foreach ($relatedProducts as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>