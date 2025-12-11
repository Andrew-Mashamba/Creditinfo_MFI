<?php

namespace App\Http\Livewire\Deposits;

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
use App\Models\AccountsModel;

class NumberOfProducts extends LivewireDatatable
{

    protected $listeners = ['refreshDepositsComponent' => '$refresh'];
    public $exportable = true;


    public function builder()
    {
        $sub_category_code = DB::table('sub_products')->where('id', Session::get('depositsViewItem'))->value('product_account');

        return AccountsModel::query()->where('parent_account_number', $sub_category_code );
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

    public function render()
    {
        $depositsProducts = sub_products::where('major_category_code', 2000) // Deposits category
            ->where('status', 'ACTIVE')
            ->withCount(['accounts' => function($query) {
                $query->where('status', 'ACTIVE');
            }])
            ->orderBy('accounts_count', 'desc')
            ->get();

        $totalDepositsAccounts = AccountsModel::where('major_category_code', 2000) // Deposits category
            ->where('status', 'ACTIVE')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '0000')
            ->count();

        $totalDepositsBalance = AccountsModel::where('major_category_code', 2000) // Deposits category
            ->where('status', 'ACTIVE')
            ->whereNotNull('client_number')
            ->where('client_number', '!=', '0000')
            ->sum('balance');

        return view('livewire.deposits.number-of-products', [
            'depositsProducts' => $depositsProducts,
            'totalDepositsAccounts' => $totalDepositsAccounts,
            'totalDepositsBalance' => $totalDepositsBalance
        ]);
    }

}
