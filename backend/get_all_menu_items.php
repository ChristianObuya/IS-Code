<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Security: Only logged-in staff can view all menu items.
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT itemID, name, description, price, category, available, imagePath 
        FROM MenuItem 
        ORDER BY name
    ");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $items
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load menu items.'
    ]);
}
?>
