// DOM Elements
const menuList = document.getElementById('menuList');
const inventoryList = document.getElementById('inventoryList');
const itemForm = document.getElementById('itemForm');
const itemName = document.getElementById('itemName');
const itemDesc = document.getElementById('itemDesc');
const itemPrice = document.getElementById('itemPrice');
const itemCategory = document.getElementById('itemCategory');
const addItemBtn = document.getElementById('addItemBtn');
const cancelBtn = document.getElementById('cancelBtn');
const ordersModal = document.getElementById('ordersModal');
const viewOrdersBtn = document.getElementById('viewOrdersBtn');
const ordersBody = document.getElementById('ordersBody');

// --- Tab Switching ---
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById(tab).classList.add('active');

        if (tab === 'menu') {
            loadMenuItems();
        } else if (tab === 'inventory') {
            loadInventory();
        }
    });
});

// Show Add Item Form
addItemBtn.addEventListener('click', () => {
    itemForm.reset();
    document.getElementById('itemID').value = '';
    document.getElementById('formTitle').textContent = 'Add New Item';
    itemForm.style.display = 'block';
    document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
});

// Cancel Form
cancelBtn.addEventListener('click', () => {
    itemForm.style.display = 'none';
});

// Load Menu Items
function loadMenuItems() {
    fetch('../backend/get_all_menu_items.php')
        .then(response => response.text())
        .then(html => {
            menuList.innerHTML = html;
            attachMenuButtonListeners();
        })
        .catch(() => {
            menuList.innerHTML = `<tr><td colspan='6'>Failed to load menu.</td></tr>`;
        });
}

function attachMenuButtonListeners() {
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = function () {
            const { id, name, price, category, desc } = this.dataset;

            document.getElementById('formTitle').textContent = 'Edit Item';
            document.getElementById('itemID').value = id;
            document.getElementById('itemName').value = name;
            document.getElementById('itemPrice').value = price;
            document.getElementById('itemCategory').value = category;
            document.getElementById('itemDesc').value = desc || '';

            itemForm.style.display = 'block';
            document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
        };
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.onclick = function () {
            const id = this.dataset.id;
            if (confirm('Are you sure you want to delete this item?')) {
                const form = new FormData();
                form.append('action', 'delete');
                form.append('id', id);
                fetch('../backend/manage_menu.php', {
                    method: 'POST',
                    body: form
                }).then(() => loadMenuItems()).catch(() => alert('Delete failed.'));
            }
        };
    });
}

// --- Save Item (Add/Edit) ---
itemForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData();
    const itemIDInput = document.getElementById('itemID').value;

    formData.append('action', itemIDInput ? 'edit' : 'add');
    if (itemIDInput) formData.append('id', itemIDInput);

    formData.append('name', itemName.value);
    formData.append('description', itemDesc.value);
    formData.append('price', itemPrice.value);
    formData.append('category', itemCategory.value);
    formData.append('available', '1');

    const imageInput = document.getElementById('itemImage');
    if (imageInput && imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }

    fetch('../backend/manage_menu.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Save response:', text);
        itemForm.style.display = 'none';
        if (imageInput) imageInput.value = '';
        loadMenuItems();
    })
    .catch(() => {
        alert('Save failed. Check connection.');
    });
});

// Load Inventory
function loadInventory() {
    fetch('../backend/get_inventory.php')
        .then(response => response.text())
        .then(html => {
            inventoryList.innerHTML = html;
            document.querySelectorAll('.btn-add-stock').forEach(btn => {
                btn.onclick = function () {
                    const itemID = this.dataset.id;
                    const input = this.previousElementSibling;
                    const quantity = parseInt(input.value, 10);
                    if (isNaN(quantity) || quantity <= 0) {
                        alert('Please enter a valid quantity.');
                        return;
                    }
                    if (confirm(`Add ${quantity} units to item ID ${itemID}?`)) {
                        updateStock(itemID, quantity);
                    }
                };
            });
        })
        .catch(() => {
            inventoryList.innerHTML = `<tr><td colspan='6'>Failed to load inventory.</td></tr>`;
        });
}

// Update Stock
function updateStock(itemID, quantity) {
    const formData = new FormData();
    formData.append('itemID', itemID);
    formData.append('quantity', quantity);
    fetch('../backend/update_inventory.php', {
        method: 'POST',
        body: formData
    })
    .then(() => loadInventory())
    .catch(() => alert('Failed to update stock.'));
}

// View Orders
viewOrdersBtn.onclick = function () {
    fetch('../backend/get_orders.php')
        .then(r => r.text())
        .then(html => {
            ordersBody.innerHTML = html;
            attachOrderStatusListeners();
            ordersModal.style.display = 'block';
        })
        .catch(() => {
            ordersBody.innerHTML = '<tr><td colspan="6">Failed to load orders.</td></tr>';
            ordersModal.style.display = 'block';
        });
};

function attachOrderStatusListeners() {
    document.querySelectorAll('#ordersModal .status-form select').forEach(select => {
        select.onchange = function () {
            const form = this.closest('form');
            const formData = new FormData(form);
            const row = form.closest('tr');
            const newStatus = this.value;

            fetch('../backend/get_orders.php', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                if (newStatus === 'collected') {
                    row.remove(); // remove immediately from modal
                }
            })
            .catch(err => {
                console.error('Update failed:', err);
                alert('Failed to update status.');
            });
        };
    });
}

function closeModal() {
    ordersModal.style.display = 'none';
}

// On Load
document.addEventListener('DOMContentLoaded', () => {
    loadMenuItems();
});