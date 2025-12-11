<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountHistoricalBalance extends Model
{
    use HasFactory;

    protected $table = 'account_historical_balances';

    protected $fillable = [
        'year',
        'account_number',
        'account_name',
        'major_category_code',
        'account_level',
        'type',
        'balance',
        'credit',
        'debit',
        'snapshot_date',
        'captured_by',
        'notes'
    ];

    protected $casts = [
        'year' => 'integer',
        'balance' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
        'snapshot_date' => 'datetime'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_number', 'account_number');
    }

    public function capturedByUser()
    {
        return $this->belongsTo(User::class, 'captured_by', 'id');
    }
}
