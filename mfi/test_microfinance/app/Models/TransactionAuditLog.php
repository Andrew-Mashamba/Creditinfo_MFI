<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionAuditLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'transaction_audit_logs';

    protected $casts = [
        'changes' => 'array',
        'context' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
} 