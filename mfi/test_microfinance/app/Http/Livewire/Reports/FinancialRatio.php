<?php

namespace App\Http\Livewire\Reports;

use App\Models\AccountsModel;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class FinancialRatio extends Component
{

    public $current_ratio;
    public $quick_ratio;

    public $net_profit_margin;
    public $dept_to_equity_ratio;
    protected $model;
    public $return_of_assets;
    public $return_of_equity;
    public $gross_profit_margin;

function returnOfAssets(){
    $net_income = DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('credit') - DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('debit');
    
    $total_assets = DB::table('general_ledger')
        ->where('major_category_code', 1000)
        ->sum('debit') - DB::table('general_ledger')
        ->where('major_category_code', 1000)
        ->sum('credit');
    
    $return_of_assets = $total_assets != 0 ? ($net_income / $total_assets) : 0;
    return $return_of_assets;
}

function returnOfEquity(){
    $net_income = DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('credit') - DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('debit');
    
    $total_equity = DB::table('general_ledger')
        ->where('major_category_code', 3000)
        ->sum('credit') - DB::table('general_ledger')
        ->where('major_category_code', 3000)
        ->sum('debit');
    
    $return_of_equity = $total_equity != 0 ? ($net_income / $total_equity) : 0;
    return $return_of_equity;
}

function grossProfitIncome(){
    $gross_profit = DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('credit') - DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('debit');
    
    $total_revenue = DB::table('general_ledger')
        ->where('major_category_code', 4000)
        ->sum('credit');
    
    $gross_profit_income = $total_revenue != 0 ? ($gross_profit / $total_revenue) : 0;
    return $gross_profit_income;
}


    public function currentRatio(){
        $total_assets = DB::table('general_ledger')
            ->where('major_category_code', 1000)
            ->sum('debit') - DB::table('general_ledger')
            ->where('major_category_code', 1000)
            ->sum('credit');
        
        $total_liabilities = DB::table('general_ledger')
            ->where('major_category_code', 2000)
            ->sum('credit') - DB::table('general_ledger')
            ->where('major_category_code', 2000)
            ->sum('debit');
        
        $ratio = $total_liabilities != 0 ? ($total_assets / $total_liabilities) : 0;
        return $ratio;
    }

    function quickRatio(){
        $total_assets = DB::table('general_ledger')
            ->where('major_category_code', 1000)
            ->sum('debit') - DB::table('general_ledger')
            ->where('major_category_code', 1000)
            ->sum('credit');
        
        $total_liabilities = DB::table('general_ledger')
            ->where('major_category_code', 2000)
            ->sum('credit') - DB::table('general_ledger')
            ->where('major_category_code', 2000)
            ->sum('debit');
        
        $quick_ratio = $total_liabilities != 0 ? ($total_assets / $total_liabilities) : 0;
        return $quick_ratio;
    }

    function netProfitMargin(){
        $total_revenue = DB::table('general_ledger')
            ->where('major_category_code', 4000)
            ->sum('credit');
        
        $total_expenses = DB::table('general_ledger')
            ->where('major_category_code', 5000)
            ->sum('debit') - DB::table('general_ledger')
            ->where('major_category_code', 5000)
            ->sum('credit');
        
        $net_profit = $total_revenue - $total_expenses;
        $net_profit_margin = $total_revenue != 0 ? ($net_profit / $total_revenue) : 0;
        return $net_profit_margin;
    }

    public function deptToEquityRatio(){
        $total_debt = DB::table('general_ledger')
            ->where('category_code', 2300)
            ->sum('credit') - DB::table('general_ledger')
            ->where('category_code', 2300)
            ->sum('debit');
        
        $total_equity = DB::table('general_ledger')
            ->where('major_category_code', 3000)
            ->sum('credit') - DB::table('general_ledger')
            ->where('major_category_code', 3000)
            ->sum('debit');
        
        $dept_to_equity_ratio = $total_equity != 0 ? ($total_debt / $total_equity) : 0;
        return $dept_to_equity_ratio;
    }
    public function render()
    {
        $this->model=AccountsModel::query();
        //get current ratio
        $this->current_ratio= $this->currentRatio();

        // get quick ratio
        $this->quick_ratio=$this->quickRatio();

        //get net profit margin
        $this->net_profit_margin=$this->netProfitMargin();

        //get dept to equity ratio
        $this->dept_to_equity_ratio=$this->deptToEquityRatio();

        //get return of the assets
        $this->return_of_assets=$this->returnOfAssets();

        //get return of equity (ROE)
        $this->return_of_equity=$this->returnOfEquity();
         // get gross profit income
        $this->gross_profit_margin =$this->grossProfitIncome();

        return view('livewire.reports.financial-ratio');
    }
}
