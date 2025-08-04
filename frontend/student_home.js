// DOM Elements
const menuGrid = document.getElementById('menuGrid');
const cartItems = document.getElementById('cartItems');
const cartTotal = document.getElementById('cartTotal');
const checkoutBtn = document.getElementById('checkoutBtn');
const ordersBtn = document.getElementById('ordersBtn');
const ordersModal = document.getElementById('ordersModal');
const ordersList = document.getElementById('ordersList');
const statusBanner = document.getElementById('statusBanner');
const orderStatusText = document.getElementById('orderStatusText');
const orderTime = document.getElementById('orderTime');

// Cart State
let cart = [];
let userOrders = JSON.parse(localStorage.getItem('userOrders')) || [];
let currentOrder = userOrders.length > 0 ? userOrders[userOrders.length - 1] : null;
let studentID = null;

// 1. Load User Session (from login)
async function loadUser() {
    try {
        // Correct: Fetch user session
        const response = await fetch('../backend/get_user.php');
        const result = await response.json();

        if (result.success && result.role === 'student') {
            studentID = result.userID;
        } else {
            window.location.href = 'index.html';
        }
    } catch (error) {
        console.error('Session error:', error);
        window.location.href = 'index.html';
    }
}

// 2. Display Current Order Status
function updateOrderStatus() {
    if (currentOrder && currentOrder.status !== 'collected') {
        statusBanner.style.display = 'block';
        orderStatusText.textContent = currentOrder.status;
        orderTime.textContent = `Placed at ${currentOrder.time}`;
    }
}

// 3. Fetch Menu from Backend (Staff-Managed Items)
async function loadMenu(category = 'all') {
    menuGrid.innerHTML = '<p class="loading">Loading menu from canteen...</p>';

    try {
        // CORRECT: Fetch menu from backend
        const response = await fetch('../backend/get_menu.php');
        if (!response.ok) throw new Error('Network error: Failed to reach server.');

        const result = await response.json();

        if (!result.success) {
            menuGrid.innerHTML = '<p class="error">Failed to load menu. Please try again later.</p>';
            console.error('Backend error:', result.message);
            return;
        }

        const items = result.data;
        if (!items || items.length === 0) {
            menuGrid.innerHTML = '<p>No menu items available at the moment.</p>';
            return;
        }

        const filtered = category === 'all'
            ? items
            : items.filter(item => item.category && item.category.toLowerCase() === category);

        if (filtered.length === 0) {
            menuGrid.innerHTML = '<p>No items in this category.</p>';
            return;
        }

        menuGrid.innerHTML = '';
        filtered.forEach(item => {
            const menuItem = document.createElement('div');
            menuItem.className = 'menu-item';

            const imagePath = item.imagePath ? item.imagePath : 'images/placeholder.jpg';

            menuItem.innerHTML = `
                <div class="menu-item-image">
                    <img src="${imagePath}" alt="${item.name}" onerror="this.src='images/placeholder.jpg';">
                </div>
                <h4>${item.name}</h4>
                <p class="desc">${item.description || 'No description available.'}</p>
                <div class="item-footer">
                    <span class="price">KES ${parseFloat(item.price).toFixed(2)}</span>
                    <button class="add-to-cart"
                            data-id="${item.itemID}"
                            data-name="${item.name}"
                            data-price="${item.price}"
                            data-category="${item.category}">
                        Add
                    </button>
                </div>
            `;
            menuGrid.appendChild(menuItem);
        });

        // Add event listeners
        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.target.dataset.id);
                const name = e.target.dataset.name;
                const price = parseFloat(e.target.dataset.price);
                const category = e.target.dataset.category;
                const description = e.target.parentElement.previousElementSibling.textContent;

                const item = { id, name, price, category, description };
                addToCart(item);
            });
        });

    } catch (error) {
        menuGrid.innerHTML = '<p class="error">Unable to connect to the canteen system.</p>';
        console.error('Menu fetch failed:', error);
    }
}

// 4. Add to Cart
function addToCart(item) {
    const existing = cart.find(i => i.id === item.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.push({ ...item, quantity: 1 });
    }
    updateCart();
}

// 5. Update Cart UI
function updateCart() {
    cartItems.innerHTML = '';
    let total = 0;

    if (cart.length === 0) {
        const empty = document.createElement('li');
        empty.className = 'empty';
        empty.textContent = 'Your cart is empty';
        cartItems.appendChild(empty);
        checkoutBtn.disabled = true;
    } else {
        cart.forEach(item => {
            const li = document.createElement('li');
            li.innerHTML = `
                ${item.name} × ${item.quantity}
                <span>KES ${(item.price * item.quantity).toFixed(2)}</span>
                <button class="delete-item" data-id="${item.id}" title="Remove item">×</button>
            `;
            cartItems.appendChild(li);
        });
        checkoutBtn.disabled = false;
    }

    total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotal.textContent = total.toFixed(2);

    // Add delete event listeners
    document.querySelectorAll('.delete-item').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = parseInt(e.target.dataset.id);
            removeFromCart(id);
        });
    });
}

// 6. Remove from Cart
function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

// 7. Open Orders Modal
ordersBtn.addEventListener('click', () => {
    ordersModal.style.display = 'block';
    ordersList.innerHTML = '';

    if (userOrders.length === 0) {
        const p = document.createElement('p');
        p.textContent = 'No orders placed yet.';
        ordersList.appendChild(p);
    } else {
        userOrders.slice().reverse().forEach(order => {
            const orderEl = document.createElement('div');
            orderEl.className = 'order-item';
            orderEl.innerHTML = `
                <p><strong>Order #${order.id}</strong> - KES ${order.total.toFixed(2)}</p>
                <p>Status: ${order.status}</p>
                <p>${order.time}</p>
                <hr>
            `;
            ordersList.appendChild(orderEl);
        });
    }
});

// 8. Close Modal
function closeModal() {
    ordersModal.style.display = 'none';
}

// 9. Proceed to Payment
checkoutBtn.addEventListener('click', async () => {
    if (cart.length === 0) return;
    if (!studentID) {
        alert('Authentication error. Please log in again.');
        window.location.href = 'index.html';
        return;
    }

    const orderData = {
        items: cart,
        total: parseFloat(cartTotal.textContent),
        time: new Date().toISOString(),
        status: 'pending'
    };

    // Save to local history
    userOrders.push({ id: Date.now(), ...orderData });
    localStorage.setItem('userOrders', JSON.stringify(userOrders));
    localStorage.setItem('pendingOrder', JSON.stringify(orderData));

    // Send order to backend
    try {
        const response = await fetch('../backend/place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                studentID: studentID,
                totalAmount: orderData.total,
                items: orderData.items.map(item => ({
                    id: item.id,
                    quantity: item.quantity
                }))
            })
        });

        const result = await response.json();

        if (result.success) {
            console.log('Order saved with ID:', result.orderID);
            window.location.href = 'student_payment.html';
        } else {
            alert('Failed to place order: ' + result.message);
        }
    } catch (error) {
        alert('Network error. Could not place order.');
        console.error('Order placement failed:', error);
    }
});

// 10. Filter Menu by Category
document.querySelectorAll('.nav-btn, .filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nav-btn, .filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        loadMenu(btn.dataset.category);
    });
});

//11. On Page Load
document.addEventListener('DOMContentLoaded', () => {
    loadUser();          // First: Check session (get_user.php)
    updateOrderStatus(); // Show active order
    loadMenu();          // Then: Load menu (get_menu.php)
    updateCart();        // Initialize cart
});