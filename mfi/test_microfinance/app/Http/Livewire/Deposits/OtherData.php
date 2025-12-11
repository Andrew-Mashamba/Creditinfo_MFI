<?php

namespace App\Http\Livewire\Deposits;

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
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\SubProducts;
use Carbon\Carbon;

class OtherData extends LivewireDatatable
{

    protected $listeners = ['refreshDepositsComponent' => '$refresh'];
    public $exportable = true;

    public function builder()
    {


        $sub_category_code= DB::table('sub_products')->where('id', Session::get('depositsViewItem'))->value('product_account');

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

        return [


            Column::name('account_name')
                ->label('Account Name'),

            Column::name('account_number')
                ->label('Account Number'),

            Column::callback('balance', function ($value) {
                return number_format($value, 0, '', ',');
            })->label('Balance'),

            Column::name('status')
                ->label('Account Status')

        ];

    }

    public function render()
    {
        $sub_category_code = DB::table('sub_products')->where('id', Session::get('depositsViewItem'))->value('product_account');

        $accounts = AccountsModel::whereNotNull('client_number')
            ->where('major_category_code', 2000) // Deposits category
            ->where('client_number', '!=', '0000')
            ->where('status', 'ACTIVE')
            ->orderBy('balance', 'desc')
            ->take(10)
            ->get();

        $recentTransactions = general_ledger::with(['account.client'])
            ->whereHas('account', function ($query) {
                $query->where('major_category_code', 2000); // Deposits category
            })
            ->latest()
            ->take(5)
            ->get();

        $monthlyDeposits = general_ledger::whereHas('account', function ($query) {
                $query->where('major_category_code', 2000); // Deposits category
            })
            ->whereYear('created_at', Carbon::now()->year)
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, SUM(credit) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $topDepositors = AccountsModel::with('client')
            ->whereNotNull('client_number')
            ->where('major_category_code', 2000) // Deposits category
            ->where('status', 'ACTIVE')
            ->where('client_number', '!=', '0000')
            ->orderBy('balance', 'desc')
            ->take(5)
            ->get();

        $depositsByProduct = DB::table('accounts')
            ->join('sub_products', 'accounts.product_number', '=', 'sub_products.sub_product_id')
            ->where('accounts.major_category_code', 2000) // Deposits category
            ->select('sub_products.product_name', DB::raw('SUM(accounts.balance) as total_balance'))
            ->groupBy('sub_products.product_name')
            ->get();

        return view('livewire.deposits.other-data', [
            'accounts' => $accounts,
            'recentTransactions' => $recentTransactions,
            'monthlyDeposits' => $monthlyDeposits,
            'topDepositors' => $topDepositors,
            'depositsByProduct' => $depositsByProduct
        ]);
    }

}
