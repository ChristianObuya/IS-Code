<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard | CampusBite</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>CampusBite</h1>
            <p>Welcome to the Admin Dashboard, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            <div class="user-actions">
                <a href="../backend/logout.php" id="logoutBtn">Logout</a>
            </div>
        </header>

        <nav class="staff-nav">
            <button class="nav-btn active" data-tab="users">User Management</button>
            <button class="nav-btn" data-tab="sales">Sales Reports</button>
            <button class="nav-btn" data-tab="stock">Stock Reports</button>
        </nav>

        <main class="main-content staff-main">
            <!-- User Management Tab -->
            <section id="users" class="tab-content active">
                <div class="section-header">
                    <h2>User Management</h2>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersList">
                        <tr><td colspan="6" class="loading">Loading users...</td></tr>
                    </tbody>
                </table>
            </section>

            <!-- Sales Reports Tab -->
            <section id="sales" class="tab-content">
                <h2>Sales Reports</h2>
                <div class="report-filters">
                    <label for="salesStartDate">Start Date:</label>
                    <input type="date" id="salesStartDate">
                    <label for="salesEndDate">End Date:</label>
                    <input type="date" id="salesEndDate">
                    <button id="generateSalesReport" class="btn btn-primary">Generate Report</button>
                </div>
                <div id="salesReportContent">
                    <p>Select a date range to view sales reports.</p>
                </div>
            </section>

            <!-- Stock Reports Tab -->
            <section id="stock" class="tab-content">
                <h2>Stock Reports</h2>
                <div id="stockReportContent">
                    <p class="loading">Loading stock reports...</p>
                </div>
            </section>
        </main>
    </div>

    <script src="admin_dashboard.js"></script>
</body>
</html>