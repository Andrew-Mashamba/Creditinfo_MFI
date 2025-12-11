<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'share_account_id',
        'transaction_type',
        'shares',
        'price_per_share',
        'total_amount',
        'narration',
        'status',
        'recipient_account_id'
    ];

    protected $casts = [
        'shares' => 'decimal:2',
        'price_per_share' => 'decimal:2',
        'total_amount' => 'decimal:2'
    ];

    public function shareAccount()
    {
        return $this->belongsTo(ShareAccount::class);
    }

    public function recipientAccount()
    {
        return $this->belongsTo(ShareAccount::class, 'recipient_account_id');
    }
} 