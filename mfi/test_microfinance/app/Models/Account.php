<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'member_id',
        'account_number',
        'type',
        'account_type',
        'balance',
        'opening_balance',
        'status',
        'term',
        'maturity_date'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'maturity_date' => 'datetime'
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function parentAccount()
    {
        return $this->belongsTo(Account::class, 'parent_account_number', 'account_number');
    }

    public function client()
    {
        return $this->belongsTo(ClientsModel::class, 'client_number', 'client_number');
    }
} 