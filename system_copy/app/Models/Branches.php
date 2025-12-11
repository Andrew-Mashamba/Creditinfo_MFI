<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branches extends Model
{
    use HasFactory;
    use Search;

    protected $guarded = [];
    protected $searchable = [
        'name',
        'region',
        'wilaya',
        'membershipNumber',


    ];
}
