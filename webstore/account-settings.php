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

$successMessage = '';
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($fullName)) {
        $errorMessages['full_name'] = 'Full name is required.';
    }
    
    if (empty($email)) {
        $errorMessages['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages['email'] = 'Please enter a valid email address.';
    } elseif ($email != $user['email'] && emailExists($email)) {
        $errorMessages['email'] = 'This email is already registered.';
    }
    
    // Password change validation
    if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
        if (empty($currentPassword)) {
            $errorMessages['current_password'] = 'Current password is required to change password.';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errorMessages['current_password'] = 'Current password is incorrect.';
        }
        
        if (empty($newPassword)) {
            $errorMessages['new_password'] = 'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $errorMessages['new_password'] = 'Password must be at least 8 characters.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errorMessages['confirm_password'] = 'Passwords do not match.';
        }
    }
    
    // If no errors, update profile
    if (empty($errorMessages)) {
        $updateData = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        
        // Only update password if provided
        if (!empty($newPassword)) {
            $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        if (updateUserProfile($userId, $updateData)) {
            $successMessage = 'Profile updated successfully!';
            
            // Update session data
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;
            
            // Refresh user data
            $user = getUserById($userId);
        } else {
            $errorMessages['general'] = 'Failed to update profile. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="dashboard-section py-5">
    <div class="container">
        <div class="dashboard-header mb-4">
            <h1 class="display-6 fw-bold mb-1">Account Settings</h1>
            <p class="text-muted">Manage your account information and settings.</p>
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
                                <li class="nav-item"><a class="nav-link rounded-pill" href="orders.php"><i class="fas fa-shopping-bag me-2"></i> My Orders</a></li>
                                <li class="nav-item"><a class="nav-link rounded-pill active" href="account-settings.php"><i class="fas fa-user-cog me-2"></i> Account Settings</a></li>
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
                    <div class="card-body p-4">
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success rounded-3 mb-4 fw-semibold"><i class="fas fa-check-circle me-2"></i><?= $successMessage ?></div>
                        <?php elseif (isset($errorMessages['general'])): ?>
                            <div class="alert alert-danger rounded-3 mb-4 fw-semibold"><i class="fas fa-exclamation-circle me-2"></i><?= $errorMessages['general'] ?></div>
                        <?php endif; ?>
                        <form method="post" class="account-form">
                            <div class="form-section mb-4">
                                <h2 class="h5 fw-bold mb-3">Personal Information</h2>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="full_name" class="form-label fw-semibold">Full Name*</label>
                                        <input type="text" name="full_name" id="full_name" class="form-control rounded-3" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                        <?php if (isset($errorMessages['full_name'])): ?>
                                            <div class="invalid-feedback d-block"><?= $errorMessages['full_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-semibold">Email Address*</label>
                                        <input type="email" name="email" id="email" class="form-control rounded-3" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        <?php if (isset($errorMessages['email'])): ?>
                                            <div class="invalid-feedback d-block"><?= $errorMessages['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                        <input type="tel" name="phone" id="phone" class="form-control rounded-3" value="<?= htmlspecialchars($user['phone']) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="address" class="form-label fw-semibold">Address</label>
                                        <textarea name="address" id="address" rows="3" class="form-control rounded-3"><?= htmlspecialchars($user['address']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="form-section mb-4">
                                <h2 class="h5 fw-bold mb-3">Change Password</h2>
                                <p class="text-muted mb-3">Leave blank if you don't want to change your password.</p>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                        <input type="password" name="current_password" id="current_password" class="form-control rounded-3">
                                        <?php if (isset($errorMessages['current_password'])): ?>
                                            <div class="invalid-feedback d-block"><?= $errorMessages['current_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="new_password" class="form-label fw-semibold">New Password</label>
                                        <input type="password" name="new_password" id="new_password" class="form-control rounded-3">
                                        <?php if (isset($errorMessages['new_password'])): ?>
                                            <div class="invalid-feedback d-block"><?= $errorMessages['new_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="confirm_password" class="form-label fw-semibold">Confirm New Password</label>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control rounded-3">
                                        <?php if (isset($errorMessages['confirm_password'])): ?>
                                            <div class="invalid-feedback d-block"><?= $errorMessages['confirm_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions d-flex gap-3">
                                <button type="submit" class="btn btn-primary px-4 py-2">Save Changes</button>
                                <a href="dashboard.php" class="btn btn-outline px-4 py-2">Cancel</a>
                            </div>
                        </form>
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
.dashboard-nav .nav-link.active, .dashboard-nav .nav-link:hover {
    background: #e3f2fd;
    color: #1976d2;
}
.card {
    border-radius: 1rem;
}
.invalid-feedback {
    font-size: 0.95rem;
    margin-top: 0.25rem;
}
</style>

<?php include 'includes/footer.php'; ?>