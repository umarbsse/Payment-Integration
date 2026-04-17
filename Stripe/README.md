# Stripe Payment Integration (PHP)

Production-ready Stripe payment integration using PHP and the Stripe REST API. This implementation uses cURL for API communication and does not depend on the Stripe SDK.

## Features

✓ **Stripe Payment Intents API** - Full control over payment flow  
✓ **Stripe Checkout Sessions** - Simplified hosted payment page  
✓ **cURL-based implementation** - No external dependencies  
✓ **Clean, well-documented code** - Detailed inline comments  
✓ **Security best practices** - Secret key protection, server-side validation  
✓ **Production-ready** - Error handling, logging, modular design  
✓ **No framework requirement** - Plain PHP (XAMPP/WAMP compatible)  

## Project Structure

```
stripe/
├── config.php                 # Stripe API credentials and app settings
├── stripe_client.php          # Core Stripe API client (cURL-based)
├── create_payment.php         # Create Payment Intent endpoint
├── checkout_session.php       # Create Checkout Session endpoint
├── success.php                # Payment success confirmation page
├── cancel.php                 # Payment cancellation/error page
├── example_form.php           # Example HTML form for testing
├── example_checkout_form.php  # Example Checkout Sessions form
├── webhooks.php               # Webhook handler (optional)
└── README.md                  # This file
```

## Quick Start

### 1. Get Stripe API Keys

1. Create a [Stripe account](https://dashboard.stripe.com/register)
2. Go to [API Keys](https://dashboard.stripe.com/apikeys)
3. Copy your Secret Key (starts with `sk_test_` or `sk_live_`)
4. Copy your Publishable Key (starts with `pk_test_` or `pk_live_`)

### 2. Configure the Application

Edit `config.php` and update your API keys:

```php
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY');
define('APP_URL', 'http://localhost/stripe');
```

### 3. Set Up Logs Directory

The application logs API requests for debugging. Create a logs directory:

```bash
mkdir logs
chmod 755 logs
```

### 4. Test the Integration

#### Option A: Using Payment Intents (Full Control)

```html
<!-- example_form.php -->
<form id="payment-form">
    <input type="number" name="amount" placeholder="Amount in dollars" required>
    <input type="email" name="email" placeholder="Email" required>
    <button type="submit">Pay with Card</button>
</form>

<script src="https://js.stripe.com/v3/"></script>
<script>
// See example_form.php for complete implementation
</script>
```

#### Option B: Using Checkout Sessions (Simplified)

```html
<!-- example_checkout_form.php -->
<form action="checkout_session.php" method="POST">
    <input type="hidden" name="amount" value="5000">
    <input type="hidden" name="currency" value="usd">
    <input type="hidden" name="description" value="Order #12345">
    <button type="submit">Pay with Stripe Checkout</button>
</form>
```

## Core Components

### 1. Config File (`config.php`)

Centralized configuration for:
- Stripe API credentials
- Application settings
- Payment limits
- Logging options

**Security Note**: For production, use environment variables:

```php
// .env or environment setup
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY'));
```

### 2. Stripe Client (`stripe_client.php`)

Main API client class using cURL. Features:

| Method | Purpose |
|--------|---------|
| `createPaymentIntent()` | Create a Payment Intent |
| `confirmPaymentIntent()` | Confirm a Payment Intent with payment method |
| `getPaymentIntent()` | Retrieve Payment Intent status |
| `createCheckoutSession()` | Create a Checkout Session |
| `isPaymentSucceeded()` | Check if Payment Intent succeeded |

**Example Usage:**

```php
$client = new StripeClient(STRIPE_SECRET_KEY);

// Create payment intent
$response = $client->createPaymentIntent(
    5000,           // Amount in cents ($50.00)
    'usd',          // Currency
    'Order #123',   // Description
    array(          // Metadata
        'order_id' => '12345',
        'customer_id' => 'cust_456'
    )
);

if ($response['success']) {
    $clientSecret = $response['data']['client_secret'];
    $paymentIntentId = $response['data']['id'];
} else {
    $error = $response['error']['message'];
}
```

### 3. Create Payment Endpoint (`create_payment.php`)

API endpoint that creates Payment Intents.

**Request:**
```
POST /create_payment.php
amount=5000
currency=usd
description=Order%20%23123
```

**Response:**
```json
{
    "success": true,
    "client_secret": "pi_1234_secret_5678",
    "payment_intent_id": "pi_1234567890",
    "status": "requires_payment_method",
    "amount": 5000,
    "currency": "USD"
}
```

### 4. Success Page (`success.php`)

Displays payment confirmation after successful payment.

**Features:**
- Verifies payment with Stripe API
- Shows transaction details
- Never trusts client-side confirmation
- Print receipt option

### 5. Cancel Page (`cancel.php`)

Handles payment cancellation and errors.

**Features:**
- User-friendly error messages
- Lists common failure reasons
- Provides retry option
- Support contact information

## Payment Intents vs Checkout Sessions

### Payment Intents

**When to Use:**
- ✓ You need a custom checkout form
- ✓ You want full control over the UX
- ✓ You need to handle multiple payment methods
- ✓ You're building a complex payment flow

**Flow:**
```
1. Create Payment Intent (backend)
2. Return client_secret to frontend
3. Collect payment details with Stripe.js
4. Confirm Payment Intent (frontend or backend)
5. Handle 3D Secure if needed
6. Redirect to success/cancel page
```

**Complexity:** Medium  
**Customization:** High  
**Best for:** Custom UI, control over UX

### Checkout Sessions

**When to Use:**
- ✓ You want simple, hosted payment page
- ✓ You don't need custom checkout UI
- ✓ You want minimal frontend code
- ✓ You're building a quick integration

**Flow:**
```
1. Create Checkout Session (backend)
2. Get session.url from response
3. Redirect customer to session.url
4. Stripe handles payment collection
5. Redirect back to your site on success/cancel
```

**Complexity:** Simple  
**Customization:** Low  
**Best for:** Simple storefronts, quick integration

### Comparison Table

| Feature | Payment Intents | Checkout Sessions |
|---------|-----------------|-------------------|
| Custom UI | ✓ Yes | ✗ Hosted page |
| 3D Secure | Manual | Automatic |
| Setup time | Medium | Fast |
| Code complexity | Medium | Simple |
| Control | Full | Limited |
| Best for | Complex flows | Simple payments |

## Security Best Practices

### 1. Protect Your Secret Key

```php
// ✓ GOOD - Environment variable
$secretKey = getenv('STRIPE_SECRET_KEY');

// ✗ BAD - Hardcoded in config
define('STRIPE_SECRET_KEY', 'sk_test_123...');

// ✗ WORSE - Exposed in frontend
const stripeSecret = 'sk_test_123...'; // JavaScript
```

### 2. Always Validate on Server Side

```php
// ✓ GOOD - Validate server-side
$amount = intval($_POST['amount']);
if ($amount < MIN_AMOUNT || $amount > MAX_AMOUNT) {
    return error('Invalid amount');
}

// ✗ BAD - Trust frontend
$amount = $_POST['amount']; // Could be hacked
```

### 3. Never Trust Payment Status from Frontend

```php
// ✓ GOOD - Verify with Stripe
$response = $client->getPaymentIntent($paymentIntentId);
if ($response['data']['status'] === 'succeeded') {
    // Process order
}

// ✗ BAD - Trust redirect parameter
if ($_GET['payment_successful'] === 'true') {
    // Process order - INSECURE!
}
```

### 4. Use Webhooks for Order Fulfillment

```php
// Webhooks are more reliable than redirects
// payment_intent.succeeded event:
// - Triggers even if customer doesn't return
// - Server-to-server verification
// - Can't be spoofed like URLs
```

### 5. Enable HTTPS in Production

```php
// Verify SSL certificate validity
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

// Always use HTTPS URLs
define('APP_URL', 'https://yourdomain.com');
```

## Example: Complete Payment Flow

### 1. Create Payment Form

```html
<!-- payment_form.html -->
<form id="payment-form">
    <div id="card-element"></div>
    <input type="email" id="email" placeholder="Email">
    <input type="hidden" id="amount" value="5000">
    <input type="hidden" id="currency" value="usd">
    <button type="submit">Pay $50.00</button>
</form>
```

### 2. Initialize Stripe.js

```javascript
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('<?php echo getStripePublishableKey(); ?>');
const elements = stripe.elements();
const cardElement = elements.create('card');
cardElement.mount('#card-element');
</script>
```

### 3. Handle Form Submission

```javascript
document.getElementById('payment-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Step 1: Create payment intent on backend
    const createResponse = await fetch('create_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            amount: document.getElementById('amount').value,
            currency: document.getElementById('currency').value,
            email: document.getElementById('email').value
        })
    });
    
    const createData = await createResponse.json();
    if (!createData.success) {
        alert('Error: ' + createData.error);
        return;
    }
    
    // Step 2: Confirm payment with Stripe.js
    const { paymentIntent, error } = await stripe.confirmCardPayment(
        createData.client_secret,
        {
            payment_method: {
                card: cardElement,
                billing_details: { email: document.getElementById('email').value }
            }
        }
    );
    
    // Step 3: Handle result
    if (error) {
        alert('Payment failed: ' + error.message);
    } else if (paymentIntent.status === 'succeeded') {
        window.location.href = 'success.php?payment_intent_id=' + paymentIntent.id;
    }
});
</script>
```

### 4. Handle Success

```php
// success.php
// The page verifies payment with Stripe API
// Shows confirmation and transaction details
```

## Error Handling

### API Errors

The `stripe_client.php` formats all errors consistently:

```php
$response = $client->createPaymentIntent(...);

if (!$response['success']) {
    // $response['error'] contains:
    // 'type' => 'card_error', 'invalid_request_error', etc.
    // 'message' => 'User-friendly message'
    // 'param' => 'Parameter that caused error'
    // 'code' => 'Error code from Stripe'
    
    $error = $response['error'];
    error_log('Stripe error: ' . $error['message']);
}
```

### HTTP Status Codes

- `200 OK` - Successful request
- `400 Bad Request` - Validation error
- `401 Unauthorized` - Authentication failed
- `402 Request Failed` - Payment declined
- `429 Too Many Requests` - Rate limited
- `500 Internal Server Error` - Server error

## Debugging

### Enable Debug Mode

```php
// config.php
define('DEBUG_MODE', true);
define('LOG_FILE_PATH', __DIR__ . '/logs/stripe_api.log');
```

### View Logs

```bash
tail -f logs/stripe_api.log
```

### Test with Stripe Test Cards

| Card Number | Description |
|-------------|-------------|
| `4242 4242 4242 4242` | Visa - Success |
| `4000 0000 0000 0002` | Visa - Decline |
| `4000 0025 0000 3155` | Visa - 3D Secure required |
| `378282246310005` | Amex - Success |

Use any future expiry date and any CVC.

## Webhook Implementation (Optional)

Webhooks provide server-to-server payment confirmation:

```php
// webhooks.php
<?php
require_once __DIR__ . '/config.php';

$payload = file_get_contents('php://input');
$event = json_decode($payload, true);

// Verify webhook signature
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
// ... verify signature ...

// Handle events
switch ($event['type']) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event['data']['object'];
        // Process order, send confirmation email
        break;
        
    case 'payment_intent.payment_failed':
        $paymentIntent = $event['data']['object'];
        // Notify customer of failure
        break;
}

http_response_code(200);
?>
```

**Set up webhook in Stripe Dashboard:**
1. Go to [Webhooks](https://dashboard.stripe.com/webhooks)
2. Add endpoint: `https://yourdomain.com/webhooks.php`
3. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`

## Production Checklist

- [ ] Use live API keys (not test keys)
- [ ] Enable HTTPS for all endpoints
- [ ] Move config to environment variables
- [ ] Set `DEBUG_MODE` to `false`
- [ ] Implement webhook handling
- [ ] Set up error logging and monitoring
- [ ] Test all payment paths with real cards
- [ ] Verify SSL certificate configuration
- [ ] Implement rate limiting
- [ ] Set up customer communication (emails)
- [ ] Document your payment process

## Troubleshooting

### "Stripe API keys not configured"

```php
// Solution: Update config.php with your actual keys
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_ACTUAL_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_ACTUAL_KEY');
```

### "Failed to initialize cURL"

```php
// Check PHP has cURL extension enabled
php -m | grep curl  // Linux/Mac
php -m | findstr curl  // Windows

// Or check phpinfo() page
```

### "SSL certificate problem"

```php
// For development only (not production!):
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

// For production, ensure certificate is properly installed
```

### "Connection timeout"

```php
// Increase timeout in stripe_client.php
curl_setopt($curl, CURLOPT_TIMEOUT, 60);  // 60 seconds
```

## API Reference

### Stripe Amounts

**Important:** Stripe amounts are in the smallest currency unit:

```
USD:  1000 = $10.00    (cents)
EUR:  1000 = €10.00    (cents)
JPY:  1000 = ¥1,000    (whole units - no decimals)
```

### Payment Statuses

| Status | Meaning |
|--------|---------|
| `requires_payment_method` | Waiting for payment method |
| `requires_confirmation` | Awaiting confirmation |
| `requires_action` | Customer action needed (3DS) |
| `processing` | Being processed |
| `succeeded` | Successfully paid |
| `canceled` | Payment canceled |

## Support & Resources

- **Stripe Documentation:** https://stripe.com/docs
- **Payment Intents Guide:** https://stripe.com/docs/payments/payment-intents
- **Checkout Sessions:** https://stripe.com/docs/payments/checkout
- **API Reference:** https://stripe.com/docs/api
- **Testing:** https://stripe.com/docs/testing

## License

This code is provided as-is for educational and commercial use.

## Version

- **v1.0** - Initial release with Payment Intents and Checkout Sessions

---

**Last Updated:** 2024

For updates and improvements, check the project repository.
