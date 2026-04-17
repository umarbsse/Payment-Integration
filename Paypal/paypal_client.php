<?php
class PayPalAPI {
    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $accessToken;
    private $tokenExpiry;

    public function __construct() {
        $this->clientId = PAYPAL_CLIENT_ID;
        $this->clientSecret = PAYPAL_CLIENT_SECRET;
        $this->baseUrl = PAYPAL_BASE_URL;
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    public function getToken() {
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $url = $this->baseUrl . '/v1/oauth2/token';
        $credentials = base64_encode($this->clientId . ':' . $this->clientSecret);

        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json'
        ];

        $data = 'grant_type=client_credentials';

        try {
            $response = $this->apiRequest($url, 'POST', $data, $headers);

            if (!isset($response['access_token'])) {
                throw new Exception('Failed to get access token: ' . json_encode($response));
            }

            $this->accessToken = $response['access_token'];
            $this->tokenExpiry = time() + ($response['expires_in'] ?? 32400) - 60;

            return $this->accessToken;

        } catch (Exception $e) {
            throw new Exception('Authentication failed: ' . $e->getMessage());
        }
    }

    public function createOrder($amount, $currency = DEFAULT_CURRENCY) {
        $accessToken = $this->getToken();

        $url = $this->baseUrl . '/v2/checkout/orders';

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'return_url' => RETURN_URL,
                'cancel_url' => CANCEL_URL,
                'brand_name' => 'Your Store Name',
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW'
            ]
        ];

        try {
            $response = $this->apiRequest($url, 'POST', json_encode($orderData), $headers);

            if (!isset($response['id']) || !isset($response['links'])) {
                throw new Exception('Invalid order response: ' . json_encode($response));
            }

            $approvalUrl = null;
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approvalUrl = $link['href'];
                    break;
                }
            }

            if (!$approvalUrl) {
                throw new Exception('Approval URL not found in order response');
            }

            return [
                'order_id' => $response['id'],
                'approval_url' => $approvalUrl,
                'status' => $response['status']
            ];

        } catch (Exception $e) {
            throw new Exception('Order creation failed: ' . $e->getMessage());
        }
    }

    public function captureOrder($orderId) {
        $accessToken = $this->getToken();

        $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture';

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        try {
            $response = $this->apiRequest($url, 'POST', '', $headers);

            if (!isset($response['status']) || $response['status'] !== 'COMPLETED') {
                throw new Exception('Payment capture failed: ' . json_encode($response));
            }

            return $response;

        } catch (Exception $e) {
            throw new Exception('Payment capture failed: ' . $e->getMessage());
        }
    }

    private function apiRequest($url, $method = 'GET', $data = '', $headers = []) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        if ($curlError) {
            throw new Exception('cURL error: ' . $curlError);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception('HTTP error ' . $httpCode . ': ' . $response);
        }

        $decodedResponse = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON decode error: ' . json_last_error_msg());
        }

        return $decodedResponse;
    }
}
?>