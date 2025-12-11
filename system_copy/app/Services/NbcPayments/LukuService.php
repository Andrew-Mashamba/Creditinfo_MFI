<?php

namespace App\Services\NbcPayments;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use phpseclib3\Crypt\PublicKeyLoader;

class LukuService
{
    protected string $baseUrl;
    protected string $channelId;
    protected string $channelName;
    protected string $authorization;
    protected ?string $privateKey = null;

    public function __construct()
    {
        // Load configuration from NBC Gateway config
        $this->baseUrl = config('services.nbc_gateway.base_url', 'https://nbc-gateway-uat.intra.nbc.co.tz');
        $this->channelId = config('services.nbc_gateway.channel_id', 'SACCOSNBC');
        $this->channelName = config('services.nbc_gateway.channel_name', 'TR');
        $this->authorization = config('services.nbc_gateway.authorization', 'Basic c2FjY29zbmJjOkBOQkNzYWNjb3Npc2FsZUx0ZA==');

        // Load private key from NBC Payments config (correct path)
        $privateKeyPath = config('services.nbc_payments.private_key_path', storage_path('keys/private.pem'));
        if (file_exists($privateKeyPath)) {
            $this->privateKey = file_get_contents($privateKeyPath);
            Log::info('LUKU Service: Private key loaded successfully', ['path' => $privateKeyPath]);
        } else {
            Log::warning('LUKU Service: Private key not found', ['path' => $privateKeyPath]);
        }
    }

    /**
     * Sign XML payload with private key
     */
    protected function signXmlPayload(string $xmlPayload): string
    {
        try {
            if (!$this->privateKey) {
                Log::warning('LUKU Service: No private key available, returning unsigned XML');
                // If no private key, just add empty signature tag
                $xml = new \SimpleXMLElement($xmlPayload);
                $xml->addChild('gepggatewaySignature', '');
                return $xml->asXML();
            }

            // Remove any existing signature tag if present
            $xmlPayload = preg_replace('/<gepggatewaySignature>.*?<\/gepggatewaySignature>/s', '', $xmlPayload);
            $xmlPayload = str_replace('</GepgGateway>', '', $xmlPayload);

            // Load private key and sign the XML
            $privateKey = PublicKeyLoader::load($this->privateKey);
            $signature = $privateKey->sign($xmlPayload . '</GepgGateway>');

            // Add signature to XML
            $signedXml = $xmlPayload . '    <gepggatewaySignature>' . base64_encode($signature) . '</gepggatewaySignature>' . PHP_EOL . '</GepgGateway>';

            Log::debug('LUKU Service: XML signed successfully', [
                'signature_length' => strlen(base64_encode($signature))
            ]);

            return $signedXml;
        } catch (Exception $e) {
            Log::error('LUKU Service: Failed to sign XML', [
                'error' => $e->getMessage()
            ]);
            // Return unsigned XML if signing fails
            return $xmlPayload;
        }
    }

    public function lookup(string $meterNumber, string $accountNumber): array
    {
        Log::info('=== LUKU SERVICE LOOKUP STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'meter_number' => $meterNumber,
            'account_number' => $accountNumber ?: 'NOT_PROVIDED',
            'baseUrl' => $this->baseUrl,
            'channelId' => $this->channelId,
            'channelName' => $this->channelName
        ]);

        // Set default account if not provided
        if (empty($accountNumber)) {
            $accountNumber = '28012040011'; // Default account
            Log::info('Using default account number', ['account' => $accountNumber]);
        }

        // Generate unique references
        $channelRef = 'LUKU' . now()->timestamp . rand(1000, 9999);

        $payload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
    <GepgGatewayBillQryReq>
        <GepgGatewayHdr>
            <ChannelID>{$this->channelId}</ChannelID>
            <ChannelName>{$this->channelName}</ChannelName>
            <Service>LUKU</Service>
        </GepgGatewayHdr>
        <gepgBillQryReq>
            <ChannelRef>{$channelRef}</ChannelRef>
            <CustCtrNum>{$meterNumber}</CustCtrNum>
            <DebitAccountNo>{$accountNumber}</DebitAccountNo>
            <DebitAccountCurrency>TZS</DebitAccountCurrency>
        </gepgBillQryReq>
    </GepgGatewayBillQryReq>
</GepgGateway>
XML;

        try {
            // Sign the XML payload
            $payload = $this->signXmlPayload($payload);

            Log::info('LUKU lookup request payload prepared', [
                'payload_length' => strlen($payload),
                'meter_in_payload' => strpos($payload, $meterNumber) !== false,
                'account_in_payload' => strpos($payload, $accountNumber) !== false,
                'is_signed' => strpos($payload, 'gepggatewaySignature') !== false
            ]);

            Log::debug('Full LUKU lookup payload', ['payload' => $payload]);

            // Log raw XML for debugging
            Log::info('=== RAW XML REQUEST ===');
            Log::info($payload);
            Log::info('=== END RAW XML REQUEST ===');

            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
                'NBC-Authorization' => $this->authorization,
                'User-Agent' => 'Mozilla/5.0 (compatible; LukuService/1.0)'
            ])
            ->timeout(30)
            ->withoutVerifying() // For internal UAT server
            ->post("{$this->baseUrl}/api/nbc-sg/v2/customerInfo", $payload);

            Log::info('LUKU lookup HTTP response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'failed' => $response->failed(),
                'headers' => $response->headers(),
                'body_length' => strlen($response->body()),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Log raw response for debugging
            if ($response->failed()) {
                Log::info('=== RAW XML RESPONSE (ERROR) ===');
                Log::info($response->body());
                Log::info('=== END RAW XML RESPONSE ===');
            }

            if ($response->failed()) {
                Log::error('LUKU lookup HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'reason' => $response->reason()
                ]);
                return ['error' => 'HTTP request failed: ' . $response->reason()];
            }

            Log::debug('LUKU lookup response body', ['body' => $response->body()]);

            $parsedResponse = $this->parseXmlResponse($response->body());

            Log::info('LUKU lookup response parsed', [
                'has_error' => isset($parsedResponse['error']),
                'response_keys' => array_keys($parsedResponse),
                'parsed_data' => $parsedResponse
            ]);

            return $parsedResponse;

        } catch (Exception $e) {
            Log::error('✗✗ LUKU lookup exception in service', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return ['error' => 'Failed to perform lookup: ' . $e->getMessage()];
        }
    }

    public function pay(array $data): array
    {
        Log::info('=== LUKU SERVICE PAYMENT STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'data' => $data,
            'user_id' => auth()->id(),
            'baseUrl' => $this->baseUrl,
            'channelId' => $this->channelId,
            'channelName' => $this->channelName
        ]);

        try {
            DB::beginTransaction();
            Log::info('Database transaction started');

            $transactionData = [
                'branch_id' => auth()->user()->branch_id ?? 1,
                'service_name' => 'LUKU',
                'service_code' => 'LUKU_PAY',
                'action_id' => $data['transaction_id'],
                'amount' => $data['amount'],
                'reference_number' => $data['channel_ref'],
                'description' => 'LUKU Payment Request',
                'third_party_reference' => $data['cbp_gw_ref'],
                'status' => 'pending',
                'transaction_type' => 'payment',
                'currency' => 'TZS',
                'created_by' => auth()->id() ?? 1,
                'bank' => 'NBC',
                'bank_account' => $data['account_number'],
                'mirror_account' => $data['credit_account'] ?? null,
                'client_bank_account' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Log::info('Inserting LUKU transaction to database', ['transaction_data' => $transactionData]);

            $transactionId = DB::table('transactions')->insertGetId($transactionData);

            Log::info('✓ LUKU transaction recorded in DB', ['transaction_id' => $transactionId]);

            // Get user details
            $user = auth()->user();
            $customerPhone = isset($data['customer_phone']) ? $data['customer_phone'] : ($user ? $user->phone_number : '');
            $customerName = isset($data['customer_name']) ? $data['customer_name'] : ($user ? $user->name : '');
            $customerEmail = isset($data['customer_email']) ? $data['customer_email'] : ($user ? $user->email : '');
            $creditAccount = isset($data['credit_account']) ? $data['credit_account'] : '012202001486';
            $paymentChannel = isset($data['payment_channel']) ? $data['payment_channel'] : 'ONLINE';
            $thirdParty = isset($data['third_party']) ? $data['third_party'] : 'NBC';
            $customerTin = isset($data['customer_tin']) ? $data['customer_tin'] : '';
            $customerNin = isset($data['customer_nin']) ? $data['customer_nin'] : '';
            $trxDateTime = now()->format('Y-m-d\TH:i:s');

            $payload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
    <GepgGatewayVendReq>
        <GepgGatewayHdr>
            <ChannelID>{$this->channelId}</ChannelID>
            <ChannelName>{$this->channelName}</ChannelName>
            <Service>LUKU</Service>
        </GepgGatewayHdr>
        <PmtHdr>
            <ChannelRef>{$data['channel_ref']}</ChannelRef>
            <CbpGwRef>{$data['cbp_gw_ref']}</CbpGwRef>
            <StsCode>7101</StsCode>
            <ResultUrl>{$data['result_url']}</ResultUrl>
        </PmtHdr>
        <gepgVendReqInf>
            <ChannelTrxId>{$data['transaction_id']}</ChannelTrxId>
            <CustCtrNum>{$data['meter_number']}</CustCtrNum>
            <DebitAccountNo>{$data['account_number']}</DebitAccountNo>
            <DebitAccountCurrency>TZS</DebitAccountCurrency>
            <Amount>{$data['amount']}</Amount>
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
            $payload = $this->signXmlPayload($payload);

            Log::info('LUKU payment request payload prepared', [
                'payload_length' => strlen($payload),
                'transaction_id' => $data['transaction_id'],
                'meter_number' => $data['meter_number'],
                'amount' => $data['amount']
            ]);

            Log::debug('Full LUKU payment payload', ['payload' => $payload]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml',
                'NBC-Authorization' => $this->authorization,
                'ChannelID' => $this->channelId,
                'ChannelName' => $this->channelName
            ])
            ->timeout(30)
            ->withoutVerifying() // For internal UAT server
            ->post("{$this->baseUrl}/api/nbc-sg/v2/luku-pay", $payload);

            Log::info('LUKU payment HTTP response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'failed' => $response->failed(),
                'headers' => $response->headers(),
                'body_length' => strlen($response->body()),
                'timestamp' => now()->toDateTimeString()
            ]);

            if ($response->failed()) {
                Log::error('✗ LUKU payment HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'reason' => $response->reason()
                ]);
                DB::rollBack();
                return ['error' => 'Payment HTTP request failed: ' . $response->reason()];
            }

            Log::debug('LUKU payment response body', ['body' => $response->body()]);

            DB::commit();
            Log::info('✓ Database transaction committed');

            $parsedResponse = $this->parseXmlResponse($response->body());

            Log::info('LUKU payment response parsed', [
                'has_error' => isset($parsedResponse['error']),
                'response_keys' => array_keys($parsedResponse),
                'parsed_data' => $parsedResponse
            ]);

            return $parsedResponse;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('✗✗ LUKU payment exception in service', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
                'timestamp' => now()->toDateTimeString()
            ]);
            return ['error' => 'Payment failed: ' . $e->getMessage()];
        }
    }

    public function checkStatus(array $data): array
    {
        Log::info('=== LUKU STATUS CHECK STARTED ===', [
            'timestamp' => now()->toDateTimeString(),
            'data' => $data,
            'baseUrl' => $this->baseUrl
        ]);

        // According to API doc, status check uses same structure as payment request
        $creditAccount = isset($data['credit_account']) ? $data['credit_account'] : '012202001486';
        $trxDateTime = isset($data['transaction_datetime']) ? $data['transaction_datetime'] : now()->format('Y-m-d\TH:i:s');
        $paymentChannel = isset($data['payment_channel']) ? $data['payment_channel'] : 'ONLINE';
        $thirdParty = isset($data['third_party']) ? $data['third_party'] : 'NBC';
        $customerPhone = isset($data['customer_phone']) ? $data['customer_phone'] : '';
        $customerName = isset($data['customer_name']) ? $data['customer_name'] : '';
        $customerTin = isset($data['customer_tin']) ? $data['customer_tin'] : '';
        $customerNin = isset($data['customer_nin']) ? $data['customer_nin'] : '';
        $customerEmail = isset($data['customer_email']) ? $data['customer_email'] : '';

        $payload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<GepgGateway>
    <GepgGatewayVendReq>
        <GepgGatewayHdr>
            <ChannelID>{$this->channelId}</ChannelID>
            <ChannelName>{$this->channelName}</ChannelName>
            <Service>LUKU</Service>
        </GepgGatewayHdr>
        <PmtHdr>
            <ChannelRef>{$data['channel_ref']}</ChannelRef>
            <CbpGwRef>{$data['cbp_gw_ref']}</CbpGwRef>
            <StsCode>7101</StsCode>
            <ResultUrl>{$data['result_url']}</ResultUrl>
        </PmtHdr>
        <gepgVendReqInf>
            <ChannelTrxId>{$data['transaction_id']}</ChannelTrxId>
            <CustCtrNum>{$data['meter_number']}</CustCtrNum>
            <DebitAccountNo>{$data['account_number']}</DebitAccountNo>
            <DebitAccountCurrency>TZS</DebitAccountCurrency>
            <Amount>{$data['amount']}</Amount>
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

        try {
            // Sign the XML payload
            $payload = $this->signXmlPayload($payload);

            Log::info('LUKU status check request prepared', [
                'payload_length' => strlen($payload),
                'channel_ref' => $data['channel_ref']
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/xml',
                'Accept' => 'application/xml',
                'NBC-Authorization' => $this->authorization,
                'ChannelID' => $this->channelId,
                'ChannelName' => $this->channelName
            ])
            ->timeout(30)
            ->withoutVerifying() // For internal UAT server
            ->post("{$this->baseUrl}/api/nbc-sg/v2/status-check-luku", $payload);

            Log::info('LUKU status check HTTP response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body_length' => strlen($response->body())
            ]);

            if ($response->failed()) {
                Log::error('✗ LUKU status check HTTP request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['error' => 'Status check failed: ' . $response->reason()];
            }

            $parsedResponse = $this->parseXmlResponse($response->body());

            Log::info('LUKU status check response parsed', [
                'has_error' => isset($parsedResponse['error']),
                'response_keys' => array_keys($parsedResponse)
            ]);

            return $parsedResponse;

        } catch (Exception $e) {
            Log::error('✗✗ LUKU status check exception', [
                'error' => $e->getMessage(),
                'channel_ref' => $data['channel_ref'] ?? 'N/A'
            ]);
            return ['error' => 'Status check failed: ' . $e->getMessage()];
        }
    }

    private function parseXmlResponse(string $xml): array
    {
        Log::info('Parsing XML response', [
            'xml_length' => strlen($xml),
            'first_100_chars' => substr($xml, 0, 100)
        ]);

        try {
            // Check if response is empty
            if (empty($xml)) {
                Log::error('Empty XML response received');
                return ['error' => 'Empty response received from server'];
            }

            // Try to detect if response is JSON instead of XML
            if (substr(trim($xml), 0, 1) === '{' || substr(trim($xml), 0, 1) === '[') {
                Log::warning('Response appears to be JSON, attempting JSON decode');
                $jsonData = json_decode($xml, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('Successfully parsed as JSON', ['data' => $jsonData]);
                    return $jsonData;
                }
            }

            $object = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
            if ($object === false) {
                $errors = libxml_get_errors();
                $errorMessages = array_map(function($error) {
                    return $error->message;
                }, $errors);
                Log::error('XML parsing errors', ['errors' => $errorMessages]);
                throw new Exception('Failed to parse XML: ' . implode(', ', $errorMessages));
            }

            $arrayData = json_decode(json_encode($object), true);
            Log::info('✓ XML successfully parsed', [
                'data_keys' => array_keys($arrayData),
                'data' => $arrayData
            ]);

            return $arrayData;
        } catch (Exception $e) {
            Log::error('✗ XML/JSON parsing failed', [
                'error' => $e->getMessage(),
                'xml_sample' => substr($xml, 0, 500),
                'full_xml' => $xml
            ]);
            return ['error' => 'Invalid response format: ' . $e->getMessage()];
        }
    }
}
