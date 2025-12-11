<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionReconciliation extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'transaction_reconciliations';

    protected $casts = [
        'external_transaction_date' => 'datetime',
        'reconciled_at' => 'datetime',
        'reconciliation_data' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
} 