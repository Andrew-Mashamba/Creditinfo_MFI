<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetTransfer extends Model
{
    use HasFactory;
    
    protected $table = 'budget_transfers';
    
    protected $fillable = [
        'transfer_number',
        'from_budget_id',
        'to_budget_id',
        'amount',
        'transfer_type',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'executed_at',
        'rejection_reason'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'executed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transfer) {
            if (!$transfer->transfer_number) {
                $transfer->transfer_number = 'TRF-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        });
    }
    
    /**
     * Get the source budget
     */
    public function fromBudget()
    {
        return $this->belongsTo(BudgetManagement::class, 'from_budget_id');
    }
    
    /**
     * Get the destination budget
     */
    public function toBudget()
    {
        return $this->belongsTo(BudgetManagement::class, 'to_budget_id');
    }
    
    /**
     * Get the requester
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    /**
     * Get the approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    /**
     * Scope for pending transfers
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }
    
    /**
     * Approve the transfer
     */
    public function approve($userId = null)
    {
        $this->status = 'APPROVED';
        $this->approved_by = $userId ?? auth()->id();
        $this->approved_at = now();
        $this->save();
        
        return $this;
    }
    
    /**
     * Reject the transfer
     */
    public function reject($reason, $userId = null)
    {
        $this->status = 'REJECTED';
        $this->rejection_reason = $reason;
        $this->approved_by = $userId ?? auth()->id();
        $this->approved_at = now();
        $this->save();
        
        return $this;
    }
    
    /**
     * Execute the transfer
     */
    public function execute()
    {
        if ($this->status !== 'APPROVED') {
            throw new \Exception('Transfer must be approved before execution');
        }
        
        \DB::transaction(function () {
            // Store old values for version tracking
            $fromBudget = $this->fromBudget;
            $fromOldAllocated = $fromBudget->allocated_amount;
            $fromOldAvailable = $fromBudget->available_amount;
            
            $toBudget = $this->toBudget;
            $toOldAllocated = $toBudget->allocated_amount;
            $toOldAvailable = $toBudget->available_amount;
            
            // Deduct from source budget
            $fromBudget->allocated_amount -= $this->amount;
            $fromBudget->available_amount -= $this->amount;
            $fromBudget->calculateBudgetMetrics();
            
            // Add to destination budget
            $toBudget->allocated_amount += $this->amount;
            $toBudget->available_amount += $this->amount;
            $toBudget->calculateBudgetMetrics();
            
            // Create version for source budget
            try {
                $fromVersionNumber = BudgetVersion::where('budget_id', $fromBudget->id)->count() + 1;
                BudgetVersion::create([
                    'budget_id' => $fromBudget->id,
                    'version_number' => $fromVersionNumber,
                    'version_name' => "Version {$fromVersionNumber} - Transfer Out",
                    'version_type' => 'TRANSFER',
                    'allocated_amount' => $fromBudget->allocated_amount,
                    'spent_amount' => $fromBudget->spent_amount,
                    'committed_amount' => $fromBudget->committed_amount,
                    'effective_from' => now(),
                    'created_by' => auth()->id() ?? $this->approved_by,
                    'revision_reason' => "Budget transfer to {$toBudget->budget_name}",
                    'change_summary' => json_encode([
                        'transfer_number' => $this->transfer_number,
                        'transfer_amount' => $this->amount,
                        'to_budget' => $toBudget->budget_name,
                        'old_allocated' => $fromOldAllocated,
                        'new_allocated' => $fromBudget->allocated_amount,
                        'transfer_reason' => $this->reason
                    ]),
                    'is_active' => true
                ]);
                
                // Deactivate previous versions
                BudgetVersion::where('budget_id', $fromBudget->id)
                    ->where('version_number', '<', $fromVersionNumber)
                    ->update(['is_active' => false]);
            } catch (\Exception $e) {
                \Log::error('Failed to create version for source budget transfer', [
                    'budget_id' => $fromBudget->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Create version for destination budget
            try {
                $toVersionNumber = BudgetVersion::where('budget_id', $toBudget->id)->count() + 1;
                BudgetVersion::create([
                    'budget_id' => $toBudget->id,
                    'version_number' => $toVersionNumber,
                    'version_name' => "Version {$toVersionNumber} - Transfer In",
                    'version_type' => 'TRANSFER',
                    'allocated_amount' => $toBudget->allocated_amount,
                    'spent_amount' => $toBudget->spent_amount,
                    'committed_amount' => $toBudget->committed_amount,
                    'effective_from' => now(),
                    'created_by' => auth()->id() ?? $this->approved_by,
                    'revision_reason' => "Budget transfer from {$fromBudget->budget_name}",
                    'change_summary' => json_encode([
                        'transfer_number' => $this->transfer_number,
                        'transfer_amount' => $this->amount,
                        'from_budget' => $fromBudget->budget_name,
                        'old_allocated' => $toOldAllocated,
                        'new_allocated' => $toBudget->allocated_amount,
                        'transfer_reason' => $this->reason
                    ]),
                    'is_active' => true
                ]);
                
                // Deactivate previous versions
                BudgetVersion::where('budget_id', $toBudget->id)
                    ->where('version_number', '<', $toVersionNumber)
                    ->update(['is_active' => false]);
            } catch (\Exception $e) {
                \Log::error('Failed to create version for destination budget transfer', [
                    'budget_id' => $toBudget->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Mark transfer as executed
            $this->status = 'EXECUTED';
            $this->executed_at = now();
            $this->save();
        });
        
        return $this;
    }
}