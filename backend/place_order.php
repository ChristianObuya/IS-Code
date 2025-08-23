<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    echo "Access denied";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['userID'])) {
        echo "Invalid session";
        exit();
    }

    $totalAmount = (float)($_POST['totalAmount'] ?? 0);
    $itemIDs = $_POST['item_ids'] ?? [];
    $quantities = $_POST['item_quantities'] ?? [];

    if ($totalAmount <= 0) {
        echo "Invalid amount";
        exit();
    }

    if (empty($itemIDs) || empty($quantities) || count($itemIDs) !== count($quantities)) {
        echo "No items";
        exit();
    }

    // Build items array
    $items = [];
    for ($i = 0; $i < count($itemIDs); $i++) {
        $id = (int)$itemIDs[$i];
        $qty = (int)$quantities[$i];
        if ($id > 0 && $qty > 0) {
            $items[] = ['id' => $id, 'quantity' => $qty];
        }
    }

    if (empty($items)) {
        echo "No valid items";
        exit();
    }

    // Check inventory stock first
    $hasEnoughStock = true;
    foreach ($items as $item) {
        $itemID = (int)$item['id'];
        $quantity = (int)$item['quantity'];
        
        $stockQuery = "SELECT i.stockQuantity FROM Inventory i WHERE i.itemID = $itemID";
        $stockResult = mysqli_query($connectdb, $stockQuery);
        
        if ($stockRow = mysqli_fetch_assoc($stockResult)) {
            $currentStock = (int)$stockRow['stockQuantity'];
            if ($quantity > $currentStock) {
                $hasEnoughStock = false;
                break;
            }
        } else {
            $hasEnoughStock = false;
            break;
        }
    }

    if (!$hasEnoughStock) {
        echo "Insufficient stock, please readjust your cart";
        exit();
    }

    // Create order
    $studentID = $_SESSION['userID'];
    $sql = "INSERT INTO `Order` (studentID, totalAmount, status) VALUES ('$studentID', '$totalAmount', 'pending')";
    
    if (mysqli_query($connectdb, $sql)) {
        $orderID = mysqli_insert_id($connectdb);

        // Add order items and update inventory
        foreach ($items as $item) {
            $itemID = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            
            if ($quantity > 0) {
                // Insert order item
                $itemSql = "INSERT INTO OrderItem (orderID, itemID, quantity) VALUES ($orderID, $itemID, $quantity)";
                mysqli_query($connectdb, $itemSql);
                
                // Update inventory
                $updateSql = "UPDATE Inventory SET stockQuantity = stockQuantity - $quantity WHERE itemID = $itemID";
                mysqli_query($connectdb, $updateSql);
            }
        }

        echo "success|$orderID";
    } else {
        echo "Failed to create order";
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orderID = $_GET['orderID'] ?? null;
    if (!$orderID || !is_numeric($orderID)) {
        echo "not_found";
        exit();
    }

    $studentID = $_SESSION['userID'];

    $query = "
        SELECT o.orderID, o.totalAmount,
               GROUP_CONCAT(
                   CONCAT('{\"name\":\"', m.name, '\",\"price\":', m.price, ',\"quantity\":', oi.quantity, '}')
               ) AS items_json
        FROM `Order` o
        JOIN OrderItem oi ON o.orderID = oi.orderID
        JOIN MenuItem m ON oi.itemID = m.itemID
        WHERE o.orderID = $orderID AND o.studentID = $studentID
        GROUP BY o.orderID
    ";

    $result = mysqli_query($connectdb, $query);
    if ($row = mysqli_fetch_assoc($result)) {
        $items = '[' . $row['items_json'] . ']';
        echo $row['orderID'] . '|' . $row['totalAmount'] . '|' . urlencode($items);
    } else {
        echo "not_found";
    }
}
?>