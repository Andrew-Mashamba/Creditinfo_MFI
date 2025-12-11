<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crdb extends Model
{
    use HasFactory;
    protected $table = 'crdb_transactions';
    protected $guarded = [];
}
