<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'payment_ref',
        'transaction_reference',
        'control_number',
        'amount',
        'currency',
        'payment_channel',
        'payer_name',
        'payer_msisdn',
        'payer_email',
        'payer_tin',
        'payer_nin',
        'paid_at',
        'received_at',
        'status',
        'raw_payload',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
        'raw_payload' => 'array',
        'paid_at' => 'datetime',
        'received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'FAILED');
    }
}
