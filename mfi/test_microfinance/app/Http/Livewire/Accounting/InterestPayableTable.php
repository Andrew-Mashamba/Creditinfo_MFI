<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Mediconesystems\LivewireDatatables\BooleanColumn;
use Mediconesystems\LivewireDatatables\html;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;
use App\Models\loans_schedules;
use App\Models\issured_shares;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use Mediconesystems\LivewireDatatables\Column;

use App\Models\Employee;
use App\Models\InterestPayable;
use Illuminate\Support\Facades\DB;


class InterestPayableTable extends LivewireDatatable
{

    public $exportStyles=true;
    public $exportWidths=true;

    function builder(){

        return   InterestPayable ::query();


       }

    public function columns(): array
    {

    return [
        Column::index($this),

        Column::callback(['interest_payable'], function ($loan_type) {
            return $loan_type;
        })->label('interest payable')->searchable(),

        Column::callback(['accrued_interest'], function ($loan_type) {
            return $loan_type;
        })->label('accrued interest')->searchable(),


        Column::callback(['payment_frequency'], function ($loan_type) {
            return $loan_type;
        })->label('payment frequency')->searchable(),

        Column::callback(['maturity_date'], function ($loan_type) {
            return $loan_type;
        })->label('maturity date')->searchable(),

        Column::callback(['deposit_date'], function ($loan_type) {
            return $loan_type;
        })->label('deposit date')->searchable(),
        Column::callback(['interest_rate'], function ($loan_type) {
            return $loan_type.'%';
        })->label('interest rate')->searchable(),


        Column::callback(['amount'], function ($loan_type) {
            return $loan_type;
        })->label('amount')->searchable(),

        Column::callback(['account_type'], function ($loan_type) {
            return $loan_type;
        })->label('account type')->searchable(),



        Column::callback(['member_id'], function ($loan_type) {
            return $loan_type;
        })->label('member id')->searchable(),


        Column::callback(['loan_provider'], function ($loan_type) {
            return $loan_type;
        })->label('loan provider')->searchable(),


        Column::callback(['loan_interest_rate'], function ($loan_type) {
            return $loan_type;
        })->label('loan interest rate')->searchable(),

        Column::callback(['interest_payable_loan'], function ($loan_type) {
            return $loan_type;
        })->label('interest payable loan')->searchable(),
        Column::callback(['accrued_interest_loan'], function ($loan_type) {
            return $loan_type;
        })->label('accrued interest loan')->searchable(),


        Column::callback(['interest_payment_schedule'], function ($loan_type) {
            return $loan_type;
        })->label('interest payment schedule')->searchable(),


        Column::callback(['loan_start_date'], function ($loan_type) {
            return $loan_type;
        })->label('loan start date')->searchable(),

        Column::callback(['loan_term'], function ($loan_type) {
            return $loan_type;
        })->label('loan term')->searchable(),

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


    
}

}
