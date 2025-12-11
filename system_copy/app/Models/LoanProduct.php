<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProduct extends Model
{
    public function charges()
    {
        return $this->hasMany(LoanProductCharge::class);
    }
} 