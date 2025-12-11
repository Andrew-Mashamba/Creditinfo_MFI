<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;
use App\Events\PaymentStatusUpdated;

class PaymentCallbackController extends Controller
{
    /**
     * Handle NBC Bills Payment Engine callback
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePaymentCallback(Request $request)
    {
        try {
            // Log the incoming callback
            Log::info('NBC Payment Callback received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'ip' => $request->ip()
            ]);

            // Validate the callback payload
            $validatedData = $request->validate([
                'statusCode' => 'required|string',
                'message' => 'required|string',
                'channelId' => 'required|string',
                'spCode' => 'required|string',
                'requestType' => 'required|string',
                'channelRef' => 'required|string',
                'timestamp' => 'required|string',
                'paymentDetails' => 'nullable|array'
            ]);

            // Extract payment details
            $paymentDetails = $validatedData['paymentDetails'] ?? [];
            $statusCode = $validatedData['statusCode'];
            $channelRef = $validatedData['channelRef'];
            
            // Check if payment was successful
            if ($statusCode === '600' && !empty($paymentDetails)) {
                // Update transaction status in database if you have a transactions table
                $this->updateTransactionStatus($channelRef, 'completed', $paymentDetails);
                
                // Broadcast event for real-time updates (if using websockets)
                event(new PaymentStatusUpdated($channelRef, 'completed', $paymentDetails));
                
                // Log successful payment
                Log::info('NBC Payment completed successfully', [
                    'channelRef' => $channelRef,
                    'billRef' => $paymentDetails['billRef'] ?? null,
                    'gatewayRef' => $paymentDetails['gatewayRef'] ?? null,
                    'amount' => $paymentDetails['amount'] ?? null,
                    'billerReceipt' => $paymentDetails['billerReceipt'] ?? null
                ]);
                
                // Return success response to NBC
                return response()->json([
                    'statusCode' => '600',
                    'message' => 'Success',
                    'billRef' => $paymentDetails['billRef'] ?? '',
                    'channelRef' => $channelRef,
                    'gatewayRef' => $paymentDetails['gatewayRef'] ?? ''
                ], 200);
                
            } else {
                // Payment failed or pending
                $this->updateTransactionStatus($channelRef, 'failed', $paymentDetails);
                
                // Broadcast event for real-time updates
                event(new PaymentStatusUpdated($channelRef, 'failed', $paymentDetails));
                
                Log::warning('NBC Payment failed or pending', [
                    'channelRef' => $channelRef,
                    'statusCode' => $statusCode,
                    'message' => $validatedData['message']
                ]);
                
                // Return appropriate response
                return response()->json([
                    'statusCode' => '600',
                    'message' => 'Acknowledged',
                    'channelRef' => $channelRef
                ], 200);
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('NBC Callback validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all()
            ]);
            
            return response()->json([
                'statusCode' => '602',
                'message' => 'Validation failed: ' . json_encode($e->errors())
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('NBC Callback processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all()
            ]);
            
            return response()->json([
                'statusCode' => '699',
                'message' => 'Exception caught: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update transaction status in database
     * 
     * @param string $channelRef
     * @param string $status
     * @param array $details
     * @return void
     */
    protected function updateTransactionStatus($channelRef, $status, $details = [])
    {
        try {
            // Check if PaymentTransaction model exists
            if (class_exists('App\Models\PaymentTransaction')) {
                $transaction = PaymentTransaction::where('channel_ref', $channelRef)->first();
                
                if ($transaction) {
                    $transaction->status = $status;
                    $transaction->gateway_ref = $details['gatewayRef'] ?? $transaction->gateway_ref;
                    $transaction->biller_receipt = $details['billerReceipt'] ?? null;
                    $transaction->response_data = json_encode($details);
                    $transaction->completed_at = $status === 'completed' ? now() : null;
                    $transaction->save();
                    
                    Log::info('Transaction status updated', [
                        'channelRef' => $channelRef,
                        'status' => $status
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update transaction status', [
                'error' => $e->getMessage(),
                'channelRef' => $channelRef
            ]);
        }
    }
}