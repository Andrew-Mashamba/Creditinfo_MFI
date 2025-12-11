<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ManualPosting extends Component
{
    public $debit_category;
    public $debit_category_code;
    public $debit_account;
    public $credit_category;
    public $credit_category_code;
    public $credit_account;
    public $amount;
    public $narration;
    public $transactionPosted;

    public $search = ''; // Property to store the search term
    public $results = []; // Property to store the search results
    public $selectedAccount = [];
    public $selectedAccountTwo=[];
    public $showDropdownTwo=false;
    public $resultsTwo;
    public $searchTwo,$source_one,$source_two;
    public $isTableVisible = true;
    public $isTableVisibleTwo=true;

    // Control table visibility
    // Store the selected account details

    public $showDropdown = false;
    public $role;

    protected $rules = [
        'source_one' => 'required',
        'source_two' => 'required',
        'debit_account' => 'required',
        'credit_account' => 'required',
        'amount' => 'required|numeric|min:0.01',
        'narration' => 'required|string|max:255',
    ];



    public function updatedSearchxxx()
    {

        if (strlen($this->search) > 0) {

        $this->results = DB::table('accounts')
            ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'accounts' as source"))
            ->where('account_name', 'LIKE', '%' . $this->search . '%')
            ->orWhere('account_number', 'LIKE', '%' . $this->search . '%')
//            ->union(
//                DB::table('sub_accounts')
//                    ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'sub_accounts' as source"))
//                    ->where('account_name', 'LIKE', '%' . $this->search . '%')
//                    ->orWhere('account_number', 'LIKE', '%' . $this->search . '%')
//            )
            ->get()
            ->toArray();

            $this->isTableVisible = true; // Show table on search

            $this->showDropdown = true;
        } else {
            $this->showDropdown = false;
            $this->results = [];
        }

    }


    public function updatedSearch()
    {
        if (strlen($this->search) > 0) {
            $searchTerm = '%' . strtolower($this->search) . '%';

            $accountsQuery = DB::table('accounts')
                ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'accounts' as source"))
                ->where(function ($query) use ($searchTerm) {
                    $query->where(DB::raw('LOWER(account_name)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(account_number)'), 'LIKE', $searchTerm);
                });

            $AccountsModelQuery = DB::table('sub_accounts')
                ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'sub_accounts' as source"))
                ->where(function ($query) use ($searchTerm) {
                    $query->where(DB::raw('LOWER(account_name)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(account_number)'), 'LIKE', $searchTerm);
                });

            // Combine results from both queries with union
            $this->results = $accountsQuery->union($AccountsModelQuery)->get()->toArray();

            $this->isTableVisible = true; // Show table on search
            $this->showDropdown = true;

        } else {
            $this->showDropdown = false;
            $this->results = [];
        }
    }





    public function selectAccount($accountId, $source)
    {

        // Fetch account details based on ID and source table
        $account = DB::table($source)->where('id', $accountId)->first();
        if ($account) {
            $this->selectedAccount = (array) $account;
        }

        $this->source_one=$source;

        $this->showDropdown = false;

    }



    public function updatedSearchTwoxxxxx()
    {

        if (strlen($this->searchTwo) > 0) {

        $this->resultsTwo = DB::table('accounts')
            ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'accounts' as source"))
            ->where('account_name', 'LIKE', '%' . $this->search . '%')
            ->orWhere('account_number', 'LIKE', '%' . $this->search . '%')
//            ->union(
//                DB::table('sub_accounts')
//                    ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'sub_accounts' as source"))
//                    ->where('account_name', 'LIKE', '%' . $this->search . '%')
//                    ->orWhere('account_number', 'LIKE', '%' . $this->search . '%')
//            )
            ->get()
            ->toArray();

            $this->isTableVisibleTwo = true; // Show table on search

            $this->showDropdownTwo = true;
        } else {
            $this->showDropdownTwo = false;
            $this->resultsTwo = [];
        }

    }


    public function updatedSearchTwo()
    {
        if (strlen($this->searchTwo) > 0) {
            $searchTerm = '%' . strtolower($this->searchTwo) . '%';

            $accountsQuery = DB::table('accounts')
                ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'accounts' as source"))
                ->where(function ($query) use ($searchTerm) {
                    $query->where(DB::raw('LOWER(account_name)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(account_number)'), 'LIKE', $searchTerm);
                });

            $AccountsModelQuery = DB::table('sub_accounts')
                ->select('id', 'account_name', 'account_number', 'balance', 'status', DB::raw("'sub_accounts' as source"))
                ->where(function ($query) use ($searchTerm) {
                    $query->where(DB::raw('LOWER(account_name)'), 'LIKE', $searchTerm)
                        ->orWhere(DB::raw('LOWER(account_number)'), 'LIKE', $searchTerm);
                });

            // Combine results from both queries with union
            $this->resultsTwo = $accountsQuery->union($AccountsModelQuery)->get()->toArray();

            $this->isTableVisibleTwo = true; // Show table on search
            $this->showDropdownTwo = true;

        } else {
            $this->showDropdownTwo = false;
            $this->resultsTwo = [];
        }
    }


    public function selectAccountTwo($accountId, $source)
    {

        // Fetch account details based on ID and source table
        $account = DB::table($source)->where('id', $accountId)->first();
        if ($account) {
            $this->selectedAccountTwo = (array) $account;
        }
        $this->source_two=$source;

        $this->showDropdownTwo = false;

    }




    // Validation methods removed - now handled by TransactionPostingService
    // The service centralizes all validation logic for consistency across the system

    public function validateParentAccount($account, $source)
{
    // Check if the source is "accounts"
    if ($source == "accounts") {
        // Fetch the account from the database
        $account = DB::table('accounts')->where('account_number', $account)->first();

        // Check if the account exists
        if ($account) {
            // Check if a related sub-account exists based on the major and category codes
            $sub_account_exists = DB::table('sub_accounts')
                ->where('major_category_code', $account->major_category_code)
                ->where('category_code', $account->category_code)
                ->where('sub_category_code', $account->sub_category_code)
                ->exists();

            return $sub_account_exists; // Return true if exists, false otherwise
        }

        // Return false if the account does not exist
        return false;
    }

    return false;
}


    public function post()
    {
        // Set account numbers from selected accounts
        $this->debit_account = $this->selectedAccount['account_number'] ?? null;
        $this->credit_account = $this->selectedAccountTwo['account_number'] ?? null;
        $source_one = $this->source_one;
        $source_two = $this->source_two;
        
        // Validate form inputs
        $this->validate();
        
        try {
            // Validate parent accounts if needed
            $is_debit_valid = $this->validateParentAccount($this->debit_account, $source_one);
            $is_credit_valid = $this->validateParentAccount($this->credit_account, $source_two);

            // Get account details from appropriate tables
            $debited_account_details = DB::table($source_one)
                ->where("account_number", $this->debit_account)
                ->first();
                
            if (!$debited_account_details) {
                throw new \Exception('Invalid debit account.');
            }

            $credited_account_details = DB::table($source_two)
                ->where("account_number", $this->credit_account)
                ->first();
                
            if (!$credited_account_details) {
                throw new \Exception('Invalid credit account.');
            }

            // Prepare data for TransactionPostingService
            // The service expects account numbers, not account objects
            $data = [
                'first_account' => $this->debit_account,  // Pass account number string
                'second_account' => $this->credit_account, // Pass account number string
                'amount' => floatval($this->amount),
                'narration' => 'MANUAL POSTING: ' . $this->narration,
                'action' => 'manual_posting'
            ];

            // Use TransactionPostingService to post the transaction
            $postingService = new TransactionPostingService();
            $result = $postingService->postTransaction($data);
            
            // Log audit trail
            $this->logAudit('Manual Transaction Posted', $result['reference_number']);
            
            // Reset form fields
            $this->resetInputFields();
            
            session()->flash('message', 'Transaction posted successfully. Reference: ' . $result['reference_number']);

        } catch (\Exception $e) {
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        }
    }



    // These methods are no longer needed as TransactionPostingService handles all ledger entries
    // Keeping them commented for reference if needed for custom implementations
    
    /*
    private function postTransaction($reference_number, $debited_account, $credited_account)
    {
        // This functionality is now handled by TransactionPostingService in the post() method
    }

    private function debit($reference_number, $debited_account, $credited_account, $debited_new_balance)
    {
        // This functionality is now handled by TransactionPostingService
    }

    private function credit($reference_number, $debited_account, $credited_account, $credited_new_balance)
    {
        // This functionality is now handled by TransactionPostingService
    }
    */

    private function logAudit($action, $reference_number)
    {
        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => json_encode([
                'reference_number' => $reference_number,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function resetInputFields()
    {
        $this->debit_category = null;
        $this->debit_category_code = null;
        $this->debit_account = null;
        $this->credit_category = null;
        $this->credit_category_code = null;
        $this->credit_account = null;
        $this->amount = null;
        $this->narration = null;
        $this->search = '';
        $this->searchTwo = '';
        $this->selectedAccount = [];
        $this->selectedAccountTwo = [];
        $this->results = [];
        $this->resultsTwo = [];
        $this->showDropdown = false;
        $this->showDropdownTwo = false;
    }



    // Transaction reversal logic
    public function reverseTransaction($reference_number)
    {
        // Get both entries for this transaction (debit and credit)
        $transactions = general_ledger::where('reference_number', $reference_number)->get();

        if ($transactions->isEmpty()) {
            session()->flash('error', 'Transaction not found.');
            return;
        }

        try {
            // Find the debit and credit entries
            $debitEntry = $transactions->where('debit', '>', 0)->first();
            $creditEntry = $transactions->where('credit', '>', 0)->first();

            if (!$debitEntry || !$creditEntry) {
                throw new \Exception('Invalid transaction structure.');
            }

            // Create reversal transaction data
            // Swap the accounts to reverse the transaction
            $reversalData = [
                'first_account' => $creditEntry->record_on_account_number,  // Was credited, now debit
                'second_account' => $debitEntry->record_on_account_number,  // Was debited, now credit
                'amount' => floatval($debitEntry->debit),
                'narration' => 'REVERSAL of Ref#' . $reference_number . ': ' . $debitEntry->narration,
                'action' => 'reversal'
            ];

            // Use TransactionPostingService to post the reversal
            $postingService = new TransactionPostingService();
            $result = $postingService->postTransaction($reversalData);

            // Update original transaction status
            general_ledger::where('reference_number', $reference_number)
                ->update([
                    'trans_status' => 'Reversed',
                    'trans_status_description' => 'Reversed by Ref#' . $result['reference_number']
                ]);

            // Log audit trail
            $this->logAudit('Transaction Reversed', $result['reference_number']);

            session()->flash('message', 'Transaction reversed successfully. New Reference: ' . $result['reference_number']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reverse transaction: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $gl_accounts = DB::table('GL_accounts')->get();
        $debit_sub_categories = [];
        $debit_accounts = [];
        $credit_sub_categories = [];
        $credit_accounts = [];

        if ($this->debit_category) {
            $debit_sub_categories = DB::table($this->debit_category)->get();
        }

        if ($this->debit_category_code) {
            $debit_accounts = DB::table('accounts')->where('category_code', $this->debit_category_code)->get();
        }

        if ($this->credit_category) {
            $credit_sub_categories = DB::table($this->credit_category)->get();
        }

        if ($this->credit_category_code) {
            $credit_accounts = DB::table('accounts')->where('category_code', $this->credit_category_code)->get();
        }

        return view('livewire.accounting.manual-posting', [
            'gl_accounts' => $gl_accounts,
            'debit_sub_categories' => $debit_sub_categories,
            'debit_accounts' => $debit_accounts,
            'credit_sub_categories' => $credit_sub_categories,
            'credit_accounts' => $credit_accounts,
        ]);
    }
}
