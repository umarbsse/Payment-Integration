<?php
/**
 * Payment Success Confirmation Page
 * GET /success.php?payment_intent_id=pi_xxxxx
 * 
 * Verifies payment with Stripe and displays confirmation.
 * For production, use webhooks for authoritative payment status.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

$success = false;
$details = [];
$error = null;
$piId = $_GET['payment_intent_id'] ?? null;

if ($piId && strpos($piId, 'pi_') === 0 && isStripeConfigured()) {
    try {
        $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
        $response = $client->getPaymentIntent($piId);
        
        if ($response['success'] && $response['data']['status'] === 'succeeded') {
            $success = true;
            $details = $response['data'];
        } else {
            $error = $response['data']['status'] ?? 'Unconfirmed';
        }
    } catch (Exception $e) {
        $error = 'Could not verify payment';
    }
} else {
    $error = 'Invalid payment confirmation';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $success ? 'Success' : 'Verification'; ?> - <?php echo APP_NAME; ?></title>
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
            text-align: center;
        }
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        .success .icon { color: #27ae60; }
        .error .icon { color: #e74c3c; }
        h1 { color: #333; margin-bottom: 15px; font-size: 28px; }
        .msg { color: #666; margin-bottom: 30px; line-height: 1.6; }
        .details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: left;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        .row:last-child { border-bottom: none; }
        .label { color: #666; font-weight: 500; }
        .value { color: #333; font-weight: 600; word-break: break-all; }
        .alert {
            background: #fadbd8;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btns {
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
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; transform: translateY(-2px); }
        .btn-secondary { background: #ecf0f1; color: #333; }
        .btn-secondary:hover { background: #d5dbdb; }
        .note {
            font-size: 13px;
            color: #999;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container <?php echo $success ? 'success' : 'error'; ?>">
        <?php if ($success): ?>
            <div class="icon">✓</div>
            <h1>Payment Successful!</h1>
            <p class="msg">Your payment has been securely processed and confirmed.</p>
            
            <div class="details">
                <div class="row">
                    <span class="label">Transaction ID:</span>
                    <span class="value"><?php echo htmlspecialchars($details['id']); ?></span>
                </div>
                <?php if (isset($details['amount'], $details['currency'])): ?>
                    <div class="row">
                        <span class="label">Amount:</span>
                        <span class="value"><?php echo strtoupper($details['currency']); ?> <?php echo number_format($details['amount']/100, 2); ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($details['created'])): ?>
                    <div class="row">
                        <span class="label">Date/Time:</span>
                        <span class="value"><?php echo date('F d, Y g:i A', $details['created']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="btns">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Return Home</a>
                <a href="javascript:window.print()" class="btn btn-secondary">Print Receipt</a>
            </div>
            <p class="note">A confirmation email has been sent to your address.</p>
        <?php else: ?>
            <div class="icon">✕</div>
            <h1>Payment Verification</h1>
            <div class="alert"><?php echo htmlspecialchars($error ?? 'Could not verify payment'); ?></div>
            <p class="msg">If your payment was processed, please check your email or contact support.</p>
            <div class="btns">
                <a href="<?php echo APP_URL; ?>" class="btn btn-primary">Return Home</a>
                <a href="<?php echo APP_URL; ?>/create_payment.php" class="btn btn-secondary">Try Again</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

