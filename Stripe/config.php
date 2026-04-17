<?php
/**
 * Stripe Configuration File
 * 
 * This file stores all Stripe API credentials and application settings.
 * For production environments, use environment variables instead of hardcoded values.
 * 
 * SECURITY NOTE: Never commit this file with actual API keys to version control.
 * Always use environment variables or a secure configuration management system.
 * 
 * Example of using environment variables:
 * $stripeSecretKey = getenv('STRIPE_SECRET_KEY');
 * $stripePublishableKey = getenv('STRIPE_PUBLISHABLE_KEY');
 * 
 * Or with PHP dotenv library:
 * require_once __DIR__ . '/vendor/autoload.php';
 * $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
 * $dotenv->load();
 * 
 * During local development (XAMPP/WAMP), you can:
 * 1. Set environment variables in your system
 * 2. Create a .env file and load it manually
 * 3. Use a local config file not tracked by git
 */

// ============================================================================
// STRIPE API CREDENTIALS
// ============================================================================

// Stripe Secret Key - Used for server-side API calls
// This should NEVER be exposed to the frontend
// Get this from: https://dashboard.stripe.com/apikeys
define('STRIPE_SECRET_KEY', 'sk_test_your_secret_key_here');

// Stripe Publishable Key - Safe to expose to frontend
// Used for client-side integrations like Stripe.js
// Get this from: https://dashboard.stripe.com/apikeys
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_publishable_key_here');

// ============================================================================
// APPLICATION SETTINGS
// ============================================================================

// Application name - used in payment descriptions
define('APP_NAME', 'My Payment Application');

// Base URL of your application - used for return/redirect URLs
// For local development: 'http://localhost/path-to-app'
// For production: 'https://yourdomain.com'
define('APP_URL', 'http://localhost/payment-integration');

// Currency code (ISO 4217)
// Common options: 'usd', 'eur', 'gbp', 'aud', 'cad', etc.
define('DEFAULT_CURRENCY', 'usd');

// ============================================================================
// STRIPE API ENDPOINTS
// ============================================================================

// Base URL for Stripe API
define('STRIPE_API_BASE_URL', 'https://api.stripe.com/v1');

// API version
define('STRIPE_API_VERSION', '2022-11-15');

// ============================================================================
// PAYMENT SETTINGS
// ============================================================================

// Minimum payment amount in cents (e.g., 50 = $0.50)
// Stripe typically requires at least $0.50 for Credit Cards
define('MIN_PAYMENT_AMOUNT', 50);

// Maximum payment amount in cents (e.g., 999999 = $9,999.99)
define('MAX_PAYMENT_AMOUNT', 999999);

// ============================================================================
// LOGGING AND DEBUGGING
// ============================================================================

// Enable debug mode - logs API responses and errors
// Set to false in production
define('DEBUG_MODE', true);

// Log file path - where API debug logs are stored
define('LOG_FILE_PATH', __DIR__ . '/logs/stripe_api.log');

// ============================================================================
// ENVIRONMENT CHECK
// ============================================================================

// Verify that the config has been properly set up
if (STRIPE_SECRET_KEY === 'sk_test_your_secret_key_here' || 
    STRIPE_PUBLISHABLE_KEY === 'pk_test_your_publishable_key_here') {
    trigger_error(
        'ERROR: Stripe API keys not configured. Please update config.php with your Stripe keys.',
        E_USER_ERROR
    );
}

// Ensure logs directory exists
if (!file_exists(dirname(LOG_FILE_PATH))) {
    mkdir(dirname(LOG_FILE_PATH), 0755, true);
}

/**
 * Helper function to get the publishable key safely
 * Can be included in JavaScript for Stripe.js initialization
 * 
 * @return string The Stripe publishable key
 */
function getStripePublishableKey() {
    return STRIPE_PUBLISHABLE_KEY;
}

/**
 * Helper function to validate that API keys are properly configured
 * 
 * @return bool True if keys are configured, false otherwise
 */
function isStripeConfigured() {
    return (
        !empty(STRIPE_SECRET_KEY) && 
        STRIPE_SECRET_KEY !== 'sk_test_your_secret_key_here' &&
        !empty(STRIPE_PUBLISHABLE_KEY) && 
        STRIPE_PUBLISHABLE_KEY !== 'pk_test_your_publishable_key_here'
    );
}

/**
 * Helper function to get the API base URL with version header
 * 
 * @return array Headers including API version for Stripe requests
 */
function getStripeRequestHeaders() {
    return array(
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded',
        'Stripe-Version: ' . STRIPE_API_VERSION,
        'User-Agent: ' . APP_NAME . '/1.0'
    );
}

?>
