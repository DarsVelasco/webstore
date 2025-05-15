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

<section class="dashboard-section">
    <div class="container">
        <div class="dashboard-header">
            <h1>Account Settings</h1>
            <p>Manage your account information and settings.</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Sidebar Navigation -->
            <div class="dashboard-sidebar">
                <div class="user-profile">
                    <div class="profile-image">
                        <img src="images/default-profile.jpg" alt="Profile Image">
                    </div>
                    <div class="profile-info">
                        <h3><?= htmlspecialchars($user['full_name']) ?></h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                </div>
                
                <nav class="dashboard-nav">
                    <ul>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                        <li class="active"><a href="account-settings.php"><i class="fas fa-user-cog"></i> Account Settings</a></li>
                        <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="dashboard-content">
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= $successMessage ?></div>
                <?php elseif (isset($errorMessages['general'])): ?>
                    <div class="alert alert-danger"><?= $errorMessages['general'] ?></div>
                <?php endif; ?>
                
                <form method="post" class="account-form">
                    <div class="form-section">
                        <h2>Personal Information</h2>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name*</label>
                            <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            <?php if (isset($errorMessages['full_name'])): ?>
                                <span class="error-message"><?= $errorMessages['full_name'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address*</label>
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            <?php if (isset($errorMessages['email'])): ?>
                                <span class="error-message"><?= $errorMessages['email'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" id="address" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Change Password</h2>
                        <p>Leave blank if you don't want to change your password.</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password">
                            <?php if (isset($errorMessages['current_password'])): ?>
                                <span class="error-message"><?= $errorMessages['current_password'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password">
                            <?php if (isset($errorMessages['new_password'])): ?>
                                <span class="error-message"><?= $errorMessages['new_password'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password">
                            <?php if (isset($errorMessages['confirm_password'])): ?>
                                <span class="error-message"><?= $errorMessages['confirm_password'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>