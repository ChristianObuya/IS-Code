// manage_menu.php
<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<script>alert('Access denied.'); window.history.back();</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid method.'); window.history.back();</script>";
    exit();
}

$action = $_POST['action'] ?? '';
$uploadDir = '../frontend/images/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

$imagePath = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileName = 'food_' . time() . '_' . basename($_FILES['image']['name']);
    $filePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowed)) {
        echo "<script>alert('Only JPG, JPEG, PNG, GIF allowed.'); window.history.back();</script>";
        exit();
    }
    if (move_uploaded_file($fileTmp, $filePath)) {
        $imagePath = 'images/' . $fileName;
    } else {
        echo "<script>alert('Upload failed.'); window.history.back();</script>";
        exit();
    }
}

if ($action === 'add') {
    $name = mysqli_real_escape_string($connectdb, trim($_POST['name']));
    $desc = mysqli_real_escape_string($connectdb, trim($_POST['description']));
    $price = (float)$_POST['price'];
    $cat = mysqli_real_escape_string($connectdb, trim($_POST['category']));
    $available = 1;
    $imagePath = $imagePath ?: 'images/placeholder.jpg';

    if (empty($name) || $price <= 0 || empty($cat)) {
        echo "<script>alert('Name, price, category required.'); window.history.back();</script>";
        exit();
    }

    $sql = "INSERT INTO MenuItem (name, description, price, category, available, imagePath) 
            VALUES ('$name', '$desc', '$price', '$cat', '$available', '$imagePath')";

    if (mysqli_query($connectdb, $sql)) {
        $itemID = mysqli_insert_id($connectdb);
        $stockSql = "INSERT INTO Inventory (itemID, stockQuantity, lowStockThreshold) VALUES ($itemID, 50, 5)";
        mysqli_query($connectdb, $stockSql);
        header('Location: ../staff/staff_dashboard.html');
        exit();
    } else {
        $error = mysqli_error($connectdb);
        echo "<script>alert('Add failed: $error'); window.history.back();</script>";
        exit();
    }
}

if ($action === 'edit') {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($connectdb, trim($_POST['name']));
    $desc = mysqli_real_escape_string($connectdb, trim($_POST['description']));
    $price = (float)$_POST['price'];
    $cat = mysqli_real_escape_string($connectdb, trim($_POST['category']));
    $available = 1;

    if (empty($name) || $price <= 0 || empty($cat)) {
        echo "<script>alert('Name, price, category required.'); window.history.back();</script>";
        exit();
    }

    if (!$imagePath) {
        $result = mysqli_query($connectdb, "SELECT imagePath FROM MenuItem WHERE itemID = $id");
        $row = mysqli_fetch_assoc($result);
        $imagePath = $row['imagePath'];
    }

    $sql = "UPDATE MenuItem SET 
                name = '$name', 
                description = '$desc', 
                price = '$price', 
                category = '$cat', 
                available = '$available', 
                imagePath = '$imagePath' 
            WHERE itemID = $id";

    if (mysqli_query($connectdb, $sql)) {
        header('Location: ../staff/staff_dashboard.html');
        exit();
    } else {
        $error = mysqli_error($connectdb);
        echo "<script>alert('Update failed: $error'); window.history.back();</script>";
        exit();
    }
}

// --- ACTION: DELETE ---
if ($action === 'delete') {
    $id = (int)$_POST['id'];

    // Check if item exists
    $result = mysqli_query($connectdb, "SELECT itemID FROM MenuItem WHERE itemID = $id");
    if (mysqli_num_rows($result) == 0) {
        echo "<script>alert('Item not found.'); window.history.back();</script>";
        exit();
    }

    // Delete related inventory row first (to avoid foreign key constraint errors)
    mysqli_query($connectdb, "DELETE FROM Inventory WHERE itemID = $id");

    // Then delete the menu item itself
    $sql = "DELETE FROM MenuItem WHERE itemID = $id";
    if (mysqli_query($connectdb, $sql)) {
        echo "<script>alert('Item deleted successfully.'); window.location.href='../staff/staff_dashboard.html';</script>";
        exit();
    } else {
        $error = mysqli_error($connectdb);
        echo "<script>alert('Delete failed: $error'); window.history.back();</script>";
        exit();
    }
}

echo "<script>alert('Invalid action.'); window.history.back();</script>";
exit();
?>