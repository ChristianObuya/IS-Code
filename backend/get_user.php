<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false]);
    exit;
}

// Return user data
echo json_encode([
    'success' => true,
    'userID' => $_SESSION['userID'],
    'role' => $_SESSION['role'],
    'name' => $_SESSION['name']
]);
?>