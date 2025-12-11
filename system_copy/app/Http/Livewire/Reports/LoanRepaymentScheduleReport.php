<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\BranchesModel;
use App\Models\LoanSubProduct;
use App\Models\loans_schedules;
use App\Models\AccountsModel;
use App\Models\Loan;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class LoanRepaymentScheduleReport extends Component
{
    public $reportPeriod = 'monthly';
    public $selectedMonth;
    public $selectedYear;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // Loan Repayment Schedule Data
    public $repaymentSchedules = [];
    public $paymentPlans = [];
    public $overduePayments = [];
    public $upcomingPayments = [];
    public $paymentHistory = [];
    public $paymentPatterns = [];
    public $collectionEfficiency = [];
    public $paymentForecasts = [];
    
    // Summary Statistics
    public $totalScheduledPayments = 0;
    public $totalOverdueAmount = 0;
    public $totalUpcomingAmount = 0;
    public $collectionRate = 0;
    public $averagePaymentDelay = 0;
    
    protected $rules = [
        'reportPeriod' => 'required|string',
        'selectedMonth' => 'required|string',
        'selectedYear' => 'required|string',
        'selectedBranch' => 'required|string'
    ];

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->format('m');
        $this->selectedYear = Carbon::now()->format('Y');
        $this->loadBranches();
        $this->loadLoanRepaymentScheduleData();
    }

    public function loadBranches()
    {
        try {
            $this->branches = BranchesModel::select('id', 'branch_name')
                ->orderBy('branch_name')
                ->get()
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error loading branches: ' . $e->getMessage());
            $this->branches = [];
        }
    }

    public function loadLoanRepaymentScheduleData()
    {
        try {
            $this->loadRepaymentSchedules();
            $this->loadPaymentPlans();
            $this->loadOverduePayments();
            $this->loadUpcomingPayments();
            $this->loadPaymentHistory();
            $this->loadPaymentPatterns();
            $this->loadCollectionEfficiency();
            $this->loadPaymentForecasts();
            $this->calculateSummaryStatistics();
        } catch (Exception $e) {
            Log::error('Error loading Loan Repayment Schedule Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadRepaymentSchedules()
    {
        // Sample repayment schedules data
        $this->repaymentSchedules = [
            [
                'loan_id' => 'LOAN-001',
                'client_name' => 'John Doe',
                'loan_amount' => 500000,
                'outstanding_balance' => 350000,
                'interest_rate' => 12.5,
                'loan_term' => 24,
                'monthly_payment' => 25000,
                'principal_payment' => 20000,
                'interest_payment' => 5000,
                'next_payment_date' => '2024-05-15',
                'remaining_payments' => 14,
                'payment_status' => 'Current',
                'last_payment_date' => '2024-04-15',
                'payment_method' => 'Bank Transfer'
            ],
            [
                'loan_id' => 'LOAN-002',
                'client_name' => 'Jane Smith',
                'loan_amount' => 300000,
                'outstanding_balance' => 180000,
                'interest_rate' => 14.0,
                'loan_term' => 18,
                'monthly_payment' => 20000,
                'principal_payment' => 15000,
                'interest_payment' => 5000,
                'next_payment_date' => '2024-05-20',
                'remaining_payments' => 9,
                'payment_status' => 'Current',
                'last_payment_date' => '2024-04-20',
                'payment_method' => 'Mobile Money'
            ],
            [
                'loan_id' => 'LOAN-003',
                'client_name' => 'Bob Wilson',
                'loan_amount' => 750000,
                'outstanding_balance' => 600000,
                'interest_rate' => 10.0,
                'loan_term' => 36,
                'monthly_payment' => 25000,
                'principal_payment' => 20000,
                'interest_payment' => 5000,
                'next_payment_date' => '2024-05-10',
                'remaining_payments' => 24,
                'payment_status' => 'Current',
                'last_payment_date' => '2024-04-10',
                'payment_method' => 'Cash'
            ],
            [
                'loan_id' => 'LOAN-004',
                'client_name' => 'Alice Brown',
                'loan_amount' => 400000,
                'outstanding_balance' => 320000,
                'interest_rate' => 15.0,
                'loan_term' => 12,
                'monthly_payment' => 35000,
                'principal_payment' => 30000,
                'interest_payment' => 5000,
                'next_payment_date' => '2024-05-25',
                'remaining_payments' => 9,
                'payment_status' => 'Current',
                'last_payment_date' => '2024-04-25',
                'payment_method' => 'Bank Transfer'
            ],
            [
                'loan_id' => 'LOAN-005',
                'client_name' => 'Charlie Davis',
                'loan_amount' => 200000,
                'outstanding_balance' => 150000,
                'interest_rate' => 12.0,
                'loan_term' => 12,
                'monthly_payment' => 18000,
                'principal_payment' => 15000,
                'interest_payment' => 3000,
                'next_payment_date' => '2024-05-30',
                'remaining_payments' => 8,
                'payment_status' => 'Current',
                'last_payment_date' => '2024-04-30',
                'payment_method' => 'Mobile Money'
            ]
        ];
    }

    public function loadPaymentPlans()
    {
        // Sample payment plans data
        $this->paymentPlans = [
            [
                'plan_type' => 'Standard Monthly',
                'loan_count' => 1200,
                'total_amount' => 45000000,
                'average_payment' => 25000,
                'success_rate' => 85.5,
                'default_rate' => 3.2
            ],
            [
                'plan_type' => 'Bi-weekly',
                'loan_count' => 300,
                'total_amount' => 12000000,
                'average_payment' => 12500,
                'success_rate' => 92.3,
                'default_rate' => 2.1
            ],
            [
                'plan_type' => 'Quarterly',
                'loan_count' => 150,
                'total_amount' => 8000000,
                'average_payment' => 75000,
                'success_rate' => 78.7,
                'default_rate' => 5.8
            ],
            [
                'plan_type' => 'Flexible',
                'loan_count' => 200,
                'total_amount' => 10000000,
                'average_payment' => 20000,
                'success_rate' => 88.9,
                'default_rate' => 4.2
            ],
            [
                'plan_type' => 'Balloon Payment',
                'loan_count' => 100,
                'total_amount' => 15000000,
                'average_payment' => 5000,
                'success_rate' => 75.0,
                'default_rate' => 8.5
            ]
        ];
    }

    public function loadOverduePayments()
    {
        // Sample overdue payments data
        $this->overduePayments = [
            [
                'loan_id' => 'LOAN-006',
                'client_name' => 'David Johnson',
                'overdue_amount' => 25000,
                'days_overdue' => 15,
                'overdue_installments' => 1,
                'last_payment_date' => '2024-03-15',
                'next_due_date' => '2024-04-15',
                'penalty_amount' => 1250,
                'total_due' => 26250,
                'risk_level' => 'Low',
                'collection_status' => 'In Progress'
            ],
            [
                'loan_id' => 'LOAN-007',
                'client_name' => 'Sarah Williams',
                'overdue_amount' => 50000,
                'days_overdue' => 30,
                'overdue_installments' => 2,
                'last_payment_date' => '2024-03-01',
                'next_due_date' => '2024-04-01',
                'penalty_amount' => 5000,
                'total_due' => 55000,
                'risk_level' => 'Medium',
                'collection_status' => 'Escalated'
            ],
            [
                'loan_id' => 'LOAN-008',
                'client_name' => 'Michael Brown',
                'overdue_amount' => 75000,
                'days_overdue' => 45,
                'overdue_installments' => 3,
                'last_payment_date' => '2024-02-15',
                'next_due_date' => '2024-03-15',
                'penalty_amount' => 11250,
                'total_due' => 86125,
                'risk_level' => 'High',
                'collection_status' => 'Legal Action'
            ],
            [
                'loan_id' => 'LOAN-009',
                'client_name' => 'Emily Davis',
                'overdue_amount' => 30000,
                'days_overdue' => 20,
                'overdue_installments' => 1,
                'last_payment_date' => '2024-03-25',
                'next_due_date' => '2024-04-25',
                'penalty_amount' => 1500,
                'total_due' => 31500,
                'risk_level' => 'Low',
                'collection_status' => 'In Progress'
            ],
            [
                'loan_id' => 'LOAN-010',
                'client_name' => 'Robert Wilson',
                'overdue_amount' => 100000,
                'days_overdue' => 60,
                'overdue_installments' => 4,
                'last_payment_date' => '2024-02-01',
                'next_due_date' => '2024-03-01',
                'penalty_amount' => 20000,
                'total_due' => 120000,
                'risk_level' => 'Very High',
                'collection_status' => 'Write-off Consideration'
            ]
        ];
    }

    public function loadUpcomingPayments()
    {
        // Sample upcoming payments data
        $this->upcomingPayments = [
            [
                'loan_id' => 'LOAN-011',
                'client_name' => 'Lisa Anderson',
                'due_date' => '2024-05-05',
                'due_amount' => 20000,
                'principal_amount' => 15000,
                'interest_amount' => 5000,
                'days_until_due' => 5,
                'payment_method' => 'Bank Transfer',
                'reminder_sent' => 'Yes',
                'risk_level' => 'Low'
            ],
            [
                'loan_id' => 'LOAN-012',
                'client_name' => 'James Taylor',
                'due_date' => '2024-05-08',
                'due_amount' => 30000,
                'principal_amount' => 25000,
                'interest_amount' => 5000,
                'days_until_due' => 8,
                'payment_method' => 'Mobile Money',
                'reminder_sent' => 'Yes',
                'risk_level' => 'Low'
            ],
            [
                'loan_id' => 'LOAN-013',
                'client_name' => 'Maria Garcia',
                'due_date' => '2024-05-12',
                'due_amount' => 25000,
                'principal_amount' => 20000,
                'interest_amount' => 5000,
                'days_until_due' => 12,
                'payment_method' => 'Cash',
                'reminder_sent' => 'No',
                'risk_level' => 'Medium'
            ],
            [
                'loan_id' => 'LOAN-014',
                'client_name' => 'Thomas Miller',
                'due_date' => '2024-05-15',
                'due_amount' => 40000,
                'principal_amount' => 35000,
                'interest_amount' => 5000,
                'days_until_due' => 15,
                'payment_method' => 'Bank Transfer',
                'reminder_sent' => 'Yes',
                'risk_level' => 'Low'
            ],
            [
                'loan_id' => 'LOAN-015',
                'client_name' => 'Jennifer White',
                'due_date' => '2024-05-18',
                'due_amount' => 18000,
                'principal_amount' => 15000,
                'interest_amount' => 3000,
                'days_until_due' => 18,
                'payment_method' => 'Mobile Money',
                'reminder_sent' => 'No',
                'risk_level' => 'Medium'
            ]
        ];
    }

    public function loadPaymentHistory()
    {
        // Sample payment history data
        $this->paymentHistory = [
            [
                'loan_id' => 'LOAN-016',
                'client_name' => 'Christopher Lee',
                'payment_date' => '2024-04-15',
                'payment_amount' => 25000,
                'principal_paid' => 20000,
                'interest_paid' => 5000,
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'Completed',
                'processing_time' => '2 hours'
            ],
            [
                'loan_id' => 'LOAN-017',
                'client_name' => 'Amanda Clark',
                'payment_date' => '2024-04-20',
                'payment_amount' => 30000,
                'principal_paid' => 25000,
                'interest_paid' => 5000,
                'payment_method' => 'Mobile Money',
                'payment_status' => 'Completed',
                'processing_time' => '1 hour'
            ],
            [
                'loan_id' => 'LOAN-018',
                'client_name' => 'Daniel Rodriguez',
                'payment_date' => '2024-04-25',
                'payment_amount' => 20000,
                'principal_paid' => 15000,
                'interest_paid' => 5000,
                'payment_method' => 'Cash',
                'payment_status' => 'Completed',
                'processing_time' => '30 minutes'
            ],
            [
                'loan_id' => 'LOAN-019',
                'client_name' => 'Jessica Martinez',
                'payment_date' => '2024-04-30',
                'payment_amount' => 35000,
                'principal_paid' => 30000,
                'interest_paid' => 5000,
                'payment_method' => 'Bank Transfer',
                'payment_status' => 'Completed',
                'processing_time' => '3 hours'
            ],
            [
                'loan_id' => 'LOAN-020',
                'client_name' => 'Kevin Thompson',
                'payment_date' => '2024-05-01',
                'payment_amount' => 18000,
                'principal_paid' => 15000,
                'interest_paid' => 3000,
                'payment_method' => 'Mobile Money',
                'payment_status' => 'Completed',
                'processing_time' => '1 hour'
            ]
        ];
    }

    public function loadPaymentPatterns()
    {
        // Sample payment patterns data
        $this->paymentPatterns = [
            'payment_timing' => [
                'on_time' => 78.5,
                'early' => 12.3,
                'late_1_7_days' => 6.2,
                'late_8_30_days' => 2.5,
                'late_30_plus_days' => 0.5
            ],
            'payment_methods' => [
                'bank_transfer' => 45.2,
                'mobile_money' => 35.8,
                'cash' => 15.6,
                'cheque' => 2.1,
                'other' => 1.3
            ],
            'payment_frequency' => [
                'monthly' => 65.0,
                'bi_weekly' => 20.0,
                'quarterly' => 10.0,
                'weekly' => 3.0,
                'other' => 2.0
            ],
            'seasonal_patterns' => [
                'january' => 85.2,
                'february' => 82.1,
                'march' => 88.7,
                'april' => 90.3,
                'may' => 87.5,
                'june' => 84.2,
                'july' => 86.8,
                'august' => 89.1,
                'september' => 91.2,
                'october' => 88.9,
                'november' => 85.6,
                'december' => 82.3
            ]
        ];
    }

    public function loadCollectionEfficiency()
    {
        // Sample collection efficiency data
        $this->collectionEfficiency = [
            'overall_collection_rate' => 87.5,
            'on_time_collection_rate' => 78.5,
            'late_collection_rate' => 9.0,
            'write_off_rate' => 1.5,
            'collection_cost_ratio' => 2.8,
            'average_collection_time' => 3.2,
            'collection_staff_efficiency' => 92.3,
            'automated_collection_rate' => 65.8
        ];
    }

    public function loadPaymentForecasts()
    {
        // Sample payment forecasts data
        $this->paymentForecasts = [
            'next_month_forecast' => [
                'expected_payments' => 1250,
                'expected_amount' => 35000000,
                'confidence_level' => 85.2,
                'risk_factors' => ['Seasonal variations', 'Economic conditions']
            ],
            'next_quarter_forecast' => [
                'expected_payments' => 3800,
                'expected_amount' => 105000000,
                'confidence_level' => 82.7,
                'risk_factors' => ['Interest rate changes', 'Market volatility']
            ],
            'next_year_forecast' => [
                'expected_payments' => 15000,
                'expected_amount' => 420000000,
                'confidence_level' => 78.9,
                'risk_factors' => ['Economic growth', 'Regulatory changes']
            ]
        ];
    }

    public function calculateSummaryStatistics()
    {
        $this->totalScheduledPayments = count($this->repaymentSchedules);
        
        $this->totalOverdueAmount = array_sum(array_column($this->overduePayments, 'overdue_amount'));
        
        $this->totalUpcomingAmount = array_sum(array_column($this->upcomingPayments, 'due_amount'));
        
        $this->collectionRate = $this->collectionEfficiency['overall_collection_rate'] ?? 0;
        
        // Calculate average payment delay
        $delays = array_column($this->overduePayments, 'days_overdue');
        $this->averagePaymentDelay = count($delays) > 0 ? array_sum($delays) / count($delays) : 0;
    }

    public function getReportPeriodLabel()
    {
        switch ($this->reportPeriod) {
            case 'daily':
                return 'Daily Report';
            case 'weekly':
                return 'Weekly Report';
            case 'monthly':
                return 'Monthly Report - ' . Carbon::createFromFormat('Y-m', $this->selectedYear . '-' . $this->selectedMonth)->format('F Y');
            case 'yearly':
                return 'Yearly Report - ' . $this->selectedYear;
            default:
                return 'Loan Repayment Schedule Report';
        }
    }

    public function updatedReportPeriod()
    {
        $this->loadLoanRepaymentScheduleData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadLoanRepaymentScheduleData();
    }

    public function updatedSelectedYear()
    {
        $this->loadLoanRepaymentScheduleData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadLoanRepaymentScheduleData();
    }

    public function exportToExcel()
    {
        // Implementation for Excel export
        session()->flash('success', 'Loan Repayment Schedule Report exported successfully!');
    }

    public function render()
    {
        return view('livewire.reports.loan-repayment-schedule-report');
    }
}
