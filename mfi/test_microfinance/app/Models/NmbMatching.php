<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NmbMatching extends Model
{
    use HasFactory;
    protected $table = 'nmb_transactions_matching';
    protected $guarded = [];
}
