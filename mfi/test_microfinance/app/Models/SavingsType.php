<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsType extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'summary',
        'status',
        'institution_id'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function subProducts()
    {
        return $this->hasMany(sub_products::class, 'savings_type_id');
    }
} 