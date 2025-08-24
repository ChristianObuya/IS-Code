// DOM Elements
const usersList = document.getElementById('usersList');
const salesReportContent = document.getElementById('salesReportContent');
const stockReportContent = document.getElementById('stockReportContent');

// --- Tab Switching ---
document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        const tab = btn.dataset.tab;
        document.getElementById(tab).classList.add('active');

        if (tab === 'users') {
            loadUsers();
        } else if (tab === 'sales') {
            loadSalesReport();
        } else if (tab === 'stock') {
            loadStockReport();
        }
    });
});

// Load Users
function loadUsers() {
    usersList.innerHTML = '<tr><td colspan="6" class="loading">Loading users...</td></tr>';
    
    fetch('../backend/get_all_users.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            usersList.innerHTML = html;
            // Add event listeners to delete buttons
            document.querySelectorAll('.delete-user-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const userId = e.target.dataset.userId;
                    deleteUser(userId);
                });
            });
        })
        .catch((error) => {
            console.error('Error:', error);
            usersList.innerHTML = '<tr><td colspan="6">Failed to load users. Please try again.</td></tr>';
        });
}

// Delete User
function deleteUser(userId) {
    if (confirm('Are you sure you want to delete this user?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', userId);
        
        fetch('../backend/user_management.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(result => {
            alert(result);
            loadUsers(); // Reload the user list
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to delete user.');
        });
    }
}

// Remove the add user button functionality since we're only allowing deletes
document.addEventListener('DOMContentLoaded', () => {
    const addUserBtn = document.getElementById('addUserBtn');
    if (addUserBtn) {
        addUserBtn.style.display = 'none';
    }
});

// Load Sales Report
function loadSalesReport() {
    let salesStartDate = document.getElementById('salesStartDate').value || '';
    let salesEndDate = document.getElementById('salesEndDate').value || '';

    if (!salesEndDate) salesEndDate = new Date().toISOString().split('T')[0];
    if (!salesStartDate) {
        const d = new Date();
        d.setDate(d.getDate() - 30); // Default to 30 days
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

// Load Stock Report
function loadStockReport() {
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

// Generate Sales Report
document.getElementById('generateSalesReport').addEventListener('click', loadSalesReport);

// On Load
document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
});