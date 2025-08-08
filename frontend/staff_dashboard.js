// DOM Elements
const menuList = document.getElementById('menuList');
const itemForm = document.getElementById('itemForm');
const itemName = document.getElementById('itemName');
const itemDesc = document.getElementById('itemDesc');
const itemPrice = document.getElementById('itemPrice');
const itemCategory = document.getElementById('itemCategory');
const itemAvailable = document.getElementById('itemAvailable');
const addItemBtn = document.getElementById('addItemBtn');
const cancelBtn = document.getElementById('cancelBtn');

// --- Tab Switching ---
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        // Add active to clicked tab
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById(tab).classList.add('active');

        // Load content
        if (tab === 'menu') loadMenuItems();
        // You can add inventory, sales, etc. later
    });
});

// --- Show Add Form ---
addItemBtn.addEventListener('click', () => {
    itemForm.reset();
    itemForm.style.display = 'block';
    document.getElementById('menuTable').scrollIntoView({ behavior: 'smooth' });
});

// --- Cancel Form ---
cancelBtn.addEventListener('click', () => {
    itemForm.style.display = 'none';
});

// --- Load Menu Items (No try-catch, No JSON) ---
function loadMenuItems() {
    fetch('../backend/get_all_menu_items.php')
        .then(response => response.text())
        .then(html => {
            menuList.innerHTML = html;
            attachMenuButtonListeners(); // Re-add clicks
        })
        .catch(() => {
            menuList.innerHTML = `<tr><td colspan='6'>Failed to load menu. Check connection.</td></tr>`;
        });
}

// --- Re-attach button clicks ---
function attachMenuButtonListeners() {
    // Edit buttons
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.onclick = function () {
            alert("Edit feature requires more setup. For now, please reload and re-add.");
        };
    });

    // Activate/Deactivate buttons
    document.querySelectorAll('.btn-delete, .btn-activate').forEach(btn => {
        btn.onclick = function () {
            const id = this.dataset.id;
            const action = this.textContent.toLowerCase();

            if (confirm(`Are you sure you want to ${action} this item?`)) {
                const form = new FormData();
                form.append('action', 'delete'); // toggle not ready yet
                form.append('id', id);

                fetch('../backend/manage_menu.php', {
                    method: 'POST',
                    body: form
                })
                .then(() => loadMenuItems()) // reload after action
                .catch(() => alert('Action failed. Check connection.'));
            }
        };
    });
}

// --- Save New Item ---
itemForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const form = new FormData();
    form.append('action', 'add');
    form.append('name', itemName.value);
    form.append('description', itemDesc.value);
    form.append('price', itemPrice.value);
    form.append('category', itemCategory.value);
    form.append('available', itemAvailable.value);

    const imageInput = document.getElementById('itemImage');
    if (imageInput.files[0]) {
        form.append('image', imageInput.files[0]);
    }

    fetch('../backend/manage_menu.php', {
        method: 'POST',
        body: form
    })
    .then(() => {
        itemForm.style.display = 'none';
        imageInput.value = '';
        loadMenuItems(); // reload the list
    })
    .catch(() => {
        alert('Save failed. Please check your internet.');
    });
});

// --- Load menu when page opens ---
document.addEventListener('DOMContentLoaded', loadMenuItems);