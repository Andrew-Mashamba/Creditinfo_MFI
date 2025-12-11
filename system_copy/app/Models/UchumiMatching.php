<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UchumiMatching extends Model
{
    use HasFactory;
    protected $table = 'uchumi_transactions_matching';
    protected $guarded = [];
}
