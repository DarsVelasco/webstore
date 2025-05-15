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
$validSortOptions = ['newest', 'price_asc', 'price_desc', 'name_asc', 'name_desc'];
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

<!-- Shop Header with Filters -->
<section class="shop-header">
    <div class="container">
        <h1>Our Products</h1>
        <form method="get" class="shop-filters">
            <div class="filter-group">
                <label for="category">Category:</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= ($categoryFilter == $category['category_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="sort">Sort By:</label>
                <select name="sort" id="sort" class="form-select">
                    <option value="newest" <?= ($sortBy == 'newest') ? 'selected' : '' ?>>Newest First</option>
                    <option value="price_asc" <?= ($sortBy == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= ($sortBy == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="name_asc" <?= ($sortBy == 'name_asc') ? 'selected' : '' ?>>Name: A-Z</option>
                    <option value="name_desc" <?= ($sortBy == 'name_desc') ? 'selected' : '' ?>>Name: Z-A</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Search:</label>
                <div class="input-group">
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Product Grid -->
<section class="product-listing">
    <div class="container">
        <?php if (empty($products)): ?>
            <p class="no-results">No products found matching your criteria.</p>
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
    </div>
</section>

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
