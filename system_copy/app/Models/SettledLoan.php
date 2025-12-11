<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettledLoan extends Model
{
    use HasFactory;

    protected $table = 'settled_loans';

    protected $fillable = [
        'loan_id',
        'loan_array_id',
        'amount',
        'institution',
        'account',
        'is_selected',
        'settlement_date',
        'settlement_method',
        'reference_number',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_selected' => 'boolean',
        'settlement_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoansModel::class, 'loan_id');
    }

    public function scopeSelected($query)
    {
        return $query->where('is_selected', true);
    }

    public function scopeByInstitution($query, $institution)
    {
        return $query->where('institution', $institution);
    }

    public function scopeByAmount($query, $minAmount = null, $maxAmount = null)
    {
        if ($minAmount) {
            $query->where('amount', '>=', $minAmount);
        }
        if ($maxAmount) {
            $query->where('amount', '<=', $maxAmount);
        }
        return $query;
    }
} 