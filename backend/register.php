<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$userID = trim($_POST['userID'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? '');

if (empty($userID) || empty($name) || empty($email) || empty($password) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

if (!in_array($role, ['student', 'staff'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
    exit;
}

try {
    // Check if userID already exists
    $stmt = $pdo->prepare("SELECT userID FROM Users WHERE userID = ?");
    $stmt->execute([$userID]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This ID is already registered.']);
        exit;
    }

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT userID FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO Users (userID, name, email, passwordHash, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userID, $name, $email, $passwordHash, $role]);

    if ($role === 'staff') {
        $roleStmt = $pdo->prepare("INSERT INTO CanteenStaff (staffID) VALUES (?)");
        $roleStmt->execute([$userID]);
    } elseif ($role === 'student') {
        $roleStmt = $pdo->prepare("INSERT INTO Student (studentID) VALUES (?)");
        $roleStmt->execute([$userID]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Registration successful! You can now log in.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Registration error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please try again later.']);
}
?>
