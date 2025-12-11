<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class NbcBillsPaymentUatService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $channelId;
    protected $channelName;
    protected $privateKey;

    public function __construct()
    {
        // UAT configuration
        $this->baseUrl = 'https://nbc-gateway-uat.intra.nbc.co.tz';
        $this->username = 'SaccosApp@nbc.co.tz';
        $this->password = 'SaccosAbc@123!';
        $this->channelId = 'SACCOSNBC';
        $this->channelName = 'TR';

        Log::info('Initializing NBC UAT Service (XML)');
        
        // Load private key
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
                Log::warning('Failed to parse private key');
                $this->generateKeyPair();
            }
        } else {
            Log::warning('No private key found, generating new key pair');
            $this->generateKeyPair();
        }
    }
    
    protected function generateKeyPair()
    {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $res = openssl_pkey_new($config);
        
        if ($res) {
            openssl_pkey_export($res, $privateKey);
            $this->privateKey = openssl_pkey_get_private($privateKey);
            Storage::put('keys/private_key.pem', $privateKey);
            
            $keyDetails = openssl_pkey_get_details($res);
            if ($keyDetails && isset($keyDetails['key'])) {
                Storage::put('keys/public_key.pem', $keyDetails['key']);
            }
            
            Log::info('New key pair generated and stored');
        } else {
            Log::error('Failed to generate key pair');
            throw new \Exception("Failed to generate RSA key pair");
        }
    }

    protected function getBasicAuthHeader(): string
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    protected function generateDigitalSignature(string $data): string
    {
        $signature = '';
        $success = openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA256);
        
        if (!$success) {
            Log::error('Failed to generate digital signature');
            return '';
        }
        
        return base64_encode($signature);
    }

    protected function arrayToXml(array $data, $rootElement = 'request'): string
    {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$rootElement></$rootElement>");
        
        $this->arrayToXmlRecursive($data, $xml);
        
        return $xml->asXML();
    }

    protected function arrayToXmlRecursive(array $data, SimpleXMLElement &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } elseif (is_object($value) && empty((array)$value)) {
                // Empty object - add empty element
                $xml->addChild($key);
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }

    protected function xmlToArray($xml)
    {
        if (is_string($xml)) {
            $xml = simplexml_load_string($xml);
        }
        
        if (!$xml) {
            return null;
        }
        
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    public function getBillers()
    {
        try {
            return Cache::remember('nbc_uat_billers', 28800, function () {
                $timestamp = now()->format('Y-m-d\TH:i:s.v');
                $channelRef = 'REF' . now()->timestamp;

                $payload = [
                    'channelId' => $this->channelId,
                    'requestType' => 'getServiceProviders',
                    'timestamp' => $timestamp,
                    'channelRef' => $channelRef
                ];

                $xmlPayload = $this->arrayToXml($payload);
                Log::info('XML Request for billers: ' . $xmlPayload);

                $digitalSignature = $this->generateDigitalSignature($xmlPayload);

                $headers = [
                    'Accept' => 'application/xml',
                    'Content-Type' => 'application/xml',
                    'NBC-Authorization' => $this->getBasicAuthHeader(),
                    'Digital-Signature' => $digitalSignature,
                    'Timestamp' => $timestamp,
                ];

                $response = Http::withHeaders($headers)
                    ->withOptions(['verify' => false])
                    ->withBody($xmlPayload, 'application/xml')
                    ->post("{$this->baseUrl}/api/nbc-sg/v2/billers-retrieval");

                Log::info('Billers response', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                if ($response->successful()) {
                    $data = $this->xmlToArray($response->body());
                    
                    if ($data && isset($data['statusCode']) && $data['statusCode'] === '600') {
                        $billers = $data['serviceProviders'] ?? [];
                        $activeBillers = array_filter($billers, fn($biller) => 
                            isset($biller['active']) && $biller['active'] === 'true'
                        );
                        
                        return [
                            'flat' => array_values($activeBillers),
                            'grouped' => $this->groupBillersByCategory($activeBillers)
                        ];
                    }
                }

                return ['flat' => [], 'grouped' => []];
            });
        } catch (\Exception $e) {
            Log::error("NBC UAT Billers Error: " . $e->getMessage());
            return ['flat' => [], 'grouped' => []];
        }
    }

    protected function groupBillersByCategory(array $billers): array
    {
        $grouped = [];
        foreach ($billers as $biller) {
            $category = $biller['category'] ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $biller;
        }
        return $grouped;
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
                'extraFields' => $params['extraFields'] ?? [],
            ];

            $xmlPayload = $this->arrayToXml($payload);
            Log::info('XML Bill Inquiry Request', ['payload' => $xmlPayload]);

            $digitalSignature = $this->generateDigitalSignature($xmlPayload);

            $headers = [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
                'NBC-Authorization' => $this->getBasicAuthHeader(),
                'Digital-Signature' => $digitalSignature,
                'Timestamp' => $timestamp,
            ];

            $url = "{$this->baseUrl}/api/nbc-sg/v2/billquery";

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->withBody($xmlPayload, 'application/xml')
                ->post($url);

            Log::info('NBC UAT inquiry response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $this->xmlToArray($response->body());

                if ($data && isset($data['statusCode'])) {
                    if ($data['statusCode'] === '600') {
                        return [
                            'success' => true,
                            'data' => $data['billDetails'] ?? [],
                            'rawResponse' => $response->body(),
                            'channelRef' => $channelRef,
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => $data['message'] ?? 'Inquiry failed',
                            'statusCode' => $data['statusCode'],
                        ];
                    }
                }
            }

            return [
                'success' => false,
                'message' => 'HTTP Error: ' . $response->status(),
                'statusCode' => $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error("NBC UAT Inquiry Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
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
                'spCode' => $params['spCode'],
                'requestType' => 'payment',
                'timestamp' => $timestamp,
                'userId' => $params['userId'] ?? auth()->id() ?? 'USER001',
                'branchCode' => $params['branchCode'] ?? '015',
                'channelRef' => $channelRef,
                'billRef' => $params['billRef'],
                'amount' => $params['amount'],
                'callbackUrl' => $params['callbackUrl'] ?? '',
                'extraFields' => $params['extraFields'] ?? [],
            ];

            // Add payment-specific fields if available
            if (isset($params['debitAccount'])) {
                $payload['debitAccount'] = $params['debitAccount'];
                $payload['debitCurrency'] = $params['debitCurrency'] ?? 'TZS';
            }
            if (isset($params['creditAccount'])) {
                $payload['creditAccount'] = $params['creditAccount'];
                $payload['creditCurrency'] = $params['creditCurrency'] ?? 'TZS';
            }
            if (isset($params['payerName'])) {
                $payload['payerName'] = $params['payerName'];
            }
            if (isset($params['payerPhone'])) {
                $payload['payerPhone'] = $params['payerPhone'];
            }

            $xmlPayload = $this->arrayToXml($payload);
            Log::info('XML Payment Request', ['payload' => $xmlPayload]);

            $digitalSignature = $this->generateDigitalSignature($xmlPayload);

            $headers = [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
                'NBC-Authorization' => $this->getBasicAuthHeader(),
                'Digital-Signature' => $digitalSignature,
                'Timestamp' => $timestamp,
            ];

            $url = "{$this->baseUrl}/api/nbc-sg/v2/bill-pay";

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->withBody($xmlPayload, 'application/xml')
                ->post($url);

            Log::info('NBC UAT payment response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $this->xmlToArray($response->body());

                if ($data && isset($data['statusCode']) && $data['statusCode'] === '600') {
                    return [
                        'status' => 'processing',
                        'gatewayRef' => $data['gatewayRef'] ?? null,
                        'message' => $data['message'] ?? 'Payment processing',
                        'channelRef' => $channelRef,
                        'timestamp' => $timestamp,
                    ];
                }

                return [
                    'status' => 'error',
                    'message' => $data['message'] ?? 'Payment failed',
                    'statusCode' => $data['statusCode'] ?? null,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'HTTP Error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error("NBC UAT Payment Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function checkPaymentStatus(array $params): array
    {
        try {
            $timestamp = now()->format('Y-m-d\TH:i:s.v');
            
            $payload = [
                'channelId' => $this->channelId,
                'spCode' => $params['spCode'],
                'requestType' => 'statusCheck',
                'timestamp' => $timestamp,
                'channelRef' => $params['channelRef'],
                'billRef' => $params['billRef'],
                'extraFields' => $params['extraFields'] ?? [],
            ];

            $xmlPayload = $this->arrayToXml($payload);
            Log::info('XML Status Check Request', ['payload' => $xmlPayload]);

            $digitalSignature = $this->generateDigitalSignature($xmlPayload);

            $headers = [
                'Accept' => 'application/xml',
                'Content-Type' => 'application/xml',
                'NBC-Authorization' => $this->getBasicAuthHeader(),
                'Digital-Signature' => $digitalSignature,
                'Timestamp' => $timestamp,
            ];

            $url = "{$this->baseUrl}/api/nbc-sg/v2/status-check";

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->withBody($xmlPayload, 'application/xml')
                ->post($url);

            if ($response->successful()) {
                $data = $this->xmlToArray($response->body());
                
                return [
                    'status' => 'success',
                    'data' => $data,
                ];
            }

            return [
                'status' => 'error',
                'message' => 'HTTP Error: ' . $response->status(),
            ];

        } catch (\Exception $e) {
            Log::error("NBC UAT Status Check Error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}