<?php
session_start();
include 'mpesa_config.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['error' => 'Access denied']);
    exit();
}

$phone = $_POST['phone'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);
$orderID = (int)($_POST['orderID'] ?? 0);

if (empty($phone) || $amount <= 0 || $orderID <= 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

// Format phone number to 254 format
if (substr($phone, 0, 1) === '0') {
    $phone = '254' . substr($phone, 1);
} elseif (substr($phone, 0, 3) !== '254') {
    $phone = '254' . $phone;
}

// Get MPESA access token
$credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MPESA_AUTH_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode != 200) {
    echo json_encode(['error' => 'Failed to connect to MPESA']);
    exit();
}

$result = json_decode($response, true);
$accessToken = $result['access_token'] ?? '';

if (empty($accessToken)) {
    echo json_encode(['error' => 'Failed to get access token']);
    exit();
}

// Prepare STK Push request
$timestamp = date('YmdHis');
$password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

$payload = [
    'BusinessShortCode' => MPESA_SHORTCODE,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => round($amount),
    'PartyA' => $phone,
    'PartyB' => MPESA_SHORTCODE,
    'PhoneNumber' => $phone,
    'CallBackURL' => MPESA_CALLBACK_URL,
    'AccountReference' => 'CAMPUSBITE' . $orderID,
    'TransactionDesc' => 'Payment for Order #' . $orderID
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MPESA_STKPUSH_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpcode == 200 && isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
    // Save checkout request ID to database
    $checkoutRequestID = $result['CheckoutRequestID'];
    $updateSql = "UPDATE `Order` SET checkout_request_id = '$checkoutRequestID' WHERE orderID = $orderID";
    mysqli_query($connectdb, $updateSql);
    
    echo json_encode(['success' => true, 'message' => 'Payment initiated']);
} else {
    $error = $result['errorMessage'] ?? 'Unknown error occurred';
    echo json_encode(['error' => 'MPESA error: ' . $error]);
}
?>