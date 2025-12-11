<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\BankAccount;
use App\Models\Cashbook;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountantDashboard extends Component
{
    public $totalReceiptsToday = 0;
    public $totalPaymentsToday = 0;
    public $totalReceiptsMTD = 0;
    public $totalPaymentsMTD = 0;
    public $bankBalances = [];
    public $cashbookBalance = 0;
    public $pendingReconciliations = 0;
    public $expenseBreakdown = [];
    public $recentPayments = [];
    public $unreconciledEntries = [];
    public $pendingExpenseClaims = [];
    public $dailyReceiptsPaymentsData = [];
    public $monthlyExpenseComparisonData = [];
    public $cashFlowProjectionData = [];
    public $showExportModal = false;
    public $selectedExportType = 'transactions';
    public $exportDateRange = 'this_month';

    public function mount()
    {
        $this->loadFinancialKPIs();
        $this->loadTransactions();
        $this->loadChartData();
    }

    private function loadFinancialKPIs()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Today's receipts and payments
        $this->totalReceiptsToday = Transaction::where('type', 'credit')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        $this->totalPaymentsToday = Transaction::where('type', 'debit')
            ->whereDate('transaction_date', $today)
            ->sum('amount');

        // Month to date receipts and payments
        $this->totalReceiptsMTD = Transaction::where('type', 'credit')
            ->whereBetween('transaction_date', [$startOfMonth, $today])
            ->sum('amount');

        $this->totalPaymentsMTD = Transaction::where('type', 'debit')
            ->whereBetween('transaction_date', [$startOfMonth, $today])
            ->sum('amount');

        // Bank balances
        $this->bankBalances = BankAccount::select('bank_name', 'account_number', 'balance')
            ->where('is_active', true)
            ->get()
            ->map(function ($account) {
                return [
                    'bank_name' => $account->bank_name,
                    'account_number' => $account->account_number,
                    'balance' => $account->balance ?? 0
                ];
            })
            ->toArray();

        // Cashbook balance
        $this->cashbookBalance = accountsModel::where('status', 'ACTIVE')
            ->sum('balance') ?? 0;

        // Pending reconciliations
        $this->pendingReconciliations = Transaction::where('is_reconciled', false)
            ->count();

        // Expense breakdown
        $this->expenseBreakdown = Expense::select('category', DB::raw('SUM(amount) as total_amount'))
            ->whereMonth('expense_date', Carbon::now()->month)
            ->groupBy('category')
            ->get()
            ->map(function ($expense) {
                return [
                    'category' => $expense->category,
                    'amount' => $expense->total_amount ?? 0
                ];
            })
            ->toArray();
    }

    private function loadTransactions()
    {
        // Recent payments
        $this->recentPayments = Transaction::where('type', 'payment')
            ->with(['account', 'branch'])
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date,
                    'account_name' => $transaction->account->name ?? 'N/A',
                    'branch_name' => $transaction->branch->name ?? 'N/A'
                ];
            })
            ->toArray();

        // Unreconciled entries
        $this->unreconciledEntries = Transaction::where('is_reconciled', false)
            ->with(['account', 'branch'])
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'description' => $transaction->description,
                    'transaction_date' => $transaction->transaction_date,
                    'type' => $transaction->type,
                    'account_name' => $transaction->account->name ?? 'N/A'
                ];
            })
            ->toArray();

        // Pending expense claims
        $this->pendingExpenseClaims = Expense::where('status', 'pending')
            ->with(['employee', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($expense) {
                return [
                    'id' => $expense->id,
                    'amount' => $expense->amount,
                    'description' => $expense->description,
                    'expense_date' => $expense->expense_date,
                    'employee_name' => $expense->employee->name ?? 'N/A',
                    'category' => $expense->category->name ?? 'N/A'
                ];
            })
            ->toArray();
    }

    private function loadChartData()
    {
        // Daily receipts vs payments (last 30 days)
        $this->dailyReceiptsPaymentsData = $this->getDailyReceiptsPaymentsData();
        
        // Monthly expense comparison (last 6 months)
        $this->monthlyExpenseComparisonData = $this->getMonthlyExpenseComparisonData();
        
        // Cash flow projection (next 3 months)
        $this->cashFlowProjectionData = $this->getCashFlowProjectionData();
    }

    private function getDailyReceiptsPaymentsData()
    {
        $days = collect();
        $receipts = collect();
        $payments = collect();

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days->push($date->format('M d'));
            
            $dailyReceipts = Transaction::where('type', 'receipt')
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $receipts->push($dailyReceipts);
            
            $dailyPayments = Transaction::where('type', 'payment')
                ->whereDate('transaction_date', $date)
                ->sum('amount');
            $payments->push($dailyPayments);
        }

        return [
            'labels' => $days->toArray(),
            'receipts' => $receipts->toArray(),
            'payments' => $payments->toArray()
        ];
    }

    private function getMonthlyExpenseComparisonData()
    {
        $months = collect();
        $expenses = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months->push($date->format('M Y'));
            
            $monthlyExpenses = Expense::whereYear('expense_date', $date->year)
                ->whereMonth('expense_date', $date->month)
                ->sum('amount');
            $expenses->push($monthlyExpenses);
        }

        return [
            'labels' => $months->toArray(),
            'expenses' => $expenses->toArray()
        ];
    }

    private function getCashFlowProjectionData()
    {
        $months = collect();
        $projectedIncome = collect();
        $projectedExpenses = collect();

        for ($i = 1; $i <= 3; $i++) {
            $date = Carbon::now()->addMonths($i);
            $months->push($date->format('M Y'));
            
            // Simple projection based on average of last 3 months
            $avgIncome = Transaction::where('type', 'receipt')
                ->whereBetween('transaction_date', [
                    Carbon::now()->subMonths(3)->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->avg('amount') ?? 0;
            $projectedIncome->push($avgIncome * 30); // Monthly projection
            
            $avgExpenses = Expense::whereBetween('expense_date', [
                Carbon::now()->subMonths(3)->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->avg('amount') ?? 0;
            $projectedExpenses->push($avgExpenses * 30); // Monthly projection
        }

        return [
            'labels' => $months->toArray(),
            'projected_income' => $projectedIncome->toArray(),
            'projected_expenses' => $projectedExpenses->toArray()
        ];
    }

    public function render()
    {
        return view('livewire.dashboard.accountant-dashboard');
    }

    public function exportData()
    {
        $this->showExportModal = true;
    }

    public function generateExport()
    {
        $this->validate([
            'selectedExportType' => 'required|in:transactions,expenses,reconciliations',
            'exportDateRange' => 'required|in:today,this_week,this_month,last_month'
        ]);

        // Generate export based on selection
        switch ($this->selectedExportType) {
            case 'transactions':
                $this->exportTransactions();
                break;
            case 'expenses':
                $this->exportExpenses();
                break;
            case 'reconciliations':
                $this->exportReconciliations();
                break;
        }

        $this->showExportModal = false;
        session()->flash('message', 'Export generated successfully!');
    }

    private function exportTransactions()
    {
        // Implementation for transaction export
        // This would typically generate a CSV or Excel file
    }

    private function exportExpenses()
    {
        // Implementation for expense export
    }

    private function exportReconciliations()
    {
        // Implementation for reconciliation export
    }

    public function refreshData()
    {
        $this->loadFinancialKPIs();
        $this->loadTransactions();
        $this->loadChartData();
        session()->flash('message', 'Data refreshed successfully!');
    }

    public function markAsReconciled($transactionId)
    {
        $transaction = Transaction::find($transactionId);
        if ($transaction) {
            $transaction->update(['is_reconciled' => true]);
            $this->loadTransactions();
            session()->flash('message', 'Transaction marked as reconciled!');
        }
    }

    public function approveExpenseClaim($expenseId)
    {
        $expense = Expense::find($expenseId);
        if ($expense) {
            $expense->update(['status' => 'approved']);
            $this->loadTransactions();
            session()->flash('message', 'Expense claim approved!');
        }
    }
}
