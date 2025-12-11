<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanGuarantor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_id',
        'guarantor_member_id',
        'guarantor_type',
        'relationship',
        'total_guaranteed_amount',
        'available_amount',
        'status',
        'guarantee_start_date',
        'guarantee_end_date',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'total_guaranteed_amount' => 'decimal:2',
        'available_amount' => 'decimal:2',
        'guarantee_start_date' => 'datetime',
        'guarantee_end_date' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function loan()
    {
        return $this->belongsTo(LoansModel::class, 'loan_id');
    }

    public function guarantorMember()
    {
        return $this->belongsTo(ClientsModel::class, 'guarantor_member_id');
    }

    public function collaterals()
    {
        return $this->hasMany(LoanCollateral::class, 'loan_guarantor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeByGuarantorType($query, $type)
    {
        return $query->where('guarantor_type', $type);
    }

    public function scopeByMember($query, $memberId)
    {
        return $query->where('guarantor_member_id', $memberId);
    }

    // Methods
    public function getTotalCollateralValue()
    {
        return $this->collaterals()->active()->sum('collateral_amount');
    }

    public function getLockedAmount()
    {
        return $this->collaterals()->active()->sum('locked_amount');
    }

    public function getAvailableAmount()
    {
        return $this->collaterals()->active()->sum('available_amount');
    }

    public function canGuaranteeMore($amount)
    {
        return $this->available_amount >= $amount;
    }

    public function isActiveGuarantor()
    {
        return $this->status === 'active' && $this->is_active;
    }

    public function releaseGuarantee()
    {
        $this->update([
            'status' => 'released',
            'guarantee_end_date' => now()
        ]);

        // Release all collaterals
        $this->collaterals()->active()->update([
            'status' => 'released',
            'collateral_end_date' => now()
        ]);
    }
}
