<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositType extends Model
{
    protected $fillable = [
        'type',
        'summary',
        'status',
        'institution_id'
    ];

    public function subProducts()
    {
        return $this->hasMany(sub_products::class, 'deposit_type_id');
    }
} 