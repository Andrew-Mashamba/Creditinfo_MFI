<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_mandatory',
        'lower_limit',
        'upper_limit',
        'default_mode'
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'lower_limit' => 'decimal:2',
        'upper_limit' => 'decimal:2'
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
