<?php

namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\LoansModel;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class LoanArrears_m extends LivewireDatatable
{
    public function builder()
    {
            return LoansModel::query()->where('status', 'ACTIVE')->where('heath', 'BAD');
    }



    public function columns(): array
    {

//        Session::put('currentloanClient',null);
//        Session::put('currentloanClient',null);
//        Session::put('currentloanID',null);

        $html = null;


        return [

            Column::name('id')
                ->label('ID'),


            Column::callback(['client_id'], function ($member_number) {

                return ClientsModel::where('id', $member_number)->value('first_name') . ' ' . ClientsModel::where('id', $member_number)->value('middle_name') . ' ' . ClientsModel::where('id', $member_number)->value('last_name');
            })->label('Client name'),

            Column::callback(['guarantor'], function ($guarantor) {

                return ClientsModel::where('client_number', $guarantor)->value('first_name') . ' ' . ClientsModel::where('client_number', $guarantor)->value('middle_name') . ' ' . ClientsModel::where('client_number', $guarantor)->value('last_name');
            })->label('Guarantor'),

            Column::name('loan_id')
                ->label('loan id'),

            Column::name('loan_account_number')
                ->label('loan account number'),
        ];
            }


}
