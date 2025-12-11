<?php

namespace App\Http\Livewire\Services;

use App\Models\PayRolls;
use Livewire\Component;
use Carbon\Carbon;

class SalarySlip extends Component
{
    public $payroll_summary;
    public $currentMonth;
    public $currentYear;
    public $selected_date;

    public function getDataThisMonthForEmployee($employee_id,$currentMonth, $currentYear)
    {


        $data = PayRolls::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->where('employee_id', $employee_id)
            ->get();

        return $data;
    }

    function boot(){

        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;

        $carbonDate = Carbon::parse($this->selected_date);

        $month = $carbonDate->month; // Get the month
        $year = $carbonDate->year;


    }
    public function render()
    {
       $this->payroll_summary =$this->getDataThisMonthForEmployee(auth()->user()->employeeId,$this->currentMonth,$this->currentYear );

        return view('livewire.services.salary-slip');
    }
}
