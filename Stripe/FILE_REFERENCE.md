# Project Structure & File Reference

Complete guide to all files in the Stripe Payment Integration project.

## 📂 Project Layout

```
stripe/
├── 📄 Core Configuration
│   ├── config.php                  # Configuration file with API keys
│   ├── .env.example                # Environment variables template
│   └── .gitignore                  # Git ignore rules (protect secrets)
│
├── 💼 API & Backend
│   ├── stripe_client.php           # Main Stripe API client (cURL)
│   ├── create_payment.php          # Create Payment Intent endpoint
│   ├── checkout_session.php        # Create Checkout Session endpoint
│   └── webhooks.php                # Webhook handler for Stripe events
│
├── 🎨 Frontend Pages
│   ├── index.php                   # Dashboard & project overview
│   ├── example_form.php            # Payment Intents checkout form
│   ├── example_checkout_form.php   # Checkout Sessions form
│   ├── success.php                 # Payment success page
│   └── cancel.php                  # Payment cancellation page
│
├── 🛠️ Developer Tools
│   └── test_api.php                # Command-line API testing tool
│
├── 📚 Documentation
│   ├── README.md                   # Complete API & usage documentation
│   ├── SETUP.md                    # Installation & deployment guide
│   ├── CHECKLIST.md                # Setup verification checklist
│   └── FILE_REFERENCE.md           # This file
│
└── 📁 Runtime Directories
    └── logs/                       # API debug logs (create this)
```

## 📄 Detailed File Description

### Configuration Files

#### config.php
**Purpose:** Central configuration for the entire payment system
**Contains:**
- Stripe API credentials (SECRET_KEY, PUBLISHABLE_KEY)
- Application settings (APP_NAME, APP_URL)
- Payment limits (MIN/MAX amounts)
- Logging configuration
- Helper functions for config access

**Usage:** Required by all PHP files, included at the top
**Security:** Should use environment variables in production

**Key Functions:**
- `getStripePublishableKey()` - Get publishable key safely for frontend
- `isStripeConfigured()` - Verify config is complete
- `getStripeRequestHeaders()` - Get headers for API calls

---

#### .env.example
**Purpose:** Template for environment variables
**Contains:** Sample structure for all configuration options
**Usage:** Copy to `.env` and fill in your actual values
**Security:** `.env` file should NOT be committed to git

**Variables Documented:**
- STRIPE_SECRET_KEY and STRIPE_PUBLISHABLE_KEY
- APP_NAME, APP_URL, DEFAULT_CURRENCY
- DEBUG_MODE and LOG_FILE_PATH
- Optional: Database, Email, Webhook settings

---

#### .gitignore
**Purpose:** Prevent accidental commit of sensitive files
**Prevents commits of:**
- .env files (with real API keys)
- API logs (may contain sensitive data)
- IDE settings (.vscode, .idea)
- OS files (Thumbs.db, .DS_Store)
- Temporary files

**Important:** Never modify this to allow committing .env!

---

### Core API Implementation

#### stripe_client.php
**Purpose:** Main Stripe API client using cURL
**Design:** OOP class-based approach
**Complexity:** ~550 lines of well-documented code

**Main Methods:**
- `__construct($secretKey, $apiBaseUrl, $debugMode, $logFilePath)`
  - Initialize the API client
  
- `createPaymentIntent($amount, $currency, $description, $metadata)`
  - Create a Payment Intent with Stripe
  - Returns: `['success' => bool, 'data' => array, 'error' => array]`

- `confirmPaymentIntent($paymentIntentId, $paymentMethodId, $returnUrl)`
  - Confirm a Payment Intent with payment details
  
- `getPaymentIntent($paymentIntentId)`
  - Retrieve Payment Intent details from Stripe
  
- `createCheckoutSession($amount, $currency, $description, $successUrl, $cancelUrl)`
  - Create a Checkout Session (simpler hosted checkout)
  
- `isPaymentSucceeded($paymentIntentId)`
  - Convenience method to check if payment succeeded

**Key Features:**
- Pure cURL implementation (no SDK)
- Automatic error handling and formatting
- Debug logging for troubleshooting
- Bearer token authentication
- Request body form-encoding

**Security Features:**
- SSL certificate verification
- Timeout configuration
- Error message sanitization
- No credential logging

---

#### create_payment.php
**Purpose:** API endpoint to create Payment Intents
**Type:** Backend API endpoint (REST)

**Accepts:** POST request with:
- `amount` (in cents) - **REQUIRED**
- `currency` (ISO code) - Optional, defaults to USD
- `description` (text) - Optional
- `order_id` (string) - Optional metadata
- `email` (email) - Optional

**Returns:** JSON response
```json
{
  "success": true,
  "client_secret": "pi_..._secret_...",
  "payment_intent_id": "pi_...",
  "status": "requires_payment_method",
  "amount": 5000,
  "currency": "USD",
  "message": "Payment intent created..."
}
```

**Security Validation:**
- Amount validated: positive, within min/max range
- Currency validated against allowed list
- Server-side validation (never trust frontend)
- Returns error for invalid requests

**Flow:**
1. Validate input parameters
2. Check Stripe configuration
3. Create Stripe API client
4. Call createPaymentIntent()
5. Format and return response

---

#### checkout_session.php
**Purpose:** API endpoint for Checkout Sessions (simplified payments)
**Type:** Backend API endpoint (REST)

**Accepts:** POST request with:
- `amount` (in cents) - **REQUIRED**
- `currency` (ISO code) - Optional
- `description` (text) - Optional
- `email` (email) - Optional

**Returns:** JSON response
```json
{
  "success": true,
  "url": "https://checkout.stripe.com/pay/cs_...",
  "session_id": "cs_...",
  "message": "Redirecting to payment page..."
}
```

**Differences from Payment Intents:**
- Redirects to Stripe-hosted checkout page
- No custom form building needed
- Simpler to implement
- Less control over UX

---

### Frontend & User Pages

#### index.php
**Purpose:** Project dashboard and getting started guide
**Type:** HTML dashboard (informational)
**Content:**
- Quick start instructions
- Links to example forms
- Feature comparison table
- File structure overview
- Security guidelines
- Next steps

**No backend logic:** Pure HTML/CSS for browsing

---

#### example_form.php
**Purpose:** Example implementation of Payment Intents flow
**Type:** HTML form + JavaScript
**Flow:**
1. User fills payment form
2. JavaScript calls Stripe.js initialization
3. User submits form
4. JavaScript creates Payment Intent on backend
5. Stripe.js confirms payment with card details
6. Redirects to success or displays error

**Technologies:**
- HTML for form markup
- JavaScript for Stripe.js integration
- CSS for styling

**Key JavaScript:**
- Stripe element mounting
- Form submission handling
- Payment confirmation
- Redirect on success

---

#### example_checkout_form.php
**Purpose:** Example of Checkout Sessions flow (simpler alternative)
**Type:** HTML form + JavaScript
**Flow:**
1. User enters email
2. JavaScript submits to checkout_session.php
3. Backend creates Checkout Session
4. JavaScript redirects to session.url (Stripe-hosted page)
5. Customer completes payment at Stripe
6. Stripe redirects back to success.php or cancel.php

**Difference:**
- No custom checkout form needed
- Stripe handles entire payment page
- Minimal frontend code

---

#### success.php
**Purpose:** Display payment success and confirm with Stripe
**Type:** HTML page with backend verification

**Flow:**
1. Receive payment_intent_id from URL parameter
2. Query Stripe API to verify payment status
3. Display confirmation if succeeded
4. Show error if payment not actually succeeded

**Security Important:**
- NEVER assume payment succeeded just from redirect
- ALWAYS verify with Stripe API
- Check final status before processing order

**Displays:**
- Transaction confirmation
- Payment amount and currency
- Transaction ID
- Date/time
- Print receipt option
- Support contact info

---

#### cancel.php
**Purpose:** Handle payment cancellation or failure
**Type:** HTML error page

**Scenarios Handled:**
- Customer cancels payment
- Card is declined
- 3D Secure verification fails
- Payment times out
- Other Payment Intent failures

**Content:**
- Friendly error message
- Reason for failure (if available)
- List of common failure reasons
- Retry button
- Support contact information

---

### Development & Production Tools

#### webhooks.php
**Purpose:** Handle incoming Stripe webhook events
**Type:** Webhook endpoint handler
**Use Case:** Production order fulfillment

**Events Handled:**
- `payment_intent.succeeded` - Payment completed
- `payment_intent.payment_failed` - Payment failed
- `payment_intent.canceled` - Payment canceled
- `charge.refunded` - Payment refunded
- `charge.dispute.created` - Chargeback initiated

**Security:**
- Verifies webhook signature (cryptographic verification)
- Rejects unverified requests with HTTP 403
- Processes only signed events from Stripe

**Functions:**
- `verifyWebhookSignature()` - Verify message is from Stripe
- `handlePaymentSucceeded()` - Process successful payment
- `handlePaymentFailed()` - Process failed payment
- `handleRefunded()` - Process refunds

**TODO Implementations:**
- Update order status in database
- Send confirmation emails
- Trigger fulfillment process
- Grant product access (digital products)

---

#### test_api.php
**Purpose:** Command-line tool to test Stripe API
**Type:** CLI (command-line interface) tool
**Usage:** `php test_api.php [command] [args]`

**Available Commands:**
```
create-intent <amount> [currency] [description]
  Create a Payment Intent
  Example: php test_api.php create-intent 5000 usd "Test"

get-intent <payment_intent_id>
  Get Payment Intent details
  Example: php test_api.php get-intent pi_123

confirm-intent <pi_id> <pm_id> [return_url]
  Confirm a Payment Intent
  Example: php test_api.php confirm-intent pi_123 pm_456

create-session <amount> [currency] [description]
  Create a Checkout Session
  Example: php test_api.php create-session 5000 usd "Product"

test-config
  Verify configuration is correct
  Example: php test_api.php test-config

help
  Show help and usage
  Example: php test_api.php help
```

**Benefits:**
- Test API without web browser
- Debug configuration issues
- No need for HTML forms
- Faster iteration during development

---

### Documentation Files

#### README.md
**Purpose:** Complete API documentation and usage guide
**Length:** Comprehensive reference document
**Sections:**
- Features overview
- Project structure
- Quick start guide
- Core components documentation
- Payment Intents vs Checkout Sessions comparison
- Security best practices
- Error handling reference
- Example code and integrations
- Troubleshooting guide
- API reference and test cards

**Audience:** Developers implementing the integration

---

#### SETUP.md
**Purpose:** Installation and deployment guide
**Length:** Step-by-step instructional guide
**Sections:**
- Local development setup (Windows/Mac/Linux)
- Stripe account creation
- Getting API keys
- Configuration options (hardcoded, .env, php-dotenv)
- Testing locally
- Production deployment checklist
- Troubleshooting common issues

**Audience:** Developers setting up the project

---

#### CHECKLIST.md
**Purpose:** Verification checklist for setup and deployment
**Type:** Interactive checklist

**Sections:**
- Initial setup (account creation, API keys)
- Local development setup
- Testing phase (all payment flows)
- Code review (security, quality)
- Production preparation
- Go-live checklist
- Post-launch monitoring
- Common issues table
- File checklist

**Audience:** Project managers, QA teams, deployment engineers

---

#### FILE_REFERENCE.md (This File)
**Purpose:** Describe all project files and their purposes
**Use:** Quick reference for understanding project structure

---

## 🔄 How Files Work Together

### Payment Intents Flow
```
1. User visits example_form.php
2. Fills payment form (amount, email, card)
3. JavaScript calls create_payment.php
4. create_payment.php uses stripe_client.php to create Payment Intent
5. Returns client_secret to frontend
6. JavaScript uses Stripe.js to confirm payment
7. Redirects to success.php on success
8. success.php queries Stripe API to verify payment
9. Displays confirmation
```

### Checkout Sessions Flow
```
1. User visits example_checkout_form.php
2. Fills basic form (email)
3. JavaScript calls checkout_session.php
4. checkout_session.php uses stripe_client.php to create Session
5. Returns session.url to frontend
6. JavaScript redirects to Stripe-hosted checkout
7. User completes payment at Stripe
8. Stripe redirects to success.php or cancel.php
9. success.php queries Stripe API to verify
```

### Webhook Flow (Production)
```
1. Customer pays via website
2. Stripe processes payment
3. Stripe sends webhook event to webhooks.php
4. webhooks.php verifies signature
5. Extracts event type (payment_intent.succeeded, etc.)
6. Calls appropriate handler function
7. Handler updates database, sends emails, etc.
8. Returns HTTP 200 to acknowledge
```

## 📊 Dependencies & Requirements

### File Dependencies

**config.php**
- No dependencies (loads first)
- Required by all other files

**stripe_client.php**
- Depends on: config.php
- Required by: create_payment.php, checkout_session.php, success.php, webhooks.php

**create_payment.php**
- Depends on: config.php, stripe_client.php
- Used by: example_form.php (JavaScript)

**success.php**
- Depends on: config.php, stripe_client.php
- Called from: example_form.php, Stripe, customer redirects

**webhooks.php**
- Depends on: config.php, stripe_client.php
- Called by: Stripe servers (HTTP POST)

### System Requirements

- PHP 7.0+ (preferably 8.0+)
- cURL extension enabled
- JSON extension enabled
- OpenSSL for HTTPS
- Web server (Apache, Nginx, or PHP built-in server)

### External Dependencies

- Stripe API (https://api.stripe.com/v1)
- Stripe.js library (for Payment Intents example)
- Browser with JavaScript support

## 🔐 Sensitive Files

**DO NOT COMMIT:**
- `.env` file (contains API keys)
- Local config with hardcoded keys
- Webhook signing secrets
- Database credentials

**ALWAYS GITIGNORE:**
- `.env`
- `config.local.php`
- `logs/`
- IDE settings
- OS temporary files

## 📈 File Modification Guide

| File | Modification | Impact |
|------|-------------|--------|
| config.php | Add/change API keys | All functionality stops if wrong |
| stripe_client.php | Change API endpoint | Breaks all API calls |
| example_form.php | Change styling | UI appearance only |
| example_checkout_form.php | Add fields | Requires backend update |
| create_payment.php | Change validation | Payment amounts may be rejected |
| success.php | Change display | Customer sees different confirmation |
| webhooks.php | Add event handling | New Stripe events can trigger logic |

## ✅ Checklist: Complete Installation

- [ ] All files present in directory
- [ ] `logs/` directory created with 755 permissions
- [ ] config.php has valid Stripe keys (or .env file)
- [ ] `.env` is in `.gitignore` (not shown in git)
- [ ] Run `php test_api.php test-config` - all passing
- [ ] Access index.php in browser - dashboard displays
- [ ] Try example_form.php - can create payment intent
- [ ] Try example_checkout_form.php - redirects to Stripe

## References

- **Stripe Documentation:** https://stripe.com/docs
- **Payment Intents API:** https://stripe.com/docs/payments/payment-intents
- **Checkout Sessions:** https://stripe.com/docs/payments/checkout
- **Webhooks:** https://stripe.com/docs/webhooks
- **Testing:** https://stripe.com/docs/testing

---

**Last Updated:** 2024
**Version:** 1.0
