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
            m.name, 
            m.itemID, 
            m.available,
            IFNULL(i.stockQuantity, 0) as stockQuantity, 
            IFNULL(i.lowStockThreshold, 5) as lowStockThreshold 
        FROM MenuItem m
        LEFT JOIN Inventory i ON m.itemID = i.itemID
        ORDER BY m.available DESC, m.name
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
