<?php
require_once 'config.php';
require_once 'paypal_client.php';

session_start();

$paymentAmount = 10.00;
$currency = DEFAULT_CURRENCY;

try {
    $paypal = new PayPalAPI();

    $order = $paypal->createOrder($paymentAmount, $currency);

    $_SESSION['paypal_order_id'] = $order['order_id'];

    error_log('Order created: ' . $order['order_id']);

    header('Location: ' . $order['approval_url']);
    exit;

} catch (Exception $e) {
    error_log('Order creation failed: ' . $e->getMessage());

    echo '<h1>Payment Error</h1>';
    echo '<p>Sorry, we could not process your payment request. Please try again.</p>';
    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<a href="javascript:history.back()">Go Back</a>';
    exit;
}
?>