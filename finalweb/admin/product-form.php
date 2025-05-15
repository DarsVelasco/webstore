<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

$categories = getAllCategories();
$product = null;
$errors = [];
$success = false;

// Handle edit mode
if (isset($_GET['id'])) {
    $product = getProductById($_GET['id']);
    if (!$product) {
        header("Location: products.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $categoryId = intval($_POST['category_id']);
    
    // Validate inputs
    if (empty($name)) $errors[] = "Product name is required";
    if (empty($description)) $errors[] = "Description is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    if ($stock < 0) $errors[] = "Stock cannot be negative";
    if ($categoryId <= 0) $errors[] = "Please select a category";
    
    // Handle image upload
    $imagePath = isset($product['image']) ? $product['image'] : '';
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = uploadProductImage($_FILES['image']);
        if ($uploadResult['success']) {
            // Delete old image if exists
            if (!empty($imagePath)) {
                deleteProductImage($imagePath);
            }
            $imagePath = $uploadResult['path'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        if (isset($_GET['id'])) {
            // Update existing product
            $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, 
                                  stock = ?, category_id = ?, image = ? WHERE product_id = ?");
            $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $categoryId, 
                            $imagePath, $_GET['id']);
        } else {
            // Insert new product
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, 
                                  category_id, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $categoryId, 
                            $imagePath);
        }
        
        if ($stmt->execute()) {
            $success = true;
            if (!isset($_GET['id'])) {
                // Clear form after successful insert
                $product = null;
            } else {
                // Refresh product data after update
                $product = getProductById($_GET['id']);
            }
        } else {
            $errors[] = "Error saving product: " . $stmt->error;
        }
    }
}

include 'includes/header.php';
?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="page-header">
            <h1><?= isset($_GET['id']) ? 'Edit' : 'Add' ?> Product</h1>
            <a href="products.php" class="btn btn-secondary">Back to Products</a>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                Product has been successfully <?= isset($_GET['id']) ? 'updated' : 'added' ?>!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required 
                       value="<?= htmlspecialchars($product['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required rows="5"
                ><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required 
                           value="<?= htmlspecialchars($product['price'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" required 
                           value="<?= htmlspecialchars($product['stock'] ?? '0') ?>">
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>" 
                                <?= (isset($product['category_id']) && $product['category_id'] == $category['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <?php if (!empty($product['image'])): ?>
                    <div class="current-image">
                        <img src="../<?= htmlspecialchars($product['image']) ?>" 
                             alt="Current product image"
                             onerror="this.src='../uploads/products/default-product.jpg'">
                        <p>Current image</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <p class="help-text">Supported formats: JPG, JPEG, PNG, GIF, WebP. Max size: 5MB</p>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= isset($_GET['id']) ? 'Update' : 'Add' ?> Product
                </button>
                <a href="products.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
</div>

<style>
.product-form {
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.2s ease;
}

.form-group input[type="text"]:focus,
.form-group input[type="number"]:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #4a90e2;
    outline: none;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.current-image {
    margin: 10px 0;
    text-align: center;
}

.current-image img {
    max-width: 200px;
    height: auto;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.current-image p {
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}

.help-text {
    margin: 5px 0;
    color: #666;
    font-size: 12px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #4a90e2;
    color: white;
}

.btn-primary:hover {
    background: #357abd;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 0;
    padding-left: 20px;
}

@media (max-width: 768px) {
    .product-form {
        margin: 10px;
        padding: 15px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?> 