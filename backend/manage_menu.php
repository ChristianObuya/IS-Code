<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$action = $_POST['action'] ?? '';
if (!in_array($action, ['add', 'edit', 'delete'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

// Define upload directory (relative to frontend/)
$uploadDir = '../frontend/images/';
$imagePath = null;

// Create images folder if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $fileName = 'food_' . uniqid() . '_' . basename($_FILES['image']['name']);
    $fileTmp = $_FILES['image']['tmp_name'];
    $filePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Validate image type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, JPEG, PNG, and GIF files are allowed.']);
        exit;
    }

    // Move uploaded file
    if (move_uploaded_file($fileTmp, $filePath)) {
        $imagePath = 'images/' . $fileName; // Save as `images/filename.jpg`
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
        exit;
    }
}

try {
    if ($action === 'add' || $action === 'edit') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $available = (int)$_POST['available'];

        if (empty($name) || $price <= 0) {
            echo json_encode(['success' => false, 'message' => 'Name and price are required.']);
            exit;
        }

        if ($action === 'add') {
            // Use placeholder if no image
            $finalImagePath = $imagePath ?? 'images/placeholder.jpg';

            $stmt = $pdo->prepare("INSERT INTO MenuItem (name, description, price, category, available, imagePath) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $available, $finalImagePath]);
            $itemID = $pdo->lastInsertId();

            // Create inventory entry
            $stmt = $pdo->prepare("INSERT INTO Inventory (itemID, stockQuantity, lowStockThreshold) VALUES (?, 50, 5)");
            $stmt->execute([$itemID]);

            echo json_encode([
                'success' => true,
                'message' => 'Item added successfully.',
                'itemID' => $itemID
            ]);
        } else {
            $id = (int)$_POST['id'];

            // If no new image, keep old one
            if (!$imagePath) {
                $stmt = $pdo->prepare("SELECT imagePath FROM MenuItem WHERE itemID = ?");
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                $imagePath = $row ? $row['imagePath'] : 'images/placeholder.jpg';
            }

            $stmt = $pdo->prepare("UPDATE MenuItem SET name=?, description=?, price=?, category=?, available=?, imagePath=? WHERE itemID=?");
            $stmt->execute([$name, $description, $price, $category, $available, $imagePath, $id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Item updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No changes made or item not found.']);
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->beginTransaction();

        // Optional: Delete image file
        $stmt = $pdo->prepare("SELECT imagePath FROM MenuItem WHERE itemID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && $row['imagePath'] && !str_contains($row['imagePath'], 'placeholder.jpg')) {
            $filePath = '../frontend/' . $row['imagePath'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM Inventory WHERE itemID = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM MenuItem WHERE itemID = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully.']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollback();
    error_log("Menu management error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred.']);
}
?>