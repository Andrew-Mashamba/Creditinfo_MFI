<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Uchumi extends Model
{
    use HasFactory;
    protected $table = 'uchumi_transactions';
    protected $guarded = [];
}
