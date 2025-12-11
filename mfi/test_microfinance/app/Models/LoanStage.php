<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanStage extends Model
{
    use HasFactory;
    protected $table='loan_stages';
    protected $guarded=[];
}
