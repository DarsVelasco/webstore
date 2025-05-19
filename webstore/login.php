<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $user = authenticateUser($email, $password);
    
    if ($user) {
        // Start session and set user data
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $errorMessage = 'Invalid email or password. Please try again.';
    }
}

include 'includes/header.php';
?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <h1 class="h3 fw-bold mb-4 text-center">Login to Your Account</h1>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success rounded-3 mb-4 fw-semibold"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success'] ?></div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger rounded-3 mb-4 fw-semibold"><i class="fas fa-exclamation-circle me-2"></i><?= $errorMessage ?></div>
                    <?php endif; ?>
                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control rounded-3" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input type="password" name="password" id="password" class="form-control rounded-3" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                                <label for="remember" class="form-check-label">Remember me</label>
                            </div>
                            <a href="forgot-password.php" class="small">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Login</button>
                    </form>
                    <div class="text-center mt-2">
                        <span class="text-muted">Don't have an account?</span> <a href="register.php" class="fw-semibold">Sign up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.auth-section {
    background: var(--light-bg, #f8f9fa);
    min-height: 100vh;
}
.card {
    border-radius: 1.25rem;
}
</style>

<?php include 'includes/footer.php'; ?>