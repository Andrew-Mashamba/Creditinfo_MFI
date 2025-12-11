<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettlementService
{
    protected $loanId;
    protected $clientNumber;

    public function __construct($loanId = null, $clientNumber = null)
    {
        $this->loanId = $loanId ?? session('currentloanID');
        $this->clientNumber = $clientNumber;
    }

    /**
     * Get all settlement data for the current loan
     */
    public function getSettlementData(): array
    {
        try {
            $settlements = DB::table('settled_loans')
                ->where('loan_id', $this->loanId)
                ->orderBy('loan_array_id')
                ->get();

            $totalAmount = 0;
            $settlementDetails = [];

            foreach ($settlements as $settlement) {
                $amount = (float)($settlement->amount ?? 0);
                $totalAmount += $amount;

                $settlementDetails[] = [
                    'id' => $settlement->loan_array_id,
                    'institution' => $settlement->institution,
                    'account' => $settlement->account,
                    'amount' => $amount,
                    'is_selected' => (bool)($settlement->is_selected ?? false),
                    'created_at' => $settlement->created_at,
                    'updated_at' => $settlement->updated_at
                ];
            }

            return [
                'total_amount' => $totalAmount,
                'settlements' => $settlementDetails,
                'count' => count($settlementDetails),
                'has_settlements' => $totalAmount > 0
            ];
        } catch (\Exception $e) {
            Log::error('Error getting settlement data: ' . $e->getMessage());
            return [
                'total_amount' => 0,
                'settlements' => [],
                'count' => 0,
                'has_settlements' => false
            ];
        }
    }

    /**
     * Save or update a settlement
     */
    public function saveSettlement(int $settlementId, array $data): bool
    {
        try {
            $result = DB::table('settled_loans')->updateOrInsert(
                [
                    'loan_id' => $this->loanId,
                    'loan_array_id' => $settlementId
                ],
                [
                    'amount' => (float)($data['amount'] ?? 0),
                    'institution' => $data['institution'] ?? null,
                    'account' => $data['account'] ?? null,
                    'is_selected' => $data['is_selected'] ?? false,
                    'updated_at' => now()
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Error saving settlement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a settlement
     */
    public function deleteSettlement(int $settlementId): bool
    {
        try {
            DB::table('settled_loans')
                ->where('loan_id', $this->loanId)
                ->where('loan_array_id', $settlementId)
                ->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting settlement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get settlement summary for display
     */
    public function getSettlementSummary(): array
    {
        $data = $this->getSettlementData();
        
        return [
            'total_amount' => $data['total_amount'],
            'formatted_total' => number_format($data['total_amount'], 2),
            'settlement_count' => $data['count'],
            'has_settlements' => $data['has_settlements'],
            'status' => $data['has_settlements'] ? 'Active' : 'No Settlements',
            'status_color' => $data['has_settlements'] ? 'green' : 'gray'
        ];
    }

    /**
     * Validate settlement data
     */
    public function validateSettlement(array $data): array
    {
        $errors = [];

        if (empty($data['institution'])) {
            $errors[] = 'Institution name is required';
        }

        if (empty($data['account'])) {
            $errors[] = 'Account number is required';
        }

        if (!isset($data['amount']) || (float)($data['amount']) <= 0) {
            $errors[] = 'Amount must be greater than 0';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get available settlement slots
     */
    public function getAvailableSlots(): array
    {
        $existingSettlements = $this->getSettlementData();
        $usedSlots = collect($existingSettlements['settlements'])->pluck('id')->toArray();
        
        $availableSlots = [];
        for ($i = 1; $i <= 5; $i++) { // Allow up to 5 settlements
            if (!in_array($i, $usedSlots)) {
                $availableSlots[] = $i;
            }
        }

        return $availableSlots;
    }

    /**
     * Get settlement statistics
     */
    public function getSettlementStatistics(): array
    {
        $data = $this->getSettlementData();
        
        return [
            'total_settlements' => $data['count'],
            'total_amount' => $data['total_amount'],
            'average_amount' => $data['count'] > 0 ? $data['total_amount'] / $data['count'] : 0,
            'largest_settlement' => $data['count'] > 0 ? max(array_column($data['settlements'], 'amount')) : 0,
            'smallest_settlement' => $data['count'] > 0 ? min(array_column($data['settlements'], 'amount')) : 0,
            'last_updated' => $data['count'] > 0 ? max(array_column($data['settlements'], 'updated_at')) : null
        ];
    }
} 