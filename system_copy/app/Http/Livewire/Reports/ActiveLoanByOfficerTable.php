<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\Column;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use App\Models\BranchesModel;
use App\Models\Employee;
class ActiveLoanByOfficerTable extends LivewireDatatable
{

    public $loanOfficer;
    protected $listeners=['loanOfficer'=>'setLoanOfficer'];

    public $exportable=true;
    public function builder()
    {

    $query= LoansModel::query()->where('status','ACTIVE');

    if(!empty($this->loanOfficer)){
        $query->where('supervisor_id',$this->loanOfficer);
    }

    return $query;


    }

    public function setLoanOfficer($loanOfficer){
        $this->loanOfficer=$loanOfficer;
    }



    public function columns()
{
    return [
        // Column for Member Name
        Column::callback(['client_id'], function ($clientId) {
            $client = ClientsModel::find($clientId);
            if ($client) {
                return trim("{$client->first_name} {$client->middle_name} {$client->last_name}");
            }
            return 'N/A'; // Return a default value if client not found
        })->label('Member name')->searchable(),

        // // Column for Guarantor
        Column::callback(['guarantor'], function ($guarantorId) {
            $guarantor = ClientsModel::where('client_number', $guarantorId)->first();
            if ($guarantor) {
                return trim("{$guarantor->first_name} {$guarantor->middle_name} {$guarantor->last_name}");
            }
            return 'N/A'; // Return a default value if guarantor not found
        })->label('Guarantor'),

        // // Column for Branch Name
        Column::callback(['branch_id'], function ($branchId) {
            $branch = BranchesModel::find($branchId);
            return $branch ? $branch->name : 'N/A'; // Return 'N/A' if branch not found
        })->label('Branch')->searchable(),

        // // Column for Loan ID
        Column::name('loan_id')->label('Loan ID'),

        // // Column for Past Due Days
        Column::callback(['days_in_arrears'], function ($daysInArrears) {
            $class = $daysInArrears > 0 ? 'bg-red-500 p-2' : '';
            return "<div class='{$class}'>" . max($daysInArrears, 0) . "</div>";
        })->label('Past Due Days')->searchable(),

        // // Column for Principle
        Column::callback('principle', function ($principle) {
            return number_format($principle, 2);
        })->label('Principle (TZS)')->searchable(),

        // // Column for Interest
        Column::callback('interest', function ($interest) {
            return "{$interest}%";
        })->label('Interest'),


        // // Column for Loan Officer
        Column::callback('supervisor_id', function ($supervisorId) {
            $employee = Employee::find($supervisorId);
            return $employee ? trim("{$employee->first_name} {$employee->middle_name} {$employee->last_name}") : 'N/A';
        })->label('Loan Officer'),

        // Column for Officer Update
        // Column::callback(['id', 'loan_id'], function ($id, $loanId) {
        //     $today = now()->format('Y-m-d');
        //     $loanSchedules = loans_schedules::where('loan_id', $loanId)
        //         ->where('installment_date', '<=', $today)
        //         ->where('completion_status', 'ACTIVE')
        //         ->whereNotNull('promise_date')
        //         ->get();

        //     if ($loanSchedules->isNotEmpty()) {
        //         $html = '<ul>';
        //         foreach ($loanSchedules as $schedule) {
        //             $html .= '<li>' . $schedule->comment . '<br><div class="text-xs text-red-500">' . $schedule->promise_date . '</div></li>';
        //         }
        //         $html .= '</ul><br>';
        //         return $html;
        //     }

        //     return ' ';
        // })->label('Officer Update'),


       Column::name('loan_status')->label('Loan Status'),
       Column::name('status')->label('Action Status'),

    ];
}


}
