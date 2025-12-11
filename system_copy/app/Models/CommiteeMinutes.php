<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommiteeMinutes extends Model
{
    use HasFactory;

    protected $guarded=[];
    protected $table ='committee_minutes';
}
