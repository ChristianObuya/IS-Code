<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $orderID = (int)$_GET['orderID'];
    
    // Generate realistic MPESA transaction ID
    $transactionID = 'MPE' . date('YmdHis') . rand(100, 999);
    
    // Update order as if payment was completed
    $sql = "UPDATE `Order` SET 
            status = 'pending',
            transactionID = '$transactionID',
            mpesa_phone = '254708374149'
            WHERE orderID = $orderID";
    
    if (mysqli_query($connectdb, $sql)) {
        echo "success";
        error_log("Sandbox: Order #$orderID completed with transaction ID: $transactionID");
    } else {
        echo "error: " . mysqli_error($connectdb);
    }
} else {
    echo "invalid_request";
}
?>