<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

include 'includes/header.php';
?>

<section class="about-section">
    <div class="container">
        <h1>About Our Company</h1>
        
        <div class="about-content">
            <div class="about-image">
                <img src="images/about-us.jpg" alt="Our Team">
            </div>
            <div class="about-text">
                <h2>Our Story</h2>
                <p>Founded in 2010, our e-commerce store has been dedicated to providing high-quality products at affordable prices. What started as a small family business has grown into a trusted online destination for thousands of customers worldwide.</p>
                
                <h2>Our Mission</h2>
                <p>We strive to make online shopping easy, enjoyable, and accessible to everyone. Our mission is to connect customers with the products they need while providing exceptional service and support.</p>
                
                <h2>Our Values</h2>
                <ul>
                    <li>Customer Satisfaction Above All</li>
                    <li>Quality Products at Fair Prices</li>
                    <li>Fast and Reliable Shipping</li>
                    <li>Transparent and Honest Business Practices</li>
                </ul>
            </div>
        </div>
        
        <div class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <img src="images/team1.jpg" alt="Team Member">
                    <h3>John Doe</h3>
                    <p>Founder & CEO</p>
                </div>
                <div class="team-member">
                    <img src="images/team2.jpg" alt="Team Member">
                    <h3>Jane Smith</h3>
                    <p>Head of Operations</p>
                </div>
                <div class="team-member">
                    <img src="images/team3.jpg" alt="Team Member">
                    <h3>Mike Johnson</h3>
                    <p>Customer Support</p>
                </div>
                <div class="team-member">
                    <img src="images/team4.jpg" alt="Team Member">
                    <h3>Sarah Williams</h3>
                    <p>Marketing Director</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>