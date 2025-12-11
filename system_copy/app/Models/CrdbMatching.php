<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrdbMatching extends Model
{
    use HasFactory;
    protected $table = 'crdb_transactions_matching';
    protected $guarded = [];
}
