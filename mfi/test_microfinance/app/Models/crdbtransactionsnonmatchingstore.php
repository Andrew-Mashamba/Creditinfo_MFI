<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class crdbtransactionsnonmatchingstore extends Model
{
    use HasFactory;
    protected $table = 'crdb_transactions_non_matching_store';
    protected $guarded = [];
}
