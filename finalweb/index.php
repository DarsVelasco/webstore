<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Get featured products
$featuredProducts = getFeaturedProducts(6);

// Get new arrivals
$newArrivals = getNewArrivals(6);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Our E-Commerce Store</h1>
        <p>Discover amazing products at great prices</p>
        <a href="shop.php" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<!-- Featured Products -->
<section class="featured-products">
    <div class="container">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Arrivals -->
<section class="new-arrivals">
    <div class="container">
        <h2>New Arrivals</h2>
        <div class="product-grid">
            <?php foreach ($newArrivals as $product): ?>
                <?php include 'includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>