<?php
session_start();
include 'config.php';  // This gives us $connectdb

// Check if user is logged in and is staff
if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {

    // Query the menu items
    $sql = "SELECT itemID, name, description, price, category, available 
            FROM MenuItem 
            ORDER BY available DESC, name ASC";

    $result = mysqli_query($connectdb, $sql);

    // Check if query worked
    if (!$result) {
        echo "<tr><td colspan='6'>Database error: " . mysqli_error($connectdb) . "</td></tr>";
        exit;
    }

    // Check if there are any items
    if (mysqli_num_rows($result) > 0) {
        $index = 1;
        while ($item = mysqli_fetch_assoc($result)) {
            $availability = $item['available'] == 1 ? 'Yes' : 'No';
            $toggleText = $item['available'] == 1 ? 'Deactivate' : 'Activate';
            $btnClass = $item['available'] == 1 ? 'btn-delete' : 'btn-activate';
            $rowClass = $item['available'] == 0 ? 'item-unavailable' : '';

            echo "
            <tr class='$rowClass'>
                <td>$index</td>
                <td>" . htmlspecialchars($item['name']) . "</td>
                <td>KES " . number_format($item['price'], 2) . "</td>
                <td>" . ucfirst($item['category']) . "</td>
                <td>$availability</td>
                <td>
                    <button class='btn-edit' data-id='" . $item['itemID'] . "'>Edit</button>
                    <button class='$btnClass' data-id='" . $item['itemID'] . "'>$toggleText</button>
                </td>
            </tr>";
            $index++;
        }
    } else {
        // No items found
        echo "<tr><td colspan='6'>No menu items available.</td></tr>";
    }

} else {
    // Not logged in or not staff
    echo "<tr><td colspan='6'>Access denied. Please log in as staff.</td></tr>";
}
?>