<?php

namespace App\Http\Livewire\Accounting;

use App\Models\LongTermAndShortTerm as ModelsLongTermAndShortTerm;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Services\BalanceSheetItemIntegrationService;

class LongTermAndShortTerm extends Component
{
    use WithFileUploads, WithPagination;

    public $show_register_modal = false;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filterLoanType = 'all'; // all, Long, Short
    public $loan_type; // Long or Short
    public $source_account_id;
    public $amount;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create loan account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - debit side)
    public $organization_name;
    public $address;
    public $phone;
    public $email;
    public $description;
    public $application_form; // File input for application
    public $contract_form; // File input for contract

    public function register()
    {
        // Validate the form fields
        $this->validate([
            'loan_type' => 'required|string|in:Long,Short', // Ensure loan_type is either Long or Short
            'source_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric',
            'organization_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email',
            'description' => 'nullable|string',
            // Check for files only if they are required
            'application_form' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
            'contract_form' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:2048',
        ]);

        // Initialize file paths as null
        $applicationFormPath = null;
        $contractFormPath = null;

        // Store the uploaded files if they exist
        if ($this->application_form) {
            $applicationFormPath = $this->application_form->store('applications');
        }

        if ($this->contract_form) {
            $contractFormPath = $this->contract_form->store('contracts');
        }

        // Save the data into the loans table
        $loan = ModelsLongTermAndShortTerm ::create([
            'loan_type' => $this->loan_type, // Store the loan type
            'source_account_id' => $this->source_account_id,
            'amount' => $this->amount,
            'user_id'=>auth()->user()->id,
            'organization_name' => $this->organization_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'status'=>'PENDING',
            'description' => $this->description,
            'application_form' => $applicationFormPath,
            'contract_form' => $contractFormPath,
        ]);

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $integrationService->createLoanAccount(
                $loan,
                $this->parent_account_number,  // Parent account to create loan account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank - debit side)
            );
            
        } catch (\Exception $e) {
            \Log::error('Failed to integrate loan with accounts table: ' . $e->getMessage());
        }

        // Reset form fields
        $this->reset();

        session()->flash('message', 'Loan application submitted successfully.');
        $this->show_register_modal = false; // Hide modal after submission
    }


    function registerModal(){
        $this->show_register_modal=!$this->show_register_modal;
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

    public function deleteAction($id)
    {
        $loan = ModelsLongTermAndShortTerm::find($id);
        if ($loan) {
            $loan->delete();
            session()->flash('message', 'Loan deleted successfully.');
        }
    }

    public function render()
    {
        $loans = ModelsLongTermAndShortTerm::query()
            ->when($this->filterLoanType !== 'all', function($query) {
                $query->where('loan_type', $this->filterLoanType);
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('organization_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $accounts = DB::table('accounts')
            ->whereIn('category_code', [2300, 2400])
            ->get();

        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '2000') // Liability accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%LOAN%')
                      ->orWhere('account_name', 'LIKE', '%BORROWING%')
                      ->orWhere('account_name', 'LIKE', '%LIABILITY%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        $otherAccounts = DB::table('bank_accounts')
            
            


            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.long-term-and-short-term', [
            'loans' => $loans,
            'accounts' => $accounts,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}
