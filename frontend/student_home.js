// === CATEGORY FILTERING ===
var buttons = document.getElementsByClassName("nav-btn");
var items = document.getElementsByClassName("menu-card");

for (var i = 0; i < buttons.length; i++) {
    buttons[i].onclick = function () {
        for (var j = 0; j < buttons.length; j++) {
            buttons[j].classList.remove("active");
        }
        this.classList.add("active");

        var category = this.getAttribute("data-category");

        for (var k = 0; k < items.length; k++) {
            if (category === "all" || items[k].getAttribute("data-category") === category) {
                items[k].style.display = "block";
            } else {
                items[k].style.display = "none";
            }
        }
    };
}

// === CART HANDLING ===
var cart = [];
var cartList = document.getElementById("cartItems");
var cartTotal = document.getElementById("cartTotal");
var checkoutBtn = document.getElementById("checkoutBtn");
var addToCartImages = document.getElementsByClassName("add-to-cart"); // these are the <img> tags

// Attach click event to each image (acts as add-to-cart button)
for (var i = 0; i < addToCartImages.length; i++) {
    addToCartImages[i].onclick = function () {
        var id = this.getAttribute("data-id");
        var name = this.getAttribute("data-name");
        var price = parseFloat(this.getAttribute("data-price"));

        // Safety check
        if (!id || !name || isNaN(price)) {
            console.error("Missing data on item:", this);
            return;
        }

        // Check if item already in cart
        var found = false;
        for (var j = 0; j < cart.length; j++) {
            if (cart[j].id == id) {
                cart[j].quantity++;
                found = true;
                break;
            }
        }

        // Add new item if not found
        if (!found) {
            cart.push({
                id: id,
                name: name,
                price: price,
                quantity: 1
            });
        }

        updateCart();
    };
}

// Update cart display
function updateCart() {
    cartList.innerHTML = "";

    if (cart.length === 0) {
        cartList.innerHTML = "<li class='empty'>Your cart is empty</li>";
        cartTotal.textContent = "0.00";
        checkoutBtn.disabled = true;
        return;
    }

    var total = 0;
    for (var i = 0; i < cart.length; i++) {
        var item = cart[i];
        var itemTotal = item.price * item.quantity;
        total += itemTotal;

        var li = document.createElement("li");
        li.innerHTML = `
            ${item.name} × ${item.quantity}
            <span>KES ${itemTotal.toFixed(2)}</span>
            <button class="delete-item" data-index="${i}">×</button>
        `;
        cartList.appendChild(li);
    }

    cartTotal.textContent = total.toFixed(2);
    checkoutBtn.disabled = false;

    // Reattach delete buttons
    var deleteButtons = document.getElementsByClassName("delete-item");
    for (var i = 0; i < deleteButtons.length; i++) {
        deleteButtons[i].onclick = function () {
            var index = parseInt(this.getAttribute("data-index"));
            if (isNaN(index) || index < 0 || index >= cart.length) return;
            cart.splice(index, 1);
            updateCart();
        };
    }
}

// Initial cart update
updateCart();

// === ORDERS MODAL ===
var ordersBtn = document.getElementById("ordersBtn");
var ordersModal = document.getElementById("ordersModal");

ordersBtn.onclick = function () {
    ordersModal.style.display = "block";
};

function closeModal() {
    ordersModal.style.display = "none";
}

// === PROCEED TO PAYMENT ===
document.getElementById("checkoutBtn").onclick = function () {
    if (cart.length === 0) {
        alert("Your cart is empty.");
        return;
    }

    // Calculate total
    var totalAmount = 0;
    for (var i = 0; i < cart.length; i++) {
        totalAmount += cart[i].price * cart[i].quantity;
    }

    // Create FormData
    var formData = new FormData();

    // Append items as simple arrays
    for (var i = 0; i < cart.length; i++) {
        formData.append("item_ids[]", cart[i].id);
        formData.append("item_quantities[]", cart[i].quantity);
    }
    formData.append("totalAmount", totalAmount.toFixed(2));

    // Debug: See what's being sent
    console.log("Sending order:");
    for (let [key, value] of formData.entries()) {
        console.log(key, "=", value);
    }

    // Send to backend
    fetch("../backend/place_order.php", {
        method: "POST",
        body: formData
    })
    .then(function (response) {
        return response.text();
    })
    .then(function (text) {
        // Debug: See what the server says
        console.log("Server response:", text);

        var trimmed = text.trim();

        // If it starts with "success|"
        if (trimmed.startsWith("success|")) {
            var orderID = trimmed.split("|")[1];
            // Redirect with orderID and totalAmount
            window.location.href = "student_payment.html?orderID=" + orderID + "&totalAmount=" + totalAmount;
        } else {
            // Show the real error
            alert("Order failed: " + trimmed);
        }
    })
    .catch(function (error) {
        alert("Network error: " + error.message);
    });
};