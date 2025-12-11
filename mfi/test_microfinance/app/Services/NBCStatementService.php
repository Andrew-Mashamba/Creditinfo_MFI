<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

/**
 * NBC Statement Service
 *
 * Handles integration with NBC PVAS (Partners Values Added Services) API
 * for fetching account statements, balances, and transaction summaries.
 *
 * API Features:
 * - JWT/OAuth2 Bearer Authentication
 * - Digital Signature Verification (SHA256withRSA)
 * - Account Statement Retrieval
 * - Account Balance Retrieval
 * - Transaction Summary Retrieval
 */
class NBCStatementService
{
    /**
     * NBC API Base URL
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * NBC API Username (Channel ID)
     *
     * @var string
     */
    protected string $username;

    /**
     * NBC API Password (Secret)
     *
     * @var string
     */
    protected string $password;

    /**
     * Path to private key file for signing requests
     *
     * @var string
     */
    protected string $privateKeyPath;

    /**
     * Path to NBC public key for verifying responses
     *
     * @var string
     */
    protected string $publicKeyPath;

    /**
     * Mock mode flag
     *
     * @var bool
     */
    protected bool $mockMode;

    /**
     * SSL verification flag
     *
     * @var bool
     */
    protected bool $verifySSL;

    /**
     * JWT Token cache key
     *
     * @var string
     */
    const TOKEN_CACHE_KEY = 'nbc_statement_jwt_token';

    /**
     * Service Codes
     */
    const SERVICE_CODE_BALANCE = 'SC990001';
    const SERVICE_CODE_SUMMARY = 'SC990002';
    const SERVICE_CODE_STATEMENT = 'SC990003';

    /**
     * Response Status Codes
     */
    const STATUS_SUCCESS = 600;
    const STATUS_FAILED = 601;
    const STATUS_SIGNATURE_FAILURE = 602;
    const STATUS_AUTH_FAILED = 615;
    const STATUS_EXCEPTION = 699;
    const STATUS_UNAUTHORIZED = 613;

    public function __construct()
    {
        // Check if mock mode is enabled
        $this->mockMode = env('NBC_STATEMENT_MOCK_MODE', false);
        $this->verifySSL = env('NBC_STATEMENT_VERIFY_SSL', true);

        if ($this->mockMode) {
            // Use local mock endpoints (without trailing path since routes already include /api/...)
            $this->baseUrl = env('APP_URL', 'http://saccos-uat.intra.nbc.co.tz/nbc_saccos/core') . '/api/mock/nbc';
            $this->username = 'mock_user';
            $this->password = 'mock_password';
            $this->privateKeyPath = '';
            $this->publicKeyPath = '';

            Log::info('NBC Statement Service: Running in MOCK MODE', [
                'mock_base_url' => $this->baseUrl
            ]);
        } else {
            $this->baseUrl = config('services.nbc_statement.base_url') ?? '';
            $this->username = config('services.nbc_statement.username') ?? '';
            $this->password = config('services.nbc_statement.password') ?? '';
            $this->privateKeyPath = config('services.nbc_statement.private_key_path') ?? '';
            $this->publicKeyPath = config('services.nbc_statement.public_key_path') ?? '';
        }

        // Only validate if attempting to use the service
        // This allows the service to be instantiated even if not configured
    }

    /**
     * Validate required configuration
     *
     * @throws Exception
     */
    protected function validateConfiguration(): void
    {
        // Skip validation in mock mode
        if ($this->mockMode) {
            return;
        }

        $required = [
            'base_url' => $this->baseUrl,
            'username' => $this->username,
            'password' => $this->password,
            'private_key_path' => $this->privateKeyPath
        ];

        foreach ($required as $name => $value) {
            if (empty($value)) {
                throw new Exception("NBC Statement Service: Missing required configuration '{$name}'");
            }
        }

        if (!file_exists($this->privateKeyPath)) {
            throw new Exception("NBC Statement Service: Private key file not found at {$this->privateKeyPath}");
        }
    }

    /**
     * Fetch account statement for a specific date
     *
     * @param string $accountNumber NBC account number
     * @param string|Carbon $statementDate Date for statement (YYYY-MM-DD)
     * @param string|null $partnerRef Optional partner reference
     * @return array Statement transactions
     * @throws Exception
     */
    public function fetchAccountStatement(string $accountNumber, $statementDate, ?string $partnerRef = null): array
    {
        // Validate configuration before using
        $this->validateConfiguration();

        $dateString = $statementDate instanceof Carbon
            ? $statementDate->format('Y-m-d')
            : $statementDate;

        $partnerRef = $partnerRef ?? $this->generatePartnerRef();

        Log::info('NBC Statement API: Fetching account statement', [
            'account_number' => $accountNumber,
            'statement_date' => $dateString,
            'partner_ref' => $partnerRef
        ]);

        try {
            // Authenticate and get JWT token
            $token = $this->authenticate();

            // Prepare request payload
            $payload = [
                'timestamp' => now()->toIso8601String(),
                'serviceCode' => self::SERVICE_CODE_STATEMENT,
                'partnerRef' => $partnerRef,
                'accountNumber' => $accountNumber,
                'statementDate' => $dateString
            ];

            // Generate digital signature (skip in mock mode)
            $headers = [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Connection' => 'Keep-Alive',
                'Accept-Encoding' => 'br,deflate,gzip,x-gzip'
            ];

            if (!$this->mockMode) {
                $signature = $this->generateSignature($payload);
                $headers['X-Signature'] = $signature;
            }

            // Make API request
            $response = Http::withOptions(['verify' => $this->verifySSL])
                ->withHeaders($headers)
                ->post($this->baseUrl . '/api/v1/casa/statement', $payload);

            // Process response
            $result = $this->processResponse($response, 'statement');

            if ($result['statusCode'] === self::STATUS_SUCCESS) {
                Log::info('NBC Statement API: Statement fetched successfully', [
                    'account_number' => $accountNumber,
                    'transaction_count' => count($result['data']['transactions'] ?? []),
                    'bank_ref' => $result['bankRef']
                ]);

                return $result['data']['transactions'] ?? [];
            }

            throw new Exception(
                "Failed to fetch statement: {$result['message']} (Code: {$result['statusCode']})"
            );

        } catch (Exception $e) {
            Log::error('NBC Statement API: Failed to fetch statement', [
                'account_number' => $accountNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Fetch account balance for a specific date
     *
     * @param string $accountNumber NBC account number
     * @param string|Carbon $statementDate Date for balance
     * @param string|null $partnerRef Optional partner reference
     * @return array Balance information
     * @throws Exception
     */
    public function fetchAccountBalance(string $accountNumber, $statementDate, ?string $partnerRef = null): array
    {
        $dateString = $statementDate instanceof Carbon
            ? $statementDate->format('Y-m-d')
            : $statementDate;

        $partnerRef = $partnerRef ?? $this->generatePartnerRef();

        Log::info('NBC Statement API: Fetching account balance', [
            'account_number' => $accountNumber,
            'statement_date' => $dateString
        ]);

        try {
            $token = $this->authenticate();

            $payload = [
                'timestamp' => now()->toIso8601String(),
                'serviceCode' => self::SERVICE_CODE_BALANCE,
                'partnerRef' => $partnerRef,
                'accountNumber' => $accountNumber,
                'statementDate' => $dateString
            ];

            $headers = [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];

            if (!$this->mockMode) {
                $signature = $this->generateSignature($payload);
                $headers['X-Signature'] = $signature;
            }

            $response = Http::withOptions(['verify' => $this->verifySSL])
                ->withHeaders($headers)
                ->post($this->baseUrl . '/api/v1/casa/balance', $payload);

            $result = $this->processResponse($response, 'balance');

            if ($result['statusCode'] === self::STATUS_SUCCESS) {
                return $result['data'] ?? [];
            }

            throw new Exception(
                "Failed to fetch balance: {$result['message']} (Code: {$result['statusCode']})"
            );

        } catch (Exception $e) {
            Log::error('NBC Statement API: Failed to fetch balance', [
                'account_number' => $accountNumber,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Authenticate with NBC API and get JWT token
     *
     * @return string JWT token
     * @throws Exception
     */
    protected function authenticate(): string
    {
        // Check cache for existing valid token
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cachedToken) {
            Log::debug('NBC Statement API: Using cached JWT token');
            return $cachedToken;
        }

        Log::info('NBC Statement API: Authenticating to get new JWT token');

        try {
            // In mock mode, routes already include /api/ prefix
            $authEndpoint = $this->mockMode
                ? $this->baseUrl . '/auth/login'
                : $this->baseUrl . '/api/auth/login';

            $response = Http::withOptions(['verify' => $this->verifySSL])
                ->post($authEndpoint, [
                    'username' => $this->username,
                    'password' => $this->password
                ]);

            if (!$response->successful()) {
                throw new Exception(
                    "Authentication failed: HTTP {$response->status()} - {$response->body()}"
                );
            }

            $data = $response->json();

            // Support both 'token' and 'jwtToken' field names
            if (!isset($data['token']) && !isset($data['jwtToken'])) {
                throw new Exception('Authentication response missing token');
            }

            $token = $data['token'] ?? $data['jwtToken'];
            $expiryMs = $data['expiry'] ?? $data['expiryMillis'] ?? 86400000; // Default 24 hours

            // Cache token (expire 5 minutes before actual expiry for safety)
            $cacheSeconds = ($expiryMs / 1000) - 300;
            Cache::put(self::TOKEN_CACHE_KEY, $token, $cacheSeconds);

            Log::info('NBC Statement API: Authentication successful', [
                'token_expiry_seconds' => $cacheSeconds
            ]);

            return $token;

        } catch (Exception $e) {
            Log::error('NBC Statement API: Authentication failed', [
                'error' => $e->getMessage()
            ]);

            throw new Exception("Failed to authenticate with NBC API: {$e->getMessage()}");
        }
    }

    /**
     * Generate digital signature for request payload
     * Uses SHA256withRSA algorithm
     *
     * @param array $payload Request payload
     * @return string Base64 encoded signature
     * @throws Exception
     */
    protected function generateSignature(array $payload): string
    {
        try {
            // Convert payload to JSON
            $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);

            if ($jsonPayload === false) {
                throw new Exception('Failed to JSON encode payload: ' . json_last_error_msg());
            }

            // Load private key
            $privateKeyContent = file_get_contents($this->privateKeyPath);
            if (!$privateKeyContent) {
                throw new Exception('Failed to read private key file');
            }

            $privateKey = openssl_pkey_get_private($privateKeyContent);
            if (!$privateKey) {
                throw new Exception('Failed to load private key: ' . openssl_error_string());
            }

            // Sign the payload using SHA256withRSA
            $signature = '';
            $signResult = openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            if (!$signResult) {
                throw new Exception('Failed to generate signature: ' . openssl_error_string());
            }

            // Note: openssl_free_key() is deprecated in PHP 8.0+
            // The key resource is automatically freed when it goes out of scope

            // Base64 encode the signature
            $base64Signature = base64_encode($signature);

            Log::debug('NBC Statement API: Digital signature generated', [
                'payload_length' => strlen($jsonPayload),
                'signature_length' => strlen($base64Signature)
            ]);

            return $base64Signature;

        } catch (Exception $e) {
            Log::error('NBC Statement API: Signature generation failed', [
                'error' => $e->getMessage()
            ]);

            throw new Exception("Failed to generate digital signature: {$e->getMessage()}");
        }
    }

    /**
     * Process API response
     *
     * @param \Illuminate\Http\Client\Response $response
     * @param string $requestType Type of request (for logging)
     * @return array Parsed response data
     * @throws Exception
     */
    protected function processResponse($response, string $requestType): array
    {
        $statusCode = $response->status();

        Log::debug('NBC Statement API: Processing response', [
            'request_type' => $requestType,
            'http_status' => $statusCode
        ]);

        if (!$response->successful()) {
            throw new Exception(
                "API request failed with HTTP status {$statusCode}: {$response->body()}"
            );
        }

        $data = $response->json();

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        // Check NBC API status code
        if (!isset($data['statusCode'])) {
            throw new Exception('Response missing statusCode field');
        }

        // Log response details
        Log::info('NBC Statement API: Response received', [
            'request_type' => $requestType,
            'status_code' => $data['statusCode'],
            'message' => $data['message'] ?? 'N/A',
            'bank_ref' => $data['bankRef'] ?? 'N/A'
        ]);

        return $data;
    }

    /**
     * Generate unique partner reference
     *
     * @return string Partner reference
     */
    protected function generatePartnerRef(): string
    {
        // Format: CB + YYMMDDHHMMSS + Random(4)
        return 'CB' . now()->format('ymdHis') . strtoupper(substr(md5(uniqid()), 0, 4));
    }

    /**
     * Map NBC transaction to bank_transactions format
     *
     * @param array $nbcTransaction NBC API transaction format
     * @return array Mapped transaction
     */
    public function mapNBCTransaction(array $nbcTransaction): array
    {
        // NBC Response Format:
        // {
        //   "transactionDate": "2025-02-10T00:00:00",
        //   "postingDate": "2025-02-10T00:00:00",
        //   "valueDate": "2023-01-02T00:00:00",
        //   "currency": "TZS",
        //   "amount": 910,
        //   "balance": 4170415.84,
        //   "reference": "99520102000100948495",
        //   "description": "SC|250210141523713748|20250210|102|NULL|",
        //   "debitCredit": "D",
        //   "debitAmount": 910,
        //   "creditAmount": 0
        // }

        return [
            'transaction_date' => Carbon::parse($nbcTransaction['transactionDate'])->format('Y-m-d'),
            'value_date' => Carbon::parse($nbcTransaction['valueDate'])->format('Y-m-d'),
            'reference_number' => $nbcTransaction['reference'],
            'narration' => $nbcTransaction['description'] ?? '',
            'withdrawal_amount' => $nbcTransaction['debitAmount'] ?? 0,
            'deposit_amount' => $nbcTransaction['creditAmount'] ?? 0,
            'balance' => $nbcTransaction['balance'] ?? 0,
            'branch' => null, // Not provided in NBC response
            'currency' => $nbcTransaction['currency'] ?? 'TZS',
            'raw_data' => $nbcTransaction // Store original for reference
        ];
    }

    /**
     * Get NBC API status code description
     *
     * @param int $statusCode
     * @return string Description
     */
    public static function getStatusCodeDescription(int $statusCode): string
    {
        $descriptions = [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_SIGNATURE_FAILURE => 'Digital Signature Verification Failure',
            self::STATUS_AUTH_FAILED => 'Authentication Failed',
            self::STATUS_EXCEPTION => 'Exception caught',
            self::STATUS_UNAUTHORIZED => 'Unauthorized service access request'
        ];

        return $descriptions[$statusCode] ?? "Unknown status code: {$statusCode}";
    }

    /**
     * Clear cached authentication token
     *
     * @return void
     */
    public function clearAuthCache(): void
    {
        Cache::forget(self::TOKEN_CACHE_KEY);
        Log::info('NBC Statement API: Authentication cache cleared');
    }

    /**
     * Test NBC API connectivity
     *
     * @return array Test results
     */
    public function testConnectivity(): array
    {
        $results = [
            'timestamp' => now()->toDateTimeString(),
            'tests' => []
        ];

        try {
            // Test 1: Authentication
            $authStart = microtime(true);
            $token = $this->authenticate();
            $authTime = round((microtime(true) - $authStart) * 1000, 2);

            $results['tests']['authentication'] = [
                'status' => 'success',
                'time_ms' => $authTime,
                'token_length' => strlen($token)
            ];

        } catch (Exception $e) {
            $results['tests']['authentication'] = [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
            $results['overall_status'] = 'failed';
            return $results;
        }

        // Test 2: Digital Signature (skip in mock mode)
        if (!$this->mockMode) {
            try {
                $testPayload = ['test' => 'signature'];
                $signature = $this->generateSignature($testPayload);

                $results['tests']['signature_generation'] = [
                    'status' => 'success',
                    'signature_length' => strlen($signature)
                ];

            } catch (Exception $e) {
                $results['tests']['signature_generation'] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        } else {
            $results['tests']['signature_generation'] = [
                'status' => 'skipped',
                'reason' => 'Running in mock mode'
            ];
        }

        $results['overall_status'] = 'success';
        $results['mock_mode'] = $this->mockMode;
        return $results;
    }
}
