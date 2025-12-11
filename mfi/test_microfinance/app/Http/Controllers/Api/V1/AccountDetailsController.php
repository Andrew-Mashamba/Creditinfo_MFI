<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AccountDetailsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

/**
 * Account Details Controller
 * 
 * Handles requests to retrieve account details from external API
 * 
 * @package App\Http\Controllers\Api\V1
 */
class AccountDetailsController extends Controller
{
    /**
     * @var AccountDetailsService
     */
    private AccountDetailsService $accountDetailsService;

    /**
     * Constructor
     */
    public function __construct(AccountDetailsService $accountDetailsService)
    {
        $this->accountDetailsService = $accountDetailsService;
    }

    /**
     * Get account details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountDetails(Request $request): JsonResponse
    {
        $requestId = 'ctrl_' . time() . '_' . \Illuminate\Support\Str::random(8);
        
        Log::info('Account details API request received', [
            'request_id' => $requestId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'accountNumber' => 'required|string|min:1|max:50'
            ], [
                'accountNumber.required' => 'Account number is required',
                'accountNumber.string' => 'Account number must be a string',
                'accountNumber.min' => 'Account number must not be empty',
                'accountNumber.max' => 'Account number must not exceed 50 characters'
            ]);

            if ($validator->fails()) {
                Log::warning('Account details request validation failed', [
                    'request_id' => $requestId,
                    'errors' => $validator->errors()->toArray()
                ]);

                return response()->json([
                    'statusCode' => 400,
                    'message' => 'Invalid request: ' . $validator->errors()->first(),
                    'body' => []
                ], 400);
            }

            $accountNumber = trim($request->input('accountNumber'));

            // Get account details from external service
            $result = $this->accountDetailsService->getAccountDetails($accountNumber);

            // Determine HTTP status code based on result
            $httpStatusCode = $this->mapStatusCodeToHttpStatus($result['statusCode']);

            Log::info('Account details API request completed', [
                'request_id' => $requestId,
                'account_number' => $accountNumber,
                'status_code' => $result['statusCode'],
                'http_status' => $httpStatusCode
            ]);

            return response()->json($result, $httpStatusCode);

        } catch (Exception $e) {
            Log::error('Account details API request failed', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'statusCode' => 700,
                'message' => 'An error occurred while processing the request',
                'body' => []
            ], 500);
        }
    }

    /**
     * Map internal status code to HTTP status code
     *
     * @param int $statusCode
     * @return int
     */
    private function mapStatusCodeToHttpStatus(int $statusCode): int
    {
        $statusMap = [
            600 => 200,  // Success
            605 => 200,  // Account not found (still 200 as per API spec)
            400 => 400,  // Bad request
            401 => 401,  // Unauthorized
            700 => 500   // Internal error
        ];

        return $statusMap[$statusCode] ?? 500;
    }

    /**
     * Test external API connectivity
     *
     * @return JsonResponse
     */
    public function testConnectivity(): JsonResponse
    {
        try {
            $result = $this->accountDetailsService->testConnectivity();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result
            ], $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connectivity test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service statistics
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->accountDetailsService->getServiceStatistics();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
} 