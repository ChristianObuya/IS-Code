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
        "UPDATE Inventory SET stockQuantity = stockQuantity + ? WHERE itemID = ?"
    );
    $stmt->execute([$quantity, $itemID]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Stock updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found in inventory.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("Update inventory failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred.']);
}
?>

