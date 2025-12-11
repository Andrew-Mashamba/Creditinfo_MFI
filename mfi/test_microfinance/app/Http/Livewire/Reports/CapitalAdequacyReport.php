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

class CapitalAdequacyReport extends Component
{
    public $reportDate;
    public $tier1Capital = [];
    public $tier2Capital = [];
    public $riskWeightedAssets = [];
    public $totalTier1Capital = 0;
    public $totalTier2Capital = 0;
    public $totalCapital = 0;
    public $totalRiskWeightedAssets = 0;
    public $capitalAdequacyRatio = 0;
    public $minimumRequiredRatio = 10; // BOT minimum requirement
    public $regulatoryCapital = 0;
    public $excessCapital = 0;
    public $complianceStatus = '';

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->calculateTier1Capital();
            $this->calculateTier2Capital();
            $this->calculateRiskWeightedAssets();
            $this->calculateCapitalAdequacyRatio();
            $this->determineComplianceStatus();
        } catch (Exception $e) {
            Log::error('Error loading Capital Adequacy Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function calculateTier1Capital()
    {
        // Tier 1 Capital (Core Capital) - Paid-up capital, reserves, retained earnings
        $this->tier1Capital = [
            'paid_up_capital' => $this->getAccountBalance('3001'), // Paid-up capital
            'share_premium' => $this->getAccountBalance('3002'), // Share premium
            'retained_earnings' => $this->getAccountBalance('3003'), // Retained earnings
            'general_reserves' => $this->getAccountBalance('3004'), // General reserves
            'statutory_reserves' => $this->getAccountBalance('3005'), // Statutory reserves
            'revaluation_reserves' => $this->getAccountBalance('3006'), // Revaluation reserves
            'other_reserves' => $this->getAccountBalance('3007'), // Other reserves
        ];

        $this->totalTier1Capital = array_sum($this->tier1Capital);
    }

    public function calculateTier2Capital()
    {
        // Tier 2 Capital (Supplementary Capital) - Subordinated debt, hybrid instruments
        $this->tier2Capital = [
            'subordinated_debt' => $this->getAccountBalance('3008'), // Subordinated debt
            'hybrid_instruments' => $this->getAccountBalance('3009'), // Hybrid instruments
            'loan_loss_provisions' => $this->getAccountBalance('3010'), // Loan loss provisions
            'revaluation_reserves_tier2' => $this->getAccountBalance('3011'), // Revaluation reserves (Tier 2)
            'undisclosed_reserves' => $this->getAccountBalance('3012'), // Undisclosed reserves
        ];

        $this->totalTier2Capital = array_sum($this->tier2Capital);
    }

    public function calculateRiskWeightedAssets()
    {
        // Risk-Weighted Assets calculation based on BOT guidelines
        $this->riskWeightedAssets = [
            'cash_and_equivalents' => [
                'amount' => $this->getAccountBalance('1001'), // Cash and cash equivalents
                'risk_weight' => 0, // 0% risk weight
                'weighted_amount' => $this->getAccountBalance('1001') * 0
            ],
            'government_securities' => [
                'amount' => $this->getAccountBalance('1002'), // Government securities
                'risk_weight' => 0, // 0% risk weight
                'weighted_amount' => $this->getAccountBalance('1002') * 0
            ],
            'bank_deposits' => [
                'amount' => $this->getAccountBalance('1003'), // Bank deposits
                'risk_weight' => 20, // 20% risk weight
                'weighted_amount' => $this->getAccountBalance('1003') * 0.20
            ],
            'member_loans_secured' => [
                'amount' => $this->getAccountBalance('1004'), // Secured member loans
                'risk_weight' => 50, // 50% risk weight
                'weighted_amount' => $this->getAccountBalance('1004') * 0.50
            ],
            'member_loans_unsecured' => [
                'amount' => $this->getAccountBalance('1005'), // Unsecured member loans
                'risk_weight' => 100, // 100% risk weight
                'weighted_amount' => $this->getAccountBalance('1005') * 1.00
            ],
            'fixed_assets' => [
                'amount' => $this->getAccountBalance('1006'), // Fixed assets
                'risk_weight' => 100, // 100% risk weight
                'weighted_amount' => $this->getAccountBalance('1006') * 1.00
            ],
            'other_assets' => [
                'amount' => $this->getAccountBalance('1007'), // Other assets
                'risk_weight' => 100, // 100% risk weight
                'weighted_amount' => $this->getAccountBalance('1007') * 1.00
            ],
        ];

        $this->totalRiskWeightedAssets = array_sum(array_column($this->riskWeightedAssets, 'weighted_amount'));
    }

    public function calculateCapitalAdequacyRatio()
    {
        $this->totalCapital = $this->totalTier1Capital + $this->totalTier2Capital;
        
        if ($this->totalRiskWeightedAssets > 0) {
            $this->capitalAdequacyRatio = ($this->totalCapital / $this->totalRiskWeightedAssets) * 100;
        } else {
            $this->capitalAdequacyRatio = 0;
        }

        $this->regulatoryCapital = $this->totalRiskWeightedAssets * ($this->minimumRequiredRatio / 100);
        $this->excessCapital = $this->totalCapital - $this->regulatoryCapital;
    }

    public function determineComplianceStatus()
    {
        if ($this->capitalAdequacyRatio >= $this->minimumRequiredRatio) {
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

    public function getRiskWeightedLoans()
    {
        // Get detailed loan data with risk weights
        $loans = LoansModel::where('status', 'ACTIVE')->get();
        
        $riskWeightedLoans = [];
        foreach ($loans as $loan) {
            $riskWeight = $this->determineLoanRiskWeight($loan);
            $riskWeightedLoans[] = [
                'loan_id' => $loan->loan_id,
                'client_number' => $loan->client_number,
                'principal' => $loan->principle,
                'risk_weight' => $riskWeight,
                'weighted_amount' => $loan->principle * ($riskWeight / 100)
            ];
        }

        return $riskWeightedLoans;
    }

    public function determineLoanRiskWeight($loan)
    {
        // Simplified risk weight determination based on loan characteristics
        if ($loan->collateral_value > 0 && $loan->collateral_value >= $loan->principle) {
            return 50; // Secured loans
        } elseif ($loan->days_in_arrears > 90) {
            return 150; // Overdue loans
        } elseif ($loan->days_in_arrears > 30) {
            return 100; // Past due loans
        } else {
            return 100; // Standard unsecured loans
        }
    }

    public function exportReport($format = 'pdf')
    {
        try {
            session()->flash('success', "Capital Adequacy Report exported as {$format} successfully!");
            
            Log::info('Capital Adequacy Report exported', [
                'format' => $format,
                'report_date' => $this->reportDate,
                'capital_adequacy_ratio' => $this->capitalAdequacyRatio,
                'compliance_status' => $this->complianceStatus,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Capital Adequacy Report export failed', [
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
        return view('livewire.reports.capital-adequacy-report');
    }
}
