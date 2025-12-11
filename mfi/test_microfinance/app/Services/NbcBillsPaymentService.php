<?php
// app/Services/NbcBillsPaymentService.phpxxx

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NbcBillsPaymentService
{
    protected $baseUrl; 
    protected $username;
    protected $password;
    protected $channelId;
    protected $privateKey;
    protected $publicKey;

    public function __construct()
    {
        // Sandbox configuration (as per original documentation)
        $this->baseUrl = config('nbc.bills_payment.base_url', 'http://22.32.245.87:4433');
        $this->username = config('nbc.bills_payment.username', 'SaccosApp@nbc.co.tz');
        $this->password = config('nbc.bills_payment.password', 'SaccosAbc@123!');
        $this->channelId = config('nbc.bills_payment.channel_id', 'SACCOSAPP');
        $this->channelName = config('nbc.bills_payment.channel_name', 'SACCOSAPPLICATION');

        Log::info('Initializing NBC Bills Payment Service');

        // Load private key - try multiple possible filenames
        $privateKeyContent = null;
        $privateKeyFiles = ['keys/private_key.pem', 'keys/private.pem'];
        
        foreach ($privateKeyFiles as $keyFile) {
            if (Storage::exists($keyFile)) {
                $privateKeyContent = Storage::get($keyFile);
                Log::info('Private key found at: ' . $keyFile);
                break;
            }
        }
        
        if ($privateKeyContent) {
            $this->privateKey = openssl_pkey_get_private($privateKeyContent);
            if (!$this->privateKey) {
                Log::warning('Private key content exists but failed to parse, generating new key pair');
                $this->generateKeyPair();
            } else {
                Log::info('Private key loaded successfully');
            }
        } else {
            Log::warning('No private key found, generating new key pair');
            $this->generateKeyPair();
        }

        // Load or derive public key
        if (!$this->publicKey && $this->privateKey) {
            // Extract public key from private key
            $keyDetails = openssl_pkey_get_details($this->privateKey);
            if ($keyDetails && isset($keyDetails['key'])) {
                $this->publicKey = openssl_pkey_get_public($keyDetails['key']);
                Log::info('Public key derived from private key');
            }
        }
        
        if (!$this->publicKey) {
            Log::warning('Could not load or derive public key');
            // Continue without public key - it's only needed for verification
        }
    }
    
    protected function getBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }
    
    protected function generateKeyPair()
    {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        
        if ($res) {
            // Export private key
            openssl_pkey_export($res, $privateKey);
            $this->privateKey = openssl_pkey_get_private($privateKey);
            
            // Store private key
            Storage::put('keys/private_key.pem', $privateKey);
            
            // Get public key
            $keyDetails = openssl_pkey_get_details($res);
            if ($keyDetails && isset($keyDetails['key'])) {
                $this->publicKey = openssl_pkey_get_public($keyDetails['key']);
                
                // Store public key
                Storage::put('keys/public_key.pem', $keyDetails['key']);
            }
            
            Log::info('New key pair generated and stored');
        } else {
            Log::error('Failed to generate key pair');
            throw new \Exception("Failed to generate RSA key pair");
        }
    }

    protected function generateDigitalSignature(array $payload): string
    {
        try {
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
            Log::debug('Payload to sign: ' . $jsonPayload);

            $signature = '';
            $success = openssl_sign($jsonPayload, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);

            if (!$success) {
                throw new \Exception("Failed to generate digital signature: " . openssl_error_string());
            }

            Log::info('Digital signature generated successfully');
            return base64_encode($signature);

        } catch (\Exception $e) {
            Log::error("Digital Signature Generation Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function verifyDigitalSignature(string $payload, string $signature): bool
    {
        $decodedSignature = base64_decode($signature);
        $result = openssl_verify($payload, $decodedSignature, $this->publicKey, OPENSSL_ALGO_SHA256);
        Log::info('Signature verification result: ' . ($result ? 'valid' : 'invalid'));
        return (bool) $result;
    }

public function getBillers()
{
    try {
        // Cache billers for 8 hours as per NBC documentation
        return Cache::remember('nbc_billers', 28800, function () {
            try {
                $timestamp = now()->format('Y-m-d\TH:i:s.v');
                $channelRef = 'REF' . now()->timestamp;

                $payload = [
                    'channelId' => $this->channelId,
                    'requestType' => 'getServiceProviders',
                    'timestamp' => $timestamp,
                    'channelRef' => $channelRef
                ];

                Log::info('Generating digital signature for biller request');
                $digitalSignature = $this->generateDigitalSignature($payload);

                $headers = [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => $this->getBasicAuthHeader(),
                    'Digital-Signature' => $digitalSignature,
                    'Timestamp' => $timestamp,
                ];

                Log::info('Sending request to NBC API', [
                    'url' => $this->baseUrl . '/bills-payments-engine/api/v1/billers-retrieval',
                    'headers' => $headers,
                    'body' => $payload
                ]);

                $response = Http::withHeaders($headers)
                    ->post("{$this->baseUrl}/bills-payments-engine/api/v1/billers-retrieval", $payload);

                Log::info('Received response', ['status' => $response->status(), 'body' => $response->body()]);

                if ($response->successful()) {
                    $data = $response->json();

                    if ($data['statusCode'] === '600') {
                        $billers = $data['serviceProviders'] ?? [];
                        // Filter only active billers and organize by category
                        $activeBillers = array_filter($billers, fn($biller) => $biller['active'] === true);
                        
                        // Group billers by category for better organization
                        $groupedBillers = [];
                        foreach ($activeBillers as $biller) {
                            $category = $biller['category'] ?? 'other';
                            if (!isset($groupedBillers[$category])) {
                                $groupedBillers[$category] = [];
                            }
                            $groupedBillers[$category][] = $biller;
                        }
                        
                        Log::info('Active billers retrieved', [
                            'total' => count($activeBillers),
                            'categories' => array_keys($groupedBillers)
                        ]);
                        
                        return [
                            'flat' => array_values($activeBillers),
                            'grouped' => $groupedBillers
                        ];
                    }

                    throw new \Exception("API Error: {$data['message']}");
                }

                throw new \Exception("HTTP Error: {$response->status()}");
            } catch (\Exception $e) {
                Log::error("NBC Billers Retrieval Error: " . $e->getMessage());
                return ['flat' => [], 'grouped' => []];
            }
        });
    } catch (\Exception $e) {
        Log::error("NBC Billers Cache Error: " . $e->getMessage());
        return ['flat' => [], 'grouped' => []];
    }
}







public function inquireDetailedBill(array $params): array
{
    try {
        $timestamp = now()->format('Y-m-d\TH:i:s.v');
        $channelRef = 'INQ' . now()->timestamp;

        $payload = [
            'channelId' => $this->channelId,
            'spCode' => $params['spCode'],
            'requestType' => 'inquiry',
            'timestamp' => $timestamp,
            'userId' => $params['userId'] ?? auth()->id() ?? 'USER001',
            'branchCode' => $params['branchCode'] ?? '015',
            'channelRef' => $channelRef,
            'billRef' => $params['billRef'],
            'extraFields' => $params['extraFields'] ?? (object)[],
        ];

        Log::info('Generating digital signature for detailed bill inquiry');
        $digitalSignature = $this->generateDigitalSignature($payload);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->getBasicAuthHeader(),            
            'Digital-Signature' => $digitalSignature,
            'Timestamp' => $timestamp,
        ];

        $url = "{$this->baseUrl}/bills-payments-engine/api/v1/inquiry";

        Log::info('Sending detailed bill inquiry to NBC', [
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload
        ]);

        $response = Http::withHeaders($headers)->post($url, $payload);

        Log::info('NBC inquiry response', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if ($data['statusCode'] === '600') {
                // Store the raw response for payment processing
                $billDetails = $data['billDetails'] ?? [];
                $billDetails['inquiryRawResponse'] = json_encode($data);
                $billDetails['spCode'] = $data['spCode'] ?? $params['spCode'];
                $billDetails['channelRef'] = $channelRef;
                
                return [
                    'success' => true,
                    'data' => $billDetails,
                    'rawResponse' => json_encode($data)
                ];
            }

            return [
                'success' => false,
                'message' => $data['message'] ?? 'Inquiry failed',
                'statusCode' => $data['statusCode'] ?? 'unknown'
            ];
        }

        return [
            'success' => false,
            'message' => "HTTP Error: {$response->status()}"
        ];

    } catch (\Exception $e) {
        Log::error("NBC Detailed Bill Inquiry Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}




public function processPaymentAsync(array $params): array
{
    try {
        $timestamp = now()->format('Y-m-d\TH:i:s.v');
        $channelRef = $params['channelRef'] ?? 'PAY' . now()->timestamp;

        $payload = [
            'channelId' => $this->channelId,
            'spCode'             => $params['spCode'],
            'requestType'        => 'payment',
            'approach'           => 'async',
            'callbackUrl'        => $params['callbackUrl'],
            'timestamp'          => $timestamp,
            'userId'             => $params['userId'],
            'branchCode'         => $params['branchCode'],
            'billRef'            => $params['billRef'],
            'channelRef'         => $channelRef,
            'amount'             => $params['amount'],
            'creditAccount'      => $params['creditAccount'] ?? $params['billDetails']['creditAccount'] ?? '',
            'creditCurrency'     => $params['creditCurrency'] ?? 'TZS',
            'debitAccount'       => $params['debitAccount'],
            'debitCurrency'      => $params['debitCurrency'] ?? 'TZS',
            'paymentType'        => $params['paymentType'] ?? 'ACCOUNT',
            'channelCode'        => $params['channelCode'] ?? 'APP',
            'payerName'          => $params['payerName'],
            'payerPhone'         => $params['payerPhone'],
            'payerEmail'         => $params['payerEmail'],
            'narration'          => $params['narration'] ?? 'Bills Payment',
            'extraFields'        => $params['extraFields'] ?? new \stdClass(),
            'inquiryRawResponse' => $params['inquiryRawResponse'] ?? $params['billDetails']['inquiryRawResponse'] ?? '',
        ];

        Log::info('Generating digital signature for payment request');
        $digitalSignature = $this->generateDigitalSignature($payload);

        $headers = [
            'Content-Type'      => 'application/json',
            'Accept'            => 'application/json',
            'Authorization' => $this->getBasicAuthHeader(), 
            'Digital-Signature' => $digitalSignature,
            'Timestamp'         => $timestamp,
        ];

        $url = "{$this->baseUrl}/bills-payments-engine/api/v1/payment";

        Log::info('Sending async payment request to NBC', [
            'url'     => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders($headers)->post($url, $payload);

        Log::info('NBC async payment response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->successful()) {
            $data = $response->json();

            if ($data['statusCode'] === '600') {
                return [
                    'status'      => 'processing',
                    'gatewayRef'  => $data['gatewayRef'] ?? null,
                    'message'     => $data['message'],
                    'channelRef'  => $channelRef,
                    'timestamp'   => $timestamp,
                ];
            }

            throw new \Exception("API Error: {$data['message']}");
        }

        throw new \Exception("HTTP Error: {$response->status()}");

    } catch (\Exception $e) {
        Log::error("NBC Payment Processing Error: " . $e->getMessage());
        return [
            'status'  => 'error',
            'message' => $e->getMessage(),
        ];
    }
}




public function checkPaymentStatus(array $data): array
{
    // Timestamp format e.g., "2023-03-07T12:29:50.968"
    $timestamp = now()->format('Y-m-d\TH:i:s.v');
$channelRef = '20230512103230499';

    // Prepare request payload
    $payload = [
        'channelId' => 'SACCOSAPP',
        'spCode'      => $data['spCode'] ?? 'BPE0001000BC',
        'requestType' => 'statusCheck',
        'timestamp'   => $timestamp,
        'channelRef'  => $channelRef,
        'billRef'     => $data['billRef'],
        'extraFields' => (object) ($data['extraFields'] ?? []),
    ];

    // Log digital signature generation
    Log::info('Generating digital signature for status check');
  
        $digitalSignature = $this->generateDigitalSignature($payload);

        $headers = [
            'Content-Type'      => 'application/json',
            'Accept'            => 'application/json',
            'Authorization' => $this->getBasicAuthHeader(), 
            'Digital-Signature' => $digitalSignature,
            'Timestamp'         => $timestamp,
        ];

        $url = "{$this->baseUrl}/bills-payments-engine/api/v1/status-check";

        Log::info('Sending ststus to NBC', [
            'url'     => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        



    try {
        $response = Http::withHeaders($headers)->post($url, $payload);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'data'   => $response->json(),
            ];
        } else {
            return [
                'status'  => 'error',
                'code'    => $response->json()['statusCode'] ?? 0,
                'message' => $response->json()['message'] ?? 'Unknown error from API',
            ];
        }
    } catch (\Exception $e) {
        Log::error('NBC Status Check failed', [
            'error'   => $e->getMessage(),
            'payload' => $payload,
        ]);

        return [
            'status'  => 'error',
            'message' => 'Exception: ' . $e->getMessage(),
        ];
    }
}





    public function getPublicKeyPem(): string
    {
        return Storage::get('keys/public_key.pem');
    }
}
