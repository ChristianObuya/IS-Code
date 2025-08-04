// DOM Elements
const receiptID = document.getElementById('receiptID');
const orderID = document.getElementById('orderID');
const receiptTime = document.getElementById('receiptTime');
const transactionID = document.getElementById('transactionID');
const receiptItems = document.getElementById('receiptItems');
const receiptTotal = document.getElementById('receiptTotal');

// Load receipt data from localStorage
const receiptData = JSON.parse(localStorage.getItem('lastReceipt'));

if (!receiptData) {
    window.location.href = 'student_home.html';
}

// Generate fake receipt ID
const generatedReceiptID = 'RB' + Date.now().toString().slice(-6);
const generatedOrderID = 'ORD' + Date.now().toString().slice(-6);

// Populate receipt
receiptID.textContent = generatedReceiptID;
orderID.textContent = generatedOrderID;
receiptTime.textContent = new Date().toLocaleString();
transactionID.textContent = receiptData.transactionID;

// Order items
receiptItems.innerHTML = '';
let total = 0;

receiptData.items.forEach(item => {
    const li = document.createElement('li');
    li.innerHTML = `
        ${item.name} × ${item.quantity} 
        <span>KES ${(item.price * item.quantity).toFixed(2)}</span>
    `;
    receiptItems.appendChild(li);
    total += item.price * item.quantity;
});

receiptTotal.textContent = total.toFixed(2);