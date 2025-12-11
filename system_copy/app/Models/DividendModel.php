<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DividendModel extends Model
{
    use HasFactory;

    protected $table = 'dividends';

    protected $fillable = [
        'member_id',
        'year',
        'rate',
        'amount',
        'paid_at',
        'payment_mode',
        'status',
        'narration',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'rate' => 'float',
        'amount' => 'float',
    ];

    public function member()
    {
        return $this->belongsTo(ClientsModel::class, 'member_id');
    }
} 