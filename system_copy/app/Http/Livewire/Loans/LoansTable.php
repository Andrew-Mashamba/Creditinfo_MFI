<?php

namespace App\Http\Livewire\Loans;


use App\Models\loans_schedules;
use Carbon\Carbon;
use Livewire\Component;


use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;
use Illuminate\Support\Facades\Log;


class LoansTable extends LivewireDatatable
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
        $userDepartment = auth()->user()->department;
        $isManagement = DB::table('departments')->where('id', $userDepartment)->value('department_name') === "HEAD OF CREDIT";

        $loanStage = Session::get('LoanStage');

      //  dd(session('stage_name'));

        $query = LoansModel::query();

        Log::info(session('stage_name') );
        $query->where('stage_id',session('stage_name'));

        switch(session('tabId')){
            case 2: $query->where('loan_type_2',"New")->whereNull('loan_type_3'); break ;

            case 3: $query->where('loan_type_2',"Top Up")->whereNull('loan_type_3'); break ;

            case 4: $query->where('loan_type_2',"Restructuring")->whereNull('loan_type_3'); break ;

            case 5: $query->where('loan_type_3',"Exception"); break ;

        }



        // if ($isManagement) {
        //     $query->where('status', $this->getManagementStatus($loanStage));
        // } else {
        //     $query->where('branch_id', auth()->user()->branch)
        //         ->where('supervisor_id', auth()->user()->id)
        //         ->where('status', $this->getLoanOfficerStatus($loanStage));
        // }

        //dd($query);
        return $query;
    }

    private function getManagementStatus($loanStage)
    {
        return $loanStage;
    }

    private function getLoanOfficerStatus($loanStage)
    {
        return $this->getManagementStatus($loanStage); // Reuse logic
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
        $html ='';

            return [

                Column::callback(['client_number'], function ($member_number) {

                    //return $member_number;
                    return DB::table('clients')->where('client_number',$member_number)->value('first_name').' '.DB::table('clients')->where('id',$member_number)->value('middle_name').' '.DB::table('clients')->where('id',$member_number)->value('last_name');
                })->label('Member name'),

                // Column::callback(['guarantor'], function ($guarantor) {

                //     return DB::table('clients')->where('member_number',$guarantor)->value('first_name').' '.DB::table('clients')->where('member_number',$guarantor)->value('middle_name').' '.DB::table('clients')->where('member_number',$guarantor)->value('last_name');
                // })->label('Guarantor'),
                Column::callback(['branch_id'], function ($branch_id) {

                    return BranchesModel::where('id',$branch_id)->value('name');
                })->label('Branch'),

                // Column::name('loan_id')
                //     ->label('loan id'),

                // Column::name('loan_account_number')
                //     ->label('loan account number'),

                // Column::callback(['days_in_arrears'],function($days_in_arrears){
                //     if($days_in_arrears >0){
                //         return '<div class="bg-customPurple p-2 "> '.$days_in_arrears.' </div>';
                //     }else{
                //         return '<div class=" "> 0 </div>';
                //     }
                // })->label('past due days')->searchable(),


                Column::callback('principle',function ($principle){
                    return number_format($principle,2);
                })
                    ->label('principle (TZS)')->searchable(),

                Column::callback('interest',function ($interest){
                    return $interest .'%';
                })
                    ->label('interest'),

                // Column::name('heath')
                //     ->label('health'),

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

                    return $status;
                    //view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
                })->label('Status')->searchable(),

                Column::callback('id', function ($id) use ($html) {
                    //$status = 1;

                    $member_number = LoansModel::where('id',$id)->value('client_number');

                  //  $status = LoansModel::where('id',$id)->value('status');


                    // if($status == 'ONPROGRESS') {
                    //     $status = 1;

                    // }
                    // elseif ($status == 'BRANCH COMMITTEE'){
                    //     $status = 2;
                    // }
                    // elseif ($status == 'CREDIT ANALYST'){
                    //     $status = 3;
                    // }
                    // elseif ($status == 'HEAD OF CREDIT'){
                    //     $status = 4;
                    // }
                    // elseif ($status == 'HQ COMMITTEE'){
                    //     $status = 5;
                    // }
                    // elseif ($status == 'CREDIT ADMINISTRATION'){
                    //     $status = 6;
                    // }
                    // elseif ($status == 'REJECTED'){
                    //     $status = 7;
                    // }
                    // elseif ($status == 'RECOVERY'){
                    //     $status = 8;
                    // }elseif($status=="AWAITING DISBURSEMENT"){
                    //     $status = 9;
                    // }elseif($status=="CLOSED"){
                    //     $status=10;
                    // }



                    if(session()->get('loanStageId')==2){
//                        if(session()->get('sortByBranch') == DB::table('branches')->where('name','HQ')->value('id')) {


                            $html2 = null;
                            if (in_array("Create, edit, and delete loan accounts", session()->get('permission_items'))) {

                                $html2 = '
                         <button wire:click="deleteLoanModal(' . $id . ',' . $member_number . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
                            bg-white rounded-md border border-red-300 hover:bg-red-100 focus:ring-4   text-red-500 hover:text-red-700 hover:bg-red-100
                            focus:outline-none focus:ring-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-600
                            dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700 inline-flex items-center
                            dark:placeholder-gray-400 dark:text-red dark:focus:ring-blue-500 dark:focus:border-blue-500">
                               <svg class="w-5 h-5 text-red-500 hover:text-red-700 hover:bg-red-100 focus:ring-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="hidden text-customPurple m-2">Delete</span>

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


                          <div class="flex items-center space-x-4 flex-lg-row">
                            <button wire:click="viewloan(' . $id . ')" class="hoverable m-2 py-2 px-4 text-sm font-medium text-center text-gray-900
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





    public function viewloan($id){

        $member_number = LoansModel::where('id',$id)->value('client_number');
        $loanType = LoansModel::where('id',$id)->value('loan_type');
//

        Session::forget('currentloanClient');
        Session::forget('currentloanID');

        // if($status == 1){
        //     Session::put('loanStatus','ONPROGRESS');
        // }elseif ($status == 2){
        //     Session::put('loanStatus','BRANCH COMMITTEE');
        // }
        // elseif ($status == 9){
        //     Session::put('loanStatus','CREDIT ANALYST');
        // }elseif($status ==10){
        //     Session::put('loanStatus','HEAD OF CREDIT');
        // }
        // elseif ($status == 3){
        //     Session::put('loanStatus','HQ COMMITTEE');
        // }
        // elseif ($status == 4){
        //     Session::put('loanStatus','CREDIT ADMINISTRATION');
        // }
        // elseif ($status == 5){
        //     Session::put('loanStatus','HQ COMMITTEE');
        // }
        // elseif ($status == 6){
        //     Session::put('loanStatus','CREDIT ADMINISTRATION');
        // }
        // elseif ($status == 7){
        //     Session::put('loanStatus','REJECTED');
        // }
        // elseif ($status == 8){
        //     Session::put('loanStatus','RECOVERY');
        // }else{
        //     Session::put('loanStatus','PENDING');
        // }
        // if ($status == 1){
        //     Session::put('disableInputs',false);
        // }

        // else{
        //     Session::put('disableInputs',true);
        // }

        Session::put('currentloanClient',$member_number);
        Session::put('currentloanID',$id);

        $this->emit('refreshClientInfoPage');




        $this->emit('currentloanID');
        session()->put('loan_type',$loanType);
        $this->emit('viewClientDetails');
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
