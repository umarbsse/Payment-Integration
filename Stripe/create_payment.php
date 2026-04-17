<?php
/**
 * Create Payment Intent API Endpoint
 * POST /create_payment.php
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

header('Content-Type: application/json');

if (!isStripeConfigured()) {
    http_response_code(503);
    die(json_encode(['success' => false, 'error' => 'Stripe not configured']));
}

// Extract request data
$amount = (int) ($_POST['amount'] ?? $_GET['amount'] ?? 0);
$currency = strtolower($_POST['currency'] ?? 'usd');
$description = $_POST['description'] ?? 'Payment';
$orderId = $_POST['order_id'] ?? null;
$email = $_POST['email'] ?? null;

try {
    // Validate amount
    if ($amount < MIN_PAYMENT_AMOUNT || $amount > MAX_PAYMENT_AMOUNT) {
        throw new Exception("Invalid amount: \$" . number_format($amount/100, 2));
    }
    
    // Validate currency
    if (!in_array($currency, ['usd', 'eur', 'gbp', 'aud', 'cad', 'nzd', 'jpy', 'cny'])) {
        throw new Exception('Invalid currency');
    }
    
    // Create Payment Intent
    $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
    
    $metadata = ['created_at' => date('c'), 'app' => APP_NAME];
    if ($orderId) $metadata['order_id'] = $orderId;
    if ($email) $metadata['email'] = $email;
    
    $response = $client->createPaymentIntent($amount, $currency, $description, $metadata);
    
    if (!$response['success']) {
        throw new Exception($response['error']['message'] ?? 'Payment creation failed');
    }
    
    $pi = $response['data'];
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'payment_intent_id' => $pi['id'],
        'client_secret' => $pi['client_secret'],
        'status' => $pi['status'],
        'amount' => $pi['amount'],
        'currency' => strtoupper($pi['currency']),
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

