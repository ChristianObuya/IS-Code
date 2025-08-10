// DOM Elements
var paymentItems = document.getElementById('paymentItems');
var paymentTotal = document.getElementById('paymentTotal');
var phoneInput = document.getElementById('phone');
var payBtn = document.getElementById('payBtn');
var paymentMsg = document.getElementById('paymentMsg');

// Get URL parameters
function getUrlParameter(name) {
    var urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

var orderID = getUrlParameter('orderID');
var totalAmount = getUrlParameter('totalAmount');

// On Load
document.addEventListener('DOMContentLoaded', function () {
    if (!orderID || !totalAmount || isNaN(orderID) || isNaN(totalAmount)) {
        alert('Invalid order data.');
        window.location.href = 'student_home.php';
        return;
    }

    // Display total
    paymentTotal.textContent = parseFloat(totalAmount).toFixed(2);

    // Fetch order details
    fetch('../backend/place_order.php?orderID=' + orderID)
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {
            data = data.trim();

            if (data === 'not_found') {
                alert('Order not found or already processed.');
                window.location.href = 'student_home.php';
                return;
            }

            var parts = data.split('|');
            var dbOrderID = parts[0];
            var totalAmount = parts[1];
            var itemsData = decodeURIComponent(parts[2]);

            var items = JSON.parse(itemsData);

            // Display items
            paymentItems.innerHTML = '';
            let total = 0;

            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                var itemTotal = item.price * item.quantity;
                total += itemTotal;

                var li = document.createElement('li');
                li.textContent = `${item.name} × ${item.quantity} - KES ${itemTotal.toFixed(2)}`;
                paymentItems.appendChild(li);
            }

            // Store for payment
            window.currentOrderID = dbOrderID;
            window.currentTotal = total;
        })
        .catch(function () {
            alert('Failed to load order. Please check your connection.');
            window.location.href = 'student_home.php';
        });
});

// Handle Pay Button Click
// Handle Pay Button Click
payBtn.addEventListener('click', function (e) {
    e.preventDefault();
    paymentMsg.textContent = '';

    var phone = phoneInput.value.trim();

    if (phone === '' || !/^(07)\d{8}$/.test(phone)) {
        paymentMsg.style.color = 'red';
        paymentMsg.textContent = 'Please enter a valid M-Pesa number (e.g., 0712345678).';
        return;
    }

    paymentMsg.style.color = 'green';
    paymentMsg.textContent = 'Payment confirmed! Generating receipt...';
    payBtn.disabled = true;

    var formData = new FormData();
    formData.append('orderID', window.currentOrderID);

    fetch('../backend/generate_receipt.php', {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        return response.text();  // ← Changed from .json() to .text()
    })
    .then(function (text) {
        text = text.trim();
        if (text === 'success') {
            window.location.href = 'receipt.html?orderID=' + window.currentOrderID;
        } else {
            alert('Receipt failed: ' + text);
        }
    })
    .catch(function (error) {
        alert('Network error: ' + error.message);
        paymentMsg.style.color = 'red';
        paymentMsg.textContent = 'Failed to process payment.';
        payBtn.disabled = false;
    });
});