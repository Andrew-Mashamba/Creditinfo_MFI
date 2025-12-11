<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Exports\StatementOfCashFlowExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StatementOfCashFlow extends Component
{
    public $reportStartDate;
    public $reportEndDate;
    public $statementData = null;
    public $isGenerating = false;
    public $isExporting = false;
    public $errorMessage = '';
    public $successMessage = '';

    public function mount()
    {
        $this->reportStartDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = Carbon::now()->format('Y-m-d');
    }

    public function generateStatement()
    {
        $this->isGenerating = true;
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->statementData = $this->getStatementData();
            $this->successMessage = 'Statement of Cash Flow generated successfully!';
            
            Log::info('Statement of Cash Flow generated', [
                'start_date' => $this->reportStartDate,
                'end_date' => $this->reportEndDate,
                'user_id' => auth()->id()
            ]);
        } catch (Exception $e) {
            $this->errorMessage = 'Error generating statement: ' . $e->getMessage();
            Log::error('Statement of Cash Flow generation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    public function getStatementData()
    {
        $startDate = Carbon::parse($this->reportStartDate)->startOfDay();
        $endDate = Carbon::parse($this->reportEndDate)->endOfDay();

        // Get operating activities (income and expense accounts)
        $operatingActivities = $this->getOperatingActivities($startDate, $endDate);
        
        // Get investing activities (asset purchases/sales)
        $investingActivities = $this->getInvestingActivities($startDate, $endDate);
        
        // Get financing activities (loans, capital)
        $financingActivities = $this->getFinancingActivities($startDate, $endDate);

        // Calculate net cash flow
        $netOperatingCashFlow = $operatingActivities['net_cash_flow'];
        $netInvestingCashFlow = $investingActivities['net_cash_flow'];
        $netFinancingCashFlow = $financingActivities['net_cash_flow'];
        $netCashFlow = $netOperatingCashFlow + $netInvestingCashFlow + $netFinancingCashFlow;

        // Get beginning and ending cash balances
        $beginningCash = $this->getCashBalance($startDate->copy()->subDay());
        $endingCash = $this->getCashBalance($endDate);

        return [
            'period' => [
                'start_date' => $startDate->format('F d, Y'),
                'end_date' => $endDate->format('F d, Y'),
                'start_date_short' => $startDate->format('M d, Y'),
                'end_date_short' => $endDate->format('M d, Y'),
            ],
            'operating_activities' => $operatingActivities,
            'investing_activities' => $investingActivities,
            'financing_activities' => $financingActivities,
            'cash_flow_summary' => [
                'net_operating_cash_flow' => $netOperatingCashFlow,
                'net_investing_cash_flow' => $netInvestingCashFlow,
                'net_financing_cash_flow' => $netFinancingCashFlow,
                'net_cash_flow' => $netCashFlow,
                'beginning_cash' => $beginningCash,
                'ending_cash' => $endingCash,
            ],
            'is_demo_mode' => $this->isDemoMode(),
        ];
    }

    private function getOperatingActivities($startDate, $endDate)
    {
        // Get income accounts
        $incomeAccounts = DB::table('accounts')
            ->where('type', 'income_accounts')
            ->get();

        // Get expense accounts
        $expenseAccounts = DB::table('accounts')
            ->where('type', 'expense_accounts')
            ->get();

        $totalIncome = 0;
        $totalExpenses = 0;
        $incomeDetails = [];
        $expenseDetails = [];

        // Calculate income from general ledger
        foreach ($incomeAccounts as $account) {
            $income = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('credit', '>', 0)
                ->sum('credit');

            if ($income > 0) {
                $totalIncome += $income;
                $incomeDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $income,
                ];
            }
        }

        // Calculate expenses from general ledger
        foreach ($expenseAccounts as $account) {
            $expense = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('debit', '>', 0)
                ->sum('debit');

            if ($expense > 0) {
                $totalExpenses += $expense;
                $expenseDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $expense,
                ];
            }
        }

        // No sample data generation - only show real data

        $netOperatingCashFlow = $totalIncome - $totalExpenses;

        return [
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_cash_flow' => $netOperatingCashFlow,
            'income_details' => $incomeDetails,
            'expense_details' => $expenseDetails,
        ];
    }

    private function getInvestingActivities($startDate, $endDate)
    {
        // Get fixed asset accounts
        $fixedAssetAccounts = DB::table('accounts')
            ->where('type', 'asset_accounts')
            ->where(function($query) {
                $query->where('account_name', 'like', '%equipment%')
                      ->orWhere('account_name', 'like', '%furniture%')
                      ->orWhere('account_name', 'like', '%vehicle%')
                      ->orWhere('account_name', 'like', '%building%')
                      ->orWhere('account_name', 'like', '%investment%');
            })
            ->get();

        $totalPurchases = 0;
        $totalSales = 0;
        $purchaseDetails = [];
        $saleDetails = [];

        foreach ($fixedAssetAccounts as $account) {
            // Asset purchases (debits)
            $purchases = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('debit', '>', 0)
                ->sum('debit');

            // Asset sales (credits)
            $sales = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('credit', '>', 0)
                ->sum('credit');

            if ($purchases > 0) {
                $totalPurchases += $purchases;
                $purchaseDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $purchases,
                ];
            }

            if ($sales > 0) {
                $totalSales += $sales;
                $saleDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $sales,
                ];
            }
        }

        $netInvestingCashFlow = $totalSales - $totalPurchases;

        return [
            'total_purchases' => $totalPurchases,
            'total_sales' => $totalSales,
            'net_cash_flow' => $netInvestingCashFlow,
            'purchase_details' => $purchaseDetails,
            'sale_details' => $saleDetails,
        ];
    }

    private function getFinancingActivities($startDate, $endDate)
    {
        // Get loan accounts (liabilities)
        $loanAccounts = DB::table('accounts')
            ->where('type', 'liability_accounts')
            ->where(function($query) {
                $query->where('account_name', 'like', '%loan%')
                      ->orWhere('account_name', 'like', '%borrowing%')
                      ->orWhere('account_name', 'like', '%debt%');
            })
            ->get();

        // Get capital accounts
        $capitalAccounts = DB::table('accounts')
            ->where('type', 'capital_accounts')
            ->get();

        $totalLoanProceeds = 0;
        $totalLoanRepayments = 0;
        $totalCapitalContributions = 0;
        $totalCapitalWithdrawals = 0;

        $loanProceedDetails = [];
        $loanRepaymentDetails = [];
        $capitalContributionDetails = [];
        $capitalWithdrawalDetails = [];

        // Calculate loan activities
        foreach ($loanAccounts as $account) {
            // Loan proceeds (credits)
            $proceeds = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('credit', '>', 0)
                ->sum('credit');

            // Loan repayments (debits)
            $repayments = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('debit', '>', 0)
                ->sum('debit');

            if ($proceeds > 0) {
                $totalLoanProceeds += $proceeds;
                $loanProceedDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $proceeds,
                ];
            }

            if ($repayments > 0) {
                $totalLoanRepayments += $repayments;
                $loanRepaymentDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $repayments,
                ];
            }
        }

        // Calculate capital activities
        foreach ($capitalAccounts as $account) {
            // Capital contributions (credits)
            $contributions = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('credit', '>', 0)
                ->sum('credit');

            // Capital withdrawals (debits)
            $withdrawals = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->where('debit', '>', 0)
                ->sum('debit');

            if ($contributions > 0) {
                $totalCapitalContributions += $contributions;
                $capitalContributionDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $contributions,
                ];
            }

            if ($withdrawals > 0) {
                $totalCapitalWithdrawals += $withdrawals;
                $capitalWithdrawalDetails[] = [
                    'account_name' => $account->account_name,
                    'amount' => $withdrawals,
                ];
            }
        }

        $netFinancingCashFlow = ($totalLoanProceeds + $totalCapitalContributions) - ($totalLoanRepayments + $totalCapitalWithdrawals);

        return [
            'total_loan_proceeds' => $totalLoanProceeds,
            'total_loan_repayments' => $totalLoanRepayments,
            'total_capital_contributions' => $totalCapitalContributions,
            'total_capital_withdrawals' => $totalCapitalWithdrawals,
            'net_cash_flow' => $netFinancingCashFlow,
            'loan_proceed_details' => $loanProceedDetails,
            'loan_repayment_details' => $loanRepaymentDetails,
            'capital_contribution_details' => $capitalContributionDetails,
            'capital_withdrawal_details' => $capitalWithdrawalDetails,
        ];
    }

    private function getCashBalance($date)
    {
        $cashAccounts = DB::table('accounts')
            ->whereIn('type', ['asset_accounts'])
            ->where(function($query) {
                $query->where('account_name', 'like', '%cash%')
                      ->orWhere('account_name', 'like', '%bank%')
                      ->orWhere('account_name', 'like', '%deposit%')
                      ->orWhere('account_name', 'like', '%savings%');
            })
            ->get();

        $totalBalance = 0;

        foreach ($cashAccounts as $account) {
            $debits = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '<=', $date)
                ->sum('debit');

            $credits = DB::table('general_ledger')
                ->where('record_on_account_number', $account->account_number)
                ->where('created_at', '<=', $date)
                ->sum('credit');

            $balance = $debits - $credits;
            $totalBalance += $balance;
        }

        return $totalBalance;
    }


    private function isDemoMode()
    {
        // Since we no longer generate sample data, this will always return false
        // indicating we're showing real data only
        return false;
    }

    public function exportPdf()
    {
        $this->isExporting = true;
        $this->errorMessage = '';

        try {
            if (!$this->statementData) {
                $this->statementData = $this->getStatementData();
            }

            // Get institution data
            $institution = DB::table('institutions')->first();
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.statement-of-cash-flow', [
                'statementData' => $this->statementData,
                'institution' => $institution
            ])->setPaper('a4', 'portrait');

            $filename = 'Statement_of_Cash_Flow_' . $this->reportStartDate . '_to_' . $this->reportEndDate . '.pdf';
            
            Log::info('Statement of Cash Flow PDF exported', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting PDF: ' . $e->getMessage();
            Log::error('Statement of Cash Flow PDF export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExporting = false;
        }
    }

    public function exportExcel()
    {
        $this->isExporting = true;
        $this->errorMessage = '';

        try {
            if (!$this->statementData) {
                $this->statementData = $this->getStatementData();
            }

            // Get institution data
            $institution = DB::table('institutions')->first();
            
            $filename = 'Statement_of_Cash_Flow_' . $this->reportStartDate . '_to_' . $this->reportEndDate . '.xlsx';
            
            Log::info('Statement of Cash Flow Excel exported', [
                'filename' => $filename,
                'user_id' => auth()->id()
            ]);

            return Excel::download(new StatementOfCashFlowExport($this->statementData, $institution), $filename);

        } catch (Exception $e) {
            $this->errorMessage = 'Error exporting Excel: ' . $e->getMessage();
            Log::error('Statement of Cash Flow Excel export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        } finally {
            $this->isExporting = false;
        }
    }

    public function render()
    {
        return view('livewire.reports.statement-of-cash-flow');
    }
}
