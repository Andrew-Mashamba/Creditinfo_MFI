<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareWithdrawal extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'withdrawn_shares' => 'integer',
        'nominal_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'approved_at' => 'datetime',
        'withdrawal_date' => 'date'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id');
    }

    public function product()
    {
        return $this->belongsTo(sub_products::class, 'product_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function receivingAccount()
    {
        return $this->belongsTo(Account::class, 'receiving_account_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

  
} 