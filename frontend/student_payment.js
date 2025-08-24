console.log('student_payment.js loaded successfully');

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded');
    
    // Get DOM elements SAFELY
    var paymentItems = document.getElementById('paymentItems');
    var paymentTotal = document.getElementById('paymentTotal');
    var phoneInput = document.getElementById('phone');
    var payBtn = document.getElementById('payBtn');
    var paymentStatus = document.getElementById('paymentStatus');

    // Debug: Check if elements exist
    console.log('Elements found:', {
        paymentItems: paymentItems,
        paymentTotal: paymentTotal,
        phoneInput: phoneInput,
        payBtn: payBtn,
        paymentStatus: paymentStatus
    });

    // Get URL parameters
    function getUrlParameter(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    var orderID = getUrlParameter('orderID');
    var totalAmount = getUrlParameter('totalAmount');

    console.log('URL parameters:', { orderID: orderID, totalAmount: totalAmount });

    if (!orderID || !totalAmount || isNaN(orderID) || isNaN(totalAmount)) {
        alert('Invalid order data.');
        window.location.href = 'student_home.php';
        return;
    }

    // SAFELY Display total - Check if element exists first
    if (paymentTotal) {
        paymentTotal.textContent = parseFloat(totalAmount).toFixed(2);
        console.log('Total displayed:', totalAmount);
    } else {
        console.error('paymentTotal element not found!');
        alert('Error: Cannot display total amount');
        return;
    }

    // Setup button click event
    if (payBtn) {
        payBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Pay button clicked!');
            handlePayment();
        });
    } else {
        console.error('Pay button not found!');
    }

    // Payment handler function
    function handlePayment() {
        console.log('handlePayment called');
        
        // Check if phone input exists
        if (!phoneInput) {
            console.error('phoneInput not found!');
            alert('Error: Phone input not found');
            return;
        }
        
        var phone = phoneInput.value.trim();
        console.log('Phone number:', phone);
        
        if (!phone) {
            alert('Please enter a phone number');
            return;
        }
        
        // Validate phone format
        if (!/^(07|2547)\d{8}$/.test(phone)) {
            alert('Please enter a valid M-Pesa number (e.g., 0712345678 or 254712345678).');
            return;
        }

        // Convert phone to 254 format if it's in 07 format
        if (phone.startsWith('07')) {
            phone = '254' + phone.substring(1);
        }

        // Show payment status
        if (paymentStatus) {
            paymentStatus.style.display = 'block';
            paymentStatus.style.backgroundColor = '#e3f2fd';
            paymentStatus.style.color = '#1565c0';
            paymentStatus.textContent = 'Initiating MPESA payment...';
        }

        // Disable button
        if (payBtn) {
            payBtn.disabled = true;
        }

        console.log('Sending MPESA request:', phone, totalAmount, orderID);

        // Send MPESA payment request
        var formData = new FormData();
        formData.append('phone', phone);
        formData.append('amount', totalAmount);
        formData.append('orderID', orderID);

        fetch('../backend/initiate_mpesa.php', {
            method: 'POST',
            body: formData
        })
        .then(function (response) {
            console.log('MPESA response status:', response.status);
            return response.json();
        })
        .then(function (data) {
            console.log('MPESA response data:', data);
            if (data.success) {
                if (paymentStatus) {
                    paymentStatus.style.backgroundColor = '#e8f5e8';
                    paymentStatus.style.color = '#2e7d32';
                    paymentStatus.textContent = 'MPESA payment initiated! Check your phone to complete the payment.';
                }
                
                // Start checking payment status
                checkMpesaPaymentStatus();
                
                // SANDBOX FIX: Automatically complete after 8 seconds
                setTimeout(function() {
                    completeSandboxPayment();
                }, 8000);
                
            } else {
                if (paymentStatus) {
                    paymentStatus.style.backgroundColor = '#ffebee';
                    paymentStatus.style.color = '#c62828';
                    paymentStatus.textContent = 'Payment failed: ' + (data.error || 'Unknown error');
                }
                if (payBtn) payBtn.disabled = false;
            }
        })
        .catch(function (error) {
            console.error('MPESA fetch error:', error);
            if (paymentStatus) {
                paymentStatus.style.backgroundColor = '#ffebee';
                paymentStatus.style.color = '#c62828';
                paymentStatus.textContent = 'Network error: ' + error.message;
            }
            if (payBtn) payBtn.disabled = false;
        });
    }

    // Function to check MPESA payment status
    function checkMpesaPaymentStatus() {
        console.log('Starting payment status check...');
        var checkInterval = setInterval(function() {
            console.log('Checking payment status for order:', orderID);
            
            fetch('../backend/check_payment.php?orderID=' + orderID)
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                console.log('Payment status response:', data);
                if (data.paid) {
                    clearInterval(checkInterval);
                    if (paymentStatus) {
                        paymentStatus.style.backgroundColor = '#e8f5e8';
                        paymentStatus.style.color = '#2e7d32';
                        paymentStatus.textContent = 'Payment successful! Redirecting...';
                    }
                    
                    // Redirect to receipt
                    setTimeout(function() {
                        window.location.href = 'receipt.html?orderID=' + orderID;
                    }, 2000);
                } else if (data.error) {
                    clearInterval(checkInterval);
                    if (paymentStatus) {
                        paymentStatus.style.backgroundColor = '#ffebee';
                        paymentStatus.style.color = '#c62828';
                        paymentStatus.textContent = 'Payment check failed: ' + data.error;
                    }
                    if (payBtn) payBtn.disabled = false;
                }
                // If not paid yet, continue checking
            })
            .catch(function(error) {
                console.error('Payment check error:', error);
            });
        }, 3000); // Check every 3 seconds
    }

    // SANDBOX: Function to automatically complete payment
    function completeSandboxPayment() {
        console.log('Sandbox: Automatically completing payment');
        
        if (paymentStatus) {
            paymentStatus.style.backgroundColor = '#fff3cd';
            paymentStatus.style.color = '#856404';
            paymentStatus.textContent = 'Sandbox: Simulating payment completion...';
        }
        
        fetch('../backend/complete_sandbox_payment.php?orderID=' + orderID)
        .then(function(response) {
            return response.text();
        })
        .then(function(result) {
            if (result === 'success') {
                console.log('Sandbox payment completed successfully');
                
                if (paymentStatus) {
                    paymentStatus.style.backgroundColor = '#e8f5e8';
                    paymentStatus.style.color = '#2e7d32';
                    paymentStatus.textContent = 'Payment successful! Redirecting...';
                }
                
                // Redirect to receipt
                setTimeout(function() {
                    window.location.href = 'receipt.html?orderID=' + orderID;
                }, 2000);
            } else {
                console.error('Sandbox completion failed:', result);
                if (paymentStatus) {
                    paymentStatus.style.backgroundColor = '#ffebee';
                    paymentStatus.style.color = '#c62828';
                    paymentStatus.textContent = 'Sandbox error: ' + result;
                }
                if (payBtn) payBtn.disabled = false;
            }
        })
        .catch(function(error) {
            console.error('Sandbox completion error:', error);
            if (paymentStatus) {
                paymentStatus.style.backgroundColor = '#ffebee';
                paymentStatus.style.color = '#c62828';
                paymentStatus.textContent = 'Network error: ' + error.message;
            }
            if (payBtn) payBtn.disabled = false;
        });
    }
});