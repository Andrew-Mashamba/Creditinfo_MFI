<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class payableModel extends Model
{
    use HasFactory;
    public $table = 'payables';
    public $guarded =[];
}
