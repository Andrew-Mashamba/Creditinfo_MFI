<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sacco extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
