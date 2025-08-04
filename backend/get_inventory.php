<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Security: Only logged-in staff can view inventory.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            i.itemID, 
            m.name, 
            i.stockQuantity, 
            i.lowStockThreshold 
        FROM Inventory i
        JOIN MenuItem m ON i.itemID = m.itemID
        ORDER BY m.name
    ");
    $stmt->execute();
    $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $inventory]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Get inventory failed: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load inventory.']);
}
?>

