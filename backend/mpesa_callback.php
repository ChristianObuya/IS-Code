<?php
include 'config.php';

// Log the incoming request
$callbackData = file_get_contents('php://input');
file_put_contents('mpesa_log.txt', date('Y-m-d H:i:s') . " - " . $callbackData . "\n", FILE_APPEND);

$data = json_decode($callbackData, true);

if (isset($data['Body']['stkCallback'])) {
    $callback = $data['Body']['stkCallback'];
    $resultCode = $callback['ResultCode'];
    $resultDesc = $callback['ResultDesc'];
    $checkoutRequestID = $callback['CheckoutRequestID'];
    
    if ($resultCode == 0) {
        // Payment successful
        $metadata = $callback['CallbackMetadata']['Item'];
        
        $amount = $mpesaReceiptNumber = $transactionDate = $phoneNumber = '';
        
        foreach ($metadata as $item) {
            switch ($item['Name']) {
                case 'Amount':
                    $amount = $item['Value'];
                    break;
                case 'MpesaReceiptNumber':
                    $mpesaReceiptNumber = $item['Value'];
                    break;
                case 'TransactionDate':
                    $transactionDate = $item['Value'];
                    break;
                case 'PhoneNumber':
                    $phoneNumber = $item['Value'];
                    break;
            }
        }
        
        // Update order with MPESA details
        $updateQuery = "UPDATE `Order` SET 
                        transactionID = '$mpesaReceiptNumber',
                        status = 'collected',
                        mpesa_phone = '$phoneNumber'
                        WHERE checkout_request_id = '$checkoutRequestID'";
        
        if (mysqli_query($connectdb, $updateQuery)) {
            error_log("MPESA Payment Successful: Receipt: $mpesaReceiptNumber");
        }
    } else {
        // Payment failed
        error_log("MPESA Payment Failed: $resultDesc");
    }
    
    // Always respond to MPESA
    header('Content-Type: application/json');
    echo json_encode(["ResultCode" => 0, "ResultDesc" => "Success"]);
    
} else {
    header('Content-Type: application/json');
    echo json_encode(["ResultCode" => 1, "ResultDesc" => "Failed"]);
}
?>