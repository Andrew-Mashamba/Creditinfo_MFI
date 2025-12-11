<?php

namespace App\Http\Livewire\SelfServices;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeRequest extends Component
{
    public $selectedTab = 'request'; // request, history, summary
    public $overtimeDate = '';
    public $startTime = '';
    public $endTime = '';
    public $totalHours = 0;
    public $reason = '';
    public $projectCode = '';
    public $supervisor = '';
    public $overtimeType = '';
    public $showSuccessMessage = false;
    public $overtimeHistory = [];
    public $overtimeSummary = [];
    public $overtimeRates = [];
    public $monthlyLimit = 40; // Hours per month

    protected $rules = [
        'overtimeDate' => 'required|date',
        'startTime' => 'required',
        'endTime' => 'required|after:startTime',
        'reason' => 'required|min:20',
        'projectCode' => 'required',
        'supervisor' => 'required',
        'overtimeType' => 'required'
    ];

    protected $messages = [
        'endTime.after' => 'End time must be after start time.',
        'reason.min' => 'Please provide a detailed reason (minimum 20 characters).',
    ];

    public function mount()
    {
        $this->loadOvertimeData();
    }

    public function loadOvertimeData()
    {
        // Sample overtime history
        $this->overtimeHistory = [
            [
                'id' => 1,
                'date' => '2025-07-25',
                'start' => '18:00',
                'end' => '21:00',
                'hours' => 3,
                'type' => 'Weekday',
                'status' => 'approved',
                'approved_by' => 'John Manager',
                'amount' => 75000,
                'reason' => 'Urgent client deliverable'
            ],
            [
                'id' => 2,
                'date' => '2025-07-20',
                'start' => '08:00',
                'end' => '17:00',
                'hours' => 9,
                'type' => 'Weekend',
                'status' => 'approved',
                'approved_by' => 'Jane Supervisor',
                'amount' => 300000,
                'reason' => 'System maintenance window'
            ],
            [
                'id' => 3,
                'date' => '2025-08-01',
                'start' => '19:00',
                'end' => '22:00',
                'hours' => 3,
                'type' => 'Weekday',
                'status' => 'pending',
                'approved_by' => null,
                'amount' => 75000,
                'reason' => 'Monthly report preparation'
            ],
            [
                'id' => 4,
                'date' => '2025-07-15',
                'start' => '18:00',
                'end' => '20:00',
                'hours' => 2,
                'type' => 'Weekday',
                'status' => 'rejected',
                'approved_by' => 'Mike Director',
                'amount' => 0,
                'reason' => 'Regular work completion'
            ]
        ];

        // Overtime summary
        $currentMonth = Carbon::now()->format('F Y');
        $this->overtimeSummary = [
            'current_month' => $currentMonth,
            'hours_worked' => 17,
            'hours_approved' => 14,
            'hours_pending' => 3,
            'hours_remaining' => 23, // From monthly limit
            'total_earnings' => 450000
        ];

        // Overtime rates
        $this->overtimeRates = [
            ['type' => 'Weekday (Mon-Fri)', 'rate' => '1.5x', 'per_hour' => 25000],
            ['type' => 'Weekend (Sat-Sun)', 'rate' => '2.0x', 'per_hour' => 33000],
            ['type' => 'Public Holiday', 'rate' => '2.5x', 'per_hour' => 42000],
            ['type' => 'Night Shift (10PM-6AM)', 'rate' => '2.0x', 'per_hour' => 33000]
        ];
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['startTime', 'endTime'])) {
            $this->calculateHours();
        }
    }

    public function calculateHours()
    {
        if ($this->startTime && $this->endTime) {
            $start = Carbon::parse($this->startTime);
            $end = Carbon::parse($this->endTime);
            
            if ($end->greaterThan($start)) {
                $this->totalHours = round($start->floatDiffInHours($end), 2);
            } else {
                $this->totalHours = 0;
            }
        }
    }

    public function submitOvertimeRequest()
    {
        $this->validate();

        // Check if overtime date is not in the future
        if (Carbon::parse($this->overtimeDate)->greaterThan(Carbon::today())) {
            $this->addError('overtimeDate', 'Cannot request overtime for future dates.');
            return;
        }

        // Check monthly limit
        $hoursThisMonth = $this->overtimeSummary['hours_worked'] + $this->overtimeSummary['hours_pending'];
        if (($hoursThisMonth + $this->totalHours) > $this->monthlyLimit) {
            $this->addError('totalHours', 'This request exceeds your monthly overtime limit of ' . $this->monthlyLimit . ' hours.');
            return;
        }

        // Here you would save the overtime request to the database
        $this->showSuccessMessage = true;
        
        // Reset form
        $this->reset(['overtimeDate', 'startTime', 'endTime', 'totalHours', 
                     'reason', 'projectCode', 'supervisor', 'overtimeType']);
        
        // Reload data
        $this->loadOvertimeData();
    }

    public function render()
    {
        return view('livewire.self-services.overtime-request');
    }
}