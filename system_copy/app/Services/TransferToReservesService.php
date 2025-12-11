<?php

namespace App\Services;

use App\Models\TransferToReserves;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class TransferToReservesService
{
    /**
     * Create a new transfer to reserves
     */
    public function createTransfer(array $data): TransferToReserves
    {
        DB::beginTransaction();
        
        try {
            // Generate transfer reference
            $data['transfer_reference'] = TransferToReserves::generateTransferReference();
            
            // Set initiator information
            $data['initiated_by'] = Auth::id();
            $data['initiated_by_name'] = Auth::user()->name ?? 'System';
            $data['initiated_at'] = now();
            
            // Set default status
            $data['status'] = $data['status'] ?? TransferToReserves::STATUS_DRAFT;
            
            // Get account names if not provided
            if (!isset($data['source_account_name'])) {
                $sourceAccount = Account::where('account_number', $data['source_account_number'])->first();
                $data['source_account_name'] = $sourceAccount ? $sourceAccount->account_name : 'Unknown Account';
            }
            
            if (!isset($data['destination_reserve_account_name'])) {
                $destAccount = Account::where('account_number', $data['destination_reserve_account_number'])->first();
                $data['destination_reserve_account_name'] = $destAccount ? $destAccount->account_name : 'Unknown Reserve';
            }
            
            // Calculate amount if percentage-based
            if ($data['calculation_method'] === TransferToReserves::METHOD_PERCENTAGE) {
                if (isset($data['base_amount']) && isset($data['percentage_of_profit'])) {
                    $data['amount'] = ($data['base_amount'] * $data['percentage_of_profit']) / 100;
                }
            }
            
            // Check regulatory compliance
            if ($data['is_statutory_requirement'] ?? false) {
                $data['meets_regulatory_requirement'] = 
                    !isset($data['minimum_required_amount']) || 
                    $data['amount'] >= $data['minimum_required_amount'];
            }
            
            // Set audit trail
            $data['session_id'] = session()->getId();
            $data['ip_address'] = request()->ip();
            $data['user_agent'] = request()->userAgent();
            
            $transfer = TransferToReserves::create($data);
            
            DB::commit();
            
            return $transfer;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Submit transfer for approval
     */
    public function submitForApproval(TransferToReserves $transfer): bool
    {
        if ($transfer->status !== TransferToReserves::STATUS_DRAFT) {
            throw new Exception('Only draft transfers can be submitted for approval');
        }
        
        $transfer->update([
            'status' => TransferToReserves::STATUS_PENDING_APPROVAL,
            'initiated_at' => now()
        ]);
        
        return true;
    }
    
    /**
     * Approve transfer
     */
    public function approveTransfer(TransferToReserves $transfer, string $notes = null): bool
    {
        if (!$transfer->canBeApproved()) {
            throw new Exception('Transfer cannot be approved in current status');
        }
        
        $transfer->update([
            'status' => TransferToReserves::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_by_name' => Auth::user()->name ?? 'System',
            'approved_at' => now(),
            'approval_notes' => $notes
        ]);
        
        return true;
    }
    
    /**
     * Reject transfer
     */
    public function rejectTransfer(TransferToReserves $transfer, string $reason): bool
    {
        if ($transfer->status !== TransferToReserves::STATUS_PENDING_APPROVAL) {
            throw new Exception('Only pending transfers can be rejected');
        }
        
        $transfer->update([
            'status' => TransferToReserves::STATUS_REJECTED,
            'rejected_by' => Auth::id(),
            'rejected_by_name' => Auth::user()->name ?? 'System',
            'rejected_at' => now(),
            'rejection_reason' => $reason
        ]);
        
        return true;
    }
    
    /**
     * Post transfer to general ledger using TransactionPostingService
     */
    public function postToGeneralLedger(TransferToReserves $transfer): bool
    {
        if (!$transfer->canBePosted()) {
            throw new Exception('Transfer cannot be posted in current status');
        }
        
        DB::beginTransaction();
        
        try {
            // Use the TransactionPostingService for proper GL posting
            $postingService = new TransactionPostingService();
            
            // Prepare transaction data for posting service
            $transactionData = [
                'first_account' => $transfer->source_account_number,
                'second_account' => $transfer->destination_reserve_account_number,
                'amount' => $transfer->amount,
                'narration' => $transfer->narration,
                'action' => 'transfer_to_reserve'
            ];
            
            // Post the transaction using the proper service
            $result = $postingService->postTransaction($transactionData);
            
            if ($result['status'] !== 'success') {
                throw new Exception('Failed to post transaction through TransactionPostingService');
            }
            
            // Update transfer record with posting details
            $transfer->update([
                'status' => TransferToReserves::STATUS_POSTED,
                'posted_by' => Auth::id(),
                'posted_by_name' => Auth::user()->name ?? 'System',
                'posted_at' => now(),
                'gl_entry_reference' => $result['reference_number'],
                'posted_to_gl' => true,
                'gl_posting_date' => now()
            ]);
            
            DB::commit();
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Reverse a posted transfer
     */
    public function reverseTransfer(TransferToReserves $transfer, string $reason): bool
    {
        if (!$transfer->canBeReversed()) {
            throw new Exception('Transfer cannot be reversed in current status');
        }
        
        DB::beginTransaction();
        
        try {
            // Use the TransactionPostingService for proper GL reversal
            $postingService = new TransactionPostingService();
            
            // Prepare reversal transaction data (swap accounts to reverse the transfer)
            $reversalData = [
                'first_account' => $transfer->destination_reserve_account_number,  // Now debiting the reserve
                'second_account' => $transfer->source_account_number,  // Now crediting the source
                'amount' => $transfer->amount,
                'narration' => 'Reversal: ' . $transfer->narration . ' - Reason: ' . $reason,
                'action' => 'transfer_reversal'
            ];
            
            // Post the reversal transaction using the proper service
            $result = $postingService->postTransaction($reversalData);
            
            if ($result['status'] !== 'success') {
                throw new Exception('Failed to post reversal through TransactionPostingService');
            }
            
            // Update transfer record with reversal details
            $transfer->update([
                'status' => TransferToReserves::STATUS_REVERSED,
                'reversed_by' => Auth::id(),
                'reversed_by_name' => Auth::user()->name ?? 'System',
                'reversed_at' => now(),
                'reversal_reason' => $reason
            ]);
            
            DB::commit();
            
            return true;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Get account balance from accounts table
     */
    private function getAccountBalance(string $accountNumber): float
    {
        $account = DB::table('accounts')
            ->where('account_number', $accountNumber)
            ->first();
        
        return $account ? floatval($account->balance) : 0;
    }
    
    /**
     * Calculate statutory reserve requirement
     */
    public function calculateStatutoryReserve(float $netProfit, float $percentage = 10): float
    {
        return ($netProfit * $percentage) / 100;
    }
    
    /**
     * Get transfers for a specific year
     */
    public function getTransfersForYear(int $year)
    {
        return TransferToReserves::forYear($year)
            ->orderBy('transfer_date', 'desc')
            ->get();
    }
    
    /**
     * Get total transfers by type for a year
     */
    public function getTotalTransfersByType(int $year): array
    {
        $transfers = TransferToReserves::forYear($year)
            ->where('status', TransferToReserves::STATUS_POSTED)
            ->groupBy('transfer_type')
            ->selectRaw('transfer_type, SUM(amount) as total_amount')
            ->get();
        
        $result = [];
        foreach ($transfers as $transfer) {
            $result[$transfer->transfer_type] = $transfer->total_amount;
        }
        
        return $result;
    }
    
    /**
     * Get total transfers to a specific reserve account
     */
    public function getTotalTransfersToAccount(string $accountNumber, int $year): float
    {
        return TransferToReserves::forYear($year)
            ->where('destination_reserve_account_number', $accountNumber)
            ->where('status', TransferToReserves::STATUS_POSTED)
            ->sum('amount');
    }
    
    /**
     * Check if statutory requirements are met
     */
    public function checkStatutoryCompliance(int $year): array
    {
        $statutoryTransfers = TransferToReserves::forYear($year)
            ->statutory()
            ->where('status', TransferToReserves::STATUS_POSTED)
            ->get();
        
        $compliance = [
            'total_required' => 0,
            'total_transferred' => 0,
            'compliant' => true,
            'details' => []
        ];
        
        foreach ($statutoryTransfers as $transfer) {
            $compliance['total_required'] += $transfer->minimum_required_amount ?? 0;
            $compliance['total_transferred'] += $transfer->amount;
            
            $compliance['details'][] = [
                'type' => $transfer->transfer_type,
                'required' => $transfer->minimum_required_amount ?? 0,
                'transferred' => $transfer->amount,
                'compliant' => $transfer->meets_regulatory_requirement
            ];
            
            if (!$transfer->meets_regulatory_requirement) {
                $compliance['compliant'] = false;
            }
        }
        
        return $compliance;
    }
}