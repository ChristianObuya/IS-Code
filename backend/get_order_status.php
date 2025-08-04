<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// 1. Check if user is logged in
if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in.']);
    exit;
}

$userID = (int)$_SESSION['userID'];
$role = $_SESSION['role'];

// 2. Get and validate input
$orderID = (int)($_GET['orderID'] ?? 0);

if ($orderID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
    exit;
}

try {
    // 3. Fetch order and verify ownership
    $stmt = $pdo->prepare("SELECT status, studentID, orderTime, completionTime FROM `Order` WHERE orderID = ?");
    $stmt->execute([$orderID]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found.']);
        exit;
    }

    // A student can only see their own order status. Staff can see any.
    if ($role === 'student' && $order['studentID'] != $userID) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not authorized to view this order.']);
        exit;
    }

    // 4. Return status
    echo json_encode([
        'success' => true, 
        'status' => $order['status'],
        'orderTime' => $order['orderTime'],
        'completionTime' => $order['completionTime']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Get order status failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>
