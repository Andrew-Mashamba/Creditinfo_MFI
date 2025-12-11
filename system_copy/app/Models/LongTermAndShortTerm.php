<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LongTermAndShortTerm extends Model
{

    use HasFactory;

    protected $table='short_long_term_loans';

    protected $guarded=[];

}
