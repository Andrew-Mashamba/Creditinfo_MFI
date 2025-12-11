<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GepgTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'control_number',
        'account_no',
        'amount',
        'currency',
        'response_code',
        'response_description',
        'payload',
        'transaction_type',
        'quote_reference',
    ];

    protected $casts = [
        'payload' => 'array',
        'amount' => 'decimal:2',
    ];
}
