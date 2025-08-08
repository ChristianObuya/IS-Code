<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<p>Access denied. Please log in as staff.</p>";
    exit();
}

$sql = "
    SELECT 
        i.itemID,
        m.name AS itemName,
        i.stockQuantity,
        i.lowStockThreshold
    FROM Inventory i
    JOIN MenuItem m ON i.itemID = m.itemID
    ORDER BY i.stockQuantity ASC  -- Show low stock first
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
            </tr>
        </thead>
        <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $itemID = $row['itemID'];
        $itemName = htmlspecialchars($row['itemName']);
        $stockQuantity = $row['stockQuantity'];
        $threshold = $row['lowStockThreshold'];

        echo "
            <tr>
                <td>$itemID</td>
                <td>$itemName</td>
                <td>$stockQuantity</td>
                <td>$threshold</td>
            </tr>";
    }

    echo "
        </tbody>
    </table>";
} else {
    echo "<p>No stock data available. Make sure inventory items exist.</p>";
}
?>