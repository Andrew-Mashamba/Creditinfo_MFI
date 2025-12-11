<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositsModel extends Model
{
    use HasFactory;
    protected $table = 'accounts';
    protected $guarded = [];
}