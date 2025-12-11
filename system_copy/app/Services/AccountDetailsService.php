<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Member;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Str;

/**
 * Account Details Service - External API Client
 * 
 * This service consumes an external API to retrieve account details.
 * It handles authentication, signature generation, request formatting,
 * and response processing according to the API specification.
 * 
 * @package App\Services
 * @author NBC SACCO System
 * @version 1.0.0
 */
class AccountDetailsService
{
    private const LOG_CHANNEL = 'account_details_external';

    private const STATUS_CODES = [
        'SUCCESS' => 600,
        'ACCOUNT_NOT_FOUND' => 605,
        'INVALID_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'INTERNAL_ERROR' => 700
    ];

    private const HTTP_STATUS_MAP = [
        200 => self::STATUS_CODES['SUCCESS'],
        400 => self::STATUS_CODES['INVALID_REQUEST'],
        401 => self::STATUS_CODES['UNAUTHORIZED'],
        605 => self::STATUS_CODES['ACCOUNT_NOT_FOUND'],
        700 => self::STATUS_CODES['INTERNAL_ERROR']
    ];

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @var string
     */
    private string $privateKeyPath;

    /**
     * @var string
     */
    private string $channelName;

    /**
     * @var string
     */
    private string $channelCode;

    /**
     * @var int
     */
    private int $timeout;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUrl = config('services.account_details.base_url', env('ACCOUNT_DETAILS_BASE_URL'));
        $this->apiKey = config('services.account_details.api_key', env('ACCOUNT_DETAILS_API_KEY'));
        $this->privateKeyPath = config('services.account_details.private_key_path', env('ACCOUNT_DETAILS_PRIVATE_KEY_PATH'));
        $this->channelName = config('services.account_details.channel_name', env('ACCOUNT_DETAILS_CHANNEL_NAME', 'NBC_SACCOS'));
        $this->channelCode = config('services.account_details.channel_code', env('ACCOUNT_DETAILS_CHANNEL_CODE', 'NBC001'));
        $this->timeout = config('services.account_details.timeout', 30);

        $this->validateConfiguration();
    }

    /**
     * Get account details from external API
     *
     * @param string $accountNumber
     * @return array
     * @throws Exception
     */
    public function getAccountDetails(string $accountNumber): array
    {
        $requestId = $this->generateRequestId();
        $startTime = microtime(true);

        Log::channel(self::LOG_CHANNEL)->info('External account details request initiated', [
            'request_id' => $requestId,
            'account_number' => $accountNumber,
            'base_url' => $this->baseUrl
        ]);

        try {
            // Validate input
            $this->validateAccountNumber($accountNumber, $requestId);

            // Prepare request data
            $requestData = $this->prepareRequestData($accountNumber, $requestId);

            // Make API call
            $response = $this->makeApiCall($requestData, $requestId);

            // Process response
            $accountDetails = $this->processResponse($response, $requestId);

            // Log success
            $executionTime = (microtime(true) - $startTime) * 1000;
            Log::channel(self::LOG_CHANNEL)->info('External account details retrieved successfully', [
                'request_id' => $requestId,
                'account_number' => $accountNumber,
                'execution_time_ms' => round($executionTime, 2),
                'external_status_code' => $response->status(),
                'fresh_data' => true
            ]);

            return $accountDetails;

        } catch (Exception $e) {
            $executionTime = (microtime(true) - $startTime) * 1000;
            Log::channel(self::LOG_CHANNEL)->error('External account details request failed', [
                'request_id' => $requestId,
                'account_number' => $accountNumber,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'execution_time_ms' => round($executionTime, 2)
            ]);

            return $this->buildErrorResponse($e, $requestId);
        }
    }

    /**
     * Validate configuration
     *
     * @throws Exception
     */
    private function validateConfiguration(): void
    {
        $requiredConfigs = [
            'base_url' => $this->baseUrl,
            'api_key' => $this->apiKey,
            'private_key_path' => $this->privateKeyPath,
            'channel_name' => $this->channelName,
            'channel_code' => $this->channelCode
        ];

        foreach ($requiredConfigs as $config => $value) {
            if (empty($value)) {
                throw new Exception("Missing required configuration: {$config}", self::STATUS_CODES['INTERNAL_ERROR']);
            }
        }

        if (!file_exists($this->privateKeyPath)) {
            throw new Exception("Private key file not found: {$this->privateKeyPath}", self::STATUS_CODES['INTERNAL_ERROR']);
        }
    }

    /**
     * Validate account number
     *
     * @param string $accountNumber
     * @param string $requestId
     * @throws Exception
     */
    private function validateAccountNumber(string $accountNumber, string $requestId): void
    {
        $validator = Validator::make(['accountNumber' => $accountNumber], [
            'accountNumber' => 'required|string|min:1|max:50'
        ]);

        if ($validator->fails()) {
            throw new Exception('Invalid account number: ' . json_encode($validator->errors()), self::STATUS_CODES['INVALID_REQUEST']);
        }

        if (!$this->isValidAccountNumber($accountNumber)) {
            throw new Exception('Invalid account number format', self::STATUS_CODES['INVALID_REQUEST']);
        }
    }

    /**
     * Prepare request data
     *
     * @param string $accountNumber
     * @param string $requestId
     * @return array
     */
    private function prepareRequestData(string $accountNumber, string $requestId): array
    {
        $requestBody = [
            'accountNumber' => $accountNumber
        ];

        $timestamp = Carbon::now()->utc()->toISOString();
        $signature = $this->generateSignature($requestBody, $requestId);

        $headers = [
            'X-API-Key' => $this->apiKey,
            'Signature' => $signature,
            'X-Timestamp' => $timestamp,
            'X-Channel-Name' => $this->channelName,
            'X-Channel-Code' => $this->channelCode,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        Log::channel(self::LOG_CHANNEL)->info('Request data prepared', [
            'request_id' => $requestId,
            'account_number' => $accountNumber,
            'timestamp' => $timestamp,
            'channel_name' => $this->channelName,
            'channel_code' => $this->channelCode
        ]);

        return [
            'url' => $this->baseUrl . '/api/v1/account-lookup',
            'method' => 'POST',
            'headers' => $headers,
            'body' => $requestBody
        ];
    }

    /**
     * Generate RSA signature
     *
     * @param array $requestBody
     * @param string $requestId
     * @return string
     * @throws Exception
     */
    private function generateSignature(array $requestBody, string $requestId): string
    {
        try {
            $jsonBody = json_encode($requestBody);
            
            if (!$jsonBody) {
                throw new Exception('Failed to encode request body to JSON');
            }

            $privateKey = file_get_contents($this->privateKeyPath);
            if (!$privateKey) {
                throw new Exception('Failed to read private key file');
            }

            $privateKeyResource = openssl_pkey_get_private($privateKey);
            if (!$privateKeyResource) {
                throw new Exception('Failed to load private key: ' . openssl_error_string());
            }

            $signature = '';
            $signResult = openssl_sign($jsonBody, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
            
            if (!$signResult) {
                throw new Exception('Failed to generate signature: ' . openssl_error_string());
            }

            $base64Signature = base64_encode($signature);
            
            openssl_free_key($privateKeyResource);

            Log::channel(self::LOG_CHANNEL)->info('Signature generated successfully', [
                'request_id' => $requestId,
                'body_length' => strlen($jsonBody),
                'signature_length' => strlen($base64Signature)
            ]);

            return $base64Signature;

        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error('Signature generation failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Make API call to external service
     *
     * @param array $requestData
     * @param string $requestId
     * @return \Illuminate\Http\Client\Response
     * @throws Exception
     */
    private function makeApiCall(array $requestData, string $requestId): \Illuminate\Http\Client\Response
    {
        Log::channel(self::LOG_CHANNEL)->info('Making external API call', [
            'request_id' => $requestId,
            'url' => $requestData['url'],
            'method' => $requestData['method'],
            'headers' => array_merge($requestData['headers'], [
                'X-API-Key' => substr($requestData['headers']['X-API-Key'], 0, 20) . '...',
                'Signature' => substr($requestData['headers']['Signature'], 0, 50) . '...'
            ]),
            'request_body' => json_encode($requestData['body'])
        ]);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders($requestData['headers'])
                ->post($requestData['url'], $requestData['body']);

            // Log detailed response information
            Log::channel(self::LOG_CHANNEL)->info('External API response received', [
                'request_id' => $requestId,
                'status_code' => $response->status(),
                'response_size' => strlen($response->body()),
                'raw_response_body' => $response->body(),
                'response_headers' => $response->headers(),
                'is_successful' => $response->successful(),
                'is_client_error' => $response->clientError(),
                'is_server_error' => $response->serverError()
            ]);

            return $response;

        } catch (Exception $e) {
            Log::channel(self::LOG_CHANNEL)->error('External API call failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'url' => $requestData['url'],
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('External API call failed: ' . $e->getMessage(), self::STATUS_CODES['INTERNAL_ERROR']);
        }
    }

    /**
     * Process API response
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $requestId
     * @return array
     * @throws Exception
     */
    private function processResponse(\Illuminate\Http\Client\Response $response, string $requestId): array
    {
        $statusCode = $response->status();
        $responseBody = $response->body();

        Log::channel(self::LOG_CHANNEL)->info('Processing external API response', [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response_body_length' => strlen($responseBody),
            'raw_response_body' => $responseBody
        ]);

        // Handle HTTP errors
        if ($statusCode >= 400) {
            Log::channel(self::LOG_CHANNEL)->warning('HTTP error status detected', [
                'request_id' => $requestId,
                'status_code' => $statusCode,
                'response_body' => $responseBody
            ]);
            $this->handleHttpError($response, $requestId);
        }

        // Parse JSON response
        $responseData = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::channel(self::LOG_CHANNEL)->error('JSON decode error', [
                'request_id' => $requestId,
                'json_error' => json_last_error_msg(),
                'raw_body' => substr($responseBody, 0, 1000)
            ]);
            throw new Exception('Invalid JSON response from external API: ' . json_last_error_msg(), self::STATUS_CODES['INTERNAL_ERROR']);
        }

        Log::channel(self::LOG_CHANNEL)->info('JSON decoded successfully', [
            'request_id' => $requestId,
            'decoded_data' => json_encode($responseData, JSON_PRETTY_PRINT),
            'data_keys' => array_keys($responseData),
            'has_body' => isset($responseData['body']),
            'body_keys' => isset($responseData['body']) ? array_keys($responseData['body']) : []
        ]);

        // Validate response structure
        $this->validateResponseStructure($responseData, $requestId);

        // Map external status codes to internal ones
        $mappedStatusCode = self::HTTP_STATUS_MAP[$statusCode] ?? self::STATUS_CODES['INTERNAL_ERROR'];

        $processedResponse = [
            'statusCode' => $mappedStatusCode,
            'message' => $responseData['message'] ?? 'SUCCESS',
            'body' => $responseData['body'] ?? []
        ];

        Log::channel(self::LOG_CHANNEL)->info('Response processed successfully', [
            'request_id' => $requestId,
            'original_http_status' => $statusCode,
            'mapped_status_code' => $mappedStatusCode,
            'response_message' => $responseData['message'] ?? 'N/A',
            'has_available_balance' => isset($responseData['body']['availableBalance']) ? 'YES' : 'NO',
            'available_balance' => $responseData['body']['availableBalance'] ?? 'NOT_SET',
            'body_structure' => json_encode($responseData['body'] ?? [], JSON_PRETTY_PRINT),
            'final_processed_response' => json_encode($processedResponse, JSON_PRETTY_PRINT)
        ]);

        return $processedResponse;
    }

    /**
     * Handle HTTP errors
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $requestId
     * @throws Exception
     */
    private function handleHttpError(\Illuminate\Http\Client\Response $response, string $requestId): void
    {
        $statusCode = $response->status();
        $responseBody = $response->body();

        Log::channel(self::LOG_CHANNEL)->warning('External API returned HTTP error', [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response_body' => $responseBody
        ]);

        // Try to parse error response
        $errorData = json_decode($responseBody, true);
        
        if ($errorData && isset($errorData['message'])) {
            throw new Exception($errorData['message'], $statusCode);
        }

        // Default error messages based on status code
        $errorMessages = [
            400 => 'Bad request to external API',
            401 => 'Unauthorized access to external API',
            403 => 'Forbidden access to external API',
            404 => 'External API endpoint not found',
            500 => 'Internal server error in external API',
            502 => 'Bad gateway to external API',
            503 => 'External API service unavailable',
            504 => 'Gateway timeout to external API'
        ];

        $message = $errorMessages[$statusCode] ?? "External API error with status code: {$statusCode}";
        throw new Exception($message, $statusCode);
    }

    /**
     * Validate response structure
     *
     * @param array $responseData
     * @param string $requestId
     * @throws Exception
     */
    private function validateResponseStructure(array $responseData, string $requestId): void
    {
        $requiredFields = ['statusCode', 'message'];
        
        foreach ($requiredFields as $field) {
            if (!isset($responseData[$field])) {
                throw new Exception("Missing required field in response: {$field}", self::STATUS_CODES['INTERNAL_ERROR']);
            }
        }

        // Validate statusCode is numeric
        if (!is_numeric($responseData['statusCode'])) {
            throw new Exception('Invalid statusCode in response: must be numeric', self::STATUS_CODES['INTERNAL_ERROR']);
        }

        Log::channel(self::LOG_CHANNEL)->info('Response structure validated', [
            'request_id' => $requestId,
            'has_body' => isset($responseData['body'])
        ]);
    }

    /**
     * Build error response
     *
     * @param Exception $exception
     * @param string $requestId
     * @return array
     */
    private function buildErrorResponse(Exception $exception, string $requestId): array
    {
        $errorCode = $exception->getCode();
        $statusCode = $this->mapExceptionCodeToStatusCode($errorCode);

        $errorResponse = [
            'statusCode' => $statusCode,
            'message' => $exception->getMessage(),
            'body' => []
        ];

        Log::channel(self::LOG_CHANNEL)->error('Building error response', [
            'request_id' => $requestId,
            'exception_class' => get_class($exception),
            'exception_code' => $errorCode,
            'mapped_status_code' => $statusCode,
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'error_response' => json_encode($errorResponse, JSON_PRETTY_PRINT)
        ]);

        return $errorResponse;
    }

    /**
     * Map exception code to API status code
     *
     * @param int $exceptionCode
     * @return int
     */
    private function mapExceptionCodeToStatusCode(int $exceptionCode): int
    {
        $codeMap = [
            self::STATUS_CODES['ACCOUNT_NOT_FOUND'] => self::STATUS_CODES['ACCOUNT_NOT_FOUND'],
            self::STATUS_CODES['INVALID_REQUEST'] => self::STATUS_CODES['INVALID_REQUEST'],
            self::STATUS_CODES['UNAUTHORIZED'] => self::STATUS_CODES['UNAUTHORIZED'],
            self::STATUS_CODES['INTERNAL_ERROR'] => self::STATUS_CODES['INTERNAL_ERROR']
        ];

        return $codeMap[$exceptionCode] ?? self::STATUS_CODES['INTERNAL_ERROR'];
    }

    /**
     * Generate unique request ID
     *
     * @return string
     */
    private function generateRequestId(): string
    {
        return 'ext_req_' . time() . '_' . Str::random(8);
    }

    /**
     * Validate account number format
     *
     * @param string $accountNumber
     * @return bool
     */
    private function isValidAccountNumber(string $accountNumber): bool
    {
        return !empty($accountNumber) && 
               strlen($accountNumber) >= 1 && 
               strlen($accountNumber) <= 50 &&
               preg_match('/^[A-Za-z0-9\-_]+$/', $accountNumber);
    }

    /**
     * Get service statistics
     *
     * @return array
     */
    public function getServiceStatistics(): array
    {
        $stats = [
            'base_url' => $this->baseUrl,
            'channel_name' => $this->channelName,
            'channel_code' => $this->channelCode,
            'timeout' => $this->timeout,
            'fresh_data_enabled' => true,
            'total_requests' => Cache::get('external_account_details_total_requests', 0),
            'errors' => Cache::get('external_account_details_errors', 0)
        ];

        return $stats;
    }

    /**
     * Test external API connectivity
     *
     * @return array
     */
    public function testConnectivity(): array
    {
        $requestId = $this->generateRequestId();
        
        try {
            $testData = $this->prepareRequestData('TEST123', $requestId);
            
            $response = Http::timeout(10)
                ->withHeaders($testData['headers'])
                ->post($testData['url'], $testData['body']);

            return [
                'success' => true,
                'status_code' => $response->status(),
                'response_time_ms' => $response->handlerStats()['total_time'] * 1000 ?? 0,
                'message' => 'External API is accessible'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'External API connectivity test failed'
            ];
        }
    }
} 