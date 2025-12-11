<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferToReserves extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transfer_to_reserves';

    protected $fillable = [
        // Transfer identification
        'transfer_reference',
        'transfer_type',
        
        // Source and destination accounts
        'source_account_number',
        'source_account_name',
        'destination_reserve_account_number',
        'destination_reserve_account_name',
        
        // Amount and period
        'amount',
        'currency',
        'financial_year',
        'financial_month',
        'financial_quarter',
        
        // Transfer details
        'transfer_date',
        'narration',
        'reason_for_transfer',
        
        // Percentage or fixed amount
        'calculation_method',
        'percentage_of_profit',
        'base_amount',
        
        // Approval workflow
        'status',
        'initiated_by',
        'initiated_by_name',
        'initiated_at',
        'approved_by',
        'approved_by_name',
        'approved_at',
        'approval_notes',
        'posted_by',
        'posted_by_name',
        'posted_at',
        'reversed_by',
        'reversed_by_name',
        'reversed_at',
        'reversal_reason',
        'rejected_by',
        'rejected_by_name',
        'rejected_at',
        'rejection_reason',
        
        // GL posting references
        'gl_entry_reference',
        'posted_to_gl',
        'gl_posting_date',
        
        // Compliance and regulatory
        'is_statutory_requirement',
        'regulatory_reference',
        'minimum_required_amount',
        'meets_regulatory_requirement',
        
        // Audit trail
        'metadata',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage_of_profit' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'minimum_required_amount' => 'decimal:2',
        'posted_to_gl' => 'boolean',
        'is_statutory_requirement' => 'boolean',
        'meets_regulatory_requirement' => 'boolean',
        'metadata' => 'array',
        'transfer_date' => 'date',
        'initiated_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'rejected_at' => 'datetime',
        'gl_posting_date' => 'datetime',
    ];

    // Constants for transfer types
    const TYPE_STATUTORY_RESERVE = 'STATUTORY_RESERVE';
    const TYPE_GENERAL_RESERVE = 'GENERAL_RESERVE';
    const TYPE_SPECIAL_RESERVE = 'SPECIAL_RESERVE';
    const TYPE_REGULATORY_CAPITAL_RESERVE = 'REGULATORY_CAPITAL_RESERVE';
    const TYPE_CONTINGENCY_RESERVE = 'CONTINGENCY_RESERVE';
    const TYPE_CAPITAL_REDEMPTION_RESERVE = 'CAPITAL_REDEMPTION_RESERVE';
    const TYPE_REVALUATION_RESERVE = 'REVALUATION_RESERVE';
    const TYPE_OTHER_RESERVE = 'OTHER_RESERVE';

    // Constants for status
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    const STATUS_APPROVED = 'APPROVED';
    const STATUS_POSTED = 'POSTED';
    const STATUS_REVERSED = 'REVERSED';
    const STATUS_REJECTED = 'REJECTED';

    // Constants for calculation methods
    const METHOD_PERCENTAGE = 'PERCENTAGE';
    const METHOD_FIXED_AMOUNT = 'FIXED_AMOUNT';

    /**
     * Generate unique transfer reference
     */
    public static function generateTransferReference(): string
    {
        $prefix = 'TRF';
        $year = date('Y');
        $month = date('m');
        $random = strtoupper(substr(uniqid(), -6));
        
        return "{$prefix}-{$year}{$month}-{$random}";
    }

    /**
     * Scope for pending approvals
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope for approved transfers
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for posted transfers
     */
    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    /**
     * Scope for transfers in a specific year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    /**
     * Scope for statutory transfers
     */
    public function scopeStatutory($query)
    {
        return $query->where('is_statutory_requirement', true);
    }

    /**
     * Check if transfer can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if transfer can be posted
     */
    public function canBePosted(): bool
    {
        return $this->status === self::STATUS_APPROVED && !$this->posted_to_gl;
    }

    /**
     * Check if transfer can be reversed
     */
    public function canBeReversed(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    /**
     * Get the calculated amount based on method
     */
    public function getCalculatedAmount(): float
    {
        if ($this->calculation_method === self::METHOD_PERCENTAGE && $this->base_amount && $this->percentage_of_profit) {
            return ($this->base_amount * $this->percentage_of_profit) / 100;
        }
        
        return $this->amount;
    }

    /**
     * Check if meets regulatory requirements
     */
    public function checkRegulatoryCompliance(): bool
    {
        if (!$this->is_statutory_requirement) {
            return true;
        }
        
        if ($this->minimum_required_amount) {
            return $this->amount >= $this->minimum_required_amount;
        }
        
        return true;
    }
}