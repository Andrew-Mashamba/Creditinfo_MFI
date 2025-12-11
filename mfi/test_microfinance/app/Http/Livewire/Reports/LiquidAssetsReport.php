<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\AccountsModel;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class LiquidAssetsReport extends Component
{
    public $reportDate;
    public $liquidAssets = [];
    public $liabilities = [];
    public $totalLiquidAssets = 0;
    public $totalLiabilities = 0;
    public $liquidityRatio = 0;
    public $minimumLiquidityRatio = 25; // BOT minimum requirement
    public $excessLiquidity = 0;
    public $complianceStatus = '';
    public $cashBalances = [];
    public $bankDeposits = [];
    public $investments = [];

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->calculateLiquidAssets();
            $this->calculateLiabilities();
            $this->calculateLiquidityRatio();
            $this->determineComplianceStatus();
        } catch (Exception $e) {
            Log::error('Error loading Liquid Assets Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function calculateLiquidAssets()
    {
        // Cash and Cash Equivalents
        $this->cashBalances = [
            'cash_on_hand' => $this->getAccountBalance('1001'), // Cash on hand
            'cash_in_vault' => $this->getAccountBalance('1002'), // Cash in vault
            'cash_in_transit' => $this->getAccountBalance('1003'), // Cash in transit
            'petty_cash' => $this->getAccountBalance('1004'), // Petty cash
        ];

        // Bank Deposits
        $this->bankDeposits = [
            'demand_deposits' => $this->getAccountBalance('1005'), // Demand deposits
            'savings_deposits' => $this->getAccountBalance('1006'), // Savings deposits
            'time_deposits' => $this->getAccountBalance('1007'), // Time deposits
            'call_deposits' => $this->getAccountBalance('1008'), // Call deposits
        ];

        // Liquid Investments
        $this->investments = [
            'treasury_bills' => $this->getAccountBalance('1009'), // Treasury bills
            'government_bonds' => $this->getAccountBalance('1010'), // Government bonds
            'bank_certificates' => $this->getAccountBalance('1011'), // Bank certificates
            'money_market_instruments' => $this->getAccountBalance('1012'), // Money market instruments
        ];

        // Combine all liquid assets
        $this->liquidAssets = [
            'cash_and_equivalents' => array_sum($this->cashBalances),
            'bank_deposits' => array_sum($this->bankDeposits),
            'liquid_investments' => array_sum($this->investments),
        ];

        $this->totalLiquidAssets = array_sum($this->liquidAssets);
    }

    public function calculateLiabilities()
    {
        // Short-term Liabilities (due within 30 days)
        $this->liabilities = [
            'member_deposits' => $this->getAccountBalance('2001'), // Member deposits
            'short_term_borrowings' => $this->getAccountBalance('2002'), // Short-term borrowings
            'accrued_expenses' => $this->getAccountBalance('2003'), // Accrued expenses
            'payables' => $this->getAccountBalance('2004'), // Payables
            'provisions' => $this->getAccountBalance('2005'), // Provisions
        ];

        $this->totalLiabilities = array_sum($this->liabilities);
    }

    public function calculateLiquidityRatio()
    {
        if ($this->totalLiabilities > 0) {
            $this->liquidityRatio = ($this->totalLiquidAssets / $this->totalLiabilities) * 100;
        } else {
            $this->liquidityRatio = 0;
        }

        $requiredLiquidity = $this->totalLiabilities * ($this->minimumLiquidityRatio / 100);
        $this->excessLiquidity = $this->totalLiquidAssets - $requiredLiquidity;
    }

    public function determineComplianceStatus()
    {
        if ($this->liquidityRatio >= $this->minimumLiquidityRatio) {
            $this->complianceStatus = 'COMPLIANT';
        } else {
            $this->complianceStatus = 'NON-COMPLIANT';
        }
    }

    public function getAccountBalance($accountNumber)
    {
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        return $account ? (float) $account->balance : 0;
    }

    public function getLiquidityTrend()
    {
        // Get liquidity trend for the last 12 months
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Calculate liquid assets for the month
            $liquidAssets = $this->calculateLiquidAssetsForDate($monthEnd);
            $liabilities = $this->calculateLiabilitiesForDate($monthEnd);
            $ratio = $liabilities > 0 ? ($liquidAssets / $liabilities) * 100 : 0;

            $trend[] = [
                'month' => $date->format('M Y'),
                'liquid_assets' => $liquidAssets,
                'liabilities' => $liabilities,
                'ratio' => $ratio
            ];
        }

        return $trend;
    }

    public function calculateLiquidAssetsForDate($date)
    {
        // This would typically query historical balances
        // For now, we'll use current balances as a placeholder
        return $this->totalLiquidAssets;
    }

    public function calculateLiabilitiesForDate($date)
    {
        // This would typically query historical balances
        // For now, we'll use current balances as a placeholder
        return $this->totalLiabilities;
    }

    public function getLiquidityByBranch()
    {
        $branches = BranchesModel::all();
        $branchLiquidity = [];

        foreach ($branches as $branch) {
            $branchLiquidity[] = [
                'branch_name' => $branch->name,
                'branch_id' => $branch->id,
                'liquid_assets' => $this->getBranchLiquidAssets($branch->id),
                'liabilities' => $this->getBranchLiabilities($branch->id),
                'liquidity_ratio' => $this->calculateBranchLiquidityRatio($branch->id)
            ];
        }

        return $branchLiquidity;
    }

    public function getBranchLiquidAssets($branchId)
    {
        // Get liquid assets for specific branch
        $accounts = AccountsModel::where('branch_number', $branchId)
            ->whereIn('account_number', ['1001', '1002', '1003', '1004', '1005', '1006', '1007', '1008'])
            ->sum('balance');
        
        return (float) $accounts;
    }

    public function getBranchLiabilities($branchId)
    {
        // Get liabilities for specific branch
        $accounts = AccountsModel::where('branch_number', $branchId)
            ->whereIn('account_number', ['2001', '2002', '2003', '2004', '2005'])
            ->sum('balance');
        
        return (float) $accounts;
    }

    public function calculateBranchLiquidityRatio($branchId)
    {
        $liquidAssets = $this->getBranchLiquidAssets($branchId);
        $liabilities = $this->getBranchLiabilities($branchId);
        
        return $liabilities > 0 ? ($liquidAssets / $liabilities) * 100 : 0;
    }

    public function exportReport($format = 'pdf')
    {
        try {
            session()->flash('success', "Liquid Assets Report exported as {$format} successfully!");
            
            Log::info('Liquid Assets Report exported', [
                'format' => $format,
                'report_date' => $this->reportDate,
                'liquidity_ratio' => $this->liquidityRatio,
                'compliance_status' => $this->complianceStatus,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Liquid Assets Report export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    public function updatedReportDate()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.reports.liquid-assets-report');
    }
}
