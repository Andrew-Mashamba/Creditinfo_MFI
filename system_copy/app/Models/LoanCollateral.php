<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanCollateral extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_guarantor_id',
        'collateral_type',
        'account_id',
        'collateral_amount',
        'account_balance',
        'locked_amount',
        'available_amount',
        'physical_collateral_id',
        'physical_collateral_description',
        'physical_collateral_location',
        'physical_collateral_owner_name',
        'physical_collateral_owner_nida',
        'physical_collateral_owner_contact',
        'physical_collateral_owner_address',
        'physical_collateral_value',
        'physical_collateral_valuation_date',
        'physical_collateral_valuation_method',
        'physical_collateral_valuer_name',
        'insurance_policy_number',
        'insurance_company_name',
        'insurance_coverage_details',
        'insurance_expiration_date',
        'status',
        'collateral_start_date',
        'collateral_end_date',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'collateral_amount' => 'decimal:2',
        'account_balance' => 'decimal:2',
        'locked_amount' => 'decimal:2',
        'available_amount' => 'decimal:2',
        'physical_collateral_value' => 'decimal:2',
        'physical_collateral_valuation_date' => 'date',
        'insurance_expiration_date' => 'date',
        'collateral_start_date' => 'datetime',
        'collateral_end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function loanGuarantor()
    {
        return $this->belongsTo(LoanGuarantor::class, 'loan_guarantor_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function loan()
    {
        return $this->hasOneThrough(LoansModel::class, LoanGuarantor::class, 'id', 'id', 'loan_guarantor_id', 'loan_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('collateral_type', $type);
    }

    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeFinancial($query)
    {
        return $query->whereIn('collateral_type', ['savings', 'deposits', 'shares']);
    }

    public function scopePhysical($query)
    {
        return $query->where('collateral_type', 'physical');
    }

    // Methods
    public function isFinancialCollateral()
    {
        return in_array($this->collateral_type, ['savings', 'deposits', 'shares']);
    }

    public function isPhysicalCollateral()
    {
        return $this->collateral_type === 'physical';
    }

    public function getCurrentAccountBalance()
    {
        if ($this->isFinancialCollateral() && $this->account) {
            return $this->account->balance ?? 0;
        }
        return $this->account_balance ?? 0;
    }

    public function canLockAmount($amount)
    {
        if ($this->isFinancialCollateral()) {
            $currentBalance = $this->getCurrentAccountBalance();
            $alreadyLocked = $this->locked_amount;
            return ($currentBalance - $alreadyLocked) >= $amount;
        }
        return true; // Physical collateral can always be locked
    }

    public function lockAmount($amount)
    {
        if (!$this->canLockAmount($amount)) {
            throw new \Exception('Insufficient balance to lock amount');
        }

        $this->update([
            'locked_amount' => $this->locked_amount + $amount,
            'available_amount' => $this->available_amount - $amount
        ]);
    }

    public function releaseAmount($amount)
    {
        if ($this->locked_amount < $amount) {
            throw new \Exception('Cannot release more than locked amount');
        }

        $this->update([
            'locked_amount' => $this->locked_amount - $amount,
            'available_amount' => $this->available_amount + $amount
        ]);
    }

    public function releaseCollateral()
    {
        $this->update([
            'status' => 'released',
            'collateral_end_date' => now(),
            'locked_amount' => 0,
            'available_amount' => $this->collateral_amount
        ]);
    }

    public function getCollateralValue()
    {
        if ($this->isFinancialCollateral()) {
            return $this->collateral_amount;
        }
        return $this->physical_collateral_value ?? 0;
    }

    public function isAccountLocked()
    {
        return $this->isFinancialCollateral() && $this->locked_amount > 0;
    }
}
