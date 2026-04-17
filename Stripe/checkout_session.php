<?php
/**
 * Stripe Checkout Session Implementation
 * 
 * This is an alternative to Payment Intents for a simpler payment flow.
 * Checkout Sessions provide a pre-built, hosted payment page hosted by Stripe.
 * 
 * DIFFERENCES FROM PAYMENT INTENTS:
 * 
 * PAYMENT INTENTS (create_payment.php):
 * - Full control over payment flow
 * - Build custom checkout form
 * - More complex integration
 * - Use with Stripe.js for client-side confirmation
 * - Better for: Complex flows, custom UI, multiple payment methods
 * 
 * CHECKOUT SESSIONS (this file):
 * - Hosted payment page by Stripe
 * - Simple redirect flow
 * - Minimal frontend code needed
 * - Automatically handles 3D Secure
 * - Better for: Simple storefronts, quick integration, less custom UI
 * 
 * WHICH TO USE?
 * - Use Payment Intents if you need custom checkout UI and full control
 * - Use Checkout Sessions if you want simplicity and Stripe's hosted page
 * - You CAN use both - let user choose payment method
 * 
 * FLOW:
 * 1. User clicks "Pay with Stripe Checkout"
 * 2. create_checkout_session.php creates session
 * 3. Redirects to session.url (Stripe hosted page)
 * 4. Stripe handles payment collection
 * 5. After payment, redirects to success.php or cancel.php
 */

// Include configuration and Stripe client
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

// Set response header for JSON responses
header('Content-Type: application/json');

/**
 * Create a Stripe Checkout Session
 * 
 * This function creates a session that redirects customer to Stripe's
 * hosted payment page. Much simpler than building custom checkout.
 * 
 * @return array Response with 'success' boolean and session URL or error
 */
function createCheckoutSession() {
    // Get payment details from request
    $paymentAmount = isset($_POST['amount']) ? $_POST['amount'] : null;
    $currency = isset($_POST['currency']) ? $_POST['currency'] : DEFAULT_CURRENCY;
    $description = isset($_POST['description']) ? $_POST['description'] : APP_NAME . ' Payment';
    $orderId = isset($_POST['order_id']) ? $_POST['order_id'] : null;
    $customerEmail = isset($_POST['email']) ? $_POST['email'] : '';
    
    // Step 1: Validate amount (CRITICAL - never trust frontend)
    if (empty($paymentAmount)) {
        http_response_code(400);
        return array('success' => false, 'error' => 'Amount is required');
    }
    
    $amount = intval($paymentAmount);
    if ($amount <= 0 || $amount < MIN_PAYMENT_AMOUNT || $amount > MAX_PAYMENT_AMOUNT) {
        http_response_code(400);
        return array(
            'success' => false,
            'error' => 'Invalid amount. Must be between $' . 
                      (MIN_PAYMENT_AMOUNT / 100) . ' and $' . (MAX_PAYMENT_AMOUNT / 100)
        );
    }
    
    // Step 2: Validate currency
    $supportedCurrencies = array('usd', 'eur', 'gbp', 'aud', 'cad', 'nzd', 'chf');
    if (!in_array(strtolower($currency), $supportedCurrencies)) {
        http_response_code(400);
        return array('success' => false, 'error' => 'Invalid currency');
    }
    
    // Step 3: Verify Stripe configuration
    if (!isStripeConfigured()) {
        http_response_code(500);
        return array('success' => false, 'error' => 'Payment system not configured');
    }
    
    // Step 4: Create Stripe client
    try {
        $stripeClient = new StripeClient(
            STRIPE_SECRET_KEY,
            STRIPE_API_BASE_URL,
            DEBUG_MODE,
            LOG_FILE_PATH
        );
    } catch (Exception $e) {
        http_response_code(500);
        return array('success' => false, 'error' => 'Failed to initialize payment processor');
    }
    
    // Step 5: Build success and cancel URLs
    $successUrl = APP_URL . '/success.php?session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl = APP_URL . '/cancel.php?reason=checkout_canceled';
    
    // Step 6: Prepare metadata
    $metadata = array(
        'created_at' => date('Y-m-d H:i:s'),
        'app' => APP_NAME
    );
    
    if (!empty($orderId)) {
        $metadata['order_id'] = $orderId;
    }
    
    // Step 7: Create the checkout session
    // Stripe redirects to success URL on payment success
    $response = $stripeClient->createCheckoutSession(
        $amount,
        $currency,
        $description,
        $successUrl,
        $cancelUrl,
        $metadata
    );
    
    // Step 8: Handle response
    if (!$response['success']) {
        http_response_code(400);
        return array(
            'success' => false,
            'error' => isset($response['error']['message']) 
                      ? $response['error']['message'] 
                      : 'Failed to create payment session'
        );
    }
    
    // Step 9: Extract session URL
    $sessionData = $response['data'];
    $sessionUrl = isset($sessionData['url']) ? $sessionData['url'] : null;
    $sessionId = isset($sessionData['id']) ? $sessionData['id'] : null;
    
    if (empty($sessionUrl) || empty($sessionId)) {
        http_response_code(500);
        return array('success' => false, 'error' => 'Invalid response from payment processor');
    }
    
    // Step 10: Return success with checkout URL
    http_response_code(200);
    return array(
        'success' => true,
        'url' => $sessionUrl,
        'session_id' => $sessionId,
        'message' => 'Redirecting to payment page...'
    );
}

// Main execution
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
        $response = createCheckoutSession();
        echo json_encode($response);
    } else {
        http_response_code(400);
        echo json_encode(array(
            'success' => false,
            'error' => 'Invalid request. Use POST with amount parameter.'
        ));
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'error' => 'An error occurred: ' . $e->getMessage()
    ));
}

?>
