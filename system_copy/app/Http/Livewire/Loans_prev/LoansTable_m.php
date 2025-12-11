<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\loans_schedules;
use Carbon\Carbon;
use Livewire\Component;
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

class LoansTable_m extends LivewireDatatable
{

    protected $listeners = [
        'refreshSavingsComponent' => '$refresh',
        'sortByBranchChanged' => 'updateSortByBranch',
        'filterLoanOfficerChanged' => 'updateFilterLoanOfficer'
    ];
    public $exportable = true;
    public $receivedSortByBranch;
    public $receivedFilterLoanOfficer;

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
        if(DB::table('departments')->where('id',auth()->user()->department)->value('department_name')=="MANAGEMENT"){
            if(session()->get('loanStageId')==2){

            return LoansModel::query()->where('status','!=','NEW LOAN');
            }else{
                return  LoansModel::query()->where('branch_id',12345);;
            }

        }else{
             if(session()->get('loanStageId')==2){
            return LoansModel::query()->where('branch_id',auth()->user()->branch)->where('supervisor_id',auth()->user()->id)->where('status','!=','NEW LOAN');
             }else{
                 return LoansModel::query()->where('branch_id',12345);
             }
        }
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

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {


//        Session::put('currentloanClient',null);
//        Session::put('currentloanClient',null);
//        Session::put('currentloanID',null);

        $html =null;

         $hq=BranchesModel::where('id',auth()->user()->branch)->value('name');



            return [

                Column::callback(['client_id'], function ($member_number) {

                    return ClientsModel::where('id',$member_number)->value('first_name').' '.ClientsModel::where('id',$member_number)->value('middle_name').' '.ClientsModel::where('id',$member_number)->value('last_name');
                })->label('Member name'),

                Column::callback(['guarantor'], function ($guarantor) {

                    return ClientsModel::where('client_number',$guarantor)->value('first_name').' '.ClientsModel::where('client_number',$guarantor)->value('middle_name').' '.ClientsModel::where('client_number',$guarantor)->value('last_name');
                })->label('Guarantor'),
                Column::callback(['branch_id'], function ($branch_id) {

                    return BranchesModel::where('id',$branch_id)->value('name');
                })->label('Branch'),

                // Column::name('loan_id')
                //     ->label('loan id'),

                Column::name('loan_account_number')
                    ->label('loan account number'),

                Column::callback(['days_in_arrears'],function($days_in_arrears){
                    if($days_in_arrears >0){
                        return '<div class="bg-red-500 p-2 "> '.$days_in_arrears.' </div>';
                    }else{
                        return '<div class=" "> 0 </div>';
                    }
                })->label('past due days')->searchable(),


                Column::callback('principle',function ($principle){
                    return number_format($principle,2);
                })
                    ->label('principle (TZS)')->searchable(),

                Column::callback('interest',function ($interest){
                    return $interest .'%';
                })
                    ->label('interest'),

                Column::name('heath')
                    ->label('health'),

                Column::callback('supervisor_id',function($supervisor){
                    $employee=Employee::where('id',$supervisor)->first();
                    if($employee) {
                        return $employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->last_name;
                    }else{
                        return null;
                    }

                    })->label('Loan officer'),

                Column::callback(['loan_id'],function($loan_id){

                    $today=now()->format('Y-m-d');
                    $loan_schedules = loans_schedules::where('loan_id',$loan_id)
                        ->where('installment_date', '<=', $today)
                        ->where('completion_status', 'ACTIVE')
                        ->whereNotNull('promise_date')
                        ->get(); // Retrieve all relevant loan schedules


                    if ($loan_schedules) {
                        // Check if the array is not empty

                        $html = '<ul>';
                        foreach ($loan_schedules as $comment) {
                            $html .= '<li>' . $comment->comment . '<br> <div class="text-xs text-red-500">'.$comment->promise_date .'</div>'.  '</li>';
                        }
                        $html .= '</ul> <br>';

                        return $html;

                    } else {
                        return  ' ';
                    }


                })->label('officer update'),

                Column::name('loan_status')
                    ->label('Loan Type'),


                Column::callback('status',function ($status){

                    return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
                })->label('Status')->searchable(),

                Column::callback('id', function ($id) use ($html) {
                    //$status = 1;

                    $member_number = LoansModel::where('id',$id)->value('client_number');

                    $status = LoansModel::where('id',$id)->value('status');


                    if($status == 'ONPROGRESS') {
                        $status = 1;

                    }
                    elseif ($status == 'AWAITING APPROVAL'){
                        $status = 2;
                    }
                    elseif ($status == 'APPROVED'){
                        $status = 3;
                    }
                    elseif ($status == 'RESTRUCTURED'){
                        $status = 4;
                    }
                    elseif ($status == 'TOP UP'){
                        $status = 5;
                    }
                    elseif ($status == 'ACTIVE'){
                        $status = 6;
                    }
                    elseif ($status == 'REJECTED'){
                        $status = 7;
                    }
                    elseif ($status == 'RECOVERY'){
                        $status = 8;
                    }elseif($status=="AWAITING DISBURSEMENT"){
                        $status = 9;
                    }elseif($status=="CLOSED"){
                        $status=10;
                    }

                    if(session()->get('loanStageId')==2){
//                        if(session()->get('sortByBranch') == DB::table('branches')->where('name','HQ')->value('id')) {

                            $html2 = null;
                            if (in_array("Create, edit, and delete loan accounts", session()->get('permission_items'))) {
                                $html2 = '
                           <style>
                              .hoverable:hover .hidden {
                                  display: block;
                               }

                           </style>
                         <button wire:click="deleteLoanModal(' . $id . ',' . $member_number . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-md border border-red-300 hover:bg-red-100 focus:ring-4   text-red-500 hover:text-red-700 hover:bg-red-100
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 inline-flex items-center
                            dark:placeholder-gray-400 dark:text-red dark:focus:ring-blue-500 dark:focus:border-blue-500">
                               <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="hidden text-red-400 m-2">Delete</span>

                            </button>
                           ';
                            }
//                        }

                    }
                    else{
                        $html2=null;
                    }

//                    if(session()->get('sortByBranch') == DB::table('branches')->where('name','HQ')->value('id')) {

                        $html = '
                            <style>
                              .hoverable:hover .hidden {
                                  display: block;
                               }
                           </style>

                          <div class="flex items-center space-x-4 flex-lg-row">
                            <button wire:click="viewloan(' . $id . ',' . $member_number . ',' . $status . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-md border border-gray-300 hover:bg-gray-100 focus:ring-4
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 items-center inline-flex
                            dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span class="hidden text-black m-2">View</span>

                            </button>

                            ' . $html2 . '

                            </div> ';
//                    }

                    return $html;
                })->label('view'),

            ];


    }





    public function viewloan($id,$member_number,$status){

        $member_number = LoansModel::where('id',$id)->value('client_number');
        $loanType = LoansModel::where('id',$id)->value('loan_type');
//

        Session::forget('currentloanClient');
        Session::forget('currentloanID');

        if($status == 1){
            Session::put('loanStatus','ONPROGRESS');
        }elseif ($status == 2){
            Session::put('loanStatus','AWAITING APPROVAL');
        }
        elseif ($status == 9){
            Session::put('loanStatus','AWAITING DISBURSEMENT');
        }elseif($status ==10){
            Session::put('loanStatus','CLOSED');
        }
        elseif ($status == 3){
            Session::put('loanStatus','APPROVED');
        }
        elseif ($status == 4){
            Session::put('loanStatus','RESTRUCTURED');
        }
        elseif ($status == 5){
            Session::put('loanStatus','TOP UP');
        }
        elseif ($status == 6){
            Session::put('loanStatus','ACTIVE');
        }
        elseif ($status == 7){
            Session::put('loanStatus','REJECTED');
        }
        elseif ($status == 8){
            Session::put('loanStatus','RECOVERY');
        }else{
            Session::put('loanStatus','PENDING');
        }
        if ($status == 1){
            Session::put('disableInputs',false);
        }else{
            Session::put('disableInputs',true);
        }

        Session::put('currentloanClient',$member_number);
        Session::put('currentloanID',$id);

        $this->emit('currentloanID');
        session()->put('loan_type',$loanType);
        $this->emit('viewClientDetails');
    }


    public function deleteLoanModal($id,$member_number){
        $member_number = LoansModel::where('id',$id)->value('client_number');
        session::forget('loanAccountID');
        session::forget('currentloanClientDeleteModal');
        session::put('loanAccountID',$id);
        session::put('currentloanClientDeleteModal',$member_number);
        $this->emit('deleteLoan');

    }




}
