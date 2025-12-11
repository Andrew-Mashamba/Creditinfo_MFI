<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LukuGatewayService
{
    protected $baseUrl;
    protected $channelId;
    protected $channelName;
    protected $apiToken;
    protected $verifySSL;

    public function __construct()
    {
        $this->baseUrl = config('services.luku_gateway.base_url');
        $this->channelId = config('services.luku_gateway.channel_id');
        $this->channelName = config('services.luku_gateway.channel_name');
        $this->apiToken = config('services.luku_gateway.api_token');
        $this->verifySSL = config('services.luku_gateway.ssl.verify', true);
    }

    /**
     * Create HTTP client with proper SSL configuration
     *
     * @return \Illuminate\Http\Client\PendingRequest
     * @throws \RuntimeException if SSL configuration is invalid
     */
    protected function createHttpClient()
    {
        $client = Http::withToken($this->apiToken)
            ->withHeaders([
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml'
            ]);

        if ($this->verifySSL) {
            try {
                $sslConfig = config('services.luku_gateway.ssl');
                
                // Verify key files exist
                if (!file_exists($sslConfig['cert_path'])) {
                    throw new \RuntimeException("Public key file not found at: {$sslConfig['cert_path']}");
                }
                if (!file_exists($sslConfig['key_path'])) {
                    throw new \RuntimeException("Private key file not found at: {$sslConfig['key_path']}");
                }
                if (!file_exists($sslConfig['ca_path'])) {
                    throw new \RuntimeException("CA certificate file not found at: {$sslConfig['ca_path']}");
                }

                // Verify key files are readable
                if (!is_readable($sslConfig['cert_path'])) {
                    throw new \RuntimeException("Public key file is not readable: {$sslConfig['cert_path']}");
                }
                if (!is_readable($sslConfig['key_path'])) {
                    throw new \RuntimeException("Private key file is not readable: {$sslConfig['key_path']}");
                }
                if (!is_readable($sslConfig['ca_path'])) {
                    throw new \RuntimeException("CA certificate file is not readable: {$sslConfig['ca_path']}");
                }

                Log::channel('luku')->info('Luku Gateway: Using SSL certificates', [
                    'cert_path' => $sslConfig['cert_path'],
                    'key_path' => $sslConfig['key_path'],
                    'ca_path' => $sslConfig['ca_path']
                ]);

                // Configure SSL with PEM format keys
                $client->withCertificate($sslConfig['cert_path'], $sslConfig['key_path'])
                      ->withCaCertificate($sslConfig['ca_path']);

            } catch (\Exception $e) {
                Log::channel('luku')->error('Luku Gateway: SSL configuration error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \RuntimeException("Failed to configure SSL: " . $e->getMessage());
            }
        } else {
            Log::channel('luku')->warning('Luku Gateway: SSL verification disabled', [
                'environment' => config('app.env')
            ]);
            $client->withoutVerifying();
        }

        return $client;
    }

    /**
     * Make an HTTP request with proper error handling
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     * @throws \Exception
     */
    protected function makeRequest(string $method, string $url, array $data = [])
    {
        try {
            $client = $this->createHttpClient();
            
            // Log the full request details
            Log::channel('luku')->info('Luku Gateway: Making API request', [
                'method' => $method,
                'url' => $url,
                'data' => $data,
                'headers' => [
                    'Content-Type' => 'application/xml',
                    'Accept' => 'application/xml',
                    'NBC-Authorization' => 'Bearer ' . substr($this->apiToken, 0, 10) . '...' // Log partial token for security
                ]
            ]);

            $response = $client->$method($url, $data);

            // Log the full response details
            Log::channel('luku')->info('Luku Gateway: Received API response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                $errorMessage = "Request failed with status: " . $response->status();
                $responseBody = $response->body();
                
                // Try to parse error message from XML response
                try {
                    $errorData = $this->xmlToArray($responseBody);
                    if (isset($errorData['GepgGateway']['sgGepgCustomerInfoRes']['RespHdr']['StsDesc'])) {
                        $errorMessage = $errorData['GepgGateway']['sgGepgCustomerInfoRes']['RespHdr']['StsDesc'];
                    } elseif (isset($errorData['GepgGateway']['GepgGatewayPymtACK']['GepgGatewayPmtSubReqAck']['PayStsDesc'])) {
                        $errorMessage = $errorData['GepgGateway']['GepgGatewayPymtACK']['GepgGatewayPmtSubReqAck']['PayStsDesc'];
                    }
                } catch (\Exception $e) {
                    // If XML parsing fails, use the raw response body
                    $errorMessage = $responseBody;
                }

                // Log specific error details
                Log::channel('luku')->error('Luku Gateway: Request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'error_message' => $errorMessage,
                    'request_data' => $data,
                    'response_body' => $responseBody
                ]);

                // Handle specific error cases
                switch ($response->status()) {
                    case 400:
                        throw new \Exception("Bad Request: " . $errorMessage . " - Please check your request format and data");
                    case 401:
                        throw new \Exception("Unauthorized: " . $errorMessage . " - Please check your API credentials");
                    case 403:
                        throw new \Exception("Forbidden: " . $errorMessage . " - You don't have permission to access this resource");
                    case 404:
                        throw new \Exception("Not Found: " . $errorMessage . " - The requested resource was not found");
                    case 500:
                        throw new \Exception("Server Error: " . $errorMessage . " - Please try again later");
                    default:
                        throw new \Exception($errorMessage);
                }
            }

            return $response;
        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Gateway: Request error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate signature for XML payload
     *
     * @param string $xmlPayload
     * @return string
     */
    protected function generateSignature(string $xmlPayload): string
    {
        // Remove XML declaration and whitespace
        $cleanXml = preg_replace('/<\?xml[^>]+\?>/', '', $xmlPayload);
        $cleanXml = preg_replace('/\s+/', '', $cleanXml);
        
        // Generate HMAC SHA256 signature
        return base64_encode(hash_hmac('sha256', $cleanXml, $this->apiToken, true));
    }

    public function meterLookup(string $meterNumber, string $debitAccountNo, string $channelRef): array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/nbc-sg/v2/customerInfo';

        Log::channel('luku')->info('Luku Gateway: Starting meter lookup request', [
            'meterNumber' => $meterNumber,
            'debitAccountNo' => $debitAccountNo,
            'channelRef' => $channelRef,
            'url' => $url,
        ]);

        // Create XML with proper formatting
        $xmlBody = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
  <GepgGatewayBillQryReq>
    <GepgGatewayHdr>
      <ChannelID>SACCOSNBC</ChannelID>
      <ChannelName>TR</ChannelName>
      <Service>LUKU_INQ</Service>
    </GepgGatewayHdr>
    <gepgBillQryReq>
      <ChannelRef>{$channelRef}</ChannelRef>
      <CustCtrNum>{$meterNumber}</CustCtrNum>
      <DebitAccountNo>{$debitAccountNo}</DebitAccountNo>
      <DebitAccountCurrency>TZS</DebitAccountCurrency>
    </gepgBillQryReq>
  </GepgGatewayBillQryReq>
</GepgGateway>
XML;

        // Remove any potential hidden characters
        $xmlBody = trim(preg_replace('/\s+/', ' ', $xmlBody));

        Log::channel('luku')->info('Luku Gateway: Prepared XML payload', [
            'xml_length' => strlen($xmlBody),
            'xml_hex' => bin2hex($xmlBody),
            'xml_payload' => $xmlBody
        ]);

        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $xmlBody,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                    'Accept: application/xml',
                    'NBC-Authorization: Basic ' . $this->apiToken,
                    'ChannelID: ' . $this->channelId,
                    'ChannelName: ' . $this->channelName
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
            ]);

            $responseBody = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($responseBody === false) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }
            
            curl_close($ch);

            Log::channel('luku')->info('Luku Gateway: API response received', [
                'status' => $statusCode,
                'response_length' => strlen($responseBody),
                'response_raw' => $responseBody
            ]);

            // if ($statusCode !== 200) {
            //     throw new \Exception("API returned status code: {$statusCode}");
            // }

            $responseData = $this->xmlToArray($responseBody);

            // Enhanced error handling
            // if (isset($responseData['GepgGateway']['sgGepgCustomerInfoRes']['RespHdr']['StsCode'])) {
            //     $apiStatusCode = $responseData['GepgGateway']['sgGepgCustomerInfoRes']['RespHdr']['StsCode'];
            //     $statusDesc = $responseData['GepgGateway']['sgGepgCustomerInfoRes']['RespHdr']['StsDesc'] ?? 'Unknown error';

            //     switch ($apiStatusCode) {
            //         case '7101':
            //             return $responseData;
            //         case '7203':
            //             throw new \Exception('Service temporarily unavailable: ' . $statusDesc);
            //         case '7204':
            //             throw new \Exception('Invalid meter number: ' . $statusDesc);
            //         case '7205':
            //             throw new \Exception('Invalid account number: ' . $statusDesc);
            //         default:
            //             throw new \Exception('API error: ' . $statusDesc . ' (Code: ' . $apiStatusCode . ')');
            //     }
            // }

            return $responseData;
        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Gateway: Meter lookup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'meter_number' => $meterNumber,
                'debit_account' => $debitAccountNo,
                'channel_ref' => $channelRef
            ]);
            throw new \Exception('Failed to process meter lookup: ' . $e->getMessage());
        }
    }

    /**
     * Convert array to XML with specified root element
     *
     * @param array $array
     * @param string $rootElement
     * @return string
     */
    protected function arrayToXml(array $array, string $rootElement = 'root'): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootElement . '/>');
        $this->arrayToXmlRecursive($array[$rootElement], $xml);
        return $xml->asXML();
    }

    /**
     * Recursively convert array to XML
     *
     * @param array $array
     * @param \SimpleXMLElement $xml
     */
    protected function arrayToXmlRecursive(array $array, \SimpleXMLElement &$xml)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->arrayToXmlRecursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * Convert XML to array
     *
     * @param string $xml
     * @return array
     */
    protected function xmlToArray(string $xml)
    {
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        return json_decode($json, true);
    }

    /**
     * Process Luku payment
     *
     * @param array $paymentData
     * @return array
     */
    public function processPayment(array $paymentData): array
    {
        $url = rtrim($this->baseUrl, '/') . '/api/nbc-sg/v2/payment';

        Log::channel('luku')->info('Luku Gateway: Starting payment processing', [
            'paymentData' => $paymentData,
            'url' => $url
        ]);

        // Prepare optional fields
        $customerTin = isset($paymentData['customer_tin']) ? $paymentData['customer_tin'] : '';
        $customerNin = isset($paymentData['customer_nin']) ? $paymentData['customer_nin'] : '';
        $customerEmail = isset($paymentData['customer_email']) ? $paymentData['customer_email'] : '';

        // Create XML with proper formatting
        $xmlBody = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
  <GepgGatewayVendReq>
    <GepgGatewayHdr>
      <ChannelID>SACCOSNBC</ChannelID>
      <ChannelName>TR</ChannelName>
      <Service>LUKU_PAY</Service>
    </GepgGatewayHdr>
    <PmtHdr>
      <ChannelRef>{$paymentData['channel_ref']}</ChannelRef>
      <CbpGwRef>{$paymentData['cbp_gw_ref']}</CbpGwRef>
      <StsCode>7101</StsCode>
      <ResultUrl>{$paymentData['result_url']}</ResultUrl>
    </PmtHdr>
    <gepgVendReqInf>
      <ChannelTrxId>{$paymentData['channel_trx_id']}</ChannelTrxId>
      <CustCtrNum>{$paymentData['meter_number']}</CustCtrNum>
      <DebitAccountNo>{$paymentData['debit_account_no']}</DebitAccountNo>
      <DebitAccountCurrency>TZS</DebitAccountCurrency>
      <Amount>{$paymentData['amount']}</Amount>
      <CreditAccountNo>{$paymentData['credit_account_no']}</CreditAccountNo>
      <TrxDtTm>{$paymentData['transaction_datetime']}</TrxDtTm>
      <UsdPayChnl>{$paymentData['payment_channel']}</UsdPayChnl>
      <ThirdParty>{$paymentData['third_party']}</ThirdParty>
      <CustomerMsisdn>{$paymentData['customer_msisdn']}</CustomerMsisdn>
      <CutomerName>{$paymentData['customer_name']}</CutomerName>
      <CustomerTIN>{$customerTin}</CustomerTIN>
      <CustomerNIN>{$customerNin}</CustomerNIN>
      <CustomerEmail>{$customerEmail}</CustomerEmail>
    </gepgVendReqInf>
  </GepgGatewayVendReq>
</GepgGateway>
XML;

        // Remove any potential hidden characters
        $xmlBody = trim(preg_replace('/\s+/', ' ', $xmlBody));

        Log::channel('luku')->info('Luku Gateway: Prepared XML payload', [
            'xml_length' => strlen($xmlBody),
            'xml_hex' => bin2hex($xmlBody),
            'xml_payload' => $xmlBody
        ]);

        try {
            $ch = curl_init();
            
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $xmlBody,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/xml',
                    'Accept: application/xml',
                    'NBC-Authorization: Basic ' . $this->apiToken,
                    'ChannelID: ' . $this->channelId,
                    'ChannelName: ' . $this->channelName
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1
            ]);

            $responseBody = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($responseBody === false) {
                throw new \Exception('cURL Error: ' . curl_error($ch));
            }
            
            curl_close($ch);

            Log::channel('luku')->info('Luku Gateway: API response received', [
                'status' => $statusCode,
                'response_length' => strlen($responseBody),
                'response_raw' => $responseBody
            ]);

            $responseData = $this->xmlToArray($responseBody);

            // Handle response status codes
            if (isset($responseData['GepgGateway']['GepgGatewayPymtACK']['GepgGatewayPmtSubReqAck']['PayStsCode'])) {
                $statusCode = $responseData['GepgGateway']['GepgGatewayPymtACK']['GepgGatewayPmtSubReqAck']['PayStsCode'];
                $statusDesc = $responseData['GepgGateway']['GepgGatewayPymtACK']['GepgGatewayPmtSubReqAck']['PayStsDesc'] ?? 'Unknown error';

                switch ($statusCode) {
                    case '7379':
                        // Success case - Payment received for processing
                        return [
                            'status' => 'success',
                            'message' => 'Payment processed successfully',
                            'data' => $responseData
                        ];
                    case '7380':
                        throw new \Exception('Invalid payment request: ' . $statusDesc);
                    case '7381':
                        throw new \Exception('Invalid meter number: ' . $statusDesc);
                    case '7382':
                        throw new \Exception('Invalid account number: ' . $statusDesc);
                    case '7383':
                        throw new \Exception('Insufficient funds: ' . $statusDesc);
                    default:
                        throw new \Exception('Unexpected error: ' . $statusDesc);
                }
            }

            return [
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => $responseData
            ];

        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Gateway: Payment API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_data' => $paymentData
            ]);
            throw new \Exception('Failed to process payment: ' . $e->getMessage());
        }
    }

    /**
     * Check token status
     *
     * @param string $token
     * @param string $channelRef
     * @return array
     */
    public function checkTokenStatus(string $token, string $channelRef)
    {
        Log::channel('luku')->info('Luku Gateway: Starting token status check', [
            'token' => $token,
            'channelRef' => $channelRef
        ]);

        $payload = [
            'GepgGateway' => [
                'GepgGatewayVendReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName,
                        'Service' => 'LUKU_STATUS_CHECK'
                    ],
                    'PmtHdr' => [
                        'ChannelRef' => $channelRef,
                        'CbpGwRef' => $token,
                        'StsCode' => '7101',
                        'ResultUrl' => config('services.luku_gateway.status_check_url')
                    ]
                ]
            ]
        ];

        Log::channel('luku')->info('Luku Gateway: Token status check payload prepared', [
            'payload' => $payload
        ]);

        try {
            $xmlData = $this->arrayToXml($payload);
            // Updated endpoint for Luku status check
            $response = $this->makeRequest('post', $this->baseUrl . '/api/nbc-sg/v2/status-check-luku', ['xml' => $xmlData]);

            Log::channel('luku')->info('Luku Gateway: Token status check API response received', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            $responseData = $this->xmlToArray($response->body());
            
            // Validate response structure
            if (!isset($responseData['GepgGateway']['sgGepgVendResp'])) {
                throw new \Exception('Invalid response structure from Luku Gateway');
            }

            return $responseData;
        } catch (\Exception $e) {
            Log::channel('luku')->error('Luku Gateway: Token status check API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 