<?php
session_start();
include 'config.php';

// Only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    echo "<tr><td colspan='6'>Access denied.</td></tr>";
    exit();
}

// Handle status update (silent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderID']) && isset($_POST['newStatus'])) {
    $orderID = (int)$_POST['orderID'];
    $newStatus = mysqli_real_escape_string($connectdb, $_POST['newStatus']);
    $validStatuses = ['pending', 'preparing', 'ready', 'collected'];

    if (in_array($newStatus, $validStatuses)) {
        $sql = "UPDATE `Order` SET status = '$newStatus' WHERE orderID = $orderID";
        mysqli_query($connectdb, $sql);
    }
    exit(); // Stop here — no output
}

// Fetch active orders (only non-collected)
$sql = "
    SELECT o.orderID, o.studentID, o.totalAmount, o.status, o.orderTime 
    FROM `Order` o
    WHERE o.status != 'collected'
    ORDER BY o.orderTime DESC
";

$result = mysqli_query($connectdb, $sql);

if (!$result) {
    echo "<tr><td colspan='6'>Database error.</td></tr>";
    exit();
}

if (mysqli_num_rows($result) > 0) {
    while ($order = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $order['orderID'] . "</td>";
        echo "<td>" . htmlspecialchars($order['studentID']) . "</td>";
        echo "<td>KES " . number_format($order['totalAmount'], 2) . "</td>";
        echo "<td>" . $order['orderTime'] . "</td>";
        echo "<td>";
        echo "  <form method='POST' style='display:inline;' class='status-form' data-orderid='" . $order['orderID'] . "'>";
        echo "    <input type='hidden' name='orderID' value='" . $order['orderID'] . "'>";
        echo "    <select name='newStatus'>";
        echo "      <option value='pending'" . ($order['status'] === 'pending' ? ' selected' : '') . ">Pending</option>";
        echo "      <option value='preparing'" . ($order['status'] === 'preparing' ? ' selected' : '') . ">Preparing</option>";
        echo "      <option value='ready'" . ($order['status'] === 'ready' ? ' selected' : '') . ">Ready</option>";
        echo "      <option value='collected'" . ($order['status'] === 'collected' ? ' selected' : '') . ">Collected</option>";
        echo "    </select>";
        echo "  </form>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No active orders.</td></tr>";
}
?>