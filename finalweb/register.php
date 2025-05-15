<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$errorMessages = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validate inputs
    if (empty($fullName)) {
        $errorMessages['full_name'] = 'Full name is required.';
    }
    
    if (empty($email)) {
        $errorMessages['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages['email'] = 'Please enter a valid email address.';
    } elseif (emailExists($email)) {
        $errorMessages['email'] = 'This email is already registered.';
    }
    
    if (empty($password)) {
        $errorMessages['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errorMessages['password'] = 'Password must be at least 8 characters.';
    }
    
    if ($password !== $confirmPassword) {
        $errorMessages['confirm_password'] = 'Passwords do not match.';
    }
    
    // If no errors, register user
    if (empty($errorMessages)) {
        if (registerUser($fullName, $email, $password, $phone, $address)) {
            $successMessage = 'Registration successful! You can now login.';
            // Clear form
            $fullName = $email = $phone = $address = '';
        } else {
            $errorMessages['general'] = 'Registration failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-form">
            <h1>Create an Account</h1>
            
            <?php if ($successMessage): ?>
                <div class="alert alert-success"><?= $successMessage ?></div>
            <?php elseif (isset($errorMessages['general'])): ?>
                <div class="alert alert-danger"><?= $errorMessages['general'] ?></div>
            <?php endif; ?>
            
            <form method="post" action="register.php">
                <div class="form-group">
                    <label for="full_name">Full Name*</label>
                    <input type="text" name="full_name" id="full_name" value="<?= isset($fullName) ? htmlspecialchars($fullName) : '' ?>" required>
                    <?php if (isset($errorMessages['full_name'])): ?>
                        <span class="error-message"><?= $errorMessages['full_name'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address*</label>
                    <input type="email" name="email" id="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    <?php if (isset($errorMessages['email'])): ?>
                        <span class="error-message"><?= $errorMessages['email'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password* (min 8 characters)</label>
                    <input type="password" name="password" id="password" required>
                    <?php if (isset($errorMessages['password'])): ?>
                        <span class="error-message"><?= $errorMessages['password'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password*</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <?php if (isset($errorMessages['confirm_password'])): ?>
                        <span class="error-message"><?= $errorMessages['confirm_password'] ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea name="address" id="address" rows="3"><?= isset($address) ? htmlspecialchars($address) : '' ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>