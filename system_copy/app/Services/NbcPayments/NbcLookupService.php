<?php

namespace App\Services\NbcPayments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NbcLookupService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $clientId;
    protected string $privateKey;

    public function __construct()
    {
        $this->baseUrl = config('services.nbc_payments.base_url');
        $this->apiKey = config('services.nbc_payments.api_key');
        $this->clientId = config('services.nbc_payments.client_id');
        $this->privateKey = config('services.nbc_payments.private_key');

        Log::info('NBC Lookup Service Initialized', [
            'baseUrl' => $this->baseUrl,
            'clientId' => $this->clientId
        ]);
    }

    public function lookup(array $payload): array
    {
        Log::info('Starting lookup process', ['payload' => $payload]);

        $this->validateLookupPayload($payload);
        Log::info('Payload validation passed');

        $requestPayload = $this->buildLookupPayload($payload);
        Log::info('Built request payload', ['requestPayload' => $requestPayload]);

        $timestamp = Carbon::now()->toIso8601String();
        $signature = $this->generateSignature($requestPayload);
        Log::info('Generated signature', ['timestamp' => $timestamp]);

        try {
            Log::info('Sending POST request to NBC Lookup API', [
                'url' => $this->baseUrl . '/domestix/api/v2/lookup',
                'headers' => [
                    'X-Api-Key' => $this->apiKey,
                    'Client-Id' => $this->clientId,
                    'Service-Name' => 'TIPS_LOOKUP'
                ],
                'payload' => $requestPayload
            ]);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'X-Api-Key' => $this->apiKey,
                'Signature' => $signature,
                'Timestamp' => $timestamp,
                'Client-Id' => $this->clientId,
                'Service-Name' => 'TIPS_LOOKUP',
            ])->withOptions(['verify' => false]) // disables SSL cert validation like `-k`
	      ->post($this->baseUrl . '/domestix/api/v2/lookup', $requestPayload);

            Log::info('Received response from NBC Lookup API', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            return $this->processResponse($response);

        } catch (\Exception $e) {
            Log::error('NBC Lookup API Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to process lookup request: ' . $e->getMessage());
        }
    }

    protected function validateLookupPayload(array $payload): void
    {
        $requiredFields = [
            'identifierType',
            'identifier',
            'destinationFsp',
            'debitAccount',
            'debitAccountCurrency',
            'debitAccountBranchCode',
            'amount',
            'debitAccountCategory'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                Log::warning("Validation failed - missing field", ['field' => $field]);
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if (!in_array($payload['identifierType'], ['BANK', 'MSISDN', 'BUSINESS'])) {
            Log::warning("Validation failed - invalid identifierType", ['value' => $payload['identifierType']]);
            throw new \InvalidArgumentException("Invalid identifierType. Must be BANK, MSISDN, or BUSINESS");
        }

        if (!in_array($payload['debitAccountCategory'], ['PERSON', 'BUSINESS'])) {
            Log::warning("Validation failed - invalid debitAccountCategory", ['value' => $payload['debitAccountCategory']]);
            throw new \InvalidArgumentException("Invalid debitAccountCategory. Must be PERSON or BUSINESS");
        }
    }

    protected function buildLookupPayload(array $payload): array
    {
        $finalPayload = [
            'serviceName' => 'TIPS_LOOKUP',
            'clientId' => $this->clientId,
            'clientRef' => $payload['clientRef'] ?? $this->generateClientRef(),
            'identifierType' => $payload['identifierType'],
            'identifier' => $payload['identifier'],
            'destinationFsp' => $payload['destinationFsp'],
            'debitAccount' => $payload['debitAccount'],
            'debitAccountCurrency' => $payload['debitAccountCurrency'],
            'debitAccountBranchCode' => $payload['debitAccountBranchCode'],
            'amount' => $payload['amount'],
            'debitAccountCategory' => $payload['debitAccountCategory'],
        ];

        Log::debug('Lookup payload constructed', ['payload' => $finalPayload]);
        return $finalPayload;
    }

    protected function generateClientRef(): string
    {
        //$ref = strtoupper($this->clientId) . now()->format('YmdHis') . strtoupper(Str::random(4));
        $ref = 'IB' . strtoupper(Str::random(10));
        Log::info('Generated client reference', ['clientRef' => $ref]);
        return $ref;
    }

    protected function generateSignature(array $payload): string
    {
        try {
               // Load private key
        $privateKeyContent = Storage::get('keys/private_key.pem');
        $privateKey = openssl_pkey_get_private($privateKeyContent);

            //$privateKey = openssl_pkey_get_private($this->privateKey);
            if (!$privateKey) {
                Log::error('Failed to load private key for signature');
                throw new \Exception('Failed to load private key');
            }

            $jsonPayload = json_encode($payload);
            openssl_sign($jsonPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            $encodedSignature = base64_encode($signature);
            Log::debug('Digital signature created successfully');

            return $encodedSignature;
        } catch (\Exception $e) {
            Log::error('Signature generation failed', ['error' => $e->getMessage()]);
            throw new \Exception('Failed to generate digital signature');
        }
    }

    protected function processResponse($response): array
    {
        $statusCode = $response->status();
        $responseData = $response->json();

        Log::debug('Processing response', [
            'statusCode' => $statusCode,
            'response' => $responseData
        ]);

        if ($statusCode !== 200) {
            Log::error("Non-200 response from NBC API", ['statusCode' => $statusCode]);
            throw new \Exception("API request failed with status: {$statusCode}");
        }

        if (!isset($responseData['statusCode'])) {
            Log::error("Invalid API response format", ['response' => $responseData]);
            throw new \Exception("Invalid API response format");
        }

        if ($responseData['statusCode'] === 600) {
            Log::info("Lookup successful", ['message' => $responseData['message'] ?? 'Success']);
            return [
                'success' => true,
                'data' => $responseData,
                'message' => $responseData['message'] ?? 'Lookup successful'
            ];
        }

        Log::warning("Lookup failed", [
            'statusCode' => $responseData['statusCode'],
            'message' => $responseData['message'] ?? 'Unknown error'
        ]);

        return [
            'success' => false,
            'error_code' => $responseData['statusCode'],
            'message' => $responseData['message'] ?? 'Lookup failed',
            'errors' => $responseData['body'] ?? null
        ];
    }

    /**
     * Helper method for bank-to-bank lookup
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
     * Helper method for bank-to-wallet lookup
     *
     * @param string $phoneNumber
     * @param string $walletProvider
     * @param string $debitAccount
     * @param string $amount
     * @param string $debitAccountCategory
     * @return array
     */
    public function bankToWalletLookup(
        string $phoneNumber,
        string $walletProvider,
        string $debitAccount,
        string $amount,
        string $debitAccountCategory = 'PERSON'
    ): array {
        return $this->lookup([
            'identifierType' => 'MSISDN',
            'identifier' => $phoneNumber,
            'destinationFsp' => $walletProvider,
            'debitAccount' => $debitAccount,
            'debitAccountCurrency' => 'TZS',
            'debitAccountBranchCode' => substr($debitAccount, 0, 3),
            'amount' => $amount,
            'debitAccountCategory' => $debitAccountCategory
        ]);
    }

    /**
     * Helper method for merchant payment (TANQR) lookup
     *
     * @param string $merchantId
     * @param string $bankCode
     * @param string $debitAccount
     * @param string $amount
     * @param string $debitAccountCategory
     * @return array
     */
    public function merchantPaymentLookup(
        string $merchantId,
        string $bankCode,
        string $debitAccount,
        string $amount,
        string $debitAccountCategory = 'BUSINESS'
    ): array {
        return $this->lookup([
            'identifierType' => 'BUSINESS',
            'identifier' => $merchantId,
            'destinationFsp' => $bankCode,
            'debitAccount' => $debitAccount,
            'debitAccountCurrency' => 'TZS',
            'debitAccountBranchCode' => substr($debitAccount, 0, 3),
            'amount' => $amount,
            'debitAccountCategory' => $debitAccountCategory
        ]);
    }
}
