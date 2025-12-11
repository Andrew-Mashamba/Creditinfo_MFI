<?php

namespace App\Http\Livewire\Loans;

use App\Models\BranchesModel;
use App\Models\Employee;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;

class NewLoansTable extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $statusFilter = '';
    public $branchFilter = '';
    public $loanOfficerFilter = '';
    public $dateFilter = '';

    // Pagination
    public $perPage = 20;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Statistics
    public $totalLoans = 0;
    public $pendingLoans = 0;
    public $approvedLoans = 0;
    public $rejectedLoans = 0;

    // Modal States
    public $showDeleteModal = false;
    public $loanToDelete = null;
    public $clientToDelete = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'branchFilter' => ['except' => ''],
        'loanOfficerFilter' => ['except' => ''],
        'dateFilter' => ['except' => ''],
    ];

    protected $listeners = [
        'refreshLoansTable' => '$refresh',
        'loanDeleted' => '$refresh',
        'loanUpdated' => '$refresh'
    ];

    public function mount()
    {
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        $this->totalLoans = LoansModel::whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])->count();
        $this->pendingLoans = LoansModel::whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])->where('status', 'PENDING')->count();
        $this->approvedLoans = LoansModel::whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])->where('status', 'APPROVED')->count();
        $this->rejectedLoans = LoansModel::whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])->where('status', 'REJECTED')->count();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
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
        $this->reset(['search', 'statusFilter', 'branchFilter', 'loanOfficerFilter', 'dateFilter']);
        $this->resetPage();
    }

    public function viewLoan($id)
    {
        $loan = LoansModel::findOrFail($id);
        
        Session::forget(['currentloanClient', 'currentloanID']);
        Session::put('currentloanClient', $loan->client_number);
        Session::put('currentloanID', $id);

        $this->emit('viewLoanDetails');
        $this->emit('refreshLoansComponent');
    }

    public function editLoan($id)
    {
        $loan = LoansModel::findOrFail($id);
        
        Session::forget(['currentloanClient', 'currentloanID']);
        Session::put('currentloanClient', $loan->client_number);
        Session::put('currentloanID', $id);

        $this->emit('editLoanDetails');
        $this->emit('refreshLoansComponent');
    }

    public function deleteLoan($id)
    {
        $loan = LoansModel::findOrFail($id);
        $this->loanToDelete = $loan;
        $this->clientToDelete = $loan->client_number;
        $this->showDeleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->loanToDelete) {
            try {
                $this->loanToDelete->delete();
                
                Session::forget(['currentloanClient', 'currentloanID']);
                
                $this->emit('loanDeleted');
                $this->loadStatistics();
                
                session()->flash('message', 'Loan application deleted successfully.');
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to delete loan application.');
            }
        }
        
        $this->closeDeleteModal();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->loanToDelete = null;
        $this->clientToDelete = null;
    }

    public function getLoansProperty()
    {
        $query = LoansModel::with(['client', 'loanProduct', 'loanBranch'])
            ->whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])
            ->where('status', '!=', 'ACTIVE');

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('loan_id', 'like', $searchTerm)
                  ->orWhere('loan_account_number', 'like', $searchTerm)
                  ->orWhere('client_number', 'like', $searchTerm)
                  ->orWhere('status', 'like', $searchTerm)
                  ->orWhere('principle', 'like', $searchTerm)
                  ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('first_name', 'like', $searchTerm)
                                 ->orWhere('middle_name', 'like', $searchTerm)
                                 ->orWhere('last_name', 'like', $searchTerm)
                                 ->orWhere('phone_number', 'like', $searchTerm)
                                 ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhereHas('loanProduct', function ($productQuery) use ($searchTerm) {
                      $productQuery->where('sub_product_name', 'like', $searchTerm);
                  });
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply branch filter
        if (!empty($this->branchFilter)) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Apply loan officer filter
        if (!empty($this->loanOfficerFilter)) {
            $query->where('supervisor_id', $this->loanOfficerFilter);
        }

        // Apply date filter
        if (!empty($this->dateFilter)) {
            $date = Carbon::parse($this->dateFilter);
            $query->whereDate('created_at', $date);
        }

        return $query->orderBy($this->sortField, $this->sortDirection)
                    ->paginate($this->perPage);
    }

    public function getBranchesProperty()
    {
        return BranchesModel::orderBy('name')->get();
    }

    public function getLoanOfficersProperty()
    {
        // Get role IDs for 'Loan Officer' and 'Credit Manager'
        $roleIds = Role::whereIn('name', ['Loan Officer', 'Credit Manager'])->pluck('id');

        // Get user IDs with those roles
        $userIds = DB::table('user_roles')
            ->whereIn('role_id', $roleIds)
            ->pluck('user_id');

        // Get employee IDs from users
        $employeeIds = User::whereIn('id', $userIds)
            ->pluck('employeeId');

        // Return employees whose id is in $employeeIds
        return Employee::whereIn('id', $employeeIds)
            ->orderBy('first_name')
            ->get();
    }

    public function getStatusOptionsProperty()
    {
        return [
            'PENDING' => 'PENDING',
            'APPROVED' => 'APPROVED',
            'REJECTED' => 'REJECTED',
            'UNDER_REVIEW' => 'UNDER REVIEW',
            'ONPROGRESS' => 'IN PROGRESS',
            'PENDING-WITH-EXCEPTIONS' => 'EXCEPTION QUEUE',
            'PENDING-EXCEPTIONS' => 'EXCEPTION QUEUE',
        ];
    }

    public function exportToExcel()
    {
        $loans = $this->getLoansForExport();
        
        $filename = 'loan_applications_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return response()->streamDownload(function () use ($loans) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, [
                'Loan ID',
                'Product',
                'Client Name',
                'Client ID',
                'Loan Type',
                'Amount (TZS)',
                'Term (Months)',
                'Current Stage',
                'Status',
                'Created Date'
            ]);
            
            // Add data
            foreach ($loans as $loan) {
                fputcsv($file, [
                    $loan->loan_account_number ?? 'N/A',
                    $loan->loanProduct->sub_product_name ?? 'Unknown Product',
                    ($loan->client ? ($loan->client->first_name . ' ' . $loan->client->last_name) : 'Unknown Client'),
                    $loan->client_number ?? 'N/A',
                    $loan->loan_type_2 ?? 'New',
                    number_format($loan->principle ?? 0, 0),
                    $loan->loan_term ?? 'N/A',
                    $loan->approval_stage ?? 'Not Set',
                    $loan->status ?? 'Unknown',
                    $loan->created_at ? $loan->created_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }
            
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportToPdf()
    {
        $loans = $this->getLoansForExport();
        
        $filename = 'loan_applications_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        // For now, we'll return a simple HTML response that can be printed as PDF
        // In a real implementation, you'd use a library like DomPDF or Snappy
        return response()->streamDownload(function () use ($loans) {
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <title>Loan Applications Report</title>
                <style>
                    body { font-family: Arial, sans-serif; font-size: 12px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; font-weight: bold; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .header h1 { margin: 0; color: #333; }
                    .header p { margin: 5px 0; color: #666; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Loan Applications Report</h1>
                    <p>Generated on: ' . now()->format('Y-m-d H:i:s') . '</p>
                    <p>Total Records: ' . $loans->count() . '</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Loan ID</th>
                            <th>Product</th>
                            <th>Client Name</th>
                            <th>Client ID</th>
                            <th>Loan Type</th>
                            <th>Amount (TZS)</th>
                            <th>Term (Months)</th>
                            <th>Current Stage</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach ($loans as $loan) {
                $html .= '<tr>
                    <td>' . ($loan->loan_account_number ?? 'N/A') . '</td>
                    <td>' . ($loan->loanProduct->sub_product_name ?? 'Unknown Product') . '</td>
                    <td>' . ($loan->client ? ($loan->client->first_name . ' ' . $loan->client->last_name) : 'Unknown Client') . '</td>
                    <td>' . ($loan->client_number ?? 'N/A') . '</td>
                    <td>' . ($loan->loan_type_2 ?? 'New') . '</td>
                    <td>' . number_format($loan->principle ?? 0, 0) . '</td>
                    <td>' . ($loan->loan_term ?? 'N/A') . '</td>
                    <td>' . ($loan->approval_stage ?? 'Not Set') . '</td>
                    <td>' . ($loan->status ?? 'Unknown') . '</td>
                    <td>' . ($loan->created_at ? $loan->created_at->format('Y-m-d') : 'N/A') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table></body></html>';
            
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html',
        ]);
    }

    private function getLoansForExport()
    {
        $query = LoansModel::with(['client', 'loanProduct', 'loanBranch'])
            ->whereIn('loan_type_2', ['New', 'Top-up', 'Restructure'])
            ->where('status', '!=', 'ACTIVE');

        // Apply search filter
        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('loan_id', 'like', $searchTerm)
                  ->orWhere('loan_account_number', 'like', $searchTerm)
                  ->orWhere('client_number', 'like', $searchTerm)
                  ->orWhere('status', 'like', $searchTerm)
                  ->orWhere('principle', 'like', $searchTerm)
                  ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                      $clientQuery->where('first_name', 'like', $searchTerm)
                                 ->orWhere('middle_name', 'like', $searchTerm)
                                 ->orWhere('last_name', 'like', $searchTerm)
                                 ->orWhere('phone_number', 'like', $searchTerm)
                                 ->orWhere('email', 'like', $searchTerm);
                  })
                  ->orWhereHas('loanProduct', function ($productQuery) use ($searchTerm) {
                      $productQuery->where('sub_product_name', 'like', $searchTerm);
                  });
            });
        }

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply branch filter
        if (!empty($this->branchFilter)) {
            $query->where('branch_id', $this->branchFilter);
        }

        // Apply loan officer filter
        if (!empty($this->loanOfficerFilter)) {
            $query->where('supervisor_id', $this->loanOfficerFilter);
        }

        // Apply date filter
        if (!empty($this->dateFilter)) {
            $date = Carbon::parse($this->dateFilter);
            $query->whereDate('created_at', $date);
        }

        return $query->orderBy($this->sortField, $this->sortDirection)->get();
    }

    public function render()
    {
        return view('livewire.loans.new-loans-table', [
            'loans' => $this->loans,
            'branches' => $this->branches,
            'loanOfficers' => $this->loanOfficers,
            'statusOptions' => $this->statusOptions,
        ]);
    }
}
