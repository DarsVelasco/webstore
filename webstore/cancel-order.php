<?php
require_once 'includes/functions.php';
require_once 'includes/connection.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    
    try {
        if (cancelOrder($userId, $orderId)) {
            $response = [
                'success' => true,
                'message' => 'Order cancelled successfully'
            ];
        }
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Unable to cancel order. ' . $e->getMessage()
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response); 