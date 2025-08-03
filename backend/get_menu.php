<?php
require_once 'config.php';

try {
    $stmt = $pdo->prepare("SELECT itemID, name, description, price, category, available, imagePath FROM MenuItem WHERE available = 1 ORDER BY category, name");
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
        'message' => 'Failed to load menu.'
    ]);
}
?>