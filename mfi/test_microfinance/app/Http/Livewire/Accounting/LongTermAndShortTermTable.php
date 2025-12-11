<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use App\Models\UnearnedDeferredRevenue;
use App\Services\CreditAndDebitService;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\Action;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\html;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;
use App\Models\loans_schedules;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\LongTermAndShortTerm ;
class LongTermAndShortTermTable extends LivewireDatatable
{

    public $exportStyles=true;
    public $exportWidths=true;

    function builder(){

     return    LongTermAndShortTerm::query();


    }

    public function columns(): array
    {
        return [
            Column::callback(['loan_type'], function ($loan_type) {
                return $loan_type;
            })->label('Loan Type')->searchable(),

            Column::callback(['source_account_id'], function ($source_account_id) {
                // Fetch the source account name
                return DB::table('accounts')->where('id', $source_account_id)->value('account_name');
            })->label('Source Account'),

            Column::callback(['amount'], function ($amount) {
                return $amount ?? 0;
            })->label('Amount'),

            Column::callback(['user_id'], function ($user_id) {
                return DB::table('users')->where('id', $user_id)->value('name') ?: null;
            })->label('Initiator')->searchable(),

            Column::callback(['organization_name'], function ($organization_name) {
                return $organization_name;
            })->label('Organization Name')->searchable(),

            Column::callback(['address'], function ($address) {
                return $address;
            })->label('Address'),

            Column::callback(['phone'], function ($phone) {
                return $phone;
            })->label('Phone'),

            Column::callback(['email'], function ($email) {
                return $email;
            })->label('Email'),

            Column::callback(['description'], function ($description) {
                return $description;
            })->label('Description')->searchable(),
            Column::callback(['application_form'], function ($application_form) {
                // Directly return the HTML string
                return $application_form && file_exists(public_path($application_form))
                    ? '<a href="' . asset($application_form) . '" target="_blank">View Form</a>'
                    : 'N/A';
            })->label('Application Form'), // Removed ->html()

            Column::callback(['contract_form'], function ($contract_form) {
                // Directly return the HTML string
                return $contract_form && file_exists(public_path($contract_form))
                    ? '<a href="' . asset($contract_form) . '" target="_blank">View Contract</a>'
                    : 'N/A';
            })->label('Contract Form'),

            Column::callback(['id'], function ($id) {
                // Directly return the HTML string
                return  '
                <div wire:click="approveAction('.$id.')" class="rounded-full bg-white p-2 w-8 cursor-pointer h-8 ">
                <svg data-slot="icon" class=" rounded-full" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                </svg>
                </div>
                ';
            })->label('action '),



        ];
    }



    function approveAction($id){

        //TODO  --- accounting 
    }
    function recognize($id){

        $data=UnearnedDeferredRevenue::find($id);
        $destination_accounts=DB::table('accounts')->where('id',$data->source_account_id)->value('account_number');
      $credit= new    CreditAndDebitService ();
      $credit->CreditAndDebit($destination_accounts,$data->amount,
              1,"Unearned / deferred revenue");
       // CreditAndDebit($source_account, $amount, $destination_accounts, $narration)

       $data->update([
        'is_recognized' =>true,
       ]);

    }

    function delivery($id){

        $data=UnearnedDeferredRevenue::find($id);
        $destination_accounts=DB::table('accounts')->where('id',$data->source_account_id)->value('account_number');
      $credit= new    CreditAndDebitService ();
      $credit->CreditAndDebit(1,$data->amount,
      $destination_accounts,"Unearned / deferred revenue");
       // CreditAndDebit($source_account, $amount, $destination_accounts, $narration)

       $data->update([
        'is_delivery' =>true,
       ]);


    }

    public function buildActions()
    {
        return [



            Action::groupBy('Export Options', function () {
                return [
                    Action::value('csv')->label('Export CSV')->export('unearnedReport.csv'),
                    Action::value('html')->label('Export HTML')->export('unearnedReport.html'),
                    Action::value('xlsx')->label('Export XLSX')->export('unearnedReport.xlsx')->styles($this->exportStyles)->widths($this->exportWidths)
                ];
            }),
        ];
    }


}
