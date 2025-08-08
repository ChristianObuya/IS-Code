// DOM Elements
const menuList = document.getElementById('menuList');
const inventoryList = document.getElementById('inventoryList');
const itemForm = document.getElementById('itemForm');
const itemName = document.getElementById('itemName');
const itemDesc = document.getElementById('itemDesc');
const itemPrice = document.getElementById('itemPrice');
const itemCategory = document.getElementById('itemCategory');
const itemAvailable = document.getElementById('itemAvailable');
const addItemBtn = document.getElementById('addItemBtn');
const cancelBtn = document.getElementById('cancelBtn');
const ordersModal = document.getElementById('ordersModal');
const viewOrdersBtn = document.getElementById('viewOrdersBtn');
const ordersBody = document.getElementById('ordersBody');

//Tab Switching
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all tabs and content
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        // Add active to clicked tab
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById(tab).classList.add('active');

        // Load content when tab is opened
        if (tab === 'menu') {
            loadMenuItems();
        } else if (tab === 'inventory') {
            loadInventory();
        } else if (tab === 'sales') {
            loadSalesReport();
        } else if (tab === 'stock') {
            loadStockReport();
        }
    });
});

//Show Add Item Form
addItemBtn.addEventListener('click', () => {
    itemForm.reset();
    itemForm.style.display = 'block';
    document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
});

//Cancel Form
cancelBtn.addEventListener('click', () => {
    itemForm.style.display = 'none';
});

//Load Menu Items
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
                const id = this.dataset.id;
                const name = this.dataset.name;
                const price = this.dataset.price;
                const category = this.dataset.category;
                const desc = this.dataset.desc;

                document.getElementById('formTitle').textContent = 'Edit Item';
                document.getElementById('itemID').value = id;
                document.getElementById('itemName').value = name;
                document.getElementById('itemPrice').value = price;
                document.getElementById('itemCategory').value = category;
                document.getElementById('itemDesc').value = desc || '';

                document.getElementById('itemForm').style.display = 'block';
                document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
            };
        });
    };




    // Delete buttons
    document.querySelectorAll('.btn-delete, .btn-activate').forEach(btn => {
        btn.onclick = function () {
            const id = this.dataset.id;
            const action = this.textContent.toLowerCase();

            if (confirm(`Are you sure you want to ${action} this item?`)) {
                const form = new FormData();
                form.append('action', 'delete');
                form.append('id', id);

                fetch('../backend/manage_menu.php', {
                    method: 'POST',
                    body: form
                })
                .then(() => loadMenuItems())
                .catch(() => alert('Action failed. Check connection.'));
            }
        };
    });

function loadInventory() {
    fetch('../backend/get_inventory.php')
        .then(response => response.text())
        .then(html => {
            inventoryList.innerHTML = html;

            // Attach Add Stock button listeners
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

//Update Stock (Add Stock Button)
function updateStock(itemID, quantity) {
    const formData = new FormData();
    formData.append('itemID', itemID);
    formData.append('quantity', quantity);

    fetch('../backend/update_inventory.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        loadInventory();
    })
    .catch(() => {
        alert('Failed to update stock. Check your connection.');
    });
}

//Load Sales Report
function loadSalesReport() {
    const salesReportContent = document.getElementById('salesReportContent');
    let salesStartDate = document.getElementById('salesStartDate').value;
    let salesEndDate = document.getElementById('salesEndDate').value;

    // Default: last 7 days
    if (!salesEndDate) salesEndDate = new Date().toISOString().split('T')[0];
    if (!salesStartDate) {
        const d = new Date();
        d.setDate(d.getDate() - 7);
        salesStartDate = d.toISOString().split('T')[0];
    }

    salesReportContent.innerHTML = '<p class="loading">Loading sales report...</p>';

    const url = `../backend/get_sales_report.php?startDate=${salesStartDate}&endDate=${salesEndDate}`;
    fetch(url)
        .then(response => response.text())
        .then(html => {
            salesReportContent.innerHTML = html;
        })
        .catch(() => {
            salesReportContent.innerHTML = '<p class="error">Failed to load sales report.</p>';
        });
}

//Load Stock Report
function loadStockReport() {
    const stockReportContent = document.getElementById('stockReportContent');
    stockReportContent.innerHTML = '<p class="loading">Loading stock report...</p>';

    fetch('../backend/get_stock_report.php')
        .then(response => response.text())
        .then(html => {
            stockReportContent.innerHTML = html;
        })
        .catch(() => {
            stockReportContent.innerHTML = '<p class="error">Failed to load stock report.</p>';
        });
}

//Save Item (Add/Edit)
itemForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData();
    const itemIDInput = document.getElementById('itemID').value;

    // Set action
    formData.append('action', itemIDInput ? 'edit' : 'add');

    // Only append id if editing
    if (itemIDInput) {
        formData.append('id', itemIDInput);
    }

    formData.append('name', document.getElementById('itemName').value);
    formData.append('description', document.getElementById('itemDesc').value);
    formData.append('price', document.getElementById('itemPrice').value);
    formData.append('category', document.getElementById('itemCategory').value);
    formData.append('available', '1');

    const imageInput = document.getElementById('itemImage');
    if (imageInput.files.length > 0) {
        formData.append('image', imageInput.files[0]);
    }

    fetch('../backend/manage_menu.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        console.log('Save response:', text);
        document.getElementById('itemForm').style.display = 'none';
        document.getElementById('itemImage').value = '';
        loadMenuItems();
    })
    .catch(() => {
        alert('Save failed. Check connection.');
    });
});

//View Orders
viewOrdersBtn.addEventListener('click', () => {
    ordersModal.style.display = 'block';
    ordersBody.innerHTML = '<tr><td colspan="6">Loading orders...</td></tr>';

    fetch('../backend/get_orders.php')
        .then(response => response.text())
        .then(html => {
            ordersBody.innerHTML = html;
        })
        .catch(() => {
            ordersBody.innerHTML = '<tr><td colspan="6">Failed to load orders.</td></tr>';
        });
});

//Close Orders Modal
function closeModal() {
    ordersModal.style.display = 'none';
}

//On Page Load
document.addEventListener('DOMContentLoaded', () => {
    loadMenuItems();
});