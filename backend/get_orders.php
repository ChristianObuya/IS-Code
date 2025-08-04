<?php
session_start();
require_once 'config.php';

// Security: Only logged-in staff can view all orders.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Please log in as staff.'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT o.orderID, o.studentID, o.totalAmount, o.status, o.orderTime 
        FROM `Order` o
        WHERE o.status != 'collected'
        ORDER BY o.orderTime DESC
    ");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $orders
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load orders: ' . $e->getMessage()
    ]);
}
?>