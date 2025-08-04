document.addEventListener('DOMContentLoaded', () => {
    // --- 1. Get data from URL and localStorage ---
    const urlParams = new URLSearchParams(window.location.search);
    const orderID = urlParams.get('orderID');
    const transactionID = urlParams.get('transactionID');
    const cartItemsData = JSON.parse(localStorage.getItem('lastReceiptCart'));

    // --- 2. Validate data and redirect if missing ---
    if (!orderID || !transactionID || !cartItemsData) {
        alert('Receipt data is missing. Returning to home page.');
        window.location.href = 'student_home.html';
        return;
    }

    // --- 3. Populate static receipt details ---
    document.getElementById('orderID').textContent = orderID;
    document.getElementById('transactionID').textContent = transactionID;
    document.getElementById('receiptID').textContent = 'RCPT' + orderID;
    // The time will be set from the first successful fetch.

    const receiptItemsEl = document.getElementById('receiptItems');
    const receiptTotalEl = document.getElementById('receiptTotal');
    receiptItemsEl.innerHTML = '';
    let total = 0;

    cartItemsData.forEach(item => {
        const li = document.createElement('li');
        li.innerHTML = `
            ${item.name} × ${item.quantity} 
            <span>KES ${(item.price * item.quantity).toFixed(2)}</span>
        `;
        receiptItemsEl.appendChild(li);
        total += item.price * item.quantity;
    });
    receiptTotalEl.textContent = total.toFixed(2);

    // Clean up local storage after displaying the receipt
    localStorage.removeItem('lastReceiptCart');

    // --- 4. Set up live status polling ---
    const statusTextEl = document.getElementById('orderStatusText');
    const statusBannerEl = document.getElementById('receiptStatusBanner');
    const receiptTimeEl = document.getElementById('receiptTime');
    const completionTimeEl = document.getElementById('completionTime');
    let statusInterval;
    let isFirstFetch = true;

    async function fetchOrderStatus() {
        try {
            const response = await fetch(`../backend/get_order_status.php?orderID=${orderID}`);
            
            if (!response.ok) {
                // Try to get a meaningful error message from the server
                try {
                    const errorResult = await response.json();
                    statusTextEl.textContent = errorResult.message || `Error: ${response.status}`;
                } catch (e) {
                    statusTextEl.textContent = `Error: ${response.status} ${response.statusText}`;
                }
                clearInterval(statusInterval);
                return;
            }

            const result = await response.json();
            if (result.success) {
                const status = result.status;
                statusTextEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

                // On the first successful fetch, set the constant order time
                if (isFirstFetch && result.orderTime) {
                    receiptTimeEl.textContent = new Date(result.orderTime).toLocaleString();
                    isFirstFetch = false;
                }


                // Display completion time if available, otherwise show a placeholder
                if (result.completionTime) {
                    completionTimeEl.textContent = new Date(result.completionTime).toLocaleString();
                } else {
                    completionTimeEl.textContent = 'In progress...';
                }

                // Change color and stop polling when the order is ready or collected
                if (status === 'ready' || status === 'collected') {
                    statusBannerEl.style.background = (status === 'ready') ? '#d4edda' : '#e2e3e5';
                    statusBannerEl.style.color = (status === 'ready') ? '#155724' : '#383d41';
                    clearInterval(statusInterval);
                }
            } else {
                statusTextEl.textContent = result.message;
                clearInterval(statusInterval);
            }
        } catch (error) {
            console.error('Error fetching order status:', error);
            statusTextEl.textContent = 'Network error. Please check connection.';
            clearInterval(statusInterval);
        }
    }

    // Start polling immediately and then every 5 seconds
    fetchOrderStatus();
    statusInterval = setInterval(fetchOrderStatus, 5000);
});
