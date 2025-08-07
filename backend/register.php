<?php
session_start();
include 'config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = isset($_POST['userID']) ? trim($_POST['userID']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($userID) || empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!in_array($role, ['student', 'staff'])) {
        $error = 'Invalid role selected.';
    }

    if (empty($error)) {
        $check_user = mysqli_query($connectdb, "SELECT userID FROM Users WHERE userID = '$userID'");
        if (mysqli_num_rows($check_user) > 0) {
            $error = 'This ID is already registered.';
        }

        $check_email = mysqli_query($connectdb, "SELECT userID FROM Users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = 'An account with this email already exists.';
        }
    }

    if (empty($error)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insert_user = "INSERT INTO Users (userID, name, email, passwordHash, role) 
                        VALUES ('$userID', '$name', '$email', '$passwordHash', '$role')";
        $run_query = mysqli_query($connectdb, $insert_user);
        if ($run_query) {
            if ($role === 'staff') {
                mysqli_query($connectdb, "INSERT INTO CanteenStaff (staffID) VALUES ('$userID')");
                header('Location: ../frontend/staff_login.html');
                exit();
            } elseif ($role === 'student') {
                mysqli_query($connectdb, "INSERT INTO Student (studentID) VALUES ('$userID')");
                header('Location: ../frontend/index.html');
                exit();
            } else {
                $error = "Invalid role selected.";
            }
        } else {
            $error = "Database error: " . mysqli_error($connectdb);
        }
    }

    if (!empty($error)) {
        echo "<script>alert('$error'); window.history.back();</script>";
        exit();
    }
}
?>