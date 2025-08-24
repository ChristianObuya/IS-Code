<?php
session_start();
include 'config.php';  // Gives us $connectdb

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

// --- 1. Get and Validate Date Range ---
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';

// If dates are not provided, set default: last 7 days
if (empty($endDate)) {
    $endDate = date('Y-m-d');
}
if (empty($startDate)) {
    $startDate = date('Y-m-d', strtotime('-7 days'));
}

// Validate dates
if (!strtotime($startDate) || !strtotime($endDate)) {
    echo "<p>Invalid date format. Please use YYYY-MM-DD.</p>";
    exit();
}

$dbStartDate = $startDate . " 00:00:00";
$dbEndDate = $endDate . " 23:59:59";

$sql = "
    SELECT 
        DATE(o.orderTime) as saleDate,
        SUM(o.totalAmount) as totalSales,
        COUNT(o.orderID) as orderCount
    FROM `Order` o
    WHERE o.orderTime BETWEEN '$dbStartDate' AND '$dbEndDate'
      AND o.status = 'collected'
    GROUP BY DATE(o.orderTime)
    ORDER BY DATE(o.orderTime)
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
                <th>Date</th>
                <th>Total Sales (KES)</th>
                <th>Order Count</th>
            </tr>
        </thead>
        <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $date = $row['saleDate'];
        $totalSales = number_format($row['totalSales'], 2);
        $orderCount = $row['orderCount'];

        echo "
            <tr>
                <td>$date</td>
                <td>KES $totalSales</td>
                <td>$orderCount</td>
            </tr>";
    }

    echo "
        </tbody>
    </table>";
} else {
    echo "<p>No sales data found for the selected date range.</p>";
}
?>