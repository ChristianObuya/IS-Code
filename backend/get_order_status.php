<?php
session_start();
include 'config.php';

// Check login
if (!isset($_SESSION['userID'])) {
    exit("null");
}

$orderID = (int)($_GET['orderID'] ?? 0);
if ($orderID <= 0) {
    exit("null");
}

$sql = "SELECT status, orderTime, completionTime, studentID FROM `Order` WHERE orderID = $orderID";
$result = mysqli_query($connectdb, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    exit("null");
}

$order = mysqli_fetch_assoc($result);

if ($_SESSION['role'] === 'student' && $order['studentID'] != $_SESSION['userID']) {
    exit("null");
}

$currentStatus = $order['status'];
$completionTime = $order['completionTime'];

// If status is 'ready' or 'collected' and completionTime is NULL, set it
if (($currentStatus === 'ready' || $currentStatus === 'collected') && !$completionTime) {
    $updateSql = "UPDATE `Order` SET completionTime = NOW() WHERE orderID = $orderID";
    mysqli_query($connectdb, $updateSql);
}

// Re-fetch latest order data so receipt is up to date
$sql = "SELECT status, orderTime, completionTime FROM `Order` WHERE orderID = $orderID";
$result = mysqli_query($connectdb, $sql);
$order = mysqli_fetch_assoc($result);

// Output: status|orderTime|completionTime
echo $order['status'] . '|' . $order['orderTime'] . '|' . ($order['completionTime'] ?: 'null');
?>
