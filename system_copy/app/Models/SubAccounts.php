<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubAccounts extends Model
{
    use HasFactory;
    public $table = 'sub_accounts';
    public $guarded = [];
}
