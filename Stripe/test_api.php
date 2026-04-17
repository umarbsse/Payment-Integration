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

function printResult($title, $data, $ok = true) {
    $color = $ok ? "\033[32m" : "\033[31m";
    $reset = "\033[0m";
    echo "\n{$color}=== {$title} ==={$reset}\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
}

function printUsage() {
    echo "\n╔════════════════════════════════════╗\n";
    echo "║  Stripe API Test Tool              ║\n";
    echo "╚════════════════════════════════════╝\n\n";
    echo "USAGE:\n  php test_api.php [command] [options]\n\n";
    echo "COMMANDS:\n";
    echo "  create-intent <amount> [currency] [desc]\n";
    echo "  get-intent <payment_intent_id>\n";
    echo "  confirm-intent <id> <method> [url]\n";
    echo "  create-session <amount> [currency] [desc]\n";
    echo "  test-config\n";
    echo "  help\n\n";
    echo "EXAMPLES:\n";
    echo "  php test_api.php create-intent 5000 usd \"Test\"\n";
    echo "  php test_api.php test-config\n\n";
    echo "Amounts in cents: 5000 = \$50.00\n";
    echo "Logs: " . LOG_FILE_PATH . "\n";
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
    
    case 'get-intent':
    case 'get-payment-intent':
        $piId = $args[1] ?? null;
        if (!$piId) die("❌ Provide Payment Intent ID\n");
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $res = $client->getPaymentIntent($piId);
            
            if ($res['success']) {
                $data = $res['data'];
                $out = array(
                    'id' => $data['id'],
                    'status' => $data['status'],
                    'amount' => number_format($data['amount']/100, 2),
                    'currency' => strtoupper($data['currency'])
                );
                printResult('Payment Intent Details', $out);
            } else {
                printResult('Error', $res['error'], false);
            }
        } catch (Exception $e) {
            printResult('Error', ['msg' => $e->getMessage()], false);
        }
        break;
    
    case 'confirm-intent':
    case 'confirm-payment-intent':
        $piId = $args[1] ?? null;
        $pmId = $args[2] ?? null;
        $url = $args[3] ?? '';
        
        if (!$piId || !$pmId) die("❌ Provide Payment Intent ID and Method ID\n");
        
        try {
            $client = new StripeClient(STRIPE_SECRET_KEY, STRIPE_API_BASE_URL, DEBUG_MODE, LOG_FILE_PATH);
            $res = $client->confirmPaymentIntent($piId, $pmId, $url);
            
            if ($res['success']) {
                $d = $res['data'];
                printResult('Payment Confirmed', ['id' => $d['id'], 'status' => $d['status']]);
            } else {
                printResult('Error', $res['error'], false);
            }
        } catch (Exception $e) {
            printResult('Error', ['msg' => $e->getMessage()], false);
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
    
    case 'test-config':
    case 'test':
        $ok = true;
        $checks = [
            'Secret Key' => !empty(STRIPE_SECRET_KEY) && strpos(STRIPE_SECRET_KEY, 'sk_test_') === 0,
            'Publishable Key' => !empty(STRIPE_PUBLISHABLE_KEY) && strpos(STRIPE_PUBLISHABLE_KEY, 'pk_test_') === 0,
            'cURL' => extension_loaded('curl'),
            'JSON' => extension_loaded('json'),
        ];
        
        echo "\n";
        foreach ($checks as $name => $pass) {
            $s = $pass ? "\033[32m✓\033[0m" : "\033[31m✕\033[0m";
            echo "  $s $name\n";
            if (!$pass) $ok = false;
        }
        
        echo "\n";
        if ($ok) {
            echo "\033[32m✅ All checks passed!\033[0m\n\n";
            echo "Try: php test_api.php create-intent 5000 usd \"Test\"\n";
        } else {
            echo "\033[31m❌ Some checks failed\033[0m\n";
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
