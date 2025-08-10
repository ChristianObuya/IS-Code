<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Home | CampusBite</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>CampusBite</h1>
            <p>Welcome to the University Canteen</p>
            <div class="user-actions">
                <button id="ordersBtn">My Orders</button>
                <a href="../backend/logout.php" id="logoutBtn">Logout</a>
            </div>
        </header>

        <section class="hero">
            <h2>What would you like to eat today?</h2>
            <p>Browse the menu and place your order online. Skip the queue!</p>
        </section>

        <nav class="category-nav">
            <button class="nav-btn active" data-category="all">All Items</button>
            <button class="nav-btn" data-category="main">Main Course</button>
            <button class="nav-btn" data-category="snack">Snacks</button>
            <button class="nav-btn" data-category="beverage">Beverages</button>
        </nav>

        <main class="main-content">
            <section class="menu-grid" id="menuGrid">
                <?php
                include '../backend/config.php';

                $query = "SELECT itemID, name, description, price, imagePath, category FROM MenuItem";
                $result = mysqli_query($connectdb, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='menu-card' data-category='" . htmlspecialchars($row['category']) . "'>";
                        // ✅ Fixed: img has data-id, data-name, data-price
                        echo "<img class='add-to-cart' 
                                    data-id='" . (int)$row['itemID'] . "' 
                                    data-name='" . htmlspecialchars($row['name']) . "' 
                                    data-price='" . (float)$row['price'] . "' 
                                    src='images/" . basename($row['imagePath']) . "' 
                                    alt='" . htmlspecialchars($row['name']) . "' />";
                        echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                        echo "<p>Ksh " . number_format($row['price'], 2) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>No menu items available.</p>";
                }
                ?>
            </section>

            <aside class="cart-sidebar">
                <h3>Your Order</h3>
                <ul id="cartItems">
                    <li class="empty">Your cart is empty</li>
                </ul>
                <div class="cart-total">
                    <strong>Total: KES </strong><span id="cartTotal">0.00</span>
                </div>
                <button id="checkoutBtn">Proceed to Payment</button>
            </aside>
        </main>
    </div>

    <div class="modal" id="ordersModal" style="display: none;">
        <div class="modal-content">
            <h3>My Orders</h3>
            <div class="modal-body">
                <div id="ordersList">
                    <p>No orders placed yet.</p>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script src="student_home.js"></script>
</body>
</html>