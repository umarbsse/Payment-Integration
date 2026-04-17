<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form - Stripe Integration</title>
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
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        h1 { color: #333; margin-bottom: 10px; font-size: 24px; }
        .subtitle { color: #999; margin-bottom: 30px; font-size: 14px; }
        .info { background: #f0f4ff; border-left: 4px solid #667eea; padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 13px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 14px; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        input:focus, select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        #card-element { padding: 12px; border: 1px solid #ddd; border-radius: 5px; }
        #card-errors { color: #e74c3c; margin-top: 8px; font-size: 14px; min-height: 20px; }
        .test-card {
            background: #fff8dc;
            border-left: 4px solid #f39c12;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            font-size: 13px;
        }
        .test-card code { font-family: monospace; background: #fffacd; padding: 2px 6px; border-radius: 3px; }
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
        button:hover:not(:disabled) { background: #5568d3; transform: translateY(-2px); }
        button:disabled { background: #bbb; cursor: not-allowed; }
        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }
        .alert-error { background: #fadbd8; color: #c0392b; border-left: 4px solid #c0392b; }
        .alert-success { background: #d5f4e6; color: #27ae60; border-left: 4px solid #27ae60; }
        .loading { display: none; text-align: center; color: #999; }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .flex { display: flex; gap: 10px; }
        .flex-1 { flex: 1; }
        .flex-half { flex: 0.5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Complete Payment</h1>
        <p class="subtitle">Using Stripe Payment Intents</p>
        
        <div class="info">📌 This form uses Payment Intents for secure card processing.</div>
        
        <div class="alert alert-error" id="error-msg"></div>
        <div class="alert alert-success" id="success-msg"></div>
        
        <form id="form">
            <div class="form-group">
                <label for="amount">Amount</label>
                <div class="flex">
                    <input type="text" id="amount" value="50.00" readonly class="flex-1">
                    <select id="currency" class="flex-half">
                        <option value="usd">USD</option>
                        <option value="eur">EUR</option>
                        <option value="gbp">GBP</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="you@example.com" required>
            </div>
            
            <div class="form-group">
                <label for="desc">Description</label>
                <input type="text" id="desc" value="Test Payment">
            </div>
            
            <div class="form-group">
                <label>Card Details</label>
                <div id="card-element"></div>
                <div id="card-errors"></div>
            </div>
            
            <div class="test-card">
                <strong>🧪 Test Card:</strong> <code>4242 4242 4242 4242</code><br>
                Expiry: Any future date | CVC: Any 3 digits
            </div>
            
            <button type="submit" id="btn">Pay $50.00</button>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="margin-top: 10px;">Processing...</p>
            </div>
        </form>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const key = '<?php include "config.php"; echo getStripePublishableKey(); ?>';
        const stripe = Stripe(key);
        const elements = stripe.elements();
        const card = elements.create('card');
        
        card.mount('#card-element');
        card.addEventListener('change', (e) => {
            const err = document.getElementById('card-errors');
            err.textContent = e.error ? e.error.message : '';
        });
        
        const form = document.getElementById('form');
        const btn = document.getElementById('btn');
        const loading = document.getElementById('loading');
        const errorMsg = document.getElementById('error-msg');
        const successMsg = document.getElementById('success-msg');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            loading.style.display = 'block';
            errorMsg.style.display = 'none';
            successMsg.style.display = 'none';
            
            try {
                // Create Payment Intent
                const res = await fetch('create_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        amount: Math.round(parseFloat(document.getElementById('amount').value) * 100),
                        currency: document.getElementById('currency').value,
                        email: document.getElementById('email').value,
                        description: document.getElementById('desc').value
                    })
                });
                
                const data = await res.json();
                if (!data.success) throw new Error(data.error);
                
                // Confirm with Stripe
                const confirmRes = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: {
                        card: card,
                        billing_details: { email: document.getElementById('email').value }
                    }
                });
                
                if (confirmRes.error) throw new Error(confirmRes.error.message);
                
                const { paymentIntent } = confirmRes;
                if (paymentIntent.status === 'succeeded') {
                    window.location.href = 'success.php?payment_intent_id=' + paymentIntent.id;
                } else {
                    throw new Error('Payment status: ' + paymentIntent.status);
                }
            } catch (err) {
                errorMsg.textContent = '❌ ' + (err.message || 'Error');
                errorMsg.style.display = 'block';
                btn.disabled = false;
                loading.style.display = 'none';
            }
        });
    </script>
</body>
</html>

