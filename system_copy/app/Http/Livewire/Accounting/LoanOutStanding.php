<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Services\LoanOutstandingService;
use Carbon\Carbon;

class LoanOutStanding extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'loan_account_number';
    public $sortDirection = 'asc';
    public $perPage = 25;
    public $filterStatus = 'all'; // all, performing, overdue, high_risk
    public $selectedLoan = null;
    public $showDetailModal = false;

    // Summary statistics
    public $totalLoans = 0;
    public $totalOutstanding = 0;
    public $totalOverdue = 0;
    public $totalPrincipalOutstanding = 0;
    public $totalInterestOutstanding = 0;
    public $overdueLoansCount = 0;
    public $portfolioAtRisk = 0;

    /**
     * Get the loan outstanding service instance
     *
     * @return LoanOutstandingService
     */
    protected function getLoanOutstandingService()
    {
        return new LoanOutstandingService();
    }

    public function mount()
    {
        $this->loadSummaryStatistics();
    }

    public function loadSummaryStatistics()
    {
        try {
            $stats = $this->getLoanOutstandingService()->getSummaryStatistics();
            $this->totalLoans = $stats['total_loans'];
            $this->totalOutstanding = $stats['total_outstanding'];
            $this->totalOverdue = $stats['total_overdue'];
            $this->totalPrincipalOutstanding = $stats['total_principal_outstanding'];
            $this->totalInterestOutstanding = $stats['total_interest_outstanding'];
            $this->overdueLoansCount = $stats['overdue_loans'];
            $this->portfolioAtRisk = $stats['portfolio_at_risk'];
        } catch (\Exception $e) {
            \Log::error('LoanOutStanding: Error loading summary statistics', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading summary statistics: ' . $e->getMessage());
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
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
            $this->selectedLoan = $this->getLoanOutstandingService()->calculateLoanOutstandingDetail($loanId);
            $this->showDetailModal = true;
        } catch (\Exception $e) {
            \Log::error('LoanOutStanding: Error loading loan details', [
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

    public function exportToExcel()
    {
        try {
            $fileName = 'loan_outstanding_' . now()->format('Y-m-d_His') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\LoanOutstandingExport($this->search, $this->filterStatus),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('LoanOutStanding: Error exporting to Excel', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error exporting data: ' . $e->getMessage());
        }
    }

    public function render()
    {
        try {
            $data = $this->getLoanOutstandingService()->calculateLoanOutstanding();
            $loans = collect($data['loans']);

            // Apply search filter
            if ($this->search) {
                $loans = $loans->filter(function($loan) {
                    return stripos($loan->loan_account_number, $this->search) !== false ||
                           stripos($loan->client_number, $this->search) !== false ||
                           stripos($loan->client_name, $this->search) !== false ||
                           stripos($loan->loan_id, $this->search) !== false;
                });
            }

            // Apply status filter
            if ($this->filterStatus === 'overdue') {
                $loans = $loans->filter(function($loan) {
                    return $loan->is_overdue;
                });
            } elseif ($this->filterStatus === 'performing') {
                $loans = $loans->filter(function($loan) {
                    return !$loan->is_overdue;
                });
            } elseif ($this->filterStatus === 'high_risk') {
                $loans = $loans->filter(function($loan) {
                    return $loan->days_in_arrears > 90;
                });
            }

            // Apply sorting
            $loans = $this->sortDirection === 'asc'
                ? $loans->sortBy($this->sortField)
                : $loans->sortByDesc($this->sortField);

            // Convert to paginated collection
            $currentPage = $this->page ?: 1;
            $offset = ($currentPage - 1) * $this->perPage;
            $paginatedLoans = $loans->slice($offset, $this->perPage)->values();

            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedLoans,
                $loans->count(),
                $this->perPage,
                $currentPage,
                ['path' => request()->url()]
            );

            // Get aged outstanding
            $agedOutstanding = $this->getLoanOutstandingService()->getAgedOutstanding();

            return view('livewire.accounting.loan-out-standing', [
                'loanOutstandings' => $paginator,
                'summary' => $data['summary'],
                'agedOutstanding' => $agedOutstanding
            ]);

        } catch (\Exception $e) {
            \Log::error('LoanOutStanding: Error rendering component', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Error loading loan outstanding data: ' . $e->getMessage());

            return view('livewire.accounting.loan-out-standing', [
                'loanOutstandings' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->perPage),
                'summary' => [
                    'total_loans' => 0,
                    'total_outstanding' => 0,
                    'total_overdue' => 0,
                    'total_principal_outstanding' => 0,
                    'total_interest_outstanding' => 0
                ],
                'agedOutstanding' => [
                    'standard' => 0,
                    'watch' => 0,
                    'substandard' => 0,
                    'doubtful' => 0,
                    'loss' => 0,
                    'total' => 0
                ]
            ]);
        }
    }
}
