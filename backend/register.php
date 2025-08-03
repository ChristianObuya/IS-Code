<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = trim($_POST['userID']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validate input
    if (empty($userID) || empty($name) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
        exit;
    }

    if (!in_array($role, ['student', 'staff'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role selected.']);
        exit;
    }

    // Convert to integer for database
    $userID = (int)$userID;
    if ($userID <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID provided.']);
        exit;
    }

    try {
        // Check if userID already exists
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE userID = ?");
        $stmt->execute([$userID]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'User ID already registered.']);
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Start transaction
        $pdo->beginTransaction();

        // Insert into Users table with provided userID
        $stmt = $pdo->prepare("INSERT INTO Users (userID, name, email, passwordHash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userID, $name, $email, $passwordHash, $role]);

        // Insert into role-specific table
        if ($role === 'student') {
            $stmt = $pdo->prepare("INSERT INTO Student (studentID) VALUES (?)");
        } else {
            $stmt = $pdo->prepare("INSERT INTO CanteenStaff (staffID) VALUES (?)");
        }
        $stmt->execute([$userID]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! You can now log in.'
        ]);

    } catch (Exception $e) {
        $pdo->rollback();
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }
}
?>