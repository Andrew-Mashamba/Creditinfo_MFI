<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Services\BalanceSheetItemIntegrationService;
use App\Models\InterestPayable as InterestPayableModel;

class InterestPayable extends Component
{
    use WithPagination;

    public $show_register_modal = false;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    public $member_id, $account_type, $category_type = 'Savings'; // Default to Savings
    public $deposit_amount, $interest_rate, $deposit_date, $maturity_date, $payment_frequency;
    public $loan_provider, $amount, $loan_interest_rate, $loan_term, $loan_start_date, $interest_payment_schedule;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create interest payable account under
    public $other_account_id; // The other account for double-entry (Expense - debit side)

    protected $rules = [
        'member_id' => 'required|integer',
        'account_type' => 'required|string|max:50',
        'category_type' => 'required|string',
        // Savings fields
        'interest_rate' => 'required_if:category_type,Savings|numeric',
        'deposit_date' => 'required_if:category_type,Savings|date',
        'maturity_date' => 'required_if:category_type,Savings|date',
        'payment_frequency' => 'required_if:category_type,Savings|string',
        // Loan fields
        'loan_provider' => 'required_if:category_type,Loan|string|max:100',
        'amount' => 'required|numeric',
        'loan_interest_rate' => 'required_if:category_type,Loan|numeric',
        'loan_term' => 'required_if:category_type,Loan|string|max:50',
        'loan_start_date' => 'required_if:category_type,Loan|date',
        //'interest_payment_schedule' => 'required_if:category_type,Loan|string',
    ];

    public function register()
    {
        // Store the appropriate fields in the DB
        $interestPayableId = DB::table('interest_payables')->insertGetId([
            'member_id' => $this->member_id,
            'created_by'=>auth()->user()->id,
            'account_type' => $this->account_type,
            'amount' =>   $this->amount ,
            'interest_rate' => $this->category_type === 'Savings' ? $this->interest_rate : null,
            'deposit_date' => $this->category_type === 'Savings' ? $this->deposit_date : null,
            'maturity_date' => $this->category_type === 'Savings' ? $this->maturity_date : null,
            'payment_frequency' => $this->category_type === 'Savings' ? $this->payment_frequency : null,
            'loan_provider' => $this->category_type === 'Loan' ? $this->loan_provider : null,
            'loan_interest_rate' => $this->category_type === 'Loan' ? $this->loan_interest_rate : null,
            'loan_term' => $this->category_type === 'Loan' ? $this->loan_term : null,
            'loan_start_date' => $this->category_type === 'Loan' ? $this->loan_start_date : null,
            'interest_payment_schedule' => $this->category_type === 'Loan' ? $this->interest_payment_schedule : null,
            'created_at' => now(),
        ]);

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $interestPayableObj = (object)[
                'id' => $interestPayableId,
                'amount' => $this->amount,
                'interest_rate' => $this->category_type === 'Savings' ? $this->interest_rate : $this->loan_interest_rate,
                'type' => $this->category_type,
                'description' => 'Interest Payable - ' . $this->account_type
            ];
            
            $integrationService->createInterestPayableAccount(
                $interestPayableObj,
                $this->parent_account_number,  // Parent account to create interest payable account under
                $this->other_account_id        // The other account for double-entry (Expense - debit side)
            );
            
        } catch (\Exception $e) {
            \Log::error('Failed to integrate interest payable with accounts table: ' . $e->getMessage());
        }

        session()->flash('message', 'Interest payable record successfully stored and integrated.');
        $this->reset();
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

    public function approveAction($id)
    {
        // Add approval logic here
        session()->flash('message', 'Action approved successfully.');
    }

    public function render()
    {
        $interestPayables = InterestPayableModel::query()
            ->when($this->search, function($query) {
                $query->where('account_type', 'like', '%' . $this->search . '%')
                    ->orWhere('member_id', 'like', '%' . $this->search . '%')
                    ->orWhere('loan_provider', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '2000') // Liability accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%INTEREST%')
                      ->orWhere('account_name', 'LIKE', '%PAYABLE%')
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

        return view('livewire.accounting.interest-payable', [
            'interestPayables' => $interestPayables,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}
