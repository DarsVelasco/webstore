<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$user = getUserById($userId);

// Get all orders for the user
$orders = getUserOrders($userId);

include 'includes/header.php';
?>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1500;">
    <div id="orderNotification" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <!-- Message will be inserted here -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelOrderModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-3x"></i>
                </div>
                <h5 class="mb-3">Are you sure you want to cancel this order?</h5>
                <p class="text-muted mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>No, Keep Order
                </button>
                <button type="button" class="btn btn-danger px-4 py-2 rounded-3" id="confirmCancelBtn">
                    <i class="fas fa-check me-2"></i>Yes, Cancel Order
                </button>
            </div>
        </div>
    </div>
</div>

<section class="dashboard-section py-5">
    <div class="container">
        <div class="dashboard-header mb-4">
            <h1 class="display-6 fw-bold mb-1">My Orders</h1>
            <p class="text-muted">View your order history and track current orders.</p>
        </div>
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="card shadow-sm rounded-4 border-0 mb-4 mb-lg-0">
                    <div class="card-body p-4">
                        <div class="user-profile text-center mb-4">
                            <div class="profile-image mb-2 mx-auto" style="width: 80px; height: 80px;">
                                <img src="images/default-profile.jpg" alt="Profile Image" class="rounded-circle w-100 h-100 object-fit-cover">
                            </div>
                            <div class="profile-info">
                                <h5 class="fw-semibold mb-0"><?= htmlspecialchars($user['full_name']) ?></h5>
                                <p class="text-muted small mb-0"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                        </div>
                        <nav class="dashboard-nav">
                            <ul class="nav flex-column gap-2">
                                <li class="nav-item"><a class="nav-link rounded-pill" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link rounded-pill active" href="orders.php"><i class="fas fa-shopping-bag me-2"></i> My Orders</a></li>
                                <li class="nav-item"><a class="nav-link rounded-pill" href="account-settings.php"><i class="fas fa-user-cog me-2"></i> Account Settings</a></li>
                                <li class="nav-item"><a class="nav-link rounded-pill" href="wishlist.php"><i class="fas fa-heart me-2"></i> Wishlist</a></li>
                                <li class="nav-item"><a class="nav-link rounded-pill" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-3">Order History</h2>
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <div class="mb-3"><i class="fas fa-shopping-bag fa-3x text-muted"></i></div>
                                <h2 class="fw-semibold mb-2">No Orders Yet</h2>
                                <p class="text-muted mb-3">You haven't placed any orders yet. Start shopping to see your orders here.</p>
                                <a href="shop.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr id="order-row-<?= $order['order_id'] ?>">
                                                <td>#<?= $order['order_id'] ?></td>
                                                <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                                                <td><?= countOrderItems($order['order_id']) ?></td>
                                                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= $order['status'] ?>">
                                                        <?= ucfirst($order['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="order-details.php?id=<?= $order['order_id'] ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye me-1"></i> View
                                                        </a>
                                                        <?php if (in_array($order['status'], ['pending', 'processing'])): ?>
                                                            <button class="btn btn-sm btn-outline-danger cancel-order-btn" 
                                                                    data-order-id="<?= $order['order_id'] ?>">
                                                                <i class="fas fa-times me-1"></i> Cancel
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.dashboard-section {
    background-color: var(--light-bg, #f8f9fa);
}

.dashboard-header h1 {
    font-size: 2rem;
}

.dashboard-nav .nav-link {
    color: #333;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    transition: background 0.2s, color 0.2s;
}

.dashboard-nav .nav-link.active, 
.dashboard-nav .nav-link:hover {
    background: #e3f2fd;
    color: #1976d2;
}

.card {
    border-radius: 1rem;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: inline-block;
    text-align: center;
    min-width: 100px;
}

.status-badge.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.status-badge.status-processing {
    background-color: #cce5ff;
    color: #004085;
}

.status-badge.status-shipped {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-badge.status-delivered {
    background-color: #d4edda;
    color: #155724;
}

.status-badge.status-cancelled {
    background-color: #f8d7da;
    color: #721c24;
}

/* Toast Styles */
.toast {
    transition: all 0.3s ease;
}

.toast.bg-success {
    background-color: #28a745 !important;
}

.toast.bg-danger {
    background-color: #dc3545 !important;
}

.toast.bg-warning {
    background-color: #ffc107 !important;
}

.toast.bg-info {
    background-color: #17a2b8 !important;
}

/* Animation for status change */
@keyframes statusChange {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.status-badge.animate {
    animation: statusChange 0.5s ease;
}

/* Modal Styles */
.modal-content {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal .btn {
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0.5px;
}

.modal-backdrop.show {
    opacity: 0.7;
}

.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: none;
}
</style>

<script>
// Initialize Bootstrap components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize toast
    const toastContainer = document.getElementById('orderNotification');
    const toast = new bootstrap.Toast(toastContainer, {
        animation: true,
        autohide: true,
        delay: 3000
    });

    // Initialize modal
    const modalElement = document.getElementById('cancelOrderModal');
    const cancelModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });

    let orderIdToCancel = null;

    function showNotification(message, type = 'success') {
        const toastElement = document.getElementById('orderNotification');
        toastElement.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info');
        toastElement.classList.add(`bg-${type}`);
        toastElement.querySelector('.toast-body').textContent = message;
        toast.show();
    }

    function updateOrderStatus(orderId, newStatus) {
        const row = document.getElementById(`order-row-${orderId}`);
        if (row) {
            const statusBadge = row.querySelector('.status-badge');
            statusBadge.className = `status-badge status-${newStatus}`;
            statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            statusBadge.classList.add('animate');
            
            if (newStatus === 'cancelled') {
                const cancelBtn = row.querySelector('.cancel-order-btn');
                if (cancelBtn) {
                    cancelBtn.remove();
                }
            }
        }
    }

    function showCancelModal(orderId) {
        console.log('Opening modal for order:', orderId);
        orderIdToCancel = orderId;
        cancelModal.show();
    }

    async function cancelOrder(orderId) {
        try {
            console.log('Cancelling order:', orderId);
            const response = await fetch('./cancel-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'order_id=' + orderId
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                showNotification('Order cancelled successfully', 'success');
                updateOrderStatus(orderId, 'cancelled');
                cancelModal.hide();
            } else {
                showNotification(data.message || 'Failed to cancel order', 'danger');
            }
        } catch (error) {
            console.error('Error cancelling order:', error);
            showNotification('An error occurred while cancelling the order', 'danger');
        }
    }

    // Add click event listeners to all cancel buttons
    document.querySelectorAll('.cancel-order-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.getAttribute('data-order-id');
            console.log('Cancel button clicked for order:', orderId);
            showCancelModal(orderId);
        });
    });

    // Add click event listener to the confirm cancel button in the modal
    document.getElementById('confirmCancelBtn').addEventListener('click', function() {
        console.log('Confirm button clicked, orderIdToCancel:', orderIdToCancel);
        if (orderIdToCancel) {
            cancelOrder(orderIdToCancel);
        }
    });

    // Debug modal events
    modalElement.addEventListener('show.bs.modal', function () {
        console.log('Modal is about to show');
    });
    
    modalElement.addEventListener('shown.bs.modal', function () {
        console.log('Modal is shown');
    });
    
    modalElement.addEventListener('hide.bs.modal', function () {
        console.log('Modal is about to hide');
    });
    
    modalElement.addEventListener('hidden.bs.modal', function () {
        console.log('Modal is hidden');
        orderIdToCancel = null;
    });
});
</script>

<?php include 'includes/footer.php'; ?>