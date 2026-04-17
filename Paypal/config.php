<?php
// PayPal API Credentials
const PAYPAL_CLIENT_ID = 'your_sandbox_client_id_here';
const PAYPAL_CLIENT_SECRET = 'your_sandbox_client_secret_here';

// PayPal Environment Settings
const PAYPAL_ENVIRONMENT = 'sandbox';
const PAYPAL_BASE_URL = (PAYPAL_ENVIRONMENT === 'sandbox')
    ? 'https://api-m.sandbox.paypal.com'
    : 'https://api-m.paypal.com';

// Application URLs
const BASE_URL = 'http://localhost/paypal';
const RETURN_URL = BASE_URL . '/success.php';
const CANCEL_URL = BASE_URL . '/cancel.php';

// Currency settings
const DEFAULT_CURRENCY = 'USD';

// Timeout for API requests (in seconds)
const API_TIMEOUT = 30;
?>