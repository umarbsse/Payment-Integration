<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Stripe Checkout Session</title>
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
            font-size: 28px;
        }
        
        .subtitle {
            color: #999;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .product-name {
            font-size: 18px;
            color: #333;
            font-weight: 600;
        }
        
        .product-price {
            font-size: 24px;
            color: #667eea;
            font-weight: 700;
        }
        
        .product-description {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
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
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .info-box {
            background: #e8f4f8;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #2c3e50;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 5px;
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
        
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
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
        
        .comparison {
            background: #fef5e7;
            border-left: 4px solid #f39c12;
            padding: 15px;
            border-radius: 5px;
            margin-top: 25px;
            font-size: 12px;
            color: #7d6608;
        }
        
        .comparison strong {
            display: block;
            margin-bottom: 8px;
        }
        
        .comparison ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .comparison li {
            padding: 4px 0;
        }
        
        .comparison li:before {
            content: '✓ ';
            color: #27ae60;
            font-weight: bold;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        <p class="subtitle">Using Stripe Checkout Sessions</p>
        
        <!-- Info about this approach -->
        <div class="info-box">
            📌 This is the <strong>Checkout Sessions</strong> flow - 
            A simplified, hosted payment page managed by Stripe.
        </div>
        
        <!-- Error Message -->
        <div class="error-message" id="error-message"></div>
        
        <!-- Product Information -->
        <div class="product-info">
            <div class="product-header">
                <span class="product-name">Premium Plan</span>
                <span class="product-price">$50.00</span>
            </div>
            <p class="product-description">
                ✓ Full access to our premium features<br>
                ✓ Priority support<br>
                ✓ Advanced analytics<br>
                ✓ Custom integrations
            </p>
        </div>
        
        <!-- Checkout Form -->
        <form id="checkout-form">
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="customer@example.com" required>
            </div>
            
            <!-- IMPORTANT: Hidden form fields -->
            <!-- These define what will be charged when user completes payment -->
            <input type="hidden" name="amount" value="5000"> <!-- Amount in cents: 50.00 USD -->
            <input type="hidden" name="currency" value="usd">
            <input type="hidden" name="description" value="Premium Plan Subscription">
            <input type="hidden" name="order_id" value="">
            
            <!-- Submit Button -->
            <button type="submit" id="submit-button">
                Go to Checkout
            </button>
            
            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="margin-top: 10px; color: #999;">Redirecting to payment...</p>
            </div>
        </form>
        
        <!-- Comparison Info -->
        <div class="comparison">
            <strong>Why Checkout Sessions?</strong>
            <ul>
                <li>Hosted payment page (no custom form needed)</li>
                <li>Automatic 3D Secure handling</li>
                <li>Minimal frontend code required</li>
                <li>Works great for simple storefronts</li>
                <li>Handles all payment method types</li>
            </ul>
        </div>
    </div>

    <script>
        const form = document.getElementById('checkout-form');
        const btn = document.getElementById('submit-button');
        const loading = document.getElementById('loading');
        const err = document.getElementById('error-message');
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            btn.disabled = true;
            loading.style.display = 'block';
            err.style.display = 'none';
            
            try {
                const res = await fetch('checkout_session.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                
                const data = await res.json();
                if (!data.success) throw new Error(data.error);
                
                window.location.href = data.url;
            } catch (error) {
                err.textContent = '❌ ' + (error.message || 'Error');
                err.style.display = 'block';
                btn.disabled = false;
                loading.style.display = 'none';
            }
        });
    </script>
</body>
</html>
