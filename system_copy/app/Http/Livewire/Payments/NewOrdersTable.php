<?php

namespace App\Http\Livewire\Payments;

use Livewire\Component;


use App\Models\Transactions;
use App\Models\Clients;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class NewOrdersTable extends LivewireDatatable
{

    protected $listeners = ['orderAdded' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        //dd(Session::get('unCommitedOrderNumber'));

        return Transactions::query();



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
        return [

            Column::name('typeOfTransfer')
                ->label('Type'),

            Column::raw('beneficiary_nane')
                ->label('Beneficiary Name'),

            Column::raw('source_branch_account_number')
                ->label('Debit AC'),

            Column::name('destination_branch_account_number')
                ->label('Credit AC'),

            Column::name('destination_bank_name')
                ->label('Institution'),

            Column::name('transaction_amount')
                ->label('Amount'),

            Column::name('description')
                ->label('Description')
        ];
    }


}
