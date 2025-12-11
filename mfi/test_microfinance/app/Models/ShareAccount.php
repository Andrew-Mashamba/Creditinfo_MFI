<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShareAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'member_number',
        'share_type_id',
        'shares_balance',
        'status',
        'account_number',
        'institution_id'
    ];

    protected $casts = [
        'shares_balance' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_number', 'member_number');
    }

    public function shareType()
    {
        return $this->belongsTo(Share::class, 'share_type_id');
    }

    public function transactions()
    {
        return $this->hasMany(ShareTransaction::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
} 