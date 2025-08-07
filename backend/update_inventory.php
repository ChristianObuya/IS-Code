<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Security: Only logged-in staff can update inventory.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$itemID = (int)($_POST['itemID'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if ($itemID <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid item ID or quantity provided.']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Inventory (itemID, stockQuantity, lowStockThreshold)
         VALUES (?, ?, 5)
         ON DUPLICATE KEY UPDATE stockQuantity = stockQuantity + VALUES(stockQuantity)"
    );
    $stmt->execute([$itemID, $quantity]);

    // This query will either insert a new row or update an existing one.
    // It will only fail if there's a database constraint error (e.g., foreign key).
    echo json_encode(['success' => true, 'message' => 'Stock updated successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Update inventory failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>
