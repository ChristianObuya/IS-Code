<?php
session_start();
include 'config.php';

if (!isset($_GET['orderID'])) {
    echo json_encode(['error' => 'Order ID required']);
    exit();
}

$orderID = (int)$_GET['orderID'];

// Check if order exists and belongs to logged in student
$studentID = isset($_SESSION['userID']) ? (int)$_SESSION['userID'] : 0;
$query = "SELECT status, transactionID FROM `Order` WHERE orderID = $orderID AND studentID = $studentID";

$result = mysqli_query($connectdb, $query);

if (!$result) {
    echo json_encode(['error' => 'Database error']);
    exit();
}

if (mysqli_num_rows($result) === 0) {
    echo json_encode(['error' => 'Order not found']);
    exit();
}

$row = mysqli_fetch_assoc($result);

if ($row['status'] === 'collected' && !empty($row['transactionID'])) {
    echo json_encode(['paid' => true, 'transactionID' => $row['transactionID']]);
} else {
    echo json_encode(['paid' => false, 'status' => $row['status']]);
}
?>