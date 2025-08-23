<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<tr><td colspan='6'>Access denied. Please log in as staff.</td></tr>";
    exit();
}

$sql = "
    SELECT 
        m.name, 
        m.itemID, 
        m.available,
        COALESCE(i.stockQuantity, 0) AS stockQuantity, 
        COALESCE(i.lowStockThreshold, 5) AS lowStockThreshold 
    FROM MenuItem m
    LEFT JOIN Inventory i ON m.itemID = i.itemID
    ORDER BY m.itemID ASC
";

$result = mysqli_query($connectdb, $sql);

if (!$result) {
    echo "<tr><td colspan='6'>Database error: " . mysqli_error($connectdb) . "</td></tr>";
    exit();
}

if (mysqli_num_rows($result) > 0) {
    while ($item = mysqli_fetch_assoc($result)) {
        $stock = $item['stockQuantity'];
        $threshold = $item['lowStockThreshold'];

        // Since all items are available, we only check stock levels
        if ($stock <= $threshold) {
            $statusText = "Low Stock";
            $statusClass = "low-stock";
        } else {
            $statusText = "In Stock";
            $statusClass = "";
        }

        echo "
        <tr data-item-id='" . $item['itemID'] . "'>
            <td>" . $item['itemID'] . "</td>
            <td>" . htmlspecialchars($item['name']) . "</td>
            <td>$stock</td>
            <td>$threshold</td>
            <td><strong class='$statusClass'>$statusText</strong></td>
            <td>
                <div class='stock-update-form'>
                    <input type='number' class='stock-input' placeholder='Qty' min='1' step='1'>
                    <button class='btn-add-stock' data-id='" . $item['itemID'] . "'>Add</button>
                    <button class='btn-delete-item' data-id='" . $item['itemID'] . "'>Delete</button>
                </div>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No inventory items found.</td></tr>";
}
?>