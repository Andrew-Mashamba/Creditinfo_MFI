<?php

namespace App\Http\Livewire\Shares;


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

    protected $listeners = ['refreshShareComponent' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        //dd(Session::get('sharesViewItem'));

        return sub_products::query()->where('category_code', '3000');


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

            Column::name('notes')
                ->label('Description'),

            Column::callback('nominal_price', function ($value) {
                return number_format($value, 0, '', ',');
            })->label('Price Per Share'),

            Column::callback(['product_account','notes'], function ($product_account,$available_shares) {
                $sub_category_code = DB::table('accounts')->where('account_number', $product_account)->value('sub_category_code');
                return number_format(DB::table('share_registers')->where('share_product_id', $sub_category_code)->sum('shares'));
            })->label('Issued Shares'),

            Column::callback(['product_account','status'], function ($product_account) {
                $sub_category_code = DB::table('accounts')->where('account_number', $product_account)->value('sub_category_code');
                return number_format(DB::table('accounts')->where('sub_category_code', $sub_category_code)->whereNotNull('client_number')->count());

            })->label('Number of accounts'),

            Column::callback(['product_account'], function ($product_account) {
                $sub_category_code = DB::table('accounts')->where('account_number', $product_account)->value('sub_category_code');
                return number_format(DB::table('accounts')->where('sub_category_code', $sub_category_code)->whereNotNull('client_number')->sum('balance'));

            })->label('Value (TZS)'),

        ];


    }


}
