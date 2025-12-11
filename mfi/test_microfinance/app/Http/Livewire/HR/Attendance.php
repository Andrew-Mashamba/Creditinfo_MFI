<?php

namespace App\Http\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Attendance extends Component
{
    use WithPagination;
    
    public $attendanceData = [];
    public $todayPresent = 0;
    public $todayAbsent = 0;
    public $todayLate = 0;
    public $todayOnLeave = 0;
    public $monthlyAttendanceRate = 0;
    public $selectedDate;
    public $search = '';
    public $filterStatus = '';
    
    // Check-in/out modal
    public $showCheckInModal = false;
    public $selectedEmployee = null;
    public $checkInTime = '';
    public $checkOutTime = '';
    public $attendanceNotes = '';

    public function mount()
    {
        $this->selectedDate = Carbon::today()->format('Y-m-d');
        $this->loadAttendanceData();
        $this->generateTodayAttendance();
    }

    public function loadAttendanceData()
    {
        $date = Carbon::parse($this->selectedDate);
        
        // Get total employees
        $totalEmployees = Employee::where('employee_status', 'active')->count();
        
        // Get today's attendance statistics
        $this->todayPresent = EmployeeAttendance::where('date', $date)
            ->where('status', 'present')
            ->count();
            
        $this->todayAbsent = EmployeeAttendance::where('date', $date)
            ->where('status', 'absent')
            ->count();
            
        $this->todayLate = EmployeeAttendance::where('date', $date)
            ->where('status', 'late')
            ->count();
            
        $this->todayOnLeave = EmployeeAttendance::where('date', $date)
            ->where('status', 'leave')
            ->count();
        
        // Calculate monthly attendance rate
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        
        $totalWorkDays = 0;
        $totalPresent = 0;
        
        for ($day = $startOfMonth->copy(); $day <= $endOfMonth; $day->addDay()) {
            if (!$day->isWeekend()) {
                $totalWorkDays++;
                if ($day <= Carbon::today()) {
                    $present = EmployeeAttendance::where('date', $day)
                        ->whereIn('status', ['present', 'late'])
                        ->count();
                    $totalPresent += $present;
                }
            }
        }
        
        if ($totalWorkDays > 0 && $totalEmployees > 0) {
            $expectedAttendance = $totalEmployees * min($totalWorkDays, Carbon::today()->day);
            $this->monthlyAttendanceRate = $expectedAttendance > 0 
                ? round(($totalPresent / $expectedAttendance) * 100, 1) 
                : 0;
        }
    }

    public function generateTodayAttendance()
    {
        $date = Carbon::parse($this->selectedDate);
        
        // Skip weekends
        if ($date->isWeekend()) {
            return;
        }
        
        // Get all active employees
        $employees = Employee::where('employee_status', 'active')->get();
        
        foreach ($employees as $employee) {
            // Check if attendance already exists for this date
            $attendance = EmployeeAttendance::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();
                
            if (!$attendance && $date->isToday()) {
                // Create default attendance record (absent by default, will be updated when they check in)
                EmployeeAttendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'status' => 'absent'
                ]);
            }
        }
    }

    public function checkIn($employeeId)
    {
        $date = Carbon::today();
        $attendance = EmployeeAttendance::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();
            
        if (!$attendance) {
            $attendance = new EmployeeAttendance([
                'employee_id' => $employeeId,
                'date' => $date
            ]);
        }
        
        $attendance->check_in = Carbon::now()->format('H:i:s');
        $attendance->status = $attendance->determineStatus();
        $attendance->save();
        
        session()->flash('success', 'Check-in recorded successfully!');
        $this->loadAttendanceData();
    }

    public function checkOut($employeeId)
    {
        $date = Carbon::today();
        $attendance = EmployeeAttendance::where('employee_id', $employeeId)
            ->where('date', $date)
            ->first();
            
        if ($attendance) {
            $attendance->check_out = Carbon::now()->format('H:i:s');
            $attendance->calculateHoursWorked();
            $attendance->save();
            
            session()->flash('success', 'Check-out recorded successfully!');
            $this->loadAttendanceData();
        }
    }

    public function markAttendance($employeeId, $status)
    {
        $date = Carbon::parse($this->selectedDate);
        
        $attendance = EmployeeAttendance::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'date' => $date
            ],
            [
                'status' => $status,
                'notes' => $this->attendanceNotes
            ]
        );
        
        if ($status == 'present') {
            if (!$attendance->check_in) {
                $attendance->check_in = '08:00:00';
            }
            if (!$attendance->check_out) {
                $attendance->check_out = '17:00:00';
            }
            $attendance->calculateHoursWorked();
            $attendance->save();
        }
        
        session()->flash('success', 'Attendance marked successfully!');
        $this->loadAttendanceData();
        $this->closeModal();
    }

    public function openCheckInModal($employeeId)
    {
        $this->selectedEmployee = Employee::find($employeeId);
        $attendance = EmployeeAttendance::where('employee_id', $employeeId)
            ->where('date', $this->selectedDate)
            ->first();
            
        if ($attendance) {
            $this->checkInTime = $attendance->check_in;
            $this->checkOutTime = $attendance->check_out;
            $this->attendanceNotes = $attendance->notes;
        } else {
            $this->checkInTime = '';
            $this->checkOutTime = '';
            $this->attendanceNotes = '';
        }
        
        $this->showCheckInModal = true;
    }

    public function saveAttendance()
    {
        $this->validate([
            'checkInTime' => 'nullable|date_format:H:i',
            'checkOutTime' => 'nullable|date_format:H:i|after:checkInTime',
        ]);
        
        $attendance = EmployeeAttendance::updateOrCreate(
            [
                'employee_id' => $this->selectedEmployee->id,
                'date' => $this->selectedDate
            ],
            [
                'check_in' => $this->checkInTime ?: null,
                'check_out' => $this->checkOutTime ?: null,
                'notes' => $this->attendanceNotes
            ]
        );
        
        // Determine status
        if ($this->checkInTime) {
            $attendance->status = $attendance->determineStatus();
            if ($this->checkOutTime) {
                $attendance->calculateHoursWorked();
            }
        } else {
            $attendance->status = 'absent';
        }
        
        $attendance->save();
        
        session()->flash('success', 'Attendance updated successfully!');
        $this->closeModal();
        $this->loadAttendanceData();
    }

    public function closeModal()
    {
        $this->showCheckInModal = false;
        $this->selectedEmployee = null;
        $this->checkInTime = '';
        $this->checkOutTime = '';
        $this->attendanceNotes = '';
    }

    public function getAttendanceRecords()
    {
        $date = Carbon::parse($this->selectedDate);
        
        $query = Employee::with(['attendances' => function($q) use ($date) {
                $q->where('date', $date);
            }])
            ->where('employee_status', 'active')
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                });
            });
            
        if ($this->filterStatus) {
            $query->whereHas('attendances', function($q) use ($date) {
                $q->where('date', $date)
                  ->where('status', $this->filterStatus);
            });
        }
        
        return $query->paginate(15);
    }

    public function previousDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
        $this->loadAttendanceData();
    }

    public function nextDay()
    {
        $date = Carbon::parse($this->selectedDate);
        if (!$date->isFuture()) {
            $this->selectedDate = $date->addDay()->format('Y-m-d');
            $this->loadAttendanceData();
        }
    }

    public function render()
    {
        return view('livewire.h-r.attendance', [
            'employees' => $this->getAttendanceRecords()
        ]);
    }
}