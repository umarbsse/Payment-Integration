<?php
/**
 * Payment Cancellation Page
 * GET /cancel.php
 * 
 * Handles payment cancellations, failures, and timeouts.
 * Use webhooks for authoritative payment status in production.
 */

require_once __DIR__ . '/config.php';

$piId = $_GET['payment_intent'] ?? null;
$errorCode = $_GET['error_code'] ?? null;
$error = $_GET['error_message'] ?? 'Your payment was not completed.';

$errorMap = [
    'card_declined' => 'Your card was declined. Try another payment method.',
    'lost_card' => 'Card reported lost. Use another card.',
    'stolen_card' => 'Card reported stolen. Use another card.',
    'expired_card' => 'Card expired. Use a different card.',
    'incorrect_cvc' => 'Security code (CVV) is incorrect.',
    'processing_error' => 'Processing error occurred. Try again.',
    'rate_limit_exceeded' => 'Too many requests. Wait and try again.',
    'authentication_required' => '3D Secure verification required.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Canceled - <?php echo APP_NAME; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .icon {
            font-size: 60px;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        h1 { color: #333; font-size: 28px; margin-bottom: 8px; }
        .subtitle { color: #999; font-size: 14px; }
        .alert {
            background: #fadbd8;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            color: #c0392b;
        }
        .details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #666;
        }
        .label { font-weight: 600; }
        .value {
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
        }
        .reasons li {
            color: #666;
            font-size: 14px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .reasons li:before { content: '• '; color: #e74c3c; margin-right: 8px; }
        .reasons li:last-child { border-bottom: none; }
        .msg { color: #666; line-height: 1.8; margin: 15px 0; }
        .btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 25px 0;
        }
        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-secondary { background: #ecf0f1; color: #333; }
        .btn-secondary:hover { background: #d5dbdb; }
        .support {
            background: #e8f4f8;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
            font-size: 14px;
            color: #2c3e50;
        }
        .support strong { display: block; margin-bottom: 8px; }
        .support a { color: #667eea; text-decoration: none; font-weight: 600; }
        .support a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">⚠</div>
            <h1>Payment Canceled</h1>
            <p class="subtitle">Your transaction was not completed</p>
        </div>
        
        <div class="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
        
        <?php if ($piId || $errorCode): ?>
            <div class="details">
                <?php if ($piId): ?>
                    <div class="row">
                        <span class="label">Transaction ID:</span>
                        <span class="value"><?php echo htmlspecialchars($piId); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($errorCode): ?>
                    <div class="row">
                        <span class="label">Error Code:</span>
                        <span class="value"><?php echo htmlspecialchars($errorCode); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="msg">
            Your payment was not processed. Common reasons:
        </div>
        
        <div class="reasons">
            <h3>Why This Happened</h3>
            <ul>
                <li>You canceled the payment process</li>
                <li>Your card was declined by your bank</li>
                <li>Insufficient funds in your account</li>
                <li>Card details are incorrect or expired</li>
                <li>3D Secure verification failed</li>
                <li>Payment session expired</li>
            </ul>
        </div>
        
        <?php if ($errorCode && isset($errorMap[$errorCode])): ?>
            <div class="msg"><strong>Details:</strong> <?php echo htmlspecialchars($errorMap[$errorCode]); ?></div>
        <?php endif; ?>
        
        <div class="btns">
            <a href="<?php echo APP_URL; ?>/create_payment.php" class="btn btn-primary">Try Payment Again</a>
            <a href="<?php echo APP_URL; ?>" class="btn btn-secondary">Return Home</a>
        </div>
        
        <div class="support">
            <strong>Still Having Issues?</strong>
            Contact our support team:<br>
            <a href="mailto:support@example.com">support@example.com</a> |
            <a href="tel:1-800-SUPPORT">1-800-SUPPORT</a>
        </div>
    </div>
</body>
</html>

