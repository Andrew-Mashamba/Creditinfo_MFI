<?php

namespace App\Http\Livewire\Loans;

use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use App\Http\Livewire\Loans\TopUp;

class TopUpLoansTable extends LivewireDatatable
{



    // Listen for the event to refresh data
    protected $listeners = ['countUpdated' => 'refreshCount'];

    // Function to refresh or handle logic after increment
    public function refreshCount()
    {

        dd('wote');
        // Any logic to perform after count is updated, for example:
        // You can also perform some other logic here
        // $this->doSomethingElse();
    }

    public $exportable = true;
    public $receivedSortByBranch;
    public $receivedFilterLoanOfficer;

    public $dataFromParent; // Public property to hold data from the upper component




    public function updateSortByBranch($value)
    {
        $this->receivedSortByBranch = $value;
    }

    public function updateFilterLoanOfficer($value)
    {
        $this->receivedFilterLoanOfficer = $value;
    }

    public function builder()
    {


        $query = LoansModel::query()->where('loan_type_2','Top-up');

        //dd(var_dump($query));



        return $query;
    }

    public function viewClient($memberId)
    {
        Session::put('memberToViewId', $memberId);
        $this->emit('refreshClientsListComponent');
    }

    public function editClient($memberId, $name)
    {
        Session::put('memberToEditId', $memberId);
        Session::put('memberToEditName', $name);
        $this->emit('refreshClientsListComponent');
    }

    public function columns(): array
    {
        return [
            Column::callback(['client_number'], function ($member_number) {
                return DB::table('clients')->where('client_number', $member_number)->value('first_name') . ' ' .
                    DB::table('clients')->where('id', $member_number)->value('middle_name') . ' ' .
                    DB::table('clients')->where('id', $member_number)->value('last_name');
            })->label('Member name'),

            Column::callback(['branch_id'], function ($branch_id) {
                return BranchesModel::where('id', $branch_id)->value('name');
            })->label('Branch'),

            Column::callback('principle', function ($principle) {
                return number_format($principle, 2);
            })->label('Principle (TZS)')->searchable(),

            Column::callback('interest', function ($interest) {
                return $interest . '%';
            })->label('Interest'),

            Column::callback('supervisor_id', function ($supervisor) {
                $employee = Employee::find($supervisor);
                return $employee ? "{$employee->first_name} {$employee->middle_name} {$employee->last_name}" : null;
            })->label('Loan officer'),

            Column::callback(['loan_id'], function ($loan_id) {
                $today = now()->format('Y-m-d');
                $loan_schedules = loans_schedules::where('loan_id', $loan_id)
                    ->where('installment_date', '<=', $today)
                    ->where('completion_status', 'ACTIVE')
                    ->whereNotNull('promise_date')
                    ->get();

                if ($loan_schedules->isNotEmpty()) {
                    $html = '<ul>';
                    foreach ($loan_schedules as $schedule) {
                        $html .= "<li>{$schedule->comment}<br><div class='text-xs text-red-500'>{$schedule->promise_date}</div></li>";
                    }
                    $html .= '</ul><br>';
                    return $html;
                }

                return ' ';
            })->label('Officer update'),

            Column::name('loan_status')->label('Loan Type'),

            Column::callback('status', function ($status) {
                return $status;
            })->label('Status')->searchable(),

            Column::callback('id', function ($id) {
                $member_number = LoansModel::where('id', $id)->value('client_number');

                $html = '<div class="flex items-center space-x-4 flex-lg-row">
                            <button wire:click="viewloan(' . $id . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900 bg-white rounded-md border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 inline-flex items-center dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden text-black m-2">View</span>
                            </button>';

                if (session()->get('loanStageId') == 2 && in_array("Create, edit, and delete loan accounts", session()->get('permission_items'))) {
                    $html .= '<button wire:click="deleteLoanModal(' . $id . ',' . $member_number . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900 bg-white rounded-md border border-red-300 hover:bg-red-100 focus:ring-4 text-red-500 hover:text-red-700">
                                <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="hidden text-customPurple m-2">Delete</span>
                              </button>';
                }

                $html .= '</div>';
                return $html;
            })->label('View'),
        ];
    }


    public function viewloan($id){


        $member_number = LoansModel::where('id',$id)->value('client_number');



        Session::forget('currentloanClient');
        Session::forget('currentloanID');


        Session::put('currentloanClient',$member_number);
        Session::put('currentloanID',$id);

       // dd('hhh');
        $this->emit( 'viewLoanDetails');
        //$this->emitSelf('countUpdated');

    }







    public function deleteLoanModal($id,$member_number){
        $member_number = LoansModel::where('id',$id)->value('member_number');
        session::forget('loanAccountID');
        session::forget('currentloanClientDeleteModal');
        session::put('loanAccountID',$id);
        session::put('currentloanClientDeleteModal',$member_number);
        $this->emit('deleteLoan');

    }


}


