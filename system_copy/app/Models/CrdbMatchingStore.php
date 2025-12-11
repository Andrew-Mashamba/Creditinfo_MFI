<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrdbMatchingStore extends Model
{
    use HasFactory;
    protected $table = 'crdb_transactions_matching_store';
    protected $guarded = [];
}
