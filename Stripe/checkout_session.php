<?php
/**
 * Stripe Checkout Session API
 * POST /checkout_session.php
 * 
 * Creates a Checkout Session for Stripe-hosted payment page.
 * Alternative to Payment Intents for simpler integration.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

header('Content-Type: application/json');

if (!isStripeConfigured()) {
    http_response_code(503);
    die(json_encode(['success' => false, 'error' => 'Stripe not configured']));
}

try {
    $amount = (int) ($_POST['amount'] ?? 0);
    $currency = strtolower($_POST['currency'] ?? 'usd');
    $description = $_POST['description'] ?? APP_NAME . ' Payment';
    $orderId = $_POST['order_id'] ?? null;
    
    // Validate inputs
    if ($amount < MIN_PAYMENT_AMOUNT || $amount > MAX_PAYMENT_AMOUNT) {
        throw new Exception('Invalid amount');
    }
    
    if (!in_array($currency, ['usd', 'eur', 'gbp', 'aud', 'cad', 'nzd'])) {
        throw new Exception('Invalid currency');
    }
    
    $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
    
    // Create session
    $metadata = ['created_at' => date('c'), 'app' => APP_NAME];
    if ($orderId) $metadata['order_id'] = $orderId;
    
    $response = $client->createCheckoutSession(
        $amount,
        $currency,
        $description,
        APP_URL . '/success.php?session_id={CHECKOUT_SESSION_ID}',
        APP_URL . '/cancel.php',
        $metadata
    );
    
    if (!$response['success']) {
        throw new Exception($response['error']['message'] ?? 'Session creation failed');
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'url' => $response['data']['url'],
        'session_id' => $response['data']['id'],
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

