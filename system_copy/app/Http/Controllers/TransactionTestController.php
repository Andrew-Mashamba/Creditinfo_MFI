<?php

namespace App\Http\Controllers;

use App\Services\TransactionProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransactionTestController extends Controller
{
    /**
     * Test cash transaction
     */
    public function testCashTransaction(Request $request)
    {
        try {
            $tps = new TransactionProcessingService(
                'cash',                    // serviceType
                'loan',                    // saccosService
                100000,                    // amount
                '1234567890',              // sourceAccount
                '0987654321',              // destinationAccount
                'M001',                    // memberId
                ['narration' => 'Test cash loan disbursement']
            );

            $result = $tps->process();

            return response()->json([
                'success' => true,
                'message' => 'Cash transaction processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Cash transaction test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cash transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test TIPS MNO transaction
     */
    public function testTipsMnoTransaction(Request $request)
    {
        try {
            $tps = new TransactionProcessingService(
                'tips_mno',                // serviceType
                'loan',                    // saccosService
                100000,                    // amount
                '1234567890',              // sourceAccount
                '0987654321',              // destinationAccount
                'M001',                    // memberId
                [
                    'phone_number' => '255712345678',
                    'wallet_provider' => 'MPESA',
                    'narration' => 'Test TIPS MNO loan disbursement',
                    'payer_name' => 'John Doe'
                ]
            );

            $result = $tps->process();

            return response()->json([
                'success' => true,
                'message' => 'TIPS MNO transaction processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('TIPS MNO transaction test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'TIPS MNO transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test TIPS Bank transaction
     */
    public function testTipsBankTransaction(Request $request)
    {
        try {
            $tps = new TransactionProcessingService(
                'tips_bank',               // serviceType
                'loan',                    // saccosService
                100000,                    // amount
                '1234567890',              // sourceAccount
                '0987654321',              // destinationAccount
                'M001',                    // memberId
                [
                    'bank_code' => '015',  // NBC bank code
                    'phone_number' => '255712345678',
                    'narration' => 'Test TIPS Bank loan disbursement',
                    'product_code' => 'FTLC'
                ]
            );

            $result = $tps->process();

            return response()->json([
                'success' => true,
                'message' => 'TIPS Bank transaction processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('TIPS Bank transaction test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'TIPS Bank transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test Internal Transfer transaction
     */
    public function testInternalTransferTransaction(Request $request)
    {
        try {
            $tps = new TransactionProcessingService(
                'internal_transfer',       // serviceType
                'loan',                    // saccosService
                100000,                    // amount
                '1234567890',              // sourceAccount
                '0987654321',              // destinationAccount
                'M001',                    // memberId
                [
                    'narration' => 'Test Internal Transfer loan disbursement',
                    'payer_name' => 'John Doe'
                ]
            );

            $result = $tps->process();

            return response()->json([
                'success' => true,
                'message' => 'Internal Transfer transaction processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Internal Transfer transaction test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal Transfer transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(Request $request, $referenceNumber)
    {
        try {
            $transaction = \App\Models\Transaction::where('reference', $referenceNumber)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reference_number' => $transaction->reference,
                    'correlation_id' => $transaction->correlation_id,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'external_reference' => $transaction->external_reference,
                    'retry_count' => $transaction->retry_count,
                    'created_at' => $transaction->created_at,
                    'completed_at' => $transaction->completed_at,
                    'failed_at' => $transaction->failed_at,
                    'error_message' => $transaction->error_message
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting transaction status', [
                'reference_number' => $referenceNumber,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error getting transaction status: ' . $e->getMessage()
            ], 500);
        }
    }
} 