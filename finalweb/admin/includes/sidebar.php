<!-- includes/sidebar.php -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
.admin-sidebar {
    width: 250px;
    height: 100vh;
    background: #2c3e50;
    color: #fff;
    position: fixed;
    left: 0;
    top: 0;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    z-index: 1000;
    overflow-y: auto;
}

.sidebar-header {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 500;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    padding: 5px;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
}

.sidebar-toggle:hover {
    color: #fff;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 0;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-nav li {
    margin-bottom: 5px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: all 0.3s ease;
}

.sidebar-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.sidebar-nav li.active a {
    background: #4a90e2;
    color: #fff;
}

.sidebar-nav i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    display: flex;
    gap: 10px;
}

.sidebar-footer a {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.sidebar-footer a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.sidebar-footer i {
    margin-right: 8px;
}

.view-site {
    background: rgba(74, 144, 226, 0.2);
}

.logout {
    background: rgba(220, 53, 69, 0.2);
}

/* Collapsed state */
.admin-sidebar.collapsed {
    width: 60px;
}

.admin-sidebar.collapsed .sidebar-header h2,
.admin-sidebar.collapsed .sidebar-nav span,
.admin-sidebar.collapsed .sidebar-footer span {
    display: none;
}

.admin-sidebar.collapsed .sidebar-nav a {
    justify-content: center;
    padding: 12px;
}

.admin-sidebar.collapsed .sidebar-nav i {
    margin: 0;
    font-size: 18px;
}

.admin-sidebar.collapsed .sidebar-footer {
    flex-direction: column;
}

.admin-sidebar.collapsed .sidebar-footer a {
    padding: 10px;
}

.admin-sidebar.collapsed .sidebar-footer i {
    margin: 0;
    font-size: 18px;
}

/* Mobile styles */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }

    .admin-sidebar.mobile-visible {
        transform: translateX(0);
    }
}

/* Add main content adjustment */
.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
    padding: 20px;
}

.main-content.sidebar-collapsed {
    margin-left: 60px;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
}
</style>

<div class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
        <button id="sidebarToggle" class="sidebar-toggle" type="button" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo $currentPage === 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="<?php echo $currentPage === 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            <li class="<?php echo $currentPage === 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            <li class="<?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li class="<?php echo $currentPage === 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="../" class="view-site">
            <i class="fas fa-external-link-alt"></i>
            <span>View Site</span>
        </a>
        <a href="../logout.php" class="logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.admin-sidebar');
    const mainContent = document.querySelector('.admin-content');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    // Load saved state
    const isSidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isSidebarCollapsed && window.innerWidth > 768) {
        sidebar.classList.add('collapsed');
        if (mainContent) {
            mainContent.classList.add('sidebar-collapsed');
        }
    }
    
    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('mobile-visible');
        } else {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed');
            }
            // Save state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            sidebar.classList.contains('mobile-visible')) {
            sidebar.classList.remove('mobile-visible');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-visible');
            if (isSidebarCollapsed) {
                sidebar.classList.add('collapsed');
                if (mainContent) {
                    mainContent.classList.add('sidebar-collapsed');
                }
            }
        }
    });
});
</script>