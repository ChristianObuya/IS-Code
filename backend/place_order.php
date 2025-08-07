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

    // --- Stock Verification Step ---
    // Lock inventory rows for the items in the cart to prevent race conditions
    $itemIDs = array_map(fn($item) => (int)$item['id'], $items);
    $placeholders = implode(',', array_fill(0, count($itemIDs), '?'));
    
    $stockCheckStmt = $pdo->prepare("SELECT itemID, stockQuantity FROM Inventory WHERE itemID IN ($placeholders) FOR UPDATE");
    $stockCheckStmt->execute($itemIDs);
    $currentStock = $stockCheckStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($items as $item) {
        $itemID = (int)$item['id'];
        $quantityNeeded = (int)$item['quantity'];
        if (!isset($currentStock[$itemID]) || $currentStock[$itemID] < $quantityNeeded) {
            throw new Exception("This item is currently out of stock, please adjust your cart.");
        }
    }

    // --- Create Order and OrderItems (if stock is sufficient) ---
    $stmt = $pdo->prepare("INSERT INTO `Order` (studentID, totalAmount, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$studentID, $totalAmount]);
    $orderID = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO OrderItem (orderID, itemID, quantity) VALUES (?, ?, ?)");
    $stockUpdateStmt = $pdo->prepare("UPDATE Inventory SET stockQuantity = stockQuantity - ? WHERE itemID = ?");

    foreach ($items as $item) {
        $itemID = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        if ($quantity > 0) {
            $itemStmt->execute([$orderID, $itemID, $quantity]);
            $stockUpdateStmt->execute([$quantity, $itemID]); // Decrease stock
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
        'message' => $e->getMessage()
    ]);
}
?>