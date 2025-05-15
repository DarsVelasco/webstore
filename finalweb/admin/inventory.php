<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Handle inventory actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'restock':
                $productId = (int)$_POST['product_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    updateProductStock($productId, $quantity);
                    logInventoryChange($productId, 'restock', $quantity, $_SESSION['user_id']);
                }
                break;
        }
    }
}

// Get inventory logs
$inventoryLogs = getInventoryLogs();

// Get low stock products
$lowStockProducts = getLowStockProducts(5);

include 'includes/header.php';
?>

<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="admin-content">
        <h1>Inventory Management</h1>
        
        <div class="inventory-grid">
            <!-- Low Stock Alert -->
            <div class="inventory-card">
                <h2>Low Stock Alert</h2>
                
                <?php if (empty($lowStockProducts)): ?>
                    <p>No products with low stock.</p>
                <?php else: ?>
                    <div class="low-stock-list">
                        <?php foreach ($lowStockProducts as $product): ?>
                            <div class="low-stock-item">
                                <div class="product-info">
                                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                                    <p>Current Stock: <?= $product['stock'] ?></p>
                                </div>
                                <div class="restock-form">
                                    <form method="post">
                                        <input type="hidden" name="action" value="restock">
                                        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                        <input type="number" name="quantity" min="1" value="10">
                                        <button type="submit" class="btn btn-sm btn-primary">Restock</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Inventory Logs -->
            <div class="inventory-card">
                <h2>Inventory Logs</h2>
                
                <?php if (empty($inventoryLogs)): ?>
                    <p>No inventory logs found.</p>
                <?php else: ?>
                    <div class="logs-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Action</th>
                                    <th>Quantity</th>
                                    <th>By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inventoryLogs as $log): ?>
                                    <tr>
                                        <td><?= date('M j, Y H:i', strtotime($log['action_time'])) ?></td>
                                        <td><?= htmlspecialchars($log['product_name']) ?></td>
                                        <td>
                                            <span class="log-action <?= $log['change_type'] ?>">
                                                <?= ucfirst($log['change_type']) ?>
                                            </span>
                                        </td>
                                        <td><?= $log['quantity_change'] ?></td>
                                        <td><?= htmlspecialchars($log['full_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>