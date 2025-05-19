<?php
// Start output buffering to catch any unwanted output
ob_start();

// Prevent any output before our JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../includes/functions.php';
require_once '../includes/connection.php';

// Clean any output buffered so far
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Redirect if not admin
if (!isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Function to send JSON response and exit
function sendJsonResponse($success, $message = '') {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: users.php");
    exit();
}

// Get and validate input parameters
$orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? strtolower(trim($_POST['status'])) : '';
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

// Check if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Validate required fields
if (!$orderId || !$status) {
    if ($isAjax) {
        sendJsonResponse(false, "Missing required fields.");
    } else {
        $_SESSION['error'] = "Missing required fields.";
        header("Location: view-orders.php" . ($userId ? "?user_id=" . $userId : ""));
        exit();
    }
}

// Validate status
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $validStatuses)) {
    if ($isAjax) {
        sendJsonResponse(false, "Invalid status value.");
    } else {
        $_SESSION['error'] = "Invalid status value.";
        header("Location: view-orders.php" . ($userId ? "?user_id=" . $userId : ""));
        exit();
    }
}

try {
    $conn = getDBConnection();
    
    // Check if order exists
    $check = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    $check->bind_param('i', $orderId);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        if ($isAjax) {
            sendJsonResponse(false, "Order not found.");
        } else {
            $_SESSION['error'] = "Order not found.";
            header("Location: view-orders.php" . ($userId ? "?user_id=" . $userId : ""));
            exit();
        }
    }
    $check->close();

    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        if ($isAjax) {
            sendJsonResponse(true, "Order status updated successfully.");
        } else {
            $_SESSION['success'] = "Order status updated successfully.";
        }
    } else {
        if ($isAjax) {
            sendJsonResponse(false, "No changes made to order status.");
        } else {
            $_SESSION['error'] = "No changes made to order status.";
        }
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    if ($isAjax) {
        sendJsonResponse(false, "An error occurred while updating the order status.");
    } else {
        $_SESSION['error'] = "An error occurred while updating the order status.";
    }
}

// Only redirect for non-AJAX requests
if (!$isAjax) {
    header("Location: view-orders.php" . ($userId ? "?user_id=" . $userId : ""));
    exit();
}

// If we get here with an AJAX request, send success response
if ($isAjax) {
    sendJsonResponse(true, "Order status updated successfully.");
}

// Check if user is logged in
if (!isLoggedIn()) {
    sendJsonResponse(false, 'Unauthorized access');
}

// Debug: Log incoming data
error_log('POST data: ' . print_r($_POST, true));

// Check if required data is provided
if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    sendJsonResponse(false, 'Missing required data - Order ID: ' . 
        (isset($_POST['order_id']) ? $_POST['order_id'] : 'not set') . 
        ', Status: ' . (isset($_POST['status']) ? $_POST['status'] : 'not set'));
}

$order_id = (int)$_POST['order_id'];
$status = strtolower(trim($_POST['status'])); // Clean the status input

// Debug: Log processed data
error_log('Processed data - Order ID: ' . $order_id . ', Status: ' . $status);

// Validate status - using shorter versions only
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!in_array($status, $valid_statuses)) {
    sendJsonResponse(false, 'Invalid status: ' . $status);
}

try {
    // First, let's check if the order exists
    $check = $conn->prepare("SELECT order_id FROM orders WHERE order_id = ?");
    $check->bind_param('i', $order_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        sendJsonResponse(false, 'Order ID not found: ' . $order_id);
    }
    $check->close();

    // Now try to update
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    if (!$stmt) {
        sendJsonResponse(false, 'Failed to prepare statement: ' . $conn->error);
    }

    // Debug: Log the query
    error_log('Updating order ' . $order_id . ' to status: ' . $status);

    // Bind parameters
    $stmt->bind_param('si', $status, $order_id);
    
    // Execute the statement
    $result = $stmt->execute();

    if ($result) {
        // Check if any rows were actually updated
        if ($stmt->affected_rows > 0) {
            sendJsonResponse(true, 'Status updated successfully');
        } else {
            sendJsonResponse(false, 'No changes made to order status');
        }
    } else {
        sendJsonResponse(false, 'Database error: ' . $stmt->error);
    }
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    sendJsonResponse(false, 'Database error: ' . $e->getMessage());
} 