<?php

namespace App\Http\Livewire\Loans;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;

class SearchLoans extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $loanTypeFilter = '';
    public $perPage = 15;
    public $selectedLoan = null;
    public $showScheduleModal = false;
    public $loanSchedules = [];

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'loanTypeFilter' => ['except' => '']
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingLoanTypeFilter()
    {
        $this->resetPage();
    }

    public function viewSchedule($loanId)
    {
        $this->selectedLoan = LoansModel::with('client')->find($loanId);

        if ($this->selectedLoan) {
            // Use the loan_id field (string) from the loan record
            $this->loanSchedules = loans_schedules::where('loan_id', $this->selectedLoan->loan_id)
                ->orderBy('id')
                ->get();

            $this->showScheduleModal = true;
        }
    }

    public function closeScheduleModal()
    {
        $this->showScheduleModal = false;
        $this->selectedLoan = null;
        $this->loanSchedules = [];
    }

    public function render()
    {
        $searchTerm = '%' . strtolower($this->search) . '%';

        $loans = DB::table('loans')
            ->select(
                'loans.id',
                'loans.loan_id',
                'loans.loan_account_number',
                'loans.client_number',
                'loans.principle',
                'loans.interest',
                'loans.loan_sub_product',
                'loans.branch_id',
                'loans.loan_type_2',
                'loans.supervisor_id',
                'loans.status',
                DB::raw("CONCAT(clients.first_name, ' ', clients.middle_name, ' ', clients.last_name) as client_name"),
                DB::raw('loan_sub_products.sub_product_name as loan_product_name'),
                DB::raw("CONCAT(employees.first_name, ' ', employees.middle_name, ' ', employees.last_name) as loan_officer_name"),
                // Calculate outstanding balance from schedules
                DB::raw('(SELECT COALESCE(SUM(principle), 0) FROM loans_schedules WHERE loan_id = loans.loan_id AND completion_status = \'ACTIVE\') as outstanding_balance'),
                DB::raw('(SELECT COALESCE(SUM(interest), 0) FROM loans_schedules WHERE loan_id = loans.loan_id AND completion_status = \'ACTIVE\') as outstanding_interest'),
                DB::raw('(SELECT COUNT(*) FROM loans_schedules WHERE loan_id = loans.loan_id AND completion_status = \'ACTIVE\') as pending_installments')
            )
            ->join('clients', 'clients.client_number', '=', 'loans.client_number')
            ->leftJoin('loan_sub_products', 'loan_sub_products.sub_product_id', '=', 'loans.loan_sub_product')
            ->leftJoin('employees', 'employees.id', '=', 'loans.supervisor_id')
            ->when($this->search, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where(DB::raw('LOWER(loans.loan_account_number)::text'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(loans.client_number::text)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(loans.principle::text)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(clients.first_name)::text'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(clients.middle_name)::text'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(clients.last_name)::text'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(loan_sub_products.sub_product_name)::text'), 'LIKE', $searchTerm);
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('loans.status', $this->statusFilter);
            })
            ->when($this->loanTypeFilter, function ($query) {
                $query->where('loans.loan_type_2', $this->loanTypeFilter);
            })
            ->orderBy('loans.id', 'desc')
            ->paginate($this->perPage);

        // Get filter options
        $statuses = DB::table('loans')
            ->select('status')
            ->distinct()
            ->whereNotNull('status')
            ->pluck('status');

        $loanTypes = DB::table('loans')
            ->select('loan_type_2')
            ->distinct()
            ->whereNotNull('loan_type_2')
            ->pluck('loan_type_2');

        return view('livewire.loans.search-loans', [
            'loans' => $loans,
            'statuses' => $statuses,
            'loanTypes' => $loanTypes
        ]);
    }
}
