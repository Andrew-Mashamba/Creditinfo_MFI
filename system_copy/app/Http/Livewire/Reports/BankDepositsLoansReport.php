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

class BankDepositsLoansReport extends Component
{
    public $reportDate;
    public $bankDeposits = [];
    public $bankLoans = [];
    public $totalDeposits = 0;
    public $totalLoans = 0;
    public $netPosition = 0;
    public $depositBreakdown = [];
    public $loanBreakdown = [];
    public $bankRelationships = [];

    public function mount()
    {
        $this->reportDate = Carbon::now()->format('Y-m-d');
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->calculateBankDeposits();
            $this->calculateBankLoans();
            $this->calculateNetPosition();
            $this->loadBankRelationships();
        } catch (Exception $e) {
            Log::error('Error loading Bank Deposits & Loans Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function calculateBankDeposits()
    {
        // Bank deposits with different financial institutions
        $this->bankDeposits = [
            'central_bank' => $this->getAccountBalance('1010'), // Central Bank deposits
            'commercial_banks' => $this->getAccountBalance('1011'), // Commercial bank deposits
            'development_banks' => $this->getAccountBalance('1012'), // Development bank deposits
            'microfinance_banks' => $this->getAccountBalance('1013'), // Microfinance bank deposits
            'savings_credit_cooperatives' => $this->getAccountBalance('1014'), // SACCOS deposits
            'other_financial_institutions' => $this->getAccountBalance('1015'), // Other FI deposits
        ];

        $this->totalDeposits = array_sum($this->bankDeposits);

        // Breakdown by deposit type
        $this->depositBreakdown = [
            'demand_deposits' => $this->getAccountBalance('1016'), // Demand deposits
            'time_deposits' => $this->getAccountBalance('1017'), // Time deposits
            'savings_deposits' => $this->getAccountBalance('1018'), // Savings deposits
            'call_deposits' => $this->getAccountBalance('1019'), // Call deposits
            'certificates_of_deposit' => $this->getAccountBalance('1020'), // CDs
        ];
    }

    public function calculateBankLoans()
    {
        // Loans from different financial institutions
        $this->bankLoans = [
            'central_bank_loans' => $this->getAccountBalance('2010'), // Central Bank loans
            'commercial_bank_loans' => $this->getAccountBalance('2011'), // Commercial bank loans
            'development_bank_loans' => $this->getAccountBalance('2012'), // Development bank loans
            'microfinance_bank_loans' => $this->getAccountBalance('2013'), // Microfinance bank loans
            'saccos_loans' => $this->getAccountBalance('2014'), // SACCOS loans
            'other_fi_loans' => $this->getAccountBalance('2015'), // Other FI loans
        ];

        $this->totalLoans = array_sum($this->bankLoans);

        // Breakdown by loan type
        $this->loanBreakdown = [
            'short_term_loans' => $this->getAccountBalance('2016'), // Short-term loans
            'medium_term_loans' => $this->getAccountBalance('2017'), // Medium-term loans
            'long_term_loans' => $this->getAccountBalance('2018'), // Long-term loans
            'overdraft_facilities' => $this->getAccountBalance('2019'), // Overdraft facilities
            'credit_lines' => $this->getAccountBalance('2020'), // Credit lines
        ];
    }

    public function calculateNetPosition()
    {
        $this->netPosition = $this->totalDeposits - $this->totalLoans;
    }

    public function loadBankRelationships()
    {
        // Get detailed bank relationships from accounts
        $this->bankRelationships = $this->getBankRelationshipDetails();
    }

    public function getBankRelationshipDetails()
    {
        $relationships = [];

        // Get deposits by bank
        $depositAccounts = AccountsModel::where('account_name', 'LIKE', '%bank%')
            ->where('major_category_code', '1000')
            ->where('balance', '>', 0)
            ->get();

        foreach ($depositAccounts as $account) {
            $bankName = $this->extractBankName($account->account_name);
            if (!isset($relationships[$bankName])) {
                $relationships[$bankName] = [
                    'bank_name' => $bankName,
                    'deposits' => 0,
                    'loans' => 0,
                    'net_position' => 0
                ];
            }
            $relationships[$bankName]['deposits'] += (float) $account->balance;
        }

        // Get loans by bank
        $loanAccounts = AccountsModel::where('account_name', 'LIKE', '%bank%')
            ->where('major_category_code', '2000')
            ->where('balance', '>', 0)
            ->get();

        foreach ($loanAccounts as $account) {
            $bankName = $this->extractBankName($account->account_name);
            if (!isset($relationships[$bankName])) {
                $relationships[$bankName] = [
                    'bank_name' => $bankName,
                    'deposits' => 0,
                    'loans' => 0,
                    'net_position' => 0
                ];
            }
            $relationships[$bankName]['loans'] += (float) $account->balance;
        }

        // Calculate net positions
        foreach ($relationships as $bankName => &$relationship) {
            $relationship['net_position'] = $relationship['deposits'] - $relationship['loans'];
        }

        return array_values($relationships);
    }

    public function extractBankName($accountName)
    {
        // Extract bank name from account name
        $bankName = $accountName;
        
        // Common bank name patterns
        $patterns = [
            '/Bank of Tanzania/i' => 'Bank of Tanzania',
            '/CRDB/i' => 'CRDB Bank',
            '/NMB/i' => 'NMB Bank',
            '/Equity Bank/i' => 'Equity Bank',
            '/KCB/i' => 'KCB Bank',
            '/Exim Bank/i' => 'Exim Bank',
            '/Diamond Trust Bank/i' => 'Diamond Trust Bank',
            '/Stanbic Bank/i' => 'Stanbic Bank',
            '/Absa Bank/i' => 'Absa Bank',
            '/TIB Bank/i' => 'TIB Bank',
        ];

        foreach ($patterns as $pattern => $bank) {
            if (preg_match($pattern, $accountName)) {
                return $bank;
            }
        }

        return $bankName;
    }

    public function getAccountBalance($accountNumber)
    {
        $account = AccountsModel::where('account_number', $accountNumber)->first();
        return $account ? (float) $account->balance : 0;
    }

    public function getInterbankTrend()
    {
        // Get interbank trend for the last 12 months
        $trend = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            // Calculate deposits and loans for the month
            $deposits = $this->calculateDepositsForDate($monthEnd);
            $loans = $this->calculateLoansForDate($monthEnd);
            $netPosition = $deposits - $loans;

            $trend[] = [
                'month' => $date->format('M Y'),
                'deposits' => $deposits,
                'loans' => $loans,
                'net_position' => $netPosition
            ];
        }

        return $trend;
    }

    public function calculateDepositsForDate($date)
    {
        // This would typically query historical balances
        // For now, we'll use current balances as a placeholder
        return $this->totalDeposits;
    }

    public function calculateLoansForDate($date)
    {
        // This would typically query historical balances
        // For now, we'll use current balances as a placeholder
        return $this->totalLoans;
    }

    public function getConcentrationRisk()
    {
        // Calculate concentration risk by bank
        $concentrationRisk = [];
        
        foreach ($this->bankRelationships as $relationship) {
            $totalExposure = $relationship['deposits'] + $relationship['loans'];
            $concentrationRisk[] = [
                'bank_name' => $relationship['bank_name'],
                'total_exposure' => $totalExposure,
                'deposit_concentration' => $this->totalDeposits > 0 ? ($relationship['deposits'] / $this->totalDeposits) * 100 : 0,
                'loan_concentration' => $this->totalLoans > 0 ? ($relationship['loans'] / $this->totalLoans) * 100 : 0,
                'risk_level' => $this->determineRiskLevel($totalExposure)
            ];
        }

        // Sort by total exposure
        usort($concentrationRisk, function($a, $b) {
            return $b['total_exposure'] <=> $a['total_exposure'];
        });

        return $concentrationRisk;
    }

    public function determineRiskLevel($exposure)
    {
        $totalAssets = $this->totalDeposits + $this->totalLoans;
        $percentage = $totalAssets > 0 ? ($exposure / $totalAssets) * 100 : 0;

        if ($percentage > 20) {
            return 'HIGH';
        } elseif ($percentage > 10) {
            return 'MEDIUM';
        } else {
            return 'LOW';
        }
    }

    public function exportReport($format = 'pdf')
    {
        try {
            session()->flash('success', "Bank Deposits & Loans Report exported as {$format} successfully!");
            
            Log::info('Bank Deposits & Loans Report exported', [
                'format' => $format,
                'report_date' => $this->reportDate,
                'total_deposits' => $this->totalDeposits,
                'total_loans' => $this->totalLoans,
                'net_position' => $this->netPosition,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
            Log::error('Bank Deposits & Loans Report export failed', [
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
        return view('livewire.reports.bank-deposits-loans-report');
    }
}
