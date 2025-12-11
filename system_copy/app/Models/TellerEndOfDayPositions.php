<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TellerEndOfDayPositions extends Model
{
    use HasFactory;
    protected $table = 'teller_end_of_day_positions';
    protected $guarded = [];
}
