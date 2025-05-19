<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.ico">
    <!-- jQuery (required for Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
      .navbar-glass {
        background: rgba(255,255,255,0.92) !important;
        box-shadow: 0 4px 24px 0 rgba(31,38,135,0.10);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-bottom: 1px solid #e2e8f0;
      }
      .navbar .nav-link.rounded-pill {
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        font-weight: 600;
        font-size: 1.1rem;
        padding: 0.5rem 1.5rem;
        margin: 0 0.2rem;
      }
      .navbar .nav-link.active, .navbar .nav-link.rounded-pill:hover {
        background: var(--primary-color);
        color: #fff !important;
        box-shadow: 0 4px 16px rgba(99,102,241,0.10);
      }
      .navbar .cart-btn {
        position: relative;
      }
      .navbar .cart-badge {
        position: absolute;
        top: 2px;
        right: 8px;
        background: var(--accent-color);
        color: #fff;
        font-size: 0.8rem;
        padding: 0.2em 0.6em;
        border-radius: 1em;
        z-index: 2;
      }
      /* Fix for dropdown menu */
      .dropdown-menu {
        margin-top: 0.5rem !important;
        border: none !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
      }
      .dropdown-toggle {
        cursor: pointer !important;
      }
      .nav-item.dropdown {
        position: relative !important;
      }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-glass fixed-top py-2">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-bold fs-3" href="index.php">
      <i class="fas fa-store" style="color: var(--accent-color);"></i> <?php echo SITE_NAME; ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item">
          <a class="nav-link rounded-pill<?php if(basename($_SERVER['PHP_SELF'])=='index.php') echo ' active'; ?>" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill<?php if(basename($_SERVER['PHP_SELF'])=='shop.php') echo ' active'; ?>" href="shop.php">Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill<?php if(basename($_SERVER['PHP_SELF'])=='about.php') echo ' active'; ?>" href="about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill<?php if(basename($_SERVER['PHP_SELF'])=='contact.php') echo ' active'; ?>" href="contact.php">Support</a>
        </li>
        <li class="nav-item">
          <a class="nav-link rounded-pill cart-btn position-relative" href="cart.php">
            <i class="fa fa-shopping-cart"></i>
            <?php if (isLoggedIn() && ($cartCount = count(getCartItems($_SESSION['user_id'])))): ?>
              <span class="cart-badge"><?php echo $cartCount; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <?php if (isLoggedIn()): ?>
            <div class="nav-item dropdown">
              <button class="nav-link rounded-pill dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow rounded-4 mt-2" aria-labelledby="userDropdown">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                  <li><a class="dropdown-item text-primary fw-bold" href="admin/dashboard.php"><i class="fas fa-crown me-2"></i> Admin Panel</a></li>
                  <li><hr class="dropdown-divider"></li>
                <?php endif; ?>
                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i> My Orders</a></li>
                <li><a class="dropdown-item" href="account-settings.php"><i class="fas fa-user-cog me-2"></i> Account Settings</a></li>
                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i> Wishlist</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
              </ul>
            </div>
          <?php else: ?>
            <div class="d-flex gap-2">
              <a class="nav-link rounded-pill" href="login.php"><i class="fa fa-sign-in-alt me-1"></i> Login</a>
              <a class="nav-link rounded-pill" href="register.php"><i class="fa fa-user-plus me-1"></i> Register</a>
            </div>
          <?php endif; ?>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['message'];
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all dropdowns
    var dropdowns = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'))
    dropdowns.map(function (dropdownToggle) {
        return new bootstrap.Dropdown(dropdownToggle, {
            offset: [0, 10],
            flip: true,
            boundary: 'viewport',
            reference: 'toggle',
            display: 'dynamic'
        });
    });

    // Add click event listener to dropdown toggle
    document.querySelector('#userDropdown').addEventListener('click', function(e) {
        e.stopPropagation();
        bootstrap.Dropdown.getOrCreateInstance(this).toggle();
    });
});
</script>
</body>
</html>