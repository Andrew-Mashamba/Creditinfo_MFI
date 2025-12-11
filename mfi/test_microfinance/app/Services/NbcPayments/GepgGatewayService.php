<?php

namespace App\Services\NbcPayments;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use phpseclib3\Crypt\PublicKeyLoader;

class GepgGatewayService
{
    protected $client;
    protected $baseUrl;
    protected $channelId;
    protected $channelName;
    protected $privateKey;
    protected $publicKey;
    protected $logger;

    // GEPG API Endpoints
    protected const ENDPOINT_BILL_QUERY = '/api/nbc-sg/v2/billquery';
    protected const ENDPOINT_BILL_PAY = '/api/nbc-sg/v2/bill-pay';
    protected const ENDPOINT_STATUS_CHECK = '/api/nbc-sg/v2/status-check';

    public function __construct(GepgLoggerService $logger)
    {
        $this->client = new Client([
            'verify' => config('gepg.verify_ssl', false)
        ]);
        $this->baseUrl = config('gepg.gateway_url');
        $this->channelId = config('gepg.channel_id');
        $this->channelName = config('gepg.channel_name');
        $this->logger = $logger;

        // Load keys from storage
        $privateKeyPath = storage_path('keys/private_key.pem');
        if (file_exists($privateKeyPath)) {
            $this->privateKey = file_get_contents($privateKeyPath);
        }
        
        $publicKeyPath = storage_path('keys/private.pem.pub');
        if (file_exists($publicKeyPath)) {
            $this->publicKey = file_get_contents($publicKeyPath);
        }
    }

    public function verifyControlNumber($controlNumber, $accountNo, $currency)
    {
        $startTime = microtime(true);
        
        try {
            $payload = [
                'GepgGatewayBillQryReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName,
                        'Service' => 'GEPG_INQ',
                    ],
                    'gepgBillQryReq' => [
                        'ChannelRef' => uniqid('inq_'),
                        'CustCtrNum' => $controlNumber,
                        'DebitAccountNo' => $accountNo,
                        'DebitAccountCurrency' => $currency,
                    ],
                ]
            ];

            $this->logger->logRequest('GEPG_INQ yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy', $payload);

            $response = $this->sendRequest($payload, 'GEPG_INQ', self::ENDPOINT_BILL_QUERY);
            
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->logResponse('GEPG_INQ xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', $response, $duration);

            // Check for error response
            if (isset($response['GepgGatewayBillQryResp']['BillHdr']['BillStsCode'])) {
                $statusCode = $response['GepgGatewayBillQryResp']['BillHdr']['BillStsCode'];
                $statusDesc = $response['GepgGatewayBillQryResp']['BillHdr']['BillStsDesc'] ?? 'Unknown error';

   //$this->logger->logResponse('GEPG_INQ qqqqqqqqqqqqqqqqqq', $statusCode, $statusDesc);
                
                // If status code indicates an error (not 0000)
                //if ($statusCode !== '0000') {
                    //throw new \Exception($statusDesc, (int)$statusCode);
               // }
            }

            return $response;
        } catch (\Exception $e) {
            $this->logger->logError('GEPG_INQ mmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmmm', $e, [
                'control_number' => $controlNumber,
                'account_no' => $accountNo,
                'currency' => $currency
            ]);
            throw $e;
        }
    }

    public function processPayment(array $paymentData, $isPrepaid = false)
    {
        $startTime = microtime(true);
        
        try {
            $service = $isPrepaid ? 'GEPG_PAY_QUOTE' : 'GEPG_PAY';

            $payload = [
                'GepgGatewayPaymentReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName,
                        'Service' => $service,
                    ],
                    'PmtHdr' => [
                        'ChannelRef' => $paymentData['channel_ref'],
                        'CbpGwRef' => $paymentData['cbp_gw_ref'],
                        'CustCtrNum' => $paymentData['control_number'],
                        'PayType' => $paymentData['pay_type'] ?? '1',
                        'EntryCnt' => count($paymentData['items']),
                        'BillStsCode' => $paymentData['status_code'],
                        'ResultUrl' => route('gepg.callback'),
                    ],
                    'PmtDtls' => [
                        'PmtDtl' => array_map(function($item) {
                            return [
                                'ChannelTrxId' => $item['channel_trx_id'],
                                'SpCode' => $item['sp_code'],
                                'PayRefId' => $item['pay_ref_id'],
                                'BillCtrNum' => $item['bill_ctr_num'],
                                'PaidAmt' => $item['paid_amt'],
                                'TrxDtTm' => $item['trx_dt_tm'],
                                'PayOpt' => $item['pay_opt'] ?? '1',
                                'PayPlan' => $item['pay_plan'] ?? '1',
                                'BillAmt' => $item['bill_amt'],
                                'MinPayAmt' => $item['min_pay_amt'] ?? '0.01',
                                'Ccy' => $item['ccy'],
                                'TrdPtyTrxId' => $item['trd_pty_trx_id'] ?? '',
                                'PyrCellNum' => $item['pyr_cell_num'] ?? '',
                                'PyrName' => $item['pyr_name'] ?? '',
                                'PyrEmail' => $item['pyr_email'] ?? '',
                                'PyrId' => $item['pyr_id'] ?? '',
                                'PyrIdType' => $item['pyr_id_type'] ?? '',
                                'DebitAmount' => $item['debit_amount'] ?? '',
                                'DealID' => $item['deal_id'] ?? '',
                                'Rsv1' => '',
                                'Rsv2' => '',
                                'Rsv3' => '',
                            ];
                        }, $paymentData['items'])
                    ],
                    'GepgGatewayProcessingInfo' => [
                        'BankType' => $paymentData['bank_type'] ?? 'ONUS',
                        'Forex' => $paymentData['forex'] ?? 'N',
                        'DebitAccountNo' => $paymentData['debit_account_no'],
                        'DebitAccountType' => $paymentData['debit_account_type'] ?? 'CASA',
                        'DebitAccountCurrency' => $paymentData['debit_account_currency'],
                        'DebitAmount' => $paymentData['debit_amount'] ?? '',
                        'CreditAccountNo' => $paymentData['credit_account_no'] ?? '',
                        'CreditCurrency' => $paymentData['credit_currency'] ?? '',
                        'CreditBankName' => $paymentData['credit_bank_name'] ?? '',
                        'CreditBankBenBic' => $paymentData['credit_bank_ben_bic'] ?? '',
                        'CreditAmount' => $paymentData['credit_amount'] ?? '',
                    ]
                ]
            ];

            $this->logger->logRequest($service, $payload);
            $this->logger->logTransaction($paymentData['channel_ref'], $paymentData);

            $response = $this->sendRequest($payload, $service, self::ENDPOINT_BILL_PAY);
            
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->logResponse($service, $response, $duration);

            return $response;
        } catch (\Exception $e) {
            $this->logger->logError($service, $e, [
                'payment_data' => $paymentData,
                'is_prepaid' => $isPrepaid
            ]);
            throw $e;
        }
    }

    public function checkStatus($channelRef, $cbpGwRef)
    {
        $startTime = microtime(true);
        
        try {
            $payload = [
                'GepgGatewayStatusCheckReq' => [
                    'GepgGatewayHdr' => [
                        'ChannelID' => $this->channelId,
                        'ChannelName' => $this->channelName,
                        'Service' => 'GEPG_STATUS',
                    ],
                    'StatusCheckReq' => [
                        'ChannelRef' => $channelRef,
                        'CbpGwRef' => $cbpGwRef,
                    ]
                ]
            ];

            $this->logger->logRequest('GEPG_STATUS', $payload);

            $response = $this->sendRequest($payload, 'GEPG_STATUS', self::ENDPOINT_STATUS_CHECK);
            
            $duration = (microtime(true) - $startTime) * 1000;
            $this->logger->logResponse('GEPG_STATUS', $response, $duration);

            return $response;
        } catch (\Exception $e) {
            $this->logger->logError('GEPG_STATUS', $e, [
                'channel_ref' => $channelRef,
                'cbp_gw_ref' => $cbpGwRef
            ]);
            throw $e;
        }
    }

    protected function sendRequest(array $payload, string $service, string $endpoint)
    {
        try {
            $xml = $this->arrayToXml($payload);
            $signedXml = $this->signXml($xml);

            $headers = [
                'Content-Type' => 'application/xml',
                'NBC-Authorization' => config('gepg.auth_token'),
            ];

            $url = $this->baseUrl . $endpoint;

            // Log the request URL and XML body
            Log::info('GEPG Request Details', [
                'service' => $service,
                'url' => $url,
                'headers' => $headers,
                'xml_body' => $signedXml,
            ]);
            $this->logger->logRequest($service, $payload, $headers);

            $response = $this->client->post($url, [
                'headers' => $headers,
                'body' => $signedXml,
            ]);

            $rawXmlResponse = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            // Log the raw XML response and HTTP status code
            Log::info('GEPG Response Details', [
                'service' => $service,
                'url' => $url,
                'status_code' => $statusCode,
                'raw_xml_response' => $rawXmlResponse,
            ]);

            $responseData = $this->parseResponse($rawXmlResponse);
            $this->logger->logResponse($service, $responseData);

            return $responseData;
        } catch (\Exception $e) {
            $this->logger->logError($service, $e, [
                'payload' => $payload,
                'url' => $this->baseUrl . $endpoint
            ]);
            throw $e;
        }
    }

    protected function signXml(string $xml): string
    {
        $privateKey = PublicKeyLoader::load($this->privateKey);
        $signature = $privateKey->sign($xml);

        $signedXml = new \SimpleXMLElement($xml);
        $signedXml->addChild('gepggatewaySignature', base64_encode($signature));

        return $signedXml->asXML();
    }

    protected function arrayToXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<GepgGateway/>');
        $this->addArrayToXml($xml, $data);
        return $xml->asXML();
    }

    protected function addArrayToXml(\SimpleXMLElement $object, array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $newObject = $object->addChild($key);
                $this->addArrayToXml($newObject, $value);
            } else {
                $object->addChild($key, htmlspecialchars($value));
            }
        }
    }

    protected function parseResponse(string $xmlResponse): array
    {
        try {
            $xml = simplexml_load_string($xmlResponse);
            if ($xml === false) {
                throw new \Exception('Failed to parse XML response');
            }

            // Log the raw response for debugging
            $this->logger->logResponse('GEPG_RESPONSE', [
                'raw_response' => $xmlResponse,
                'has_signature' => isset($xml->gepggatewaySignature)
            ]);

            // Convert XML to array without signature verification
            $json = json_encode($xml);
            if ($json === false) {
                throw new \Exception('Failed to encode XML to JSON: ' . json_last_error_msg());
            }

            $data = json_decode($json, true);
            if ($data === null) {
                throw new \Exception('Failed to decode JSON: ' . json_last_error_msg());
            }

            // Log the processed response
            $this->logger->logResponse('GEPG_PROCESSED', $data);

            return $data;
        } catch (\Exception $e) {
            $this->logger->logError('GEPG_PARSE', $e, [
                'xml_response' => $xmlResponse
            ]);
            throw $e;
        }
    }
}