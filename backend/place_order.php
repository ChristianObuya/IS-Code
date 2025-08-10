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
    if ($totalAmount <= 0) {
        echo "Invalid amount";
        exit();
    }

    $itemIDs = $_POST['item_ids'] ?? [];
    $quantities = $_POST['item_quantities'] ?? [];

    if (empty($itemIDs) || empty($quantities) || count($itemIDs) !== count($quantities)) {
        echo "No items";
        exit();
    }

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

    $sql = "INSERT INTO `Order` (studentID, totalAmount, status) VALUES ('{$_SESSION['userID']}', '$totalAmount', 'pending')";
    if (mysqli_query($connectdb, $sql)) {
        $orderID = mysqli_insert_id($connectdb);

        foreach ($items as $item) {
            $itemID = (int)$item['id'];
            $quantity = (int)$item['quantity'];
            if ($quantity > 0) {
                $itemSql = "INSERT INTO OrderItem (orderID, itemID, quantity) VALUES ($orderID, $itemID, $quantity)";
                mysqli_query($connectdb, $itemSql);
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