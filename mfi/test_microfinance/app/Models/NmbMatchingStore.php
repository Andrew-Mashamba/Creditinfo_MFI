<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NmbMatchingStore extends Model
{
    use HasFactory;
    protected $table = 'nmb_transactions_matching_store';
    protected $guarded = [];
}
