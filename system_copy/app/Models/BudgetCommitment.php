<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCommitment extends Model
{
    use HasFactory;
    
    protected $table = 'budget_commitments';
    
    protected $fillable = [
        'budget_id',
        'commitment_type',
        'commitment_number',
        'vendor_name',
        'description',
        'committed_amount',
        'utilized_amount',
        'remaining_amount',
        'commitment_date',
        'expected_delivery_date',
        'expiry_date',
        'status',
        'created_by',
        'line_items'
    ];
    
    protected $casts = [
        'committed_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'commitment_date' => 'date',
        'expected_delivery_date' => 'date',
        'expiry_date' => 'date',
        'line_items' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the budget
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }
    
    /**
     * Get the creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Scope for active commitments
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['COMMITTED', 'PARTIALLY_UTILIZED']);
    }
    
    /**
     * Scope for expired commitments
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
            ->where('status', '!=', 'EXPIRED');
    }
    
    /**
     * Utilize commitment
     */
    public function utilize($amount)
    {
        if ($amount > $this->remaining_amount) {
            throw new \Exception('Amount exceeds remaining commitment');
        }
        
        $this->utilized_amount += $amount;
        $this->remaining_amount -= $amount;
        
        if ($this->remaining_amount == 0) {
            $this->status = 'FULLY_UTILIZED';
        } else {
            $this->status = 'PARTIALLY_UTILIZED';
        }
        
        $this->save();
        
        // Convert to expense in budget
        $this->budget->committed_amount -= $amount;
        $this->budget->spent_amount += $amount;
        $this->budget->calculateBudgetMetrics();
        
        return $this;
    }
    
    /**
     * Cancel commitment
     */
    public function cancel()
    {
        if ($this->status === 'FULLY_UTILIZED') {
            throw new \Exception('Cannot cancel fully utilized commitment');
        }
        
        $releasedAmount = $this->remaining_amount;
        $oldCommittedAmount = $this->budget->committed_amount;
        
        // Release committed amount back to budget
        $this->budget->committed_amount -= $this->remaining_amount;
        $this->budget->calculateBudgetMetrics();
        
        // Create version for commitment cancellation
        try {
            $versionNumber = BudgetVersion::where('budget_id', $this->budget_id)->count() + 1;
            
            BudgetVersion::create([
                'budget_id' => $this->budget_id,
                'version_number' => $versionNumber,
                'version_name' => "Version {$versionNumber} - Commitment Cancelled",
                'version_type' => 'CANCELLED',
                'allocated_amount' => $this->budget->allocated_amount,
                'spent_amount' => $this->budget->spent_amount,
                'committed_amount' => $this->budget->committed_amount,
                'effective_from' => now(),
                'created_by' => auth()->id() ?? 1,
                'revision_reason' => "Commitment {$this->commitment_number} cancelled - Released {$releasedAmount}",
                'change_summary' => json_encode([
                    'commitment_number' => $this->commitment_number,
                    'commitment_type' => $this->commitment_type,
                    'vendor' => $this->vendor_name,
                    'released_amount' => $releasedAmount,
                    'old_committed_amount' => $oldCommittedAmount,
                    'new_committed_amount' => $this->budget->committed_amount,
                    'cancelled_at' => now()->toDateTimeString(),
                    'cancelled_by' => auth()->user()->name ?? 'System'
                ]),
                'is_active' => true
            ]);
            
            // Deactivate previous versions
            BudgetVersion::where('budget_id', $this->budget_id)
                ->where('version_number', '<', $versionNumber)
                ->update(['is_active' => false]);
            
            \Log::info('Version created for commitment cancellation', [
                'budget_id' => $this->budget_id,
                'commitment_id' => $this->id,
                'commitment_number' => $this->commitment_number,
                'released_amount' => $releasedAmount,
                'version_number' => $versionNumber
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create version for commitment cancellation', [
                'budget_id' => $this->budget_id,
                'commitment_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
        
        $this->status = 'CANCELLED';
        $this->save();
        
        return $this;
    }
    
    /**
     * Check if commitment is expired
     */
    public function checkExpiry()
    {
        if ($this->expiry_date && $this->expiry_date < now() && $this->status !== 'EXPIRED') {
            $this->expire();
        }
    }
    
    /**
     * Expire commitment
     */
    public function expire()
    {
        if ($this->status !== 'FULLY_UTILIZED') {
            $releasedAmount = $this->remaining_amount;
            $oldCommittedAmount = $this->budget->committed_amount;
            
            // Release remaining amount
            $this->budget->committed_amount -= $this->remaining_amount;
            $this->budget->calculateBudgetMetrics();
            
            // Create version for commitment expiry
            try {
                $versionNumber = BudgetVersion::where('budget_id', $this->budget_id)->count() + 1;
                
                BudgetVersion::create([
                    'budget_id' => $this->budget_id,
                    'version_number' => $versionNumber,
                    'version_name' => "Version {$versionNumber} - Commitment Expired",
                    'version_type' => 'CANCELLED',
                    'allocated_amount' => $this->budget->allocated_amount,
                    'spent_amount' => $this->budget->spent_amount,
                    'committed_amount' => $this->budget->committed_amount,
                    'effective_from' => now(),
                    'created_by' => 1, // System generated
                    'revision_reason' => "Commitment {$this->commitment_number} expired - Released {$releasedAmount}",
                    'change_summary' => json_encode([
                        'commitment_number' => $this->commitment_number,
                        'commitment_type' => $this->commitment_type,
                        'vendor' => $this->vendor_name,
                        'released_amount' => $releasedAmount,
                        'old_committed_amount' => $oldCommittedAmount,
                        'new_committed_amount' => $this->budget->committed_amount,
                        'expired_at' => now()->toDateTimeString(),
                        'expiry_date' => $this->expiry_date->toDateString(),
                        'reason' => 'Automatic expiry'
                    ]),
                    'is_active' => true
                ]);
                
                // Deactivate previous versions
                BudgetVersion::where('budget_id', $this->budget_id)
                    ->where('version_number', '<', $versionNumber)
                    ->update(['is_active' => false]);
                
                \Log::info('Version created for commitment expiry', [
                    'budget_id' => $this->budget_id,
                    'commitment_id' => $this->id,
                    'commitment_number' => $this->commitment_number,
                    'released_amount' => $releasedAmount
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create version for commitment expiry', [
                    'budget_id' => $this->budget_id,
                    'commitment_id' => $this->id,
                    'error' => $e->getMessage()
                ]);
            }
            
            $this->status = 'EXPIRED';
            $this->save();
        }
    }
}