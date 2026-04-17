<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Integration Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f7fa; color: #333; }
        header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        header h1 { font-size: 32px; margin-bottom: 10px; }
        header p { font-size: 16px; opacity: 0.9; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: all 0.3s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.15); }
        .card h2 { color: #667eea; margin-bottom: 10px; font-size: 18px; }
        .card p { color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 15px; }
        .card-link { display: inline-block; background: #667eea; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 600; transition: all 0.3s; font-size: 14px; }
        .card-link:hover { background: #5568d3; }
        .card-link.secondary { background: #ecf0f1; color: #333; }
        .card-link.secondary:hover { background: #d5dbdb; }
        .info-card { background: #e8f4f8; border-left: 4px solid #3498db; }
        .info-card h2 { color: #2c3e50; }
        .info-card p { color: #34495e; }
        .title { font-size: 24px; color: #333; margin: 40px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #667eea; }
        .status { display: inline-block; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: 600; margin-bottom: 10px; }
        .status-configured { background: #d5f4e6; color: #27ae60; }
        .status-pending { background: #fef5e7; color: #d68910; }
        .code { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 5px; padding: 15px; margin: 15px 0; font-family: Monaco, monospace; font-size: 13px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th { background: #667eea; color: white; padding: 15px; text-align: left; }
        td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; }
        footer { background: #2c3e50; color: white; text-align: center; padding: 20px; margin-top: 60px; }
    </style>
</head>
<body>
    <header>
        <h1>🔐 Stripe Payment Integration</h1>
        <p>Production-Ready PHP Implementation</p>
    </header>
    
    <div class="container">
        <div class="title">Getting Started</div>
        
        <div class="card info-card">
            <h2>Configuration</h2>
            <p><strong>1. Set your API keys:</strong></p>
            <div class="code">Edit config.php or .env with STRIPE_SECRET_KEY and STRIPE_PUBLISHABLE_KEY</div>
            <p style="margin-top: 15px;"><strong>2. Choose payment method:</strong></p>
            <p><a href="example_form.php" style="color: #667eea; text-decoration: none;">Payment Intents</a> (custom UI) or <a href="example_checkout_form.php" style="color: #667eea; text-decoration: none;">Checkout Sessions</a> (hosted page)</p>
        </div>
        
        <div class="title">Payment Methods</div>
        <div class="grid">
            <div class="card">
                <h2>💳 Payment Intents</h2>
                <span class="status status-configured">Custom UI</span>
                <p>Full control with Stripe.js integration. Best for complex flows.</p>
                <a href="example_form.php" class="card-link">Open Form</a>
            </div>
            <div class="card">
                <h2>🛒 Checkout Sessions</h2>
                <span class="status status-pending">Hosted Page</span>
                <p>Stripe-managed checkout. Quick setup, minimal code.</p>
                <a href="example_checkout_form.php" class="card-link">Open Form</a>
            </div>
        </div>
        
        <div class="title">File Reference</div>
        <div class="grid">
            <div class="card">
                <h2>Core Files</h2>
                <p><strong>config.php</strong> - Configuration & credentials<br>
                <strong>stripe_client.php</strong> - cURL API client<br>
                <strong>create_payment.php</strong> - Payment Intent endpoint<br>
                <strong>checkout_session.php</strong> - Checkout endpoint</p>
            </div>
            <div class="card">
                <h2>Pages</h2>
                <p><strong>example_form.php</strong> - Payment form demo<br>
                <strong>example_checkout_form.php</strong> - Checkout demo<br>
                <strong>success.php</strong> - Confirmation page<br>
                <strong>cancel.php</strong> - Cancellation page</p>
            </div>
            <div class="card">
                <h2>API & Support</h2>
                <p><strong>webhooks.php</strong> - Webhook handler<br>
                <strong>test_api.php</strong> - CLI testing tool<br>
                <strong>.env</strong> - Environment config<br>
                <strong>README.md</strong> - Full docs</p>
            </div>
        </div>
        
        <div class="title">Comparison</div>
        <table>
            <tr><th>Feature</th><th>Payment Intents</th><th>Checkout Sessions</th></tr>
            <tr><td>Custom UI</td><td>✅ Full Control</td><td>❌ Hosted</td></tr>
            <tr><td>Setup Time</td><td>📊 Medium</td><td>⚡ Quick</td></tr>
            <tr><td>3D Secure</td><td>Manual</td><td>✅ Auto</td></tr>
            <tr><td>Best For</td><td>Complex flows</td><td>Simple payments</td></tr>
        </table>
        
        <div class="title">Tools & Security</div>
        <div class="grid">
            <div class="card">
                <h2>CLI Tool</h2>
                <div class="code">php test_api.php create-intent 5000 usd "Test"<br>php test_api.php test-config</div>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">Run: php test_api.php help</p>
            </div>
            <div class="card">
                <h2>Test Card</h2>
                <p><strong>4242 4242 4242 4242</strong><br>
                Any future expiry<br>
                Any 3-digit CVC<br>
                No real charges</p>
            </div>
            <div class="card info-card">
                <h2>🔒 Security</h2>
                <p>✓ Never hardcode API keys<br>
                ✓ Always validate server-side<br>
                ✓ Use environment variables<br>
                ✓ Verify webhooks</p>
            
            <div style="margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 8px;">✓ Verify with Stripe</h3>
                <p style="color: #34495e; font-size: 14px;">Don't assume payment succeeded from redirect. Query Stripe API to verify.</p>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 8px;">✓ Use Webhooks in production</h3>
                <p style="color: #34495e; font-size: 14px;">Webhooks are more reliable than redirects. Use them for order fulfillment.</p>
            </div>
            
            <div>
                <h3 style="color: #2c3e50; margin-bottom: 8px;">✓ Enable HTTPS</h3>
                <p style="color: #34495e; font-size: 14px;">Always use HTTPS in production. Never send payment data over HTTP.</p>
            </div>
        </div>
        
        <!-- Next Steps -->
        <div class="section-title">📋 Next Steps</div>
        
        <div class="grid">
            <div class="card">
                <h2>1. Configuration</h2>
                <p>Edit <strong>config.php</strong> with your Stripe API keys from the Stripe Dashboard.</p>
                <p style="margin-top: 15px; font-size: 12px; color: #666;">Using test keys? Great for development!</p>
            </div>
            
            <div class="card">
                <h2>2. Choose Payment Method</h2>
                <p>Start with <strong>example_form.php</strong> (Payment Intents) or <strong>example_checkout_form.php</strong> (Checkout).</p>
                <p style="margin-top: 15px; font-size: 12px; color: #666;">Try making a test payment with card 4242 4242 4242 4242.</p>
            </div>
            
            <div class="card">
                <h2>3. Read Documentation</h2>
                <p>Review <strong>README.md</strong> for complete API reference and <strong>SETUP.md</strong> for deployment.</p>
                <p style="margin-top: 15px; font-size: 12px; color: #666;">Understand Payment Intents vs Checkout for your use case.</p>
            </div>
            
            <div class="card">
                <h2>4. Deploy to Production</h2>
                <p>Get live API keys from Stripe Dashboard. Update config with live keys and enable HTTPS.</p>
                <p style="margin-top: 15px; font-size: 12px; color: #666;">Set up webhooks for reliable order fulfillment.</p>
            </div>
        </div>
        
    </div>
    
    <!-- Footer -->
    <footer>
        <p>Stripe Payment Integration v1.0 • Production-Ready PHP Implementation</p>
        <p style="margin-top: 10px; font-size: 12px; opacity: 0.8;">
            <a href="README.md" style="color: #3498db; text-decoration: none;">Documentation</a> • 
            <a href="SETUP.md" style="color: #3498db; text-decoration: none;">Setup Guide</a> • 
            <a href="https://stripe.com/docs" style="color: #3498db; text-decoration: none; " target="_blank">Stripe Docs</a>
        </p>
    </footer>
</body>
</html>
