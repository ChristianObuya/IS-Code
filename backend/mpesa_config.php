<?php
// MPESA API Configuration (SANDBOX - TESTING)
define('MPESA_CONSUMER_KEY', 'aCX6gJPeoJlFPdnJhkBemWO00BBMuCGqRumbf7Z9vs8bHsuk');
define('MPESA_CONSUMER_SECRET', '89IjRAf3SY3fLhHFUqA1A0oNdZGxDlFWjoxJOWqyZ3BMTqZIRJQq1EaUkb0UjPPh');
define('MPESA_SHORTCODE', '174379'); // Sandbox shortcode (use this exact number)
define('MPESA_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'); // Sandbox passkey
define('MPESA_CALLBACK_URL', 'https://example.com'); // Update with your callback URL
define('MPESA_ENVIRONMENT', 'sandbox'); // Change to 'production' when live

// MPESA API URLs
if (MPESA_ENVIRONMENT === 'sandbox') {
    define('MPESA_AUTH_URL', 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STKPUSH_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    define('MPESA_QUERY_URL', 'https://sandbox.safaricom.co.ke/mpesa/stkpushquery/v1/query');
} else {
    define('MPESA_AUTH_URL', 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    define('MPESA_STKPUSH_URL', 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    define('MPESA_QUERY_URL', 'https://api.safaricom.co.ke/mpesa/stkpushquery/v1/query');
}
?>