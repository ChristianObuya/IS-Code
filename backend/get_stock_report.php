<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Security: Only logged-in staff can view inventory reports
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

try {
    // --- 1. Stock Data Query ---
    $stmt = $pdo->prepare("
        SELECT 
            i.itemID,
            m.name AS itemName,
            i.stockQuantity,
            i.lowStockThreshold
        FROM Inventory i
        JOIN MenuItem m ON i.itemID = m.itemID
        ORDER BY i.stockQuantity ASC  -- Show low stock first
    ");
    $stmt->execute();
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 2. Return Report ---
    echo json_encode([
        'success' => true,
        'data' => $stockData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Stock report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to generate stock report.']);
}
?>

