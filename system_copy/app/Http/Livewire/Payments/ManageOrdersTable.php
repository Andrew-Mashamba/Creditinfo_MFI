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

class ManageOrdersTable extends LivewireDatatable
{

    protected $listeners = ['viewOrder' => '$refresh'];
    public $exportable = true;


    public function builder()
    {

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

            Column::name('created_at')
                ->label('action Date'),

            Column::raw('date')
                ->label('transaction Date'),
            Column::raw('reference_number')
                ->label('RNN'),

                // Column::raw('order_number')
            //     ->label('Order id'),
            Column::name('transaction_amount')
                ->label('Amount')->searchable(),
            Column::name('description')
                ->label('Description')->searchable(),
            Column::name('trans_status')
                ->label('Progress'),

            Column::name('status')
                ->label('Status')
        ];
    }


}
