<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntangibleAsset extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $table = 'assets_list';
}
