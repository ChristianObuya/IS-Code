<?php
session_start();
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'staff') {
    header("Location: staff_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Staff Dashboard | CampusBite</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>CampusBite</h1>
            <p>Welcome to the Staff Dashboard, <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            <div class="user-actions">
                <button id="viewOrdersBtn">View Orders</button>
                <a href="../backend/logout.php" id="logoutBtn">Logout</a>
            </div>
        </header>

        <nav class="staff-nav">
            <button class="nav-btn active" data-tab="menu">Menu Management</button>
            <button class="nav-btn" data-tab="inventory">Inventory</button>
        </nav>

        <main class="main-content staff-main">
            <section id="menu" class="tab-content active">
                <div class="section-header">
                    <h2>Manage Menu Items</h2>
                    <button id="addItemBtn" class="btn btn-primary">+ Add New Item</button>
                </div>

                <form id="itemForm" style="display: none;">
                    <h3 id="formTitle">Add New Item</h3>
                    <input type="hidden" id="itemID">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" id="itemName" required />
                        </div>
                        <div class="form-group">
                            <label>Price (Ksh)</label>
                            <input type="number" id="itemPrice" step="0.00" min="0" required />
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select id="itemCategory" required>
                                <option value="">Select Category</option>
                                <option value="main">Main Course</option>
                                <option value="snack">Snack</option>
                                <option value="beverage">Beverage</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea id="itemDesc" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" id="itemImage" accept="image/*" />
                        <small>Leave blank to keep current image</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-success">Save Item</button>
                        <button type="button" id="cancelBtn" class="btn-cancel">Cancel</button>
                    </div>
                </form>

                <table class="data-table" id="menuTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="menuList">
                        <tr><td colspan="6" class="loading">Loading menu...</td></tr>
                    </tbody>
                </table>
            </section>

            <section id="inventory" class="tab-content">
                <h2>Inventory Levels</h2>
                <table class="data-table" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>Stock</th>
                            <th>Threshold</th>
                            <th>Status</th>
                            <th>Add Stock</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryList">
                        <tr><td colspan="6" class="loading">Loading inventory...</td></tr>
                    </tbody>
                </table>
            </section>
        </main>

        <div class="modal" id="ordersModal" style="display: none;">
            <div class="modal-content">
                <h3>Student Orders</h3>
                <div class="modal-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Student ID</th>
                                <th>Total (KES)</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="ordersBody"></tbody>
                    </table>
                </div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="staff_dashboard.js"></script>
</body>
</html>