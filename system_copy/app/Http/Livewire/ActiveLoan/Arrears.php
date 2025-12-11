<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;
use App\Models\loans_schedules;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\ClientsModel;

use App\Models\BranchesModel;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Arrears extends LivewireDatatable
{

    public $exportable=true;


    function builder(){
        $query = LoansModel::query()->where('status','DELINQUENT');

        return $query;
    }


    public function columns(): array
    {
        $html ='';

        return [

            Column::callback(['client_number'], function ($member_number) {

                //return $member_number;
                return DB::table('clients')->where('client_number',$member_number)->value('first_name').' '.DB::table('clients')->where('id',$member_number)->value('middle_name').' '.DB::table('clients')->where('id',$member_number)->value('last_name');
            })->label('Member name'),


            Column::callback(['branch_id'], function ($branch_id) {

                return BranchesModel::where('id',$branch_id)->value('name');
            })->label('Branch'),

            Column::name('loan_account_number')
                ->label('loan account number'),

            Column::callback('principle',function ($principle){
                return number_format($principle,2);
            })
                ->label('principle (TZS)')->searchable(),

            Column::callback('interest',function ($interest){
                return $interest .'%';
            })
                ->label('interest'),


            Column::callback(['loan_sub_product'], function ($sub_product_id) {
                return DB::table('loan_sub_products')->where('sub_product_id',$sub_product_id)->value('sub_product_name');
            })->label('Loan Product'),


            Column::callback(['days_in_arrears'],function($days_in_arrears){
                if($days_in_arrears >0){
                    return '<div class="bg-customPurple p-2 "> '.$days_in_arrears.' </div>';
                }else{
                    return '<div class=" "> 0 </div>';
                }
            })->label('past due days')->searchable(),

            Column::callback(['arrears_in_amount'],function($days_in_arrears){
                if($days_in_arrears >0){
                    return '<div class="bg-customPurple p-2 "> '.$days_in_arrears.' </div>';
                }else{
                    return '<div class=" "> 0 </div>';
                }
            })->label('Amount in arreas')->searchable(),



            Column::callback('supervisor_id',function($supervisor){
                $employee=Employee::where('id',$supervisor)->first();
                if($employee) {
                    return $employee->first_name . ' ' . $employee->middle_name . ' ' . $employee->last_name;
                }else{
                    return null;
                }

            })->label('Loan officer'),



            Column::callback('status',function ($status){

                return view('livewire.branches.table-status', ['status' => $status, 'move' => false]);
            })->label('Status')->searchable(),

            Column::callback('id', function ($id) use ($html) {
                //$status = 1;

                $member_number = LoansModel::where('id',$id)->value('client_number');

                $status = LoansModel::where('id',$id)->value('status');

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
                            </div> ';

                return $html;

            })->label('view'),


        ];


    }


    function viewloan($id ){


        $client=DB::table('loans')->where('id',$id)->first();
        session()->put('client_number',$client->client_number);
        session()->put('loan_table_id',$id);
        $this->emit('displayLoanReport',5);
    }


}
