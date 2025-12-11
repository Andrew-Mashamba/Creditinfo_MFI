<?php

namespace App\Http\Livewire\Expenses;

use App\Services\ExpenseReportService;
use App\Services\AccruedExpenseService;
use App\Models\Account;
use App\Models\BudgetManagement;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;

class ExpenseReports extends Component
{
    // Filter properties
    public $date_from;
    public $date_to;
    public $status = '';
    public $account_id = '';
    public $budget_item_id = '';
    public $user_id = '';
    public $budget_status = '';
    public $payment_type = '';
    public $report_type = 'detailed'; // detailed, summary, trend, accrued

    // Statistics
    public $statistics = null;
    public $monthlyTrend = null;
    public $accruedAging = null;
    public $showStatistics = false;

    // Data lists
    public $accounts = [];
    public $budgetItems = [];
    public $users = [];

    // Payment types
    public $paymentTypes = [
        'money_transfer' => 'Money Transfer',
        'bill_payment' => 'Bill Payment',
        'luku_payment' => 'LUKU Payment',
        'gepg_payment' => 'GEPG Payment',
        'accrual' => 'Accrual'
    ];

    // Status options
    public $statusOptions = [
        'PENDING_APPROVAL' => 'Pending Approval',
        'APPROVED' => 'Approved',
        'PAID' => 'Paid',
        'RETIRED' => 'Retired',
        'ACCRUED' => 'Accrued'
    ];

    // Budget status options
    public $budgetStatusOptions = [
        'WITHIN_BUDGET' => 'Within Budget',
        'OVER_BUDGET' => 'Over Budget',
        'CRITICAL' => 'Critical',
        'WARNING' => 'Warning',
        'MODERATE' => 'Moderate',
        'HEALTHY' => 'Healthy'
    ];

    public function mount()
    {
        // Set default date range to current month
        $this->date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_to = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Load filter options
        $this->loadFilterOptions();
    }

    private function loadFilterOptions()
    {
        // Load expense accounts (major_category_code 5000, level 3)
        $this->accounts = Account::where('major_category_code', '5000')
            ->where('account_level', 3)
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();

        // Load budget items
        $this->budgetItems = BudgetManagement::where('status', 'ACTIVE')
            ->orderBy('budget_name')
            ->get();

        // Load users
        $this->users = User::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
    }

    public function generateReport()
    {
        try {
            $reportService = new ExpenseReportService();
            $filters = $this->prepareFilters();

            if ($this->report_type === 'detailed') {
                $result = $reportService->generateExpenseReport($filters);
            } elseif ($this->report_type === 'summary') {
                $this->statistics = $reportService->generateCategorySummary($filters);
                $this->showStatistics = true;
                session()->flash('success', 'Summary report generated successfully!');
                return;
            } elseif ($this->report_type === 'trend') {
                $year = Carbon::parse($this->date_from)->year;
                $this->monthlyTrend = $reportService->generateMonthlyTrend($year);
                $this->showStatistics = true;
                session()->flash('success', 'Trend report generated successfully!');
                return;
            } elseif ($this->report_type === 'accrued') {
                $accruedService = new AccruedExpenseService();
                $this->accruedAging = $accruedService->getAccruedExpensesAging();
                $this->showStatistics = true;
                session()->flash('success', 'Accrued expenses report generated successfully!');
                return;
            }

            if ($result['success']) {
                session()->flash('success', 'Report generated successfully! ' . $result['records_count'] . ' records.');

                // Trigger download
                return response()->download($result['file_path'], $result['file_name'])
                    ->deleteFileAfterSend(true);
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to generate report');
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error generating report: ' . $e->getMessage());
        }
    }

    private function prepareFilters()
    {
        $filters = [
            'date_from' => $this->date_from,
            'date_to' => $this->date_to
        ];

        if (!empty($this->status)) {
            $filters['status'] = $this->status;
        }

        if (!empty($this->account_id)) {
            $filters['account_id'] = $this->account_id;
        }

        if (!empty($this->budget_item_id)) {
            $filters['budget_item_id'] = $this->budget_item_id;
        }

        if (!empty($this->user_id)) {
            $filters['user_id'] = $this->user_id;
        }

        if (!empty($this->budget_status)) {
            $filters['budget_status'] = $this->budget_status;
        }

        if (!empty($this->payment_type)) {
            $filters['payment_type'] = $this->payment_type;
        }

        return $filters;
    }

    public function clearFilters()
    {
        $this->date_from = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->date_to = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->status = '';
        $this->account_id = '';
        $this->budget_item_id = '';
        $this->user_id = '';
        $this->budget_status = '';
        $this->payment_type = '';
        $this->report_type = 'detailed';
        $this->showStatistics = false;
        $this->statistics = null;
        $this->monthlyTrend = null;
        $this->accruedAging = null;

        session()->flash('info', 'Filters cleared.');
    }

    public function render()
    {
        return view('livewire.expenses.expense-reports');
    }
}
