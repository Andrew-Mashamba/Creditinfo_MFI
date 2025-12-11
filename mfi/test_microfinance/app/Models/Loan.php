<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'loans';
    protected $guarded = [];
    
    /**
     * The attributes that should have default values.
     *
     * @var array
     */
    protected $attributes = [
        'pay_method' => 'internal_transfer',
    ];

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function loanAccount()
    {
        return $this->belongsTo(AccountsModel::class, 'loan_account_number', 'account_number');
    }

    public function loanProduct()
    {
        return $this->belongsTo(LoanSubProduct::class, 'loan_sub_product', 'product_id');
    }

    public function schedules()
    {
        return $this->hasMany(loans_schedules::class, 'loan_id', 'loan_id');
    }

    /**
     * Get the maximum days in arrears for this loan
     */
    public function getMaxDaysInArrearsAttribute()
    {
        return $this->schedules()->max('days_in_arrears') ?? 0;
    }

    /**
     * Get the total amount in arrears for this loan
     */
    public function getTotalAmountInArrearsAttribute()
    {
        return $this->schedules()->sum('amount_in_arrears') ?? 0;
    }
} 