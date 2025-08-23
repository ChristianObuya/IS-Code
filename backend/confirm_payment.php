<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid method";
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['userID'])) {
    echo "Access denied";
    exit();
}

$orderID = (int)($_POST['orderID'] ?? 0);
$transactionID = trim($_POST['transactionID'] ?? '');

if ($orderID <= 0 || empty($transactionID)) {
    echo "Invalid data";
    exit();
}

$studentID = (int)$_SESSION['userID'];

// Update the order with transaction ID
$sql = "UPDATE `Order` SET transactionID = '$transactionID' WHERE orderID = $orderID AND studentID = $studentID";

if (mysqli_query($connectdb, $sql)) {
    if (mysqli_affected_rows($connectdb) > 0) {
        echo "success";
    } else {
        // No rows affected - order not found or doesn't belong to user
        echo "Order not found";
    }
} else {
    // Database error
    echo "Database error: " . mysqli_error($connectdb);
}
?>