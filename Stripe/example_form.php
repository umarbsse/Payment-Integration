<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form - Payment Intents</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }
        
        .subtitle {
            color: #999;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        #card-element {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        
        .StripeElement {
            color: #666;
        }
        
        .StripeElement--focus {
            border-color: #667eea;
        }
        
        .StripeElement--invalid {
            border-color: #e74c3c;
        }
        
        #card-errors {
            color: #e74c3c;
            margin-top: 8px;
            font-size: 14px;
            min-height: 20px;
        }
        
        button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 5px;
            background: #667eea;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        button:hover:not(:disabled) {
            background: #5568d3;
            transform: translateY(-2px);
        }
        
        button:disabled {
            background: #bbb;
            cursor: not-allowed;
        }
        
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #333;
        }
        
        .test-card {
            background: #fff8dc;
            border-left: 4px solid #f39c12;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 13px;
        }
        
        .test-card strong {
            display: block;
            margin-bottom: 5px;
            color: #d68910;
        }
        
        .test-card code {
            font-family: monospace;
            background: #fffacd;
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        .loading {
            display: none;
            text-align: center;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background: #fadbd8;
            color: #c0392b;
            border-left: 4px solid #c0392b;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
        
        .success-message {
            background: #d5f4e6;
            color: #27ae60;
            border-left: 4px solid #27ae60;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Complete Payment</h1>
        <p class="subtitle">Using Stripe Payment Intents API</p>
        
        <div class="info-box">
            📌 This form demonstrates the <strong>Payment Intents</strong> flow.
            You have full control over the checkout UI and payment flow.
        </div>
        
        <div class="error-message" id="error-message"></div>
        <div class="success-message" id="success-message"></div>
        
        <form id="payment-form">
            <!-- Payment Amount -->
            <div class="form-group">
                <label for="amount">Amount (USD)</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="amount" placeholder="50.00" value="50.00" required readonly style="flex: 1;">
                    <select id="currency" style="flex: 0.5;">
                        <option value="usd">USD</option>
                        <option value="eur">EUR</option>
                        <option value="gbp">GBP</option>
                    </select>
                </div>
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="customer@example.com" required>
            </div>
            
            <!-- Description -->
            <div class="form-group">
                <label for="description">Order Description</label>
                <input type="text" id="description" placeholder="Order #12345" value="Test Payment">
            </div>
            
            <!-- Card Element (Stripe.js) -->
            <div class="form-group">
                <label>Card Details</label>
                <div id="card-element"></div>
                <div id="card-errors"></div>
            </div>
            
            <!-- Test Card Info -->
            <div class="test-card">
                <strong>🧪 Test Card:</strong>
                <code>4242 4242 4242 4242</code><br>
                Expiry: Any future date<br>
                CVC: Any 3 digits
            </div>
            
            <!-- Submit Button -->
            <button type="submit" id="submit-button">Pay $50.00</button>
            
            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="margin-top: 10px; color: #999;">Processing payment...</p>
            </div>
        </form>
    </div>

    <!-- Stripe.js Library -->
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        // ===================================================================
        // STRIPE.JS IMPLEMENTATION - Payment Intents Flow
        // ===================================================================
        
        // Initialize Stripe with your publishable key
        // This key is safe to expose - it only allows creating payments
        const publishableKey = '<?php include "config.php"; echo getStripePublishableKey(); ?>';
        const stripe = Stripe(publishableKey);
        const elements = stripe.elements();
        
        // Create card element
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');
        
        // Handle card element errors
        cardElement.addEventListener('change', (event) => {
            const errorElement = document.getElementById('card-errors');
            if (event.error) {
                errorElement.textContent = event.error.message;
                errorElement.style.display = 'block';
            } else {
                errorElement.textContent = '';
                errorElement.style.display = 'none';
            }
        });
        
        // Get DOM elements
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const loadingDiv = document.getElementById('loading');
        const errorDiv = document.getElementById('error-message');
        const successDiv = document.getElementById('success-message');
        const amountInput = document.getElementById('amount');
        
        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Disable submit button during processing
            submitButton.disabled = true;
            loadingDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            try {
                // STEP 1: Create Payment Intent on backend
                // This validates amount and creates a Payment Intent with Stripe
                console.log('1. Creating payment intent...');
                
                const amount = parseFloat(amountInput.value) * 100; // Convert to cents
                const currency = document.getElementById('currency').value;
                const email = document.getElementById('email').value;
                const description = document.getElementById('description').value;
                
                const createResponse = await fetch('create_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        amount: Math.round(amount),
                        currency: currency,
                        email: email,
                        description: description
                    })
                });
                
                if (!createResponse.ok) {
                    throw new Error(`HTTP error! status: ${createResponse.status}`);
                }
                
                const createData = await createResponse.json();
                
                if (!createData.success) {
                    throw new Error(createData.error || 'Failed to create payment');
                }
                
                console.log('Created Payment Intent:', createData.payment_intent_id);
                
                // STEP 2: Confirm Payment with Stripe.js
                // User's card details are confirmed with Stripe, not sent to our backend
                console.log('2. Confirming payment with card...');
                
                const confirmResponse = await stripe.confirmCardPayment(
                    createData.client_secret,
                    {
                        payment_method: {
                            card: cardElement,
                            billing_details: {
                                email: email
                            }
                        }
                    }
                );
                
                // STEP 3: Handle Payment Intent result
                if (confirmResponse.error) {
                    // Card was declined or payment failed
                    throw new Error(confirmResponse.error.message);
                }
                
                const { paymentIntent } = confirmResponse;
                console.log('Payment Intent status:', paymentIntent.status);
                
                // STEP 4: Check payment status and redirect
                if (paymentIntent.status === 'succeeded') {
                    // Payment successful - redirect to success page
                    console.log('Payment succeeded!');
                    window.location.href = 'success.php?payment_intent_id=' + paymentIntent.id;
                } else if (paymentIntent.status === 'requires_action') {
                    // Payment needs additional verification (3D Secure)
                    console.log('Payment requires authentication');
                    showError('Additional verification required. Please check your phone or email.');
                } else {
                    throw new Error('Unexpected payment status: ' + paymentIntent.status);
                }
                
            } catch (error) {
                // Handle errors
                console.error('Payment error:', error);
                showError(error.message || 'An error occurred during payment');
                
                // Re-enable submit button for retry
                submitButton.disabled = false;
                loadingDiv.style.display = 'none';
            }
        });
        
        // Helper function to display errors
        function showError(message) {
            errorDiv.textContent = '❌ ' + message;
            errorDiv.style.display = 'block';
        }
        
        // Helper function to display success
        function showSuccess(message) {
            successDiv.textContent = '✓ ' + message;
            successDiv.style.display = 'block';
        }
    </script>
</body>
</html>
