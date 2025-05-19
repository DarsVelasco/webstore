<?php
// Prevent constant redefinitions
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'root');
if (!defined('DB_NAME')) define('DB_NAME', 'ecommerce');

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create database connection if not already set
if (!isset($conn)) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
}

// Site config constants
if (!defined('SITE_NAME')) define('SITE_NAME', 'E-Commerce Store');
if (!defined('SITE_URL')) define('SITE_URL', '/webstore');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
