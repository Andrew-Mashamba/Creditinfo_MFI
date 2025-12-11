<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DisburseLoanRequest;
use App\Http\Requests\Api\SimpleLoanDisbursementRequest;
use App\Http\Resources\LoanDisbursementResource;
use App\Services\Api\LoanDisbursementService;
use App\Services\Api\AutoLoanDisbursementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * API Controller for External Loan Disbursement
 * 
 * Provides API endpoints for external systems to trigger automatic loan disbursements.
 * Supports multiple payment methods and comprehensive validation.
 * 
 * @package App\Http\Controllers\Api
 * @version 1.0
 */
class LoanDisbursementController extends Controller
{
    private $disbursementService;
    private $autoLoanService;

    public function __construct(
        LoanDisbursementService $disbursementService,
        AutoLoanDisbursementService $autoLoanService
    ) {
        $this->disbursementService = $disbursementService;
        $this->autoLoanService = $autoLoanService;
    }

    /**
     * Disburse a loan through API
     * 
     * @param DisburseLoanRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @api {post} /api/v1/loans/disburse Disburse Loan
     * @apiName DisburseLoan
     * @apiGroup Loans
     * @apiVersion 1.0.0
     * 
     * @apiHeader {String} Authorization Bearer token
     * @apiHeader {String} X-API-Key API key for external system
     * 
     * @apiParam {String} loan_id Unique loan identifier
     * @apiParam {String} payment_method Payment method (CASH, NBC_ACCOUNT, TIPS_MNO, TIPS_BANK)
     * @apiParam {Object} [payment_details] Payment method specific details
     * @apiParam {String} [payment_details.account_number] Account number for NBC_ACCOUNT
     * @apiParam {String} [payment_details.phone_number] Phone number for TIPS_MNO
     * @apiParam {String} [payment_details.mno_provider] MNO provider for TIPS_MNO
     * @apiParam {String} [payment_details.bank_code] Bank code for TIPS_BANK
     * @apiParam {String} [payment_details.bank_account] Bank account for TIPS_BANK
     * @apiParam {String} [narration] Custom narration for transaction
     * @apiParam {Boolean} [validate_only=false] Only validate without processing
     * 
     * @apiSuccess {Boolean} success Request success status
     * @apiSuccess {String} message Response message
     * @apiSuccess {Object} data Disbursement details
     * @apiSuccess {String} data.transaction_id Unique transaction identifier
     * @apiSuccess {String} data.loan_id Loan identifier
     * @apiSuccess {String} data.status Disbursement status
     * @apiSuccess {Number} data.disbursed_amount Net amount disbursed
     * @apiSuccess {Object} data.deductions Deduction breakdown
     * @apiSuccess {String} data.payment_reference Payment reference/control number
     * @apiSuccess {String} data.disbursement_date Disbursement timestamp
     */
    public function disburse(DisburseLoanRequest $request)
    {
        $startTime = microtime(true);
        $transactionId = uniqid('API_DISB_');
        
        try {
            Log::info('API Loan Disbursement Request Received', [
                'transaction_id' => $transactionId,
                'loan_id' => $request->loan_id,
                'payment_method' => $request->payment_method,
                'api_user' => $request->user()->id ?? 'external',
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Start database transaction
            DB::beginTransaction();

            // Validate loan exists and is eligible for disbursement
            $loan = $this->disbursementService->validateLoan($request->loan_id);

            // If validate_only flag is set, return validation result
            if ($request->validate_only) {
                DB::rollback();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Loan validated successfully. Ready for disbursement.',
                    'data' => [
                        'transaction_id' => $transactionId,
                        'loan_id' => $loan->loan_id,
                        'validation_status' => 'PASSED',
                        'loan_details' => [
                            'client_number' => $loan->client_number,
                            'loan_amount' => $loan->principle,
                            'loan_type' => $loan->loan_type,
                            'status' => $loan->status
                        ]
                    ]
                ], 200);
            }

            // Process the disbursement
            $result = $this->disbursementService->processDisbursement(
                $loan,
                $request->payment_method,
                $request->payment_details ?? [],
                $request->narration,
                $transactionId
            );

            // Commit transaction
            DB::commit();

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('API Loan Disbursement Successful', [
                'transaction_id' => $transactionId,
                'loan_id' => $loan->loan_id,
                'execution_time_ms' => $executionTime,
                'disbursed_amount' => $result['net_disbursement_amount']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan disbursed successfully',
                'data' => new LoanDisbursementResource($result),
                'meta' => [
                    'execution_time_ms' => $executionTime,
                    'timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (Exception $e) {
            DB::rollback();

            Log::error('API Loan Disbursement Failed', [
                'transaction_id' => $transactionId,
                'loan_id' => $request->loan_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            $statusCode = $this->getErrorStatusCode($e);

            return response()->json([
                'success' => false,
                'message' => $this->sanitizeErrorMessage($e->getMessage()),
                'error' => [
                    'code' => $e->getCode() ?: 'DISBURSEMENT_ERROR',
                    'transaction_id' => $transactionId
                ],
                'meta' => [
                    'timestamp' => now()->toISOString()
                ]
            ], $statusCode);
        }
    }

    /**
     * Get disbursement status
     * 
     * @param Request $request
     * @param string $transactionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request, $transactionId)
    {
        try {
            $status = $this->disbursementService->getTransactionStatus($transactionId);

            return response()->json([
                'success' => true,
                'data' => $status
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found',
                'error' => [
                    'code' => 'TRANSACTION_NOT_FOUND'
                ]
            ], 404);
        }
    }

    /**
     * Bulk disbursement endpoint
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDisburse(Request $request)
    {
        $request->validate([
            'disbursements' => 'required|array|min:1|max:100',
            'disbursements.*.loan_id' => 'required|string',
            'disbursements.*.payment_method' => 'required|string|in:CASH,NBC_ACCOUNT,TIPS_MNO,TIPS_BANK',
            'disbursements.*.payment_details' => 'sometimes|array'
        ]);

        $results = [];
        $successCount = 0;
        $failureCount = 0;

        foreach ($request->disbursements as $index => $disbursement) {
            try {
                $loan = $this->disbursementService->validateLoan($disbursement['loan_id']);
                
                $result = $this->disbursementService->processDisbursement(
                    $loan,
                    $disbursement['payment_method'],
                    $disbursement['payment_details'] ?? [],
                    $disbursement['narration'] ?? null,
                    uniqid('BULK_')
                );

                $results[] = [
                    'index' => $index,
                    'loan_id' => $disbursement['loan_id'],
                    'success' => true,
                    'data' => new LoanDisbursementResource($result)
                ];
                $successCount++;

            } catch (Exception $e) {
                $results[] = [
                    'index' => $index,
                    'loan_id' => $disbursement['loan_id'],
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $failureCount++;
            }
        }

        return response()->json([
            'success' => $failureCount === 0,
            'message' => "Processed {$successCount} successful and {$failureCount} failed disbursements",
            'data' => [
                'summary' => [
                    'total' => count($request->disbursements),
                    'successful' => $successCount,
                    'failed' => $failureCount
                ],
                'results' => $results
            ]
        ], 200);
    }

    /**
     * Simplified automatic loan creation and disbursement
     * 
     * @param SimpleLoanDisbursementRequest $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @api {post} /api/v1/loans/auto-disburse Auto Create & Disburse Loan
     * @apiName AutoDisburseLoan
     * @apiGroup Loans
     * @apiVersion 1.0.0
     * 
     * @apiHeader {String} Authorization Bearer token
     * @apiHeader {String} X-API-Key API key for external system
     * 
     * @apiParam {String} client_number Client number from the system
     * @apiParam {Number} amount Loan amount to disburse
     * 
     * @apiSuccess {Boolean} success Request success status
     * @apiSuccess {Object} data Loan and disbursement details
     * @apiSuccess {String} data.loan_id Auto-generated loan ID
     * @apiSuccess {String} data.transaction_id Transaction reference
     * @apiSuccess {Number} data.net_disbursed Net amount disbursed after deductions
     */
    public function autoDisburse(SimpleLoanDisbursementRequest $request)
    {
        $startTime = microtime(true);
        
        try {
            Log::info('Auto Loan Disbursement Request Received', [
                'client_number' => $request->client_number,
                'amount' => $request->amount,
                'tenure' => $request->tenure ?? 'default',
                'api_user' => $request->user()->id ?? 'external',
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Process automatic loan creation and disbursement
            $result = $this->autoLoanService->createAndDisburseLoan(
                $request->client_number,
                $request->amount,
                $request->tenure
            );

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('Auto Loan Disbursement Successful', [
                'loan_id' => $result['loan_id'],
                'transaction_id' => $result['transaction_id'],
                'execution_time_ms' => $executionTime,
                'net_disbursed' => $result['net_disbursed']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Loan created and disbursed successfully',
                'data' => [
                    'transaction_id' => $result['transaction_id'],
                    'loan_id' => $result['loan_id'],
                    'loan_account' => $result['loan_account'],
                    'client' => [
                        'number' => $result['client_number'],
                        'name' => $result['client_name'],
                        'nbc_account' => $result['nbc_account']
                    ],
                    'loan_details' => [
                        'amount' => $result['loan_amount'],
                        'tenure_months' => $result['tenure_months'],
                        'interest_rate' => $result['interest_rate'] . '%',
                        'monthly_installment' => $result['monthly_installment'],
                        'total_payable' => $result['total_payable']
                    ],
                    'disbursement' => [
                        'gross_amount' => $result['loan_amount'],
                        'deductions' => [
                            'total' => $result['deductions']['total'],
                            'charges' => $result['deductions']['charges'],
                            'insurance' => $result['deductions']['insurance'],
                            'first_interest' => $result['deductions']['first_interest'],
                            'breakdown' => $result['deductions']['breakdown']
                        ],
                        'net_disbursed' => $result['net_disbursed'],
                        'payment_method' => 'NBC_ACCOUNT',
                        'payment_reference' => $result['payment_reference'],
                        'disbursement_date' => $result['disbursement_date']
                    ],
                    'repayment' => [
                        'first_payment_date' => $result['first_payment_date'],
                        'control_numbers' => $result['control_numbers'],
                        'frequency' => 'MONTHLY'
                    ]
                ],
                'meta' => [
                    'execution_time_ms' => $executionTime,
                    'timestamp' => now()->toISOString()
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Auto Loan Disbursement Failed', [
                'client_number' => $request->client_number ?? null,
                'amount' => $request->amount ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            $statusCode = $this->getErrorStatusCode($e);

            return response()->json([
                'success' => false,
                'message' => $this->sanitizeErrorMessage($e->getMessage()),
                'error' => [
                    'code' => $e->getCode() ?: 'AUTO_DISBURSEMENT_ERROR'
                ],
                'meta' => [
                    'timestamp' => now()->toISOString()
                ]
            ], $statusCode);
        }
    }

    /**
     * Get appropriate HTTP status code based on exception
     * 
     * @param Exception $e
     * @return int
     */
    private function getErrorStatusCode(Exception $e)
    {
        $message = strtolower($e->getMessage());
        
        if (strpos($message, 'not found') !== false) {
            return 404;
        }
        
        if (strpos($message, 'validation') !== false || strpos($message, 'invalid') !== false) {
            return 422;
        }
        
        if (strpos($message, 'unauthorized') !== false) {
            return 401;
        }
        
        if (strpos($message, 'forbidden') !== false) {
            return 403;
        }
        
        if (strpos($message, 'insufficient funds') !== false) {
            return 402;
        }
        
        return 500;
    }

    /**
     * Sanitize error message for external consumption
     * 
     * @param string $message
     * @return string
     */
    private function sanitizeErrorMessage($message)
    {
        // Remove sensitive information from error messages
        $message = preg_replace('/\b[A-Z0-9]{10,}\b/', '[REDACTED]', $message);
        $message = preg_replace('/\b\d{4,}\b/', '[NUMBER]', $message);
        
        return $message;
    }
}