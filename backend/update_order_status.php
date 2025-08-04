<?php
session_start();
require_once 'config.php';

// === 1. Check if staff is logged in ===
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Please log in as staff.'
    ]);
    exit;
}

$staffID = (int)$_SESSION['userID'];

// === 2. Validate request method ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);
    exit;
}

// === 3. Get and validate input ===
$orderID = (int)($_POST['orderID'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($orderID <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid order ID.'
    ]);
    exit;
}

if (!in_array($status, ['pending', 'preparing', 'ready', 'collected'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value.'
    ]);
    exit;
}

// === 4. Verify order exists ===
try {
    $stmt = $pdo->prepare("SELECT * FROM `Order` WHERE orderID = ?");
    $stmt->execute([$orderID]);
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found.'
        ]);
        exit;
    }
} catch (Exception $e) {
    error_log("Order check failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error while checking order.'
    ]);
    exit;
}

// === 5. Verify staff ID exists in CanteenStaff table (for data integrity) ===
try {
    $stmt = $pdo->prepare("SELECT staffID FROM CanteenStaff WHERE staffID = ?");
    $stmt->execute([$staffID]);
    if ($stmt->rowCount() === 0) {
        error_log("Data integrity issue: Staff user with userID {$staffID} not found in CanteenStaff table.");
        echo json_encode([
            'success' => false,
            'message' => 'Staff account configuration error. Please contact an administrator.'
        ]);
        exit;
    }
} catch (Exception $e) {
    error_log("Staff validity check failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error while verifying staff account.'
    ]);
    exit;
}

// === 6. Update order status and staff ID ===
try {
    $sql = "UPDATE `Order` SET status = ?, updatedByStaffID = ?";
    $params = [$status, $staffID];

    // If status is 'ready' or 'collected', set completion time, but only if it's not already set.
    if (in_array($status, ['ready', 'collected'])) {
        $sql .= ", completionTime = IFNULL(completionTime, NOW())";
    }
    
    $sql .= " WHERE orderID = ?";
    $params[] = $orderID;

    $stmt = $pdo->prepare($sql);
    // The $staffID variable is set from the session at the top of the file
    $result = $stmt->execute($params);

    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made. Order may already be in this status.'
        ]);
    }
} catch (Exception $e) {
    error_log("Status update failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error during final update. Check browser console (F12) for details.',
        'debug_error' => $e->getMessage(),
        'debug_values' => "OrderID: {$orderID}, Status: {$status}, StaffID: {$staffID}"
    ]);
}
?>