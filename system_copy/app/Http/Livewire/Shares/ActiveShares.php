<?php

namespace App\Http\Livewire\Shares;


use App\Models\ClientsModel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

use App\Models\AccountsModel;
use App\Models\sub_products;


use Illuminate\Support\Str;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\NumberColumn;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Illuminate\Support\Facades\Session;
use App\Models\search;

class ActiveShares extends LivewireDatatable
{

    protected $listeners = ['refreshShareComponent' => '$refresh'];
    public $exportable = true;
    public $sub_product_no;




    public function builder()
    {
        $sub_category_code = DB::table('sub_products')->where('id', Session::get('sharesViewItem'))->value('product_account');

        return AccountsModel::query()->where('parent_account_number', $sub_category_code )->whereNotNull('client_number');

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
                ->label('Account Number'),

            Column::callback(['product_account'], function ($product_account) {
                $sub_category_code = DB::table('accounts')->where('account_number', $product_account)->value('sub_category_code');
                return number_format(DB::table('share_registers')->where('share_product_id', $sub_category_code)->sum('shares'));
            })->label('Issued Shares'),

            Column::callback('balance', function ($value) {
                return number_format($value, 0, '', ',');
            })->label('Value (TZS)'),


        ];


    }


}

