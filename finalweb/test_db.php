<?php
require_once 'includes/connection.php';

function testDatabaseConnection() {
    global $conn;
    
    echo "<h2>Database Connection Test</h2>";
    
    // Test connection
    if ($conn->connect_error) {
        echo "Connection failed: " . $conn->connect_error;
        return;
    }
    echo "Database connection successful!<br>";
    
    // Test products table
    $result = $conn->query("SHOW TABLES LIKE 'products'");
    if ($result->num_rows > 0) {
        echo "Products table exists<br>";
        
        // Check products structure
        $result = $conn->query("DESCRIBE products");
        echo "<h3>Products Table Structure:</h3>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
        
        // Count products
        $result = $conn->query("SELECT COUNT(*) as count FROM products");
        $row = $result->fetch_assoc();
        echo "Total products: " . $row['count'] . "<br>";
    } else {
        echo "Products table does not exist!<br>";
    }
    
    // Test categories table
    $result = $conn->query("SHOW TABLES LIKE 'categories'");
    if ($result->num_rows > 0) {
        echo "Categories table exists<br>";
        
        // Check categories structure
        $result = $conn->query("DESCRIBE categories");
        echo "<h3>Categories Table Structure:</h3>";
        echo "<pre>";
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
        
        // Count categories
        $result = $conn->query("SELECT COUNT(*) as count FROM categories");
        $row = $result->fetch_assoc();
        echo "Total categories: " . $row['count'] . "<br>";
    } else {
        echo "Categories table does not exist!<br>";
    }
}

// Run the test
testDatabaseConnection();
?> 