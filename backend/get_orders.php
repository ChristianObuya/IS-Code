<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("
        SELECT o.orderID, o.studentID, o.totalAmount, o.status, o.orderTime 
        FROM `Order` o 
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