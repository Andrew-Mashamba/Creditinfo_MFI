<?php

namespace App\Http\Livewire\Loans;

use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Loan_sub_products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;

class DeclinedLoans extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $branchFilter = '';
    public $loanOfficerFilter = '';
    public $dateFilter = '';
    public $productFilter = '';
    public $rejectionReasonFilter = '';

    // Pagination
    public $perPage = 20;
    public $sortField = 'updated_at';
    public $sortDirection = 'desc';

    // Statistics
    public $totalDeclined = 0;
    public $declinedToday = 0;
    public $declinedThisWeek = 0;
    public $declinedThisMonth = 0;

    // Modal States
    public $showDetailsModal = false;
    public $selectedLoan = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'branchFilter' => ['except' => ''],
        'loanOfficerFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
        'productFilter' => ['except' => ''],
        'rejectionReasonFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'refreshDeclinedLoansTable' => '$refresh',
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        $this->totalDeclined = LoansModel::where('status', 'REJECTED')->count();
        $this->declinedToday = LoansModel::where('status', 'REJECTED')
            ->whereDate('updated_at', Carbon::today())
            ->count();
        $this->declinedThisWeek = LoansModel::where('status', 'REJECTED')
            ->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        $this->declinedThisMonth = LoansModel::where('status', 'REJECTED')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedBranchFilter()
    {
        $this->resetPage();
    }

    public function updatedLoanOfficerFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->resetPage();
    }

    public function updatedProductFilter()
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
        $this->search = '';
        $this->branchFilter = '';
        $this->loanOfficerFilter = '';
        $this->dateFilter = '';
        $this->productFilter = '';
        $this->rejectionReasonFilter = '';
        $this->resetPage();
    }

    public function viewDetails($loanId)
    {
        $this->selectedLoan = LoansModel::with(['client'])
            ->find($loanId);
        
        // Get product information
        if ($this->selectedLoan && $this->selectedLoan->loan_sub_product) {
            $product = Loan_sub_products::where('sub_product_id', $this->selectedLoan->loan_sub_product)->first();
            $this->selectedLoan->product_name = $product ? $product->sub_product_name : 'N/A';
        }
        
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedLoan = null;
    }

    public function exportToExcel()
    {
        // Export functionality - implement based on your export requirements
        session()->flash('message', 'Export functionality will be implemented.');
    }

    public function exportToPdf()
    {
        // Export functionality - implement based on your export requirements
        session()->flash('message', 'Export functionality will be implemented.');
    }

    public function getLoansProperty()
    {
        $query = LoansModel::query()
            ->where('status', 'REJECTED')
            ->with(['client']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('loan_id', 'like', '%' . $this->search . '%')
                    ->orWhere('loan_account_number', 'like', '%' . $this->search . '%')
                    ->orWhere('principle', 'like', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('client_number', 'like', '%' . $this->search . '%');
                    });
            });
        }

        // Apply branch filter
        if (!empty($this->branchFilter)) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Apply loan officer filter
        if (!empty($this->loanOfficerFilter)) {
            $query->where('loan_officer', $this->loanOfficerFilter);
        }

        // Apply date filter
        if (!empty($this->dateFilter)) {
            $query->whereDate('updated_at', $this->dateFilter);
        }

        // Apply product filter
        if (!empty($this->productFilter)) {
            $query->where('loan_sub_product', $this->productFilter);
        }

        // Apply rejection reason filter if available
        if (!empty($this->rejectionReasonFilter)) {
            $query->where('rejection_reason', 'like', '%' . $this->rejectionReasonFilter . '%');
        }

        // Apply sorting
        $query->orderBy($this->sortField, $this->sortDirection);

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        $loans = $this->loans;
        
        // Get filter options
        $branches = BranchesModel::where('status', 'ACTIVE')->get();
        $loanOfficers = Employee::where('employee_status', 'ACTIVE')->get();
        $products = Loan_sub_products::where('sub_product_status', 'ACTIVE')->get();
        
        // Get unique rejection reasons
        $rejectionReasons = LoansModel::where('status', 'REJECTED')
            ->whereNotNull('rejection_reason')
            ->distinct('rejection_reason')
            ->pluck('rejection_reason');

        return view('livewire.loans.declined-loans', [
            'loans' => $loans,
            'branches' => $branches,
            'loanOfficers' => $loanOfficers,
            'products' => $products,
            'rejectionReasons' => $rejectionReasons,
        ]);
    }
}