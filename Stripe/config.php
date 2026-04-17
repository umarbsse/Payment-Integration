<?php
/**
 * Stripe Payment Configuration
 * Uses environment variables for credentials (production-safe)
 */

// Environment detection
$isProduction = getenv('APP_ENV') === 'production' || php_sapi_name() === 'cli-server';

// API Credentials (from environment variables)
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: 'sk_test_your_secret_key_here');
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_your_publishable_key_here');

// App Settings
define('APP_NAME', getenv('APP_NAME') ?: 'Payment Service');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('DEFAULT_CURRENCY', getenv('DEFAULT_CURRENCY') ?: 'usd');
define('APP_ENV', $isProduction ? 'production' : 'development');

// Stripe API
define('STRIPE_API_BASE_URL', 'https://api.stripe.com/v1');
define('STRIPE_API_VERSION', '2022-11-15');
define('STRIPE_API_TIMEOUT', 30);
define('STRIPE_RETRY_ATTEMPTS', 3);

// Payment Limits
define('MIN_PAYMENT_AMOUNT', 50);  // $0.50
define('MAX_PAYMENT_AMOUNT', 999999);  // $9,999.99

// Logging
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true' || !$isProduction);
define('LOG_FILE_PATH', getenv('LOG_FILE_PATH') ?: __DIR__ . '/logs/stripe_api.log');

// Setup
if (!is_dir(dirname(LOG_FILE_PATH))) {
    @mkdir(dirname(LOG_FILE_PATH), 0755, true);
}

if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        if (!getenv($key)) putenv("$key=$value");
    }
}

// Functions
function isStripeConfigured(): bool {
    return STRIPE_SECRET_KEY !== 'sk_test_your_secret_key_here' && 
           STRIPE_PUBLISHABLE_KEY !== 'pk_test_your_publishable_key_here';
}

function getStripePublishableKey(): string {
    return STRIPE_PUBLISHABLE_KEY;
}

function getStripeRequestHeaders(): array {
    return [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded',
        'Stripe-Version: ' . STRIPE_API_VERSION,
    ];
}

?>
