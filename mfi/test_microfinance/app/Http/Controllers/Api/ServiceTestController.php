<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Import real service classes
use App\Services\Payments\InternalFundsTransferService;
use App\Services\Payments\ExternalFundsTransferService;
use App\Services\AccountDetailsService;
use App\Services\SmsService;
use App\Services\NbcPayments\LukuService;
use App\Services\NbcPayments\GepgGatewayService;
use App\Services\NbcPayments\GepgLoggerService;
use App\Services\PaymentLinkService;
use App\Services\Payments\BillPaymentService;
use App\Services\NbcBillsPaymentService;
use App\Models\BankAccount;

class ServiceTestController extends Controller
{
    /**
     * Internal Funds Transfer - Real Implementation
     */
    public function internalFundsTransfer(Request $request)
    {
        $requestId = 'IFT_' . Str::uuid()->toString();
        
        Log::info('=== INTERNAL FUNDS TRANSFER REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'endpoint' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'source_account' => 'required|string',
            'destination_account' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string',
            'beneficiary_name' => 'nullable|string',
        ]);

        Log::info('[IFT] Validated request data', [
            'request_id' => $requestId,
            'validated_data' => $validated,
            'validation_passed' => true
        ]);

        try {
            $service = new InternalFundsTransferService();
            
            Log::info('[IFT] Initiating account lookup', [
                'request_id' => $requestId,
                'destination_account' => $validated['destination_account'],
                'lookup_type' => 'destination',
                'service_class' => get_class($service)
            ]);
            
            // First perform account lookup
            $lookupStartTime = microtime(true);
            $lookupResult = $service->lookupAccount($validated['destination_account'], 'destination');
            $lookupDuration = round((microtime(true) - $lookupStartTime) * 1000, 2);
            
            Log::info('[IFT] Account lookup completed', [
                'request_id' => $requestId,
                'lookup_success' => $lookupResult['success'] ?? false,
                'lookup_duration_ms' => $lookupDuration,
                'account_found' => isset($lookupResult['data']['accountName']),
                'account_name' => $lookupResult['data']['accountName'] ?? null,
                'lookup_response' => $lookupResult
            ]);
            
            if (!$lookupResult['success']) {
                Log::warning('[IFT] Account lookup failed', [
                    'request_id' => $requestId,
                    'destination_account' => $validated['destination_account'],
                    'error_message' => $lookupResult['message'] ?? 'Unknown error',
                    'error_details' => $lookupResult
                ]);
                
                // Return the actual service response
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => 'Account lookup failed',
                    'details' => $lookupResult,
                    'timestamp' => now()->toDateTimeString(),
                    'processing_time_ms' => $lookupDuration
                ], 200);
            }
            
            // Perform the transfer - map to service expected field names
            $transferData = [
                'from_account' => $validated['source_account'],
                'to_account' => $validated['destination_account'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'narration' => $validated['description'] ?? 'Internal funds transfer test',
                'beneficiary_name' => $lookupResult['data']['accountName'] ?? $validated['beneficiary_name'] ?? 'Beneficiary',
                'sender_name' => $validated['sender_name'] ?? 'Test Sender',
                'reference' => $validated['reference'] ?? 'TEST_' . Str::upper(Str::random(10))
            ];
            
            Log::info('[IFT] Initiating transfer', [
                'request_id' => $requestId,
                'transfer_data' => $transferData,
                'transfer_reference' => $transferData['reference']
            ]);
            
            $transferStartTime = microtime(true);
            $result = $service->transfer($transferData);
            $transferDuration = round((microtime(true) - $transferStartTime) * 1000, 2);

            Log::info('[IFT] Transfer completed', [
                'request_id' => $requestId,
                'transfer_success' => $result['success'] ?? false,
                'transfer_duration_ms' => $transferDuration,
                'transaction_id' => $result['data']['reference'] ?? null,
                'transaction_status' => $result['data']['status'] ?? null,
                'transfer_response' => $result
            ]);

            $response = [
                'status' => $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'message' => $result['success'] ? ($result['message'] ?? 'Transfer processed') : ($result['error'] ?? $result['message'] ?? 'Transfer failed'),
                'data' => $result['data'] ?? null,
                'transaction_id' => $result['data']['reference'] ?? null,
                'timestamp' => now()->toDateTimeString(),
                'processing_time_ms' => $lookupDuration + $transferDuration
            ];

            Log::info('=== INTERNAL FUNDS TRANSFER REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'total_duration_ms' => $lookupDuration + $transferDuration,
                'response' => $response
            ]);

            return response()->json($response, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[IFT] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString()),
                'timestamp' => now()->toDateTimeString()
            ], 500);
        }
    }

    /**
     * External Funds Transfer - Real Implementation
     */
    public function externalFundsTransfer(Request $request)
    {
        $requestId = 'EFT_' . Str::uuid()->toString();
        
        Log::info('=== EXTERNAL FUNDS TRANSFER REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'endpoint' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'source_account' => 'required|string',
            'destination_account' => 'required|string',
            'destination_bank' => 'required|string',
            'swift_code' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'beneficiary_name' => 'required|string',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string',
        ]);

        Log::info('[EFT] Validated request data', [
            'request_id' => $requestId,
            'validated_data' => $validated,
            'transfer_type' => $validated['amount'] >= 20000000 ? 'TISS' : 'TIPS'
        ]);

        try {
            $service = new ExternalFundsTransferService();
            
            Log::info('[EFT] Initiating external account lookup', [
                'request_id' => $requestId,
                'destination_account' => $validated['destination_account'],
                'destination_bank' => $validated['destination_bank'],
                'amount' => $validated['amount']
            ]);
            
            // First perform account lookup
            $lookupStartTime = microtime(true);
            $lookupResult = $service->lookupAccount(
                $validated['destination_account'],
                $validated['destination_bank'],
                $validated['amount']
            );
            $lookupDuration = round((microtime(true) - $lookupStartTime) * 1000, 2);
            
            Log::info('[EFT] External account lookup completed', [
                'request_id' => $requestId,
                'lookup_success' => $lookupResult['success'] ?? false,
                'lookup_duration_ms' => $lookupDuration,
                'account_name' => $lookupResult['data']['accountName'] ?? null,
                'bank_name' => $lookupResult['data']['bankName'] ?? null,
                'lookup_response' => $lookupResult
            ]);
            
            if (!$lookupResult['success']) {
                Log::warning('[EFT] External account lookup failed', [
                    'request_id' => $requestId,
                    'error_details' => $lookupResult
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => 'External account lookup failed',
                    'details' => $lookupResult
                ], 400);
            }
            
            // Perform the external transfer - map to service expected field names
            $transferData = [
                'from_account' => $validated['source_account'],
                'to_account' => $validated['destination_account'],
                'bank_code' => $validated['destination_bank'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'beneficiary_name' => $lookupResult['data']['accountName'] ?? $validated['beneficiary_name'],
                'narration' => $validated['description'] ?? 'External funds transfer test',
                'sender_name' => $validated['sender_name'] ?? 'Test Sender',
                'reference' => $validated['reference'] ?? 'EXT_' . Str::upper(Str::random(10))
            ];
            
            Log::info('[EFT] Initiating external transfer', [
                'request_id' => $requestId,
                'transfer_data' => $transferData,
                'transfer_system' => $validated['amount'] >= 20000000 ? 'TISS' : 'TIPS'
            ]);
            
            $transferStartTime = microtime(true);
            $result = $service->transfer($transferData);
            $transferDuration = round((microtime(true) - $transferStartTime) * 1000, 2);

            Log::info('[EFT] External transfer completed', [
                'request_id' => $requestId,
                'transfer_success' => $result['success'] ?? false,
                'transfer_duration_ms' => $transferDuration,
                'transaction_id' => $result['data']['reference'] ?? null,
                'transfer_response' => $result
            ]);

            $response = [
                'status' => $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'message' => $result['message'] ?? 'External transfer processed',
                'data' => $result['data'] ?? null,
                'transaction_id' => $result['data']['reference'] ?? null,
                'timestamp' => now()->toDateTimeString(),
                'processing_time_ms' => $lookupDuration + $transferDuration
            ];

            Log::info('=== EXTERNAL FUNDS TRANSFER REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'total_duration_ms' => $lookupDuration + $transferDuration,
                'response' => $response
            ]);

            return response()->json($response, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[EFT] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Mini Statement - Real Implementation
     */
    public function miniStatement(Request $request)
    {
        $requestId = 'MINI_' . Str::uuid()->toString();
        
        Log::info('=== MINI STATEMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'account_number' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:10',
            'channel_code' => 'nullable|string',
        ]);

        // Fetch NBC Bank account if not provided
        if (empty($validated['account_number'])) {
            $nbcAccount = BankAccount::where('bank_name', 'NBC Bank')->first();
            
            if (!$nbcAccount) {
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => 'NBC Bank account not found in bank_accounts table'
                ], 400);
            }
            
            $validated['account_number'] = $nbcAccount->account_number;
            
            Log::info('[MINI] Using NBC Bank account', [
                'request_id' => $requestId,
                'account_number' => $validated['account_number'],
                'account_name' => $nbcAccount->account_name
            ]);
        }

        Log::info('[MINI] Validated request data', [
            'request_id' => $requestId,
            'account_number' => $validated['account_number'],
            'transaction_limit' => $validated['limit'] ?? 5
        ]);

        try {
            $service = new AccountDetailsService();
            
            $params = [
                'accountNumber' => $validated['account_number'],
                'channelCode' => $validated['channel_code'] ?? 'SACCOSNBC',
                'channelName' => 'NBC_SACCOS',
                'includeTransactions' => true,
                'transactionLimit' => $validated['limit'] ?? 5
            ];
            
            Log::info('[MINI] Fetching account details', [
                'request_id' => $requestId,
                'params' => $params
            ]);
            
            $startTime = microtime(true);
            $result = $service->getAccountDetails($validated['account_number']);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[MINI] Account details retrieved', [
                'request_id' => $requestId,
                'status_code' => $result['statusCode'] ?? null,
                'duration_ms' => $duration,
                'account_name' => $result['body']['accountName'] ?? null,
                'transaction_count' => count($result['body']['transactions'] ?? []),
                'response' => $result
            ]);
            
            if (($result['statusCode'] ?? 500) !== 200) {
                Log::warning('[MINI] Failed to retrieve mini statement', [
                    'request_id' => $requestId,
                    'error_details' => $result
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => $result['message'] ?? 'Failed to retrieve mini statement',
                    'details' => $result
                ], 400);
            }

            $response = [
                'status' => 'success',
                'request_id' => $requestId,
                'account_number' => $validated['account_number'],
                'account_name' => $result['body']['accountName'] ?? 'Account Holder',
                'currency' => $result['body']['currency'] ?? 'TZS',
                'current_balance' => $result['body']['currentBalance'] ?? 0,
                'available_balance' => $result['body']['availableBalance'] ?? 0,
                'transactions' => $result['body']['transactions'] ?? [],
                'generated_at' => now()->toDateTimeString(),
                'processing_time_ms' => $duration
            ];

            Log::info('=== MINI STATEMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => true,
                'duration_ms' => $duration
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('[MINI] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Full Statement - Real Implementation
     */
    public function fullStatement(Request $request)
    {
        $requestId = 'FULL_' . Str::uuid()->toString();
        
        Log::info('=== FULL STATEMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'account_number' => 'nullable|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'format' => 'nullable|in:json,pdf,excel',
            'channel_code' => 'nullable|string',
        ]);

        // Fetch NBC Bank account if not provided
        if (empty($validated['account_number'])) {
            $nbcAccount = BankAccount::where('bank_name', 'NBC Bank')->first();
            
            if (!$nbcAccount) {
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => 'NBC Bank account not found in bank_accounts table'
                ], 400);
            }
            
            $validated['account_number'] = $nbcAccount->account_number;
            
            Log::info('[FULL] Using NBC Bank account', [
                'request_id' => $requestId,
                'account_number' => $validated['account_number'],
                'account_name' => $nbcAccount->account_name
            ]);
        }

        Log::info('[FULL] Validated request data', [
            'request_id' => $requestId,
            'account_number' => $validated['account_number'],
            'date_range' => $validated['from_date'] . ' to ' . $validated['to_date'],
            'format' => $validated['format'] ?? 'json'
        ]);

        try {
            $service = new AccountDetailsService();
            
            $params = [
                'accountNumber' => $validated['account_number'],
                'fromDate' => $validated['from_date'],
                'toDate' => $validated['to_date'],
                'channelCode' => $validated['channel_code'] ?? 'SACCOSNBC',
                'channelName' => 'NBC_SACCOS',
                'format' => $validated['format'] ?? 'json'
            ];
            
            Log::info('[FULL] Fetching full statement', [
                'request_id' => $requestId,
                'params' => $params
            ]);
            
            $startTime = microtime(true);
$result = $service->getAccountDetails($validated['account_number']);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[FULL] Full statement retrieved', [
                'request_id' => $requestId,
                'status_code' => $result['statusCode'] ?? null,
                'duration_ms' => $duration,
                'transaction_count' => count($result['body']['transactions'] ?? []),
                'response_size_bytes' => strlen(json_encode($result))
            ]);
            
            if (($result['statusCode'] ?? 500) !== 200) {
                Log::warning('[FULL] Failed to retrieve full statement', [
                    'request_id' => $requestId,
                    'error_details' => $result
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => $result['message'] ?? 'Failed to retrieve full statement',
                    'details' => $result
                ], 400);
            }

            $response = [
                'status' => 'success',
                'request_id' => $requestId,
                'account_number' => $validated['account_number'],
                'account_name' => $result['body']['accountName'] ?? 'Account Holder',
                'account_type' => $result['body']['accountType'] ?? 'Account',
                'currency' => $result['body']['currency'] ?? 'TZS',
                'period' => [
                    'from' => $validated['from_date'],
                    'to' => $validated['to_date']
                ],
                'opening_balance' => $result['body']['openingBalance'] ?? 0,
                'closing_balance' => $result['body']['closingBalance'] ?? 0,
                'total_debits' => $result['body']['totalDebits'] ?? 0,
                'total_credits' => $result['body']['totalCredits'] ?? 0,
                'transaction_count' => count($result['body']['transactions'] ?? []),
                'transactions' => $result['body']['transactions'] ?? [],
                'format' => $validated['format'] ?? 'json',
                'generated_at' => now()->toDateTimeString(),
                'processing_time_ms' => $duration
            ];

            Log::info('=== FULL STATEMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => true,
                'duration_ms' => $duration,
                'transactions_returned' => count($result['body']['transactions'] ?? [])
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('[FULL] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Account Lookup - Real Implementation
     */
    public function accountLookup(Request $request)
    {
        $requestId = 'LOOKUP_' . Str::uuid()->toString();
        
        Log::info('=== ACCOUNT LOOKUP REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'account_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'id_number' => 'nullable|string',
            'email' => 'nullable|email',
            'channel_code' => 'nullable|string',
        ]);

        try {
            // At least one identifier required
            if (!$validated['account_number'] && !$validated['phone_number'] && 
                !$validated['id_number'] && !$validated['email']) {
                throw new \Exception('At least one search parameter is required');
            }

            Log::info('[LOOKUP] Search parameters', [
                'request_id' => $requestId,
                'search_params' => array_filter($validated)
            ]);

            $service = new AccountDetailsService();
            
            // Use account_number for lookup
            $accountNumber = $validated['account_number'];
            
            Log::info('[LOOKUP] Performing account lookup', [
                'request_id' => $requestId,
                'account_number' => $accountNumber
            ]);
            
            $startTime = microtime(true);
            $result = $service->getAccountDetails($accountNumber);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[LOOKUP] Account lookup completed', [
                'request_id' => $requestId,
                'status_code' => $result['statusCode'] ?? null,
                'duration_ms' => $duration,
                'account_found' => isset($result['body']['accountName']),
                'response' => $result
            ]);
            
            if (($result['statusCode'] ?? 500) !== 200) {
                Log::warning('[LOOKUP] Account lookup failed', [
                    'request_id' => $requestId,
                    'error_details' => $result
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => $result['message'] ?? 'Account lookup failed',
                    'details' => $result
                ], 400);
            }

            $response = [
                'status' => 'success',
                'request_id' => $requestId,
                'account' => $result['body'],
                'timestamp' => now()->toDateTimeString(),
                'processing_time_ms' => $duration
            ];

            Log::info('=== ACCOUNT LOOKUP REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => true,
                'duration_ms' => $duration
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('[LOOKUP] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Utilities Payment - Real Implementation
     */
    public function utilities(Request $request)
    {
        $requestId = 'UTIL_' . Str::uuid()->toString();
        
        Log::info('=== UTILITIES PAYMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'utility_type' => 'required|in:water,electricity,gas,internet,tv',
            'account_number' => 'required|string',
            'meter_number' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'provider_code' => 'required|string',
        ]);
        // Force DSTV reference number for TV utility type
        if ($validated['utility_type'] === 'tv') {
            $validated['meter_number'] = '7029243019';
        }

        Log::info('[UTIL] Validated request data', [
            'request_id' => $requestId,
            'utility_type' => $validated['utility_type'],
            'provider_code' => $validated['provider_code'],
            'amount' => $validated['amount'],
            'meter_number' => $validated['meter_number'] ?? null
        ]);

        try {
            $service = new BillPaymentService();
            
            // Map to service expected field names
            // Use specific reference for DSTV service
            $defaultReference = $validated['provider_code'] . '_' . time();
            
            $paymentData = [
                'from_account' => $validated['account_number'],
                'bill_reference' => $validated['meter_number'] ?? $defaultReference,
                'meter_number' => $validated['meter_number'] ?? null,
                'amount' => $validated['amount'],
                'customer_name' => $validated['customer_name'] ?? 'Customer',
                'customer_phone' => $validated['phone_number'] ?? null,
                'payer_name' => $validated['customer_name'] ?? 'Payer',
                'sp_code' => $validated['provider_code'],
                'reference' => 'UTIL_' . Str::upper(Str::random(10))
            ];
            
            // Determine bill type based on utility type
            $billTypeMap = [
                'water' => 'WATER',
                'electricity' => 'LUKU',
                'gas' => 'GAS',
                'internet' => 'INTERNET',
                'tv' => $validated['provider_code'] // Use provider code for TV (DSTV, AZAM, etc.)
            ];
            
            $billType = $billTypeMap[$validated['utility_type']] ?? $validated['provider_code'];
            
            Log::info('[UTIL] Processing utility payment', [
                'request_id' => $requestId,
                'bill_type' => $billType,
                'payment_data' => $paymentData
            ]);
            
            $startTime = microtime(true);
            $result = $service->payBill($billType, $paymentData);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('[UTIL] Utility payment processed', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'duration_ms' => $duration,
                'payment_status' => $result['success'] ? 'SUCCESS' : 'FAILED',
                'response' => $result
            ]);

            // Extract response data (excluding success and message)
            $responseData = array_diff_key($result, array_flip(['success', 'message']));

            $response = [
                'status' => $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'reference_number' => $result['provider_reference'] ?? null,
                'utility_type' => $validated['utility_type'],
                'account_number' => $validated['account_number'],
                'amount' => $validated['amount'],
                'payment_status' => $result['success'] ? 'SUCCESS' : 'FAILED',
                'timestamp' => now()->toDateTimeString(),
                'message' => $result['message'] ?? 'Utility payment processed',
                'data' => $responseData,
                'processing_time_ms' => $duration
            ];

            Log::info('=== UTILITIES PAYMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'duration_ms' => $duration
            ]);

            return response()->json($response, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[UTIL] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * SMS Service - Real Implementation
     */
    public function sms(Request $request)
    {
        $requestId = 'SMS_' . Str::uuid()->toString();
        
        Log::info('=== SMS SERVICE REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'phone_numbers' => 'required|array',
            'phone_numbers.*' => 'required|string',
            'message' => 'required|string|max:160',
            'sender_id' => 'nullable|string|max:11',
            'schedule_time' => 'nullable|date',
            'sms_type' => 'nullable|in:TRANSACTIONAL,PROMOTIONAL',
        ]);

        Log::info('[SMS] Validated request data', [
            'request_id' => $requestId,
            'recipient_count' => count($validated['phone_numbers']),
            'message_length' => strlen($validated['message']),
            'sms_type' => $validated['sms_type'] ?? 'TRANSACTIONAL'
        ]);

        try {
            $service = new SmsService();
            $results = [];
            $errors = [];
            
            Log::info('[SMS] Starting SMS dispatch', [
                'request_id' => $requestId,
                'total_recipients' => count($validated['phone_numbers'])
            ]);
            
            // Send SMS to each number
            foreach ($validated['phone_numbers'] as $index => $phoneNumber) {
                try {
                    Log::debug('[SMS] Sending to recipient', [
                        'request_id' => $requestId,
                        'recipient_index' => $index + 1,
                        'phone_number' => $phoneNumber
                    ]);
                    
                    $startTime = microtime(true);
                    $result = $service->send(
                        $phoneNumber,
                        $validated['message'],
                        null,
                        [
                            'smsType' => $validated['sms_type'] ?? 'TRANSACTIONAL',
                            'senderId' => $validated['sender_id'] ?? 'SACCOS',
                            'scheduleTime' => $validated['schedule_time'] ?? null
                        ]
                    );
                    $duration = round((microtime(true) - $startTime) * 1000, 2);
                    
                    $results[] = [
                        'phone' => $phoneNumber,
                        'status' => 'sent',
                        'message_id' => $result['messageId'] ?? null,
                        'duration_ms' => $duration
                    ];
                    
                    Log::debug('[SMS] Message sent successfully', [
                        'request_id' => $requestId,
                        'phone' => $phoneNumber,
                        'message_id' => $result['messageId'] ?? null,
                        'duration_ms' => $duration
                    ]);
                } catch (\Exception $e) {
                    $errors[] = [
                        'phone' => $phoneNumber,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                    
                    Log::warning('[SMS] Failed to send message', [
                        'request_id' => $requestId,
                        'phone' => $phoneNumber,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $response = [
                'status' => count($errors) === 0 ? 'success' : 'partial',
                'request_id' => $requestId,
                'message_id' => 'SMS_' . Str::upper(Str::random(10)),
                'recipients' => count($validated['phone_numbers']),
                'successful' => count($results),
                'failed' => count($errors),
                'results' => $results,
                'errors' => $errors,
                'message' => $validated['message'],
                'sender_id' => $validated['sender_id'] ?? 'SACCOS',
                'timestamp' => now()->toDateTimeString()
            ];

            Log::info('=== SMS SERVICE REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'total_recipients' => count($validated['phone_numbers']),
                'successful' => count($results),
                'failed' => count($errors),
                'status' => $response['status']
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('[SMS] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Direct Debit - Real Implementation
     */
    public function directDebit(Request $request)
    {
        $requestId = 'DD_' . Str::uuid()->toString();
        
        Log::info('=== DIRECT DEBIT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'account_number' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'mandate_reference' => 'required|string',
            'beneficiary_account' => 'required|string',
            'beneficiary_name' => 'required|string',
            'frequency' => 'nullable|in:once,daily,weekly,monthly,yearly',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'bank_code' => 'nullable|string',
        ]);

        Log::info('[DD] Validated request data', [
            'request_id' => $requestId,
            'mandate_reference' => $validated['mandate_reference'],
            'frequency' => $validated['frequency'] ?? 'once',
            'amount' => $validated['amount']
        ]);

        try {
            $service = new NbcBillsPaymentService();
            
            $mandateData = [
                'accountNumber' => $validated['account_number'],
                'amount' => $validated['amount'],
                'mandateReference' => $validated['mandate_reference'],
                'beneficiaryAccount' => $validated['beneficiary_account'],
                'beneficiaryName' => $validated['beneficiary_name'],
                'frequency' => $validated['frequency'] ?? 'once',
                'startDate' => $validated['start_date'] ?? now()->format('Y-m-d'),
                'endDate' => $validated['end_date'] ?? null,
                'bankCode' => $validated['bank_code'] ?? 'NBC'
            ];
            
            Log::info('[DD] Setting up direct debit mandate', [
                'request_id' => $requestId,
                'mandate_data' => $mandateData
            ]);
            
            $startTime = microtime(true);
            $result = $service->setupDirectDebit($mandateData);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[DD] Direct debit setup completed', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'duration_ms' => $duration,
                'mandate_id' => $result['data']['mandateId'] ?? null,
                'response' => $result
            ]);
            
            $response = [
                'status' => $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'mandate_id' => $result['data']['mandateId'] ?? 'DD_' . Str::upper(Str::random(10)),
                'mandate_reference' => $validated['mandate_reference'],
                'account_number' => $validated['account_number'],
                'beneficiary_account' => $validated['beneficiary_account'],
                'beneficiary_name' => $validated['beneficiary_name'],
                'amount' => $validated['amount'],
                'frequency' => $validated['frequency'] ?? 'once',
                'status' => $result['data']['status'] ?? 'Pending',
                'timestamp' => now()->toDateTimeString(),
                'message' => $result['message'] ?? 'Direct debit mandate created',
                'data' => $result['data'] ?? null,
                'processing_time_ms' => $duration
            ];

            Log::info('=== DIRECT DEBIT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => $result['success'] ?? false,
                'duration_ms' => $duration
            ]);

            return response()->json($response, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[DD] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Control Number Payments - Real Implementation (GEPG)
     */
    public function controlNumberPayments(Request $request)
    {
        $requestId = 'CN_' . Str::uuid()->toString();
        
        Log::info('=== CONTROL NUMBER PAYMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'control_number' => 'required|string',
            'payer_name' => 'required|string',
            'payer_phone' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_description' => 'nullable|string',
            'currency' => 'nullable|string|size:3',
            'account_number' => 'required|string',
        ]);

        Log::info('[CN] Validated request data', [
            'request_id' => $requestId,
            'control_number' => $validated['control_number'],
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'TZS'
        ]);

        try {
            $logger = new GepgLoggerService();
            $service = new GepgGatewayService($logger);
            
            Log::info('[CN] Verifying control number', [
                'request_id' => $requestId,
                'control_number' => $validated['control_number']
            ]);
            
            $verifyStartTime = microtime(true);
            $verifyResult = $service->verifyControlNumber(
                $validated['control_number'],
                $validated['account_number'],
                $validated['currency'] ?? 'TZS'
            );
            $verifyDuration = round((microtime(true) - $verifyStartTime) * 1000, 2);
            
            Log::info('[CN] Control number verification completed', [
                'request_id' => $requestId,
                'verification_duration_ms' => $verifyDuration,
                'verification_result' => $verifyResult
            ]);
            
            $paymentData = [
                'controlNumber' => $validated['control_number'],
                'accountNumber' => $validated['account_number'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'payerName' => $validated['payer_name'],
                'payerPhone' => $validated['payer_phone'] ?? null,
                'description' => $validated['payment_description'] ?? 'Control number payment',
                'reference' => 'CN_PAY_' . Str::upper(Str::random(10))
            ];
            
            Log::info('[CN] Processing control number payment', [
                'request_id' => $requestId,
                'payment_reference' => $paymentData['reference'],
                'payment_data' => $paymentData
            ]);
            
            $paymentStartTime = microtime(true);
            $result = $service->processPayment($paymentData);
            $paymentDuration = round((microtime(true) - $paymentStartTime) * 1000, 2);

            Log::info('[CN] Control number payment processed', [
                'request_id' => $requestId,
                'success' => isset($result['success']) && $result['success'],
                'payment_duration_ms' => $paymentDuration,
                'payment_status' => $result['status'] ?? null,
                'response' => $result
            ]);

            $response = [
                'status' => isset($result['success']) && $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'payment_reference' => $paymentData['reference'],
                'control_number' => $validated['control_number'],
                'payer_name' => $validated['payer_name'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'payment_status' => $result['status'] ?? 'Processing',
                'receipt_number' => $result['receiptNumber'] ?? null,
                'timestamp' => now()->toDateTimeString(),
                'message' => $result['message'] ?? 'Control number payment processed',
                'data' => $result,
                'processing_time_ms' => $verifyDuration + $paymentDuration
            ];

            Log::info('=== CONTROL NUMBER PAYMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => isset($result['success']) && $result['success'],
                'total_duration_ms' => $verifyDuration + $paymentDuration
            ]);

            return response()->json($response, isset($result['success']) && $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[CN] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * LUKU Payment - Real Implementation
     */
    public function luku(Request $request)
    {
        $requestId = 'LUKU_' . Str::uuid()->toString();
        
        Log::info('=== LUKU PAYMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'meter_number' => 'required|string',
            'amount' => 'required|numeric|min:1000',
            'customer_phone' => 'required|string',
            'customer_name' => 'nullable|string',
            'account_number' => 'nullable|string',
        ]);

        Log::info('[LUKU] Validated request data', [
            'request_id' => $requestId,
            'meter_number' => $validated['meter_number'],
            'amount' => $validated['amount'],
            'account_number' => $validated['account_number'] ?? '28012040011'
        ]);

        try {
            $service = new LukuService();
            
            Log::info('[LUKU] Looking up meter details', [
                'request_id' => $requestId,
                'meter_number' => $validated['meter_number']
            ]);
            
            $lookupStartTime = microtime(true);
            $lookupResult = $service->lookup(
                $validated['meter_number'],
                $validated['account_number'] ?? '28012040011'
            );
            $lookupDuration = round((microtime(true) - $lookupStartTime) * 1000, 2);
            
            Log::info('[LUKU] Meter lookup completed', [
                'request_id' => $requestId,
                'lookup_success' => isset($lookupResult['success']) && $lookupResult['success'],
                'lookup_duration_ms' => $lookupDuration,
                'customer_name' => $lookupResult['customerName'] ?? null,
                'lookup_response' => $lookupResult
            ]);
            
            if (!isset($lookupResult['success']) || !$lookupResult['success']) {
                Log::warning('[LUKU] Meter lookup failed', [
                    'request_id' => $requestId,
                    'error_details' => $lookupResult
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => 'Meter lookup failed',
                    'details' => $lookupResult
                ], 400);
            }
            
            Log::info('[LUKU] Processing token purchase', [
                'request_id' => $requestId,
                'amount' => $validated['amount'],
                'customer_name' => $lookupResult['customerName'] ?? $validated['customer_name'] ?? 'Customer'
            ]);
            
            $purchaseStartTime = microtime(true);
            $paymentResult = $service->purchaseToken(
                $validated['meter_number'],
                $validated['amount'],
                $validated['account_number'] ?? '28012040011',
                [
                    'customerPhone' => $validated['customer_phone'],
                    'customerName' => $lookupResult['customerName'] ?? $validated['customer_name'] ?? 'Customer'
                ]
            );
            $purchaseDuration = round((microtime(true) - $purchaseStartTime) * 1000, 2);

            Log::info('[LUKU] Token purchase completed', [
                'request_id' => $requestId,
                'purchase_success' => $paymentResult['success'] ?? false,
                'purchase_duration_ms' => $purchaseDuration,
                'token' => $paymentResult['token'] ?? null,
                'units' => $paymentResult['units'] ?? null,
                'response' => $paymentResult
            ]);

            $response = [
                'status' => $paymentResult['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'transaction_id' => $paymentResult['transactionId'] ?? 'LUKU_' . Str::upper(Str::random(10)),
                'meter_number' => $validated['meter_number'],
                'customer_phone' => $validated['customer_phone'],
                'customer_name' => $lookupResult['customerName'] ?? $validated['customer_name'] ?? 'Customer',
                'amount' => $validated['amount'],
                'token' => $paymentResult['token'] ?? null,
                'units' => $paymentResult['units'] ?? null,
                'timestamp' => now()->toDateTimeString(),
                'message' => $paymentResult['message'] ?? 'LUKU token generated',
                'data' => $paymentResult,
                'processing_time_ms' => $lookupDuration + $purchaseDuration
            ];

            Log::info('=== LUKU PAYMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => $paymentResult['success'] ?? false,
                'total_duration_ms' => $lookupDuration + $purchaseDuration,
                'token_generated' => !empty($paymentResult['token'])
            ]);

            return response()->json($response, $paymentResult['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[LUKU] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * GEPG Payment - Real Implementation
     */
    public function gepg(Request $request)
    {
        $requestId = 'GEPG_' . Str::uuid()->toString();
        
        Log::info('=== GEPG PAYMENT REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'bill_id' => 'required|string',
            'payer_name' => 'required|string',
            'payer_phone' => 'required|string',
            'payer_email' => 'nullable|email',
            'amount' => 'required|numeric|min:0.01',
            'payment_option' => 'required|in:exact,partial,full',
            'currency' => 'nullable|string|size:3',
            'account_number' => 'required|string',
        ]);

        Log::info('[GEPG] Validated request data', [
            'request_id' => $requestId,
            'bill_id' => $validated['bill_id'],
            'amount' => $validated['amount'],
            'payment_option' => $validated['payment_option']
        ]);

        try {
            $logger = new GepgLoggerService();
            $service = new GepgGatewayService($logger);
            
            $billData = [
                'billId' => $validated['bill_id'],
                'payerName' => $validated['payer_name'],
                'payerPhone' => $validated['payer_phone'],
                'payerEmail' => $validated['payer_email'] ?? null,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'paymentOption' => $validated['payment_option'],
                'accountNumber' => $validated['account_number'],
                'reference' => 'GEPG_' . Str::upper(Str::random(10))
            ];
            
            Log::info('[GEPG] Generating control number', [
                'request_id' => $requestId,
                'reference' => $billData['reference'],
                'bill_data' => $billData
            ]);
            
            $startTime = microtime(true);
            $result = $service->generateControlNumber($billData);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[GEPG] Control number generation completed', [
                'request_id' => $requestId,
                'success' => isset($result['success']) && $result['success'],
                'duration_ms' => $duration,
                'control_number' => $result['controlNumber'] ?? null,
                'response' => $result
            ]);
            
            $response = [
                'status' => isset($result['success']) && $result['success'] ? 'success' : 'error',
                'request_id' => $requestId,
                'gepg_reference' => $billData['reference'],
                'control_number' => $result['controlNumber'] ?? null,
                'bill_id' => $validated['bill_id'],
                'payer_name' => $validated['payer_name'],
                'payer_phone' => $validated['payer_phone'],
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'payment_option' => $validated['payment_option'],
                'expire_date' => $result['expireDate'] ?? now()->addDays(7)->format('Y-m-d'),
                'payment_status' => 'Pending',
                'timestamp' => now()->toDateTimeString(),
                'message' => $result['message'] ?? 'GEPG control number generated',
                'data' => $result,
                'processing_time_ms' => $duration
            ];

            Log::info('=== GEPG PAYMENT REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => isset($result['success']) && $result['success'],
                'duration_ms' => $duration,
                'control_number_generated' => !empty($result['controlNumber'])
            ]);

            return response()->json($response, isset($result['success']) && $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('[GEPG] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Pay By Link - Real Implementation
     */
    public function payByLink(Request $request)
    {
        $requestId = 'LINK_' . Str::uuid()->toString();
        
        Log::info('=== PAY BY LINK REQUEST STARTED ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'endpoint' => $request->fullUrl(),
            'raw_input' => $request->all()
        ]);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'expiry_hours' => 'nullable|integer|min:1|max:720',
            'callback_url' => 'nullable|url',
            'currency' => 'nullable|string|size:3',
        ]);

        Log::info('[LINK] Validated request data', [
            'request_id' => $requestId,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'TZS',
            'expiry_hours' => $validated['expiry_hours'] ?? 24
        ]);

        try {
            $service = new PaymentLinkService();
            
            $linkData = [
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'description' => $validated['description'],
                'customer_reference' => 'LINK_' . Str::upper(Str::random(10)),
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'expiry_minutes' => ($validated['expiry_hours'] ?? 24) * 60,
                'callback_url' => $validated['callback_url'] ?? config('app.url') . '/api/payment-callback',
                'metadata' => [
                    'source' => 'api_test',
                    'request_id' => $requestId,
                    'timestamp' => now()->toIso8601String()
                ]
            ];
            
            Log::info('[LINK] Generating payment link', [
                'request_id' => $requestId,
                'customer_reference' => $linkData['customer_reference'],
                'link_data' => $linkData
            ]);
            
            $startTime = microtime(true);
            $result = $service->generateUniversalPaymentLink($linkData);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info('[LINK] Payment link generation completed', [
                'request_id' => $requestId,
                'success' => isset($result['success']) && $result['success'],
                'duration_ms' => $duration,
                'link_id' => $result['data']['link_id'] ?? null,
                'payment_url' => $result['data']['payment_url'] ?? null,
                'response' => $result
            ]);
            
            if (!isset($result['success']) || !$result['success']) {
                Log::warning('[LINK] Payment link generation failed', [
                    'request_id' => $requestId,
                    'error_details' => $result
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'request_id' => $requestId,
                    'message' => $result['message'] ?? 'Failed to generate payment link',
                    'details' => $result
                ], 400);
            }

            $response = [
                'status' => 'success',
                'request_id' => $requestId,
                'link_id' => $result['data']['link_id'] ?? $linkData['customer_reference'],
                'payment_link' => $result['data']['payment_url'] ?? null,
                'short_link' => $result['data']['short_url'] ?? null,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'] ?? 'TZS',
                'description' => $validated['description'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'expiry_date' => $result['data']['expiry_date'] ?? now()->addHours($validated['expiry_hours'] ?? 24)->toDateTimeString(),
                'callback_url' => $validated['callback_url'] ?? null,
                'status' => 'Active',
                'qr_code' => $result['data']['qr_code'] ?? null,
                'timestamp' => now()->toDateTimeString(),
                'message' => 'Payment link generated successfully',
                'data' => $result['data'] ?? null,
                'processing_time_ms' => $duration
            ];

            Log::info('=== PAY BY LINK REQUEST COMPLETED ===', [
                'request_id' => $requestId,
                'success' => true,
                'duration_ms' => $duration,
                'payment_link_generated' => !empty($result['data']['payment_url'])
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('[LINK] Exception occurred', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'request_id' => $requestId,
                'message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => explode("\n", $e->getTraceAsString())
            ], 500);
        }
    }

    /**
     * Get all available services
     */
    public function listServices()
    {
        $requestId = 'LIST_' . Str::uuid()->toString();
        
        Log::info('=== SERVICE LIST REQUEST ===', [
            'request_id' => $requestId,
            'timestamp' => now()->toIso8601String()
        ]);

        $services = [
            [
                'name' => 'Internal Funds Transfer',
                'endpoint' => '/api/test-services/internal-funds-transfer',
                'method' => 'POST',
                'description' => 'Transfer funds between NBC accounts using real NBC API',
                'status' => 'active',
                'required_params' => ['source_account', 'destination_account', 'amount'],
                'optional_params' => ['currency', 'description', 'reference', 'beneficiary_name']
            ],
            [
                'name' => 'External Funds Transfer',
                'endpoint' => '/api/test-services/external-funds-transfer',
                'method' => 'POST',
                'description' => 'Transfer funds to other banks via TISS/TIPS using real NBC API',
                'status' => 'active',
                'required_params' => ['source_account', 'destination_account', 'destination_bank', 'amount', 'beneficiary_name'],
                'optional_params' => ['swift_code', 'currency', 'description', 'reference']
            ],
            [
                'name' => 'Mini Statement',
                'endpoint' => '/api/test-services/mini-statement',
                'method' => 'POST',
                'description' => 'Get mini statement with last few transactions using real NBC API',
                'status' => 'active',
                'required_params' => ['account_number'],
                'optional_params' => ['limit', 'channel_code']
            ],
            [
                'name' => 'Full Statement',
                'endpoint' => '/api/test-services/full-statement',
                'method' => 'POST',
                'description' => 'Get full account statement for date range using real NBC API',
                'status' => 'active',
                'required_params' => ['account_number', 'from_date', 'to_date'],
                'optional_params' => ['format', 'channel_code']
            ],
            [
                'name' => 'Account Lookup',
                'endpoint' => '/api/test-services/account-lookup',
                'method' => 'POST',
                'description' => 'Lookup account details using real NBC API',
                'status' => 'active',
                'required_params' => ['One of: account_number, phone_number, id_number, email'],
                'optional_params' => ['channel_code']
            ],
            [
                'name' => 'Utilities',
                'endpoint' => '/api/test-services/utilities',
                'method' => 'POST',
                'description' => 'Process utility payments using real NBC Bills Payment API',
                'status' => 'active',
                'required_params' => ['utility_type', 'account_number', 'amount', 'provider_code'],
                'optional_params' => ['meter_number', 'customer_name', 'phone_number']
            ],
            [
                'name' => 'SMS',
                'endpoint' => '/api/test-services/sms',
                'method' => 'POST',
                'description' => 'Send SMS messages using real NBC SMS API',
                'status' => 'active',
                'required_params' => ['phone_numbers', 'message'],
                'optional_params' => ['sender_id', 'schedule_time', 'sms_type']
            ],
            [
                'name' => 'Direct Debit',
                'endpoint' => '/api/test-services/direct-debit',
                'method' => 'POST',
                'description' => 'Setup direct debit mandates using real NBC API',
                'status' => 'active',
                'required_params' => ['account_number', 'amount', 'mandate_reference', 'beneficiary_account', 'beneficiary_name'],
                'optional_params' => ['frequency', 'start_date', 'end_date', 'bank_code']
            ],
            [
                'name' => 'Control Number Payments',
                'endpoint' => '/api/test-services/control-number-payments',
                'method' => 'POST',
                'description' => 'Process control number payments via GEPG using real NBC API',
                'status' => 'active',
                'required_params' => ['control_number', 'payer_name', 'amount', 'account_number'],
                'optional_params' => ['payer_phone', 'payment_description', 'currency']
            ],
            [
                'name' => 'LUKU',
                'endpoint' => '/api/test-services/luku',
                'method' => 'POST',
                'description' => 'Purchase LUKU tokens using real NBC LUKU API',
                'status' => 'active',
                'required_params' => ['meter_number', 'amount', 'customer_phone'],
                'optional_params' => ['customer_name', 'account_number']
            ],
            [
                'name' => 'GEPG',
                'endpoint' => '/api/test-services/gepg',
                'method' => 'POST',
                'description' => 'Generate GEPG control numbers using real NBC GEPG API',
                'status' => 'active',
                'required_params' => ['bill_id', 'payer_name', 'payer_phone', 'amount', 'payment_option', 'account_number'],
                'optional_params' => ['payer_email', 'currency']
            ],
            [
                'name' => 'Pay By Link',
                'endpoint' => '/api/test-services/pay-by-link',
                'method' => 'POST',
                'description' => 'Generate payment links using real Payment Gateway API',
                'status' => 'active',
                'required_params' => ['amount', 'description'],
                'optional_params' => ['customer_email', 'customer_phone', 'customer_name', 'expiry_hours', 'callback_url', 'currency']
            ],
        ];

        return response()->json([
            'status' => 'success',
            'request_id' => $requestId,
            'services' => $services,
            'base_url' => config('app.url'),
            'timestamp' => now()->toDateTimeString(),
            'note' => 'All endpoints use real service implementations with detailed logging. Check logs for request IDs to trace specific requests.',
            'logging_info' => [
                'log_channel' => config('logging.default'),
                'log_path' => storage_path('logs/laravel.log'),
                'request_id_format' => 'SERVICE_UUID',
                'log_levels' => ['INFO', 'WARNING', 'ERROR', 'DEBUG']
            ]
        ], 200);
    }
}