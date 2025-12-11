<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashBookNonMatching extends Model
{
    use HasFactory;
    protected $table = 'cashbook_non_matching';
    protected $guarded = [];
}
