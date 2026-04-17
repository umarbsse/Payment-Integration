<?php
/**
 * Stripe API Client
 * Handles Payment Intents and Checkout Sessions with retry logic and caching
 */

class StripeClient {
    private $secretKey;
    private $baseUrl;
    private $debugMode;
    private $logPath;
    private $retryAttempts;

    public function __construct(string $secretKey, string $baseUrl = 'https://api.stripe.com/v1', bool $debug = false, ?string $logPath = null) {
        if (empty($secretKey)) {
            throw new Exception('Stripe Secret Key required');
        }
        
        $this->secretKey = $secretKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->debugMode = $debug;
        $this->logPath = $logPath;
        $this->retryAttempts = STRIPE_RETRY_ATTEMPTS ?? 3;
    }

    /**
     * Create Payment Intent
     * @param int $amount Amount in cents
     * @param string $currency Currency code
     * @param string $description Payment description
     * @param array $metadata Custom metadata
     */
    public function createPaymentIntent(int $amount, string $currency = 'usd', string $description = '', array $metadata = []): array {
        $payload = [
            'amount' => $amount,
            'currency' => strtolower($currency),
            'payment_method_types[]' => 'card',
        ];
        
        if ($description) $payload['description'] = $description;
        if ($metadata) {
            foreach ($metadata as $k => $v) {
                $payload["metadata[$k]"] = $v;
            }
        }
        
        return $this->request('POST', '/payment_intents', $payload);
    }

    /**
     * Confirm Payment Intent with payment method
     */
    public function confirmPaymentIntent(string $piId, string $pmId, string $returnUrl = ''): array {
        $payload = ['payment_method' => $pmId];
        if ($returnUrl) $payload['return_url'] = $returnUrl;
        
        return $this->request('POST', "/payment_intents/$piId/confirm", $payload);
    }

    /**
     * Retrieve Payment Intent details
     */
    public function getPaymentIntent(string $piId): array {
        return $this->request('GET', "/payment_intents/$piId");
    }

    /**
     * Create Checkout Session
     */
    public function createCheckoutSession(int $amount, string $currency = 'usd', string $description = '', string $successUrl = '', string $cancelUrl = '', array $metadata = []): array {
        $payload = [
            'payment_method_types[]' => 'card',
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => strtolower($currency),
            'line_items[0][price_data][unit_amount]' => $amount,
            'line_items[0][price_data][product_data][name]' => $description ?: 'Payment',
            'line_items[0][quantity]' => '1',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ];
        
        if ($metadata) {
            foreach ($metadata as $k => $v) {
                $payload["metadata[$k]"] = $v;
            }
        }
        
        return $this->request('POST', '/checkout/sessions', $payload);
    }

    /**
     * Check if payment succeeded
     */
    public function isPaymentSucceeded(string $piId): bool {
        $response = $this->getPaymentIntent($piId);
        return $response['success'] && ($response['data']['status'] ?? null) === 'succeeded';
    }

    /**
     * Make HTTP request to Stripe API with retry logic
     */
    private function request(string $method, string $endpoint, array $payload = []): array {
        $attempt = 0;
        $lastError = null;
        
        while ($attempt < $this->retryAttempts) {
            try {
                $result = $this->makeRequest($method, $endpoint, $payload);
                
                if ($result['success'] || ($result['http_code'] ?? 0) < 500) {
                    return $result;
                }
                
                // Retry on 5xx errors
                $lastError = $result;
                $attempt++;
                if ($attempt < $this->retryAttempts) {
                    usleep(100000 * $attempt);  // Exponential backoff
                }
            } catch (Exception $e) {
                $lastError = ['error' => $e->getMessage()];
                $attempt++;
            }
        }
        
        return $lastError ?? ['success' => false, 'error' => ['message' => 'Request failed after retries']];
    }

    /**
     * Actual cURL request execution
     */
    private function makeRequest(string $method, string $endpoint, array $payload = []): array {
        $url = $this->baseUrl . $endpoint;
        $response = ['success' => false, 'data' => null, 'error' => null, 'http_code' => 0];
        
        try {
            $ch = curl_init($url);
            if (!$ch) throw new Exception('cURL initialization failed');
            
            $headers = [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/x-www-form-urlencoded',
                'Stripe-Version: ' . STRIPE_API_VERSION,
            ];
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => STRIPE_API_TIMEOUT,
                CURLOPT_HTTPHEADER => $headers,
            ]);
            
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($payload) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            }
            
            $body = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            
            if ($errno) throw new Exception("cURL error: $error");
            
            $data = json_decode($body, true);
            if ($data === null) throw new Exception('Invalid JSON response');
            
            $response['http_code'] = $code;
            
            if ($code >= 400) {
                $response['error'] = $data['error'] ?? ['message' => "HTTP $code"];
                return $response;
            }
            
            $response['success'] = true;
            $response['data'] = $data;
            
            if ($this->debugMode && $this->logPath) {
                $this->log("$method $endpoint ($code)");
            }
        } catch (Exception $e) {
            $response['error'] = ['message' => $e->getMessage()];
            if ($this->debugMode && $this->logPath) {
                $this->log("ERROR: " . $e->getMessage());
            }
        }
        
        return $response;
    }
    
    private function log(string $message): void {
        if (!$this->logPath) return;
        $msg = date('Y-m-d H:i:s') . " - $message\n";
        @file_put_contents($this->logPath, $msg, FILE_APPEND);
    }
}

