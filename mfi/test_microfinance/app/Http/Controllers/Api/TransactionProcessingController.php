<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\TransactionProcessingService;
use App\Models\Transaction;
use Exception;

class TransactionProcessingController extends Controller
{
    /**
     * POST /api/transactions/process
     * Accepts a transaction request from external services and processes it.
     */
    public function process(Request $request)
    {
        $payload = $request->all();
        Log::info('API Transaction Processing Request', ['payload' => $payload]);

        $validator = Validator::make($payload, [
            'service_type' => 'required|string|in:cash,tips_mno,tips_bank,internal_transfer',
            'saccos_service' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'source_account' => 'required|string',
            'destination_account' => 'required|string',
            'member_id' => 'required|string',
            'meta' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            Log::warning('API Transaction Validation Failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tps = new TransactionProcessingService(
                $payload['service_type'],
                $payload['saccos_service'],
                $payload['amount'],
                $payload['source_account'],
                $payload['destination_account'],
                $payload['member_id'],
                $payload['meta'] ?? []
            );
            $result = $tps->process();
            Log::info('API Transaction Processed', ['result' => $result]);
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            Log::error('API Transaction Processing Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Transaction processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/transactions/{reference}/status
     * Get transaction status by reference number
     */
    public function getStatus($reference)
    {
        try {
            $transaction = Transaction::where('reference', $reference)
                ->orWhere('external_reference', $reference)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference,
                    'external_reference' => $transaction->external_reference,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'created_at' => $transaction->created_at->toIso8601String(),
                    'completed_at' => $transaction->completed_at?->toIso8601String(),
                    'failed_at' => $transaction->failed_at?->toIso8601String(),
                    'error_code' => $transaction->error_code,
                    'error_message' => $transaction->error_message,
                    'external_system' => $transaction->external_system,
                    'saccos_service' => $transaction->transaction_category
                ]
            ]);

        } catch (Exception $e) {
            Log::error('API Transaction Status Check Failed', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction status'
            ], 500);
        }
    }

    /**
     * GET /api/transactions
     * Get transaction history with optional filters
     */
    public function getHistory(Request $request)
    {
        try {
            $query = Transaction::query();

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('service_type')) {
                $query->where('transaction_subcategory', $request->service_type);
            }

            if ($request->has('saccos_service')) {
                $query->where('transaction_category', $request->saccos_service);
            }

            if ($request->has('member_id')) {
                $query->where('member_id', $request->member_id);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $transactions->items(),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total()
                ]
            ]);

        } catch (Exception $e) {
            Log::error('API Transaction History Retrieval Failed', [
                'error' => $e->getMessage(),
                'filters' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history'
            ], 500);
        }
    }
} 