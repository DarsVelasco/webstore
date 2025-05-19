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
<div class="main-rounded-container">
  <section class="text-center mb-4">
    <h1 class="mb-4">Contact Us</h1>
    <p class="lead mx-auto mb-5" style="max-width: 700px; color: var(--text-light);">
      Have a question or need support? Fill out the form below and our team will get back to you as soon as possible.
    </p>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
          <div class="card shadow-sm rounded-4">
            <div class="card-body p-4">
              <form action="contact.php" method="post">
                <div class="mb-3 text-start">
                  <label for="name" class="form-label">Your Name</label>
                  <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3 text-start">
                  <label for="email" class="form-label">Your Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3 text-start">
                  <label for="message" class="form-label">Message</label>
                  <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Message</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php include 'includes/footer.php'; ?>