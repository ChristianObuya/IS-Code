<?php
session_start();
include 'config.php';  // Gives us $connectdb

// Check if logged in and is staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<script>alert('Access denied. Please log in as staff.'); window.history.back();</script>";
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
    exit();
}

// Get and validate itemID and quantity
$itemID = (int)($_POST['itemID'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 0);

if ($itemID <= 0 || $quantity <= 0) {
    echo "<script>alert('Invalid item or quantity.'); window.history.back();</script>";
    exit();
}

// Check if item exists in MenuItem
$check = mysqli_query($connectdb, "SELECT itemID FROM MenuItem WHERE itemID = $itemID");
if (mysqli_num_rows($check) == 0) {
    echo "<script>alert('Item not found.'); window.history.back();</script>";
    exit();
}

// Update stock: Add new quantity to existing stock
$sql = "
    UPDATE Inventory 
    SET stockQuantity = stockQuantity + $quantity 
    WHERE itemID = $itemID";

if (mysqli_query($connectdb, $sql)) {
    // Success!
    echo "<script>alert('Stock updated successfully! Added $quantity units.'); window.history.back();</script>";
} else {
    // Database error
    echo "<script>alert('Database error: " . mysqli_error($connectdb) . "'); window.history.back();</script>";
}
?>