<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Method not allowed");
}

$orderID = (int)($_POST['orderID'] ?? 0);
if ($orderID <= 0) {
    exit("Invalid data");
}

$check = mysqli_query($connectdb, "SELECT * FROM Receipt WHERE orderID = $orderID");
if (mysqli_num_rows($check) > 0) {
    echo "success";
    exit();
}

$orderResult = mysqli_query($connectdb, "SELECT totalAmount FROM `Order` WHERE orderID = $orderID");
if (mysqli_num_rows($orderResult) == 0) {
    exit("Order not found");
}

$orderRow = mysqli_fetch_assoc($orderResult);
$totalAmount = $orderRow['totalAmount'];

$sql = "INSERT INTO Receipt (orderID, totalAmount) VALUES ($orderID, $totalAmount)";
if (mysqli_query($connectdb, $sql)) {
    echo "success";
} else {
    echo "Database error";
}
?>