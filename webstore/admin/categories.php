<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get all categories
$categories = getAllCategories();

include 'includes/header.php';
?>

<h1>Categories</h1>

<div class="content-actions">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['category_id'] ?></td>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td><?= htmlspecialchars($category['description']) ?></td>
                    <td><?= $category['product_count'] ?></td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary edit-category" 
                                    data-id="<?= $category['category_id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                                    data-description="<?= htmlspecialchars($category['description']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-category" 
                                    data-id="<?= $category['category_id'] ?>"
                                    <?= $category['product_count'] > 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" name="id">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveCategory">Save</button>
            </div>
        </div>
    </div>
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
                Are you sure you want to delete this category?
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

/* Add styles for action buttons */
.btn-group .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.btn-group .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-group .btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-group .btn-danger:hover {
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.btn-group .btn[disabled] {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    let categoryToDelete = null;

    // Handle edit button clicks
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('categoryId').value = this.dataset.id;
            document.getElementById('categoryName').value = this.dataset.name;
            document.getElementById('categoryDescription').value = this.dataset.description;
            categoryModal.show();
        });
    });

    // Handle delete button clicks
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            if (!this.disabled) {
                categoryToDelete = this.dataset.id;
                deleteModal.show();
            }
        });
    });

    // Handle save category
    document.getElementById('saveCategory').addEventListener('click', function() {
        const form = document.getElementById('categoryForm');
        const formData = new FormData(form);

        fetch('save-category.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error saving category');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving category');
        })
        .finally(() => {
            categoryModal.hide();
        });
    });

    // Handle confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (categoryToDelete) {
            fetch(`delete-category.php?id=${categoryToDelete}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting category');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting category');
            })
            .finally(() => {
                deleteModal.hide();
                categoryToDelete = null;
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>