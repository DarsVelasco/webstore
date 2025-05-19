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
    overflow-x: hidden;
    min-width: 60px; /* Minimum width when collapsed */
}

.sidebar-header {
    padding: 1rem;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 60px;
    position: sticky;
    top: 0;
    background: inherit;
    z-index: 2;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-toggle {
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    padding: 0.5rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
    min-width: 32px;
    min-height: 32px;
}

.sidebar-toggle:hover {
    color: #fff;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
    overflow-x: hidden;
}

.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 100%;
}

.sidebar-nav li {
    margin-bottom: 0.25rem;
    width: 100%;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.25rem;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    transition: all 0.3s ease;
    white-space: nowrap;
    width: 100%;
    min-height: 44px;
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
    margin-right: 0.75rem;
    text-align: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.sidebar-nav span {
    font-size: 0.9rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255,255,255,0.1);
    display: flex;
    gap: 0.5rem;
    position: sticky;
    bottom: 0;
    background: inherit;
    z-index: 2;
}

.sidebar-footer a {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.3s ease;
    min-height: 38px;
    white-space: nowrap;
}

.sidebar-footer a:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.sidebar-footer i {
    margin-right: 0.5rem;
    font-size: 1rem;
    flex-shrink: 0;
}

.sidebar-footer span {
    font-size: 0.9rem;
    white-space: nowrap;
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
    padding: 0.75rem;
}

.admin-sidebar.collapsed .sidebar-nav i {
    margin: 0;
    font-size: 1.25rem;
}

.admin-sidebar.collapsed .sidebar-footer {
    flex-direction: column;
    padding: 0.5rem;
}

.admin-sidebar.collapsed .sidebar-footer a {
    padding: 0.5rem;
    justify-content: center;
}

.admin-sidebar.collapsed .sidebar-footer i {
    margin: 0;
    font-size: 1.25rem;
}

/* Mobile styles */
@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }

    .admin-sidebar.mobile-visible {
        transform: translateX(0);
    }

    .sidebar-toggle {
        display: flex;
    }
}

/* Zoom support */
@media screen and (min-resolution: 1dppx) {
    .admin-sidebar {
        min-width: 60px;
    }

    .sidebar-nav a {
        min-height: 44px;
    }

    .sidebar-footer a {
        min-height: 38px;
    }
}

/* High DPI screen support */
@media screen and (-webkit-min-device-pixel-ratio: 2), 
       screen and (min-resolution: 192dpi) {
    .admin-sidebar {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
}

/* Add main content adjustment */
.main-content {
    margin-left: 250px;
    transition: margin-left 0.3s ease;
    padding: 1.25rem;
    min-width: 320px;
}

.main-content.sidebar-collapsed {
    margin-left: 60px;
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
        width: 100%;
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
    const mainContent = document.querySelector('.main-content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mediaQuery = window.matchMedia('(max-width: 768px)');

    function toggleSidebar() {
        if (mediaQuery.matches) {
            sidebar.classList.toggle('mobile-visible');
        } else {
            sidebar.classList.toggle('collapsed');
            if (mainContent) {
                mainContent.classList.toggle('sidebar-collapsed');
            }
        }
    }

    sidebarToggle.addEventListener('click', toggleSidebar);

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (mediaQuery.matches && 
            !sidebar.contains(event.target) && 
            !event.target.matches('#sidebarToggle') &&
            sidebar.classList.contains('mobile-visible')) {
            sidebar.classList.remove('mobile-visible');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (!mediaQuery.matches && sidebar.classList.contains('mobile-visible')) {
            sidebar.classList.remove('mobile-visible');
        }
    });
});
</script>