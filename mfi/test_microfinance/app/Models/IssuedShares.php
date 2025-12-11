<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedShares extends Model
{
    use HasFactory;

    protected $table = 'issued_shares';

    protected $fillable = [
        'institution_id',
        'reference_number',
        'share_id',
        'member',
        'product',
        'account_number',
        'price',
        'branch',
        'client_number',
        'number_of_shares',
        'nominal_price',
        'total_value',
        'linked_savings_account',
        'linked_share_account',
        'status',
        'created_by'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'nominal_price' => 'decimal:2',
        'total_value' => 'decimal:2',
        'number_of_shares' => 'integer'
    ];

    public function share()
    {
        return $this->belongsTo(SharesModel::class, 'share_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'client_number', 'client_number');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 