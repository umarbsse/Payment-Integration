<?php
require_once 'config.php';
require_once 'paypal_client.php';

session_start();

$orderToken = $_GET['token'] ?? null;

if (!$orderToken) {
    echo '<h1>Payment Error</h1>';
    echo '<p>Invalid payment request. No order token provided.</p>';
    echo '<a href="' . BASE_URL . '/create_order.php">Try Again</a>';
    exit;
}

try {
    $paypal = new PayPalAPI();

    $capture = $paypal->captureOrder($orderToken);

    error_log('Payment captured for order: ' . $orderToken);

    unset($_SESSION['paypal_order_id']);

    echo '<h1>Payment Successful!</h1>';
    echo '<p>Thank you for your payment. Your transaction has been completed successfully.</p>';

    if (isset($capture['purchase_units'][0]['payments']['captures'][0])) {
        $captureDetails = $capture['purchase_units'][0]['payments']['captures'][0];
        echo '<h2>Transaction Details:</h2>';
        echo '<ul>';
        echo '<li><strong>Order ID:</strong> ' . htmlspecialchars($orderToken) . '</li>';
        echo '<li><strong>Capture ID:</strong> ' . htmlspecialchars($captureDetails['id']) . '</li>';
        echo '<li><strong>Amount:</strong> ' . htmlspecialchars($captureDetails['amount']['value']) . ' ' . htmlspecialchars($captureDetails['amount']['currency_code']) . '</li>';
        echo '<li><strong>Status:</strong> ' . htmlspecialchars($captureDetails['status']) . '</li>';
        echo '</ul>';
    }

    echo '<a href="' . BASE_URL . '">Return to Home</a>';

} catch (Exception $e) {
    error_log('Payment capture failed for order ' . $orderToken . ': ' . $e->getMessage());

    echo '<h1>Payment Processing Error</h1>';
    echo '<p>Sorry, we could not process your payment. Please contact support if the charge appears on your account.</p>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';

    echo '<a href="' . BASE_URL . '/create_order.php">Try Again</a>';
    exit;
}
?>