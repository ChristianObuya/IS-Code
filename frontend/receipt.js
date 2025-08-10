document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var orderID = urlParams.get('orderID');

    if (!orderID) {
        alert('Receipt data missing.');
        window.location.href = 'student_home.php';
        return;
    }

    document.getElementById('orderID').textContent = orderID;
    document.getElementById('receiptID').textContent = 'RCPT' + orderID;
    document.getElementById('transactionID').textContent = 'MPESA-' + orderID;

    var receiptItemsEl = document.getElementById('receiptItems');
    var receiptTotalEl = document.getElementById('receiptTotal');
    receiptItemsEl.innerHTML = '';

    fetch('../backend/place_order.php?orderID=' + orderID)
        .then(function (response) {
            return response.text();
        })
        .then(function (data) {
            data = data.trim();

            if (data === 'not_found') {
                alert('Order not found.');
                window.location.href = 'student_home.php';
                return;
            }

            var parts = data.split('|');
            var totalAmount = parts[1];
            var itemsData = decodeURIComponent(parts[2]);
            var items = JSON.parse(itemsData);

            var total = 0;
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                var itemTotal = item.price * item.quantity;
                total += itemTotal;

                var li = document.createElement('li');
                li.innerHTML = `
                    ${item.name} × ${item.quantity} 
                    <span>KES ${itemTotal.toFixed(2)}</span>
                `;
                receiptItemsEl.appendChild(li);
            }

            receiptTotalEl.textContent = total.toFixed(2);
        })
        .catch(function () {
            alert('Failed to load receipt.');
            window.location.href = 'student_home.php';
        });

    var statusTextEl = document.getElementById('orderStatusText');
    var receiptTimeEl = document.getElementById('receiptTime');
    var completionTimeEl = document.getElementById('completionTime');

    statusTextEl.textContent = 'Paid';
    receiptTimeEl.textContent = new Date().toLocaleString();
    completionTimeEl.textContent = 'In progress...';

    fetch('../backend/get_order_status.php?orderID=' + orderID)
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
            if (result.success) {
                statusTextEl.textContent = result.status.charAt(0).toUpperCase() + result.status.slice(1);
                if (result.orderTime) {
                    receiptTimeEl.textContent = new Date(result.orderTime).toLocaleString();
                }
                if (result.completionTime) {
                    completionTimeEl.textContent = new Date(result.completionTime).toLocaleString();
                }
            }
        })
        .catch(function () {
            statusTextEl.textContent = 'Status: Error';
        });
});