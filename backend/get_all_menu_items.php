<?php
session_start();
include 'config.php';

if (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') {

    $sql = "SELECT itemID, name, description, price, category, available 
            FROM MenuItem 
            ORDER BY available DESC, name ASC";

    $result = mysqli_query($connectdb, $sql);

    if (!$result) {
        echo "<tr><td colspan='6'>Database error: " . mysqli_error($connectdb) . "</td></tr>";
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        while ($item = mysqli_fetch_assoc($result)) {
            $btnClass = 'btn-delete';
            $toggleText = 'Delete';

            echo "
            <tr>
                <td>" . $item['itemID'] . "</td>
                <td>" . htmlspecialchars($item['name']) . "</td>
                <td>KES " . number_format($item['price'], 2) . "</td>
                <td>" . ucfirst($item['category']) . "</td>
                <td>Yes</td>
                <td>
                    <button class='btn-edit' 
                            data-id='" . $item['itemID'] . "'
                            data-name='" . htmlspecialchars($item['name']) . "'
                            data-price='" . $item['price'] . "'
                            data-category='" . $item['category'] . "'
                            data-desc='" . htmlspecialchars($item['description'] ?? '') . "'>
                        Edit
                    </button>
                    <button class='btn-delete' data-id='" . $item['itemID'] . "'>Delete</button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>No menu items available.</td></tr>";
    }

} else {
    echo "<tr><td colspan='6'>Access denied. Please log in as staff.</td></tr>";
}
?>