<?php
session_start();
$role = $_SESSION['role'] ?? null;

session_destroy();

if ($role === 'staff') {
    header('Location: ../frontend/staff_login.html');
    exit();
} elseif ($role === 'student') {
    header('Location: ../frontend/index.html');
    exit();
} else {
    header('Location: ../frontend/admin_login.html');
    exit();
}
?>