<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $expectedRole = trim($_POST['expectedRole'] ?? '');

    if (empty($email) || empty($password) || empty($expectedRole)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!in_array($expectedRole, ['student', 'staff'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid login context provided.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['passwordHash'])) {
            // Role verification
            if ($user['role'] !== $expectedRole) {
                echo json_encode([
                    'success' => false,
                    'message' => "This is not a valid {$expectedRole} account. Please use the correct login page."
                ]);
                exit;
            }

            // Set session variables
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            echo json_encode([
                'success' => true,
                'role' => $user['role'],
                'name' => $user['name']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email or password.'
            ]);
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred. Please try again.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
}
?>