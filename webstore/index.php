<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Get featured products
$featuredProducts = getFeaturedProducts(6);

// Get new arrivals
$newArrivals = getNewArrivals(6);

include 'includes/header.php';
?>

<div class="main-rounded-container">
  <!-- Hero Section -->
  <section class="shoply-hero text-center mb-4">
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 3rem;">
      <div class="shoply-hero-text" style="flex: 1 1 320px; min-width: 280px;">
        <h1 class="shoply-hero-title" style="font-size: 3.2rem; font-weight: 900; margin-bottom: 1.2rem;">Let's Shop All-In-One</h1>
        <div class="shoply-hero-subtitle" style="font-size: 1.4rem; color: var(--text-light); margin-bottom: 2.2rem;">Visit Collectibles And Follow Your Passion.</div>
      </div>
      <div class="shoply-hero-img" style="flex: 0 0 380px; max-width: 380px; border-radius: 2.5rem; overflow: hidden; box-shadow: 0 8px 32px 0 rgba(31,38,135,0.10); background: #fff;">
        <img src="https://images.vexels.com/media/users/3/192431/isolated/preview/7f22ea54db73af152e941f40dd72853c-winter-online-shopping-illustration.png" alt="Hero" style="width: 100%; height: auto; display: block; border-radius: 2.5rem;">
      </div>
    </div>
  </section>

  <!-- Cards Row -->
  <section class="shoply-cards-row mb-4 text-center">
    <div style="display: flex; gap: 2rem; justify-content: center; flex-wrap: wrap;">
      <a href="shop.php?recent=1" class="shoply-card" style="text-decoration:none; min-width: 180px; text-align: center;">Recent</a>
      <a href="shop.php?sort=popular" class="shoply-card" style="text-decoration:none; min-width: 180px; text-align: center;">Popular Items <span>&rarr;</span></a>
      <a href="shop.php?special=1" class="shoply-card" style="text-decoration:none; min-width: 180px; text-align: center;">Special Offers For You <span>&rarr;</span></a>
      <a href="shop.php" class="shoply-card cta" style="text-decoration:none; min-width: 180px; text-align: center;">Show All <span>&rarr;</span></a>
    </div>
  </section>

  <!-- Featured Products -->
  <section class="featured-products mb-4">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="product-grid">
      <?php foreach ($featuredProducts as $product): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- New Arrivals -->
  <section class="new-arrivals">
    <h2 class="text-center mb-4">New Arrivals</h2>
    <div class="product-grid">
      <?php foreach ($newArrivals as $product): ?>
        <?php include 'includes/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<!-- Floating Help Button -->
<a href="contact.php" class="floating-help-btn" title="Need Help? Contact Us">
  <i class="fas fa-comments"></i>
  <span class="help-text">Help</span>
</a>

<style>
.floating-help-btn {
  position: fixed;
  bottom: 40px;
  right: 40px;
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  color: white;
  border-radius: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
  transition: all 0.3s ease;
  z-index: 1000;
  padding: 12px 24px;
  gap: 8px;
  font-weight: 500;
}

.floating-help-btn i {
  font-size: 20px;
}

.floating-help-btn .help-text {
  font-size: 16px;
  display: none;
}

.floating-help-btn:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
  color: white;
  padding-right: 30px;
}

.floating-help-btn:hover .help-text {
  display: inline;
}

@media (max-width: 768px) {
  .floating-help-btn {
    bottom: 25px;
    right: 25px;
    padding: 10px;
    border-radius: 50%;
  }
  
  .floating-help-btn:hover {
    padding: 10px;
  }
  
  .floating-help-btn .help-text {
    display: none !important;
  }
  
  .floating-help-btn i {
    font-size: 18px;
  }
}
</style>

<?php include 'includes/footer.php'; ?>