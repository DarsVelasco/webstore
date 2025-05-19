<?php
// Prevent function redeclaration
if (!function_exists('getStatusColor')) {
    function getStatusColor($status) {
        switch ($status) {
            case 'pending':
                return 'warning';
            case 'processing':
                return 'info';
            case 'shipped':
                return 'primary';
            case 'delivered':
                return 'success';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }
}

// Database connection
function getDBConnection() {
    global $conn;
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function authenticateUser($email, $password) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

// User functions
function registerUser($fullName, $email, $password, $phone, $address) {
    $conn = getDBConnection();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullName, $email, $hashedPassword, $phone, $address);
    return $stmt->execute();
}

function emailExists($email) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getUserById($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUserProfile($userId, $updateData) {
    $conn = getDBConnection();
    
    if (isset($updateData['password'])) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE user_id = ?");
        $stmt->bind_param("sssssi", $updateData['full_name'], $updateData['email'], $updateData['phone'], 
                          $updateData['address'], $updateData['password'], $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $updateData['full_name'], $updateData['email'], $updateData['phone'], 
                          $updateData['address'], $userId);
    }
    
    return $stmt->execute();
}

// Category functions
function getAllCategories() {
    $conn = getDBConnection();
    $sql = "SELECT c.*, COUNT(p.product_id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.category_id = p.category_id 
            GROUP BY c.category_id 
            ORDER BY c.name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCategoryById($categoryId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addCategory($name, $description) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    return $stmt->execute();
}

function updateCategory($categoryId, $name, $description) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
    $stmt->bind_param("ssi", $name, $description, $categoryId);
    return $stmt->execute();
}

function deleteCategory($categoryId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $categoryId);
    return $stmt->execute();
}

// Product functions
function getFeaturedProducts($limit = 6) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE p.stock > 0 ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getNewArrivals($limit = 6) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE p.stock > 0 ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getFilteredProducts($categoryId = null, $searchQuery = null, $sortBy = 'newest', $page = 1, $itemsPerPage = 12) {
    $conn = getDBConnection();
    $offset = ($page - 1) * $itemsPerPage;

    $sql = "SELECT p.*, c.name AS category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.category_id 
            WHERE p.stock > 0";

    $params = [];
    $types = "";

    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }

    if ($searchQuery) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    // Sorting
    switch ($sortBy) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'name_asc':
            $sql .= " ORDER BY p.name ASC";
            break;
        case 'name_desc':
            $sql .= " ORDER BY p.name DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }

    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $types .= "ii";

    // Debugging: Log the SQL query and parameters
    error_log("SQL Query: " . $sql);
    error_log("Parameters: " . json_encode($params));

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalFilteredProducts($categoryId = null, $searchQuery = null) {
    $conn = getDBConnection();
    
    $sql = "SELECT COUNT(*) AS total FROM products p WHERE p.stock > 0";
    $params = [];
    $types = "";
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
        $types .= "i";
    }
    
    if ($searchQuery) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = "%$searchQuery%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}

function getProductById($productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE p.product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getRelatedProducts($categoryId, $excludeProductId, $limit = 4) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           WHERE p.category_id = ? AND p.product_id != ? AND p.stock > 0 
                           ORDER BY RAND() LIMIT ?");
    $stmt->bind_param("iii", $categoryId, $excludeProductId, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllProducts($page = 1, $itemsPerPage = 10) {
    $conn = getDBConnection();
    $offset = ($page - 1) * $itemsPerPage;
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.category_id 
                           ORDER BY p.product_id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalProducts() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) AS total FROM products");
    return $result->fetch_assoc()['total'];
}

function updateProductStock($productId, $quantityChange) {
    $conn = getDBConnection();
    try {
        // First check if we have enough stock
        $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ? FOR UPDATE");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare error");
        }
        
        $stmt->bind_param("i", $productId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Database execute error");
        }
        
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($product['stock'] + $quantityChange < 0) {
            throw new Exception("Not enough stock available");
        }
        
        // Update the stock
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare error");
        }
        
        $stmt->bind_param("ii", $quantityChange, $productId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Database execute error");
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Update stock error: " . $e->getMessage());
        throw $e;
    }
}

function deleteProduct($productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    return $stmt->execute();
}

function getLowStockProducts($threshold = 5) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM products WHERE stock <= ? ORDER BY stock ASC");
    $stmt->bind_param("i", $threshold);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Cart functions
function getCartItems($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT ci.*, p.name, p.price, p.image, p.stock 
                           FROM cart_items ci 
                           JOIN products p ON ci.product_id = p.product_id 
                           WHERE ci.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function addToCart($userId, $productId, $quantity = 1) {
    $conn = getDBConnection();
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $existingItem = $stmt->get_result()->fetch_assoc();
    
    if ($existingItem) {
        // Replace quantity if already in cart
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
        $stmt->bind_param("ii", $quantity, $existingItem['cart_item_id']);
        return $stmt->execute();
    } else {
        // Add new item to cart
        $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        return $stmt->execute();
    }
}

function updateCartItem($cartItemId, $quantity) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $stmt->bind_param("ii", $quantity, $cartItemId);
    return $stmt->execute();
}

function removeCartItem($cartItemId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
    $stmt->bind_param("i", $cartItemId);
    return $stmt->execute();
}

function calculateCartSubtotal($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT SUM(ci.quantity * p.price) AS subtotal 
                           FROM cart_items ci 
                           JOIN products p ON ci.product_id = p.product_id 
                           WHERE ci.user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['subtotal'] ?? 0;
}

function clearCart($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

// Order functions
function getOrders($limit = 10, $offset = 0) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT o.*, u.full_name as customer_name 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           ORDER BY o.order_date DESC 
                           LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function createOrder($userId, $totalAmount, $shippingAddress, $paymentMethod) {
    $conn = getDBConnection();
    try {
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, address, payment_method) 
                               VALUES (?, ?, 'pending', ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare error");
        }
        
        $stmt->bind_param("idss", $userId, $totalAmount, $shippingAddress, $paymentMethod);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Database execute error");
        }
        
        $insertId = $conn->insert_id;
        if (!$insertId) {
            error_log("No insert ID returned");
            throw new Exception("No order ID generated");
        }
        
        return $insertId;
    } catch (Exception $e) {
        error_log("Create order error: " . $e->getMessage());
        throw $e;
    }
}

function addOrderItem($orderId, $productId, $quantity, $price) {
    $conn = getDBConnection();
    try {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Database prepare error");
        }
        
        $stmt->bind_param("iiid", $orderId, $productId, $quantity, $price);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Database execute error");
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Add order item error: " . $e->getMessage());
        throw $e;
    }
}

function getUserOrders($userId, $currentPage = 1, $itemsPerPage = 10) {
    $conn = getDBConnection();
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    $stmt = $conn->prepare("SELECT o.*, 
                                  o.order_date as created_at,
                                  o.total_amount as total,
                                  (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items 
                           FROM orders o 
                           WHERE o.user_id = ? 
                           ORDER BY o.order_date DESC 
                           LIMIT ? OFFSET ?");
    
    $stmt->bind_param("iii", $userId, $itemsPerPage, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserRecentOrders($userId, $limit = 3) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT ?");
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function countUserOrders($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function countUserOrdersByStatus($userId, $status) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM orders WHERE user_id = ? AND status = ?");
    $stmt->bind_param("is", $userId, $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getOrderItems($orderId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi 
                           JOIN products p ON oi.product_id = p.product_id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function calculateOrderTotal($orderId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT SUM(quantity * price) as total FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

function countOrderItems($orderId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM order_items WHERE order_id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function getUserOrder($userId, $orderId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND order_id = ?");
    $stmt->bind_param("ii", $userId, $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function cancelOrder($userId, $orderId) {
    $conn = getDBConnection();
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get order details and verify it belongs to the user and is cancellable
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND order_id = ? AND status IN ('pending', 'processing')");
        $stmt->bind_param("ii", $userId, $orderId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        
        if (!$order) {
            throw new Exception("Order cannot be cancelled");
        }
        
        // Update order status to cancelled
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        
        // Get order items
        $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Restore stock for each item
        foreach ($items as $item) {
            updateProductStock($item['product_id'], $item['quantity']);
            logInventoryChange($item['product_id'], 'edit', $item['quantity'], $userId);
        }
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Cancel order error: " . $e->getMessage());
        throw $e;
    }
}

function getTotalOrders() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) AS total FROM orders");
    return $result->fetch_assoc()['total'];
}

function getRecentOrders($limit = 5) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT o.*, u.full_name FROM orders o 
                           JOIN users u ON o.user_id = u.user_id 
                           ORDER BY o.order_date DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalRevenue() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT SUM(total_amount) AS total FROM orders WHERE status = 'delivered'");
    return $result->fetch_assoc()['total'] ?? 0;
}

function getSalesData($days = 30) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT DATE(order_date) AS date, SUM(total_amount) AS total 
                           FROM orders 
                           WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) 
                           AND status = 'delivered'
                           GROUP BY DATE(order_date) 
                           ORDER BY date");
    $stmt->bind_param("i", $days);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Review functions
function getProductReviews($productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT r.*, u.full_name FROM reviews r 
                           JOIN users u ON r.user_id = u.user_id 
                           WHERE r.product_id = ? ORDER BY r.review_date DESC");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function calculateAverageRating($productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT AVG(rating) AS average FROM reviews WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return round($result['average'] ?? 0);
}

function countUserReviews($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM reviews WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'];
}

function hasPurchasedProduct($userId, $productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.order_id 
                           WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] > 0;
}

// Contact functions
function saveContactMessage($name, $email, $subject, $message) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);
    return $stmt->execute();
}

// Inventory log functions
function logInventoryChange($productId, $changeType, $quantityChange, $actionBy) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO inventory_logs (product_id, change_type, quantity_change, action_by) 
                           VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $productId, $changeType, $quantityChange, $actionBy);
    return $stmt->execute();
}

function getInventoryLogs($limit = 50) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT il.*, p.name AS product_name, u.full_name 
                           FROM inventory_logs il 
                           JOIN products p ON il.product_id = p.product_id 
                           JOIN users u ON il.action_by = u.user_id 
                           ORDER BY il.action_time DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// User management functions
function getAllUsers($page = 1, $itemsPerPage = 10) {
    $conn = getDBConnection();
    $offset = ($page - 1) * $itemsPerPage;
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY user_id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $itemsPerPage, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTotalUsers() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) AS total FROM users");
    return $result->fetch_assoc()['total'];
}

function updateUserRole($userId, $role) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->bind_param("si", $role, $userId);
    return $stmt->execute();
}

function deleteUser($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    return $stmt->execute();
}

// Recently viewed products
function getRecentlyViewedProducts($userId, $limit = 4) {
    // This would typically use a separate table to track viewed products
    // For simplicity, we'll return featured products in this example
    return getFeaturedProducts($limit);
}

function getCartItem($cartItemId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT ci.*, p.stock 
                           FROM cart_items ci 
                           JOIN products p ON ci.product_id = p.product_id 
                           WHERE ci.cart_item_id = ?");
    $stmt->bind_param("i", $cartItemId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Wishlist functions
function addToWishlist($userId, $productId) {
    $conn = getDBConnection();
    
    // Check if item already exists in wishlist
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return false; // Item already in wishlist
    }
    
    try {
        // Add item to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $productId);
        return $stmt->execute();
    } catch (Exception $e) {
        // If there's a duplicate key error, return false
        if ($e->getCode() == 1062) { // MySQL duplicate entry error code
            return false;
        }
        throw $e; // Re-throw other exceptions
    }
}

function removeFromWishlist($userId, $productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    return $stmt->execute();
}

function getWishlistItems($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, w.created_at as added_date 
                           FROM wishlist w 
                           JOIN products p ON w.product_id = p.product_id 
                           WHERE w.user_id = ? 
                           ORDER BY w.created_at DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function isInWishlist($userId, $productId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function countWishlistItems($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

// Image handling functions
function getProductImageUrl($product) {
    if (!empty($product['image'])) {
        $imagePath = $product['image'];
        // Check if the image exists in the uploads directory
        if (file_exists(__DIR__ . '/../' . $imagePath)) {
            return $imagePath;
        }
        // If the image doesn't exist, check if it's a full URL
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $imagePath;
        }
    }
    // Return default image if no valid image is found
    return 'uploads/products/default-product.jpg';
}

function uploadProductImage($file) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        return ['success' => false, 'message' => $errors[$file['error']] ?? 'Unknown upload error'];
    }

    // Validate file size (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size must be less than 5MB'];
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, GIF & WebP files are allowed'];
    }

    // Create upload directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/products';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return ['success' => false, 'message' => 'Failed to create upload directory'];
        }
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($mimeType === 'image/webp' && $extension !== 'webp') {
        $extension = 'webp';
    }
    $filename = uniqid('product_') . '.' . $extension;
    $targetPath = $uploadDir . '/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'message' => 'Failed to move uploaded file'];
    }

    // Set proper permissions
    chmod($targetPath, 0644);

    // Return relative path for database storage
    return [
        'success' => true,
        'path' => 'uploads/products/' . $filename
    ];
}

function deleteProductImage($imagePath) {
    $fullPath = dirname(__DIR__) . '/' . $imagePath;
    if (!empty($imagePath) && file_exists($fullPath) && $imagePath != 'uploads/products/default-product.jpg') {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Get admin permissions for a specific user
 * @param int $userId The user ID
 * @return array Array of permission names
 */
function getAdminPermissions($userId) {
    global $conn;
    
    $userId = (int)$userId;
    $permissions = [];
    
    $query = "SELECT permission_name FROM admin_permissions WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['permission_name'];
        }
    }
    
    return $permissions;
}

/**
 * Update admin permissions for a specific user
 * @param int $userId The user ID
 * @param array $permissions Array of permission names
 * @return bool True if successful, false otherwise
 */
function updateAdminPermissions($userId, $permissions) {
    global $conn;
    
    $userId = (int)$userId;
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete existing permissions
        $deleteQuery = "DELETE FROM admin_permissions WHERE user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Insert new permissions
        if (!empty($permissions)) {
            $insertQuery = "INSERT INTO admin_permissions (user_id, permission_name) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            
            foreach ($permissions as $permission) {
                $stmt->bind_param("is", $userId, $permission);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return false;
    }
}

/**
 * Check if user has a specific permission
 * @param int $userId The user ID
 * @param string $permission The permission name to check
 * @return bool True if user has permission, false otherwise
 */
function hasPermission($userId, $permission) {
    $permissions = getAdminPermissions($userId);
    return in_array($permission, $permissions);
}

/**
 * Get total number of orders for a specific user
 * @param int $userId The user ID
 * @return int Total number of orders
 */
function getTotalUserOrders($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

/**
 * Get total amount spent by a specific user
 * @param int $userId The user ID
 * @return float Total amount spent
 */
function getTotalUserSpent($userId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND status != 'cancelled'");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}
?>