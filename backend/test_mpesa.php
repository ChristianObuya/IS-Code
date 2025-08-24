<?php
include 'mpesa_config.php';

echo "<h2>MPESA Connection Test</h2>";

// Test access token
$credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, MPESA_AUTH_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200) {
    echo "SUCCESS: Connected to MPESA API<br>";
    echo "Consumer Key: " . substr(MPESA_CONSUMER_KEY, 0, 10) . "...<br>";
    echo "Environment: " . MPESA_ENVIRONMENT . "<br>";
} else {
    echo "FAILED: Cannot connect to MPESA (HTTP $httpcode)<br>";
    echo "Response: " . htmlspecialchars($response) . "<br>";
}

echo "<h3>Test Instructions:</h3>";
echo "1. Use phone: <strong>254708374149</strong><br>";
echo "2. Use PIN: <strong>1234</strong><br>";
echo "3. Check browser console for errors<br>";
echo "4. Check mpesa_log.txt for callbacks<br>";
?>