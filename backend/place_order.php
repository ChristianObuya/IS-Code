<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Security: User must be a logged-in student to place an order.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Please log in as a student.']);
    exit;
}

// Validate required fields
if (!isset($input['items']) || !isset($input['totalAmount'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

$studentID = (int)$_SESSION['userID']; // Use session for security
$totalAmount = floatval($input['totalAmount']);
$items = $input['items'];

try {
    // Start transaction
    $pdo->beginTransaction();

    // Verify student exists
    $stmt = $pdo->prepare("SELECT * FROM Student WHERE studentID = ?");
    $stmt->execute([$studentID]);
    if ($stmt->rowCount() === 0) {
        throw new Exception("Invalid student ID: $studentID");
    }

    // Insert into `Order`
    $stmt = $pdo->prepare("INSERT INTO `Order` (studentID, totalAmount, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$studentID, $totalAmount]);
    $orderID = $pdo->lastInsertId();

    // Insert each item into `OrderItem`
    $itemStmt = $pdo->prepare("INSERT INTO OrderItem (orderID, itemID, quantity) VALUES (?, ?, ?)");
    foreach ($items as $item) {
        $itemID = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        if ($quantity > 0) {
            $itemStmt->execute([$orderID, $itemID, $quantity]);
        }
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'orderID' => $orderID,
        'message' => 'Order placed successfully.'
    ]);

} catch (Exception $e) {
    $pdo->rollback();
    error_log("Order placement failed: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to place order: ' . $e->getMessage()
    ]);
}
?>