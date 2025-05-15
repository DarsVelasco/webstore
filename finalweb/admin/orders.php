<?php
require_once '../includes/functions.php';
require_once '../includes/connection.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$orders = getOrders($limit, $offset);
$totalOrders = getTotalOrders();
$totalPages = ceil($totalOrders / $limit);

include 'includes/header.php';
?>

<h1>Orders</h1>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= $order['order_id'] ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                        <span class="status-badge <?= $order['status'] ?>">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-primary view-order" 
                                    data-id="<?= $order['order_id'] ?>">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-info update-status" 
                                    data-id="<?= $order['order_id'] ?>"
                                    data-status="<?= $order['status'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<!-- Order Details Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetails"></div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div class="mb-3">
                        <label for="orderStatus" class="form-label">Status</label>
                        <select class="form-select" id="orderStatus" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveStatus">Update</button>
            </div>
        </div>
    </div>
</div>

<style>
.table-responsive {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge.pending { background: #fff3cd; color: #856404; }
.status-badge.processing { background: #cce5ff; color: #004085; }
.status-badge.completed { background: #d4edda; color: #155724; }
.status-badge.cancelled { background: #f8d7da; color: #721c24; }

/* Order Details Styles */
.order-details {
    margin-bottom: 20px;
}

.order-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.order-info-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.order-info-item h6 {
    margin: 0 0 5px 0;
    color: #6c757d;
    font-size: 12px;
    text-transform: uppercase;
}

.order-info-item p {
    margin: 0;
    font-size: 16px;
    color: #2c3e50;
}

.order-items {
    width: 100%;
    margin-top: 20px;
}

.order-items th {
    background: #f8f9fa;
    padding: 10px;
}

.order-items td {
    padding: 10px;
    border-bottom: 1px solid #dee2e6;
}

.order-total {
    text-align: right;
    margin-top: 20px;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    const statusModal = new bootstrap.Modal(document.getElementById('statusModal'));

    // Handle view order
    document.querySelectorAll('.view-order').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.id;
            
            fetch(`get-order-details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('orderDetails').innerHTML = data.html;
                        orderModal.show();
                    } else {
                        alert('Error loading order details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details');
                });
        });
    });

    // Handle update status
    document.querySelectorAll('.update-status').forEach(button => {
        button.addEventListener('click', function() {
            const orderId = this.dataset.id;
            const currentStatus = this.dataset.status;
            
            document.getElementById('orderId').value = orderId;
            document.getElementById('orderStatus').value = currentStatus;
            statusModal.show();
        });
    });

    // Handle save status
    document.getElementById('saveStatus').addEventListener('click', function() {
        const form = document.getElementById('statusForm');
        const formData = new FormData(form);

        fetch('update-order-status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status');
        })
        .finally(() => {
            statusModal.hide();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 