<?php

namespace App\Http\Livewire\Savings;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

use App\Models\issured_shares;
use App\Models\sub_products;
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

        return sub_products::query()->where('product_id', '2000');


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

        return  [

            Column::name('sub_product_name')
                ->label('Product Name'),

            Column::name('interest_value')
                ->label('Interest Value'),

            Column::name('interest_tenure')
                ->label('Interest Tenure'),

            Column::name('notes')
                ->label('Description'),


            Column::name('sub_category_code')
                ->label('Sub Category Code'),


        ];

    }


}
