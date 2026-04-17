<?php
/**
 * Stripe Webhook Handler
 * 
 * This file handles incoming webhook events from Stripe.
 * Webhooks are server-to-server notifications that occur regardless of user actions.
 * 
 * IMPORTANT: This is for PRODUCTION use with order fulfillment
 * 
 * WHY WEBHOOKS?
 * - Webhooks are more reliable than redirects
 * - Triggers even if customer doesn't return to your site
 * - Can't be spoofed (message is cryptographically signed)
 * - Automatically retried by Stripe if processing fails
 * 
 * SETUP:
 * 1. Get webhook signing secret from Stripe Dashboard
 * 2. Add to .env: STRIPE_WEBHOOK_SECRET=whsec_test_...
 * 3. Configure webhook endpoint in Stripe Dashboard:
 *    https://dashboard.stripe.com/webhooks
 * 
 * SECURITY:
 * - Always verify webhook signature
 * - Only process verified webhooks
 * - Respond with 200 OK immediately (don't do long operations)
 * - Handle idempotently (same event could be received multiple times)
 */

// Include configuration
require_once __DIR__ . '/config.php';

// Get webhook signing secret from environment
// This should be set in .env or environment variables
$webhookSecret = getenv('STRIPE_WEBHOOK_SECRET');

if (empty($webhookSecret)) {
    // Webhook secret not configured
    http_response_code(400);
    die('Webhook secret not configured');
}

/**
 * Verify Stripe Webhook Signature
 * 
 * Stripe sends a signature header that proves the message came from Stripe.
 * We verify this signature to ensure we're not processing spoofed events.
 * 
 * @param string $payload Raw request body
 * @param string $signatureHeader Value from HTTP_STRIPE_SIGNATURE header
 * @param string $secret Webhook signing secret
 * @return bool True if signature is valid
 */
function verifyWebhookSignature($payload, $signatureHeader, $secret) {
    // The signature header format: t=timestamp,v1=hash
    if (empty($signatureHeader)) {
        return false;
    }
    
    // Parse signature header
    $signature_parts = array();
    foreach (explode(',', $signatureHeader) as $part) {
        list($key, $value) = explode('=', $part);
        $signature_parts[$key] = $value;
    }
    
    if (!isset($signature_parts['t']) || !isset($signature_parts['v1'])) {
        return false;
    }
    
    $timestamp = $signature_parts['t'];
    $signature = $signature_parts['v1'];
    
    // Create signed content
    // Format: timestamp.payload
    $signed_content = $timestamp . '.' . $payload;
    
    // Compute expected signature
    // Use HMAC-SHA256 to sign with webhook secret
    $expected_signature = hash_hmac('sha256', $signed_content, $secret);
    
    // Compare signatures (use timing-safe comparison)
    return hash_equals($expected_signature, $signature);
}

/**
 * Handle payment_intent.succeeded event
 * 
 * This event occurs when a customer successfully completes payment.
 * Use this to:
 * - Mark order as paid
 * - Send confirmation email
 * - Trigger fulfillment
 * - Grant access to digital product
 * 
 * @param array $paymentIntent The Payment Intent object from Stripe
 */
function handlePaymentSucceeded($paymentIntent) {
    // Extract important information
    $paymentIntentId = $paymentIntent['id'];
    $amount = $paymentIntent['amount'];  // In cents
    $currency = $paymentIntent['currency'];
    $metadata = $paymentIntent['metadata'] ?? array();
    
    // Log the successful payment
    error_log("Payment succeeded: $paymentIntentId - " . ($amount / 100) . " $currency");
    
    // Extract order information from metadata
    $orderId = isset($metadata['order_id']) ? $metadata['order_id'] : null;
    $customerId = isset($metadata['customer_id']) ? $metadata['customer_id'] : null;
    
    // TODO: Implement your business logic here:
    // 1. Update order status to "paid" in database
    // 2. Send confirmation email to customer
    // 3. Trigger order fulfillment (if digital product)
    // 4. Grant access to premium features
    // 5. Update inventory (if physical product)
    
    // Example:
    // $db->query("UPDATE orders SET status='paid', payment_id='$paymentIntentId' WHERE id='$orderId'");
    // sendConfirmationEmail($customerId, $orderId, $amount);
    // grantPremiumAccess($customerId);
    
    return true;
}

/**
 * Handle payment_intent.payment_failed event
 * 
 * This event occurs when payment is declined or fails.
 * Use this to:
 * - Mark order as failed
 * - Send failure notification email
 * - Suggest alternative payment methods
 * 
 * @param array $paymentIntent The Payment Intent object from Stripe
 */
function handlePaymentFailed($paymentIntent) {
    $paymentIntentId = $paymentIntent['id'];
    $status = $paymentIntent['status'];
    $lastError = $paymentIntent['last_payment_error'] ?? array();
    
    // Log the failed payment
    error_log("Payment failed: $paymentIntentId - Status: $status");
    
    // Extract error details
    $errorCode = $lastError['code'] ?? 'unknown';
    $errorMessage = $lastError['message'] ?? 'Payment failed';
    
    // Extract order information
    $metadata = $paymentIntent['metadata'] ?? array();
    $orderId = isset($metadata['order_id']) ? $metadata['order_id'] : null;
    $customerId = isset($metadata['customer_id']) ? $metadata['customer_id'] : null;
    
    // TODO: Implement your business logic:
    // 1. Update order status to "failed" in database
    // 2. Send failure notification to customer
    // 3. Suggest retry or alternative payment method
    // 4. TODO: Maybe offer to save card for later?
    
    // Example:
    // $db->query("UPDATE orders SET status='failed', error='$errorCode' WHERE id='$orderId'");
    // sendFailureEmail($customerId, $errorMessage);
    
    return true;
}

/**
 * Handle charge.refunded event
 * 
 * This event occurs when a payment is refunded.
 * Use this to:
 * - Mark order as refunded
 * - Revoke access if applicable
 * - Send refund confirmation
 * 
 * @param array $charge The Charge object from Stripe
 */
function handleRefunded($charge) {
    $chargeId = $charge['id'];
    $amount = $charge['amount'];
    
    error_log("Charge refunded: $chargeId - Amount: " . ($amount / 100));
    
    // TODO: Implement refund handling:
    // 1. Find associated Payment Intent
    // 2. Revoke access if applicable
    // 3. Send refund confirmation email
    
    return true;
}

// Main execution
try {
    // Step 1: Get raw request body
    // IMPORTANT: Get raw body before PHP parses it
    // This is needed for signature verification
    $payload = file_get_contents('php://input');
    
    // Step 2: Get signature header
    $signatureHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    // Step 3: Verify webhook signature
    // This ensures the message is actually from Stripe
    if (!verifyWebhookSignature($payload, $signatureHeader, $webhookSecret)) {
        http_response_code(403);
        die('Webhook signature verification failed');
    }
    
    // Step 4: Decode JSON payload
    $event = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        die('Invalid JSON payload');
    }
    
    // Step 5: Extract event type and data
    $eventType = $event['type'] ?? null;
    $eventData = $event['data']['object'] ?? array();
    
    // Log the webhook event
    error_log("Webhook event received: $eventType");
    
    // Step 6: Handle different event types
    switch ($eventType) {
        // ===== PAYMENT INTENT EVENTS =====
        
        case 'payment_intent.succeeded':
            // Payment was completed successfully
            handlePaymentSucceeded($eventData);
            break;
            
        case 'payment_intent.payment_failed':
            // Payment was declined or failed
            handlePaymentFailed($eventData);
            break;
            
        case 'payment_intent.canceled':
            // Payment Intent was canceled
            error_log("Payment Intent canceled: " . $eventData['id']);
            // TODO: Handle cancellation (update order status, etc.)
            break;
            
        case 'payment_intent.amount_capturable_updated':
            // Amount that can be captured changed
            error_log("Payment Intent amount capturable changed: " . $eventData['id']);
            break;
        
        // ===== CHARGE EVENTS =====
        
        case 'charge.refunded':
            // A charge was refunded
            handleRefunded($eventData);
            break;
            
        case 'charge.dispute.created':
            // A customer initiated a dispute (chargeback)
            error_log("Charge dispute created: " . $eventData['id']);
            // TODO: Handle dispute (notify customer, prepare response)
            break;
        
        // ===== CUSTOMER EVENTS =====
        
        case 'customer.subscription.updated':
            // Subscription was updated (if using subscriptions)
            error_log("Subscription updated: " . $eventData['id']);
            break;
        
        // ===== OTHER EVENTS =====
        
        default:
            // Unhandled event type
            // Don't fail - Stripe expects 200 OK for all events
            error_log("Unhandled webhook event: $eventType");
            break;
    }
    
    // Step 7: Return 200 OK to acknowledge receipt
    // Stripe expects 200 OK within 3 seconds
    // Do NOT do long operations here - respond immediately
    // Queue long tasks (emails, database queries) for background processing
    http_response_code(200);
    echo json_encode(array('status' => 'received'));
    
} catch (Exception $e) {
    // Handle any exceptions
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    die('Internal server error');
}

?>
