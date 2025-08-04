<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderID = (int)($input['orderID'] ?? 0);
$transactionID = trim($input['transactionID'] ?? '');

if ($orderID <= 0 || empty($transactionID)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit;
}

try {
    // We also verify the studentID matches the session userID for extra security
    $stmt = $pdo->prepare("UPDATE `Order` SET transactionID = ? WHERE orderID = ? AND studentID = ?");
    $stmt->execute([$transactionID, $orderID, (int)$_SESSION['userID']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Payment confirmed.']);
    } else {
        // This will catch the case where the orderID doesn't exist or doesn't belong to the user
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Order not found. Could not confirm payment.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Payment confirmation failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>