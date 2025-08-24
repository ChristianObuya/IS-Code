<?php
session_start();
include 'config.php';

// Simple check - allow anyone to view users (or adjust as needed)
$canView = true; // Set to your access logic

if (!$canView) {
    echo "<tr><td colspan='6'>Access denied.</td></tr>";
    exit();
}

$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($connectdb, $sql);

if (!$result) {
    echo "<tr><td colspan='6'>Error: " . mysqli_error($connectdb) . "</td></tr>";
    exit();
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $badgeClass = '';
        if ($row['role'] == 'admin') {
            $badgeClass = 'bg-danger';
        } elseif ($row['role'] == 'staff') {
            $badgeClass = 'bg-warning';
        } else {
            $badgeClass = 'bg-info';
        }
        
        echo "<tr>
                <td>{$row['userID']}</td>
                <td>{$row['name']}</td>
                <td>{$row['email']}</td>
                <td><span class='badge {$badgeClass}'>{$row['role']}</span></td>
                <td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>
                <td>
                    <button class='btn btn-sm btn-outline-danger delete-user-btn' data-user-id='{$row['userID']}'>Delete</button>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No users found.</td></tr>";
}
?>