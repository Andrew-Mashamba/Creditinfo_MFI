<?php

namespace App\Http\Livewire\Accounting;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\BalanceSheetItemIntegrationService;

class Unearned extends Component
{
    use WithPagination;
    public $user_id;
    public $source_account_id;
    public $destination_account_id;
    public $status;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create unearned revenue account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - debit side)
    public $is_recognized;
    public $is_delivery;
    public $description;
    public $name;
    public $address;
    public $phone;
    public $email;
    public $show_register_modal=false,$amount;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $filterStatus = 'all'; // all, PENDING, recognized, delivered

    function registerModal(){
        $this->show_register_modal=!$this->show_register_modal;
    }


    protected $rules = [
        'user_id' => 'nullable|integer',
        'source_account_id' => 'nullable|integer',
        'destination_account_id' => 'nullable|integer',

        'description' => 'required|string',
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'amount'=>'numeric'
    ];



    public function register(){
        $this->validate();

         // Insert data into the database
         $unearnedRevenueId = DB::table('unearned_deferred_revenue')->insertGetId([
            'user_id' => auth()->user()->id,
            'source_account_id' => $this->source_account_id,
            'destination_account_id' => $this->destination_account_id,
            'status' => 'PENDING',
            'is_recognized' => false,
            'is_delivery' =>false,
            'description' => $this->description,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'amount'=>$this->amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            $unearnedRevenueObj = (object)[
                'id' => $unearnedRevenueId,
                'amount' => $this->amount,
                'customer_name' => $this->name,
                'description' => $this->description,
                'status' => 'PENDING'
            ];
            
            $integrationService->createUnearnedRevenueAccount(
                $unearnedRevenueObj,
                $this->parent_account_number,  // Parent account to create unearned revenue account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank - debit side)
            );
            
        } catch (\Exception $e) {
            \Log::error('Failed to integrate unearned revenue with accounts table: ' . $e->getMessage());
        }

        $this->reset();

        session()->flash('message', 'Unearned revenue successfully inserted and integrated.');
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

    public function recognizeRevenue($id)
    {
        DB::table('unearned_deferred_revenue')
            ->where('id', $id)
            ->update([
                'is_recognized' => true,
                'status' => 'RECOGNIZED',
                'updated_at' => now()
            ]);
        
        session()->flash('message', 'Revenue recognized successfully.');
    }

    public function markDelivered($id)
    {
        DB::table('unearned_deferred_revenue')
            ->where('id', $id)
            ->update([
                'is_delivery' => true,
                'updated_at' => now()
            ]);
        
        session()->flash('message', 'Marked as delivered successfully.');
    }

    public function render()
    {
        $unearnedRevenues = DB::table('unearned_deferred_revenue')
            ->when($this->filterStatus !== 'all', function($query) {
                if ($this->filterStatus === 'recognized') {
                    $query->where('is_recognized', true);
                } elseif ($this->filterStatus === 'delivered') {
                    $query->where('is_delivery', true);
                } else {
                    $query->where('status', $this->filterStatus);
                }
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $accounts = DB::table('accounts')
            ->whereIn('category_code', [2600, 2700])
            ->get();

        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '2000') // Liability accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%UNEARNED%')
                      ->orWhere('account_name', 'LIKE', '%DEFERRED%')
                      ->orWhere('account_name', 'LIKE', '%REVENUE%')
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

        return view('livewire.accounting.unearned', [
            'unearnedRevenues' => $unearnedRevenues,
            'accounts' => $accounts,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }



//     public function store()
//     {
//         $this->validate();

//         // Fetch accounts for the receivables category
//         $get_accounts = DB::table('accounts_receivable')->get();

//         if ($get_accounts->isEmpty()) {
//             $next_code = 1501; // Starting code for Accounts Receivable
//         } else {
//             $category_code = $get_accounts->first()->category_code;
//             $existing_codes = $get_accounts->pluck('category_code')->toArray();
//             $range_start = intval($category_code) + 1;
//             $range_limit = intval($category_code) + 999;

//             // Generate the next unique sub_category_code
//             $next_code = $range_start;
//             while (in_array(strval($next_code), $existing_codes) && $next_code <= $range_limit) {
//                 $next_code++;
//             }

//             // Validate next_code before inserting
//             if ($next_code > $range_limit) {
//                 session()->flash('error', 'Unable to generate a unique code within the range.');
//                 return;
//             }
//         }

//         // Format the account name
//         $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->customer_name)));
//         $formattedAccountName = str_replace(' ', '_', $formattedAccountName);

//         $category_code = DB::table('asset_accounts')->where('category_name', 'accounts_receivable')->value('category_code');

//         // Create a new AR account
// //        DB::table('accounts_receivable')->insert([
// //            'category_code' => $category_code,
// //            'sub_category_code' => $next_code,
// //            'sub_category_name' => $formattedAccountName,
// //        ]);

//         // Generate account number
//         $account_number = $this->generate_account_number(auth()->user()->branch, $next_code);

//         // Create a new account entry in the AccountsModel
//         $id = AccountsModel::create([
//             'account_use' => 'internal',
//             'institution_number' => auth()->user()->institution_id,
//             'branch_number' => auth()->user()->branch,
//             'major_category_code' => 1000,
//             'category_code' => $category_code,
//             'sub_category_code' => $this->source,
//             'account_name' => $this->customer_name,
//             'account_number' => $account_number,
//             'notes' => $this->customer_name,
//             'bank_id' => null,
//             'mirror_account' => null,
//             'account_level' => '3',
//         ])->id;

//         $reference_number = time();
//         $credited_account_number = AccountsModel::where("sub_category_code", $this->source)->value('account_number');
//         $credited_balance = AccountsModel::where("account_number", $credited_account_number)->value('balance');
//         $credited_account_name = AccountsModel::where("account_number", $credited_account_number)->value('account_name');
//         $credit_new_balance = $credited_balance + $this->amount;

//         $debited_account_details = AccountsModel::where("account_number", $account_number)->first();
//         $debited_account_name = $debited_account_details->account_name;
//         $debited_account_number = $debited_account_details->account_number;
//         $debited_balance = AccountsModel::where("account_number", $debited_account_number)->value('balance');
//         $debited_new_balance = $debited_balance - $this->amount;

//         $narration = 'Accounts Receivable : ' . $this->customer_name;

//         // DEBIT ACCOUNT RECEIVABLE
//         $this->debit($reference_number, $debited_account_number,  $credited_account_number, $this->amount, $narration, $debited_new_balance,$credit_new_balance, $debited_account_name,$credited_account_name);
//         //CREDIT REVENUE ACCOUNT
//         $this->credit($reference_number, $debited_account_number,  $credited_account_number, $this->amount, $narration, $debited_new_balance,$credit_new_balance, $debited_account_name,$credited_account_name);


//         ARModel::create([
//             'customer_name' => $this->customer_name,
//             'due_date' => $this->due_date,
//             'invoice_number' => $this->invoice_number,
//             'amount' => $this->amount,
//             'source' => $credited_account_number,
//             'account_number' => $account_number,
//         ]);

//         $this->resetInputFields();
//         $this->fetchReceivables();
//         session()->flash('message', 'Accounts Receivable Registered Successfully.');
//     }


}
