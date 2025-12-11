<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareOwnership extends Model
{
    use HasFactory;

    protected $table = 'share_ownership';
    
    protected $fillable = [
        'institution_id',
        'client_number',
        'shares',
        'total_value',
        'number_of_members',
        'savings',
        'deposits',
        'interest_free_loans',
        'end_business_year_date'
    ];

    protected $casts = [
        'shares' => 'integer',
        'total_value' => 'decimal:2',
        'number_of_members' => 'integer',
        'savings' => 'decimal:2',
        'deposits' => 'decimal:2',
        'interest_free_loans' => 'decimal:2',
        'end_business_year_date' => 'date'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'client_number', 'client_number');
    }
} 