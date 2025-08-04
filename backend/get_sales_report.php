<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Security: Only logged-in staff can view reports
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

try {
    // --- 1. Date Range Input ---
    $startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-7 days')); // Default: last 7 days
    $endDate = $_GET['endDate'] ?? date('Y-m-d');

    // Basic validation (more robust validation is recommended)
    if (strtotime($startDate) === false || strtotime($endDate) === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format.']);
        exit;
    }

    // --- 2. Sales Data Query ---
    $stmt = $pdo->prepare("
        SELECT 
            DATE(o.orderTime) as saleDate,
            SUM(o.totalAmount) as totalSales,
            COUNT(o.orderID) as orderCount
        FROM `Order` o
        WHERE o.orderTime BETWEEN ? AND ?
        AND o.status = 'collected'  -- Consider only completed orders
        GROUP BY DATE(o.orderTime)
        ORDER BY DATE(o.orderTime)
    ");

    // Convert to datetime for the database
    $dbStartDate = $startDate . " 00:00:00";
    $dbEndDate = $endDate . " 23:59:59";

    $stmt->execute([$dbStartDate, $dbEndDate]);
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. Return Report ---
    echo json_encode([
        'success' => true,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'data' => $salesData
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("Sales report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to generate sales report.']);
}
?>

