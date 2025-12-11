<?php

namespace App\Http\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Leave;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveManagement extends Component
{
    use WithPagination;
    
    public $leaveRequests = [];
    public $leaveTypes = [];
    public $totalPending = 0;
    public $totalApproved = 0;
    public $totalRejected = 0;
    public $totalOnLeave = 0;
    public $search = '';
    public $filterStatus = '';

    public function mount()
    {
        $this->loadLeaveData();
    }

    public function loadLeaveData()
    {
        // Leave types
        $this->leaveTypes = [
            'annual' => 'Annual Leave',
            'sick' => 'Sick Leave',
            'maternity' => 'Maternity Leave',
            'paternity' => 'Paternity Leave',
            'compassionate' => 'Compassionate Leave',
            'study' => 'Study Leave',
            'unpaid' => 'Unpaid Leave'
        ];

        // Get real leave statistics
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $this->totalPending = Leave::where('status', 'pending')->count();
        $this->totalApproved = Leave::where('status', 'approved')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();
        $this->totalRejected = Leave::where('status', 'rejected')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();
            
        // Count employees currently on leave
        $today = Carbon::today();
        $this->totalOnLeave = Leave::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();
    }

    public function getLeaveRequests()
    {
        $query = Leave::with('employee')
            ->when($this->search, function($q) {
                $q->whereHas('employee', function($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('employee_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc');
            
        return $query->paginate(10);
    }

    public function approveLeave($leaveId)
    {
        $leave = Leave::find($leaveId);
        
        if ($leave && $leave->status === 'pending') {
            // Calculate days for this leave
            $startDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);
            $leaveDays = $startDate->diffInDays($endDate) + 1;
            
            // Check leave balance
            $employee = $leave->employee;
            $currentYear = Carbon::now()->year;
            
            // Calculate total used days
            $approvedLeaves = Leave::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->get();
                
            $totalUsed = 0;
            foreach ($approvedLeaves as $approvedLeave) {
                $s = Carbon::parse($approvedLeave->start_date);
                $e = Carbon::parse($approvedLeave->end_date);
                $totalUsed += $s->diffInDays($e) + 1;
            }
                
            $balance = 21 - $totalUsed; // 21 days annual leave
            
            if ($leaveDays > $balance && $leave->leave_type === 'annual') {
                session()->flash('error', 'Insufficient leave balance. Employee has only ' . $balance . ' days remaining.');
                return;
            }
            
            $leave->update([
                'status' => 'approved'
            ]);
            
            session()->flash('success', 'Leave request approved successfully!');
            $this->loadLeaveData();
        }
    }

    public function rejectLeave($leaveId, $reason = null)
    {
        $leave = Leave::find($leaveId);
        
        if ($leave && $leave->status === 'pending') {
            $leave->update([
                'status' => 'rejected'
            ]);
            
            session()->flash('info', 'Leave request rejected.');
            $this->loadLeaveData();
        }
    }

    public function getEmployeeLeaveBalance($employeeId)
    {
        $currentYear = Carbon::now()->year;
        $approvedLeaves = Leave::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->get();
            
        $totalUsed = 0;
        foreach ($approvedLeaves as $leave) {
            $s = Carbon::parse($leave->start_date);
            $e = Carbon::parse($leave->end_date);
            $totalUsed += $s->diffInDays($e) + 1;
        }
            
        return 21 - $totalUsed; // 21 days annual leave
    }

    public function render()
    {
        return view('livewire.h-r.leave-management', [
            'leaves' => $this->getLeaveRequests()
        ]);
    }
}