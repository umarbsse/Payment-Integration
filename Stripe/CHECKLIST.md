# Project Checklist - Stripe Payment Integration

Use this checklist to ensure proper setup and deployment of the payment integration.

## Initial Setup

- [ ] **Clone/Download Project**
  - [ ] All files are in place
  - [ ] Directory permissions are correct (755 for directories, 644 for files)

- [ ] **Create Stripe Account**
  - [ ] Go to https://dashboard.stripe.com/register
  - [ ] Verify email address
  - [ ] Enable 2FA for security

- [ ] **Get API Keys**
  - [ ] Log in to Stripe Dashboard
  - [ ] Navigate to Developers → API Keys
  - [ ] Copy Test Secret Key (sk_test_...)
  - [ ] Copy Test Publishable Key (pk_test_...)

## Local Development Setup

- [ ] **Server Requirements**
  - [ ] PHP 7.0+ installed (preferably 8.0+)
  - [ ] cURL extension enabled: `php -m | grep curl`
  - [ ] JSON extension enabled: `php -m | grep json`
  - [ ] Apache/Nginx running OR using `php -S localhost:8000`

- [ ] **Project Configuration**
  - [ ] Create `.env` file from `.env.example` (OR edit config.php)
  - [ ] Add STRIPE_SECRET_KEY with test key
  - [ ] Add STRIPE_PUBLISHABLE_KEY with test key
  - [ ] Set APP_URL (e.g., http://localhost:8000)
  - [ ] Create `logs/` directory and set permissions: `chmod 755 logs`

- [ ] **Configuration Verification**
  - [ ] Run: `php test_api.php test-config`
  - [ ] All checks pass (green ✓)
  - [ ] No errors or warnings

## Testing Phase

- [ ] **Test Payment Intents Flow**
  - [ ] Navigate to `/example_form.php`
  - [ ] Fill in amount: 50.00
  - [ ] Fill in email: test@example.com
  - [ ] Use test card: 4242 4242 4242 4242
  - [ ] Expiry: Any future date
  - [ ] CVC: Any 3 digits
  - [ ] Click "Pay"
  - [ ] Payment completes successfully
  - [ ] Redirected to success.php

- [ ] **Test Checkout Sessions Flow**
  - [ ] Navigate to `/example_checkout_form.php`
  - [ ] Fill in email: test@example.com
  - [ ] Click "Go to Checkout"
  - [ ] Redirected to Stripe checkout page
  - [ ] Complete payment with test card
  - [ ] Redirected back to success.php

- [ ] **Test Card Decline**
  - [ ] Try card: 4000 0000 0000 0002
  - [ ] Payment should be declined
  - [ ] Error message displayed
  - [ ] Redirected to cancel.php

- [ ] **API Testing**
  - [ ] Run: `php test_api.php create-intent 5000 usd "Test"`
  - [ ] Returns payment intent details
  - [ ] Can retrieve with: `php test_api.php get-intent pi_...`

- [ ] **Debug Logs**
  - [ ] Check `logs/stripe_api.log`
  - [ ] Contains API request/response details
  - [ ] No sensitive data exposed in logs

## Code Review

- [ ] **Security Review**
  - [ ] Secret keys not hardcoded in version control
  - [ ] `.env` file in `.gitignore`
  - [ ] No credentials in README or documentation
  - [ ] SSL verification enabled in cURL calls
  - [ ] Input validation on all endpoints

- [ ] **Code Quality**
  - [ ] All functions have documentation
  - [ ] Error handling implemented
  - [ ] Consistent code formatting
  - [ ] No Debug statements left in code

- [ ] **API Integration**
  - [ ] Payment Intents creation working
  - [ ] Payment Intents retrieval working
  - [ ] Checkout Sessions creation working
  - [ ] Error responses properly formatted

## Production Preparation

- [ ] **Security Hardening**
  - [ ] HTTPS enabled for all URLs
  - [ ] SSL certificate valid and trusted
  - [ ] Security headers configured (if using framework)
  - [ ] CSRF protection implemented (if needed)
  - [ ] Rate limiting implemented

- [ ] **Configuration for Production**
  - [ ] Get Live API Keys from Stripe Dashboard
  - [ ] Set production environment variables
  - [ ] Update .env with live keys (sk_live_, pk_live_)
  - [ ] Disable DEBUG_MODE in config.php
  - [ ] Set temporary file/log paths to secure locations

- [ ] **Database & Persistence (if needed)**
  - [ ] Database setup for storing orders
  - [ ] Payment history tables created
  - [ ] Connection string configured securely
  - [ ] Backup strategy in place

- [ ] **Webhook Setup**
  - [ ] Webhook endpoint deployed and accessible
  - [ ] Stripe Dashboard → Webhooks
  - [ ] Add endpoint: https://yourdomain.com/webhooks.php
  - [ ] Select events: payment_intent.succeeded, payment_intent.payment_failed
  - [ ] Get signing secret and add to .env
  - [ ] Webhook signature verification working

- [ ] **Email Configuration (if applicable)**
  - [ ] Confirmation email templates created
  - [ ] Email service configured
  - [ ] Test confirmations sending successfully
  - [ ] Emails not going to spam folder

- [ ] **Monitoring & Logging**
  - [ ] Log rotation configured
  - [ ] Error alerting setup (email, Slack, etc.)
  - [ ] Payment success rate monitoring
  - [ ] Failed payment alerts configured

## Go-Live Checklist

- [ ] **Pre-Launch Testing**
  - [ ] Full end-to-end payment flow tested with live cards
  - [ ] Test small amount ($0.50 or equivalent)
  - [ ] Verify charge appears in Stripe Dashboard
  - [ ] Webhook notifications received and processed
  - [ ] Confirmation email received

- [ ] **Data Security**
  - [ ] No test data in production database
  - [ ] No sensitive data in error messages (shown to users)
  - [ ] PCI compliance verified (don't store card data)
  - [ ] Backups automated and tested
  - [ ] Disaster recovery plan documented

- [ ] **Deployment**
  - [ ] Code deployed to production server
  - [ ] Database migrations run successfully
  - [ ] All dependencies installed
  - [ ] File permissions correct
  - [ ] Logs directory writable by web server

- [ ] **Launch**
  - [ ] Make payment page live
  - [ ] Monitor initial transactions closely
  - [ ] Have support team ready for issues
  - [ ] Check error logs regularly first 24 hours
  - [ ] Verify customer confirmations received

## Post-Launch

- [ ] **Monitoring**
  - [ ] Daily payment volume check
  - [ ] Error rate monitoring
  - [ ] Webhook delivery failures monitored
  - [ ] Email bounce rates checked

- [ ] **Optimization**
  - [ ] Analyze payment success rates
  - [ ] Review failed payment causes
  - [ ] Optimize checkout UX based on metrics
  - [ ] Monitor payment processing times

- [ ] **Documentation**
  - [ ] Production runbook created
  - [ ] Incident response procedures written
  - [ ] Team trained on payment system
  - [ ] Support documentation updated

- [ ] **Maintenance Schedule**
  - [ ] Monthly security review
  - [ ] Quarterly API updates review
  - [ ] Annual PCI compliance audit
  - [ ] Regular backup verification

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Stripe not configured" | Check config.php has actual API keys, not placeholders |
| "cURL error" | Verify cURL extension: `php -m \| grep curl` |
| "SSL certificate error" | Update CA bundle or disable verification for dev only |
| "Payment doesn't show in Stripe" | Check if using live vs test keys correctly |
| "Webhook not received" | Verify endpoint is publicly accessible and returning 200 |

## File Checklist

- [ ] config.php - Configuration and credentials
- [ ] stripe_client.php - Main API client
- [ ] create_payment.php - Payment Intent endpoint
- [ ] checkout_session.php - Checkout Session endpoint
- [ ] success.php - Success page
- [ ] cancel.php - Cancellation page
- [ ] webhooks.php - Webhook handler
- [ ] example_form.php - Payment Intents example
- [ ] example_checkout_form.php - Checkout Sessions example
- [ ] test_api.php - CLI testing tool
- [ ] index.php - Dashboard/index page
- [ ] README.md - Documentation
- [ ] SETUP.md - Setup guide
- [ ] .gitignore - Git ignore rules
- [ ] .env.example - Environment template
- [ ] logs/ - Directory for API logs (create if not exists)

## Notes & Status

```
Project Status: [ ] Not Started  [ ] In Progress  [ ] Complete

Last Updated: _______________

Completed By: _______________

Sign-off: _______________
```

---

**Remember:** 
- Never commit `.env` file with real keys to version control
- Test thoroughly in test mode before going live
- Keep Stripe library/API documentation handy
- Monitor Stripe Dashboard for all transactions
- Have a support plan for payment issues

For questions, see README.md or visit https://stripe.com/docs
