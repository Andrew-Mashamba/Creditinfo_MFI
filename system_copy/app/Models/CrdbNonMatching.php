<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrdbNonMatching extends Model
{
    use HasFactory;
    protected $table = 'crdb_transactions_non_matching';
    protected $guarded = [];
}
