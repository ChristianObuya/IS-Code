// DOM Elements
const paymentItems = document.getElementById('paymentItems');
const paymentTotal = document.getElementById('paymentTotal');
const phoneInput = document.getElementById('phone');
const payBtn = document.getElementById('payBtn');
const paymentMsg = document.getElementById('paymentMsg');

// Load pending order from localStorage
const pendingOrderCart = JSON.parse(localStorage.getItem('pendingOrder'))?.items;

// Get orderID from URL
const urlParams = new URLSearchParams(window.location.search);
const orderID = urlParams.get('orderID');

if (!pendingOrderCart || !orderID) {
    alert('No pending order found or order ID is missing. Returning to menu.');
    window.location.href = 'student_home.html';
}

// Display order summary
function loadOrderSummary() {
    paymentItems.innerHTML = '';
    let total = 0;

    pendingOrderCart.forEach(item => {
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

payBtn.addEventListener('click', async () => {
    const phone = phoneInput.value.trim();
    paymentMsg.textContent = '';
    paymentMsg.style.color = 'red';

    if (!phone || !/^(07|01)\d{8}$/.test(phone)) {
        paymentMsg.textContent = 'Please enter a valid M-Pesa phone number.';
        return;
    }

    paymentMsg.textContent = 'Confirming payment...';
    payBtn.disabled = true;

    try {
        // Generate a mock transaction ID for the M-Pesa push
        const transactionID = 'RDI' + Date.now().toString().slice(-8).toUpperCase();

        const response = await fetch('../backend/confirm_payment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ orderID, transactionID })
        });

        if (!response.ok) {
            let errorMsg = `Error: ${response.status} ${response.statusText}`;
            try {
                // Try to get a more specific error from the backend
                const errorResult = await response.json();
                errorMsg = errorResult.message || errorMsg;
            } catch (e) {
                // The response was not JSON, which is common for 404 or 500 errors.
            }
            throw new Error(errorMsg);
        }

        const result = await response.json();

        if (result.success) {
            // Save cart items for the receipt page to display
            localStorage.setItem('lastReceiptCart', JSON.stringify(pendingOrderCart));
            localStorage.removeItem('pendingOrder');

            paymentMsg.style.color = 'green';
            paymentMsg.textContent = 'Payment confirmed! Redirecting...';

            // Redirect to the receipt page with the real orderID
            window.location.href = `receipt.html?orderID=${orderID}&transactionID=${transactionID}`;
        } else {
            paymentMsg.textContent = `Error: ${result.message}`;
            payBtn.disabled = false;
        }
    } catch (error) {
        paymentMsg.textContent = error.message; // Display the specific error
        payBtn.disabled = false;
        console.error('Payment confirmation error:', error);
    }
});

// On Load
document.addEventListener('DOMContentLoaded', () => {
    loadOrderSummary();
});