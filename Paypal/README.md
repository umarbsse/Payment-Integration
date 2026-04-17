# PayPal Integration

PHP PayPal payment integration using REST API v2.

## Files

- config.php - Configuration
- paypal_client.php - API client
- create_order.php - Create order
- success.php - Handle success
- cancel.php - Handle cancel

## Setup

1. Get PayPal Client ID and Secret from developer.paypal.com
2. Update config.php with credentials
3. For production, use environment variables and HTTPS

## Usage

Visit create_order.php to start payment flow.

## Production Notes

- Use env vars for credentials
- Enable HTTPS
- Add proper error handling and logging