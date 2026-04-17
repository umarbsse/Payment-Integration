<?php
require_once 'config.php';

session_start();

$orderToken = $_GET['token'] ?? null;

if ($orderToken) {
    error_log('Payment cancelled for order: ' . $orderToken);
}

unset($_SESSION['paypal_order_id']);

echo '<h1>Payment Cancelled</h1>';
echo '<p>You have cancelled the payment process. No charges have been made to your account.</p>';

echo '<a href="' . BASE_URL . '/create_order.php">Try Again</a>';
echo '<br><br>';
echo '<a href="' . BASE_URL . '">Return to Home</a>';
?>