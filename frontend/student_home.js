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
var cartImages = document.getElementsByClassName("add-to-cart");

for (var i = 0; i < cartImages.length; i++) {
    cartImages[i].onclick = function () {
        var name = this.getAttribute("data-name");
        var price = parseFloat(this.getAttribute("data-price"));
        cart.push({ name: name, price: price });
        updateCart();
    };
}

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
        var li = document.createElement("li");
        li.textContent = cart[i].name + " - KES " + cart[i].price.toFixed(2);
        li.setAttribute("data-index", i);
        li.onclick = function () {
            var index = this.getAttribute("data-index");
            cart.splice(index, 1);
            updateCart();
        };
        cartList.appendChild(li);
        total += cart[i].price;
    }
    cartTotal.textContent = total.toFixed(2);
    checkoutBtn.disabled = false;
}

// === ORDERS MODAL ===
var ordersBtn = document.getElementById("ordersBtn");
var ordersModal = document.getElementById("ordersModal");

ordersBtn.onclick = function () {
    ordersModal.style.display = "block";
};

function closeModal() {
    ordersModal.style.display = "none";
}

checkoutBtn.onclick = function () {
    window.location.href = "student_payment.html";
};