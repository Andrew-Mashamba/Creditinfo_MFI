<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nmb extends Model
{
    use HasFactory;
    protected $table = 'nmb_transactions';
    protected $guarded = [];
}
