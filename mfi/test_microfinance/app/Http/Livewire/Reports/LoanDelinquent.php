<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;


class LoanDelinquent extends LivewireDatatable
{

    public $valuePath;
    public $value;
    public $value2;
    public $value3;

    public $endDate;
    public $startDate,$branch,$endRange,$startRange;

    protected $listeners = [
        'daysRange' => 'daysRange',
        'daysIN' => 'daysIN',
        'endRange','setStartDate','setEndDate','startRange','setBranch'
    ];

    function setBranch($value){
       $this->branch=$value;
    }

    function startRange($value){

        $this->startRange=$value;
    }
    function setStartDate($value){
          $this->startDate=$value;
    }

    function setEndDate($value){
        $this->endDate=$value;
    }

    function endRange($value){
        $this->endRange=$value;
    }
    public function builder()
    {
        try {
            $query = LoansModel::query();




            if(!empty($this->startRange)){
                $query->where('days_in_arrears','>=',$this->startRange);
            }


            if(!empty($this->endRange)){
                $query->where('days_in_arrears','<=',$this->endRange);
            }


            if(!empty($this->endDate)){
                $query->where('created_at','>=',$this->endDate);
            }


            if(!empty($this->startDate)){
                $query->where('created_at','<=',$this->startDate);
            }

            if(!empty($this->branch)){
                $query->where('branch_id',$this->branch);
            }







            return $query;
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            // \Log::error('Error in builder function: ' . $e->getMessage());

            // Return an empty query or handle the error as needed
            return LoansModel::query(); // returns no results
        }
    }


    public function daysRange($start, $end)
    {
        $this->valuePath = "Range";
        $this->value = $start;
        $this->value2 = $end;
    }

    public function daysIN($day)
    {
        $this->valuePath = "day";
    }

    public function columns()
    {




        return [


            Column::callback(['client_id'], function ($member_number) {

                return ClientsModel::where('id', $member_number)->value('first_name') . ' ' . ClientsModel::where('id', $member_number)->value('middle_name') . ' ' . ClientsModel::where('id', $member_number)->value('last_name');
            })->label('Client name'),

            Column::callback(['guarantor'], function ($guarantor) {

                return ClientsModel::where('client_number', $guarantor)->value('first_name') . ' ' . ClientsModel::where('client_number', $guarantor)->value('middle_name') . ' ' . ClientsModel::where('client_number', $guarantor)->value('last_name');
            })->label('Guarantor'),
            Column::callback(['branch_id'], function ($branch_id) {

                return BranchesModel::where('id', $branch_id)->value('name');
            })->label('Branch'),

            Column::name('loan_id')
                ->label('loan id'),

            Column::name('loan_account_number')
                ->label('loan account number'),

            Column::callback(['days_in_arrears'], function ($days_in_arrears) {
                if ($days_in_arrears > 0) {
                    return '<div class="bg-red-500 p-2 "> ' . $days_in_arrears . ' </div>';
                } else {
                    return '<div class=" "> 0 </div>';
                }
            })->label('past due days')->searchable(),


            Column::callback('principle', function ($principle) {
                return number_format($principle, 2);
            })
                ->label('principle (TZS)')->searchable(),

            Column::callback('interest', function ($interest) {
                return $interest . '%';
            })
                ->label('interest'),

            Column::name('heath')
                ->label('health'),

            Column::callback( 'loan_id', function ( $loan_id) {
                return loans_schedules::where('loan_id', $loan_id)->where('comment', '!=', null)->max('comment');
            })
                ->label('officer updates'),

            Column::callback([ 'supervisor_id'], function ( $supervisor) {
                $first =    Employee::where('id', $supervisor)->value('first_name');
                $middle = Employee::where('id', $supervisor)->value('middle_name');
                $last = Employee::where('id', $supervisor)->value('last_name');

                return  $first . ' ' . $middle . ' ' . $last;
            })->label('officer Name'),


            Column::callback('status', function ($status) {

                return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
            })->label('Status')->searchable(),


        ];
    }
}
