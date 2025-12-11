<?php

namespace App\Http\Livewire\Accounting;


use App\Models\AccountsModel;
use App\Models\sub_products;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class OtherData extends LivewireDatatable
{

    protected $listeners = ['refreshSavingsComponent' => '$refresh'];
    public $exportable = true;




    public function builder()
    {
        //dd(Session::get('sharesViewItem'));


        if (Session::get('savingsViewItem') == '2') {
            return AccountsModel::query()->where('sub_product_number', '101');
        }
        if (Session::get('savingsViewItem') == '3') {
            return AccountsModel::query()->where('sub_product_number', '102');
        }
        if (Session::get('savingsViewItem') == '4') {
            return AccountsModel::query()->where('sub_product_number', '103');
        }
        if (Session::get('savingsViewItem') == '5') {
            return AccountsModel::query()->where('sub_product_number', '104');
        }
        if (Session::get('savingsViewItem') == '6') {
            return AccountsModel::query()->where('sub_product_number', '105');
        }
        if (Session::get('savingsViewItem') == '8') {
            return AccountsModel::query();
        }
        return AccountsModel::query();


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
