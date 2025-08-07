<?php
include 'config.php';

$sql = "SELECT itemID, name, description, price, category, available, imagePath 
        FROM MenuItem 
        WHERE available = 1 
        ORDER BY category, name";

$result = mysqli_query($connectdb, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='menu-item'>";
        echo "<img src='uploads/{$row['imagePath']}' alt='{$row['name']}' />";
        echo "<h3>{$row['name']}</h3>";
        echo "<p>{$row['description']}</p>";
        echo "<p>Price: Ksh {$row['price']}</p>";
        echo "<p>Category: {$row['category']}</p>";
        echo "</div>";
    }
} else {
    echo "<p>No menu items available.</p>";
}
?>