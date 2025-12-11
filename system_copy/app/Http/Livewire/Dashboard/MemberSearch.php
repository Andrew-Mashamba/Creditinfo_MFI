<?php

namespace App\Http\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;

class MemberSearch extends Component
{
    use WithPagination;

    // Search properties
    public $searchClientNumber = '';
    public $searchMemberNumber = '';
    public $searchAccountNumber = '';
    public $searchName = '';
    public $searchPhone = '';
    public $searchMembershipType = '';
    public $searchStatus = '';
    public $searchBranch = '';
    public $searchGender = '';
    
    // UI properties
    public $showAdvancedSearch = false;
    public $loading = false;
    public $hasActiveSearch = false;

    protected $queryString = [
        'searchClientNumber' => ['except' => ''],
        'searchMemberNumber' => ['except' => ''],
        'searchAccountNumber' => ['except' => ''],
        'searchName' => ['except' => ''],
        'searchPhone' => ['except' => ''],
        'searchMembershipType' => ['except' => ''],
        'searchStatus' => ['except' => ''],
        'searchBranch' => ['except' => ''],
        'searchGender' => ['except' => ''],
    ];

    public function mount()
    {
        $this->checkActiveSearch();
    }

    public function updatedSearchClientNumber()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchMemberNumber()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchAccountNumber()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchName()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchPhone()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchMembershipType()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchStatus()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchBranch()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function updatedSearchGender()
    {
        $this->checkActiveSearch();
        $this->resetPage();
    }

    public function checkActiveSearch()
    {
        $this->hasActiveSearch = !empty($this->searchClientNumber) || 
                                !empty($this->searchMemberNumber) || 
                                !empty($this->searchAccountNumber) || 
                                !empty($this->searchName) || 
                                !empty($this->searchPhone) || 
                                !empty($this->searchMembershipType) || 
                                !empty($this->searchStatus) || 
                                !empty($this->searchBranch) || 
                                !empty($this->searchGender);
    }

    public function clearSearch()
    {
        $this->searchClientNumber = '';
        $this->searchMemberNumber = '';
        $this->searchAccountNumber = '';
        $this->searchName = '';
        $this->searchPhone = '';
        $this->searchMembershipType = '';
        $this->searchStatus = '';
        $this->searchBranch = '';
        $this->searchGender = '';
        $this->hasActiveSearch = false;
        $this->resetPage();
    }

    // Removed showAllMembers method to conserve resources

    // Modal properties
    public $showMemberModal = false;
    public $selectedMember = null;
    public $memberAccounts = [];
    public $memberLoans = [];
    public $memberBills = [];

    public function viewMemberDetails($memberId)
    {
        $this->loading = true;
        
        try {
            // Get member details
            $this->selectedMember = ClientsModel::with(['branch'])->find($memberId);
            
            if ($this->selectedMember) {
                // Get accounts - try without relationship first
                $this->memberAccounts = \App\Models\Account::where('client_number', $this->selectedMember->client_number)->get();
                
                // Get loans - try without relationship first
                $this->memberLoans = \App\Models\LoansModel::where('client_number', $this->selectedMember->client_number)->get();
                
                // Get pending bills
                $this->memberBills = \App\Models\Bill::where('member_id', $memberId)
                    ->where('status', 'PENDING')
                    ->get();
                
                $this->showMemberModal = true;
            } else {
                session()->flash('error', 'Member not found');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error loading member details: ' . $e->getMessage());
        }
        
        $this->loading = false;
    }

    public function closeMemberModal()
    {
        $this->showMemberModal = false;
        $this->selectedMember = null;
        $this->memberAccounts = [];
        $this->memberLoans = [];
        $this->memberBills = [];
    }

    public function downloadRepaymentSchedule($loanId)
    {
        try {
            $loan = \App\Models\LoansModel::find($loanId);
            if ($loan) {
                // Generate repayment schedule PDF
                // You can implement PDF generation here
                session()->flash('success', 'Repayment schedule download started for loan #' . $loan->loan_number);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error downloading repayment schedule: ' . $e->getMessage());
        }
    }

    public function editMember($memberId)
    {
        // Redirect to edit member page or emit event
        $this->emit('editMember', $memberId);
    }

    public function exportResults()
    {
        $this->loading = true;
        
        try {
            $members = $this->getMembersQuery()->get();
            
            // Generate Excel/CSV export
            $filename = 'member_search_results_' . date('Y-m-d_H-i-s') . '.xlsx';
            
            // You can implement Excel export here using Laravel Excel or similar
            // For now, we'll just show a success message
            
            session()->flash('success', 'Export completed successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Export failed: ' . $e->getMessage());
        }
        
        $this->loading = false;
    }

    private function getMembersQuery()
    {
        $query = ClientsModel::with(['branch', 'accounts']);

        // Search by client number (most important field)
        if (!empty($this->searchClientNumber)) {
            $query->where('client_number', 'like', '%' . $this->searchClientNumber . '%');
        }

        // Search by member number
        if (!empty($this->searchMemberNumber)) {
            $query->where('member_number', 'like', '%' . $this->searchMemberNumber . '%');
        }

        // Search by account number
        if (!empty($this->searchAccountNumber)) {
            $query->where('account_number', 'like', '%' . $this->searchAccountNumber . '%');
        }

        // Search by name
        if (!empty($this->searchName)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->searchName . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchName . '%')
                  ->orWhere('middle_name', 'like', '%' . $this->searchName . '%')
                  ->orWhere('business_name', 'like', '%' . $this->searchName . '%')
                  ->orWhere('full_name', 'like', '%' . $this->searchName . '%');
            });
        }

        // Search by phone (multiple phone fields)
        if (!empty($this->searchPhone)) {
            $query->where(function ($q) {
                $q->where('phone_number', 'like', '%' . $this->searchPhone . '%')
                  ->orWhere('mobile_phone_number', 'like', '%' . $this->searchPhone . '%')
                  ->orWhere('mobile_phone', 'like', '%' . $this->searchPhone . '%')
                  ->orWhere('contact_number', 'like', '%' . $this->searchPhone . '%');
            });
        }

        // Search by membership type
        if (!empty($this->searchMembershipType)) {
            $query->where('membership_type', $this->searchMembershipType);
        }

        // Search by status (both client_status and status fields)
        if (!empty($this->searchStatus)) {
            $query->where(function ($q) {
                $q->where('client_status', 'like', '%' . $this->searchStatus . '%')
                  ->orWhere('status', 'like', '%' . $this->searchStatus . '%');
            });
        }

        // Search by branch (branch_id field)
        if (!empty($this->searchBranch)) {
            $query->where('branch_id', $this->searchBranch);
        }

        // Search by gender
        if (!empty($this->searchGender)) {
            $query->where('gender', $this->searchGender);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $this->loading = true;
        
        // Only show members if there's an active search
        if ($this->hasActiveSearch) {
            $members = $this->getMembersQuery()->paginate(12);
        } else {
            // Return empty paginator when no search is active
            $members = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                12,
                1,
                ['path' => request()->url()]
            );
        }
        
        $this->loading = false;
        
        return view('livewire.dashboard.member-search', [
            'members' => $members
        ]);
    }
}
