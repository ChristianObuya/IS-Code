<?php
session_start();
include 'config.php';

// Hard-coded administration key for security
define('ADMIN_REGISTRATION_KEY', 'CampusBiteAdmin2024');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userID = isset($_POST['userID']) ? trim($_POST['userID']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $adminKey = isset($_POST['adminKey']) ? trim($_POST['adminKey']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($userID) || empty($name) || empty($email) || empty($password) || empty($adminKey)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^\d{6}$/', $userID)) {
        $error = 'Admin ID must be exactly 6 digits.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($adminKey !== ADMIN_REGISTRATION_KEY) {
        $error = 'Invalid administration key.';
    } elseif ($role !== 'admin') {
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
            $success = 'Admin account created successfully!';
            echo "<script>
                alert('$success');
                window.location.href = '../frontend/admin_login.html';
            </script>";
            exit();
        } else {
            $error = "Database error: " . mysqli_error($connectdb);
        }
    }

    if (!empty($error)) {
        echo "<script>
            alert('$error');
            window.history.back();
        </script>";
        exit();
    }
}
?>