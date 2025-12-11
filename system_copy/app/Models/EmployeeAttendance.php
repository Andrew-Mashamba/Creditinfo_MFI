<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'hours_worked',
        'overtime_hours',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate hours worked
    public function calculateHoursWorked()
    {
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_in);
            $checkOut = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->check_out);
            
            // If check out is before check in, assume it's the next day
            if ($checkOut < $checkIn) {
                $checkOut->addDay();
            }
            
            $hoursWorked = $checkOut->diffInMinutes($checkIn) / 60;
            
            // Calculate overtime (assuming 8 hours is standard)
            $standardHours = 8;
            $overtime = max(0, $hoursWorked - $standardHours);
            
            $this->hours_worked = round($hoursWorked, 2);
            $this->overtime_hours = round($overtime, 2);
            
            return $this->hours_worked;
        }
        
        return 0;
    }

    // Determine attendance status based on check-in time
    public function determineStatus($workStartTime = '08:00:00')
    {
        if (!$this->check_in) {
            return 'absent';
        }
        
        $checkInTime = Carbon::parse($this->check_in);
        $expectedTime = Carbon::parse($workStartTime);
        
        // Grace period of 15 minutes
        $gracePeriod = $expectedTime->copy()->addMinutes(15);
        
        if ($checkInTime <= $expectedTime) {
            return 'present';
        } elseif ($checkInTime <= $gracePeriod) {
            return 'present'; // Within grace period
        } else {
            return 'late';
        }
    }
}