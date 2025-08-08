<?php
session_start();
include 'config.php';

// Check if user is logged in and is staff
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<script>alert('Access denied. Please log in as staff.'); window.history.back();</script>";
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
    exit();
}

// Get the action: add, edit, or delete
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Define upload directory (relative to your project)
$uploadDir = '../frontend/images/';

// Create the images folder if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imagePath = null;

// Handle image upload (if any)
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileName = 'food_' . time() . '_' . basename($_FILES['image']['name']);
    $filePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Allow only certain image types
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.'); window.history.back();</script>";
        exit();
    }

    // Move uploaded file
    if (move_uploaded_file($fileTmp, $filePath)) {
        $imagePath = 'images/' . $fileName;  // Save relative path for database
    } else {
        echo "<script>alert('Failed to upload image. Check folder permissions.'); window.history.back();</script>";
        exit();
    }
}

// --- ACTION: ADD NEW MENU ITEM ---
if ($action === 'add') {
    $name = mysqli_real_escape_string($connectdb, trim($_POST['name']));
    $description = mysqli_real_escape_string($connectdb, trim($_POST['description']));
    $price = (float)$_POST['price'];
    $category = mysqli_real_escape_string($connectdb, trim($_POST['category']));
    $available = 1; // Always available

    // Validate required fields
    if (empty($name) || $price <= 0 || empty($category)) {
        echo "<script>alert('Name, price, and category are required.'); window.history.back();</script>";
        exit();
    }

    // Use placeholder image if no upload
    $imagePath = $imagePath ?: 'images/placeholder.jpg';

    // Insert into MenuItem table
    $sql = "INSERT INTO MenuItem (name, description, price, category, available, imagePath) 
            VALUES ('$name', '$description', '$price', '$category', '$available', '$imagePath')";

    if (mysqli_query($connectdb, $sql)) {
        // Get the new auto-generated itemID
        $itemID = mysqli_insert_id($connectdb);

        // Insert into Inventory with the correct itemID
        $stockSql = "INSERT INTO Inventory (itemID, stockQuantity, lowStockThreshold) 
                     VALUES ($itemID, 50, 5)";
        mysqli_query($connectdb, $stockSql); // Run even if fails

        // Redirect back to dashboard
        header('Location: ../staff/staff_dashboard.html');
        exit();
    } else {
        // Show MySQL error
        $error = mysqli_error($connectdb);
        echo "<script>alert('Database error: $error'); window.history.back();</script>";
        exit();
    }
}

// EDIT 
if ($action === 'edit') {
    // ✅ You must get the id from POST
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($connectdb, trim($_POST['name']));
    $description = mysqli_real_escape_string($connectdb, trim($_POST['description']));
    $price = (float)$_POST['price'];
    $category = mysqli_real_escape_string($connectdb, trim($_POST['category']));
    $available = 1; // Always available

    // Validate
    if (empty($name) || $price <= 0 || empty($category)) {
        echo "<script>alert('Name, price, and category are required.'); window.history.back();</script>";
        exit();
    }

    // If no new image, keep the old one
    if (!$imagePath) {
        $result = mysqli_query($connectdb, "SELECT imagePath FROM MenuItem WHERE itemID = $id");
        $row = mysqli_fetch_assoc($result);
        $imagePath = $row['imagePath'];
    }

    // Update the item
    $sql = "UPDATE MenuItem SET 
                name = '$name', 
                description = '$description', 
                price = '$price', 
                category = '$category', 
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

// DELETE
if ($action === 'delete') {
    $id = (int)$_POST['id'];

    // Check if item exists
    $result = mysqli_query($connectdb, "SELECT itemID FROM MenuItem WHERE itemID = $id");
    if (mysqli_num_rows($result) == 0) {
        echo "<script>alert('Item not found.'); window.history.back();</script>";
        exit();
    }

    // Delete the item
    $sql = "DELETE FROM MenuItem WHERE itemID = $id";
    if (mysqli_query($connectdb, $sql)) {
        echo "<script>alert('Item deleted successfully.'); window.history.back();</script>";
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