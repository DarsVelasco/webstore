<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get all products
$products = getAllProducts();

include 'includes/header.php';
?>

<h1>Products</h1>

<div class="content-actions">
    <a href="product-form.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['product_id'] ?></td>
                    <td>
                        <img src="../<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="product-thumbnail"
                             onerror="this.onerror=null; this.src='data:image/svg+xml;base64,<?= base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50"><rect width="50" height="50" fill="#f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="8" fill="#999" text-anchor="middle" dy=".3em">No Image</text></svg>') ?>';">
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                    <td>₱<?= number_format($product['price'], 2) ?></td>
                    <td><?= $product['stock'] ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="product-form.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger delete-product" data-id="<?= $product['product_id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this product?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
.content-actions {
    margin-bottom: 20px;
}

.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.table-responsive {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group {
    display: flex;
    gap: 5px;
}

/* Enhanced button styling */
.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white !important;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white !important;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

/* Make buttons more visible */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
    opacity: 1 !important;
}

.btn i {
    font-size: 0.875rem;
    vertical-align: middle;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let productToDelete = null;

    // Handle delete button clicks
    document.querySelectorAll('.delete-product').forEach(button => {
        button.addEventListener('click', function() {
            productToDelete = this.dataset.id;
            deleteModal.show();
        });
    });

    // Handle confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (productToDelete) {
            fetch(`delete-product.php?id=${productToDelete}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the row from the table
                    const row = document.querySelector(`button[data-id="${productToDelete}"]`).closest('tr');
                    row.remove();
                    
                    // Show success message
                    alert('Product deleted successfully');
                } else {
                    alert('Error deleting product');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting product');
            })
            .finally(() => {
                deleteModal.hide();
                productToDelete = null;
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>