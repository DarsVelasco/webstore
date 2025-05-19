<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Set page title for header
$pageTitle = "Shop - " . SITE_NAME;

// Get categories for filter
$categories = getAllCategories();
error_log("Categories found: " . count($categories));

// Get current page and items per page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = 12;

// Get filter parameters safely
$categoryFilter = isset($_GET['category']) ? (int)$_GET['category'] : null;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Validate sort parameter
$validSortOptions = ['newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc', 'popular'];
if (!in_array($sortBy, $validSortOptions)) {
    $sortBy = 'newest';
}

// Get filtered products
$products = getFilteredProducts($categoryFilter, $searchQuery, $sortBy, $currentPage, $itemsPerPage);
error_log("Products found after filtering: " . count($products));

$totalProducts = getTotalFilteredProducts($categoryFilter, $searchQuery);
error_log("Total filtered products: " . $totalProducts);

// Include header
include 'includes/header.php';
?>

<!-- Add shop-specific CSS -->
<link rel="stylesheet" href="css/shop.css">

<div class="main-rounded-container">
  <section class="shop-header text-center mb-4">
    <h1 class="mb-4">Our Products</h1>
    <form method="get" class="shop-filters mb-4">
      <div class="card shadow-sm p-3 mb-4 border-0 rounded-4">
        <div class="row g-3 align-items-end justify-content-center">
          <div class="col-md-3 col-12">
            <label for="category" class="form-label fw-semibold">Category</label>
            <select name="category" id="category" class="form-select rounded-3">
              <option value="">All Categories</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>" <?= ($categoryFilter == $category['category_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3 col-12">
            <label for="sort" class="form-label fw-semibold">Sort By</label>
            <select name="sort" id="sort" class="form-select rounded-3">
              <option value="newest" <?= ($sortBy == 'newest') ? 'selected' : '' ?>>Newest First</option>
              <option value="price_asc" <?= ($sortBy == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
              <option value="price_desc" <?= ($sortBy == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
              <option value="name_asc" <?= ($sortBy == 'name_asc') ? 'selected' : '' ?>>Name: A-Z</option>
              <option value="name_desc" <?= ($sortBy == 'name_desc') ? 'selected' : '' ?>>Name: Z-A</option>
              <option value="popular" <?= ($sortBy == 'popular') ? 'selected' : '' ?>>Most Popular</option>
            </select>
          </div>
          <div class="col-md-3 col-12">
            <label for="search" class="form-label fw-semibold">Search</label>
            <div class="search-wrapper">
              <input type="text" name="search" id="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($searchQuery ?? '') ?>">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </section>

  <section class="product-listing">
    <?php if (empty($products)): ?>
      <p class="no-results text-center">No products found matching your criteria.</p>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach ($products as $product): ?>
          <?php include 'includes/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <!-- Pagination -->
      <?php if ($totalProducts > $itemsPerPage): ?>
        <div class="pagination">
          <?php
          $totalPages = ceil($totalProducts / $itemsPerPage);
          for ($i = 1; $i <= $totalPages; $i++):
            $queryParams = $_GET;
            $queryParams['page'] = $i;
            $url = 'shop.php?' . http_build_query($queryParams);
            ?>
            <a href="<?= $url ?>" class="<?= ($i == $currentPage) ? 'active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</div>

<style>
.shop-header .card {
  background: #fff;
  border-radius: 1.5rem;
  box-shadow: 0 2px 16px rgba(0,0,0,0.06);
}
.shop-header .form-label {
  font-weight: 600;
  color: #333;
  margin-bottom: 0.5rem;
  display: block;
}
.shop-header .form-select, 
.shop-header .form-control {
  height: 48px;
  font-size: 1rem;
  border-radius: 0.7rem;
  width: 100%;
}
.shop-header .search-wrapper {
  position: relative;
  width: 100%;
}
.shop-header .search-wrapper .btn {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 48px;
  padding: 0;
  border-radius: 0 0.7rem 0.7rem 0;
}
.shop-header .search-wrapper .btn i {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 16px;
}
.shop-header .search-wrapper .form-control {
  padding-right: 48px;
}
@media (max-width: 767px) {
  .shop-header .row.g-3 > [class^='col-'] {
    margin-bottom: 1rem;
  }
}
</style>

<!-- Add JavaScript for instant filtering -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const filterForm = document.querySelector('.shop-filters');
    const categorySelect = document.getElementById('category');
    const sortSelect = document.getElementById('sort');
    
    // Add change event listeners
    categorySelect.addEventListener('change', function() {
        filterForm.submit();
    });
    
    sortSelect.addEventListener('change', function() {
        filterForm.submit();
    });
});
</script>

<?php include 'includes/footer.php'; ?>
