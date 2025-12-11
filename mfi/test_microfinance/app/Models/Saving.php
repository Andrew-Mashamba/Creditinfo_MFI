<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $table = 'accounts';
    protected $guarded = [];

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    /**
     * Scope to only include savings accounts
     */
    public function scopeSavings($query)
    {
        return $query->where('product_number', '2000');
    }
} 