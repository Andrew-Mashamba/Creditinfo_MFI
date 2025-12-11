<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DepositsAndLoansInBanksAndFinancialInstitutionsForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $cashBalances = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get cash balances
        $this->cashBalances = [];
        $majorAccounts = DB::table('accounts')
            ->where('category_code', '1000')->get();
        
        foreach ($majorAccounts as $account) {
            $cashBalance = DB::table('general_ledger')
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('sub_category_code', $account->sub_category_code)
                ->sum('debit');

            //skip subcategory code 0000
            if ($account->sub_category_code == '0000') {
                continue;
            }
                
            $this->cashBalances[] = [
                "name" => $account->account_name,
                "balance" => $cashBalance ?? 0,
                "date" => $this->endDate
            ];
        }  
    }

    public function updatedStartDate()
    {
        $this->loadData();
    }

    public function updatedEndDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.deposits-and-loans-in-banks-and-financial-institutions-for-the-month-ended');
    }
} 