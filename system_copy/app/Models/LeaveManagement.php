<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LeaveManagement extends Model
{
    use HasFactory;

    protected $table='leave_management';
    protected $fillable=['total_days','days_acquire','leave_days_taken','balance','employee_number','created_at','updated_at'];

    public function employeeLeave($employeeId){

        LeaveManagement::create([
           'total_days'=>28,
            'days_acquire'=>0,
            'leave_days_taken'=>0,
            'balance'=>0,
            'employee_number'=>$employeeId,
            'created_at'=>carbon::now(),
            'updated_at'=>carbon::now(),
        ]);
    }
}
