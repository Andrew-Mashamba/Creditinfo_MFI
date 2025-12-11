<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;

class StatementOfCashFlows extends Component
{
    // Period selection
    public $selectedYear;
    public $comparisonYears = [];
    public $companyName = 'NBC SACCOS LTD';
    public $reportingDate;
    
    // Display options
    public $method = 'indirect'; // indirect or direct
    public $showDetailed = false;
    public $expandedSections = [];
    
    // Cash flow data
    public $operatingActivities = [];
    public $investingActivities = [];
    public $financingActivities = [];
    public $cashFlowSummary = [];
    
    // Configuration
    public $accountMappings = [];
    public $showConfiguration = false;
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->reportingDate = Carbon::create($this->selectedYear, 12, 31)->format('d F Y');
        
        $this->loadCashFlowData();
    }
    
    public function updatedSelectedYear()
    {
        $this->comparisonYears = [
            $this->selectedYear,
            $this->selectedYear - 1
        ];
        $this->reportingDate = Carbon::create($this->selectedYear, 12, 31)->format('d F Y');
        $this->loadCashFlowData();
    }
    
    public function toggleMethod()
    {
        $this->method = $this->method === 'indirect' ? 'direct' : 'indirect';
        $this->loadCashFlowData();
    }
    
    public function toggleDetailed()
    {
        $this->showDetailed = !$this->showDetailed;
    }
    
    public function toggleSection($section)
    {
        if (in_array($section, $this->expandedSections)) {
            $key = array_search($section, $this->expandedSections);
            unset($this->expandedSections[$key]);
            $this->expandedSections = array_values($this->expandedSections);
        } else {
            $this->expandedSections[] = $section;
        }
    }
    
    private function loadCashFlowData()
    {
        foreach ($this->comparisonYears as $year) {
            if ($this->method === 'indirect') {
                $this->loadIndirectMethod($year);
            } else {
                $this->loadDirectMethod($year);
            }
        }
        
        $this->calculateCashFlowSummary();
    }
    
    private function loadIndirectMethod($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Operating Activities - Indirect Method
        $this->operatingActivities[$year] = [
            'net_income' => $this->calculateNetIncome($year),
            'adjustments' => [],
            'working_capital_changes' => [],
            'total' => 0
        ];
        
        // Add back non-cash expenses
        $this->operatingActivities[$year]['adjustments'] = [
            'depreciation' => $this->getDepreciationExpense($year),
            'amortization' => $this->getAmortizationExpense($year),
            'provisions' => $this->getProvisionExpense($year),
            'impairment' => $this->getImpairmentExpense($year),
            'loss_on_disposal' => $this->getLossOnDisposal($year),
            'gain_on_disposal' => -$this->getGainOnDisposal($year), // Subtract gains
            'interest_expense' => $this->getInterestExpense($year),
            'interest_income' => -$this->getInterestIncome($year), // Subtract income
            'dividend_income' => -$this->getDividendIncome($year),
        ];
        
        // Working capital changes
        $this->operatingActivities[$year]['working_capital_changes'] = [
            'receivables' => -$this->getChangeInReceivables($year),
            'inventories' => -$this->getChangeInInventories($year),
            'prepayments' => -$this->getChangeInPrepayments($year),
            'payables' => $this->getChangeInPayables($year),
            'accruals' => $this->getChangeInAccruals($year),
            'provisions_used' => -$this->getProvisionsUsed($year),
        ];
        
        // Interest and dividends received/paid
        $this->operatingActivities[$year]['cash_flows'] = [
            'interest_received' => $this->getInterestReceived($year),
            'interest_paid' => -$this->getInterestPaid($year),
            'dividends_received' => $this->getDividendsReceived($year),
            'income_tax_paid' => -$this->getIncomeTaxPaid($year),
        ];
        
        // Calculate total operating cash flow
        $this->operatingActivities[$year]['total'] = 
            $this->operatingActivities[$year]['net_income'] +
            array_sum($this->operatingActivities[$year]['adjustments']) +
            array_sum($this->operatingActivities[$year]['working_capital_changes']) +
            array_sum($this->operatingActivities[$year]['cash_flows']);
        
        // Investing Activities
        $this->investingActivities[$year] = [
            'ppe_purchases' => -$this->getPPEPurchases($year),
            'ppe_disposals' => $this->getPPEDisposals($year),
            'intangible_purchases' => -$this->getIntangiblePurchases($year),
            'intangible_disposals' => $this->getIntangibleDisposals($year),
            'investment_purchases' => -$this->getInvestmentPurchases($year),
            'investment_disposals' => $this->getInvestmentDisposals($year),
            'loans_granted' => -$this->getLoansGranted($year),
            'loans_repaid' => $this->getLoansRepaid($year),
            'total' => 0
        ];
        
        $this->investingActivities[$year]['total'] = array_sum(array_filter($this->investingActivities[$year], function($key) {
            return $key !== 'total';
        }, ARRAY_FILTER_USE_KEY));
        
        // Financing Activities
        $this->financingActivities[$year] = [
            'share_capital_issued' => $this->getShareCapitalIssued($year),
            'share_capital_repurchased' => -$this->getShareCapitalRepurchased($year),
            'borrowings_received' => $this->getBorrowingsReceived($year),
            'borrowings_repaid' => -$this->getBorrowingsRepaid($year),
            'lease_payments' => -$this->getLeasePayments($year),
            'dividends_paid' => -$this->getDividendsPaid($year),
            'total' => 0
        ];
        
        $this->financingActivities[$year]['total'] = array_sum(array_filter($this->financingActivities[$year], function($key) {
            return $key !== 'total';
        }, ARRAY_FILTER_USE_KEY));
    }
    
    private function loadDirectMethod($year)
    {
        // Operating Activities - Direct Method
        $this->operatingActivities[$year] = [
            'cash_receipts' => [
                'from_customers' => $this->getCashFromCustomers($year),
                'interest_received' => $this->getInterestReceived($year),
                'dividends_received' => $this->getDividendsReceived($year),
                'other_receipts' => $this->getOtherOperatingReceipts($year),
            ],
            'cash_payments' => [
                'to_suppliers' => -$this->getCashToSuppliers($year),
                'to_employees' => -$this->getCashToEmployees($year),
                'interest_paid' => -$this->getInterestPaid($year),
                'income_tax_paid' => -$this->getIncomeTaxPaid($year),
                'other_payments' => -$this->getOtherOperatingPayments($year),
            ],
            'total' => 0
        ];
        
        $this->operatingActivities[$year]['total'] = 
            array_sum($this->operatingActivities[$year]['cash_receipts']) +
            array_sum($this->operatingActivities[$year]['cash_payments']);
        
        // Investing and Financing activities remain the same
        $this->loadIndirectMethod($year); // Reuse investing and financing from indirect
    }
    
    private function calculateCashFlowSummary()
    {
        foreach ($this->comparisonYears as $year) {
            $openingCash = $this->getOpeningCashBalance($year);
            $operatingCF = $this->operatingActivities[$year]['total'] ?? 0;
            $investingCF = $this->investingActivities[$year]['total'] ?? 0;
            $financingCF = $this->financingActivities[$year]['total'] ?? 0;
            
            $this->cashFlowSummary[$year] = [
                'opening_balance' => $openingCash,
                'operating_activities' => $operatingCF,
                'investing_activities' => $investingCF,
                'financing_activities' => $financingCF,
                'net_increase' => $operatingCF + $investingCF + $financingCF,
                'fx_effects' => $this->getFXEffects($year),
                'closing_balance' => $openingCash + $operatingCF + $investingCF + $financingCF + $this->getFXEffects($year)
            ];
        }
    }
    
    // Helper methods to get specific cash flow items
    private function calculateNetIncome($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Income
        $income = DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '4000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.credit AS DECIMAL(20,2))) - SUM(CAST(gl.debit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
        
        // Expenses
        $expenses = DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.major_category_code', '5000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
        
        return $income - $expenses;
    }
    
    private function getDepreciationExpense($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%depreciation%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getAmortizationExpense($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%amortization%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getProvisionExpense($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%provision%')
            ->where('a.major_category_code', '5000')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getImpairmentExpense($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%impairment%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getLossOnDisposal($year)
    {
        // Placeholder - implement based on your disposal tracking
        return 0;
    }
    
    private function getGainOnDisposal($year)
    {
        // Placeholder - implement based on your disposal tracking
        return 0;
    }
    
    private function getInterestExpense($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%interest%expense%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getInterestIncome($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%interest%income%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->select(DB::raw('SUM(CAST(gl.credit AS DECIMAL(20,2))) - SUM(CAST(gl.debit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getDividendIncome($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getChangeInReceivables($year)
    {
        $currentYear = $this->getAccountBalanceAtDate('receivables', "$year-12-31");
        $previousYear = $this->getAccountBalanceAtDate('receivables', ($year - 1) . "-12-31");
        return $currentYear - $previousYear;
    }
    
    private function getChangeInInventories($year)
    {
        $currentYear = $this->getAccountBalanceAtDate('inventory', "$year-12-31");
        $previousYear = $this->getAccountBalanceAtDate('inventory', ($year - 1) . "-12-31");
        return $currentYear - $previousYear;
    }
    
    private function getChangeInPrepayments($year)
    {
        $currentYear = $this->getAccountBalanceAtDate('prepayment', "$year-12-31");
        $previousYear = $this->getAccountBalanceAtDate('prepayment', ($year - 1) . "-12-31");
        return $currentYear - $previousYear;
    }
    
    private function getChangeInPayables($year)
    {
        $currentYear = $this->getAccountBalanceAtDate('payable', "$year-12-31");
        $previousYear = $this->getAccountBalanceAtDate('payable', ($year - 1) . "-12-31");
        return $currentYear - $previousYear;
    }
    
    private function getChangeInAccruals($year)
    {
        $currentYear = $this->getAccountBalanceAtDate('accrual', "$year-12-31");
        $previousYear = $this->getAccountBalanceAtDate('accrual', ($year - 1) . "-12-31");
        return $currentYear - $previousYear;
    }
    
    private function getProvisionsUsed($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getInterestReceived($year)
    {
        // Cash basis interest received
        return 0;
    }
    
    private function getInterestPaid($year)
    {
        // Cash basis interest paid
        return 0;
    }
    
    private function getDividendsReceived($year)
    {
        // Cash basis dividends received
        return 0;
    }
    
    private function getIncomeTaxPaid($year)
    {
        // Tax payments made during the year
        return 0;
    }
    
    private function getPPEPurchases($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->whereIn('a.account_name', ['Property, Plant and Equipment', 'Fixed Assets', 'PPE'])
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->where('gl.debit', '>', 0)
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getPPEDisposals($year)
    {
        // Placeholder - implement based on disposal tracking
        return 0;
    }
    
    private function getIntangiblePurchases($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getIntangibleDisposals($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getInvestmentPurchases($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getInvestmentDisposals($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getLoansGranted($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        // Get all active loan account numbers created within the year
        $activeLoanAccounts = DB::table('loans')
            ->where('status', 'ACTIVE')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->pluck('loan_account_number');
        
        // If no active loans, return 0
        if ($activeLoanAccounts->isEmpty()) {
            return 0;
        }
        
        // Sum the balances from accounts table for these loan accounts in a single query
        $totalLoanBalance = DB::table('accounts')
            ->whereIn('account_number', $activeLoanAccounts)
            ->sum('balance') ?? 0;
        
        return $totalLoanBalance;
    }
    
    private function getLoansRepaid($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('loan_repayments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount') ?? 0;
    }
    
    private function getShareCapitalIssued($year)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        $endDate = Carbon::createFromFormat('Y-m-d', "$year-12-31")->endOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', '%share%capital%')
            ->whereBetween('gl.created_at', [$startDate, $endDate])
            ->where('gl.credit', '>', 0)
            ->select(DB::raw('SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getShareCapitalRepurchased($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getBorrowingsReceived($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getBorrowingsRepaid($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getLeasePayments($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getDividendsPaid($year)
    {
        // Placeholder
        return 0;
    }
    
    private function getCashFromCustomers($year)
    {
        // Direct method - cash received from customers
        return 0;
    }
    
    private function getCashToSuppliers($year)
    {
        // Direct method - cash paid to suppliers
        return 0;
    }
    
    private function getCashToEmployees($year)
    {
        // Direct method - cash paid to employees
        return 0;
    }
    
    private function getOtherOperatingReceipts($year)
    {
        // Direct method - other operating receipts
        return 0;
    }
    
    private function getOtherOperatingPayments($year)
    {
        // Direct method - other operating payments
        return 0;
    }
    
    private function getOpeningCashBalance($year)
    {
        $date = Carbon::createFromFormat('Y-m-d', "$year-01-01")->startOfYear();
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->whereIn('a.account_name', ['Cash', 'Bank', 'Cash and Bank', 'Cash at Bank', 'Cash in Hand'])
            ->where('gl.created_at', '<', $date)
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    private function getFXEffects($year)
    {
        // Foreign exchange effects on cash
        return 0;
    }
    
    private function getAccountBalanceAtDate($accountType, $date)
    {
        $dateObj = Carbon::parse($date);
        
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->where('a.account_name', 'LIKE', "%$accountType%")
            ->where('gl.created_at', '<=', $dateObj)
            ->select(DB::raw('SUM(CAST(gl.debit AS DECIMAL(20,2))) - SUM(CAST(gl.credit AS DECIMAL(20,2))) as total'))
            ->value('total') ?? 0;
    }
    
    public function formatNumber($number)
    {
        if ($number < 0) {
            return '(' . number_format(abs($number), 2) . ')';
        }
        return number_format($number, 2);
    }
    
    public function exportToExcel()
    {
        $exportData = $this->prepareExportData();
        
        $export = new class($exportData) implements FromArray {
            private $data;
            
            public function __construct(array $data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        };
        
        return Excel::download($export, 'cash_flow_statement_' . $this->selectedYear . '.xlsx');
    }
    
    public function exportToPDF()
    {
        session()->flash('message', 'PDF export will be implemented');
    }
    
    private function prepareExportData()
    {
        $data = [];
        
        // Header
        $data[] = [$this->companyName];
        $data[] = ['STATEMENT OF CASH FLOWS'];
        $data[] = ['For the year ended 31 December ' . $this->selectedYear];
        $data[] = ['Method: ' . ucfirst($this->method)];
        $data[] = [];
        
        // Add cash flow data
        // ... (implementation details)
        
        return $data;
    }
    
    public function render()
    {
        return view('livewire.accounting.statement-of-cash-flows', [
            'operatingActivities' => $this->operatingActivities,
            'investingActivities' => $this->investingActivities,
            'financingActivities' => $this->financingActivities,
            'cashFlowSummary' => $this->cashFlowSummary,
            'comparisonYears' => $this->comparisonYears
        ]);
    }
}