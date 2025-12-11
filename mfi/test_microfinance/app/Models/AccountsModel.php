<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountsModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'accounts';

    /**
     * Accessor for account_code (maps to account_number)
     */
    public function getAccountCodeAttribute()
    {
        return $this->account_number;
    }

    /**
     * Allow querying by account_code
     */
    public function scopeWhereAccountCode($query, $code)
    {
        return $query->where('account_number', $code);
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function shareProduct()
    {
        return $this->belongsTo(sub_products::class, 'product_number', 'sub_product_id');
    }
}