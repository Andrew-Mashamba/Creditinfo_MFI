<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

/**
 * NBC Controller
 * 
 * Handles requests for NBC member information display
 * 
 * @package App\Http\Controllers
 */
class NbcController extends Controller
{
    /**
     * Display NBC member payment page
     *
     * @param Request $request
     * @param string $memberNumber
     * @param string $clientNumber
     * @return \Illuminate\View\View
     */
    public function showMemberInfo(Request $request, string $memberNumber, string $clientNumber)
    {
        $requestId = 'nbc_' . time() . '_' . \Illuminate\Support\Str::random(8);
        
        Log::info('NBC member payment page request received', [
            'request_id' => $requestId,
            'member_number' => $memberNumber,
            'client_number' => $clientNumber,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        try {
            // Validate client number (using the second parameter)
            $validator = Validator::make(['client_number' => $clientNumber], [
                'client_number' => 'required|string|min:1|max:20'
            ], [
                'client_number.required' => 'Client number is required',
                'client_number.string' => 'Client number must be a string',
                'client_number.min' => 'Client number must not be empty',
                'client_number.max' => 'Client number must not exceed 20 characters'
            ]);

            if ($validator->fails()) {
                Log::warning('NBC member payment request validation failed', [
                    'request_id' => $requestId,
                    'client_number' => $clientNumber,
                    'errors' => $validator->errors()->toArray()
                ]);

                return view('nbc.error', [
                    'error' => 'Invalid client number: ' . $validator->errors()->first(),
                    'member_number' => $clientNumber
                ]);
            }

            // Clean the client number
            $clientNumber = trim($clientNumber);

            // Get client information from database using client_number
            $client = \App\Models\ClientsModel::where('client_number', $clientNumber)->first();
            
            if (!$client) {
                Log::warning('Client not found', [
                    'request_id' => $requestId,
                    'client_number' => $clientNumber
                ]);

                return view('nbc.error', [
                    'error' => 'Client not found with client number: ' . $clientNumber,
                    'member_number' => $clientNumber
                ]);
            }

            // Get pending bills for this client
            $pendingBills = \App\Models\Bill::where('client_number', $clientNumber)
                ->where('status', 'PENDING')
                ->with('service')
                ->get();

            Log::info('NBC member payment page request completed', [
                'request_id' => $requestId,
                'client_number' => $clientNumber,
                'client_found' => true,
                'pending_bills_count' => $pendingBills->count(),
                'status' => 'success'
            ]);

            return view('nbc.payment-livewire', [
                'memberNumber' => $memberNumber,
                'clientNumber' => $clientNumber
            ]);

        } catch (Exception $e) {
            Log::error('NBC member payment page request failed', [
                'request_id' => $requestId,
                'member_number' => $memberNumber,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('nbc.error', [
                'error' => 'An error occurred while processing the request',
                'member_number' => $memberNumber
            ]);
        }
    }

    /**
     * Process bill payment via MNO push notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPayment(Request $request)
    {
        $requestId = 'payment_' . time() . '_' . \Illuminate\Support\Str::random(8);
        
        Log::info('NBC payment processing request received', [
            'request_id' => $requestId,
            'request_data' => $request->all(),
            'bill_ids' => $request->input('bill_ids'),
            'bill_ids_type' => gettype($request->input('bill_ids')),
            'bill_ids_count' => is_array($request->input('bill_ids')) ? count($request->input('bill_ids')) : 'not array'
        ]);

        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'bill_ids' => 'required|array|min:1',
                'bill_ids.*' => 'integer',
                'phone_number' => 'required|string|regex:/^0[0-9]{9,10}$/',
                'mno_provider' => 'required|in:MPESA,AIRTEL,TIGOPESA,HALOPESA',
                'custom_amounts' => 'sometimes|array',
                'custom_amounts.*' => 'numeric|min:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first()
                ], 400);
            }

            $billIds = $request->input('bill_ids');
            $phoneNumber = $request->input('phone_number');
            $mnoProvider = $request->input('mno_provider');
            $customAmounts = $request->input('custom_amounts', []);

            Log::info('Processing payment with data', [
                'request_id' => $requestId,
                'bill_ids' => $billIds,
                'phone_number' => $phoneNumber,
                'mno_provider' => $mnoProvider,
                'custom_amounts' => $customAmounts
            ]);

            // Get bills
            $bills = \App\Models\Bill::whereIn('id', $billIds)
                ->where('status', 'PENDING')
                ->with(['service', 'member'])
                ->get();

            Log::info('Bills query result', [
                'request_id' => $requestId,
                'bills_found' => $bills->count(),
                'bill_ids_requested' => $billIds,
                'bills_retrieved' => $bills->pluck('id')->toArray()
            ]);

            if ($bills->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid pending bills found'
                ], 404);
            }

            // Calculate total amount including custom amounts
            $totalAmount = 0;
            foreach ($bills as $bill) {
                $customAmount = $customAmounts[$bill->id] ?? null;
                if ($customAmount && $customAmount > 0) {
                    // Check if this bill allows custom amounts
                    $serviceName = strtolower($bill->service->name ?? '');
                    $allowsCustomAmount = in_array($serviceName, [
                        'savings deposit', 
                        'member deposits', 
                        'fixed deposit', 
                        'loan payment', 
                        'loan repayment'
                    ]);
                    
                    if ($allowsCustomAmount) {
                        $totalAmount += $customAmount;
                    } else {
                        $totalAmount += $bill->amount_due;
                    }
                } else {
                    $totalAmount += $bill->amount_due;
                }
            }

            // Simulate MNO push notification
            $paymentResult = $this->simulateMnoPayment($phoneNumber, $mnoProvider, $totalAmount, $bills, $requestId);

            if ($paymentResult['success']) {
                // Update bills status to PAID
                foreach ($bills as $bill) {
                    $customAmount = $customAmounts[$bill->id] ?? null;
                    $amountToPay = $bill->amount_due;
                    
                    // Check if this bill allows custom amounts
                    $serviceName = strtolower($bill->service->name ?? '');
                    $allowsCustomAmount = in_array($serviceName, [
                        'savings deposit', 
                        'member deposits', 
                        'fixed deposit', 
                        'loan payment', 
                        'loan repayment'
                    ]);
                    
                    if ($customAmount && $customAmount > 0 && $allowsCustomAmount) {
                        $amountToPay = $customAmount;
                    }
                    
                    $bill->update([
                        'status' => 'PAID',
                        'amount_paid' => $amountToPay,
                        'updated_at' => now()
                    ]);

                    // Create payment record
                    \App\Models\Payment::create([
                        'bill_id' => $bill->id,
                        'payment_ref' => 'PAY_' . time() . '_' . $bill->id,
                        'transaction_reference' => $paymentResult['transaction_reference'],
                        'control_number' => $bill->control_number,
                        'amount' => $amountToPay,
                        'currency' => 'TZS',
                        'payment_channel' => $mnoProvider,
                        'payer_name' => $bills->first()->member->full_name ?? 'Unknown',
                        'payer_msisdn' => $phoneNumber,
                        'paid_at' => now(),
                        'received_at' => now(),
                        'status' => 'Confirmed',
                        'raw_payload' => json_encode($request->all()),
                        'response_data' => json_encode($paymentResult)
                    ]);
                }

                Log::info('Payment processed successfully', [
                    'request_id' => $requestId,
                    'bills_count' => $bills->count(),
                    'total_amount' => $totalAmount,
                    'transaction_reference' => $paymentResult['transaction_reference']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_reference' => $paymentResult['transaction_reference'],
                    'total_amount' => $totalAmount,
                    'bills_paid' => $bills->count()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed: ' . $paymentResult['message']
                ], 400);
            }

        } catch (Exception $e) {
            Log::error('Payment processing failed', [
                'request_id' => $requestId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing payment'
            ], 500);
        }
    }

    /**
     * Simulate MNO push notification payment
     *
     * @param string $phoneNumber
     * @param string $mnoProvider
     * @param float $amount
     * @param \Illuminate\Support\Collection $bills
     * @param string $requestId
     * @return array
     */
    private function simulateMnoPayment($phoneNumber, $mnoProvider, $amount, $bills, $requestId)
    {
        Log::info('Simulating MNO push notification', [
            'request_id' => $requestId,
            'phone_number' => $phoneNumber,
            'mno_provider' => $mnoProvider,
            'amount' => $amount,
            'bills_count' => $bills->count()
        ]);

        // Simulate processing delay
        sleep(2);

        // Simulate success (90% success rate for demo)
        $isSuccess = rand(1, 100) <= 90;

        if ($isSuccess) {
            $transactionReference = 'TXN_' . time() . '_' . strtoupper(substr(md5(rand()), 0, 8));
            
            Log::info('MNO payment simulation successful', [
                'request_id' => $requestId,
                'transaction_reference' => $transactionReference
            ]);

            return [
                'success' => true,
                'transaction_reference' => $transactionReference,
                'message' => 'Payment authorized successfully',
                'provider_response' => [
                    'status' => 'SUCCESS',
                    'message' => 'Transaction completed successfully',
                    'reference' => $transactionReference,
                    'timestamp' => now()->toISOString()
                ]
            ];
        } else {
            Log::warning('MNO payment simulation failed', [
                'request_id' => $requestId,
                'reason' => 'User declined payment'
            ]);

            return [
                'success' => false,
                'message' => 'Payment was declined by user',
                'provider_response' => [
                    'status' => 'FAILED',
                    'message' => 'User declined the payment request',
                    'timestamp' => now()->toISOString()
                ]
            ];
        }
    }


} 