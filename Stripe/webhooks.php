<?php
/**
 * Stripe Webhook Handler
 * POST /webhooks.php
 * 
 * Receives and processes webhook events from Stripe.
 * Always verify signatures and respond with 200 OK immediately.
 */

require_once __DIR__ . '/config.php';

$secret = getenv('STRIPE_WEBHOOK_SECRET');
if (!$secret) {
    http_response_code(400);
    die('Webhook secret not configured');
}

function verifySignature($payload, $header, $secret) {
    if (!$header) return false;
    
    $parts = [];
    foreach (explode(',', $header) as $part) {
        [$key, $val] = explode('=', $part);
        $parts[$key] = $val;
    }
    
    if (!isset($parts['t'], $parts['v1'])) return false;
    
    $expected = hash_hmac('sha256', $parts['t'] . '.' . $payload, $secret);
    return hash_equals($expected, $parts['v1']);
}

try {
    $payload = file_get_contents('php://input');
    $header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    if (!verifySignature($payload, $header, $secret)) {
        http_response_code(403);
        die('Invalid signature');
    }
    
    $event = json_decode($payload, true);
    if (!$event) {
        http_response_code(400);
        die('Invalid JSON');
    }
    
    $type = $event['type'] ?? null;
    $data = $event['data']['object'] ?? [];
    
    error_log("Webhook: $type - " . ($data['id'] ?? 'unknown'));
    
    switch ($type) {
        case 'payment_intent.succeeded':
            // Payment succeeded - update order status, send confirmation, etc.
            // error_log("Payment succeeded: " . $data['id'] . " - " . ($data['amount']/100) . " " . $data['currency']);
            // TODO: Update database, send email, grant access
            break;
            
        case 'payment_intent.payment_failed':
            // Payment failed - notify customer
            // error_log("Payment failed: " . $data['id']);
            // TODO: Update order status, send failure notification
            break;
            
        case 'charge.refunded':
            // Refund processed - revoke access, send confirmation
            // error_log("Refunded: " . $data['id']);
            // TODO: Revoke access, update database
            break;
            
        case 'charge.dispute.created':
            // Customer initiated chargeback - investigate and respond
            // error_log("Dispute: " . $data['id']);
            // TODO: Notify support team
            break;
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    die('Error');
}

