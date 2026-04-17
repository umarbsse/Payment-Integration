<?php
/**
 * Payment Success Page
 * 
 * This page is displayed after a customer successfully completes their payment.
 * It retrieves the payment status from Stripe to confirm the payment succeeded.
 * 
 * FLOW:
 * 1. User is redirected here after Stripe.js confirms the payment
 * 2. Payment Intent ID is provided in URL parameter
 * 3. Server queries Stripe to get final payment status
 * 4. Display confirmation message with transaction details
 * 
 * SECURITY:
 * - NEVER assume payment succeeded just because user reached this page
 * - ALWAYS query Stripe to verify payment status
 * - Check payment_intent_id is valid format before querying API
 * 
 * IMPORTANT:
 * - For production order fulfillment, use Stripe webhooks
 * - Webhooks are more reliable than redirect-based confirmations
 * - See webhook documentation for handling payment.intent.succeeded
 */

// Include configuration and Stripe client
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

// Initialize variables
$paymentSuccess = false;
$paymentDetails = array();
$errorMessage = null;
$paymentIntentId = null;

/**
 * Validate that the payment_intent_id has valid format
 * 
 * Stripe Payment Intent IDs start with "pi_"
 * 
 * @param string $id The ID to validate
 * @return bool True if valid format
 */
function isValidPaymentIntentId($id) {
    // Payment Intent IDs should start with "pi_" and be alphanumeric
    return !empty($id) && strpos($id, 'pi_') === 0 && strlen($id) > 3;
}

/**
 * Confirm payment by checking Stripe API
 * 
 * This is the critical security check - we verify with Stripe that payment succeeded
 * before showing confirmation to user or processing order.
 * 
 * @return bool True if payment is confirmed as succeeded
 */
function confirmPaymentStatus() {
    global $paymentIntentId, $paymentDetails, $errorMessage;
    
    // Step 1: Get Payment Intent ID from request
    $paymentIntentId = isset($_GET['payment_intent_id']) ? $_GET['payment_intent_id'] : null;
    
    if (!isValidPaymentIntentId($paymentIntentId)) {
        $errorMessage = 'Invalid or missing payment confirmation ID';
        return false;
    }
    
    // Step 2: Verify Stripe is configured
    if (!isStripeConfigured()) {
        $errorMessage = 'Payment system not configured';
        return false;
    }
    
    // Step 3: Create Stripe API client
    try {
        $stripeClient = new StripeClient(
            STRIPE_SECRET_KEY,
            STRIPE_API_BASE_URL,
            DEBUG_MODE,
            LOG_FILE_PATH
        );
    } catch (Exception $e) {
        $errorMessage = 'Failed to connect to payment processor';
        return false;
    }
    
    // Step 4: Retrieve Payment Intent details from Stripe
    // This is the AUTHORITATIVE source of truth for payment status
    $response = $stripeClient->getPaymentIntent($paymentIntentId);
    
    if (!$response['success']) {
        // API call failed
        if (isset($response['error']['message'])) {
            $errorMessage = 'Could not verify payment: ' . $response['error']['message'];
        } else {
            $errorMessage = 'Could not verify payment status from processor';
        }
        return false;
    }
    
    // Step 5: Extract payment details
    $paymentDetails = $response['data'];
    
    // Step 6: Check payment status
    // Possible statuses:
    // - succeeded: Payment is complete and successful
    // - processing: Payment is being processed
    // - requires_action: Customer needs to complete additional steps
    // - requires_payment_method: Payment method required
    // - canceled: Payment was canceled
    
    $status = isset($paymentDetails['status']) ? $paymentDetails['status'] : null;
    
    if ($status === 'succeeded') {
        // Payment succeeded!
        return true;
    } elseif ($status === 'processing') {
        $errorMessage = 'Your payment is being processed. Please check your email for confirmation.';
        return false;
    } elseif ($status === 'requires_action') {
        $errorMessage = 'Your payment requires additional verification. Please complete the verification process.';
        return false;
    } else {
        $errorMessage = 'Payment status: ' . ucfirst($status);
        return false;
    }
}

// Attempt to confirm payment status
$paymentSuccess = confirmPaymentStatus();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $paymentSuccess ? 'Payment Successful' : 'Payment Verification'; ?> - <?php echo APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        
        .status-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        .status-icon.success {
            color: #27ae60;
        }
        
        .status-icon.error {
            color: #e74c3c;
        }
        
        h1 {
            color: #333;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
            font-size: 16px;
        }
        
        .payment-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #666;
            font-weight: 500;
        }
        
        .detail-value {
            color: #333;
            font-weight: 600;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #ecf0f1;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d5dbdb;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: #fadbd8;
            color: #c0392b;
            border-left: 4px solid #c0392b;
        }
        
        .note {
            font-size: 13px;
            color: #999;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($paymentSuccess): ?>
            <!-- SUCCESS STATE -->
            <div class="status-icon success">✓</div>
            <h1>Payment Successful!</h1>
            <p class="message">Your payment has been securely processed and confirmed.</p>
            
            <?php if (!empty($paymentDetails)): ?>
                <div class="payment-details">
                    <?php if (isset($paymentDetails['id'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Transaction ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($paymentDetails['id']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($paymentDetails['amount']) && isset($paymentDetails['currency'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Amount:</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars(strtoupper($paymentDetails['currency'])); ?>
                                <?php echo number_format($paymentDetails['amount'] / 100, 2); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($paymentDetails['status'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Status:</span>
                            <span class="detail-value"><?php echo htmlspecialchars(ucfirst($paymentDetails['status'])); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($paymentDetails['created'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Date/Time:</span>
                            <span class="detail-value"><?php echo date('F d, Y g:i A', $paymentDetails['created']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="button-group">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Return to Home</a>
                <a href="javascript:window.print()" class="btn btn-secondary">Print Receipt</a>
            </div>
            
            <p class="note">A confirmation email has been sent to your registered email address.</p>
            
        <?php else: ?>
            <!-- ERROR STATE -->
            <div class="status-icon error">✕</div>
            <h1>Payment Verification</h1>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($paymentIntentId)): ?>
                <p class="message">
                    Transaction ID: <strong><?php echo htmlspecialchars($paymentIntentId); ?></strong>
                </p>
                
                <?php if (!empty($paymentDetails) && isset($paymentDetails['status'])): ?>
                    <p class="message">
                        Current Status: <strong><?php echo htmlspecialchars(ucfirst($paymentDetails['status'])); ?></strong>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
            
            <p class="message">
                If your payment was deducted from your account, it may take a few moments to process.
                <br><br>
                Please check your email or contact our support team for assistance.
            </p>
            
            <div class="button-group">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Back to Home</a>
                <a href="<?php echo APP_URL; ?>/create_payment.php" class="btn btn-secondary">Try Again</a>
            </div>
            
            <p class="note">Support: contact@example.com | Phone: 1-800-SUPPORT</p>
            
        <?php endif; ?>
    </div>
</body>
</html>
