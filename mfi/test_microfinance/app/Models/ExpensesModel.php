<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class ExpensesModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'expenses';
}
