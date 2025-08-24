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

$sql = "
    SELECT 
        i.itemID,
        m.name AS itemName,
        i.stockQuantity,
        i.lowStockThreshold
    FROM Inventory i
    JOIN MenuItem m ON i.itemID = m.itemID
    ORDER BY i.itemID ASC  -- Order by itemID instead of stock quantity
";

$result = mysqli_query($connectdb, $sql);

if (!$result) {
    echo "<p>Database error: " . mysqli_error($connectdb) . "</p>";
    exit();
}

if (mysqli_num_rows($result) > 0) {
    echo "
    <table class='data-table'>
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Item Name</th>
                <th>Stock Quantity</th>
                <th>Low Stock Threshold</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $itemID = $row['itemID'];
        $itemName = htmlspecialchars($row['itemName']);
        $stockQuantity = $row['stockQuantity'];
        $threshold = $row['lowStockThreshold'];
        
        // Determine status based on stock level
        $status = '';
        $statusClass = '';
        if ($stockQuantity == 0) {
            $status = 'Out of Stock';
            $statusClass = 'status-out';
        } elseif ($stockQuantity <= $threshold) {
            $status = 'Low Stock';
            $statusClass = 'status-low';
        } else {
            $status = 'In Stock';
            $statusClass = 'status-ok';
        }

        echo "
            <tr>
                <td>$itemID</td>
                <td>$itemName</td>
                <td>$stockQuantity</td>
                <td>$threshold</td>
                <td class='$statusClass'>$status</td>
            </tr>";
    }

    echo "
        </tbody>
    </table>";
} else {
    echo "<p>No stock data available. Make sure inventory items exist.</p>";
}
?>