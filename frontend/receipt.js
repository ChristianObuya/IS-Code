document.addEventListener('DOMContentLoaded', function () {
    var urlParams = new URLSearchParams(window.location.search);
    var orderID = urlParams.get('orderID');

    if (!orderID) {
        alert('Receipt data missing.');
        window.location.href = 'student_home.php';
        return;
    }

    // Set static values
    document.getElementById('orderID').textContent = orderID;
    document.getElementById('receiptID').textContent = 'RCPT' + orderID;
    document.getElementById('transactionID').textContent = 'MPESA-' + orderID;

    var receiptItemsEl = document.getElementById('receiptItems');
    var receiptTotalEl = document.getElementById('receiptTotal');
    receiptItemsEl.innerHTML = '';

    // Load order items
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

    // Status Elements
    var statusTextEl = document.getElementById('orderStatusText');
    var receiptTimeEl = document.getElementById('receiptTime');
    var completionTimeEl = document.getElementById('completionTime');

    // Function to fetch and update status
    function updateStatus() {
        fetch('../backend/get_order_status.php?orderID=' + orderID)
            .then(function (response) {
                return response.text();
            })
            .then(function (data) {
                data = data.trim();

                if (data === 'null' || data === 'Order not found' || data === 'Not authorized') {
                    return;
                }

                var parts = data.split('|');
                var status = parts[0];
                var orderTime = parts[1];
                var completionTime = parts[2];

                if (status !== 'null') {
                    statusTextEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    receiptTimeEl.textContent = new Date(orderTime).toLocaleString();

                    if (status === 'ready' || status === 'collected') {
                        var banner = document.getElementById('receiptStatusBanner');
                        if (status === 'ready') {
                            banner.style.background = '#d4edda';
                            banner.style.color = '#155724';
                        } else if (status === 'collected') {
                            banner.style.background = '#e2e3e5';
                            banner.style.color = '#383d41';
                        }
                    }

                    if (completionTime !== 'null') {
                        completionTimeEl.textContent = new Date(completionTime).toLocaleString();
                    } else {
                        completionTimeEl.textContent = 'In progress...';
                    }
                }
            })
            .catch(function () {
                statusTextEl.textContent = 'Status: Error';
            });
    }

    // Initial status load
    updateStatus();

    // Poll every 5 seconds
    setInterval(updateStatus, 5000);
});