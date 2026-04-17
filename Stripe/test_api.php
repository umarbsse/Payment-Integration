<?php
/**
 * Stripe API Test/Playground Tool
 * 
 * This is a command-line tool to test the Stripe API directly.
 * Useful for debugging and understanding how the API works.
 * 
 * USAGE:
 * php test_api.php [action] [parameters]
 * 
 * Examples:
 * php test_api.php create-intent 5000 usd "Test Payment"
 * php test_api.php get-intent pi_1234567890
 * php test_api.php list-intents
 *
 * Run without arguments to see all available commands.
 */

// Include configuration and client
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/stripe_client.php';

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    die("This script must be run from command line.\n");
}

// Get command line arguments
$args = array_slice($argv, 1);

// Helper function to print formatted output
function printResult($title, $data, $success = true) {
    $color = $success ? "\033[32m" : "\033[31m";  // Green or Red
    $reset = "\033[0m";
    
    echo "\n{$color}=== {$title} ==={$reset}\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

// Helper function to print usage
function printUsage() {
    echo "╔════════════════════════════════════════════════════════════════════╗\n";
    echo "║        Stripe API Test Tool - Command Line Interface              ║\n";
    echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
    echo "USAGE:\n";
    echo "  php test_api.php [command] [options]\n\n";
    echo "COMMANDS:\n\n";
    echo "  1. Create Payment Intent\n";
    echo "     php test_api.php create-intent <amount> [currency] [description]\n";
    echo "     Example: php test_api.php create-intent 5000 usd \"Test\"\n\n";
    echo "  2. Get Payment Intent\n";
    echo "     php test_api.php get-intent <payment_intent_id>\n";
    echo "     Example: php test_api.php get-intent pi_1234567890\n\n";
    echo "  3. Confirm Payment Intent\n";
    echo "     php test_api.php confirm-intent <payment_intent_id> <payment_method_id>\n";
    echo "     Example: php test_api.php confirm-intent pi_1234 pm_5678 https://example.com/success\n\n";
    echo "  4. Create Checkout Session\n";
    echo "     php test_api.php create-session <amount> [currency] [description]\n";
    echo "     Example: php test_api.php create-session 5000 usd \"Product Purchase\"\n\n";
    echo "  5. Test Configuration\n";
    echo "     php test_api.php test-config\n\n";
    echo "  6. Show Help\n";
    echo "     php test_api.php help\n\n";
    echo "NOTES:\n";
    echo "  - Amounts are in cents (e.g., 5000 = \$50.00)\n";
    echo "  - Currency defaults to 'usd'\n";
    echo "  - Check logs/stripe_api.log for API details\n";
}

// Verify configuration
if (!isStripeConfigured()) {
    echo "\n\033[31m❌ ERROR: Stripe not configured!\033[0m\n";
    echo "Please update config.php with your API keys.\n\n";
    exit(1);
}

// Parse command
$command = $args[0] ?? null;

switch ($command) {
    
    // ===================================================================
    // CREATE PAYMENT INTENT
    // ===================================================================
    case 'create-intent':
    case 'create-payment-intent':
        $amount = isset($args[1]) ? intval($args[1]) : null;
        $currency = isset($args[2]) ? $args[2] : 'usd';
        $description = isset($args[3]) ? $args[3] : 'Test Payment';
        
        if (empty($amount) || $amount <= 0) {
            die("❌ Please provide a valid amount in cents\n");
        }
        
        echo "Creating Payment Intent...\n";
        echo "  Amount: \$$" . ($amount / 100) . " $currency\n";
        echo "  Description: $description\n";
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $response = $client->createPaymentIntent($amount, $currency, $description);
            
            if ($response['success']) {
                $data = $response['data'];
                printResult(
                    'Payment Intent Created ✓',
                    array(
                        'id' => $data['id'],
                        'status' => $data['status'],
                        'amount' => $data['amount'],
                        'currency' => $data['currency'],
                        'client_secret' => substr($data['client_secret'], 0, 20) . '...',
                        'created' => date('Y-m-d H:i:s', $data['created'])
                    ),
                    true
                );
                
                echo "\n✅ Next steps:\n";
                echo "  1. Use client_secret with Stripe.js to confirm payment\n";
                echo "  2. Run: php test_api.php get-intent {$data['id']}\n";
            } else {
                printResult('Error', $response['error'], false);
            }
        } catch (Exception $e) {
            printResult('Exception', array('error' => $e->getMessage()), false);
        }
        break;
    
    // ===================================================================
    // GET PAYMENT INTENT
    // ===================================================================
    case 'get-intent':
    case 'get-payment-intent':
        $paymentIntentId = $args[1] ?? null;
        
        if (empty($paymentIntentId)) {
            die("❌ Please provide a Payment Intent ID\n");
        }
        
        echo "Retrieving Payment Intent: $paymentIntentId...\n";
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $response = $client->getPaymentIntent($paymentIntentId);
            
            if ($response['success']) {
                $data = $response['data'];
                $output = array(
                    'id' => $data['id'],
                    'status' => $data['status'],
                    'amount' => $data['amount'] . ' ' . strtoupper($data['currency']),
                    'payment_method' => $data['payment_method'] ?? 'none',
                    'created' => date('Y-m-d H:i:s', $data['created'])
                );
                
                if (!empty($data['charges']['data'])) {
                    $charge = $data['charges']['data'][0];
                    $output['charge_id'] = $charge['id'];
                    $output['charge_status'] = $charge['status'];
                }
                
                printResult('Payment Intent Details ✓', $output, true);
            } else {
                printResult('Error', $response['error'], false);
            }
        } catch (Exception $e) {
            printResult('Exception', array('error' => $e->getMessage()), false);
        }
        break;
    
    // ===================================================================
    // CONFIRM PAYMENT INTENT
    // ===================================================================
    case 'confirm-intent':
    case 'confirm-payment-intent':
        $paymentIntentId = $args[1] ?? null;
        $paymentMethodId = $args[2] ?? null;
        $returnUrl = $args[3] ?? '';
        
        if (empty($paymentIntentId) || empty($paymentMethodId)) {
            die("❌ Please provide Payment Intent ID and Payment Method ID\n");
        }
        
        echo "Confirming Payment Intent: $paymentIntentId...\n";
        echo "  With payment method: $paymentMethodId\n";
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $response = $client->confirmPaymentIntent($paymentIntentId, $paymentMethodId, $returnUrl);
            
            if ($response['success']) {
                $data = $response['data'];
                printResult(
                    'Payment Confirmed ✓',
                    array(
                        'id' => $data['id'],
                        'status' => $data['status'],
                        'amount' => $data['amount'],
                        'payment_method' => $data['payment_method']
                    ),
                    true
                );
            } else {
                printResult('Error', $response['error'], false);
            }
        } catch (Exception $e) {
            printResult('Exception', array('error' => $e->getMessage()), false);
        }
        break;
    
    // ===================================================================
    // CREATE CHECKOUT SESSION
    // ===================================================================
    case 'create-session':
    case 'create-checkout-session':
        $amount = isset($args[1]) ? intval($args[1]) : null;
        $currency = isset($args[2]) ? $args[2] : 'usd';
        $description = isset($args[3]) ? $args[3] : 'Test Product';
        $successUrl = APP_URL . '/success.php?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = APP_URL . '/cancel.php';
        
        if (empty($amount) || $amount <= 0) {
            die("❌ Please provide a valid amount in cents\n");
        }
        
        echo "Creating Checkout Session...\n";
        echo "  Amount: \$$" . ($amount / 100) . " $currency\n";
        echo "  Description: $description\n";
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $response = $client->createCheckoutSession(
                $amount,
                $currency,
                $description,
                $successUrl,
                $cancelUrl
            );
            
            if ($response['success']) {
                $data = $response['data'];
                printResult(
                    'Checkout Session Created ✓',
                    array(
                        'session_id' => $data['id'],
                        'url' => $data['url'],
                        'created' => date('Y-m-d H:i:s', $data['created'])
                    ),
                    true
                );
                
                echo "\n✅ Next steps:\n";
                echo "  Redirect customer to: {$data['url']}\n";
            } else {
                printResult('Error', $response['error'], false);
            }
        } catch (Exception $e) {
            printResult('Exception', array('error' => $e->getMessage()), false);
        }
        break;
    
    // ===================================================================
    // TEST CONFIGURATION
    // ===================================================================
    case 'test-config':
    case 'test':
        echo "Testing Stripe Configuration...\n\n";
        
        $checks = array(
            'Secret Key Configured' => !empty(STRIPE_SECRET_KEY) && STRIPE_SECRET_KEY !== 'sk_test_your_secret_key_here',
            'Publishable Key Configured' => !empty(STRIPE_PUBLISHABLE_KEY) && STRIPE_PUBLISHABLE_KEY !== 'pk_test_your_publishable_key_here',
            'cURL Enabled' => extension_loaded('curl'),
            'JSON Extension Enabled' => extension_loaded('json'),
            'Log Directory Writable' => is_writable(dirname(LOG_FILE_PATH)),
            'Using Test Keys' => strpos(STRIPE_SECRET_KEY, 'sk_test_') === 0,
        );
        
        $allPassed = true;
        foreach ($checks as $check => $result) {
            $status = $result ? "\033[32m✓\033[0m" : "\033[31m✕\033[0m";
            echo "  $status $check\n";
            if (!$result) $allPassed = false;
        }
        
        echo "\n";
        if ($allPassed) {
            echo "\033[32m✅ All checks passed! Configuration looks good.\033[0m\n";
            
            echo "\n📝 Test commands to try:\n";
            echo "  php test_api.php create-intent 5000 usd \"Test Order\"\n";
            echo "  php test_api.php create-session 5000 usd \"Test Product\"\n";
            
        } else {
            echo "\033[31m⚠️  Some checks failed. Please review configuration.\033[0m\n";
            echo "See SETUP.md for troubleshooting help.\n";
        }
        
        echo "\n";
        break;
    
    // ===================================================================
    // HELP / USAGE
    // ===================================================================
    case 'help':
    case '--help':
    case '-h':
    case '':
    case null:
        printUsage();
        break;
    
    default:
        echo "\n❌ Unknown command: $command\n";
        echo "Run 'php test_api.php help' for usage information.\n\n";
        exit(1);
}

// End of test tool
?>
