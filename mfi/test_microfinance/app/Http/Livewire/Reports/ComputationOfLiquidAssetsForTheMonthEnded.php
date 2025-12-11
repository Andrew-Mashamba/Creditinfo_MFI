<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ComputationOfLiquidAssetsForTheMonthEnded extends Component
{
    public $startDate;
    public $endDate;
    public $liquidAssets = [];
    public $totalCash = 0;
    public $totalBankDeposits = 0;
    public $totalInvestments = 0;
    public $totalLiquidAssets = 0;
    public $totalLiabilities = 0;
    public $liquidityRatio = 0;

    public function mount()
    {
        // $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        // $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        // Get cash balances
        $cashBalances = [];
        $majorAccounts = DB::table('accounts')
            ->where('category_code', '1000')->get();
        

        foreach ($majorAccounts as $account) {
            $cashBalance = DB::table('general_ledger')
                ->whereBetween('created_at', [$this->startDate, $this->endDate])
                ->where('sub_category_code', $account->sub_category_code)
                ->sum('debit');

            if ($account->sub_category_code == '0000') {
                continue;
            }
                
            $cashBalances[] = [
                "name" => $account->account_name,
                "balance" => $cashBalance ?? 0,
                "date" => $this->endDate
            ];
           
        }     
        // dd($cashBalances);   
                // Get liabilities
                $liabilities = [];
                $majorLiabilities = DB::table('accounts')
                    ->where('major_category_code', '2000')
                    ->get();                   
                
        
                foreach ($majorLiabilities as $account) {
                    $liabilityBalance = DB::table('general_ledger')
                        ->whereBetween('created_at', [$this->startDate, $this->endDate])
                        ->where('sub_category_code', $account->sub_category_code)
                        ->sum('credit');
                       
                    $liabilities[] = [
                        "name" => $account->account_name,
                        "balance" => $liabilityBalance ?? 0,
                        "date" => $this->endDate
                    ];
                   
                }
                // dd($liabilities);
       

        // Get investments
        $investments = [];
        $investmentsList = DB::table('investments_list')
            ->whereBetween('investment_date', [$this->startDate, $this->endDate])            
            ->get();            

            foreach ($investmentsList as $investment) {    
                $investmentBalance = DB::table('investments_list')
                    ->where('id', $investment->id)
                    ->sum('principal_amount');
                
                $investments[] = [
                    "name" => $investment->investment_type,
                    "balance" => $investmentBalance ?? 0,
                    "interest_rate" => $investment->interest_rate,
                    "maturity_date" => $investment->maturity_date,
                    "date" => $this->endDate
                ];
            }

            // dd($investments);
            

        // Combine all liquid assets
        $this->liquidAssets = [
            'cash' => $cashBalances,          
            'investments' => $investments,
            'liabilities' => $liabilities
        ];

        // Calculate totals
        $this->totalCash = collect($cashBalances)->sum('balance');
        // $this->totalBankDeposits = $bankDeposits->sum('balance');
        $this->totalInvestments = collect($investments)->sum('balance');
        $this->totalLiquidAssets = $this->totalCash + $this->totalInvestments;
        $this->totalLiabilities = collect($liabilities)->sum('balance');

        // Calculate liquidity ratio
        $this->liquidityRatio = $this->totalLiabilities > 0 
            ? ($this->totalLiquidAssets / $this->totalLiabilities) * 100 
            : 0;
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
        return view('livewire.reports.computation-of-liquid-assets-for-the-month-ended');
    }
} 