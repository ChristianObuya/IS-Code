<?php
// Start session to get logged-in staff ID
session_start();

require_once 'config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Staff login required.'
    ]);
    exit;
}

// Get staff ID from session
$staffID = (int)$_SESSION['userID'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$orderID = (int)($_POST['orderID'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($orderID <= 0 || !in_array($status, ['pending', 'preparing', 'ready', 'collected'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Update order status and set who updated it
    $stmt = $pdo->prepare("UPDATE `Order` SET status = ?, updatedByStaffID = ? WHERE orderID = ?");
    $result = $stmt->execute([$status, $staffID, $orderID]);

    if ($result && $stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'updatedBy' => $staffID
        ]);
    } else {
        $pdo->rollback();
        // Order not found or no change
        echo json_encode([
            'success' => false,
            'message' => 'Order not found or no update made.'
        ]);
    }

} catch (Exception $e) {
    $pdo->rollback();
    error_log("Status update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error. Could not update status.'
    ]);
}
?>