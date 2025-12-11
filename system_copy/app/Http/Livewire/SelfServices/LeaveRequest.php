<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use App\Models\LeaveManagement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveRequest extends Component
{
    public $leaveType = '';
    public $startDate = '';
    public $endDate = '';
    public $reason = '';
    public $selectedTab = 'request'; // request, history, balance
    public $leaveHistory = [];
    public $leaveBalance = [];
    public $showSuccessMessage = false;

    protected $rules = [
        'leaveType' => 'required',
        'startDate' => 'required|date|after_or_equal:today',
        'endDate' => 'required|date|after_or_equal:startDate',
        'reason' => 'required|min:10'
    ];

    public function mount()
    {
        $this->loadLeaveData();
    }

    public function loadLeaveData()
    {
        // Sample leave balance data
        $this->leaveBalance = [
            ['type' => 'Annual Leave', 'entitled' => 21, 'used' => 5, 'remaining' => 16],
            ['type' => 'Sick Leave', 'entitled' => 10, 'used' => 2, 'remaining' => 8],
            ['type' => 'Study Leave', 'entitled' => 5, 'used' => 0, 'remaining' => 5],
            ['type' => 'Compassionate Leave', 'entitled' => 3, 'used' => 1, 'remaining' => 2]
        ];

        // Sample leave history
        $this->leaveHistory = [
            [
                'id' => 1,
                'type' => 'Annual Leave',
                'start_date' => '2025-06-15',
                'end_date' => '2025-06-19',
                'days' => 5,
                'status' => 'approved',
                'approved_by' => 'John Manager',
                'reason' => 'Family vacation'
            ],
            [
                'id' => 2,
                'type' => 'Sick Leave',
                'start_date' => '2025-05-10',
                'end_date' => '2025-05-11',
                'days' => 2,
                'status' => 'approved',
                'approved_by' => 'Jane Supervisor',
                'reason' => 'Medical checkup'
            ],
            [
                'id' => 3,
                'type' => 'Compassionate Leave',
                'start_date' => '2025-04-20',
                'end_date' => '2025-04-20',
                'days' => 1,
                'status' => 'approved',
                'approved_by' => 'Mike Director',
                'reason' => 'Family emergency'
            ]
        ];
    }

    public function submitLeaveRequest()
    {
        $this->validate();

        // Calculate number of days
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $days = $start->diffInDays($end) + 1; // Including both start and end dates

        // Here you would save the leave request to the database
        // For now, we'll just show a success message
        
        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['leaveType', 'startDate', 'endDate', 'reason']);
        
        // Reload data
        $this->loadLeaveData();
    }

    public function render()
    {
        return view('livewire.self-services.leave-request');
    }
}