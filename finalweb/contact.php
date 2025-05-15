<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        // Save to database
        if (saveContactMessage($name, $email, $subject, $message)) {
            $successMessage = 'Thank you for your message! We will get back to you soon.';
            // Clear form
            $name = $email = $subject = $message = '';
        } else {
            $errorMessage = 'There was an error submitting your message. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<section class="contact-section">
    <div class="container">
        <h1>Contact Us</h1>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php elseif ($errorMessage): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <div class="contact-info">
                <h2>Get in Touch</h2>
                <p>Have questions or feedback? We'd love to hear from you!</p>
                
                <div class="contact-method">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>support@ecommercestore.com</p>
                </div>
                
                <div class="contact-method">
                    <i class="fas fa-phone"></i>
                    <h3>Phone</h3>
                    <p>+1 (555) 123-4567</p>
                </div>
                
                <div class="contact-method">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Address</h3>
                    <p>123 Main Street<br>New York, NY 10001</p>
                </div>
                
                <div class="business-hours">
                    <h3>Business Hours</h3>
                    <p>Monday - Friday: 9am - 6pm</p>
                    <p>Saturday: 10am - 4pm</p>
                    <p>Sunday: Closed</p>
                </div>
            </div>
            
            <div class="contact-form">
                <h2>Send Us a Message</h2>
                <form method="post" action="contact.php">
                    <div class="form-group">
                        <label for="name">Name*</label>
                        <input type="text" name="name" id="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" name="email" id="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" value="<?= isset($subject) ? htmlspecialchars($subject) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message*</label>
                        <textarea name="message" id="message" rows="5" required><?= isset($message) ? htmlspecialchars($message) : '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>