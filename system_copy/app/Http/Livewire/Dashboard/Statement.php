<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;

use App\Models\general_ledger;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class Statement extends LivewireDatatable
{

    protected $listeners = ['refreshTransactiosTable' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        $term = session('term', '');

        return general_ledger::query()
        ->where('record_on_account_number', 'LIKE', '%' . $term . '%');
        //->leftJoin('branches', 'branches.id', 'clients.branch')
    }

    public function viewClient($memberId){
        Session::put('memberToViewId',$memberId);
        $this->emit('refreshClientsListComponent');
    }
    public function editClient($memberId,$name){
        Session::put('memberToEditId',$memberId);
        Session::put('memberToEditName',$name);
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
                ->label('Date'),

            Column::name('narration')
                ->label('Narration'),

            Column::name('credit')
                ->label('credit'),

            Column::name('debit')
                ->label('debit'),

                Column::name('record_on_account_number_balance')
                ->label('balance'),

        ];
    }

    public function edit($id){
        $this->emitUp('editClient',$id);
        }

        public function block($id){
        $this->emitUp('blockClient',$id);
        }


}
