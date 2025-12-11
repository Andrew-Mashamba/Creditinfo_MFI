<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareRegister extends Model
{
    use SoftDeletes;
    
    protected $table = 'share_registers';
    
    protected $guarded = [];

    /**
     * Get the member that owns the share register.
     */
    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id', 'id');
    }

    /**
     * Get the share product associated with the register.
     */
    public function product()
    {
        return $this->belongsTo(sub_products::class, 'product_id', 'id');
    }

    /**
     * Get the account associated with the share register.
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'share_account_number', 'account_number');
    }

    /**
     * Scope a query to only include active share registers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the total value of shares.
     */
    public function getTotalValueAttribute()
    {
        return $this->current_share_balance * $this->current_price;
    }

    /**
     * Get the available shares for withdrawal.
     */
    public function getAvailableSharesAttribute()
    {
        return $this->current_share_balance - $this->total_shares_redeemed;
    }
} 