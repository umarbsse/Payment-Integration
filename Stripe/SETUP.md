# Setup Guide - Stripe Payment Integration

Step-by-step instructions to get this payment integration running locally and in production.

## Table of Contents

1. [Local Development Setup](#local-development-setup)
2. [Stripe Account Setup](#stripe-account-setup)
3. [Configuration](#configuration)
4. [Testing Locally](#testing-locally)
5. [Deploying to Production](#deploying-to-production)
6. [Troubleshooting](#troubleshooting)

---

## Local Development Setup

### Prerequisites

- PHP 7.0+ (preferably 8.0+)
- cURL extension enabled
- XAMPP, WAMP, or LAMP server running
- A text editor (VS Code recommended)

### Step 1: Install PHP (if needed)

**Windows (XAMPP):**
1. Download [XAMPP](https://www.apachefriends.org/)
2. Run installer and select Apache + PHP
3. Start Apache from XAMPP Control Panel

**macOS:**
```bash
# Using Homebrew
brew install php
```

**Linux:**
```bash
# Ubuntu/Debian
sudo apt-get install php php-curl

# Fedora
sudo dnf install php php-curl
```

### Step 2: Verify PHP and cURL

```bash
# Check PHP version
php --version
# Output: PHP 8.1.0 or higher

# Check cURL is enabled
php -m | grep curl
# Output: curl
```

### Step 3: Set Up Project Directory

```bash
# Create project folder
mkdir -p ~/projects/stripe-payment

# Navigate to project
cd ~/projects/stripe-payment

# Copy all project files here
# (config.php, stripe_client.php, create_payment.php, etc.)

# Create logs directory
mkdir logs
chmod 755 logs
```

### Step 4: Configure Web Server

**XAMPP (Windows/Mac):**
1. Place project in `C:\xampp\htdocs\stripe-payment` (Windows) or `/Applications/XAMPP/htdocs/stripe-payment` (Mac)
2. Access at `http://localhost/stripe-payment`

**Local PHP Server:**
```bash
cd ~/projects/stripe-payment
php -S localhost:8000
# Now access at http://localhost:8000
```

---

## Stripe Account Setup

### Step 1: Create Stripe Account

1. Go to [stripe.com/register](https://stripe.com/register)
2. Enter email and password
3. Verify email address
4. Complete account setup

### Step 2: Get API Keys

1. Log in to [Stripe Dashboard](https://dashboard.stripe.com)
2. Click "Developers" in top-left menu
3. Select "API Keys" from sidebar
4. You'll see two sets of keys:
   - **Test Keys** (for development)
   - **Live Keys** (for production)

For now, use **Test Keys**:
- Publishable Key (starts with `pk_test_`)
- Secret Key (starts with `sk_test_`)

### Step 3: Copy Your Keys

You'll need these for the next section. Keep them safe!

---

## Configuration

### Option A: Simple Configuration (Development Only)

**Edit `config.php`:**

```php
define('STRIPE_SECRET_KEY', 'sk_test_YOUR_SECRET_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_YOUR_PUBLISHABLE_KEY');
define('APP_URL', 'http://localhost:8000');
define('DEBUG_MODE', true);
```

⚠️ **WARNING:** Do NOT commit this with real keys!

### Option B: Using Environment Variables (Recommended)

**Step 1: Create `.env` file**

```bash
# Copy the example file
cp .env.example .env

# Or create manually in project root
touch .env
```

**Step 2: Edit `.env`**

```
STRIPE_SECRET_KEY=sk_test_YOUR_SECRET_KEY
STRIPE_PUBLISHABLE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
APP_URL=http://localhost:8000
DEBUG_MODE=true
```

**Step 3: Update `config.php` to load `.env`**

Add this at the very top of `config.php`:

```php
<?php
// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// Now use environment variables
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY'));
// ... rest of config
```

**Step 4: Add `.env` to `.gitignore`**

The `.env` file is already in `.gitignore` - if you're using git:

```bash
# Verify .env is ignored
grep ".env" .gitignore
```

### Option C: Using php-dotenv (Best for Production)

**Install via Composer:**

```bash
composer require vlucas/phpdotenv
```

**Use in your app:**

```php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define('STRIPE_SECRET_KEY', $_ENV['STRIPE_SECRET_KEY']);
define('STRIPE_PUBLISHABLE_KEY', $_ENV['STRIPE_PUBLISHABLE_KEY']);
```

---

## Testing Locally

### Test Payment Intents Flow

1. Open browser to: `http://localhost:8000/example_form.php`

2. Fill in the form:
   - Amount: `50.00` (pre-filled)
   - Email: `test@example.com`
   - Card: Use test card `4242 4242 4242 4242`
   - Expiry: Any future date (e.g., `12/25`)
   - CVC: Any 3 digits (e.g., `123`)

3. Click "Pay $50.00"

4. Check for success page

**Test Cards:**

| Card Number | Description |
|-------------|-------------|
| `4242 4242 4242 4242` | Success |
| `4000 0000 0000 0002` | Decline |
| `4000 0025 0000 3155` | 3D Secure |
| `378282246310005` | Amex Success |

### Test Checkout Sessions Flow

1. Open browser to: `http://localhost:8000/example_checkout_form.php`

2. Enter email: `test@example.com`

3. Click "Go to Checkout"

4. You'll be redirected to Stripe's hosted checkout page

5. Fill in test card and complete purchase

### View Debug Logs

```bash
# Check API logs
tail -f logs/stripe_api.log

# On Windows (PowerShell)
Get-Content logs/stripe_api.log -Wait
```

---

## Deploying to Production

### Step 1: Get Production Keys

1. Log in to [Stripe Dashboard](https://dashboard.stripe.com)
2. Click "Developers" → "API Keys"
3. Toggle "View Live Data"
4. Copy your **live** keys (start with `sk_live_` and `pk_live_`)

⚠️ **IMPORTANT:** Live keys charge real money!

### Step 2: Update Configuration

**Option A: Environment Variables (Recommended)**

Update your server's environment:

```bash
# Linux/Mac
export STRIPE_SECRET_KEY='sk_live_YOUR_LIVE_SECRET_KEY'
export STRIPE_PUBLISHABLE_KEY='pk_live_YOUR_LIVE_PUBLISHABLE_KEY'

# Or in .env on production server
echo "STRIPE_SECRET_KEY=sk_live_..." >> /var/www/stripe/.env
echo "STRIPE_PUBLISHABLE_KEY=pk_live_..." >> /var/www/stripe/.env
```

**Option B: Update config.php**

```php
// Production config
define('STRIPE_SECRET_KEY', 'sk_live_YOUR_LIVE_SECRET_KEY');
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_YOUR_LIVE_PUBLISHABLE_KEY');
define('APP_URL', 'https://yourdomain.com');
define('DEBUG_MODE', false);  // ⚠️ Disable debugging in production
```

### Step 3: Enable HTTPS

```php
// Ensure SSL is verified
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

// Update APP_URL to HTTPS
define('APP_URL', 'https://yourdomain.com');
```

### Step 4: Set Up Webhooks (Recommended)

Webhooks provide server-to-server payment confirmation:

1. Go to [Webhooks Setup](https://dashboard.stripe.com/webhooks)
2. Click "Add Endpoint"
3. Enter your webhook URL: `https://yourdomain.com/webhooks.php`
4. Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`
5. Copy the signing secret and add to `.env`

### Step 5: Final Checklist

- [ ] HTTPS enabled
- [ ] Live API keys configured
- [ ] DEBUG_MODE set to false
- [ ] Log files configured
- [ ] `.env` file in .gitignore
- [ ] Webhooks set up
- [ ] Test purchase with small amount
- [ ] Verify email notifications working

---

## Troubleshooting

### "Stripe keys not configured"

**Problem:** Config error on page load.

**Solution:**
```php
// Check config.php has actual keys, not placeholders
define('STRIPE_SECRET_KEY', 'sk_test_actual_key_not_placeholder');
```

### "Failed to initialize cURL"

**Problem:** PHP cURL extension not loaded.

**Solution:**
```bash
# Check if cURL is enabled
php -m | grep curl

# If not, install cURL:
# Ubuntu: sudo apt-get install php-curl
# macOS: brew install php-curl
```

### "HTTP 401 - Unauthorized"

**Problem:** Invalid API key format or authentication failure.

**Solution:**
- Verify key starts with `sk_test_` or `sk_live_`
- Check for extra spaces: `' sk_test_...'` ❌ (has space)
- Ensure it's the Secret Key, not Publishable Key

### "SSL certificate problem"

**Problem:** cURL can't verify Stripe's SSL certificate.

**Solution:**
```php
// For development only:
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

// For production, ensure certificate is properly installed
// Update certificate bundle if needed
curl_setopt($curl, CURLOPT_CAINFO, '/path/to/cacert.pem');
```

### "Payment declined"

**Problem:** Test card shows decline message.

**Solution:**
- Use card `4000 0000 0000 0002` to intentionally test decline
- Use `4242 4242 4242 4242` for success
- Check card hasn't expired

### "Logs directory not writable"

**Problem:** Can't write to logs folder.

**Solution:**
```bash
# Make logs directory writable
chmod 777 logs

# Or create with correct permissions
mkdir -p logs && chmod 755 logs
```

### Payment succeeds but didn't receive email

**Problem:** Confirmation not received.

**Solution:**
- Check email spam/junk folder
- Verify `APP_URL` is correct in config
- Check Stripe Dashboard for payment (it succeeded even if email failed)

---

## Next Steps

1. **Read the README.md** for detailed API documentation
2. **Review stripe_client.php** to understand the cURL implementation
3. **Set up webhooks** for production order fulfillment
4. **Customize templates** in success.php and cancel.php
5. **Implement database storage** for transaction records
6. **Add email notifications** for payment confirmations

## Support

- **Stripe Documentation:** https://stripe.com/docs
- **Stripe Support:** https://support.stripe.com
- **PHP cURL Docs:** https://www.php.net/manual/en/book.curl.php

---

**Last Updated:** 2024
