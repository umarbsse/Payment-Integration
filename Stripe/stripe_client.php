<?php
/**
 * Stripe API Client
 * 
 * This class handles all communication with the Stripe REST API using cURL.
 * It provides methods for:
 * - Creating Payment Intents
 * - Confirming Payment Intents
 * - Retrieving Payment Intent details
 * - Creating Checkout Sessions
 * - Error handling and logging
 * 
 * The class uses Bearer token authentication with the Stripe Secret Key.
 * All amounts are handled in cents (basic currency unit).
 * 
 * SECURITY NOTES:
 * - This class should ONLY be instantiated on the server side
 * - Never pass this object to frontend code
 * - Always validate amounts and parameters server-side
 * - Use HTTPS in production to protect API calls
 */

class StripeClient {
    
    /**
     * Stripe API Secret Key for authentication
     * @var string
     */
    private $secretKey;
    
    /**
     * Base URL for Stripe API endpoints
     * @var string
     */
    private $apiBaseUrl;
    
    /**
     * Request headers including authentication
     * @var array
     */
    private $headers;
    
    /**
     * cURL handle for API requests
     * @var resource
     */
    private $curlHandle;
    
    /**
     * Application name (for API version)
     * @var string
     */
    private $appName;
    
    /**
     * Enable debug logging
     * @var bool
     */
    private $debugMode;
    
    /**
     * Log file path
     * @var string
     */
    private $logFilePath;

    /**
     * Constructor - Initialize Stripe API client
     * 
     * @param string $secretKey Stripe Secret Key for API authentication
     * @param string $apiBaseUrl Base URL for Stripe API (default: https://api.stripe.com/v1)
     * @param bool $debugMode Enable debug logging (optional)
     * @param string $logFilePath Path to log file (optional)
     */
    public function __construct($secretKey, $apiBaseUrl = 'https://api.stripe.com/v1', $debugMode = false, $logFilePath = null) {
        // Validate that a secret key is provided
        if (empty($secretKey)) {
            throw new Exception('Stripe Secret Key is required');
        }
        
        // Initialize instance variables
        $this->secretKey = $secretKey;
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
        $this->debugMode = $debugMode;
        $this->logFilePath = $logFilePath;
        $this->appName = 'StripeClient/1.0';
        
        // Set up common headers for all API requests
        $this->headers = array(
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded',
            'Stripe-Version: 2022-11-15',
            'User-Agent: ' . $this->appName
        );
    }

    /**
     * Create a Stripe Payment Intent
     * 
     * A Payment Intent tracks the customer's payment throughout the payment flow.
     * The client_secret is returned and can be shared with the frontend to complete
     * the payment using Stripe.js or a checkout form.
     * 
     * @param int $amount Payment amount in cents (e.g., 5000 = $50.00)
     * @param string $currency Currency code (e.g., 'usd', 'eur')
     * @param string $description Human-readable description of the payment
     * @param array $metadata Optional key-value pairs to attach to the Payment Intent
     * @param string $statement_descriptor Optional descriptor for bank statement
     * 
     * @return array Response with 'success' boolean and data or error message
     * 
     * @example
     * $client = new StripeClient(STRIPE_SECRET_KEY);
     * $response = $client->createPaymentIntent(5000, 'usd', 'Order #12345');
     * if ($response['success']) {
     *     $clientSecret = $response['data']['client_secret'];
     *     $paymentIntentId = $response['data']['id'];
     * }
     */
    public function createPaymentIntent($amount, $currency = 'usd', $description = '', $metadata = array(), $statement_descriptor = '') {
        // Prepare the request payload
        $payload = array(
            'amount' => intval($amount),  // Stripe requires amount in cents
            'currency' => strtolower($currency),
            'payment_method_types[]' => 'card'  // Arrays use brackets in form encoding
        );
        
        // Add optional parameters if provided
        if (!empty($description)) {
            $payload['description'] = $description;
        }
        
        if (!empty($statement_descriptor)) {
            $payload['statement_descriptor'] = substr($statement_descriptor, 0, 22);  // Max 22 chars
        }
        
        // Add metadata if provided
        if (!empty($metadata) && is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $payload['metadata[' . $key . ']'] = $value;
            }
        }
        
        // Make the API request
        return $this->makeRequest('POST', '/payment_intents', $payload);
    }

    /**
     * Confirm a Stripe Payment Intent
     * 
     * After the customer enters their payment details, the Payment Intent must be
     * confirmed. This can be done with a payment method ID obtained from Stripe.js
     * or by passing a source token.
     * 
     * @param string $paymentIntentId The ID of the Payment Intent to confirm
     * @param string $paymentMethodId The ID of the payment method to use
     * @param string $returnUrl URL to redirect to after payment confirmation
     * 
     * @return array Response with 'success' boolean and data or error message
     * 
     * @example
     * $response = $client->confirmPaymentIntent('pi_123abc', 'pm_456def', 'https://example.com/success');
     */
    public function confirmPaymentIntent($paymentIntentId, $paymentMethodId, $returnUrl = '') {
        // Prepare the confirmation payload
        $payload = array(
            'payment_method' => $paymentMethodId
        );
        
        // Add return URL if provided (required for redirect-based flows)
        if (!empty($returnUrl)) {
            $payload['return_url'] = $returnUrl;
        }
        
        // Make the API request to confirm the payment intent
        return $this->makeRequest('POST', '/payment_intents/' . $paymentIntentId . '/confirm', $payload);
    }

    /**
     * Retrieve a Stripe Payment Intent
     * 
     * Fetch the current status and details of a Payment Intent.
     * Useful for confirming payment status, checking charge changes, etc.
     * 
     * @param string $paymentIntentId The ID of the Payment Intent to retrieve
     * 
     * @return array Response with 'success' boolean and Payment Intent data
     * 
     * @example
     * $response = $client->getPaymentIntent('pi_123abc');
     * if ($response['success']) {
     *     $status = $response['data']['status']; // 'succeeded', 'processing', 'requires_action', etc.
     * }
     */
    public function getPaymentIntent($paymentIntentId) {
        // Make a GET request for the Payment Intent
        return $this->makeRequest('GET', '/payment_intents/' . $paymentIntentId);
    }

    /**
     * Create a Stripe Checkout Session
     * 
     * Checkout Sessions are a simpler way to handle payments. They provide an
     * all-in-one hosted payment page instead of building a custom checkout form.
     * 
     * @param int $amount Amount in cents
     * @param string $currency Currency code
     * @param string $description Payment description
     * @param string $successUrl URL to redirect to after successful payment
     * @param string $cancelUrl URL to redirect to if payment is canceled
     * @param array $metadata Optional metadata
     * 
     * @return array Response with 'success' boolean and session details
     * 
     * @example
     * $response = $client->createCheckoutSession(
     *     5000, 
     *     'usd', 
     *     'Order #123',
     *     'https://example.com/success',
     *     'https://example.com/cancel'
     * );
     */
    public function createCheckoutSession($amount, $currency = 'usd', $description = '', $successUrl = '', $cancelUrl = '', $metadata = array()) {
        // Prepare the checkout session payload
        $payload = array(
            'payment_method_types[]' => 'card',
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => intval($amount),
            'line_items[0][price_data][product_data][name]' => !empty($description) ? $description : 'Payment',
            'line_items[0][quantity]' => '1',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl
        );
        
        // Add metadata if provided
        if (!empty($metadata) && is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $payload['metadata[' . $key . ']'] = $value;
            }
        }
        
        // Make the API request
        return $this->makeRequest('POST', '/checkout/sessions', $payload);
    }

    /**
     * Make a cURL request to the Stripe API
     * 
     * This is the core method that handles all HTTP communication with Stripe.
     * It handles:
     * - Request preparation (method, headers, payload)
     * - Error handling (HTTP errors, connection errors)
     * - Response parsing (JSON decoding)
     * - Debug logging
     * 
     * @param string $method HTTP method ('GET', 'POST', 'DELETE')
     * @param string $endpoint API endpoint (e.g., '/payment_intents')
     * @param array $payload Request payload (optional, for POST/DELETE)
     * 
     * @return array Formatted response with 'success' and 'data' keys
     * @private
     */
    private function makeRequest($method, $endpoint, $payload = array()) {
        // Initialize the response array
        $response = array(
            'success' => false,
            'data' => null,
            'error' => null,
            'http_code' => null
        );
        
        try {
            // Build the full API URL
            $url = $this->apiBaseUrl . $endpoint;
            
            // Initialize cURL session
            $curl = curl_init();
            
            if ($curl === false) {
                throw new Exception('Failed to initialize cURL');
            }
            
            // Set cURL options based on HTTP method
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);  // Always verify SSL in production
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);     // Verify SSL certificate properly
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);           // 30 second timeout
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
            
            // Set HTTP method and payload
            if ($method === 'GET') {
                // For GET requests, no special setup needed
            } elseif ($method === 'POST') {
                curl_setopt($curl, CURLOPT_POST, true);
                // Encode payload as application/x-www-form-urlencoded
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
            } elseif ($method === 'DELETE') {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($payload)) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
                }
            }
            
            // Execute the request
            $responseBody = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrorNo = curl_errno($curl);
            
            // Close the cURL session
            curl_close($curl);
            
            // Handle cURL errors
            if ($curlErrorNo !== 0) {
                throw new Exception('cURL Error (' . $curlErrorNo . '): ' . $curlError);
            }
            
            // Log the request and response if debug mode is enabled
            if ($this->debugMode) {
                $this->logDebug($method, $endpoint, $payload, $responseBody, $httpCode);
            }
            
            // Decode the JSON response
            $decodedResponse = json_decode($responseBody, true);
            
            // Check if JSON decoding was successful
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from Stripe API: ' . json_last_error_msg());
            }
            
            // Store the HTTP status code
            $response['http_code'] = $httpCode;
            
            // Handle Stripe API errors (4xx and 5xx responses)
            if ($httpCode >= 400) {
                // Stripe returns error details in the response
                if (isset($decodedResponse['error'])) {
                    $error = $decodedResponse['error'];
                    $response['error'] = array(
                        'type' => isset($error['type']) ? $error['type'] : 'unknown_error',
                        'message' => isset($error['message']) ? $error['message'] : 'An unknown error occurred',
                        'param' => isset($error['param']) ? $error['param'] : null,
                        'code' => isset($error['code']) ? $error['code'] : null
                    );
                } else {
                    $response['error'] = array(
                        'type' => 'unknown_error',
                        'message' => 'HTTP ' . $httpCode . ' error (check API response)'
                    );
                }
                return $response;
            }
            
            // Success! Store the response data
            $response['success'] = true;
            $response['data'] = $decodedResponse;
            
        } catch (Exception $e) {
            // Catch any exceptions and format as error response
            $response['error'] = array(
                'type' => 'client_error',
                'message' => $e->getMessage()
            );
            
            // Log the exception if debug mode is enabled
            if ($this->debugMode && $this->logFilePath) {
                error_log('Exception in StripeClient: ' . $e->getMessage() . "\n", 3, $this->logFilePath);
            }
        }
        
        return $response;
    }

    /**
     * Log debug information about API requests and responses
     * 
     * Useful for troubleshooting and monitoring API interactions in development.
     * For production, consider using a more sophisticated logging system.
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $payload Request payload
     * @param string $response API response body
     * @param int $httpCode HTTP response code
     * @private
     */
    private function logDebug($method, $endpoint, $payload, $response, $httpCode) {
        // Only log if log file path is set
        if (empty($this->logFilePath)) {
            return;
        }
        
        // Format log entry
        $logEntry = "\n" . str_repeat('=', 80) . "\n";
        $logEntry .= date('Y-m-d H:i:s') . " - $method $endpoint (HTTP $httpCode)\n";
        $logEntry .= "Request Payload:\n";
        $logEntry .= json_encode($payload, JSON_PRETTY_PRINT) . "\n";
        $logEntry .= "Response:\n";
        $logEntry .= $response . "\n";
        
        // Write to log file
        file_put_contents($this->logFilePath, $logEntry, FILE_APPEND);
    }

    /**
     * Check if a Payment Intent has been successfully paid
     * 
     * Convenience method to check payment status without manually checking status string.
     * 
     * @param string $paymentIntentId The Payment Intent ID
     * @return bool True if the payment has succeeded, false otherwise
     */
    public function isPaymentSucceeded($paymentIntentId) {
        $response = $this->getPaymentIntent($paymentIntentId);
        
        if (!$response['success']) {
            return false;
        }
        
        return isset($response['data']['status']) && $response['data']['status'] === 'succeeded';
    }

}

?>
