<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employeefiles extends Model
{
    use HasFactory;
    protected $table = 'employeefiles';
    protected $fillable = ['employeeN','empName','docName','path'];
}
