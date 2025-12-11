<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $table='leaves';
    protected $guarded=[];
    
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
