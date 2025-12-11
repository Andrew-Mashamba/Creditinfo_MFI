<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionRetryLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'transaction_retry_logs';

    protected $casts = [
        'retry_at' => 'datetime',
        'retry_payload' => 'array',
        'retry_response' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
} 