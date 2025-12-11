<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\DB;
class BillingController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function inquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channelId' => 'required|string',
            'spCode' => 'required|string',
            'requestType' => 'required|string|in:inquiry',
            'timestamp' => 'required|date',
            'userId' => 'required|string',
            'branchCode' => 'required|string',
            'channelRef' => 'required|string',
            'billRef' => 'required|string',
            'extraFields' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => '601',
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'spCode' => $request->spCode,
                'channelId' => $request->channelId,
                'requestType' => 'inquiry',
                'channelRef' => $request->channelRef,
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'data' => []
            ], 422);
        }

        try {
            $bill = Bill::with(['member', 'service'])
                ->where('control_number', $request->billRef)
                ->first();

            if (!$bill) {
                return response()->json([
                    'statusCode' => '602',
                    'message' => 'Bill not found',
                    'spCode' => $request->spCode,
                    'channelId' => $request->channelId,
                    'requestType' => 'inquiry',
                    'channelRef' => $request->channelRef,
                    'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                    'data' => []
                ], 404);
            }

           

            $member = DB::table('clients')->where('client_number', $bill->member_id)->first();

            //return $member;

            return response()->json([
                'statusCode' => '600',
                'message' => 'Success',
                'channelId' => $request->channelId,
                'spCode' => $request->spCode,
                'requestType' => 'inquiry',
                'channelRef' => $request->channelRef,
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'billDetails' => [
                    'billRef' => $bill->control_number,
                    'serviceName' => $bill->service->name,
                    'description' => $bill->service->description,
                    'billCreatedAt' => $bill->created_at->format('Y-m-d\TH:i:s'),
                    'totalAmount' => (string)$bill->amount_due,
                    'balance' => (string)($bill->amount_due - $bill->amount_paid),
                    'phoneNumber' => $member->phone_number ?? 'N/A',
                    'billedName' => $member->first_name ?? 'N/A' . ' ' . $member->last_name ?? 'N/A',
                    'currency' => 'TZS',
                    'paymentMode' => $this->getPaymentMode($bill->payment_mode),
                    'expiryDate' => $bill->due_date->format('Ymd\THis'),
                    'creditAccount' => $bill->credit_account_number,
                    'creditCurrency' => 'TZS',
                    'extraFields' => $request->extraFields ?? []
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Bill inquiry failed: ' . $e->getMessage());
            return response()->json([
                'statusCode' => '602',
                'message' => 'Failed to process inquiry: ' . $e->getMessage(),
                'spCode' => $request->spCode,
                'channelId' => $request->channelId,
                'requestType' => 'inquiry',
                'channelRef' => $request->channelRef,
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'data' => []
            ], 500);
        }
    }

   public function getPaymentMode(int $mode): string
    {
        return match ($mode) {
            1 => 'Partial',   // Multiple installments; final installment can exceed remaining balance
            2 => 'Full',      // Single installment; amount >= billed amount
            3 => 'Exact',     // Single installment; amount == billed amount
            4 => 'Limited',   // Multiple installments; final installment == remaining balance
            5 => 'Infinity',  // Any amount > 0; infinite installments allowed
            default => 'Unknown',
        };
    }





    

    public function paymentNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channelId' => 'required|string',
            'spCode' => 'required|string',
            'requestType' => 'required|string|in:payment',
            'approach' => 'required|string|in:async,sync',
            'callbackUrl' => 'required_if:approach,async|url',
            'timestamp' => 'required|date',
            'userId' => 'required|string',
            'branchCode' => 'required|string',
            'billRef' => 'required|string',
            'channelRef' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'creditAccount' => 'required|string',
            'creditCurrency' => 'required|string|size:3',
            'paymentType' => 'required|string',
            'channelCode' => 'required|string',
            'payerName' => 'required|string',
            'payerPhone' => 'required|string',
            'payerEmail' => 'required|email',
            'narration' => 'required|string',
            'extraFields' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'statusCode' => '601',
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'spCode' => $request->spCode,
                'channelId' => $request->channelId,
                'requestType' => 'payment',
                'channelRef' => $request->channelRef,
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'paymentDetails' => null
            ], 422);
        }

        try {
            // Check if bill is already fully paid (non-infinity mode only)
            // Infinity mode (payment_mode 5) allows unlimited payments even after PAID status
            $bill = Bill::where('control_number', $request->billRef)->first();
            if ($bill && $bill->status === 'PAID' && $bill->payment_mode != '5') {
                return response()->json([
                    'statusCode' => '601',
                    'message' => 'Error, Transaction reference number already paid',
                    'spCode' => $request->spCode,
                    'channelId' => $request->channelId,
                    'requestType' => 'payment',
                    'channelRef' => $request->channelRef,
                    'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                    'paymentDetails' => null
                ], 409);
            }

            // Generate unique references using cryptographically secure functions
            $gatewayRef = 'PE' . time() . random_int(1000, 9999);
            $billerReceipt = 'RCPT' . random_int(100000, 999999);

            // Generate unique transaction reference (different from channelRef)
            $transactionReference = 'TXN' . time() . random_int(10000, 99999) . substr(hash('sha256', $request->channelRef . microtime()), 0, 6);
            
            Log::info('Generated unique references', [
                'channel_ref' => $request->channelRef,
                'transaction_reference' => $transactionReference,
                'gateway_ref' => $gatewayRef,
                'biller_receipt' => $billerReceipt
            ]);

            // Create transaction record
            // Step 1: Check if transaction exists using correlation_id (channelRef) only
            // Note: We don't check billRef because infinity mode bills can have multiple payments
            $existingTransaction = Transaction::where('correlation_id', $request->channelRef)->first();

            if ($existingTransaction) {
                if (in_array($existingTransaction->status, ['COMPLETED', 'PAID'])) {
                    Log::info("Transaction with this channel reference already processed", [
                        'correlation_id' => $request->channelRef,
                        'existing_status' => $existingTransaction->status
                    ]);

                    return response()->json([
                        'statusCode' => '601',
                        'message' => 'Error, Transaction reference number already paid',
                        'spCode' => $request->spCode,
                        'channelId' => $request->channelId,
                        'requestType' => 'payment',
                        'channelRef' => $request->channelRef,
                        'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                        'paymentDetails' => null
                    ], 409); // HTTP 409 Conflict
                }

                Log::warning("Transaction with correlation_id exists but not completed", [
                    'correlation_id' => $request->channelRef,
                    'existing_status' => $existingTransaction->status
                ]);
            }

            // Step 2: Proceed to insert transaction
            Log::info('Preparing transaction data for creation', [
                'reference' => $transactionReference,
                'channel_ref' => $request->channelRef,
                'external_reference' => $request->billRef,
                'source' => 'payment_api',
                'type' => 'credit',
                'amount' => $request->amount,
                'currency' => $request->creditCurrency,
                'service_name' => 'Payment API',
                'narration' => $request->narration,
                'status' => 'PENDING',
                'received_at' => now()->toDateTimeString(),
                'channel_id' => $request->channelId,
                'sp_code' => $request->spCode,
                'gateway_ref' => $gatewayRef,
                'biller_receipt' => $billerReceipt,
                'payment_type' => $request->paymentType,
                'channel_code' => $request->channelCode,
                'payer_name' => $request->payerName,
                'payer_phone' => $request->payerPhone,
                'payer_email' => $request->payerEmail,
                'extra_fields_type' => gettype($request->extraFields),
                'extra_fields_value' => $request->extraFields,
                'raw_payload_size' => strlen(json_encode($request->all()))
            ]);

            try {
                // Ensure extra_fields is properly formatted
                $extraFields = $request->extraFields;
                if (is_string($extraFields)) {
                    try {
                        $extraFields = json_decode($extraFields, true) ?? [];
                    } catch (\Exception $e) {
                        Log::warning('Failed to decode extra_fields JSON string, using empty array', [
                            'extra_fields' => $extraFields,
                            'error' => $e->getMessage()
                        ]);
                        $extraFields = [];
                    }
                } elseif (!is_array($extraFields)) {
                    $extraFields = [];
                }

                $transactionData = [
                    // Core transaction identifiers
                    'reference' => (string) $transactionReference,
                    'external_reference' => (string) $request->billRef,
                    'correlation_id' => (string) $request->channelRef,

                    // Transaction details
                    'source' => 'payment_api',
                    'type' => 'credit',
                    'transaction_category' => 'BILL_PAYMENT',
                    'transaction_subcategory' => 'INCOMING',
                    'amount' => (float) $request->amount,
                    'currency' => (string) ($request->creditCurrency ?? 'TZS'),
                    'service_name' => 'Bills Payment API',
                    'narration' => (string) $request->narration,
                    'description' => (string) $request->narration,
                    'status' => 'PENDING',
                    'reconciliation_status' => 'UNRECONCILED',

                    // Channel and source information
                    'channel_id' => (string) $request->channelId,
                    'channel_code' => (string) $request->channelCode,
                    'sp_code' => (string) $request->spCode,
                    'gateway_ref' => (string) $gatewayRef,
                    'biller_receipt' => (string) $billerReceipt,
                    'payment_type' => (string) $request->paymentType,

                    // Payer information (third party data)
                    'payer_name' => (string) $request->payerName,
                    'payer_phone' => (string) $request->payerPhone,
                    'payer_email' => (string) $request->payerEmail,

                    // Credit account information
                    'lookup_account_number' => (string) $request->creditAccount,

                    // External system tracking
                    'external_system' => 'NBC_BILLS_PAYMENT',
                    'external_transaction_id' => (string) $request->channelRef,

                    // User and branch information
                    'initiated_by' => (string) $request->userId,

                    // Timestamps
                    'received_at' => now(),
                    'initiated_at' => $request->timestamp ? \Carbon\Carbon::parse($request->timestamp) : now(),
                    'created_at' => now(),
                    'updated_at' => now(),

                    // Request tracking
                    'client_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),

                    // Store full payloads for audit trail
                    'raw_payload' => json_encode($request->all()),
                    'external_request_payload' => $request->all(),
                    'extra_fields' => $extraFields,

                    // Third party request details (individual fields)
                    'callback_url' => $request->callbackUrl ?? null,
                    'branch_code' => (string) $request->branchCode,
                    'approach' => (string) $request->approach,
                    'request_timestamp' => $request->timestamp ? \Carbon\Carbon::parse($request->timestamp) : null,
                    'credit_account' => (string) $request->creditAccount,
                    'credit_currency' => (string) ($request->creditCurrency ?? 'TZS'),

                    // Flags
                    'is_manual' => false,
                    'is_system_generated' => false,
                    'requires_approval' => false,
                ];
                
                Log::info('Transaction data prepared, attempting to create transaction', [
                    'data_keys' => array_keys($transactionData),
                    'data_types' => array_map('gettype', $transactionData)
                ]);

                $transaction = Transaction::create($transactionData);

                Log::info("Transaction successfully created: {$transaction->reference}", [
                    'transaction_id' => $transaction->id,
                    'created_at' => $transaction->created_at
                ]);
                
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Database query exception during transaction creation', [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'sql' => $e->getSql() ?? 'N/A',
                    'bindings' => $e->getBindings() ?? [],
                    'request_data' => $request->all()
                ]);
                throw $e;
            } catch (\Exception $e) {
                Log::error('General exception during transaction creation', [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'request_data' => $request->all()
                ]);
                throw $e;
            }

            if ($request->approach === 'async') {
                // Process payment asynchronously
                $requestData = $request->all();
                $transactionId = $transaction->id;
                dispatch(function() use ($requestData, $transactionId, $gatewayRef, $billerReceipt) {
                    $this->processAsyncPayment(
                        collect($requestData),
                        Transaction::find($transactionId),
                        $gatewayRef,
                        $billerReceipt
                    );
                })->afterResponse();

                // For async approach, return immediate acknowledgment
                return response()->json([
                    'statusCode' => '600',
                    'message' => 'Received and validated, engine is now processing your request',
                    'channelId' => $request->channelId,
                    'spCode' => $request->spCode,
                    'requestType' => 'payment',
                    'channelRef' => $request->channelRef,
                    'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                    'paymentDetails' => [
                        'billRef' => $request->billRef,
                        'gatewayRef' => $gatewayRef,
                        'amount' => $request->amount,
                        'currency' => $request->creditCurrency,
                        'transactionTime' => now()->format('Ymd\THis'),
                        'billerReceipt' => $billerReceipt,
                        'remarks' => 'Successfully received',
                        'extraFields' => $request->extraFields ?? []
                    ]
                ]);
            } else {
                // For sync approach, process immediately and return result 
                    $paymentResult = $this->billingService->processPayment(
                        $request->billRef,
                        [
                            'payment_ref' => $request->channelRef,
                            'amount' => $request->amount,
                            'payment_channel' => $request->channelCode,
                            'paid_at' => $request->timestamp,
                            'raw_payload' => json_encode($request->all())
                        ]
                    );

                    if (!$paymentResult['success']) {
                        $transaction->update([
                            'status' => 'FAILED',
                            'external_status_message' => substr($paymentResult['message'], 0, 255), // Limit message to 255 characters
                        ]);

                        return response()->json([
                            'statusCode' => '601',
                            'message' => $paymentResult['message'],
                            'channelId' => $request->channelId,
                            'spCode' => $request->spCode,
                            'requestType' => 'payment',
                            'channelRef' => $request->channelRef,
                            'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                            'paymentDetails' => [
                                'billRef' => $request->billRef,
                                'amount' => $request->amount,
                                'transactionTime' => now()->format('Ymd\THis'),
                                'remarks' => 'Payment processing failed',
                                'extraFields' => $request->extraFields ?? []
                            ]
                        ], $paymentResult['status_code']);
                    }

                    $transaction->update([
                        'status' => 'COMPLETED',
                        'external_status_message' => 'Payment processed successfully',
                    ]);

                    return response()->json([
                        'statusCode' => '600',
                        'message' => 'Success',
                        'channelId' => $request->channelId,
                        'spCode' => $request->spCode,
                        'requestType' => 'payment',
                        'channelRef' => $request->channelRef,
                        'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                        'paymentDetails' => [
                            'billRef' => $request->billRef,
                            'gatewayRef' => $gatewayRef,
                            'amount' => $request->amount,
                            'currency' => $request->creditCurrency,
                            'transactionTime' => now()->format('Ymd\THis'),
                            'billerReceipt' => $billerReceipt,
                            'remarks' => 'Successfully received',
                            'extraFields' => $request->extraFields ?? []
                        ]
                    ]);
            }

        } catch (Exception $e) {
            Log::error('Payment notification processing failed: ' . $e->getMessage());

            if (isset($transaction)) {
                $transaction->update([
                    'status' => 'FAILED',
                    'external_status_message' => 'Exception occurred during processing',
                    'error_message' => substr($e->getMessage(), 0, 255) // Limit error message to 255 characters
                ]);
            }

            return response()->json([
                'statusCode' => '601',
                'message' => 'Failed to process payment notification: ' . $e->getMessage(),
                'spCode' => $request->spCode,
                'channelId' => $request->channelId,
                'requestType' => 'payment',
                'channelRef' => $request->channelRef,
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'paymentDetails' => [
                            'billRef' => $request->billRef,
                            'gatewayRef' => $gatewayRef,
                            'amount' => $request->amount,
                            'currency' => $request->creditCurrency,
                            'transactionTime' => now()->format('Ymd\THis'),
                            'billerReceipt' => $billerReceipt,
                            'remarks' => 'Successfully received',
                            'extraFields' => $request->extraFields ?? []
                        ]
            ], 500);
        }
    }










private function processAsyncPayment($requestData, $transaction, $gatewayRef, $billerReceipt)
{
    Log::info('Starting async payment processing', [
        'channelRef' => $requestData['channelRef'],
        'billRef' => $requestData['billRef'],
        'amount' => $requestData['amount']
    ]);

    try {
        Log::info('Calling billingService->processPayment', [
            'payment_ref' => $requestData['channelRef'],
            'amount' => $requestData['amount']
        ]);

        $paymentResult = $this->billingService->processPayment(
            $requestData['billRef'],
            [
                'payment_ref' => $requestData['channelRef'],
                'amount' => $requestData['amount'],
                'payment_channel' => $requestData['channelCode'],
                'paid_at' => $requestData['timestamp'],
                'raw_payload' => json_encode($requestData)
            ]
        );

        Log::info('Payment processed in billingService', [
            'result' => $paymentResult
        ]);

        if (!$paymentResult['success']) {
            // Handle failed payment processing
            $errorMessage = $paymentResult['message'] ?? 'Payment processing failed';
            
                            $transaction->update([
                    'status' => 'FAILED',
                    'external_status_message' => substr($errorMessage, 0, 255), // Limit message to 255 characters
                    'error_message' => substr($errorMessage, 0, 255) // Limit error message to 255 characters
                ]);

            Log::info('Transaction updated to failed status', [
                'transaction_id' => $transaction->id,
                'reason' => $errorMessage
            ]);

            $callbackData = [
                'statusCode' => '601',
                'message' => $errorMessage,
                'channelId' => $requestData['channelId'],
                'spCode' => $requestData['spCode'],
                'requestType' => 'payment',
                'channelRef' => $requestData['channelRef'],
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'paymentDetails' => [
                    'billRef' => $requestData['billRef'],
                    'amount' => $requestData['amount'],
                    'transactionTime' => now()->format('Ymd\THis'),
                    'remarks' => 'Payment processing failed',
                    'gatewayRef' => $gatewayRef,
                    'currency' => $requestData['creditCurrency'],
                    'billerReceipt' => $billerReceipt,
                    'extraFields' => $requestData['extraFields'] ?? []
                ]
            ];
        } else {
            // Handle successful payment processing
            $transaction->update([
                'status' => 'COMPLETED',
                'external_status_message' => 'Payment processed successfully',
            ]);

            Log::info('Transaction updated to success', [
                'transaction_id' => $transaction->id
            ]);



           

            $callbackData = [
                'statusCode' => '600',
                'message' => 'Success',
                'channelId' => $requestData['channelId'],
                'spCode' => $requestData['spCode'],
                'requestType' => 'payment',
                'channelRef' => $requestData['channelRef'],
                'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
                'paymentDetails' => [
                    'billRef' => $requestData['billRef'],
                    'gatewayRef' => $gatewayRef,
                    'amount' => $requestData['amount'],
                    'currency' => $requestData['creditCurrency'],
                    'transactionTime' => now()->format('Ymd\THis'),
                    'billerReceipt' => $billerReceipt,
                    'remarks' => 'Successfully received',
                    'extraFields' => $requestData['extraFields'] ?? []
                ]
            ];
        }

        Log::info('Sending callback to partner', [
            'callbackUrl' => $requestData['callbackUrl'],
            'payload' => $callbackData
        ]);

        try {
            $response = Http::post($requestData['callbackUrl'], $callbackData);
            
            if ($response->successful()) {
                Log::info('Callback sent successfully');
            } else {
                Log::error('Callback failed with status: ' . $response->status(), [
                    'response' => $response->body()
                ]);
                // You might want to queue a retry here
            }
        } catch (Exception $e) {
            Log::error('Failed to send callback', [
                'error' => $e->getMessage()
            ]);
            // You might want to queue a retry here
        }

    } catch (Exception $e) {
        Log::error('Async payment processing failed', [
            'error' => $e->getMessage(),
            'channelRef' => $requestData['channelRef']
        ]);

                        $transaction->update([
                    'status' => 'FAILED',
                    'external_status_message' => 'Async processing failed',
                    'error_message' => substr($e->getMessage(), 0, 255) // Limit error message to 255 characters
                ]);

        Log::info('Transaction updated to failed status', [
            'transaction_id' => $transaction->id
        ]);

        $callbackData = [
            'statusCode' => '601',
            'message' => 'Payment processing failed: ' . $e->getMessage(),
            'channelId' => $requestData['channelId'],
            'spCode' => $requestData['spCode'],
            'requestType' => 'payment',
            'channelRef' => $requestData['channelRef'],
            'timestamp' => now()->format('Y-m-d\TH:i:s.u'),
            'paymentDetails' => [
                'billRef' => $requestData['billRef'],
                'amount' => $requestData['amount'],
                'transactionTime' => now()->format('Ymd\THis'),
                'remarks' => 'Payment processing failed',
                'gatewayRef' => $gatewayRef,
                'currency' => $requestData['creditCurrency'],
                'billerReceipt' => $billerReceipt,
                'extraFields' => $requestData['extraFields'] ?? []
            ]
        ];

        try {
            Http::post($requestData['callbackUrl'], $callbackData);
            Log::info('Failure callback sent successfully');
        } catch (Exception $ex) {
            Log::error('Failed to send failure callback', [
                'error' => $ex->getMessage()
            ]);
        }
    }
}





public function status(Request $request)
{
    Log::info('Status check request received', [
        'request_data' => $request->all(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent()
    ]);

    try {
        $validated = $request->validate([
            'channelId' => 'required|string',
            'spCode' => 'required|string',
            'requestType' => 'required|in:statusCheck',
            'timestamp' => 'required|date',
            'channelRef' => 'required|string',
            'billRef' => 'required|string',
        ]);

        Log::info('Status check request validated successfully', [
            'validated_data' => $validated
        ]);

        $bill = Bill::where('control_number', $validated['billRef'])->first();

        if (!$bill) {
            Log::warning('Bill not found during status check', [
                'bill_ref' => $validated['billRef'],
                'channel_ref' => $validated['channelRef']
            ]);

            return response()->json([
                'statusCode' => '603',
                'message' => 'Bill not found',
                'spCode' => $validated['spCode'],
                'channelId' => $validated['channelId'],
                'requestType' => 'statusCheck',
                'channelRef' => $validated['channelRef'],
                'timestamp' => now()->toISOString(),
                'paymentDetails' => null
            ], 404);
        }

        Log::info('Bill found for status check', [
            'bill_id' => $bill->id,
            'control_number' => $bill->control_number,
            'status' => $bill->status,
            'amount_due' => $bill->amount_due,
            'amount_paid' => $bill->amount_paid
        ]);

        $response = [
            'statusCode' => '600',
            'message' => 'Success',
            'channelId' => $validated['channelId'],
            'spCode' => $validated['spCode'],
            'requestType' => 'statusCheck',
            'channelRef' => $validated['channelRef'],
            'timestamp' => now()->toISOString(),
            'paymentDetails' => [
                'billRef' => $bill->control_number,
                'gatewayRef' => $validated['channelRef'] ?? 'N/A',
                'amount' => (string) $bill->amount_due,
                'currency' => 'TZS',
                'transactionTime' => now()->format('Ymd\THis'),
                'billerReceipt' => 'N/A',
                'remarks' => 'Successfully received',
                'accountingStatus' => $bill->status === 'PAID' ? 'PAID' : 'PENDING',
                'billerNotified' => 'N/A',
                'extraFields' => new \stdClass()
            ]
        ];

        Log::info('Status check response prepared', [
            'response_data' => $response
        ]);

        return response()->json($response);

    } catch (Exception $e) {
        Log::error('Status check failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'statusCode' => '603',
            'message' => 'Failed to check bill status: ' . $e->getMessage(),
            'spCode' => $request->input('spCode', ''),
            'channelId' => $request->input('channelId', ''),
            'requestType' => 'statusCheck',
            'channelRef' => $request->input('channelRef', ''),
            'timestamp' => now()->toISOString(),
            'paymentDetails' => null
        ], 500);
    }
}



}