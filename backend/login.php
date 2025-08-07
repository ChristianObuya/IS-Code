<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $expectedRole = isset($_POST['expectedRole']) ? trim($_POST['expectedRole']) : '';

    if (empty($email) || empty($password) || empty($expectedRole)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!in_array($expectedRole, ['student', 'staff'])) {
        $error = 'Invalid role provided.';
    }

    if (empty($error)) {
        $check_user = mysqli_query($connectdb, "SELECT * FROM Users WHERE email = '$email'");

        if ($check_user && mysqli_num_rows($check_user) === 1) {
            $user = mysqli_fetch_assoc($check_user);

            if (password_verify($password, $user['passwordHash'])) {
                if ($user['role'] !== $expectedRole) {
                    $error = "This is not a valid {$expectedRole} account. Please use the correct login page.";
                } else {
                    $_SESSION['userID'] = $user['userID'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    if ($user['role'] === 'staff') {
                        header('Location: ../frontend/staff_dashboard.html');
                        exit();
                    } elseif ($user['role'] === 'student') {
                        header('Location: ../frontend/student_home.php');
                        exit();
                    } else {
                        $error = "Invalid role assigned in system.";
                    }
                }
            } else {
                $error = 'Incorrect password.';
            }
        } else {
            $error = 'No account found with that email.';
        }
    }

    if (!empty($error)) {
        echo "<script>alert('$error'); window.history.back();</script>";
        exit();
    }
}
?>