<?php

namespace App\Http\Livewire\Accounting;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

use App\Models\issured_shares;
use App\Models\AccountsModel;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class NumberOfProducts extends LivewireDatatable
{

    protected $listeners = ['refreshSavingsComponent' => '$refresh'];
    public $exportable = true;




    public function builder()
    {
        //dd(Session::get('sharesViewItem'));

        return AccountsModel::query()->where('product_number', '10');


    }

    public function viewMember($memberId)
    {
        Session::put('memberToViewId', $memberId);
        $this->emit('refreshMembersListComponent');
    }

    public function editMember($memberId, $name)
    {
        Session::put('memberToEditId', $memberId);
        Session::put('memberToEditName', $name);
        $this->emit('refreshMembersListComponent');
    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {


        return [

            Column::name('account_name')
                ->label('Account Name'),

            Column::name('account_number')
                ->label('Account number'),

            Column::name('balance')
                ->label('Current Balance'),

            Column::name('notes')
                ->label('Notes'),

            Column::name('status')
                ->label('Status')

        ];
    }


}
