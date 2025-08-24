<?php
session_start();
include 'config.php';

$isAdmin = false;
if (isset($_SESSION['userID'])) {
    $userID = (int)$_SESSION['userID'];
    $checkAdminSql = "SELECT role FROM users WHERE userID = $userID";
    $adminResult = mysqli_query($connectdb, $checkAdminSql);
    
    if ($adminResult && mysqli_num_rows($adminResult) > 0) {
        $userData = mysqli_fetch_assoc($adminResult);
        if (strtolower($userData['role']) === 'admin') {
            $isAdmin = true;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid method.";
    exit();
}

$action = $_POST['action'] ?? '';

// --- ACTION: DELETE ---
if ($action === 'delete') {
    // Check if ID is provided
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo "User ID is required.";
        exit();
    }
    
    $userId = (int)$_POST['id'];
    
    if ($userId <= 0) {
        echo "Invalid User ID.";
        exit();
    }
    
    $checkSql = "SELECT role FROM users WHERE userID = $userId";
    $checkResult = mysqli_query($connectdb, $checkSql);
    
    if (!$checkResult) {
        echo "Database error: " . mysqli_error($connectdb);
        exit();
    }
    
    if (mysqli_num_rows($checkResult) == 0) {
        echo "User not found.";
        exit();
    }
    
    $row = mysqli_fetch_assoc($checkResult);
    $role = $row['role'];
    
    mysqli_begin_transaction($connectdb);
    
    $success = true;
    $errorMsg = "";
    
    if ($role === 'student') {
        $orderSql = "SELECT orderID FROM `order` WHERE studentID = $userId";
        $orderResult = mysqli_query($connectdb, $orderSql);
        
        if ($orderResult && mysqli_num_rows($orderResult) > 0) {
            while ($orderRow = mysqli_fetch_assoc($orderResult)) {
                $orderId = $orderRow['orderID'];
                
                $deleteOrderItems = mysqli_query($connectdb, "DELETE FROM orderitem WHERE orderID = $orderId");
                if (!$deleteOrderItems) {
                    $success = false;
                    $errorMsg = "Error deleting order items: " . mysqli_error($connectdb);
                    break;
                }
                
                $deleteReceipts = mysqli_query($connectdb, "DELETE FROM receipt WHERE orderID = $orderId");
                if (!$deleteReceipts && mysqli_errno($connectdb) != 0) {
                    $success = false;
                    $errorMsg = "Error deleting receipts: " . mysqli_error($connectdb);
                    break;
                }
            }
            
            if ($success) {
                $deleteOrders = mysqli_query($connectdb, "DELETE FROM `order` WHERE studentID = $userId");
                if (!$deleteOrders) {
                    $success = false;
                    $errorMsg = "Error deleting orders: " . mysqli_error($connectdb);
                }
            }
        }
        
    } elseif ($role === 'staff') {
        $updateOrders = mysqli_query($connectdb, "UPDATE `order` SET updatedByStaffID = NULL WHERE updatedByStaffID = $userId");
        if (!$updateOrders) {
            $success = false;
            $errorMsg = "Error updating staff orders: " . mysqli_error($connectdb);
        }
    }
    
    if ($success) {
        if ($role === 'student') {
            $deleteStudent = mysqli_query($connectdb, "DELETE FROM student WHERE studentID = $userId");
            if (!$deleteStudent) {
                $success = false;
                $errorMsg = "Error deleting from student table: " . mysqli_error($connectdb);
            }
        } elseif ($role === 'staff') {
            $deleteStaff = mysqli_query($connectdb, "DELETE FROM canteenstaff WHERE staffID = $userId");
            if (!$deleteStaff) {
                $success = false;
                $errorMsg = "Error deleting from staff table: " . mysqli_error($connectdb);
            }
        }
    }
    
    if ($success) {
        $deleteUser = mysqli_query($connectdb, "DELETE FROM users WHERE userID = $userId");
        if (!$deleteUser) {
            $success = false;
            $errorMsg = "Error deleting from users table: " . mysqli_error($connectdb);
        }
    }
    
    if ($success) {
        mysqli_commit($connectdb);
        echo "User deleted successfully.";
    } else {
        mysqli_rollback($connectdb);
        echo "Delete failed: $errorMsg";
    }
    
    exit();
}

echo "Invalid action.";
exit();
?>