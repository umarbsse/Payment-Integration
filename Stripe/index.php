<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Integration - Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .card-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .card-link:hover {
            background: #5568d3;
        }
        
        .card-link.secondary {
            background: #ecf0f1;
            color: #333;
        }
        
        .card-link.secondary:hover {
            background: #d5dbdb;
        }
        
        .info-card {
            background: #e8f4f8;
            border-left: 4px solid #3498db;
        }
        
        .info-card h2 {
            color: #2c3e50;
        }
        
        .info-card p {
            color: #34495e;
        }
        
        .section-title {
            font-size: 24px;
            color: #333;
            margin: 40px 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .status-configured {
            background: #d5f4e6;
            color: #27ae60;
        }
        
        .status-pending {
            background: #fef5e7;
            color: #d68910;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
        }
        
        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 60px;
        }
        
        .badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin: 2px;
        }
        
        .badge.blue { background: #3498db; }
        .badge.green { background: #27ae60; }
        .badge.orange { background: #f39c12; }
        .badge.red { background: #e74c3c; }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <h1>🔐 Stripe Payment Integration</h1>
        <p>Production-Ready PHP Implementation using cURL</p>
    </header>
    
    <!-- Main Content -->
    <div class="container">
        
        <!-- Quick Start Section -->
        <div class="section-title">🚀 Quick Start</div>
        
        <div class="card info-card">
            <h2>Getting Started</h2>
            <p><strong>1. Configure your Stripe API keys:</strong></p>
            <div class="code-block">
                Edit <strong>config.php</strong> or create <strong>.env</strong> file<br>
                Add your STRIPE_SECRET_KEY and STRIPE_PUBLISHABLE_KEY
            </div>
            <p><strong>2. Read the documentation:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 10px;">
                <li><a href="README.md" style="color: #667eea; text-decoration: none;">README.md</a> - Complete API documentation</li>
                <li><a href="SETUP.md" style="color: #667eea; text-decoration: none;">SETUP.md</a> - Installation & deployment guide</li>
            </ul>
            <p><strong>3. Choose your payment method:</strong></p>
            <p>Use <strong>Payment Intents</strong> (example_form.php) for custom checkout UI<br>
               OR <strong>Checkout Sessions</strong> (example_checkout_form.php) for Stripe-hosted page</p>
        </div>
        
        <!-- Payment Methods Section -->
        <div class="section-title">💳 Payment Methods</div>
        
        <div class="grid">
            <!-- Payment Intents Card -->
            <div class="card">
                <h2>Payment Intents</h2>
                <span class="status status-configured">RECOMMENDED FOR CUSTOM UI</span>
                <p>Full control over checkout experience. Build your own payment form and use Stripe.js to confirm payment.</p>
                <p><strong>Best for:</strong></p>
                <ul style="margin-left: 20px; font-size: 13px; color: #666;">
                    <li>Custom checkout UI</li>
                    <li>Complex payment flows</li>
                    <li>Premium user experience</li>
                </ul>
                <br>
                <a href="example_form.php" class="card-link">🎨 View Example Form</a>
                <a href="#" class="card-link secondary" onclick="alert('See example_form.php source code for implementation'); return false;">📖 View Code</a>
            </div>
            
            <!-- Checkout Sessions Card -->
            <div class="card">
                <h2>Checkout Sessions</h2>
                <span class="status status-pending">QUICK & SIMPLE</span>
                <p>Stripe-hosted payment page. Minimal frontend code needed. Handles everything for you.</p>
                <p><strong>Best for:</strong></p>
                <ul style="margin-left: 20px; font-size: 13px; color: #666;">
                    <li>Simple storefronts</li>
                    <li>Quick implementation</li>
                    <li>Less custom UI needed</li>
                </ul>
                <br>
                <a href="example_checkout_form.php" class="card-link">🛒 View Example Form</a>
                <a href="#" class="card-link secondary" onclick="alert('See example_checkout_form.php source code for implementation'); return false;">📖 View Code</a>
            </div>
        </div>
        
        <!-- Implementation Files Section -->
        <div class="section-title">📁 Project Files</div>
        
        <div class="grid">
            <!-- Core Files -->
            <div class="card">
                <h2>Core Implementation</h2>
                <p><strong>config.php</strong><br>
                <span style="font-size: 12px; color: #999;">Configuration and API credentials</span></p>
                
                <p style="margin-top: 15px;"><strong>stripe_client.php</strong><br>
                <span style="font-size: 12px; color: #999;">Main API client using cURL</span></p>
                
                <p style="margin-top: 15px;"><strong>create_payment.php</strong><br>
                <span style="font-size: 12px; color: #999;">Create Payment Intent endpoint</span></p>
                
                <p style="margin-top: 15px;"><strong>checkout_session.php</strong><br>
                <span style="font-size: 12px; color: #999;">Create Checkout Session endpoint</span></p>
            </div>
            
            <!-- User Pages -->
            <div class="card">
                <h2>User-Facing Pages</h2>
                <p><strong>example_form.php</strong><br>
                <span style="font-size: 12px; color: #999;">Payment Intents checkout form</span></p>
                
                <p style="margin-top: 15px;"><strong>example_checkout_form.php</strong><br>
                <span style="font-size: 12px; color: #999;">Checkout Sessions form</span></p>
                
                <p style="margin-top: 15px;"><strong>success.php</strong><br>
                <span style="font-size: 12px; color: #999;">Payment confirmation page</span></p>
                
                <p style="margin-top: 15px;"><strong>cancel.php</strong><br>
                <span style="font-size: 12px; color: #999;">Payment cancellation/error page</span></p>
            </div>
            
            <!-- Additional Files -->
            <div class="card">
                <h2>Additional Files</h2>
                <p><strong>webhooks.php</strong><br>
                <span style="font-size: 12px; color: #999;">Webhook handler for production</span></p>
                
                <p style="margin-top: 15px;"><strong>test_api.php</strong><br>
                <span style="font-size: 12px; color: #999;">Command-line API testing tool</span></p>
                
                <p style="margin-top: 15px;"><strong>.env.example</strong><br>
                <span style="font-size: 12px; color: #999;">Environment variables template</span></p>
                
                <p style="margin-top: 15px;"><strong>.gitignore</strong><br>
                <span style="font-size: 12px; color: #999;">Prevent committing secrets</span></p>
            </div>
        </div>
        
        <!-- Feature Comparison -->
        <div class="section-title">⚖️ Payment Intents vs Checkout Sessions</div>
        
        <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #667eea; color: white;">
                    <th style="padding: 15px; text-align: left;">Feature</th>
                    <th style="padding: 15px; text-align: center;">Payment Intents</th>
                    <th style="padding: 15px; text-align: center;">Checkout Sessions</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px 15px;">Custom UI</td>
                    <td style="padding: 12px 15px; text-align: center;">✅ Full Control</td>
                    <td style="padding: 12px 15px; text-align: center;">❌ Hosted Page</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px 15px;">Setup Time</td>
                    <td style="padding: 12px 15px; text-align: center;">📊 Medium</td>
                    <td style="padding: 12px 15px; text-align: center;">⚡ Quick</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px 15px;">3D Secure</td>
                    <td style="padding: 12px 15px; text-align: center;">Manual Handling</td>
                    <td style="padding: 12px 15px; text-align: center;">✅ Automatic</td>
                </tr>
                <tr style="border-bottom: 1px solid #e9ecef;">
                    <td style="padding: 12px 15px;">Code Complexity</td>
                    <td style="padding: 12px 15px; text-align: center;">More Code</td>
                    <td style="padding: 12px 15px; text-align: center;">Less Code</td>
                </tr>
                <tr>
                    <td style="padding: 12px 15px;">Best For</td>
                    <td style="padding: 12px 15px; text-align: center;">Complex Flows</td>
                    <td style="padding: 12px 15px; text-align: center;">Simple Payments</td>
                </tr>
            </tbody>
        </table>
        
        <!-- Developer Tools Section -->
        <div class="section-title">🛠️ Developer Tools</div>
        
        <div class="grid">
            <!-- CLI Test Tool -->
            <div class="card">
                <h2>Command Line Test Tool</h2>
                <p>Test the Stripe API directly from your terminal without using web forms.</p>
                <p style="margin-top: 15px; font-size: 13px;"><strong>Available Commands:</strong></p>
                <div class="code-block" style="margin: 10px 0;">
php test_api.php create-intent 5000 usd "Test"<br>
php test_api.php get-intent pi_123<br>
php test_api.php create-session 5000 usd "Product"<br>
php test_api.php test-config
                </div>
                <p style="font-size: 12px; color: #666;">Run: <code>php test_api.php help</code> for all commands</p>
            </div>
            
            <!-- Documentation -->
            <div class="card">
                <h2>Documentation</h2>
                <p><strong>📖 <a href="README.md" style="color: #667eea; text-decoration: none;">README.md</a></strong><br>
                Complete API documentation and examples</p>
                
                <p style="margin-top: 15px;"><strong>⚙️ <a href="SETUP.md" style="color: #667eea; text-decoration: none;">SETUP.md</a></strong><br>
                Installation and deployment guide</p>
                
                <p style="margin-top: 15px;"><strong>🔒 Security</strong><br>
                Never commit API keys. Use environment variables.</p>
            </div>
            
            <!-- Testing -->
            <div class="card">
                <h2>Testing & Environment</h2>
                <p><strong>Test Mode:</strong><br>
                Uses test API keys (sk_test_, pk_test_)<br>
                No real charges</p>
                
                <p style="margin-top: 15px;"><strong>Test Card:</strong><br>
                <code style="background: #f0f0f0; padding: 2px 5px;">4242 4242 4242 4242</code><br>
                Expary: Any future date | CVC: Any 3 digits</p>
                
                <p style="margin-top: 15px;">
                <a href="https://stripe.com/docs/testing" target="_blank" style="color: #667eea; text-decoration: none;">View More Test Cards →</a>
                </p>
            </div>
        </div>
        
        <!-- Security Section -->
        <div class="section-title">🔐 Security & Best Practices</div>
        
        <div class="card info-card">
            <h2>Key Security Points</h2>
            
            <div style="margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 8px;">✓ Never expose Secret Keys</h3>
                <p style="color: #34495e; font-size: 14px;">Secret keys should only exist on your server. Use environment variables, never hardcode.</p>
            </div>
            
            <div style="margin-bottom: 20px;">
                <h3 style="color: #2c3e50; margin-bottom: 8px;">✓ Always validate on server</h3>
                <p style="color: #34495e; font-size: 14px;">Never trust frontend data. Validate amounts, email, and all parameters server-side.</p>
            </div>
            
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
