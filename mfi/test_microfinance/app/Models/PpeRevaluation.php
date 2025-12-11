<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Log;

class PpeRevaluation extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ppe_id', 'revaluation_date', 'old_value', 'new_value', 'revaluation_amount',
        'revaluation_type', 'performed_by', 'approved_by', 'reason', 'supporting_documents',
        'valuation_method', 'status', 'journal_entry_reference'
    ];
    
    protected $casts = [
        'revaluation_date' => 'date',
        'old_value' => 'decimal:2',
        'new_value' => 'decimal:2',
        'revaluation_amount' => 'decimal:2'
    ];
    
    public function ppe()
    {
        return $this->belongsTo(PPE::class, 'ppe_id');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy
        ]);
    }
    
    public function postToGeneralLedger()
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Revaluation must be approved before posting');
        }
        
        try {
            $transactionService = new TransactionPostingService();
            $institution = \DB::table('institutions')->where('id', 1)->first();
            
            // Get proper PPE account (level 3)
            $ppeAccount = $this->ppe->account_number;
            $account = \DB::table('accounts')->where('account_number', $ppeAccount)->first();
            if ($account && $account->account_level < 3) {
                // Find appropriate child account
                $childAccount = \DB::table('accounts')
                    ->where('parent_account_number', $ppeAccount)
                    ->where('account_level', '3')
                    ->orderBy('account_number')
                    ->first();
                
                if ($childAccount) {
                    $ppeAccount = $childAccount->account_number;
                } else {
                    // Default to Land and Buildings
                    $ppeAccount = '0101100016001610';
                }
            }
            
            // Determine accounts based on revaluation type
            if ($this->revaluation_type === 'appreciation') {
                // Use proper Property Revaluation Reserve account (level 3)
                $revaluationSurplusAccount = $institution->revaluation_surplus_account ?? 
                                            '0101300034003410'; // Property Revaluation Reserve
                
                // Debit: PPE Asset Account, Credit: Revaluation Surplus
                $transactionData = [
                    'first_account' => $ppeAccount,
                    'second_account' => $revaluationSurplusAccount,
                    'amount' => abs($this->revaluation_amount),
                    'narration' => "PPE Revaluation (Appreciation): {$this->ppe->name}",
                    'action' => 'ppe_revaluation'
                ];
            } else {
                // Find proper impairment loss account (level 3)
                $impairmentLossAccount = $institution->impairment_loss_account ?? 
                                       $institution->other_expenses_account ?? 
                                       '0101150020';
                
                // Ensure we have a level 3 account
                $lossAccount = \DB::table('accounts')->where('account_number', $impairmentLossAccount)->first();
                if ($lossAccount && $lossAccount->account_level < 3) {
                    $childLoss = \DB::table('accounts')
                        ->where('parent_account_number', $impairmentLossAccount)
                        ->where('account_level', '3')
                        ->first();
                    if ($childLoss) {
                        $impairmentLossAccount = $childLoss->account_number;
                    }
                }
                
                // Impairment: Debit: Impairment Loss, Credit: PPE Asset Account
                $transactionData = [
                    'first_account' => $impairmentLossAccount,
                    'second_account' => $ppeAccount,
                    'amount' => abs($this->revaluation_amount),
                    'narration' => "PPE Revaluation (Impairment): {$this->ppe->name}",
                    'action' => 'ppe_revaluation'
                ];
            }
            
            $result = $transactionService->postTransaction($transactionData);
            
            if ($result['status'] === 'success') {
                $this->update([
                    'status' => 'posted',
                    'journal_entry_reference' => $result['reference'] ?? null
                ]);
                
                // Update PPE closing value
                $this->ppe->update([
                    'closing_value' => $this->new_value,
                    'market_value' => $this->new_value,
                    'last_valuation_date' => $this->revaluation_date,
                    'valuation_by' => $this->performed_by
                ]);
                
                Log::info('PPE revaluation posted successfully', [
                    'revaluation_id' => $this->id,
                    'ppe_id' => $this->ppe_id,
                    'amount' => $this->revaluation_amount
                ]);
                
                return true;
            }
            
            throw new \Exception('Failed to post revaluation transaction');
            
        } catch (\Exception $e) {
            Log::error('Error posting PPE revaluation', [
                'revaluation_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}