<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'accounts';

    protected $casts = [
        'paid_up_shares' => 'integer',
        'price_per_share' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }

    public function dividends()
    {
        return $this->hasMany(Dividend::class);
    }

    /**
     * Scope to only include share accounts
     */
    public function scopeShares($query)
    {
        return $query->where('product_number', '1000');
    }
}
