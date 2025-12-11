<?php

namespace App\Services\NbcPayments;

use Exception;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;


class NbcPaymentService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected string $privateKey;
    protected ?string $callbackUrl = null;

    public function __construct()
    {
        try {
            Log::info('Initializing NbcPaymentService with configuration values');

            $this->baseUrl = config('services.nbc_payments.base_url');
            $this->apiKey = config('services.nbc_payments.api_key');
            $this->clientId = config('services.nbc_payments.client_id');
            $this->privateKey = config('services.nbc_payments.private_key');
            $this->callbackUrl = config('services.nbc_payments.callback_url');

            if (empty($this->baseUrl) || empty($this->apiKey) || empty($this->clientId) || empty($this->privateKey)) {
                throw new \RuntimeException('Missing required configuration for NbcPaymentService');
            }

            Log::debug('NbcPaymentService configuration loaded', [
                'baseUrl' => $this->baseUrl,
                'clientId' => $this->clientId,
                'callbackUrl' => $this->callbackUrl
            ]);
        } catch (Exception $e) {
            Log::error('Failed to initialize NbcPaymentService: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process outgoing payment
     *
     * @param array $payload
     * @param string $serviceName
     * @return array
     * @throws \Exception
     */
    public function processPayment(array $payload, string $serviceName): array
    {
        $logContext = [
            'serviceName' => $serviceName,
            'clientId' => $this->clientId,
            'payload' => $this->sanitizePayloadForLogging($payload)
        ];

        Log::info('Starting payment processing', $logContext);

        try {
            // Step 1: Validate payload
            Log::debug(message: 'Validating payment payload');
            $this->validatePaymentPayload($payload, $serviceName);
            Log::debug('Payload validation successful');

            // Step 2: Build request payload
            Log::debug('Building payment request payload');
            $requestPayload = $this->buildPaymentPayload($payload, $serviceName);
            Log::debug('Request payload built', ['requestPayload' => $this->sanitizePayloadForLogging($requestPayload)]);

            // Step 3: Generate signature
            Log::debug('Generating digital signature');
            $timestamp = Carbon::now()->toIso8601String();
            $signature = $this->generateSignature($requestPayload);
            Log::debug('Signature generated successfully');

            // Step 4: Make API request
            Log::info('Making API request to NBC Payments', [
                'endpoint' => '/domestix/api/v2/outgoing-transfers',
                'timestamp' => $timestamp
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Signature' => $signature,
                'X-Trace-Uuid' => 'domestix-' . (string) Str::uuid(),
                'Timestamp' => $timestamp,
                'Client-Id' => $this->clientId,
                'Service-Name' => $serviceName,
            ])->withOptions(['verify' => false]) // disables SSL cert validation like `-k`
            ->post($this->baseUrl . '/domestix/api/v2/outgoing-transfers', $requestPayload);

            Log::debug('API request completed', [
                'statusCode' => $response->status(),
                'response' => $this->sanitizeResponseForLogging($response->json())
            ]);

            // Step 5: Process response
            Log::debug('Processing API response');
            $result = $this->processResponse($response);
            Log::info('Payment processing completed', ['success' => $result['success']]);

            return $result;

        } catch (InvalidArgumentException $e) {
            Log::error('Invalid payment payload: ' . $e->getMessage(), $logContext);
            throw new Exception('Invalid payment request: ' . $e->getMessage(), 400, $e);
        } catch (Exception $e) {
            Log::error('NBC Payment processing failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to process payment request: ' . $e->getMessage(), 500, $e);
        }
    }




    protected string $endpoint = 'https://22.32.245.67:443/domestix/api/v2/outgoing-transfers';

    public function sendTransfer(array $payload): array
    {
        $timestamp = Carbon::now()->toIso8601String();
        
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Trace-Uuid' => 'domestix-' . (string) Str::uuid(),
            'Signature' => $this->generateSignature($payload),
            'X-Api-Key' => $this->apiKey,
            'Timestamp' => $timestamp,
            'Client-Id' => $this->clientId,
        ];

        try {
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false]) // disables SSL cert validation like `-k`
                ->post($this->endpoint, $payload);

            return [
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }



    /**
     * Validate the payment payload
     *
     * @param array $payload
     * @param string $serviceName
     * @throws \InvalidArgumentException
     */
    protected function validatePaymentPayload(array $payload, string $serviceName): void
    {
        Log::debug('Starting payload validation', ['serviceName' => $serviceName]);

        $requiredFields = [
            'payerDetails' => [
                'identifierType',
                'identifier',
                'phoneNumber',
                'initiatorId',
                'branchCode',
                'fspId',
                'fullName',
                'accountCategory',
                'accountType',
                'identity' => ['type', 'value']
            ],
            'payeeDetails' => [
                'identifierType',
                'identifier',
                'fspId',
                'destinationFsp',
                'fullName',
                'accountCategory',
                'accountType',
                'identity' => ['type', 'value']
            ],
            'transactionDetails' => [
                'debitAmount',
                'debitCurrency',
                'creditAmount',
                'creditCurrency'
            ],
            'remarks'
        ];

        // Additional fields for merchant payments
        if ($serviceName === 'TIPS_B2B_OUTWARD_TRANSFER') {
            Log::debug('Adding merchant-specific validation rules');
            $requiredFields['payeeDetails']['additionalInfo'] = ['field62'];
            $requiredFields['transactionDetails'][] = 'productCode';
            $requiredFields['transactionDetails'][] = 'isServiceChargeApplicable';
            $requiredFields['transactionDetails'][] = 'serviceChargeBearer';
        }

        $this->validateNestedFields($payload, $requiredFields);

        // Validate service name
        $validServiceNames = ['TIPS_B2W_TRANSFER', 'TIPS_B2B_OUTWARD_TRANSFER'];
        if (!in_array($serviceName, $validServiceNames)) {
            $errorMsg = "Invalid serviceName. Must be one of: " . implode(', ', $validServiceNames);
            Log::error($errorMsg, ['serviceName' => $serviceName]);
            throw new \InvalidArgumentException($errorMsg);
        }

        // Validate account categories
        $validCategories = ['PERSON', 'BUSINESS', 'MERCHANT'];
        if (!in_array($payload['payerDetails']['accountCategory'], $validCategories)) {
            $errorMsg = "Invalid payer accountCategory. Must be one of: " . implode(', ', $validCategories);
            Log::error($errorMsg, ['accountCategory' => $payload['payerDetails']['accountCategory']]);
            throw new \InvalidArgumentException($errorMsg);
        }

        if (!in_array($payload['payeeDetails']['accountCategory'], $validCategories)) {
            $errorMsg = "Invalid payee accountCategory. Must be one of: " . implode(', ', $validCategories);
            Log::error($errorMsg, ['accountCategory' => $payload['payeeDetails']['accountCategory']]);
            throw new \InvalidArgumentException($errorMsg);
        }

        // Validate account types
        $validTypes = ['BANK', 'WALLET'];
        if (!in_array($payload['payerDetails']['accountType'], $validTypes)) {
            $errorMsg = "Invalid payer accountType. Must be one of: " . implode(', ', $validTypes);
            Log::error($errorMsg, ['accountType' => $payload['payerDetails']['accountType']]);
            throw new \InvalidArgumentException($errorMsg);
        }

        if (!in_array($payload['payeeDetails']['accountType'], $validTypes)) {
            $errorMsg = "Invalid payee accountType. Must be one of: " . implode(', ', $validTypes);
            Log::error($errorMsg, ['accountType' => $payload['payeeDetails']['accountType']]);
            throw new \InvalidArgumentException($errorMsg);
        }

        Log::debug('Payload validation completed successfully');
    }

    /**
     * Recursively validate nested fields
     *
     * @param array $data
     * @param array $fields
     * @param string $path
     * @throws \InvalidArgumentException
     */
    protected function validateNestedFields(array $data, array $fields, string $path = ''): void
    {
        foreach ($fields as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (is_array($value)) {
                if (!isset($data[$key])) {
                    $errorMsg = "Missing required field: {$currentPath}";
                    Log::error($errorMsg, ['missingField' => $currentPath]);
                    throw new \InvalidArgumentException($errorMsg);
                }
                $this->validateNestedFields($data[$key], $value, $currentPath);
            } else {
                if (!isset($data[$value])) {
                    $errorMsg = "Missing required field: {$currentPath}.{$value}";
                    Log::error($errorMsg, ['missingField' => "{$currentPath}.{$value}"]);
                    throw new \InvalidArgumentException($errorMsg);
                }
            }
        }
    }

    /**
     * Build the payment payload structure
     *
     * @param array $payload
     * @param string $serviceName
     * @return array
     */
    protected function buildPaymentPayload(array $payload, string $serviceName): array
    {
        Log::debug('Building payment payload structure');

        $builtPayload = [
            'serviceName' => $serviceName,
            'clientId' => $this->clientId,
            'clientRef' => $payload['clientRef'] ?? $this->generateClientRef(),
            'customerRef' => $payload['customerRef'] ?? $this->generateCustomerRef(),
            'lookupRef' => $payload['lookupRef'] ?? $this->generateClientRef(),
            'timestamp' => Carbon::now()->toIso8601String(),
            'callbackUrl' => $payload['callbackUrl'] ?? $this->callbackUrl,
            'payerDetails' => $payload['payerDetails'],
            'payeeDetails' => $payload['payeeDetails'],
            'transactionDetails' => $payload['transactionDetails'],
            'remarks' => $payload['remarks'],
        ];

        Log::debug('Payment payload built successfully');
        return $builtPayload;
    }

    /**
     * Generate a unique client reference
     *
     * @return string
     */
    protected function generateClientRef(): string
    {
        // Generate a shorter reference: clientId + timestamp (YYYYMMDDHH) + 2 random chars
        $ref = $this->clientId . date('YmdH') . Str::random(2);
        Log::debug('Generated client reference', ['clientRef' => $ref]);
        return $ref;
    }

    /**
     * Generate a customer reference
     *
     * @return string
     */
    protected function generateCustomerRef(): string
    {
        $ref = 'CUST' . date('YmdHis') . Str::random(4);
        Log::debug('Generated customer reference', ['customerRef' => $ref]);
        return $ref;
    }

    /**
     * Generate digital signature for the payload
     *
     * @param array $payload
     * @return string
     * @throws \Exception
     */
    protected function generateSignature(array $payload): string
    {
        Log::debug('Starting signature generation');

        try {
            Log::debug('Loading private key for signature generation');
            $privateKeyContent = Storage::get('keys/private_key.pem');

            if (!$privateKeyContent) {
                throw new Exception('Private key file not found or empty');
            }

            $privateKey = openssl_pkey_get_private($privateKeyContent);
            if (!$privateKey) {
                $errorMsg = 'Failed to load private key: ' . openssl_error_string();
                Log::error($errorMsg);
                throw new Exception($errorMsg);
            }

            $jsonPayload = json_encode($payload);
            if ($jsonPayload === false) {
                throw new Exception('Failed to JSON encode payload: ' . json_last_error_msg());
            }

            $signatureGenerated = openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            if (!$signatureGenerated) {
                $errorMsg = 'Failed to generate signature: ' . openssl_error_string();
                Log::error($errorMsg);
                throw new Exception($errorMsg);
            }

            $encodedSignature = base64_encode($signature);
            Log::debug('Signature generated successfully');

            return $encodedSignature;
        } catch (Exception $e) {
            Log::error('Signature generation failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Failed to generate digital signature: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Process the API response
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return array
     * @throws \Exception
     */
    protected function processResponse($response): array
    {
        $statusCode = $response->status();
        Log::debug('Processing API response', ['statusCode' => $statusCode]);

        try {
            $responseData = $response->json();

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON response', [
                    'error' => json_last_error_msg(),
                    'response' => $response->body()
                ]);
                return [
                    'success' => false,
                    'error_code' => 500,
                    'message' => 'Invalid response format from NBC API',
                    'data' => null
                ];
            }

            Log::debug('API response data', $this->sanitizeResponseForLogging($responseData));

            // Handle both 200 and non-200 status codes
            if ($responseData['statusCode'] === 600) {
                Log::info('Payment processed successfully', [
                    'engineRef' => $responseData['engineRef'] ?? null,
                    'message' => $responseData['message'] ?? ''
                ]);

                return [
                    'success' => true,
                    'data' => $responseData,
                    'message' => $responseData['message'] ?? 'Payment processed successfully',
                    'engineRef' => $responseData['engineRef'] ?? null
                ];
            }

            // Handle error responses
            $errorMessage = $responseData['message'] ?? 'Unknown error occurred';
            if (isset($responseData['body']) && is_array($responseData['body'])) {
                $errorMessage = implode(', ', $responseData['body']);
            }

            Log::warning('Payment processing failed', [
                'error_code' => $responseData['statusCode'],
                'message' => $errorMessage,
                'errors' => $responseData['body'] ?? null
            ]);

            return [
                'success' => false,
                'error_code' => $responseData['statusCode'],
                'message' => $errorMessage,
                'engineRef' => $responseData['engineRef'] ?? null,
                'data' => $responseData['body'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('Failed to process API response: ' . $e->getMessage(), [
                'statusCode' => $statusCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error_code' => 500,
                'message' => 'Failed to process API response: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }







    /**
 * Helper method for bank-to-bank transfer lookup
 *
 * @param string $accountNumber
 * @param string $bankCode
 * @param string $debitAccount
 * @param string $amount
 * @param string $debitAccountCategory
 * @return array
 */
public function bankToBankLookup(
    string $accountNumber,
    string $bankCode,
    string $debitAccount,
    string $amount,
    string $debitAccountCategory = 'PERSON'
): array {
    return $this->lookup([
        'identifierType' => 'BANK',
        'identifier' => $accountNumber,
        'destinationFsp' => $bankCode,
        'debitAccount' => $debitAccount,
        'debitAccountCurrency' => 'TZS',
        'debitAccountBranchCode' => substr($debitAccount, 0, 3), // Assuming first 3 digits are branch code
        'amount' => $amount,
        'debitAccountCategory' => $debitAccountCategory
    ]);
}


/**
 * Process Bank-to-Bank transfer
 *
 * @param array $lookupResponse
 * @param string $debitAccount
 * @param string $amount
 * @param string $phoneNumber
 * @param string $initiatorId
 * @param string $remarks
 * @param string $productCode
 * @return array
 */
public function processBankToBankTransfer(
    array $lookupResponse,
    string $debitAccount,
    string $amount,
    string $phoneNumber,
    string $initiatorId,
    string $remarks,
    string $productCode = 'FTLC'
): array {
    $payload = [
        'payerDetails' => [
            'identifierType' => 'BANK',
            'identifier' => $debitAccount,
            'phoneNumber' => $phoneNumber,
            'initiatorId' => $initiatorId,
            'branchCode' => substr($debitAccount, 0, 3),
            'fspId' => '015', // NBC's FSP ID
            'fullName' => 'Customer Name', // Should be retrieved from user data
            'accountCategory' => $lookupResponse['debitAccountCategory'] ?? 'PERSON',
            'accountType' => 'BANK',
            'identity' => [
                'type' => 'NIN',
                'value' => '123456789012345678' // Should be retrieved from user data
            ]
        ],
        'payeeDetails' => [
            'identifierType' => 'BANK',
            'identifier' => $lookupResponse['body']['identifier'],
            'fspId' => $lookupResponse['body']['fspId'],
            'destinationFsp' => $lookupResponse['body']['destinationFsp'] ?? $lookupResponse['body']['fspId'],
            'fullName' => $lookupResponse['body']['fullName'],
            'accountCategory' => $lookupResponse['body']['accountCategory'],
            'accountType' => 'BANK',
            'identity' => [
                'type' => $lookupResponse['body']['identity']['type'] ?? '',
                'value' => $lookupResponse['body']['identity']['value'] ?? ''
            ]
        ],
        'transactionDetails' => [
            'debitAmount' => $amount,
            'debitCurrency' => 'TZS',
            'creditAmount' => $amount,
            'creditCurrency' => 'TZS',
            'productCode' => $productCode,
            'isServiceChargeApplicable' => true,
            'serviceChargeBearer' => 'OUR'
        ],
        'remarks' => $remarks,
        'lookupRef' => $lookupResponse['clientRef']
    ];

    // Add additionalInfo for merchant payments
    //if ($lookupResponse['body']['accountCategory'] === 'MERCHANT') {
        $payload['payeeDetails']['additionalInfo'] = [
            'field62' => $lookupResponse['body']['identifier'] . $initiatorId
        ];
    //}

    return $this->processPayment($payload, 'TIPS_B2B_OUTWARD_TRANSFER');
}




    /**
     * Process Bank-to-Wallet (B2W) transfer
     *
     * @param array $lookupResponse
     * @param string $debitAccount
     * @param string $amount
     * @param string $phoneNumber
     * @param string $initiatorId
     * @param string $remarks
     * @return array
     */
    public function processBankToWalletTransfer(
        array $lookupResponse,
        string $debitAccount,
        string $amount,
        string $phoneNumber,
        string $initiatorId,
        string $remarks,
        string $payerName = null
    ): array {
        Log::info('Processing Bank-to-Wallet transfer', [
            'debitAccount' => $this->maskAccountNumber($debitAccount),
            'amount' => $amount,
            'phoneNumber' => $this->maskPhoneNumber($phoneNumber),
            'initiatorId' => $initiatorId
        ]);

        try {
            // Format phone number to international format
            $formattedPhoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Get the base URL dynamically
            $baseUrl = Request::root();

            $payload = [
                'serviceName' => 'TIPS_B2W_TRANSFER',
                'clientId' => $this->clientId,
                'clientRef' => $this->generateClientRef(),
                'customerRef' => $this->generateCustomerRef(),
                'lookupRef' => $lookupResponse['clientRef'],
                'timestamp' => Carbon::now()->toIso8601String(),
                'callbackUrl' => $baseUrl . '/api/v1/nbc-payments/callback',
                'payerDetails' => [
                    'identifierType' => 'BANK',
                    'identifier' => $debitAccount,
                    'phoneNumber' => $formattedPhoneNumber,
                    'initiatorId' => $initiatorId,
                    'branchCode' => substr($debitAccount, 0, 3),
                    'fspId' => '015', // NBC's FSP ID
                    'fullName' => $payerName ?: 'NBC Customer', // Use provided name or default
                    'accountCategory' => 'PERSON',
                    'accountType' => 'BANK',
                    'identity' => [
                        'type' => '',
                        'value' => ''
                    ]
                ],
                'payeeDetails' => [
                    'identifierType' => $lookupResponse['body']['identifierType'],
                    'identifier' => $lookupResponse['body']['identifier'],
                    'fspId' => $lookupResponse['body']['fspId'],
                    'destinationFsp' => $lookupResponse['body']['destinationFsp'] ?? $lookupResponse['body']['fspId'],
                    'fullName' => $lookupResponse['body']['fullName'],
                    'accountCategory' => $lookupResponse['body']['accountCategory'],
                    'accountType' => $lookupResponse['body']['accountType'],
                    'identity' => [
                        'type' => '',
                        'value' => ''
                    ]
                ],
                'transactionDetails' => [
                    'debitAmount' => $amount,
                    'debitCurrency' => 'TZS',
                    'creditAmount' => $amount,
                    'creditCurrency' => 'TZS',
                    'productCode' => '',
                    'isServiceChargeApplicable' => true,
                    'serviceChargeBearer' => 'OUR'
                ],
                'remarks' => $remarks ?: 'Test Outgoing Transfer Initiated from NLCBTZTX'
            ];

            return $this->processPayment($payload, 'TIPS_B2W_TRANSFER');

        } catch (Exception $e) {
            Log::error('Bank-to-Wallet transfer failed: ' . $e->getMessage(), [
                'debitAccount' => $this->maskAccountNumber($debitAccount),
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Format phone number to international format (255XXXXXXXXX)
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If number starts with 0, replace with 255
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = '255' . substr($phoneNumber, 1);
        }

        // If number doesn't start with 255, add it
        if (substr($phoneNumber, 0, 3) !== '255') {
            $phoneNumber = '255' . $phoneNumber;
        }

        return $phoneNumber;
    }

    /**
     * Process Merchant Payment (TANQR)
     *
     * @param array $lookupResponse
     * @param string $debitAccount
     * @param string $amount
     * @param string $phoneNumber
     * @param string $initiatorId
     * @param string $remarks
     * @param string $merchantId
     * @param string $field62
     * @return array
     */
    public function processMerchantPayment(
        array $lookupResponse,
        string $debitAccount,
        string $amount,
        string $phoneNumber,
        string $initiatorId,
        string $remarks,
        string $merchantId,
        string $field62
    ): array {
        Log::info('Processing Merchant payment', [
            'debitAccount' => $this->maskAccountNumber($debitAccount),
            'amount' => $amount,
            'phoneNumber' => $this->maskPhoneNumber($phoneNumber),
            'merchantId' => $merchantId,
            'initiatorId' => $initiatorId
        ]);

        try {
            $payload = [
                'payerDetails' => [
                    'identifierType' => 'BANK',
                    'identifier' => $debitAccount,
                    'phoneNumber' => $phoneNumber,
                    'initiatorId' => $initiatorId,
                    'branchCode' => substr($debitAccount, 0, 3),
                    'fspId' => '015', // NBC's FSP ID
                    'fullName' => 'Business Name', // Should be retrieved from business data
                    'accountCategory' => 'BUSINESS',
                    'accountType' => 'BANK',
                    'identity' => [
                        'type' => 'TIN',
                        'value' => '123456789' // Should be retrieved from business data
                    ]
                ],
                'payeeDetails' => [
                    'identifierType' => 'BUSINESS',
                    'identifier' => $merchantId,
                    'fspId' => $lookupResponse['body']['fspId'],
                    'destinationFsp' => $lookupResponse['body']['destinationFsp'] ?? $lookupResponse['body']['fspId'],
                    'fullName' => $lookupResponse['body']['fullName'],
                    'accountCategory' => 'MERCHANT',
                    'accountType' => 'BANK',
                    'identity' => [
                        'type' => 'TIN',
                        'value' => $lookupResponse['body']['identity']['value'] ?? ''
                    ],
                    'additionalInfo' => [
                        'field62' => $field62
                    ]
                ],
                'transactionDetails' => [
                    'debitAmount' => $amount,
                    'debitCurrency' => 'TZS',
                    'creditAmount' => $amount,
                    'creditCurrency' => 'TZS',
                    'productCode' => 'FTLC',
                    'isServiceChargeApplicable' => true,
                    'serviceChargeBearer' => 'OUR'
                ],
                'remarks' => $remarks,
                'lookupRef' => $lookupResponse['clientRef']
            ];

            return $this->processPayment($payload, 'TIPS_B2B_OUTWARD_TRANSFER');
        } catch (Exception $e) {
            Log::error('Merchant payment failed: ' . $e->getMessage(), [
                'debitAccount' => $this->maskAccountNumber($debitAccount),
                'amount' => $amount,
                'merchantId' => $merchantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize sensitive data in payload for logging
     *
     * @param array $payload
     * @return array
     */
    protected function sanitizePayloadForLogging(array $payload): array
    {
        $sanitized = $payload;

        // Mask sensitive payer details
        if (isset($sanitized['payerDetails'])) {
            if (isset($sanitized['payerDetails']['identifier'])) {
                $sanitized['payerDetails']['identifier'] = $this->maskAccountNumber($sanitized['payerDetails']['identifier']);
            }
            if (isset($sanitized['payerDetails']['phoneNumber'])) {
                $sanitized['payerDetails']['phoneNumber'] = $this->maskPhoneNumber($sanitized['payerDetails']['phoneNumber']);
            }
            if (isset($sanitized['payerDetails']['identity']['value'])) {
                $sanitized['payerDetails']['identity']['value'] = '***MASKED***';
            }
        }

        // Mask sensitive payee details
        if (isset($sanitized['payeeDetails'])) {
            if (isset($sanitized['payeeDetails']['identifier'])) {
                $sanitized['payeeDetails']['identifier'] = '***MASKED***';
            }
            if (isset($sanitized['payeeDetails']['identity']['value'])) {
                $sanitized['payeeDetails']['identity']['value'] = '***MASKED***';
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize sensitive data in response for logging
     *
     * @param array|null $response
     * @return array|null
     */
    protected function sanitizeResponseForLogging(?array $response): ?array
    {
        if ($response === null) {
            return null;
        }

        $sanitized = $response;

        // Mask sensitive data in response body
        if (isset($sanitized['body']['identifier'])) {
            $sanitized['body']['identifier'] = '***MASKED***';
        }
        if (isset($sanitized['body']['identity']['value'])) {
            $sanitized['body']['identity']['value'] = '***MASKED***';
        }

        return $sanitized;
    }

    /**
     * Mask account number for logging (show only first and last 4 digits)
     *
     * @param string $accountNumber
     * @return string
     */
    protected function maskAccountNumber(string $accountNumber): string
    {
        if (strlen($accountNumber) <= 8) {
            return '***' . substr($accountNumber, -4);
        }
        return substr($accountNumber, 0, 4) . '***' . substr($accountNumber, -4);
    }

    /**
     * Mask phone number for logging (show only last 4 digits)
     *
     * @param string $phoneNumber
     * @return string
     */
    protected function maskPhoneNumber(string $phoneNumber): string
    {
        return '***' . substr($phoneNumber, -4);
    }

    /**
     * Perform lookup operation
     *
     * @param array $payload
     * @return array
     */
    protected function lookup(array $payload): array
    {
        $timestamp = Carbon::now()->toIso8601String();
        $signature = $this->generateSignature($payload);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Signature' => $signature,
                'X-Trace-Uuid' => 'domestix-' . (string) Str::uuid(),
                'Timestamp' => $timestamp,
                'Client-Id' => $this->clientId,
                'Service-Name' => 'TIPS_LOOKUP',
            ])->withOptions(['verify' => false])
            ->post($this->baseUrl . '/domestix/api/v2/lookup', $payload);

            return [
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 500,
                'error' => $e->getMessage(),
            ];
        }
    }
}
