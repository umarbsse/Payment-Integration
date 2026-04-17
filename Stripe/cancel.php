<?php
/**
 * Payment Cancellation/Failure Page
 * 
 * This page handles cases where:
 * - Customer cancels payment before completion
 * - Payment fails or is declined
 * - Payment times out
 * - 3DS verification fails
 * 
 * IMPORTANT NOTES:
 * - This page is informational for the customer
 * - Always use Stripe webhooks for authoritative payment status
 * - Webhooks are triggered even if customer doesn't return to this page
 * - See README for webhook implementation guidance
 * 
 * FLOW:
 * 1. Customer cancels Stripe checkout or confirmation fails
 * 2. Browser redirects to this page with error details
 * 3. Show user-friendly error message
 * 4. Provide option to retry payment
 * 5. Suggest contacting support if issues persist
 */

// Include configuration
require_once __DIR__ . '/config.php';

// Get error details from request (if provided)
$paymentIntentId = isset($_GET['payment_intent']) ? $_GET['payment_intent'] : null;
$errorCode = isset($_GET['error_code']) ? $_GET['error_code'] : null;
$errorMessage = isset($_GET['error_message']) ? $_GET['error_message'] : null;

// Default cancellation reason
if (empty($errorMessage)) {
    $errorMessage = 'Your payment was not completed. Please try again or contact support.';
}

/**
 * Map error codes to user-friendly messages
 * 
 * @param string $code Stripe error code
 * @return string User-friendly error message
 */
function getErrorExplanation($code) {
    $explanations = array(
        'card_declined' => 'Your card was declined. Please check your card details and try again, or use a different payment method.',
        'lost_card' => 'Your card was reported lost. Please use another card or contact your bank.',
        'stolen_card' => 'Your card was reported stolen. Please use another card or contact your bank.',
        'expired_card' => 'Your card has expired. Please use a different card or update your card expiry date.',
        'incorrect_cvc' => 'The security code (CVV) is incorrect. Please check and try again.',
        'processing_error' => 'A processing error occurred. Please try again in a few moments.',
        'rate_limit_exceeded' => 'Too many requests. Please wait a few moments and try again.',
        'authentication_required' => '3D Secure verification is required. Please complete the authentication process.',
        'card_error' => 'A card error occurred. Please verify your information and try again.',
        'canceled' => 'Your payment was canceled. You can try again anytime.'
    );
    
    return isset($explanations[$code]) ? $explanations[$code] : 'A payment error occurred.';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Canceled - <?php echo APP_NAME; ?></title>
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
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .status-icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #999;
            font-size: 14px;
        }
        
        .alert {
            background: #fadbd8;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            color: #c0392b;
        }
        
        .content {
            margin-bottom: 30px;
        }
        
        .content p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .details-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #666;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
        }
        
        .detail-value {
            color: #999;
            font-family: monospace;
            word-break: break-all;
        }
        
        .reasons {
            background: #f5f7fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .reasons h3 {
            color: #333;
            font-size: 14px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .reasons ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .reasons li {
            color: #666;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
        }
        
        .reasons li:before {
            content: '•';
            color: #e74c3c;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-right: 8px;
        }
        
        .reasons li:last-child {
            border-bottom: none;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 28px;
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
        
        .support-section {
            background: #e8f4f8;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .support-section strong {
            display: block;
            margin-bottom: 8px;
        }
        
        .support-section a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .support-section a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="status-icon">⚠</div>
            <h1>Payment Canceled</h1>
            <p class="subtitle">Your transaction was not completed</p>
        </div>
        
        <!-- Main Alert -->
        <div class="alert">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        
        <!-- Transaction Details (if available) -->
        <?php if (!empty($paymentIntentId) || !empty($errorCode)): ?>
            <div class="details-section">
                <?php if (!empty($paymentIntentId)): ?>
                    <div class="detail-row">
                        <span class="detail-label">Transaction ID:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($paymentIntentId); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errorCode)): ?>
                    <div class="detail-row">
                        <span class="detail-label">Error Code:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($errorCode); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- Content -->
        <div class="content">
            <p>
                Your payment was not processed. This could happen for several reasons:
            </p>
            
            <div class="reasons">
                <h3>Common Reasons</h3>
                <ul>
                    <li>You canceled the payment process</li>
                    <li>Your card was declined by your bank</li>
                    <li>Insufficient funds in your account</li>
                    <li>Card details are incorrect or expired</li>
                    <li>3D Secure verification was not completed</li>
                    <li>Payment session expired (after 10 minutes)</li>
                    <li>Browser was closed during payment</li>
                </ul>
            </div>
            
            <!-- Error-specific explanation -->
            <?php if (!empty($errorCode)): ?>
                <p>
                    <strong>Why this happened:</strong><br>
                    <?php echo htmlspecialchars(getErrorExplanation($errorCode)); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="button-group">
            <a href="<?php echo APP_URL; ?>/create_payment.php" class="btn btn-primary">Try Payment Again</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">Return Home</a>
        </div>
        
        <!-- Support Information -->
        <div class="support-section">
            <strong>Still Having Issues?</strong>
            Contact our support team:<br>
            <a href="mailto:support@example.com">support@example.com</a> | 
            <a href="tel:1-800-SUPPORT">1-800-SUPPORT</a>
        </div>
    </div>
</body>
</html>
