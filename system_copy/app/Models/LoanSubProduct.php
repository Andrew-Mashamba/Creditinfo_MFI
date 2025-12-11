<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanSubProduct extends Model
{
    use HasFactory;

    protected $table = 'loan_sub_products';
    protected $guarded = [];

    /**
     * Get the loans for this product
     */
    public function loans()
    {
        return $this->hasMany(Loan::class, 'loan_sub_product', 'product_id');
    }
}
