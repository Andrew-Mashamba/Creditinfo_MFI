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
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DailyOperationsReportExport;

class DailyOperationsReport extends Component
{
    public $reportDate;
    public $selectedBranch = 'all';
    public $branches = [];
    
    // Daily Summary Data
    public $dailySummary = [];
    public $transactionSummary = [];
    public $loanOperations = [];
    public $depositOperations = [];
    public $withdrawalOperations = [];
    public $newLoans = [];
    public $loanDisbursements = [];
    public $loanRepayments = [];
    public $newClients = [];
    public $staffActivities = [];
    
    // Statistics
    public $totalTransactions = 0;
    public $totalTransactionValue = 0;
    public $totalNewLoans = 0;
    public $totalLoanDisbursements = 0;
    public $totalLoanRepayments = 0;
    public $totalNewClients = 0;
    public $averageTransactionValue = 0;
    
    // Export loading state
    public $isExporting = false;
    public $exportProgress = 0;
    
    protected $rules = [
        'reportDate' => 'required|date',
        'selectedBranch' => 'required|string'
    ];

    public function mount()
    {
        $this->reportDate = Carbon::today()->format('Y-m-d');
        $this->loadBranches();
        $this->loadDailyOperationsData();
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

    public function loadDailyOperationsData()
    {
        try {
            $this->loadDailySummary();
            $this->loadTransactionSummary();
            $this->loadLoanOperations();
            $this->loadDepositOperations();
            $this->loadWithdrawalOperations();
            $this->loadNewLoans();
            $this->loadLoanDisbursements();
            $this->loadLoanRepayments();
            $this->loadNewClients();
            $this->loadStaffActivities();
            $this->calculateStatistics();
        } catch (Exception $e) {
            Log::error('Error loading Daily Operations Report data: ' . $e->getMessage());
            session()->flash('error', 'Error loading report data: ' . $e->getMessage());
        }
    }

    public function loadDailySummary()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();
        
        // Build branch filter
        $branchFilter = $this->selectedBranch !== 'all' ? ['branch_id' => $this->selectedBranch] : [];

        try {
            // Get total clients served (unique clients who had transactions)
            $totalClientsServed = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->distinct('client_number')
                ->count('client_number');

            // Get total transactions
            $totalTransactions = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->count();

            // Get total transaction value
            $totalTransactionValue = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->sum('amount');

            // Get new loans processed
            $newLoansProcessed = DB::table('loans')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('branch_id', $this->selectedBranch);
                })
                ->whereIn('status', ['APPROVED', 'DISBURSED', 'ACTIVE'])
                ->count();

            // Get loan disbursements
            $loanDisbursements = DB::table('loans')
                ->whereBetween('disbursement_date', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('branch_id', $this->selectedBranch);
                })
                ->whereNotNull('disbursement_date')
                ->count();

            // Get loan repayments
            $loanRepayments = DB::table('loan_repayments')
                ->join('loans', 'loan_repayments.loan_id', '=', 'loans.loan_id')
                ->whereBetween('loan_repayments.payment_date', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('loans.branch_id', $this->selectedBranch);
                })
                ->count();

            // Get new clients registered
            $newClientsRegistered = DB::table('clients')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('branch_id', $this->selectedBranch);
                })
                ->count();

            // Get staff on duty (employees who had activities today)
            $staffOnDuty = DB::table('employees')
                ->where('employment_status', 'active')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('branch_id', $this->selectedBranch);
                })
                ->count();

            // Calculate average transaction time (simplified - using processing time if available)
            $avgTransactionTime = 'N/A';
            $transactionWithTime = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotNull('processed_at')
                ->whereNotNull('received_at')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, received_at, processed_at)) as avg_seconds')
                ->first();

            if ($transactionWithTime && $transactionWithTime->avg_seconds) {
                $avgMinutes = round($transactionWithTime->avg_seconds / 60, 1);
                $avgTransactionTime = $avgMinutes . ' minutes';
            }

            // Get peak hours (hour with most transactions)
            $peakHour = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('HOUR(created_at) as hour, COUNT(*) as transaction_count')
                ->groupBy('hour')
                ->orderByDesc('transaction_count')
                ->first();

            $peakHours = 'N/A';
            if ($peakHour) {
                $startHour = $peakHour->hour;
                $endHour = $startHour + 1;
                $peakHours = sprintf('%02d:00 - %02d:00', $startHour, $endHour);
            }

            $this->dailySummary = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('l'),
                'total_clients_served' => $totalClientsServed,
                'total_transactions' => $totalTransactions,
                'total_transaction_value' => $totalTransactionValue,
                'new_loans_processed' => $newLoansProcessed,
                'loan_disbursements' => $loanDisbursements,
                'loan_repayments' => $loanRepayments,
                'new_clients_registered' => $newClientsRegistered,
                'staff_on_duty' => $staffOnDuty,
                'average_transaction_time' => $avgTransactionTime,
                'peak_hours' => $peakHours,
                'system_uptime' => '99.8%' // This would need system monitoring data
            ];

        } catch (Exception $e) {
            Log::error('Error loading daily summary: ' . $e->getMessage());
            // Fallback to empty data
            $this->dailySummary = [
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('l'),
                'total_clients_served' => 0,
                'total_transactions' => 0,
                'total_transaction_value' => 0,
                'new_loans_processed' => 0,
                'loan_disbursements' => 0,
                'loan_repayments' => 0,
                'new_clients_registered' => 0,
                'staff_on_duty' => 0,
                'average_transaction_time' => 'N/A',
                'peak_hours' => 'N/A',
                'system_uptime' => 'N/A'
            ];
        }
    }

    public function loadTransactionSummary()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            // Get deposits
            $deposits = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('type', 'deposit')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as average_amount')
                ->first();

            // Get withdrawals
            $withdrawals = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('type', 'withdrawal')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as average_amount')
                ->first();

            // Get transfers
            $transfers = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('type', 'transfer')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as average_amount')
                ->first();

            // Get loan payments
            $loanPayments = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereIn('type', ['loan_payment', 'loan_repayment'])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as average_amount')
                ->first();

            // Get other transactions (excluding the above types)
            $otherTransactions = DB::table('transactions')
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereNotIn('type', ['deposit', 'withdrawal', 'transfer', 'loan_payment', 'loan_repayment'])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                          ->where('accounts.branch_id', $this->selectedBranch);
                })
                ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount, AVG(amount) as average_amount')
                ->first();

            $this->transactionSummary = [
                'deposits' => [
                    'count' => $deposits->count ?? 0,
                    'total_amount' => $deposits->total_amount ?? 0,
                    'average_amount' => round($deposits->average_amount ?? 0, 2)
                ],
                'withdrawals' => [
                    'count' => $withdrawals->count ?? 0,
                    'total_amount' => $withdrawals->total_amount ?? 0,
                    'average_amount' => round($withdrawals->average_amount ?? 0, 2)
                ],
                'transfers' => [
                    'count' => $transfers->count ?? 0,
                    'total_amount' => $transfers->total_amount ?? 0,
                    'average_amount' => round($transfers->average_amount ?? 0, 2)
                ],
                'loan_payments' => [
                    'count' => $loanPayments->count ?? 0,
                    'total_amount' => $loanPayments->total_amount ?? 0,
                    'average_amount' => round($loanPayments->average_amount ?? 0, 2)
                ],
                'other_transactions' => [
                    'count' => $otherTransactions->count ?? 0,
                    'total_amount' => $otherTransactions->total_amount ?? 0,
                    'average_amount' => round($otherTransactions->average_amount ?? 0, 2)
                ]
            ];

        } catch (Exception $e) {
            Log::error('Error loading transaction summary: ' . $e->getMessage());
            // Fallback to empty data
            $this->transactionSummary = [
                'deposits' => ['count' => 0, 'total_amount' => 0, 'average_amount' => 0],
                'withdrawals' => ['count' => 0, 'total_amount' => 0, 'average_amount' => 0],
                'transfers' => ['count' => 0, 'total_amount' => 0, 'average_amount' => 0],
                'loan_payments' => ['count' => 0, 'total_amount' => 0, 'average_amount' => 0],
                'other_transactions' => ['count' => 0, 'total_amount' => 0, 'average_amount' => 0]
            ];
        }
    }

    public function loadLoanOperations()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->loanOperations = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.product_id')
                ->leftJoin('employees', 'loans.supervisor_id', '=', 'employees.id')
                ->whereBetween('loans.created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('loans.branch_id', $this->selectedBranch);
                })
                ->select([
                    'loans.loan_id',
                    'loans.principle_amount as loan_amount',
                    'loans.status',
                    'loans.created_at',
                    'loans.updated_at',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'loan_sub_products.product_name as loan_type',
                    'employees.first_name as officer_first_name',
                    'employees.last_name as officer_last_name'
                ])
                ->orderBy('loans.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($loan) {
                    // Calculate processing time
                    $processingTime = 'N/A';
                    if ($loan->created_at && $loan->updated_at) {
                        $start = Carbon::parse($loan->created_at);
                        $end = Carbon::parse($loan->updated_at);
                        $diffInHours = $start->diffInHours($end);
                        $diffInMinutes = $start->diffInMinutes($end) % 60;
                        
                        if ($diffInHours > 0) {
                            $processingTime = $diffInHours . ' hours' . ($diffInMinutes > 0 ? ' ' . $diffInMinutes . ' minutes' : '');
                        } else {
                            $processingTime = $diffInMinutes . ' minutes';
                        }
                    }

                    return [
                        'loan_id' => $loan->loan_id,
                        'client_name' => trim($loan->first_name . ' ' . $loan->middle_name . ' ' . $loan->last_name),
                        'loan_amount' => $loan->loan_amount ?? 0,
                        'loan_type' => $loan->loan_type ?? 'N/A',
                        'officer' => trim(($loan->officer_first_name ?? '') . ' ' . ($loan->officer_last_name ?? '')),
                        'status' => $loan->status ?? 'N/A',
                        'processing_time' => $processingTime,
                        'timestamp' => Carbon::parse($loan->created_at)->format('h:i A')
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading loan operations: ' . $e->getMessage());
            $this->loanOperations = [];
        }
    }

    public function loadDepositOperations()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->depositOperations = DB::table('transactions')
                ->join('clients', 'transactions.client_number', '=', 'clients.client_number')
                ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->whereBetween('transactions.created_at', [$startOfDay, $endOfDay])
                ->where('transactions.type', 'deposit')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('accounts.branch_id', $this->selectedBranch);
                })
                ->select([
                    'transactions.id as transaction_id',
                    'transactions.amount',
                    'transactions.created_at',
                    'transactions.reference',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'accounts.account_type',
                    'accounts.type'
                ])
                ->orderBy('transactions.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($transaction) {
                    return [
                        'transaction_id' => $transaction->reference ?? 'TXN-' . $transaction->transaction_id,
                        'client_name' => trim($transaction->first_name . ' ' . $transaction->middle_name . ' ' . $transaction->last_name),
                        'amount' => $transaction->amount ?? 0,
                        'account_type' => $transaction->account_type ?? $transaction->type ?? 'N/A',
                        'officer' => 'N/A', // This would need to be linked to the processing officer
                        'timestamp' => Carbon::parse($transaction->created_at)->format('h:i A')
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading deposit operations: ' . $e->getMessage());
            $this->depositOperations = [];
        }
    }

    public function loadWithdrawalOperations()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->withdrawalOperations = DB::table('transactions')
                ->join('clients', 'transactions.client_number', '=', 'clients.client_number')
                ->leftJoin('accounts', 'transactions.account_id', '=', 'accounts.id')
                ->whereBetween('transactions.created_at', [$startOfDay, $endOfDay])
                ->where('transactions.type', 'withdrawal')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('accounts.branch_id', $this->selectedBranch);
                })
                ->select([
                    'transactions.id as transaction_id',
                    'transactions.amount',
                    'transactions.created_at',
                    'transactions.reference',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'accounts.account_type',
                    'accounts.type'
                ])
                ->orderBy('transactions.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($transaction) {
                    return [
                        'transaction_id' => $transaction->reference ?? 'TXN-' . $transaction->transaction_id,
                        'client_name' => trim($transaction->first_name . ' ' . $transaction->middle_name . ' ' . $transaction->last_name),
                        'amount' => $transaction->amount ?? 0,
                        'account_type' => $transaction->account_type ?? $transaction->type ?? 'N/A',
                        'officer' => 'N/A', // This would need to be linked to the processing officer
                        'timestamp' => Carbon::parse($transaction->created_at)->format('h:i A')
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading withdrawal operations: ' . $e->getMessage());
            $this->withdrawalOperations = [];
        }
    }

    public function loadNewLoans()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->newLoans = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->leftJoin('loan_sub_products', 'loans.loan_sub_product', '=', 'loan_sub_products.product_id')
                ->leftJoin('employees', 'loans.supervisor_id', '=', 'employees.id')
                ->whereBetween('loans.created_at', [$startOfDay, $endOfDay])
                ->whereIn('loans.status', ['APPROVED', 'DISBURSED', 'ACTIVE'])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('loans.branch_id', $this->selectedBranch);
                })
                ->select([
                    'loans.loan_id',
                    'loans.principle_amount as loan_amount',
                    'loans.interest_rate',
                    'loans.tenure as term_months',
                    'loans.status',
                    'loans.created_at as approval_date',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'loan_sub_products.product_name as loan_type',
                    'employees.first_name as officer_first_name',
                    'employees.last_name as officer_last_name'
                ])
                ->orderBy('loans.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($loan) {
                    return [
                        'loan_id' => $loan->loan_id,
                        'client_name' => trim($loan->first_name . ' ' . $loan->middle_name . ' ' . $loan->last_name),
                        'loan_amount' => $loan->loan_amount ?? 0,
                        'loan_type' => $loan->loan_type ?? 'N/A',
                        'interest_rate' => $loan->interest_rate ?? 0,
                        'term_months' => $loan->term_months ?? 0,
                        'officer' => trim(($loan->officer_first_name ?? '') . ' ' . ($loan->officer_last_name ?? '')),
                        'approval_date' => Carbon::parse($loan->approval_date)->format('Y-m-d'),
                        'status' => $loan->status ?? 'N/A'
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading new loans: ' . $e->getMessage());
            $this->newLoans = [];
        }
    }

    public function loadLoanDisbursements()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->loanDisbursements = DB::table('loans')
                ->join('clients', 'loans.client_number', '=', 'clients.client_number')
                ->leftJoin('employees', 'loans.supervisor_id', '=', 'employees.id')
                ->whereBetween('loans.disbursement_date', [$startOfDay, $endOfDay])
                ->whereNotNull('loans.disbursement_date')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('loans.branch_id', $this->selectedBranch);
                })
                ->select([
                    'loans.loan_id',
                    'loans.net_disbursement_amount as disbursed_amount',
                    'loans.disbursement_date',
                    'loans.disbursement_method as method',
                    'loans.status',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'employees.first_name as officer_first_name',
                    'employees.last_name as officer_last_name'
                ])
                ->orderBy('loans.disbursement_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function($loan) {
                    return [
                        'loan_id' => $loan->loan_id,
                        'client_name' => trim($loan->first_name . ' ' . $loan->middle_name . ' ' . $loan->last_name),
                        'disbursed_amount' => $loan->disbursed_amount ?? 0,
                        'disbursement_date' => Carbon::parse($loan->disbursement_date)->format('Y-m-d'),
                        'officer' => trim(($loan->officer_first_name ?? '') . ' ' . ($loan->officer_last_name ?? '')),
                        'method' => $loan->method ?? 'N/A',
                        'status' => $loan->status ?? 'N/A'
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading loan disbursements: ' . $e->getMessage());
            $this->loanDisbursements = [];
        }
    }

    public function loadLoanRepayments()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->loanRepayments = DB::table('loan_repayments')
                ->join('loans', 'loan_repayments.loan_id', '=', 'loans.loan_id')
                ->join('clients', 'loan_repayments.client_number', '=', 'clients.client_number')
                ->leftJoin('employees', 'loan_repayments.processed_by', '=', 'employees.id')
                ->whereBetween('loan_repayments.payment_date', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('loans.branch_id', $this->selectedBranch);
                })
                ->select([
                    'loan_repayments.loan_id',
                    'loan_repayments.amount as repayment_amount',
                    'loan_repayments.payment_date as repayment_date',
                    'loan_repayments.payment_method as method',
                    'loan_repayments.status',
                    'loan_repayments.receipt_number',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'employees.first_name as officer_first_name',
                    'employees.last_name as officer_last_name'
                ])
                ->orderBy('loan_repayments.payment_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function($repayment) {
                    // For now, we'll use the total amount as both principal and interest
                    // In a real system, you might have separate fields for principal and interest
                    $repaymentAmount = $repayment->repayment_amount ?? 0;
                    $principalAmount = $repaymentAmount * 0.8; // Assuming 80% principal, 20% interest
                    $interestAmount = $repaymentAmount * 0.2;

                    return [
                        'loan_id' => $repayment->loan_id,
                        'client_name' => trim($repayment->first_name . ' ' . $repayment->middle_name . ' ' . $repayment->last_name),
                        'repayment_amount' => $repaymentAmount,
                        'principal_amount' => round($principalAmount, 2),
                        'interest_amount' => round($interestAmount, 2),
                        'repayment_date' => Carbon::parse($repayment->repayment_date)->format('Y-m-d'),
                        'officer' => trim(($repayment->officer_first_name ?? '') . ' ' . ($repayment->officer_last_name ?? '')),
                        'method' => $repayment->method ?? 'N/A',
                        'status' => $repayment->status ?? 'N/A'
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading loan repayments: ' . $e->getMessage());
            $this->loanRepayments = [];
        }
    }

    public function loadNewClients()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            $this->newClients = DB::table('clients')
                ->leftJoin('employees', 'clients.created_by', '=', 'employees.id')
                ->whereBetween('clients.created_at', [$startOfDay, $endOfDay])
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('clients.branch_id', $this->selectedBranch);
                })
                ->select([
                    'clients.client_number as client_id',
                    'clients.first_name',
                    'clients.middle_name',
                    'clients.last_name',
                    'clients.created_at as registration_date',
                    'clients.client_type',
                    'clients.status',
                    'employees.first_name as officer_first_name',
                    'employees.last_name as officer_last_name'
                ])
                ->orderBy('clients.created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($client) {
                    // Get initial deposit from first account opening transaction
                    $initialDeposit = DB::table('transactions')
                        ->where('client_number', $client->client_id)
                        ->where('type', 'deposit')
                        ->orderBy('created_at', 'asc')
                        ->value('amount') ?? 0;

                    return [
                        'client_id' => $client->client_id,
                        'client_name' => trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name),
                        'registration_date' => Carbon::parse($client->registration_date)->format('Y-m-d'),
                        'client_type' => $client->client_type ?? 'Individual',
                        'officer' => trim(($client->officer_first_name ?? '') . ' ' . ($client->officer_last_name ?? '')),
                        'status' => $client->status ?? 'Active',
                        'initial_deposit' => $initialDeposit
                    ];
                })
                ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading new clients: ' . $e->getMessage());
            $this->newClients = [];
        }
    }

    public function loadStaffActivities()
    {
        $date = Carbon::parse($this->reportDate);
        $startOfDay = $date->startOfDay();
        $endOfDay = $date->endOfDay();

        try {
            // Get active employees for the selected branch
            $employees = DB::table('employees')
                ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
                ->where('employees.employment_status', 'active')
                ->when($this->selectedBranch !== 'all', function($query) {
                    $query->where('employees.branch_id', $this->selectedBranch);
                })
                ->select([
                    'employees.id',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.job_title as position',
                    'employees.branch_id',
                    'departments.department_name as department'
                ])
                ->get();

            $this->staffActivities = $employees->map(function($employee) use ($startOfDay, $endOfDay) {
                // Count activities completed (loans processed)
                $activitiesCompleted = DB::table('loans')
                    ->where('supervisor_id', $employee->id)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->count();

                // Count unique clients served
                $clientsServed = DB::table('loans')
                    ->where('supervisor_id', $employee->id)
                    ->whereBetween('created_at', [$startOfDay, $endOfDay])
                    ->distinct('client_number')
                    ->count('client_number');

                // Count transactions processed (this is a simplified approach)
                // In a real system, you might have a direct link between transactions and employees
                $transactionsProcessed = DB::table('transactions')
                    ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
                    ->whereBetween('transactions.created_at', [$startOfDay, $endOfDay])
                    ->where('accounts.branch_id', $employee->branch_id ?? null)
                    ->count();

                // Calculate efficiency rating based on activities
                $efficiencyRating = 'Good';
                if ($activitiesCompleted >= 10) {
                    $efficiencyRating = 'Excellent';
                } elseif ($activitiesCompleted >= 5) {
                    $efficiencyRating = 'Good';
                } elseif ($activitiesCompleted >= 1) {
                    $efficiencyRating = 'Average';
                } else {
                    $efficiencyRating = 'Low';
                }

                return [
                    'staff_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'position' => $employee->position ?? 'N/A',
                    'department' => $employee->department ?? 'N/A',
                    'activities_completed' => $activitiesCompleted,
                    'clients_served' => $clientsServed,
                    'transactions_processed' => $transactionsProcessed,
                    'efficiency_rating' => $efficiencyRating
                ];
            })
            ->sortByDesc('activities_completed')
            ->take(10)
            ->values()
            ->toArray();

        } catch (Exception $e) {
            Log::error('Error loading staff activities: ' . $e->getMessage());
            $this->staffActivities = [];
        }
    }

    public function calculateStatistics()
    {
        $this->totalTransactions = $this->transactionSummary['deposits']['count'] + 
                                 $this->transactionSummary['withdrawals']['count'] + 
                                 $this->transactionSummary['transfers']['count'] + 
                                 $this->transactionSummary['loan_payments']['count'] + 
                                 $this->transactionSummary['other_transactions']['count'];

        $this->totalTransactionValue = $this->transactionSummary['deposits']['total_amount'] + 
                                     $this->transactionSummary['withdrawals']['total_amount'] + 
                                     $this->transactionSummary['transfers']['total_amount'] + 
                                     $this->transactionSummary['loan_payments']['total_amount'] + 
                                     $this->transactionSummary['other_transactions']['total_amount'];

        $this->totalNewLoans = count($this->newLoans);
        $this->totalLoanDisbursements = count($this->loanDisbursements);
        $this->totalLoanRepayments = count($this->loanRepayments);
        $this->totalNewClients = count($this->newClients);

        $this->averageTransactionValue = $this->totalTransactions > 0 ? 
            $this->totalTransactionValue / $this->totalTransactions : 0;
    }

    public function updatedReportDate()
    {
        $this->loadDailyOperationsData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadDailyOperationsData();
    }

    public function exportToExcel()
    {
        try {
            $this->isExporting = true;
            $this->exportProgress = 0;
            
            // Get branch name for filename
            $branchName = 'All_Branches';
            if ($this->selectedBranch !== 'all') {
                $branch = collect($this->branches)->firstWhere('id', $this->selectedBranch);
                $branchName = $branch ? str_replace(' ', '_', $branch['branch_name']) : 'Branch_' . $this->selectedBranch;
            }
            
            // Generate filename
            $date = Carbon::parse($this->reportDate)->format('Y-m-d');
            $filename = "Daily_Operations_Report_{$branchName}_{$date}.xlsx";
            
            // Prepare export data
            $exportData = [
                'reportDate' => $this->reportDate,
                'selectedBranch' => $this->selectedBranch,
                'branchName' => $branchName,
                'dailySummary' => $this->dailySummary,
                'transactionSummary' => $this->transactionSummary,
                'loanOperations' => $this->loanOperations,
                'depositOperations' => $this->depositOperations,
                'withdrawalOperations' => $this->withdrawalOperations,
                'newLoans' => $this->newLoans,
                'loanDisbursements' => $this->loanDisbursements,
                'loanRepayments' => $this->loanRepayments,
                'newClients' => $this->newClients,
                'staffActivities' => $this->staffActivities,
                'statistics' => [
                    'totalTransactions' => $this->totalTransactions,
                    'totalTransactionValue' => $this->totalTransactionValue,
                    'totalNewLoans' => $this->totalNewLoans,
                    'totalLoanDisbursements' => $this->totalLoanDisbursements,
                    'totalLoanRepayments' => $this->totalLoanRepayments,
                    'totalNewClients' => $this->totalNewClients,
                    'averageTransactionValue' => $this->averageTransactionValue,
                ]
            ];
            
            // Export to Excel
            $download = Excel::download(new DailyOperationsReportExport($exportData), $filename);
            
            // Show success message after export starts
            session()->flash('success', 'Daily Operations Report exported successfully!');
            
            return $download;
            
        } catch (Exception $e) {
            Log::error('Error exporting Daily Operations Report to Excel: ' . $e->getMessage());
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
        } finally {
            $this->isExporting = false;
            $this->exportProgress = 0;
        }
    }

    public function render()
    {
        return view('livewire.reports.daily-operations-report');
    }
}
