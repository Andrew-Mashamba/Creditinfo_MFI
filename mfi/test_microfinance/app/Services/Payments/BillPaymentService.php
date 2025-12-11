<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * Unified Bill Payment Service
 * Handles all utility bill payments including GEPG, LUKU, and other service providers
 */
class BillPaymentService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected string $privateKeyPath;
    protected string $gatewayUrl;
    protected string $gatewayAuth;
    protected string $channelId;
    protected string $channelName;

    // Bill types
    const BILL_TYPE_GEPG = 'GEPG';
    const BILL_TYPE_LUKU = 'LUKU';
    const BILL_TYPE_WATER = 'WATER';
    const BILL_TYPE_TELECOM = 'TELECOM';
    const BILL_TYPE_TV = 'TV';
    const BILL_TYPE_INSURANCE = 'INSURANCE';
    const BILL_TYPE_OTHER = 'OTHER';

    // Service provider configurations
    protected array $serviceProviders = [
        'GEPG' => [
            'name' => 'Government Electronic Payment Gateway',
            'endpoint' => '/api/nbc-sg/v2',
            'verification_required' => true,
            'use_gateway' => true
        ],
        'LUKU' => [
            'name' => 'LUKU Prepaid Electricity',
            'endpoint' => '/api/nbc-sg/v2/customerInfo',
            'payment_endpoint' => '/api/nbc-sg/v2/luku-pay',
            'verification_required' => true,
            'use_gateway' => true
        ],
        'DAWASCO' => [
            'name' => 'Dar es Salaam Water',
            'endpoint' => '/api/nbc-sg/v2/billquery',
            'payment_endpoint' => '/api/nbc-sg/v2/bill-pay',
            'verification_required' => true,
            'use_gateway' => true
        ],
        'TTCL' => [
            'name' => 'Tanzania Telecommunications',
            'endpoint' => '/api/nbc-sg/v2/billquery',
            'payment_endpoint' => '/api/nbc-sg/v2/bill-pay',
            'verification_required' => false,
            'use_gateway' => true
        ],
        'DSTV' => [
            'name' => 'DSTV',
            'endpoint' => '/api/nbc-sg/v2/billquery',
            'payment_endpoint' => '/api/nbc-sg/v2/bill-pay',
            'verification_required' => true,
            'use_gateway' => true
        ],
        'AZAM' => [
            'name' => 'Azam TV',
            'endpoint' => '/api/nbc-sg/v2/billquery',
            'payment_endpoint' => '/api/nbc-sg/v2/bill-pay',
            'verification_required' => true,
            'use_gateway' => true
        ]
    ];

    public function __construct()
    {
        $this->baseUrl = config('services.nbc_payments.base_url');
        $this->apiKey = config('services.nbc_payments.api_key');
        $this->clientId = config('services.nbc_payments.client_id');
        $this->privateKeyPath = config('services.nbc_payments.private_key_path');


        // NBC Gateway config (for bill payments)
        $this->gatewayUrl = config('services.nbc_gateway.base_url');
        $this->gatewayAuth = config('services.nbc_gateway.authorization');
        $this->channelId = config('services.nbc_gateway.channel_id');
        $this->channelName = config('services.nbc_gateway.channel_name');
        $this->logInfo('Bill Payment Service initialized', [
            'base_url' => $this->baseUrl,
            'gateway_url' => $this->gatewayUrl,
            'client_id' => $this->clientId,
            'channel_id' => $this->channelId,
            'providers' => array_keys($this->serviceProviders)
        ]);
    }

    /**
     * Inquire bill details
     *
     * @param string $billType
     * @param string $referenceNumber
     * @param array $additionalData
     * @return array
     */
    public function inquireBill(string $billType, string $referenceNumber, array $additionalData = []): array
    {
        $startTime = microtime(true);

        $this->logInfo("Starting bill inquiry", [
            'bill_type' => $billType,
            'reference' => $referenceNumber
        ]);

        try {
            switch ($billType) {
                case self::BILL_TYPE_GEPG:
                    $result = $this->inquireGEPGBill($referenceNumber, $additionalData);
                    break;

                case self::BILL_TYPE_LUKU:
                    $result = $this->inquireLUKUBill($referenceNumber, $additionalData);
                    break;

                default:
                    $result = $this->inquireGenericBill($billType, $referenceNumber, $additionalData);
                    break;
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($result['success']) {
                $this->logInfo("Bill inquiry successful", [
                    'bill_type' => $billType,
                    'reference' => $referenceNumber,
                    'duration_ms' => $duration
                ]);
            }

            $result['response_time'] = $duration;
            return $result;

        } catch (Exception $e) {
            $this->logError("Bill inquiry failed", [
                'bill_type' => $billType,
                'reference' => $referenceNumber,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'bill_type' => $billType,
                'reference' => $referenceNumber
            ];
        }
    }

    /**
     * Pay bill
     *
     * @param string $billType
     * @param array $paymentData
     * @return array
     */
    public function payBill(string $billType, array $paymentData): array
    {
        $startTime = microtime(true);
        $reference = $this->generateReference('BILL');

        $this->logInfo("Starting bill payment", [
            'reference' => $reference,
            'bill_type' => $billType,
            'amount' => $paymentData['amount'] ?? 0
        ]);

        try {
            // Validate payment data
            $this->validatePaymentData($paymentData);

            // Process based on bill type
            switch ($billType) {
                case self::BILL_TYPE_GEPG:
                    $result = $this->payGEPGBill($reference, $paymentData);
                    break;

                case self::BILL_TYPE_LUKU:
                    $result = $this->payLUKUBill($reference, $paymentData);
                    break;

                default:
                    $result = $this->payGenericBill($billType, $reference, $paymentData);
                    break;
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($result['success']) {
                // Save transaction
                $this->saveTransaction([
                    'reference' => $reference,
                    'type' => 'BILL_PAYMENT',
                    'bill_type' => $billType,
                    'bill_reference' => $paymentData['bill_reference'] ?? '',
                    'from_account' => $paymentData['from_account'],
                    'amount' => $paymentData['amount'],
                    'status' => 'SUCCESS',
                    'response_code' => $result['response_code'] ?? '',
                    'response_message' => $result['message'] ?? '',
                    'provider_reference' => $result['provider_reference'] ?? '',
                    'token' => $result['token'] ?? null,
                    'duration_ms' => $duration
                ]);

                $this->logInfo("Bill payment successful", [
                    'reference' => $reference,
                    'bill_type' => $billType,
                    'provider_reference' => $result['provider_reference'] ?? '',
                    'duration_ms' => $duration
                ]);

                $result['reference'] = $reference;
                $result['response_time'] = $duration;
                return $result;
            }

            throw new Exception($result['error'] ?? 'Payment failed');

        } catch (Exception $e) {
            $this->logError("Bill payment failed", [
                'reference' => $reference,
                'bill_type' => $billType,
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            // Save failed transaction
            $this->saveTransaction([
                'reference' => $reference,
                'type' => 'BILL_PAYMENT',
                'bill_type' => $billType,
                'bill_reference' => $paymentData['bill_reference'] ?? '',
                'from_account' => $paymentData['from_account'] ?? '',
                'amount' => $paymentData['amount'] ?? 0,
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return [
                'success' => false,
                'reference' => $reference,
                'error' => $e->getMessage(),
                'timestamp' => Carbon::now()->toIso8601String()
            ];
        }
    }

    /**
     * Inquire GEPG bill
     */
    protected function inquireGEPGBill(string $controlNumber, array $additionalData): array
    {
        try {
            $payload = [
                'GepgGatewayBillQryReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->clientId,
                        'ChannelName' => 'SACCOS',
                        'Service' => 'GEPG_INQ',
                    ],
                    'gepgBillQryReq' => [
                        'ChannelRef' => $this->generateReference('GEPGINQ'),
                        'CustCtrNum' => $controlNumber,
                        'DebitAccountNo' => $additionalData['account_number'] ?? config('services.nbc_payments.saccos_account'),
                        'DebitAccountCurrency' => 'TZS',
                    ],
                ]
            ];

            $response = $this->sendXMLRequest('/api/nbc-sg/v2/billquery', $payload, 'GEPG_INQ');

            if ($response['success']) {
                $billData = $response['data']['GepgGatewayBillQryResp'] ?? [];

                return [
                    'success' => true,
                    'bill_type' => self::BILL_TYPE_GEPG,
                    'control_number' => $controlNumber,
                    'bill_amount' => $billData['BillDtl']['BillAmt'] ?? 0,
                    'minimum_amount' => $billData['BillDtl']['MinPayAmt'] ?? 0,
                    'service_provider' => $billData['BillDtl']['SpName'] ?? '',
                    'payer_name' => $billData['BillDtl']['PyrName'] ?? '',
                    'bill_description' => $billData['BillDtl']['BillDesc'] ?? '',
                    'bill_status' => $billData['BillHdr']['BillStsCode'] ?? '',
                    'expiry_date' => $billData['BillDtl']['BillExprDt'] ?? '',
                    'raw_response' => $billData
                ];
            }

            throw new Exception($response['message'] ?? 'GEPG inquiry failed');

        } catch (Exception $e) {
            throw new Exception("GEPG bill inquiry error: " . $e->getMessage());
        }
    }

    /**
     * Pay GEPG bill
     */
    protected function payGEPGBill(string $reference, array $paymentData): array
    {
        try {
            $payload = [
                'GepgGatewayPaymentReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->clientId,
                        'ChannelName' => 'SACCOS',
                        'Service' => 'GEPG_PAY',
                    ],
                    'PmtHdr' => [
                        'ChannelRef' => $reference,
                        'CbpGwRef' => $paymentData['cbp_gw_ref'] ?? '',
                        'CustCtrNum' => $paymentData['control_number'],
                        'PayType' => '1',
                        'EntryCnt' => 1,
                        'BillStsCode' => $paymentData['bill_status'] ?? '',
                    ],
                    'PmtDtls' => [
                        'PmtDtl' => [
                            'ChannelTrxId' => $reference,
                            'SpCode' => $paymentData['sp_code'] ?? '',
                            'PayRefId' => $paymentData['pay_ref_id'] ?? '',
                            'BillCtrNum' => $paymentData['control_number'],
                            'PaidAmt' => $paymentData['amount'],
                            'TrxDtTm' => Carbon::now()->format('Y-m-d\TH:i:s'),
                            'PayOpt' => '1',
                            'Ccy' => 'TZS',
                            'PyrName' => $paymentData['payer_name'] ?? '',
                            'DebitAmount' => $paymentData['amount'],
                        ]
                    ],
                    'GepgGatewayProcessingInfo' => [
                        'BankType' => 'ONUS',
                        'Forex' => 'N',
                        'DebitAccountNo' => $paymentData['from_account'],
                        'DebitAccountType' => 'CASA',
                        'DebitAccountCurrency' => 'TZS',
                        'DebitAmount' => $paymentData['amount'],
                    ]
                ]
            ];

            $response = $this->sendXMLRequest('/api/nbc-sg/v2/bill-pay', $payload, 'GEPG_PAY');

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => 'GEPG payment successful',
                    'provider_reference' => $response['data']['PmtTrxInf']['TrxId'] ?? '',
                    'response_code' => $response['data']['PmtTrxInf']['TrxSts'] ?? '',
                    'control_number' => $paymentData['control_number']
                ];
            }

            throw new Exception($response['message'] ?? 'GEPG payment failed');

        } catch (Exception $e) {
            throw new Exception("GEPG payment error: " . $e->getMessage());
        }
    }

    /**
     * Inquire LUKU bill
     */
    protected function inquireLUKUBill(string $meterNumber, array $additionalData): array
    {
        try {
            $payload = [
                'serviceName' => 'LUKU_LOOKUP',
                'clientId' => $this->clientId,
                'clientRef' => $this->generateReference('LUKULOOKUP'),
                'meterNumber' => $meterNumber,
                'accountNumber' => $additionalData['account_number'] ?? config('services.nbc_payments.saccos_account'),
                'accountCurrency' => 'TZS'
            ];

            $signature = $this->generateSignature($payload);

            $response = $this->sendRequest('/api/nbc-luku/v2/lookup', $payload, [
                'Signature' => $signature,
                'Service-Name' => 'LUKU_LOOKUP'
            ]);

            if ($response['success']) {
                $lukuData = $response['data'];

                return [
                    'success' => true,
                    'bill_type' => self::BILL_TYPE_LUKU,
                    'meter_number' => $meterNumber,
                    'owner_name' => $lukuData['owner'] ?? '',
                    'meter_status' => $lukuData['statusDescription'] ?? '',
                    'debts' => $lukuData['debts'] ?? [],
                    'reference' => $lukuData['reference'] ?? '',
                    'raw_response' => $lukuData
                ];
            }

            throw new Exception($response['message'] ?? 'LUKU inquiry failed');

        } catch (Exception $e) {
            throw new Exception("LUKU meter inquiry error: " . $e->getMessage());
        }
    }

    /**
     * Pay LUKU bill (using XML format for NBC Gateway)
     */
    protected function payLUKUBill(string $reference, array $paymentData): array
    {
        try {
            // Get LUKU configuration
            $creditAccount = config('services.luku_gateway.credit_account', '012202001486');
            $user = auth()->user();
            $customerPhone = $paymentData['customer_phone'] ?? ($user ? $user->phone_number : '');
            $customerName = $paymentData['customer_name'] ?? ($user ? $user->name : '');
            $customerEmail = $paymentData['customer_email'] ?? ($user ? $user->email : '');
            $customerTin = $paymentData['customer_tin'] ?? '';
            $customerNin = $paymentData['customer_nin'] ?? '';
            $trxDateTime = Carbon::now()->format('Y-m-d\TH:i:s');
            $transactionId = $paymentData['transaction_id'] ?? $reference;
            $channelRef = $paymentData['channel_ref'] ?? $reference;
            $cbpGwRef = $paymentData['cbp_gw_ref'] ?? $reference;
            $resultUrl = $paymentData['result_url'] ?? config('app.url') . '/api/luku/callback';
            $paymentChannel = $paymentData['payment_channel'] ?? 'ONLINE';
            $thirdParty = $paymentData['third_party'] ?? 'NBC';

            // Build XML payload similar to LukuService
            $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
    <GepgGatewayVendReq>
        <GepgGatewayHdr>
            <ChannelID>{$this->channelId}</ChannelID>
            <ChannelName>{$this->channelName}</ChannelName>
            <Service>LUKU</Service>
        </GepgGatewayHdr>
        <PmtHdr>
            <ChannelRef>{$channelRef}</ChannelRef>
            <CbpGwRef>{$cbpGwRef}</CbpGwRef>
            <StsCode>7101</StsCode>
            <ResultUrl>{$resultUrl}</ResultUrl>
        </PmtHdr>
        <gepgVendReqInf>
            <ChannelTrxId>{$transactionId}</ChannelTrxId>
            <CustCtrNum>{$paymentData['meter_number']}</CustCtrNum>
            <DebitAccountNo>{$paymentData['from_account']}</DebitAccountNo>
            <DebitAccountCurrency>TZS</DebitAccountCurrency>
            <Amount>{$paymentData['amount']}</Amount>
            <CreditAccountNo>{$creditAccount}</CreditAccountNo>
            <TrxDtTm>{$trxDateTime}</TrxDtTm>
            <UsdPayChnl>{$paymentChannel}</UsdPayChnl>
            <ThirdParty>{$thirdParty}</ThirdParty>
            <CustomerMsisdn>{$customerPhone}</CustomerMsisdn>
            <CutomerName>{$customerName}</CutomerName>
            <CustomerTIN>{$customerTin}</CustomerTIN>
            <CustomerNIN>{$customerNin}</CustomerNIN>
            <CustomerEmail>{$customerEmail}</CustomerEmail>
        </gepgVendReqInf>
    </GepgGatewayVendReq>
</GepgGateway>
XML;

            // Sign the XML payload
            $signedXml = $this->signXmlPayload($xmlPayload);

            $this->logDebug("Sending LUKU payment request (XML)", [
                'url' => $this->gatewayUrl . '/api/nbc-sg/v2/luku-pay',
                'meter_number' => $paymentData['meter_number'],
                'amount' => $paymentData['amount'],
                'payload_length' => strlen($signedXml)
            ]);

            // Send XML request to NBC Gateway
            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml',
                'NBC-Authorization' => $this->gatewayAuth,
                'ChannelID' => $this->channelId,
                'ChannelName' => $this->channelName
            ])
            ->withOptions(['verify' => false])
            ->timeout(30)
            ->post($this->gatewayUrl . '/api/nbc-sg/v2/luku-pay', $signedXml);

            $statusCode = $response->status();
            $responseBody = $response->body();

            $this->logDebug("LUKU payment response received", [
                'status_code' => $statusCode,
                'body_length' => strlen($responseBody)
            ]);

            // Debug info
            $debugInfo = [
                'request' => [
                    'url' => $this->gatewayUrl . '/api/nbc-sg/v2/luku-pay',
                    'method' => 'POST',
                    'headers' => [
                        'Content-Type' => 'application/xml',
                        'Accept' => 'application/xml',
                        'Authorization' => '***REDACTED***',
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName
                    ],
                    'payload_length' => strlen($signedXml)
                ],
                'response' => [
                    'status_code' => $statusCode,
                    'headers' => $response->headers(),
                    'body' => $responseBody
                ]
            ];

            if ($statusCode === 200 || $statusCode === 201) {
                // Parse XML response
                $parsedResponse = $this->parseXmlResponse($responseBody);

                // Extract token if available
                $token = $parsedResponse['token'] ?? $parsedResponse['Token'] ?? '';
                $units = $parsedResponse['units'] ?? $parsedResponse['Units'] ?? '';
                $receiptNumber = $parsedResponse['receiptNumber'] ?? $parsedResponse['ReceiptNumber'] ?? '';

                // Save token if received
                if (!empty($token)) {
                    $this->saveLukuToken([
                        'reference' => $reference,
                        'meter_number' => $paymentData['meter_number'],
                        'token' => $token,
                        'units' => $units,
                        'amount' => $paymentData['amount']
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'LUKU payment successful',
                    'provider_reference' => $receiptNumber,
                    'token' => $token,
                    'units' => $units,
                    'meter_number' => $paymentData['meter_number'],
                    'debug' => $debugInfo
                ];
            }

            return [
                'success' => false,
                'error' => "LUKU payment failed with status {$statusCode}",
                'debug' => $debugInfo
            ];

        } catch (Exception $e) {
            $this->logError("LUKU payment error", [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);

            throw new Exception("LUKU payment error: " . $e->getMessage());
        }
    }

    /**
     * Sign XML payload with private key (for LUKU and GEPG)
     */
    protected function signXmlPayload(string $xmlPayload): string
    {
        try {
            if (!file_exists($this->privateKeyPath)) {
                $this->logWarning('Private key not found, returning unsigned XML', [
                    'path' => $this->privateKeyPath
                ]);
                // Add empty signature tag if no key available
                $xml = new \SimpleXMLElement($xmlPayload);
                $xml->addChild('gepggatewaySignature', '');
                return $xml->asXML();
            }

            $privateKeyContent = file_get_contents($this->privateKeyPath);

            // Remove any existing signature tag if present
            $xmlPayload = preg_replace('/<gepggatewaySignature>.*?<\/gepggatewaySignature>/s', '', $xmlPayload);
            $xmlPayload = str_replace('</GepgGateway>', '', $xmlPayload);

            // Load private key and sign the XML
            $privateKey = PublicKeyLoader::load($privateKeyContent);
            $signature = $privateKey->sign($xmlPayload . '</GepgGateway>');

            // Add signature to XML
            $signedXml = $xmlPayload . '    <gepggatewaySignature>' . base64_encode($signature) . '</gepggatewaySignature>' . PHP_EOL . '</GepgGateway>';

            $this->logDebug('XML signed successfully', [
                'signature_length' => strlen(base64_encode($signature))
            ]);

            return $signedXml;
        } catch (Exception $e) {
            $this->logError('Failed to sign XML', [
                'error' => $e->getMessage()
            ]);
            // Return unsigned XML if signing fails
            return $xmlPayload;
        }
    }

    /**
     * Parse XML response
     */
    protected function parseXmlResponse(string $xml): array
    {
        try {
            // Check if response is empty
            if (empty($xml)) {
                $this->logError('Empty XML response received');
                return ['error' => 'Empty response received from server'];
            }

            // Try to detect if response is JSON instead of XML
            if (substr(trim($xml), 0, 1) === '{' || substr(trim($xml), 0, 1) === '[') {
                $this->logWarning('Response appears to be JSON, attempting JSON decode');
                $jsonData = json_decode($xml, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $jsonData;
                }
            }

            $object = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
            if ($object === false) {
                $errors = libxml_get_errors();
                $errorMessages = array_map(function($error) {
                    return $error->message;
                }, $errors);
                $this->logError('XML parsing errors', ['errors' => $errorMessages]);
                throw new Exception('Failed to parse XML: ' . implode(', ', $errorMessages));
            }

            $arrayData = json_decode(json_encode($object), true);
            $this->logDebug('XML successfully parsed', [
                'data_keys' => array_keys($arrayData)
            ]);

            return $arrayData;
        } catch (Exception $e) {
            $this->logError('XML/JSON parsing failed', [
                'error' => $e->getMessage(),
                'xml_sample' => substr($xml, 0, 500)
            ]);
            return ['error' => 'Invalid response format: ' . $e->getMessage()];
        }
    }

    /**
     * Inquire generic bill
     */
    protected function inquireGenericBill(string $billType, string $referenceNumber, array $additionalData): array
    {
        try {
            if (!isset($this->serviceProviders[$billType])) {
                throw new Exception("Unknown bill type: {$billType}");
            }

            $provider = $this->serviceProviders[$billType];

            $payload = [
                'serviceName' => "{$billType}_INQUIRY",
                'clientId' => $this->clientId,
                'clientRef' => $this->generateReference("{$billType}INQ"),
                'referenceNumber' => $referenceNumber,
                'accountNumber' => $additionalData['account_number'] ?? config('services.nbc_payments.saccos_account'),
                'additionalData' => $additionalData
            ];

            $response = $this->sendRequest($provider['endpoint'], $payload, [], $provider);

            if ($response['success']) {
                return [
                    'success' => true,
                    'bill_type' => $billType,
                    'reference_number' => $referenceNumber,
                    'bill_amount' => $response['data']['amount'] ?? 0,
                    'customer_name' => $response['data']['customerName'] ?? '',
                    'bill_description' => $response['data']['description'] ?? '',
                    'due_date' => $response['data']['dueDate'] ?? '',
                    'raw_response' => $response['data']
                ];
            }

            throw new Exception($response['message'] ?? 'Bill inquiry failed');

        } catch (Exception $e) {
            throw new Exception("Bill inquiry error: " . $e->getMessage());
        }
    }

    /**
     * Pay generic bill
     */
    protected function payGenericBill(string $billType, string $reference, array $paymentData): array
    {
        try {
            if (!isset($this->serviceProviders[$billType])) {
                throw new Exception("Unknown bill type: {$billType}");
            }

            $provider = $this->serviceProviders[$billType];

            $payload = [
                'serviceName' => "{$billType}_PAYMENT",
                'clientId' => $this->clientId,
                'clientRef' => $reference,
                'referenceNumber' => $paymentData['bill_reference'],
                'amount' => $paymentData['amount'],
                'accountNumber' => $paymentData['from_account'],
                'customerName' => $paymentData['customer_name'] ?? '',
                'timestamp' => Carbon::now()->toIso8601String()
            ];

            $response = $this->sendRequest($provider['endpoint'] . '/payment', $payload);

            if ($response['success']) {
                return [
                    'success' => true,
                    'message' => "{$billType} payment successful",
                    'provider_reference' => $response['data']['transactionId'] ?? '',
                    'response_code' => $response['data']['responseCode'] ?? '',
                    'bill_reference' => $paymentData['bill_reference']
                ];
            }

            throw new Exception($response['message'] ?? 'Payment failed');

        } catch (Exception $e) {
            throw new Exception("Payment error: " . $e->getMessage());
        }
    }

    /**
     * Save LUKU token
     */
    protected function saveLukuToken(array $tokenData): void
    {
        try {
            DB::table('luku_tokens')->insert([
                'reference' => $tokenData['reference'],
                'meter_number' => $tokenData['meter_number'],
                'token' => $tokenData['token'],
                'units' => $tokenData['units'],
                'amount' => $tokenData['amount'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->logInfo("LUKU token saved", [
                'reference' => $tokenData['reference'],
                'meter' => $tokenData['meter_number']
            ]);
        } catch (Exception $e) {
            $this->logError("Failed to save LUKU token", [
                'error' => $e->getMessage(),
                'reference' => $tokenData['reference']
            ]);
        }
    }

    /**
     * Validate payment data
     */
    protected function validatePaymentData(array $data): void
    {
        $required = ['from_account', 'amount'];

        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new Exception("Invalid amount");
        }
    }

    /**
     * Generate digital signature
     */
    protected function generateSignature(array $payload): string
    {
        try {
            if (!file_exists($this->privateKeyPath)) {
                throw new Exception("Private key file not found");
            }

            $privateKeyContent = file_get_contents($this->privateKeyPath);
            $privateKey = openssl_pkey_get_private($privateKeyContent);

            if (!$privateKey) {
                throw new Exception("Failed to load private key");
            }

            $jsonPayload = json_encode($payload);
            openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            return base64_encode($signature);

        } catch (Exception $e) {
            $this->logError("Signature generation failed", ['error' => $e->getMessage()]);
            throw new Exception("Failed to generate digital signature: " . $e->getMessage());
        }
    }

    /**
     * Send JSON request
     */
    protected function sendRequest(string $endpoint, array $payload, array $additionalHeaders = [], array $provider = []): array
    {
        try {
            // Use NBC Gateway if provider specifies it
            $baseUrl = (!empty($provider) && isset($provider['use_gateway']) && $provider['use_gateway'])
                ? $this->gatewayUrl
                : $this->baseUrl;
            $url = $baseUrl . $endpoint;

            $headers = array_merge([
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Client-Id' => $this->clientId,
                'Timestamp' => Carbon::now()->toIso8601String()
            ], $additionalHeaders);

            // Add NBC Gateway authorization header if using gateway
            if (!empty($provider) && isset($provider['use_gateway']) && $provider['use_gateway']) {
                $headers['NBC-Authorization'] = $this->gatewayAuth;
                $headers['ChannelID'] = $this->channelId;
                $headers['ChannelName'] = $this->channelName;
            }

            // Sanitize headers for logging (hide sensitive data)
            $sanitizedHeaders = $headers;
            if (isset($sanitizedHeaders['X-Api-Key'])) {
                $sanitizedHeaders['X-Api-Key'] = '***REDACTED***';
            }
            if (isset($sanitizedHeaders['NBC-Authorization'])) {
                $sanitizedHeaders['NBC-Authorization'] = '***REDACTED***';
            }

            $this->logDebug("Sending bill payment request", [
                'url' => $url,
                'service' => $payload['serviceName'] ?? 'UNKNOWN',
                'headers' => $sanitizedHeaders,
                'payload' => $payload
            ]);

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->timeout(30)
                ->post($url, $payload);

            $statusCode = $response->status();
            $responseData = $response->json() ?? [];
            $responseBody = $response->body();

            $this->logDebug("Bill payment response received", [
                'status_code' => $statusCode,
                'service' => $payload['serviceName'] ?? 'UNKNOWN',
                'response' => $responseData
            ]);

            // Capture request/response details for debugging
            $debugInfo = [
                'request' => [
                    'url' => $url,
                    'method' => 'POST',
                    'headers' => $sanitizedHeaders,
                    'payload' => $payload
                ],
                'response' => [
                    'status_code' => $statusCode,
                    'headers' => $response->headers(),
                    'body' => $responseData ?: $responseBody
                ]
            ];

            if ($statusCode === 200 || $statusCode === 201) {
                return [
                    'success' => true,
                    'data' => $responseData,
                    'debug' => $debugInfo
                ];
            }

            return [
                'success' => false,
                'message' => $responseData['message'] ?? "Request failed with status {$statusCode}",
                'debug' => $debugInfo
            ];

        } catch (Exception $e) {
            $this->logError("Request failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'debug' => [
                    'request' => [
                        'url' => $url ?? 'N/A',
                        'endpoint' => $endpoint,
                        'payload' => $payload
                    ],
                    'error' => $e->getMessage()
                ]
            ];
        }
    }
    /**
     * Send XML request (for GEPG)
     */
    protected function sendXMLRequest(string $endpoint, array $payload, string $service): array
    {
        try {
            $url = $this->baseUrl . $endpoint;
            $xml = $this->arrayToXml($payload);

            $headers = [
                'Content-Type' => 'application/xml',
                'X-Api-Key' => $this->apiKey,
                'Client-Id' => $this->clientId,
                'Service-Name' => $service
            ];

            $this->logDebug("Sending XML request", [
                'url' => $url,
                'service' => $service
            ]);

            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->timeout(30)
                ->send('POST', $url, ['body' => $xml]);

            $statusCode = $response->status();
            $xmlResponse = $response->body();

            $this->logDebug("XML response received", [
                'status_code' => $statusCode,
                'service' => $service
            ]);

            if ($statusCode === 200 || $statusCode === 201) {
                $responseData = $this->xmlToArray($xmlResponse);
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            return [
                'success' => false,
                'message' => "Request failed with status {$statusCode}"
            ];

        } catch (Exception $e) {
            $this->logError("XML request failed", [
                'endpoint' => $endpoint,
                'service' => $service,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Convert array to XML
     */
    protected function arrayToXml(array $data, $rootElement = null): string
    {
        if ($rootElement === null) {
            $rootElement = new \SimpleXMLElement('<root/>');
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subNode = $rootElement->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                $rootElement->addChild($key, htmlspecialchars($value));
            }
        }

        return $rootElement->asXML();
    }

    /**
     * Convert XML to array
     */
    protected function xmlToArray(string $xml): array
    {
        $xmlObject = simplexml_load_string($xml);
        $json = json_encode($xmlObject);
        return json_decode($json, true);
    }

    /**
     * Generate unique reference
     */
    protected function generateReference(string $prefix = 'BILL'): string
    {
        // NBC API requires alphanumeric clientRef only (no underscores or special chars)
        return $prefix . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    /**
     * Save transaction to database
     */
    protected function saveTransaction(array $data): void
    {
        try {
            DB::table('payment_transactions')->insert([
                'reference' => $data['reference'],
                'type' => $data['type'],
                'bill_type' => $data['bill_type'] ?? null,
                'bill_reference' => $data['bill_reference'] ?? null,
                'from_account' => $data['from_account'],
                'amount' => $data['amount'],
                'status' => $data['status'],
                'response_code' => $data['response_code'] ?? null,
                'response_message' => $data['response_message'] ?? null,
                'provider_reference' => $data['provider_reference'] ?? null,
                'token' => $data['token'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'duration_ms' => $data['duration_ms'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (Exception $e) {
            $this->logError("Failed to save transaction", [
                'error' => $e->getMessage(),
                'reference' => $data['reference']
            ]);
        }
    }

    /**
     * Log information
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::channel('payments')->info("[BILL] {$message}", $context);
    }

    /**
     * Log error
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::channel('payments')->error("[BILL] {$message}", $context);
    }

    /**
     * Log debug
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::channel('payments')->debug("[BILL] {$message}", $context);
    }

    /**
     * Log warning
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::channel('payments')->warning("[BILL] {$message}", $context);
    }
}
