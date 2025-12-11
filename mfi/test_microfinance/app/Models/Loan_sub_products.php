<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan_sub_products extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the charges for this loan product
     */
    public function charges()
    {
        return $this->hasMany(LoanProductCharge::class, 'loan_product_id', 'sub_product_id');
    }
}
