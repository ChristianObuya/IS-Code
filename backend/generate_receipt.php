<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$orderID = (int)($_POST['orderID'] ?? 0);
$totalAmount = floatval($_POST['totalAmount'] ?? 0);

if ($orderID <= 0 || $totalAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid receipt data.']);
    exit;
}

try {
    // Check if receipt already exists
    $stmt = $pdo->prepare("SELECT * FROM Receipt WHERE orderID = ?");
    $stmt->execute([$orderID]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Receipt already exists.']);
        exit;
    }

    // Insert receipt
    $stmt = $pdo->prepare("INSERT INTO Receipt (orderID, totalAmount) VALUES (?, ?)");
    $stmt->execute([$orderID, $totalAmount]);

    echo json_encode([
        'success' => true,
        'receiptID' => $pdo->lastInsertId(),
        'message' => 'Receipt generated.'
    ]);

} catch (Exception $e) {
    error_log("Receipt generation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to generate receipt.']);
}
?>