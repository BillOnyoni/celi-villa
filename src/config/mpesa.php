<?php
// src/config/mpesa.php
class MpesaAPI {
    private $consumerKey;
    private $consumerSecret;
    private $businessShortCode;
    private $passkey;
    private $environment; // 'sandbox' or 'production'
    
    public function __construct() {
        // Load from environment variables
        $this->consumerKey = $_ENV['MPESA_CONSUMER_KEY'] ?? '';
        $this->consumerSecret = $_ENV['MPESA_CONSUMER_SECRET'] ?? '';
        $this->businessShortCode = $_ENV['MPESA_SHORTCODE'] ?? '';
        $this->passkey = $_ENV['MPESA_PASSKEY'] ?? '';
        $this->environment = $_ENV['MPESA_ENVIRONMENT'] ?? 'sandbox';
        
        // Validate required credentials
        if (empty($this->consumerKey) || empty($this->consumerSecret) || 
            empty($this->businessShortCode) || empty($this->passkey)) {
            throw new Exception("M-Pesa credentials not properly configured. Please check your env.php file.");
        }
    }
    
    private function getBaseUrl() {
        return $this->environment === 'production' 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
    }
    
    public function getAccessToken() {
        $url = $this->getBaseUrl() . '/oauth/v1/generate?grant_type=client_credentials';
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->consumerKey . ':' . $this->consumerSecret)
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Failed to get access token. HTTP Code: $httpCode. Response: $response");
        }
        
        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new Exception("Access token not found in response: $response");
        }
        
        return $data['access_token'];
    }
    
    public function stkPush($phone, $amount, $accountReference, $transactionDesc, $callbackUrl) {
        $accessToken = $this->getAccessToken();
        
        // Format phone number
        $phone = $this->formatPhoneNumber($phone);
        
        // Validate amount
        if ($amount < 1) {
            throw new Exception("Amount must be at least KES 1");
        }
        
        // Generate password
        $timestamp = date('YmdHis');
        $password = base64_encode($this->businessShortCode . $this->passkey . $timestamp);
        
        $url = $this->getBaseUrl() . '/mpesa/stkpush/v1/processrequest';
        
        $payload = [
            'BusinessShortCode' => $this->businessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)$amount,
            'PartyA' => $phone,
            'PartyB' => $this->businessShortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("STK Push failed. HTTP Code: $httpCode. Response: $response");
        }
        
        $data = json_decode($response, true);
        
        if (!isset($data['CheckoutRequestID'])) {
            $errorMessage = $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'Unknown error';
            throw new Exception("STK Push failed: $errorMessage");
        }
        
        return $data;
    }
    
    private function formatPhoneNumber($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Convert to international format
        if (substr($phone, 0, 1) === '0') {
            $phone = '254' . substr($phone, 1);
        } elseif (substr($phone, 0, 3) !== '254') {
            $phone = '254' . $phone;
        }
        
        // Validate Kenyan phone number format
        if (!preg_match('/^254[17]\d{8}$/', $phone)) {
            throw new Exception("Invalid Kenyan phone number format. Use format: 0712345678");
        }
        
        return $phone;
    }
    
    public function queryTransactionStatus($checkoutRequestId) {
        $accessToken = $this->getAccessToken();
        $timestamp = date('YmdHis');
        $password = base64_encode($this->businessShortCode . $this->passkey . $timestamp);
        
        $url = $this->getBaseUrl() . '/mpesa/stkpushquery/v1/query';
        
        $payload = [
            'BusinessShortCode' => $this->businessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);
        
        if ($curlError) {
            throw new Exception("cURL Error: $curlError");
        }
        
        return json_decode($response, true);
    }
}
?>