<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

include 'includes/header.php';
?>

<div class="main-rounded-container">
  <section class="text-center mb-4">
    <h1 class="mb-4">About Us</h1>
    <p class="lead mx-auto mb-5" style="max-width: 700px; color: var(--text-light);">
      Welcome to <?php echo SITE_NAME; ?>! We are your one-stop shop for all your needs, offering quality products at affordable prices. Our mission is to provide a seamless and enjoyable shopping experience for everyone.
    </p>
    <div class="container">
      <div class="row justify-content-center g-4">
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm rounded-4">
            <div class="card-body">
              <h3 class="card-title mb-3">Our Story</h3>
              <p class="card-text">Founded in 2023, we have quickly grown to become a trusted name in e-commerce. Our team is passionate about curating the best products and delivering them to your doorstep.</p>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm rounded-4">
            <div class="card-body">
              <h3 class="card-title mb-3">Our Values</h3>
              <ul class="card-text text-start ps-3">
                <li>Quality &amp; Affordability</li>
                <li>Customer Satisfaction</li>
                <li>Fast &amp; Reliable Delivery</li>
                <li>Secure Shopping</li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 shadow-sm rounded-4">
            <div class="card-body">
              <h3 class="card-title mb-3">Contact Us</h3>
              <p class="card-text">Have questions? <a href="contact.php" class="link-primary text-decoration-underline">Contact our team</a> and we'll be happy to help!</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>