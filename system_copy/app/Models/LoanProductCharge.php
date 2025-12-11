<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProductCharge extends Model
{
    protected $table = 'loan_product_charges';

    protected $fillable = [
        'loan_product_id',
        'type',
        'name',
        'value_type',
        'value',
        'account_id',
        'min_cap',
        'max_cap'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_cap' => 'decimal:2',
        'max_cap' => 'decimal:2'
    ];

    /**
     * Get the loan product that owns this charge
     */
    public function loanProduct()
    {
        return $this->belongsTo(Loan_sub_products::class, 'loan_product_id', 'sub_product_id');
    }
} 