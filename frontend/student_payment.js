// DOM Elements
const paymentItems = document.getElementById('paymentItems');
const paymentTotal = document.getElementById('paymentTotal');
const phoneInput = document.getElementById('phone');
const payBtn = document.getElementById('payBtn');
const paymentMsg = document.getElementById('paymentMsg');

// Load pending order from localStorage
const pendingOrder = JSON.parse(localStorage.getItem('pendingOrder'));

if (!pendingOrder) {
    window.location.href = 'student_home.html';
}

// Display order summary
function loadOrderSummary() {
    paymentItems.innerHTML = '';
    let total = 0;

    pendingOrder.items.forEach(item => {
        const li = document.createElement('li');
        li.innerHTML = `
            ${item.name} × ${item.quantity} 
            <span>KES ${(item.price * item.quantity).toFixed(2)}</span>
        `;
        paymentItems.appendChild(li);
        total += item.price * item.quantity;
    });

    paymentTotal.textContent = total.toFixed(2);
}

// Simulate M-Pesa Payment
// Simulate M-Pesa Payment
payBtn.addEventListener('click', async () => {
    const phone = phoneInput.value.trim();
    paymentMsg.textContent = '';
    paymentMsg.style.color = 'red';

    if (!phone) {
        paymentMsg.textContent = 'Please enter your phone number.';
        return;
    }

    if (!/^(07|2547)\d{8}$/.test(phone.replace(/^0/, '254'))) {
        paymentMsg.textContent = 'Enter a valid Kenyan phone number.';
        return;
    }

    paymentMsg.textContent = 'Processing payment...';
    payBtn.disabled = true;

    try {
        // First: Confirm order exists in DB (it should already be there from student_home)
        // But if not, we can re-send here — for safety
        const pendingOrder = JSON.parse(localStorage.getItem('pendingOrder'));
        const studentID = 3; // In full system, get from session

        // Optional: Re-send order if not already saved
        // But ideally, it was saved in student_home.js

        // Simulate payment success
        await new Promise(resolve => setTimeout(resolve, 1500));

        const transactionID = 'MPESA' + Date.now();

        // Save receipt data
        const receiptData = {
            ...pendingOrder,
            transactionID,
            paymentTime: new Date().toISOString()
        };

        localStorage.setItem('lastReceipt', JSON.stringify(receiptData));
        localStorage.removeItem('pendingOrder');

        paymentMsg.style.color = 'green';
        paymentMsg.textContent = 'Payment confirmed! Redirecting...';

        setTimeout(() => {
            window.location.href = 'receipt.html';
        }, 1000);

    } catch (error) {
        paymentMsg.textContent = 'Payment failed. Please try again.';
        payBtn.disabled = false;
        console.error(error);
    }
});

// On Load
document.addEventListener('DOMContentLoaded', () => {
    loadOrderSummary();
});