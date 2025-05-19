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
    $firstName = trim($_POST['first_name']);
    $middleName = trim($_POST['middle_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $phone = trim($_POST['phone']);
    $streetAddress = trim($_POST['street_address']);
    $barangay = trim($_POST['barangay']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $zipCode = trim($_POST['zip_code']);
    $country = trim($_POST['country']);
    $terms = isset($_POST['terms']);
    $remember = isset($_POST['remember']);
    
    // Validate inputs
    if (empty($firstName)) {
        $errorMessages['first_name'] = 'First name is required.';
    }
    if (empty($lastName)) {
        $errorMessages['last_name'] = 'Last name is required.';
    }
    if (empty($email)) {
        $errorMessages['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages['email'] = 'Please enter a valid email address.';
    } elseif (emailExists($email)) {
        $errorMessages['email'] = 'This email is already registered.';
    }
    if (empty($phone)) {
        $errorMessages['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9+\-\s()]{10,15}$/', $phone)) {
        $errorMessages['phone'] = 'Please enter a valid phone number.';
    }
    if (empty($password)) {
        $errorMessages['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errorMessages['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirmPassword) {
        $errorMessages['confirm_password'] = 'Passwords do not match.';
    }
    if (empty($streetAddress)) {
        $errorMessages['street_address'] = 'Street Address is required.';
    }
    if (empty($barangay)) {
        $errorMessages['barangay'] = 'Barangay is required.';
    }
    if (empty($city)) {
        $errorMessages['city'] = 'City/Municipality is required.';
    }
    if (empty($province)) {
        $errorMessages['province'] = 'Province/State is required.';
    }
    if (empty($zipCode)) {
        $errorMessages['zip_code'] = 'ZIP/Postal Code is required.';
    }
    if (empty($country)) {
        $errorMessages['country'] = 'Country is required.';
    }
    if (!$terms) {
        $errorMessages['terms'] = 'You must agree to the terms and agreement.';
    }
    // If no errors, register user
    if (empty($errorMessages)) {
        $fullName = $firstName . ' ' . $middleName . ' ' . $lastName;
        $address = $streetAddress . ', ' . $barangay . ', ' . $city . ', ' . $province . ', ' . $zipCode . ', ' . $country;
        if (registerUser($fullName, $email, $password, $phone, $address)) {
            $_SESSION['success'] = 'Registration successful! You can now login.';
            header("Location: login.php");
            exit();
        } else {
            $errorMessages['general'] = 'Registration failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 p-4">
                    <h1 class="h3 fw-bold mb-4 text-center">Create an Account</h1>
                    <?php if ($successMessage): ?>
                        <div class="alert alert-success rounded-3 mb-4 fw-semibold"><i class="fas fa-check-circle me-2"></i><?= $successMessage ?></div>
                    <?php elseif (isset($errorMessages['general'])): ?>
                        <div class="alert alert-danger rounded-3 mb-4 fw-semibold"><i class="fas fa-exclamation-circle me-2"></i><?= $errorMessages['general'] ?></div>
                    <?php endif; ?>
                    <form method="post" action="register.php">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="first_name" class="form-label fw-semibold">First Name*</label>
                                <input type="text" name="first_name" id="first_name" class="form-control rounded-3" value="<?= isset($firstName) ? htmlspecialchars($firstName) : '' ?>" required>
                                <?php if (isset($errorMessages['first_name'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['first_name'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label for="middle_name" class="form-label fw-semibold">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" class="form-control rounded-3" value="<?= isset($middleName) ? htmlspecialchars($middleName) : '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="last_name" class="form-label fw-semibold">Last Name*</label>
                                <input type="text" name="last_name" id="last_name" class="form-control rounded-3" value="<?= isset($lastName) ? htmlspecialchars($lastName) : '' ?>" required>
                                <?php if (isset($errorMessages['last_name'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['last_name'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address*</label>
                                <input type="email" name="email" id="email" class="form-control rounded-3" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                                <?php if (isset($errorMessages['email'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['email'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number*</label>
                                <input type="tel" name="phone" id="phone" class="form-control rounded-3" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>" required>
                                <?php if (isset($errorMessages['phone'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['phone'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="street_address" class="form-label fw-semibold">Street Address*</label>
                                <input type="text" name="street_address" id="street_address" class="form-control rounded-3" value="<?= isset($streetAddress) ? htmlspecialchars($streetAddress) : '' ?>" required>
                                <?php if (isset($errorMessages['street_address'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['street_address'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="barangay" class="form-label fw-semibold">Barangay*</label>
                                <input type="text" name="barangay" id="barangay" class="form-control rounded-3" value="<?= isset($barangay) ? htmlspecialchars($barangay) : '' ?>" required>
                                <?php if (isset($errorMessages['barangay'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['barangay'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-semibold">City/Municipality*</label>
                                <input type="text" name="city" id="city" class="form-control rounded-3" value="<?= isset($city) ? htmlspecialchars($city) : '' ?>" required>
                                <?php if (isset($errorMessages['city'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['city'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="province" class="form-label fw-semibold">Province/State*</label>
                                <input type="text" name="province" id="province" class="form-control rounded-3" value="<?= isset($province) ? htmlspecialchars($province) : '' ?>" required>
                                <?php if (isset($errorMessages['province'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['province'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="zip_code" class="form-label fw-semibold">ZIP/Postal Code*</label>
                                <input type="text" name="zip_code" id="zip_code" class="form-control rounded-3" value="<?= isset($zipCode) ? htmlspecialchars($zipCode) : '' ?>" required>
                                <?php if (isset($errorMessages['zip_code'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['zip_code'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label fw-semibold">Country*</label>
                                <select name="country" id="country" class="form-select rounded-3" required>
                                    <option value="">Select a country</option>
                                    <option value="Afghanistan" data-flag="🇦🇫" <?= isset($country) && $country === 'Afghanistan' ? 'selected' : '' ?>>Afghanistan</option>
                                    <option value="Armenia" data-flag="🇦🇲" <?= isset($country) && $country === 'Armenia' ? 'selected' : '' ?>>Armenia</option>
                                    <option value="Azerbaijan" data-flag="🇦🇿" <?= isset($country) && $country === 'Azerbaijan' ? 'selected' : '' ?>>Azerbaijan</option>
                                    <option value="Bahrain" data-flag="🇧🇭" <?= isset($country) && $country === 'Bahrain' ? 'selected' : '' ?>>Bahrain</option>
                                    <option value="Bangladesh" data-flag="🇧🇩" <?= isset($country) && $country === 'Bangladesh' ? 'selected' : '' ?>>Bangladesh</option>
                                    <option value="Bhutan" data-flag="🇧🇹" <?= isset($country) && $country === 'Bhutan' ? 'selected' : '' ?>>Bhutan</option>
                                    <option value="Brunei" data-flag="🇧🇳" <?= isset($country) && $country === 'Brunei' ? 'selected' : '' ?>>Brunei</option>
                                    <option value="Cambodia" data-flag="🇰🇭" <?= isset($country) && $country === 'Cambodia' ? 'selected' : '' ?>>Cambodia</option>
                                    <option value="China" data-flag="🇨🇳" <?= isset($country) && $country === 'China' ? 'selected' : '' ?>>China</option>
                                    <option value="Cyprus" data-flag="🇨🇾" <?= isset($country) && $country === 'Cyprus' ? 'selected' : '' ?>>Cyprus</option>
                                    <option value="Georgia" data-flag="🇬🇪" <?= isset($country) && $country === 'Georgia' ? 'selected' : '' ?>>Georgia</option>
                                    <option value="Hong Kong" data-flag="🇭🇰" <?= isset($country) && $country === 'Hong Kong' ? 'selected' : '' ?>>Hong Kong</option>
                                    <option value="India" data-flag="🇮🇳" <?= isset($country) && $country === 'India' ? 'selected' : '' ?>>India</option>
                                    <option value="Indonesia" data-flag="🇮🇩" <?= isset($country) && $country === 'Indonesia' ? 'selected' : '' ?>>Indonesia</option>
                                    <option value="Iran" data-flag="🇮🇷" <?= isset($country) && $country === 'Iran' ? 'selected' : '' ?>>Iran</option>
                                    <option value="Iraq" data-flag="🇮🇶" <?= isset($country) && $country === 'Iraq' ? 'selected' : '' ?>>Iraq</option>
                                    <option value="Israel" data-flag="🇮🇱" <?= isset($country) && $country === 'Israel' ? 'selected' : '' ?>>Israel</option>
                                    <option value="Japan" data-flag="🇯🇵" <?= isset($country) && $country === 'Japan' ? 'selected' : '' ?>>Japan</option>
                                    <option value="Jordan" data-flag="🇯🇴" <?= isset($country) && $country === 'Jordan' ? 'selected' : '' ?>>Jordan</option>
                                    <option value="Kazakhstan" data-flag="🇰🇿" <?= isset($country) && $country === 'Kazakhstan' ? 'selected' : '' ?>>Kazakhstan</option>
                                    <option value="Kuwait" data-flag="🇰🇼" <?= isset($country) && $country === 'Kuwait' ? 'selected' : '' ?>>Kuwait</option>
                                    <option value="Kyrgyzstan" data-flag="🇰🇬" <?= isset($country) && $country === 'Kyrgyzstan' ? 'selected' : '' ?>>Kyrgyzstan</option>
                                    <option value="Laos" data-flag="🇱🇦" <?= isset($country) && $country === 'Laos' ? 'selected' : '' ?>>Laos</option>
                                    <option value="Lebanon" data-flag="🇱🇧" <?= isset($country) && $country === 'Lebanon' ? 'selected' : '' ?>>Lebanon</option>
                                    <option value="Macau" data-flag="🇲🇴" <?= isset($country) && $country === 'Macau' ? 'selected' : '' ?>>Macau</option>
                                    <option value="Malaysia" data-flag="🇲🇾" <?= isset($country) && $country === 'Malaysia' ? 'selected' : '' ?>>Malaysia</option>
                                    <option value="Maldives" data-flag="🇲🇻" <?= isset($country) && $country === 'Maldives' ? 'selected' : '' ?>>Maldives</option>
                                    <option value="Mongolia" data-flag="🇲🇳" <?= isset($country) && $country === 'Mongolia' ? 'selected' : '' ?>>Mongolia</option>
                                    <option value="Myanmar" data-flag="🇲🇲" <?= isset($country) && $country === 'Myanmar' ? 'selected' : '' ?>>Myanmar</option>
                                    <option value="Nepal" data-flag="🇳🇵" <?= isset($country) && $country === 'Nepal' ? 'selected' : '' ?>>Nepal</option>
                                    <option value="North Korea" data-flag="🇰🇵" <?= isset($country) && $country === 'North Korea' ? 'selected' : '' ?>>North Korea</option>
                                    <option value="Oman" data-flag="🇴🇲" <?= isset($country) && $country === 'Oman' ? 'selected' : '' ?>>Oman</option>
                                    <option value="Pakistan" data-flag="🇵🇰" <?= isset($country) && $country === 'Pakistan' ? 'selected' : '' ?>>Pakistan</option>
                                    <option value="Palestine" data-flag="🇵🇸" <?= isset($country) && $country === 'Palestine' ? 'selected' : '' ?>>Palestine</option>
                                    <option value="Philippines" data-flag="🇵🇭" <?= isset($country) && $country === 'Philippines' ? 'selected' : '' ?>>Philippines</option>
                                    <option value="Qatar" data-flag="🇶🇦" <?= isset($country) && $country === 'Qatar' ? 'selected' : '' ?>>Qatar</option>
                                    <option value="Saudi Arabia" data-flag="🇸🇦" <?= isset($country) && $country === 'Saudi Arabia' ? 'selected' : '' ?>>Saudi Arabia</option>
                                    <option value="Singapore" data-flag="🇸🇬" <?= isset($country) && $country === 'Singapore' ? 'selected' : '' ?>>Singapore</option>
                                    <option value="South Korea" data-flag="🇰🇷" <?= isset($country) && $country === 'South Korea' ? 'selected' : '' ?>>South Korea</option>
                                    <option value="Sri Lanka" data-flag="🇱🇰" <?= isset($country) && $country === 'Sri Lanka' ? 'selected' : '' ?>>Sri Lanka</option>
                                    <option value="Syria" data-flag="🇸🇾" <?= isset($country) && $country === 'Syria' ? 'selected' : '' ?>>Syria</option>
                                    <option value="Taiwan" data-flag="🇹🇼" <?= isset($country) && $country === 'Taiwan' ? 'selected' : '' ?>>Taiwan</option>
                                    <option value="Tajikistan" data-flag="🇹🇯" <?= isset($country) && $country === 'Tajikistan' ? 'selected' : '' ?>>Tajikistan</option>
                                    <option value="Thailand" data-flag="🇹🇭" <?= isset($country) && $country === 'Thailand' ? 'selected' : '' ?>>Thailand</option>
                                    <option value="Timor-Leste" data-flag="🇹🇱" <?= isset($country) && $country === 'Timor-Leste' ? 'selected' : '' ?>>Timor-Leste</option>
                                    <option value="Turkey" data-flag="🇹🇷" <?= isset($country) && $country === 'Turkey' ? 'selected' : '' ?>>Turkey</option>
                                    <option value="Turkmenistan" data-flag="🇹🇲" <?= isset($country) && $country === 'Turkmenistan' ? 'selected' : '' ?>>Turkmenistan</option>
                                    <option value="United Arab Emirates" data-flag="🇦🇪" <?= isset($country) && $country === 'United Arab Emirates' ? 'selected' : '' ?>>United Arab Emirates</option>
                                    <option value="Uzbekistan" data-flag="🇺🇿" <?= isset($country) && $country === 'Uzbekistan' ? 'selected' : '' ?>>Uzbekistan</option>
                                    <option value="Vietnam" data-flag="🇻🇳" <?= isset($country) && $country === 'Vietnam' ? 'selected' : '' ?>>Vietnam</option>
                                    <option value="Yemen" data-flag="🇾🇪" <?= isset($country) && $country === 'Yemen' ? 'selected' : '' ?>>Yemen</option>
                                </select>
                                <?php if (isset($errorMessages['country'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['country'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold">Password* <span class="text-muted small">(min 8 characters)</span></label>
                                <input type="password" name="password" id="password" class="form-control rounded-3" required>
                                <div class="password-strength mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="password-strength-text" class="form-text text-muted mt-1"></small>
                                </div>
                                <?php if (isset($errorMessages['password'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['password'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm Password*</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control rounded-3" required>
                                <?php if (isset($errorMessages['confirm_password'])): ?>
                                    <div class="invalid-feedback d-block"><?= $errorMessages['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-check mt-4">
                            <input type="checkbox" name="terms" id="terms" class="form-check-input" required <?= isset($terms) && $terms ? 'checked' : '' ?>>
                            <label for="terms" class="form-check-label">I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> *</label>
                            <?php if (isset($errorMessages['terms'])): ?>
                                <div class="invalid-feedback d-block"><?= $errorMessages['terms'] ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" <?= isset($remember) && $remember ? 'checked' : '' ?>>
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 mt-4" id="registerBtn" disabled>Register</button>
                    </form>
                    <div class="text-center mt-3">
                        <span class="text-muted">Already have an account?</span> <a href="login.php" class="fw-semibold">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Terms and Agreement Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms of Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Please read the Terms of Service carefully. Scroll to the bottom to enable the accept button.
                </div>

                <div class="terms-content">
                    <h6 class="fw-bold mb-3">1. Acceptance of Terms</h6>
                    <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>

                    <h6 class="fw-bold mb-3">2. Account Registration and Security</h6>
                    <p>To use certain features of the service, you must register for an account. You agree to:</p>
                    <ul>
                        <li>Provide accurate and complete information during registration</li>
                        <li>Maintain the security of your account credentials</li>
                        <li>Notify us immediately of any unauthorized access</li>
                        <li>Accept responsibility for all activities under your account</li>
                    </ul>

                    <h6 class="fw-bold mb-3">3. User Conduct</h6>
                    <p>You agree not to:</p>
                    <ul>
                        <li>Use the service for any illegal purpose</li>
                        <li>Violate any laws or regulations</li>
                        <li>Infringe on the rights of others</li>
                        <li>Attempt to gain unauthorized access</li>
                        <li>Interfere with the proper functioning of the service</li>
                    </ul>

                    <h6 class="fw-bold mb-3">4. Privacy and Data Protection</h6>
                    <p>We collect and process your personal information in accordance with our Privacy Policy. By using our services, you consent to such processing and warrant that all data provided by you is accurate.</p>

                    <h6 class="fw-bold mb-3">5. Intellectual Property</h6>
                    <p>All content, features, and functionality of the service are owned by us and are protected by international copyright, trademark, and other intellectual property laws.</p>

                    <h6 class="fw-bold mb-3">6. Service Modifications</h6>
                    <p>We reserve the right to modify or discontinue the service at any time without notice. We shall not be liable to you or any third party for any modification, suspension, or discontinuance of the service.</p>

                    <h6 class="fw-bold mb-3">7. Limitation of Liability</h6>
                    <p>To the maximum extent permitted by law, we shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the service.</p>

                    <h6 class="fw-bold mb-3">8. Termination</h6>
                    <p>We may terminate or suspend your account and access to the service immediately, without prior notice, for any reason, including breach of these Terms of Service.</p>

                    <h6 class="fw-bold mb-3">9. Governing Law</h6>
                    <p>These terms shall be governed by and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions.</p>

                    <h6 class="fw-bold mb-3">10. Changes to Terms</h6>
                    <p>We reserve the right to modify these terms at any time. We will notify users of any changes by updating the date at the top of these terms and by maintaining a changelog.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="acceptTerms" data-bs-dismiss="modal" disabled>
                    <span id="acceptButtonText">Please read the terms first</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');
    let strength = 0;
    let feedback = '';

    if (password.length >= 8) strength += 20;
    if (password.match(/[A-Z]/)) strength += 20;
    if (password.match(/[a-z]/)) strength += 20;
    if (password.match(/[0-9]/)) strength += 20;
    if (password.match(/[^A-Za-z0-9]/)) strength += 20;

    strengthBar.style.width = strength + '%';
    if (strength < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        feedback = 'Weak';
    } else if (strength < 80) {
        strengthBar.className = 'progress-bar bg-warning';
        feedback = 'Medium';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        feedback = 'Strong';
    }
    strengthText.textContent = feedback;
});

// Terms and Agreement handling
let hasReadTerms = false;

document.getElementById('terms').addEventListener('change', function() {
    if (!hasReadTerms) {
        alert('Please read the terms and conditions first by clicking the link.');
        this.checked = false;
        return;
    }
    document.getElementById('registerBtn').disabled = !this.checked;
});

document.getElementById('acceptTerms').addEventListener('click', function() {
    hasReadTerms = true;
    document.getElementById('terms').checked = true;
    document.getElementById('registerBtn').disabled = false;
});

// Handle modal events
const termsModal = document.getElementById('termsModal');
const modalBody = termsModal.querySelector('.modal-body');
const acceptButton = document.getElementById('acceptTerms');
const acceptButtonText = document.getElementById('acceptButtonText');

termsModal.addEventListener('show.bs.modal', function () {
    // Reset state
    acceptButton.disabled = true;
    acceptButtonText.textContent = 'Please read the terms first';
    hasReadTerms = false;
});

modalBody.addEventListener('scroll', function() {
    const scrollPosition = this.scrollTop + this.clientHeight;
    const scrollHeight = this.scrollHeight;
    
    // Enable accept button when user has scrolled to bottom
    if (scrollPosition >= scrollHeight - 10) {
        acceptButton.disabled = false;
        acceptButtonText.textContent = 'I Accept';
    }
});
</script>

<style>
.auth-section {
    background: var(--light-bg, #f8f9fa);
    min-height: 100vh;
}
.card {
    border-radius: 1.25rem;
}
.invalid-feedback {
    font-size: 0.95rem;
    margin-top: 0.25rem;
}
/* Country Select Styles */
.form-select {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    line-height: 1.5;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: 0.75rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
}
/* Flag emoji styles */
.form-select option {
    padding: 0.5rem 1rem;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-select option::before {
    content: attr(data-flag);
    font-size: 1.2em;
    margin-right: 0.5rem;
}
/* Custom select arrow */
.form-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23666' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px 12px;
    padding-right: 2.5rem;
}
/* Selected option style */
.form-select option:checked {
    background-color: var(--primary-color);
    color: white;
}
/* Hover state */
.form-select option:hover {
    background-color: #f8f9fa;
}
.terms-content {
    font-size: 0.95rem;
    line-height: 1.6;
}
.terms-content h6 {
    color: #2c3e50;
    margin-top: 1.5rem;
}
.terms-content ul {
    padding-left: 1.2rem;
}
.terms-content ul li {
    margin-bottom: 0.5rem;
}
.terms-content p {
    margin-bottom: 1rem;
    color: #4a5568;
}
</style>

<?php include 'includes/footer.php'; ?>