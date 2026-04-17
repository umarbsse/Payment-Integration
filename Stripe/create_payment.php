<?php
/**
 * Create Payment Intent Page
 * 
 * This is the main entry point for initiating a payment.
 * It creates a Stripe Payment Intent and returns the client_secret to the frontend.
 * 
 * The frontend then uses Stripe.js or a checkout form to complete the payment.
 * After successful payment, the user is redirected to success.php.
 * 
 * FLOW:
 * 1. User submits payment form with amount
 * 2. Server-side validation of amount
 * 3. Create Payment Intent via Stripe API
 * 4. Return client_secret to frontend (as JSON)
 * 5. Frontend uses client_secret with Stripe.js to confirm payment
 * 6. On success, redirect to success.php with payment_intent_id
 * 
 * SECURITY CONSIDERATIONS:
 * - Always validate amount on server side - never trust frontend input
 * - Check that amount is within allowed range
 * - Ensure amount matches order details
 * - Use HTTPS in production
 * - Sanitize all user inputs
 */

// Include configuration and Stripe client
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

// Set response header for JSON responses
header('Content-Type: application/json');

/**
 * Validate the payment amount
 * 
 * Ensures the amount is:
 * - A valid number
 * - Within acceptable range
 * - Greater than minimum amount
 * 
 * @param mixed $amount Amount in cents from request
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateAmount($amount) {
    // Check that amount is provided
    if (empty($amount)) {
        return array('valid' => false, 'error' => 'Amount is required');
    }
    
    // Convert to integer and validate it's numeric
    $amount = intval($amount);
    if (!is_numeric($amount) || $amount <= 0) {
        return array('valid' => false, 'error' => 'Amount must be a positive number');
    }
    
    // Check minimum amount (default: $0.50 USD)
    if ($amount < MIN_PAYMENT_AMOUNT) {
        return array(
            'valid' => false,
            'error' => 'Minimum payment amount is $' . (MIN_PAYMENT_AMOUNT / 100)
        );
    }
    
    // Check maximum amount
    if ($amount > MAX_PAYMENT_AMOUNT) {
        return array(
            'valid' => false,
            'error' => 'Maximum payment amount is $' . (MAX_PAYMENT_AMOUNT / 100)
        );
    }
    
    return array('valid' => true, 'error' => null);
}

/**
 * Validate the currency code
 * 
 * @param string $currency Currency code (e.g., 'usd', 'eur')
 * @return bool True if valid currency code
 */
function isValidCurrency($currency) {
    // List of supported currencies (you can expand this)
    $supportedCurrencies = array(
        'usd', 'eur', 'gbp', 'aud', 'cad', 'nzd', 'chf', 'cny', 'inr', 'jpy', 'mxn', 'sgd', 'hkd'
    );
    
    // Check if currency is in supported list
    return in_array(strtolower($currency), $supportedCurrencies);
}

/**
 * Create a new Payment Intent for the customer
 * 
 * This is the main business logic that:
 * 1. Validates input parameters
 * 2. Creates a Payment Intent with Stripe
 * 3. Returns the client_secret and payment intent ID
 * 
 * @return array Response to send to frontend
 */
function createPayment() {
    // Get payment details from request
    // In a real application, this would come from an order/cart system
    $paymentAmount = isset($_POST['amount']) ? $_POST['amount'] : (isset($_GET['amount']) ? $_GET['amount'] : null);
    $currency = isset($_POST['currency']) ? $_POST['currency'] : DEFAULT_CURRENCY;
    $description = isset($_POST['description']) ? $_POST['description'] : APP_NAME . ' Payment';
    $orderId = isset($_POST['order_id']) ? $_POST['order_id'] : null;
    
    // Step 1: Validate the payment amount (CRITICAL - never trust frontend)
    $validationResult = validateAmount($paymentAmount);
    if (!$validationResult['valid']) {
        http_response_code(400);
        return array(
            'success' => false,
            'error' => $validationResult['error']
        );
    }
    
    // Step 2: Validate currency
    if (!isValidCurrency($currency)) {
        http_response_code(400);
        return array(
            'success' => false,
            'error' => 'Invalid currency code'
        );
    }
    
    // Step 3: Verify Stripe is configured
    if (!isStripeConfigured()) {
        http_response_code(500);
        return array(
            'success' => false,
            'error' => 'Payment system not configured'
        );
    }
    
    // Step 4: Create Stripe API client
    try {
        $stripeClient = new StripeClient(
            STRIPE_SECRET_KEY,
            STRIPE_API_BASE_URL,
            DEBUG_MODE,
            LOG_FILE_PATH
        );
    } catch (Exception $e) {
        http_response_code(500);
        return array(
            'success' => false,
            'error' => 'Failed to initialize payment processor'
        );
    }
    
    // Step 5: Prepare metadata for the Payment Intent
    // This helps track orders and customer information within Stripe
    $metadata = array();
    if (!empty($orderId)) {
        $metadata['order_id'] = $orderId;
    }
    $metadata['created_at'] = date('Y-m-d H:i:s');
    $metadata['app'] = APP_NAME;
    
    // Step 6: Create the Payment Intent with Stripe
    // The client_secret will be returned to the frontend to confirm the payment
    $createResponse = $stripeClient->createPaymentIntent(
        $paymentAmount,           // Amount in cents
        $currency,                // Currency code
        $description,             // User-friendly description
        $metadata                 // Metadata for tracking
    );
    
    // Step 7: Handle API response
    if (!$createResponse['success']) {
        // Payment Intent creation failed
        http_response_code(400);
        
        // Provide user-friendly error message
        $errorMessage = 'Failed to create payment - ';
        if (isset($createResponse['error']['message'])) {
            $errorMessage .= $createResponse['error']['message'];
        } else {
            $errorMessage .= 'Please try again later';
        }
        
        return array(
            'success' => false,
            'error' => $errorMessage
        );
    }
    
    // Step 8: Extract important data from Payment Intent
    $paymentIntentData = $createResponse['data'];
    $clientSecret = isset($paymentIntentData['client_secret']) ? $paymentIntentData['client_secret'] : null;
    $paymentIntentId = isset($paymentIntentData['id']) ? $paymentIntentData['id'] : null;
    $status = isset($paymentIntentData['status']) ? $paymentIntentData['status'] : 'unknown';
    
    // Validate we got the client secret
    if (empty($clientSecret) || empty($paymentIntentId)) {
        http_response_code(500);
        return array(
            'success' => false,
            'error' => 'Invalid response from payment processor'
        );
    }
    
    // Step 9: Return success response
    // The frontend will use client_secret with Stripe.js to confirm payment
    http_response_code(200);
    return array(
        'success' => true,
        'client_secret' => $clientSecret,
        'payment_intent_id' => $paymentIntentId,
        'status' => $status,
        'amount' => $paymentAmount,
        'currency' => strtoupper($currency),
        'message' => 'Payment intent created. Please complete payment.'
    );
}

// Main execution logic
try {
    // Check if this is a payment creation request (POST or GET with amount parameter)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
        // Handle payment creation request
        $response = createPayment();
        echo json_encode($response);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['amount'])) {
        // Also support GET requests for testing (not recommended for production)
        $_POST = $_GET;  // Copy GET params to POST for processing
        $response = createPayment();
        echo json_encode($response);
        
    } else {
        // No payment request - this might be a form page load
        // Return error or show form
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Payment amount not provided'
        ));
    }
    
} catch (Exception $e) {
    // Handle any unexpected exceptions
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'An unexpected error occurred: ' . $e->getMessage()
    ));
}

?>
