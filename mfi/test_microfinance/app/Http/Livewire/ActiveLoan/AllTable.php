<?php

namespace App\Http\Livewire\ActiveLoan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\LoansModel;
use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\ClientsModel;
use Carbon\Carbon;


class AllTable extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $selectedBranch = '';
    public $selectedStatus = '';
    public $selectedProduct = '';
    public $selectedOfficer = '';
    public $dateRange = '';
    public $amountRange = '';
    public $arrearsFilter = '';

    // Pagination and Sorting
    public $perPage = 25;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // UI State
    public $showFilters = false;
    public $selectedLoans = [];
    public $bulkAction = '';
    public $showLoanDetails = false;
    public $selectedLoanId = null;
    
    // Modal States
    public $showEditModal = false;
    public $showDetailsModal = false;
    public $selectedLoan = null;
    public $editForm = [];

    // Loan Details Modal
    public $loanDetails = null;
    public $loanSchedule = null;
    public $loanStatusHistory = null;
    public $loanModifications = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedBranch' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
        'selectedProduct' => ['except' => ''],
        'selectedOfficer' => ['except' => ''],
        'dateRange' => ['except' => ''],
        'amountRange' => ['except' => ''],
        'arrearsFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'refreshTable' => '$refresh',
        'loanUpdated' => '$refresh',
        'bulkActionPerformed' => '$refresh'
    ];

    public function mount()
    {
        $this->sortField = 'created_at';
        $this->sortDirection = 'desc';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedBranch()
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus()
    {
        $this->resetPage();
    }

    public function updatingSelectedProduct()
    {
        $this->resetPage();
    }

    public function updatingSelectedOfficer()
    {
        $this->resetPage();
    }

    public function updatingDateRange()
    {
        $this->resetPage();
    }

    public function updatingAmountRange()
    {
        $this->resetPage();
    }

    public function updatingArrearsFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'selectedBranch', 'selectedStatus', 'selectedProduct', 
            'selectedOfficer', 'dateRange', 'amountRange', 'arrearsFilter'
        ]);
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function selectAll()
    {
        if (count($this->selectedLoans) === $this->loans->count()) {
            $this->selectedLoans = [];
        } else {
            $this->selectedLoans = $this->loans->pluck('id')->map(fn($id) => (string) $id);
        }
    }

    public function performBulkAction()
    {
        if (empty($this->selectedLoans) || empty($this->bulkAction)) {
            return;
        }

        switch ($this->bulkAction) {
            case 'export':
                $this->exportSelectedLoans();
                break;
            case 'status_change':
                $this->bulkStatusChange();
                break;
            case 'assign_officer':
                $this->bulkAssignOfficer();
                break;
        }

        $this->selectedLoans = [];
        $this->bulkAction = '';
        $this->emit('bulkActionPerformed');
    }

    public function viewLoanDetails($loanId)
    {
        $this->selectedLoanId = $loanId;
        $this->loadLoanDetails($loanId);
        $this->showLoanDetails = true;
    }

    public function loadLoanDetails($loanId)
    {
        // Load loan details
        $this->loanDetails = LoansModel::with(['client', 'loanBranch', 'schedules'])->where('status', 'ACTIVE')
            ->find($loanId);

        // Load loan schedule - try both loan_id string and numeric id since data is inconsistent
        if ($this->loanDetails) {
            $this->loanSchedule = DB::table('loans_schedules')
                ->where(function($query) use ($loanId) {
                    $query->where('loan_id', $this->loanDetails->loan_id)
                          ->orWhere('loan_id', (string)$loanId)
                          ->orWhere('loan_id', (string)$this->loanDetails->id);
                })
                ->orderBy('installment_date', 'asc')
                ->get();
        } else {
            $this->loanSchedule = collect();
        }

        // Load status history (if table exists) - using the actual loan_id string
        if ($this->loanDetails) {
            try {
                $this->loanStatusHistory = DB::table('loan_status_changes')
                    ->where('loan_id', $this->loanDetails->loan_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $this->loanStatusHistory = collect();
            }

            // Load modifications/restructures (if table exists) - using the actual loan_id string
            try {
                $this->loanModifications = DB::table('loan_restructures')
                    ->where('loan_id', $this->loanDetails->loan_id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                $this->loanModifications = collect();
            }
        } else {
            $this->loanStatusHistory = collect();
            $this->loanModifications = collect();
        }
    }

    public function closeLoanDetails()
    {
        $this->showLoanDetails = false;
        $this->selectedLoanId = null;
        $this->loanDetails = null;
        $this->loanSchedule = null;
        $this->loanStatusHistory = null;
        $this->loanModifications = null;
    }

    public function editLoan($loanId)
    {
        $this->selectedLoan = LoansModel::with(['client', 'loanBranch', 'loanProduct'])->where('status', 'ACTIVE')->find($loanId);
        if ($this->selectedLoan) {
            $this->editForm = [
                'loan_account_number' => $this->selectedLoan->loan_account_number,
                'status' => $this->selectedLoan->status,
                'principle' => $this->selectedLoan->principle,
                'interest_rate' => $this->selectedLoan->interest_rate,
                'tenure' => $this->selectedLoan->tenure,
                'branch_id' => $this->selectedLoan->branch_id,
            ];
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->selectedLoan = null;
        $this->editForm = [];
    }

    public function updateLoan()
    {
        if ($this->selectedLoan) {
            $this->selectedLoan->update($this->editForm);
            $this->closeEditModal();
            $this->emit('loanUpdated');
        }
    }

    public function showLoanDetails($loanId)
    {
        $this->selectedLoan = LoansModel::with(['client', 'loanBranch', 'loanProduct'])->where('status', 'ACTIVE')->find($loanId);
        if ($this->selectedLoan) {
            $this->loadLoanDetails($loanId);
            $this->showDetailsModal = true;
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLoan = null;
        $this->loanDetails = null;
        $this->loanSchedule = null;
        $this->loanStatusHistory = null;
        $this->loanModifications = null;
    }

    public function downloadRepaymentSchedule($loanId)
    {
        // Implementation for downloading repayment schedule
        $this->emit('downloadSchedule', $loanId);
    }

    public function exportSelectedLoans()
    {
        // Implementation for exporting selected loans
        $this->emit('exportLoans', $this->selectedLoans);
    }

    public function bulkStatusChange()
    {
        // Implementation for bulk status change
        $this->emit('bulkStatusChange', $this->selectedLoans);
    }

    public function bulkAssignOfficer()
    {
        // Implementation for bulk officer assignment
        $this->emit('bulkAssignOfficer', $this->selectedLoans);
    }

    public function getLoansProperty()
    {
        $query = LoansModel::query()
            ->with(['client', 'loanBranch', 'loanProduct'])
            ->where('status', 'ACTIVE')
            ->select([
                'loans.*',
                DB::raw('(SELECT COUNT(*) FROM loans_schedules WHERE loans_schedules.loan_id = CAST(loans.id AS VARCHAR) AND completion_status != \'PAID\') as unpaid_installments'),
                DB::raw('(SELECT SUM(amount_in_arrears) FROM loans_schedules WHERE loans_schedules.loan_id = CAST(loans.id AS VARCHAR)) as total_arrears'),
                DB::raw('(SELECT MAX(installment_date) FROM loans_schedules WHERE loans_schedules.loan_id = CAST(loans.id AS VARCHAR) AND completion_status = \'PAID\') as last_payment_date')
            ]);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('loan_account_number', 'like', '%' . $this->search . '%')
                  ->orWhere('client_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function ($clientQuery) {
                      $clientQuery->where('first_name', 'like', '%' . $this->search . '%')
                                 ->orWhere('last_name', 'like', '%' . $this->search . '%')
                                 ->orWhere('phone_number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        if ($this->selectedStatus) {
            $query->where('status', $this->selectedStatus);
        }

        if ($this->selectedProduct) {
            $query->where('loan_sub_product', $this->selectedProduct);
        }

        if ($this->selectedOfficer) {
            $query->where('supervisor_id', $this->selectedOfficer);
        }

        if ($this->dateRange) {
            $dates = explode(' to ', $this->dateRange);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', $dates);
            }
        }

        if ($this->amountRange) {
            $amounts = explode(' - ', $this->amountRange);
            if (count($amounts) === 2) {
                $query->whereBetween('principle', $amounts);
            }
        }

        if ($this->arrearsFilter) {
            switch ($this->arrearsFilter) {
                case 'current':
                    $query->where('days_in_arrears', 0);
                    break;
                case '1-30':
                    $query->whereBetween('days_in_arrears', [1, 30]);
                    break;
                case '31-60':
                    $query->whereBetween('days_in_arrears', [31, 60]);
                    break;
                case '61-90':
                    $query->whereBetween('days_in_arrears', [61, 90]);
                    break;
                case '90+':
                    $query->where('days_in_arrears', '>', 90);
                    break;
            }
        }

        // Apply sorting
        if ($this->sortField && $this->sortDirection) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function getBranchesProperty()
    {
        return BranchesModel::orderBy('name')->get();
    }

    public function getLoanProductsProperty()
    {
        return DB::table('loan_sub_products')
            ->select('sub_product_id', 'sub_product_name')
            ->orderBy('sub_product_name')
            ->get();
    }

    public function getLoanOfficersProperty()
    {
        return Employee::orderBy('first_name')->get();
    }

    public function getStatusOptionsProperty()
    {
        return [
            'ACTIVE' => 'Active',
            'IN_ARREAR' => 'In Arrears',
            'DELINQUENT' => 'Delinquent',
            'RECOVERY' => 'Recovery',
            'WRITEN_OFF' => 'Written Off',
            'CLOSED' => 'Closed'
        ];
    }

    public function getArrearsFilterOptionsProperty()
    {
        return [
            'current' => 'Current (0 days)',
            '1-30' => '1-30 days',
            '31-60' => '31-60 days',
            '61-90' => '61-90 days',
            '90+' => '90+ days'
        ];
    }



    public function render()
    {
        return view('livewire.active-loan.all-table', [
            'loans' => $this->loans,
            'branches' => $this->branches,
            'loanProducts' => $this->loanProducts,
            'loanOfficers' => $this->loanOfficers,
            'statusOptions' => $this->statusOptions,
            'arrearsFilterOptions' => $this->arrearsFilterOptions,
        ]);
    }
}
