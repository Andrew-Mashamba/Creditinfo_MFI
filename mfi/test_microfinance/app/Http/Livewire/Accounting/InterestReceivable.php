<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Services\LoanInterestReceivableService;
use App\Services\LoanInterestAccrualService;
use Carbon\Carbon;

class InterestReceivable extends Component
{
    use WithPagination;

    // Tab control
    public $activeTab = 'overview'; // overview, accruals, income-report, audit-trail

    // Search and filters
    public $search = '';
    public $sortField = 'loan_account_number';
    public $sortDirection = 'asc';
    public $perPage = 25;
    public $filterStatus = 'all'; // all, current, overdue
    public $filterClassification = 'all'; // all, performing, npl
    public $selectedLoan = null;
    public $showDetailModal = false;

    // Date filters
    public $dateFrom;
    public $dateTo;
    public $accrualDate;

    // Summary statistics
    public $totalLoans = 0;
    public $totalInterestReceivable = 0;
    public $overdueInterest = 0;
    public $futureInterest = 0;
    public $collectionRate = 0;

    // Accrual statistics
    public $totalAccruedInterest = 0;
    public $performingInterest = 0;
    public $suspendedInterest = 0;
    public $accrualLoansCount = 0;
    public $nplLoansCount = 0;

    // Income report data
    public $incomeReportPeriod = 'month'; // day, week, month, quarter, year
    public $incomeBreakdown = [];
    public $totalIncome = 0;
    public $interestIncome = 0;
    public $feeIncome = 0;
    public $otherIncome = 0;

    // Audit trail data
    public $auditTrailDateFrom;
    public $auditTrailDateTo;
    public $auditTrailAction = '';
    public $auditTrailSearch = '';
    public $selectedAuditDetail = null;
    public $showAuditDetailModal = false;

    /**
     * Get the loan interest service instance (legacy schedule-based)
     */
    protected function getLoanInterestService()
    {
        return new LoanInterestReceivableService();
    }

    /**
     * Get the loan interest accrual service (new accrual-based)
     */
    protected function getLoanInterestAccrualService()
    {
        return new LoanInterestAccrualService();
    }

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->accrualDate = now()->subDay()->format('Y-m-d');
        $this->auditTrailDateFrom = now()->subDays(30)->format('Y-m-d');
        $this->auditTrailDateTo = now()->format('Y-m-d');
        $this->loadSummaryStatistics();
        $this->loadAccrualStatistics();
        $this->loadIncomeReport();
    }

    public function loadSummaryStatistics()
    {
        try {
            $stats = $this->getLoanInterestService()->getSummaryStatistics();
            $this->totalLoans = $stats['total_loans'];
            $this->totalInterestReceivable = $stats['total_interest_receivable'];
            $this->overdueInterest = $stats['overdue_interest'];
            $this->futureInterest = $stats['future_interest'];
            $this->collectionRate = $stats['collection_rate'];
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error loading summary statistics', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function loadAccrualStatistics()
    {
        try {
            // Get accrual summary for the selected date
            $summary = DB::table('loan_interest_accrual_summaries')
                ->where('accrual_date', $this->accrualDate)
                ->first();

            if ($summary) {
                $this->totalAccruedInterest = $summary->total_interest_accrued ?? 0;
                $this->performingInterest = $summary->performing_interest_accrued ?? 0;
                $this->suspendedInterest = $summary->suspended_interest ?? 0;
                $this->accrualLoansCount = $summary->total_loans_processed ?? 0;
                $this->nplLoansCount = $summary->npl_loans_count ?? 0;
            } else {
                // Get cumulative data if no summary for specific date
                $totals = DB::table('loan_interest_accruals')
                    ->selectRaw('
                        SUM(daily_interest) as total_interest,
                        SUM(CASE WHEN is_suspended = false THEN daily_interest ELSE 0 END) as performing_interest,
                        SUM(CASE WHEN is_suspended = true THEN daily_interest ELSE 0 END) as suspended_interest,
                        COUNT(DISTINCT loan_id) as loans_count,
                        SUM(CASE WHEN is_suspended = true THEN 1 ELSE 0 END) as npl_count
                    ')
                    ->where('accrual_date', $this->accrualDate)
                    ->first();

                $this->totalAccruedInterest = $totals->total_interest ?? 0;
                $this->performingInterest = $totals->performing_interest ?? 0;
                $this->suspendedInterest = $totals->suspended_interest ?? 0;
                $this->accrualLoansCount = $totals->loans_count ?? 0;
                $this->nplLoansCount = $totals->npl_count ?? 0;
            }
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error loading accrual statistics', [
                'error' => $e->getMessage()
            ]);
        }
    }

    public function loadIncomeReport()
    {
        try {
            // Determine date range based on period
            $startDate = $this->getReportStartDate();
            $endDate = $this->dateTo ?? now()->format('Y-m-d');

            // Get interest income from general_ledger
            // Column is record_on_account_number, date is created_at
            $this->interestIncome = DB::table('general_ledger as gl')
                ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                ->where('a.major_category_code', '4000')
                ->where(function($q) {
                    $q->where('a.account_name', 'LIKE', '%INTEREST INCOME%')
                      ->orWhere('a.account_number', 'LIKE', '01014000400%');
                })
                ->whereDate('gl.created_at', '>=', $startDate)
                ->whereDate('gl.created_at', '<=', $endDate)
                ->sum('gl.credit') ?? 0;

            // Get fee income (loan fees and service fees)
            $this->feeIncome = DB::table('general_ledger as gl')
                ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                ->where('a.major_category_code', '4000')
                ->where(function($q) {
                    $q->where('a.account_name', 'LIKE', '%FEE%')
                      ->orWhere('a.account_name', 'LIKE', '%CHARGE%')
                      ->orWhere('a.account_number', 'LIKE', '01014000410%')
                      ->orWhere('a.account_number', 'LIKE', '01014000420%');
                })
                ->whereDate('gl.created_at', '>=', $startDate)
                ->whereDate('gl.created_at', '<=', $endDate)
                ->sum('gl.credit') ?? 0;

            // Get other income
            $this->otherIncome = DB::table('general_ledger as gl')
                ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                ->where('a.major_category_code', '4000')
                ->where(function($q) {
                    $q->where('a.account_number', 'LIKE', '01014000430%')
                      ->orWhere('a.account_number', 'LIKE', '01014000440%')
                      ->orWhere('a.account_number', 'LIKE', '01014000450%')
                      ->orWhere('a.account_number', 'LIKE', '01014000460%')
                      ->orWhere('a.account_number', 'LIKE', '01014000470%')
                      ->orWhere('a.account_number', 'LIKE', '01014000480%');
                })
                ->whereDate('gl.created_at', '>=', $startDate)
                ->whereDate('gl.created_at', '<=', $endDate)
                ->sum('gl.credit') ?? 0;

            $this->totalIncome = $this->interestIncome + $this->feeIncome + $this->otherIncome;

            // Get detailed breakdown by level 2 income accounts
            $this->incomeBreakdown = DB::table('accounts')
                ->where('major_category_code', '4000')
                ->whereRaw('CAST(account_level AS INTEGER) = 2')
                ->where('status', 'ACTIVE')
                ->select('account_number', 'account_name', 'balance')
                ->orderBy('account_number')
                ->get()
                ->map(function($account) use ($startDate, $endDate) {
                    // Get period income for this category (from child accounts)
                    $periodIncome = DB::table('general_ledger as gl')
                        ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
                        ->where('a.parent_account_number', $account->account_number)
                        ->whereDate('gl.created_at', '>=', $startDate)
                        ->whereDate('gl.created_at', '<=', $endDate)
                        ->sum('gl.credit') ?? 0;

                    return [
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'ytd_balance' => $account->balance ?? 0,
                        'period_income' => $periodIncome
                    ];
                })
                ->toArray();

        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error loading income report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function getReportStartDate()
    {
        switch ($this->incomeReportPeriod) {
            case 'day':
                return now()->format('Y-m-d');
            case 'week':
                return now()->startOfWeek()->format('Y-m-d');
            case 'month':
                return now()->startOfMonth()->format('Y-m-d');
            case 'quarter':
                return now()->startOfQuarter()->format('Y-m-d');
            case 'year':
                return now()->startOfYear()->format('Y-m-d');
            default:
                return $this->dateFrom ?? now()->startOfMonth()->format('Y-m-d');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedActiveTab()
    {
        if ($this->activeTab === 'accruals') {
            $this->loadAccrualStatistics();
        } elseif ($this->activeTab === 'income-report') {
            $this->loadIncomeReport();
        } elseif ($this->activeTab === 'audit-trail') {
            $this->resetPage();
        }
    }

    public function updatedAuditTrailDateFrom()
    {
        $this->resetPage();
    }

    public function updatedAuditTrailDateTo()
    {
        $this->resetPage();
    }

    public function updatedAuditTrailAction()
    {
        $this->resetPage();
    }

    public function updatedAuditTrailSearch()
    {
        $this->resetPage();
    }

    public function viewAuditDetail($auditId)
    {
        try {
            $this->selectedAuditDetail = DB::table('income_audit_trail as iat')
                ->leftJoin('users as u', 'iat.performed_by', '=', 'u.id')
                ->leftJoin('other_income as i', 'iat.income_id', '=', 'i.id')
                ->where('iat.id', $auditId)
                ->select([
                    'iat.*',
                    'u.name as user_name',
                    'u.email as user_email',
                    'i.income_code',
                    'i.amount as current_amount',
                    'i.status as current_status'
                ])
                ->first();

            if ($this->selectedAuditDetail) {
                // Decode JSON values for display
                $this->selectedAuditDetail->old_values_decoded = $this->selectedAuditDetail->old_values
                    ? json_decode($this->selectedAuditDetail->old_values, true)
                    : null;
                $this->selectedAuditDetail->new_values_decoded = $this->selectedAuditDetail->new_values
                    ? json_decode($this->selectedAuditDetail->new_values, true)
                    : null;
            }

            $this->showAuditDetailModal = true;
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error loading audit detail', [
                'error' => $e->getMessage(),
                'audit_id' => $auditId
            ]);
            session()->flash('error', 'Error loading audit details: ' . $e->getMessage());
        }
    }

    public function closeAuditDetailModal()
    {
        $this->showAuditDetailModal = false;
        $this->selectedAuditDetail = null;
    }

    public function getIncomeAuditTrail()
    {
        $query = DB::table('income_audit_trail as iat')
            ->leftJoin('users as u', 'iat.performed_by', '=', 'u.id')
            ->leftJoin('other_income as i', 'iat.income_id', '=', 'i.id')
            ->select([
                'iat.*',
                'u.name as user_name',
                'u.email as user_email',
                'i.income_code',
                'i.income_source',
                'i.amount',
                'i.income_category',
                'i.received_from'
            ]);

        // Apply date filters
        if ($this->auditTrailDateFrom) {
            $query->whereDate('iat.created_at', '>=', $this->auditTrailDateFrom);
        }
        if ($this->auditTrailDateTo) {
            $query->whereDate('iat.created_at', '<=', $this->auditTrailDateTo);
        }

        // Apply action filter
        if ($this->auditTrailAction) {
            $query->where('iat.action', $this->auditTrailAction);
        }

        // Apply search filter
        if ($this->auditTrailSearch) {
            $query->where(function($q) {
                $q->where('i.income_code', 'LIKE', '%' . $this->auditTrailSearch . '%')
                  ->orWhere('i.income_source', 'LIKE', '%' . $this->auditTrailSearch . '%')
                  ->orWhere('i.received_from', 'LIKE', '%' . $this->auditTrailSearch . '%')
                  ->orWhere('u.name', 'LIKE', '%' . $this->auditTrailSearch . '%')
                  ->orWhere('iat.description', 'LIKE', '%' . $this->auditTrailSearch . '%');
            });
        }

        return $query->orderBy('iat.created_at', 'desc')->paginate($this->perPage);
    }

    public function getGLIncomeTransactions()
    {
        // Get GL transactions for income accounts with reversal tracking
        return DB::table('general_ledger as gl')
            ->join('accounts as a', 'gl.record_on_account_number', '=', 'a.account_number')
            ->leftJoin('users as u', 'gl.sender_id', '=', 'u.id')
            ->where('a.major_category_code', '4000')
            ->whereDate('gl.created_at', '>=', $this->auditTrailDateFrom)
            ->whereDate('gl.created_at', '<=', $this->auditTrailDateTo)
            ->when($this->auditTrailSearch, function($q) {
                $q->where(function($q2) {
                    $q2->where('gl.reference_number', 'LIKE', '%' . $this->auditTrailSearch . '%')
                       ->orWhere('gl.narration', 'LIKE', '%' . $this->auditTrailSearch . '%')
                       ->orWhere('a.account_name', 'LIKE', '%' . $this->auditTrailSearch . '%');
                });
            })
            ->select([
                'gl.id',
                'gl.reference_number',
                'gl.narration',
                'gl.credit',
                'gl.debit',
                'gl.trans_status',
                'gl.created_at',
                'a.account_number',
                'a.account_name',
                'u.name as created_by_name'
            ])
            ->orderBy('gl.created_at', 'desc')
            ->limit(100)
            ->get();
    }

    public function updatedAccrualDate()
    {
        $this->loadAccrualStatistics();
    }

    public function updatedIncomeReportPeriod()
    {
        $this->loadIncomeReport();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function viewLoanDetails($loanId)
    {
        try {
            $this->selectedLoan = $this->getLoanInterestService()->calculateLoanInterestReceivable($loanId);
            $this->showDetailModal = true;
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error loading loan details', [
                'error' => $e->getMessage(),
                'loan_id' => $loanId
            ]);
            session()->flash('error', 'Error loading loan details: ' . $e->getMessage());
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedLoan = null;
    }

    public function runDailyAccrual()
    {
        try {
            $service = $this->getLoanInterestAccrualService();
            $result = $service->processDailyAccrual(Carbon::parse($this->accrualDate));

            if ($result['status'] === 'success') {
                $this->loadAccrualStatistics();
                session()->flash('success', 'Daily interest accrual completed. ' .
                    ($result['statistics']['total_loans'] ?? 0) . ' loans processed, ' .
                    'TZS ' . number_format($result['statistics']['total_interest_accrued'] ?? 0, 2) . ' accrued.');
            } else {
                session()->flash('error', 'Accrual failed: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error running daily accrual', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error running accrual: ' . $e->getMessage());
        }
    }

    public function exportToExcel()
    {
        try {
            $fileName = 'interest_receivable_' . now()->format('Y-m-d_His') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\InterestReceivableExport($this->search, $this->filterStatus),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error exporting to Excel', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    public function exportIncomeReport()
    {
        try {
            $fileName = 'income_report_' . now()->format('Y-m-d_His') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\IncomeReportExport(
                    $this->getReportStartDate(),
                    $this->dateTo ?? now()->format('Y-m-d'),
                    $this->incomeBreakdown
                ),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error exporting income report', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error exporting report: ' . $e->getMessage());
        }
    }

    public function render()
    {
        try {
            // Interest Receivable Data (legacy schedule-based)
            $data = $this->getLoanInterestService()->calculateInterestReceivable();
            $loans = collect($data['loans']);

            // Apply search filter
            if ($this->search) {
                $loans = $loans->filter(function($loan) {
                    return stripos($loan->loan_account_number, $this->search) !== false ||
                           stripos($loan->client_number, $this->search) !== false ||
                           stripos($loan->loan_id, $this->search) !== false;
                });
            }

            // Apply status filter
            if ($this->filterStatus === 'overdue') {
                $loans = $loans->filter(function($loan) {
                    return $loan->overdue_interest > 0;
                });
            } elseif ($this->filterStatus === 'current') {
                $loans = $loans->filter(function($loan) {
                    return $loan->overdue_interest == 0 && $loan->total_interest_receivable > 0;
                });
            }

            // Apply sorting
            $loans = $this->sortDirection === 'asc'
                ? $loans->sortBy($this->sortField)
                : $loans->sortByDesc($this->sortField);

            // Convert to paginated collection
            $currentPage = $this->page ?? 1;
            $offset = ($currentPage - 1) * $this->perPage;
            $paginatedLoans = $loans->slice($offset, $this->perPage)->values();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedLoans,
                $loans->count(),
                $this->perPage,
                $currentPage,
                ['path' => request()->url()]
            );

            // Get aged receivables
            $agedReceivables = $this->getLoanInterestService()->getAgedInterestReceivable();

            // Get daily accruals for the accruals tab
            $dailyAccruals = collect();
            if ($this->activeTab === 'accruals') {
                $dailyAccruals = DB::table('loan_interest_accruals as lia')
                    ->leftJoin('loans as l', 'lia.loan_id', '=', 'l.loan_id')
                    ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
                    ->where('lia.accrual_date', $this->accrualDate)
                    ->select([
                        'lia.*',
                        'l.loan_account_number',
                        'l.principle',
                        'c.first_name',
                        'c.last_name',
                        'c.client_number'
                    ])
                    ->orderBy('lia.daily_interest', 'desc')
                    ->paginate($this->perPage);
            }

            // Get accrual history for chart
            $accrualHistory = DB::table('loan_interest_accrual_summaries')
                ->where('accrual_date', '>=', now()->subDays(30)->format('Y-m-d'))
                ->orderBy('accrual_date')
                ->get();

            // Get audit trail data for the audit-trail tab
            $incomeAuditTrail = collect();
            $glIncomeTransactions = collect();
            $auditStats = [
                'total_records' => 0,
                'creates' => 0,
                'updates' => 0,
                'reversals' => 0
            ];

            if ($this->activeTab === 'audit-trail') {
                $incomeAuditTrail = $this->getIncomeAuditTrail();
                $glIncomeTransactions = $this->getGLIncomeTransactions();

                // Calculate audit statistics
                $auditStats = DB::table('income_audit_trail')
                    ->whereDate('created_at', '>=', $this->auditTrailDateFrom)
                    ->whereDate('created_at', '<=', $this->auditTrailDateTo)
                    ->selectRaw("
                        COUNT(*) as total_records,
                        SUM(CASE WHEN action = 'CREATE' THEN 1 ELSE 0 END) as creates,
                        SUM(CASE WHEN action = 'UPDATE' THEN 1 ELSE 0 END) as updates,
                        SUM(CASE WHEN action = 'REVERSE' THEN 1 ELSE 0 END) as reversals
                    ")
                    ->first();
            }

            return view('livewire.accounting.interest-receivable', [
                'interestReceivables' => $paginator,
                'summary' => $data['summary'],
                'agedReceivables' => $agedReceivables,
                'dailyAccruals' => $dailyAccruals,
                'accrualHistory' => $accrualHistory,
                'incomeAuditTrail' => $incomeAuditTrail,
                'glIncomeTransactions' => $glIncomeTransactions,
                'auditStats' => $auditStats
            ]);

        } catch (\Exception $e) {
            \Log::error('InterestReceivable: Error rendering component', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading interest receivable data: ' . $e->getMessage());

            return view('livewire.accounting.interest-receivable', [
                'interestReceivables' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'summary' => [
                    'total_loans' => 0,
                    'total_interest_receivable' => 0,
                    'overdue_interest' => 0,
                    'future_interest' => 0
                ],
                'agedReceivables' => [
                    'current' => 0,
                    '31_60_days' => 0,
                    '61_90_days' => 0,
                    '91_180_days' => 0,
                    'over_180_days' => 0,
                    'total' => 0
                ],
                'dailyAccruals' => collect(),
                'accrualHistory' => collect(),
                'incomeAuditTrail' => collect(),
                'glIncomeTransactions' => collect(),
                'auditStats' => (object)['total_records' => 0, 'creates' => 0, 'updates' => 0, 'reversals' => 0]
            ]);
        }
    }
}
