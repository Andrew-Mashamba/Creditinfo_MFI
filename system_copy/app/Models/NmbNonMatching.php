<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NmbNonMatching extends Model
{
    use HasFactory;
    protected $table = 'nmb_transactions_non_matching';
    protected $guarded = [];
}
