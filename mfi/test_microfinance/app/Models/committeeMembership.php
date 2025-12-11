<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class committeeMembership extends Model
{
    use HasFactory;
    protected $table="committee_membership";

    protected $guarded=[];
    
}
