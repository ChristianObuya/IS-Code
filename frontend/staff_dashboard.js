// DOM Elements
const menuList = document.getElementById('menuList');
const inventoryList = document.getElementById('inventoryList');
const ordersBody = document.getElementById('ordersBody');
const itemForm = document.getElementById('itemForm');
const formTitle = document.getElementById('formTitle');
const itemID = document.getElementById('itemID');
const itemName = document.getElementById('itemName');
const itemDesc = document.getElementById('itemDesc');
const itemPrice = document.getElementById('itemPrice');
const itemCategory = document.getElementById('itemCategory');
const itemAvailable = document.getElementById('itemAvailable');
const addItemBtn = document.getElementById('addItemBtn');
const cancelBtn = document.getElementById('cancelBtn');
const ordersModal = document.getElementById('ordersModal');
const viewOrdersBtn = document.getElementById('viewOrdersBtn');

// --- Helper Functions ---
function handleAuthError(response) {
    if (response.status === 403) {
        alert('Your session has expired or you are not authorized. Please log in again.');
        window.location.href = 'staff_login.html';
        return true; // Indicates an auth error was handled
    }
    return false;
}

// Tab Switching
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById(tab).classList.add('active');
        if (tab === 'menu') loadMenuItems();
        if (tab === 'inventory') loadInventory();
    });
});

// Show Add Item Form
addItemBtn.addEventListener('click', () => {
    formTitle.textContent = 'Add New Item';
    itemForm.reset();
    itemID.value = '';
    itemForm.style.display = 'block';
    document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
});

// Cancel Form
cancelBtn.addEventListener('click', () => {
    itemForm.style.display = 'none';
});

// Load Menu Items
async function loadMenuItems() {
    try {
        const response = await fetch('../backend/get_menu.php');
        const result = await response.json();

        if (!result.success) {
            menuList.innerHTML = `<tr><td colspan="6">Failed to load menu.</td></tr>`;
            return;
        }

        if (result.data.length === 0) {
            menuList.innerHTML = `<tr><td colspan="6">No menu items available.</td></tr>`;
            return;
        }

        menuList.innerHTML = '';
        result.data.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${item.itemID}</td>
                <td>${item.name}</td>
                <td>KES ${parseFloat(item.price).toFixed(2)}</td>
                <td>${item.category || 'Unknown'}</td>
                <td>${item.available ? 'Yes' : 'No'}</td>
                <td>
                    <button class="btn-edit" data-id="${item.itemID}">Edit</button>
                    <button class="btn-delete" data-id="${item.itemID}">Delete</button>
                </td>
            `;
            menuList.appendChild(tr);
        });

        // Add event listeners
        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => editItem(e.target.dataset.id));
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => deleteItem(e.target.dataset.id));
        });

    } catch (error) {
        menuList.innerHTML = `<tr><td colspan="6">Network error.</td></tr>`;
        console.error('Load menu error:', error);
    }
}

// Edit Item
async function editItem(id) {
    // Note: The backend script get_menu.php does not support fetching a single item.
    // This function will fetch all available items and find the correct one.
    try {
        const response = await fetch(`../backend/get_menu.php`);
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            const itemToEdit = result.data.find(item => item.itemID == id);

            if (itemToEdit) {
                formTitle.textContent = 'Edit Item';
                itemID.value = itemToEdit.itemID;
                itemName.value = itemToEdit.name;
                itemDesc.value = itemToEdit.description || '';
                itemPrice.value = itemToEdit.price;
                itemCategory.value = itemToEdit.category || '';
                itemAvailable.value = itemToEdit.available;
                itemForm.style.display = 'block';
                document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Error: Could not find the selected item to edit.');
            }
        }
    } catch (error) {
        alert('Failed to load item for editing.');
        console.error(error);
    }
}

// Delete Item
async function deleteItem(id) {
    if (!confirm('Are you sure you want to delete this menu item? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('../backend/manage_menu.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        });

        if (handleAuthError(response)) return;

        const result = await response.json();
        if (!result.success) {
            alert('Delete failed: ' + result.message);
        } else {
            loadMenuItems(); // Refresh the list
        }
    } catch (error) {
        alert('A network error occurred while trying to delete the item.');
        console.error('Delete item error:', error);
    }
}

// Save Item (Add/Edit)
itemForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData();
    formData.append('action', itemID.value ? 'edit' : 'add');
    if (itemID.value) formData.append('id', itemID.value);
    formData.append('name', itemName.value);
    formData.append('description', itemDesc.value);
    formData.append('price', itemPrice.value);
    formData.append('category', itemCategory.value);
    formData.append('available', itemAvailable.value);

    const imageInput = document.getElementById('itemImage');
    if (imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }

    try {
        const response = await fetch('../backend/manage_menu.php', {
            method: 'POST',
            body: formData  // Uses multipart/form-data
        });

        if (handleAuthError(response)) return;

        const result = await response.json();
        if (result.success) {
            itemForm.style.display = 'none';
            imageInput.value = '';
            loadMenuItems();
        } else {
            alert('Save failed: ' + result.message);  // Now shows real error
        }
    } catch (error) {
        alert('Network error: ' + error.message);
        console.error('Menu save error:', error);
    }
});

// Load Inventory
async function loadInventory() {
    try {
        const response = await fetch('../backend/get_inventory.php');
        const result = await response.json();

        if (!result.success) {
            inventoryList.innerHTML = `<tr><td colspan="5">Failed to load inventory.</td></tr>`;
            return;
        }

        inventoryList.innerHTML = '';
        result.data.forEach(inv => {
            const status = inv.stockQuantity <= inv.lowStockThreshold ? 'Low Stock' : 'In Stock';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${inv.itemID}</td>
                <td>${inv.name}</td>
                <td>${inv.stockQuantity}</td>
                <td>${inv.lowStockThreshold}</td>
                <td><strong class="${status === 'Low Stock' ? 'low-stock' : ''}">${status}</strong></td>
            `;
            inventoryList.appendChild(tr);
        });
    } catch (error) {
        inventoryList.innerHTML = `<tr><td colspan="5">Network error.</td></tr>`;
        console.error('Load inventory error:', error);
    }
}

// View Orders
viewOrdersBtn.addEventListener('click', async () => {
    ordersModal.style.display = 'block';
    ordersBody.innerHTML = '<tr><td colspan="6">Loading orders...</td></tr>';

    try {
        const response = await fetch('../backend/get_orders.php');
        if (handleAuthError(response)) return;

        const result = await response.json();

        if (!result.success || result.data.length === 0) {
            ordersBody.innerHTML = '<tr><td colspan="6">No orders at the moment.</td></tr>';
            return;
        }

        ordersBody.innerHTML = '';
        result.data.forEach(order => {
            const tr = document.createElement('tr');
            tr.id = `order-row-${order.orderID}`; // Add an ID to the row for easy removal
            tr.innerHTML = `
                <td>${order.orderID}</td>
                <td>${order.studentID}</td>
                <td>KES ${parseFloat(order.totalAmount).toFixed(2)}</td>
                <td>${order.status}</td>
                <td>${new Date(order.orderTime).toLocaleString()}</td>
                <td>
                    <select class="status-select" data-id="${order.orderID}">
                        <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="preparing" ${order.status === 'preparing' ? 'selected' : ''}>Preparing</option>
                        <option value="ready" ${order.status === 'ready' ? 'selected' : ''}>Ready</option>
                        <option value="collected" ${order.status === 'collected' ? 'selected' : ''}>Collected</option>
                    </select>
                </td>
            `;
            ordersBody.appendChild(tr);
        });

        // Add status change listeners
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const orderID = e.target.dataset.id;
                const status = e.target.value;
                updateOrderStatus(orderID, status);
            });
        });

    } catch (error) {
        ordersBody.innerHTML = '<tr><td colspan="6">Failed to load orders.</td></tr>';
        console.error('Load orders error:', error);
    }
});

// Update Order Status
async function updateOrderStatus(orderID, status) {
    try {
        const response = await fetch('../backend/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `orderID=${orderID}&status=${status}`
        });

        if (handleAuthError(response)) return;

        const result = await response.json();

        // Handle other errors reported by the server
        if (!result.success) {
            alert('Update failed: ' + result.message);
            console.error('Server response:', result); // Log full details for debugging
        } else {
            // The status was updated successfully. No alert is needed,
            // as the user can see the change in the dropdown.
            console.log('Status updated successfully for order ' + orderID);

            // If status is 'collected', remove the row from the view after a short delay
            if (status === 'collected') {
                const rowToRemove = document.getElementById(`order-row-${orderID}`);
                if (rowToRemove) {
                    rowToRemove.style.opacity = '0';
                    setTimeout(() => rowToRemove.remove(), 500);
                }
            }
        }
    } catch (error) {
        alert('A network error occurred. Please check your connection and try again.');
        console.error('Update status fetch error:', error);
    }
}

// Close Modal
function closeModal() {
    ordersModal.style.display = 'none';
}

// On Load
document.addEventListener('DOMContentLoaded', () => {
    loadMenuItems(); // Load menu by default
});