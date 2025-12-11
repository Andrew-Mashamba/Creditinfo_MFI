<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class TradePayables extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showPaymentModal = false;
    public $showApprovalModal = false;
    public $editMode = false;
    
    // Search and Filters
    public $search = '';
    public $statusFilter = 'all';
    public $priorityFilter = 'all';
    public $vendorFilter = '';
    public $dateFrom;
    public $dateTo;
    
    // Payable Form Data
    public $payableId;
    public $vendor_id;
    public $vendor_name = '';
    public $vendor_email = '';
    public $vendor_phone = '';
    public $vendor_address = '';
    public $vendor_tax_id = '';
    public $invoice_number = '';
    public $invoice_date;
    public $bill_number = '';
    public $purchase_order_number = '';
    public $amount = 0;
    public $vat_amount = 0;
    public $total_amount = 0;
    public $currency = 'TZS';
    public $due_date;
    public $payment_terms = 30;
    public $description = '';
    public $expense_account_id;
    public $payable_account_id;
    public $notes = '';
    public $priority = 'normal';
    public $approval_status = 'pending';


    protected $rules = [
        'customer_name' => 'required|string',
        'due_date' => 'required|date',
        'invoice_number' => 'required|string',
        'amount' => 'required|numeric|min:0',
    ];

    private CreditAndDebitService $creditAndDebitService;

    public function mount()
    {
        $this->fetchReceivables();
    }

    public function fetchReceivables()
    {
        $this->accountsPayable = payableModel::all();
    }

    /**
     * @throws \Exception
     */
    public function store()
    {

        // dd( $this->all());

        $this->validate();

        $liability_account_number = null;
        $cash_account_number = null;
        $expense_account_number = null;



// Assuming $payment_type is defined and contains the payment method
        if ($this->payment_type === 'cash') {
            // Retrieve the liability parent account
            $liability_parent_account = AccountsModel::where("sub_category_code", $this->liability_accounts_payable_code)->first();

            $liability_account_number = $this->createNewAccountNumber(
                $liability_parent_account->major_category_code,
                $liability_parent_account->category_code,
                $liability_parent_account->sub_category_code,
                $liability_parent_account->account_number
            );

            // Retrieve the cash parent account
            $cash_parent_account = AccountsModel::where("sub_category_code", $this->cash_code)->first();

            $cash_account_number = $this->createNewAccountNumber(
                $cash_parent_account->major_category_code,
                $cash_parent_account->category_code,
                $cash_parent_account->sub_category_code,
                $cash_parent_account->account_number
            );


            $liability_account = AccountsModel::where("account_number", $liability_account_number)->first();
            $cash_account = AccountsModel::where("account_number", $cash_account_number)->first();


            $narration = 'Payable - Cash Transaction  : ' . $this->customer_name;

            $data = [
                'first_account' => $liability_account,
                'second_account' => $cash_account,
                'amount' => $this->amount,
                'narration' => $narration,
            ];

        } elseif ($this->payment_type === 'non-cash') {
            // Retrieve the liability parent account
            $liability_parent_account = AccountsModel::where("sub_category_code", $this->liability_accounts_payable_code)->first();

            $liability_account_number = $this->createNewAccountNumber(
                $liability_parent_account->major_category_code,
                $liability_parent_account->category_code,
                $liability_parent_account->sub_category_code,
                $liability_parent_account->account_number
            );


            // Create income account
            $expense_parent_account = AccountsModel::where("sub_category_code", $this->expense_accounts_payable_code)->first();

            $expense_account_number = $this->createNewAccountNumber(
                $expense_parent_account->major_category_code,
                $expense_parent_account->category_code,
                $expense_parent_account->sub_category_code,
                $expense_parent_account->account_number
            );

            $liability_account = AccountsModel::where("account_number", $liability_account_number)->first();
            $expense_account = AccountsModel::where("account_number", $expense_account_number)->first();

            $narration = 'Payable - Non Cash Transaction : ' . $this->customer_name;

            $data = [
                'first_account' => $liability_account,
                'second_account' => $expense_account,
                'amount' => $this->amount,
                'narration' => $narration,
            ];
        }

        // Post the transaction
        $transactionServicex = new TransactionPostingService();
        $response = $transactionServicex->postTransaction($data);



        payableModel::create([
            'customer_name' => $this->customer_name,
            'due_date' => $this->due_date,
            'invoice_number' => $this->invoice_number,
            'amount' => $this->amount,
            'liability_account' => $liability_account_number ,
            'cash_account' => $cash_account_number,
            'expense_account' => $expense_account_number,
        ]);

        $this->resetInputFields();
        $this->fetchReceivables();
        session()->flash('message', 'Accounts Receivable Registered Successfully.');
    }

    public function edit($id)
    {
        $receivable = payableModel::findOrFail($id);
        $this->arId = $receivable->id;
        $this->customer_name = $receivable->customer_name;
        $this->due_date = $receivable->due_date;
        $this->invoice_number = $receivable->invoice_number;
        $this->amount = $receivable->amount;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        if ($this->arId) {
            $receivable = payableModel::find($this->arId);
            $receivable->update([
                'customer_name' => $this->customer_name,
                'due_date' => $this->due_date,
                'invoice_number' => $this->invoice_number,
                'amount' => $this->amount,
            ]);

            $this->resetInputFields();
            $this->fetchReceivables();
            session()->flash('message', 'Accounts Receivable Updated Successfully.');
            $this->isEdit = false;
        }
    }

    public function delete($id)
    {
        payableModel::find($id)->delete();
        $this->fetchReceivables();
        session()->flash('message', 'Accounts Receivable Deleted Successfully.');
    }

    public function markAsPaid($id)
    {

        $this->payModal=!$this->payModal;
        $this->selected_id=$id;



        $account = payableModel::findOrFail($this->selected_id);

        $this->amount= $account->amount  - $account->payment;

    }


    public function makePayment(){

        $model = payableModel::findOrFail($this->selected_id);


        $debited_account =AccountsModel::where('account_number',$model->asset_account)->first();
        $credited_account =AccountsModel::where('account_number', $model->income_account)->first();



        $data = [

            'first_account' => $debited_account,
            'second_account' => $credited_account,
            'amount' => $this->amount,
            'narration' =>  $this->description,

        ];
        $transactionServicex = new TransactionPostingService();


        $response = $transactionServicex->postTransaction($data);





        $model->payment += $this->amount;
        $model->save();

        if (($model->amount - $model->payment) == 0) {
            $model->is_paid = true;
            $model->save();
        }

        $this->fetchReceivables();
        session()->flash('message', 'Account marked as paid successfully.');

    }

    private function resetInputFields()
    {
        $this->customer_name = '';
        $this->due_date = '';
        $this->invoice_number = '';
        $this->amount = '';
        $this->arId = '';
    }


    function luhn_checksum($number) {
        $digits = str_split($number);
        $sum = 0;
        $alt = false;
        for ($i = count($digits) - 1; $i >= 0; $i--) {
            $n = $digits[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
        return $sum % 10;
    }

    public function generate_account_number($branch_code, $product_code): string
    {

        //sub_category_code
        do {
            // Generate a 5-digit random number for the unique account identifier
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

            // Concatenate branch code, unique identifier, and product code
            $partial_account_number = $branch_code . $unique_identifier . $product_code;

            // Calculate the checksum digit
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;

            // Form the final 12-digit account number
            $full_account_number = $partial_account_number . $checksum;

            // Check for uniqueness using Laravel's Eloquent model
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();

        } while (!$is_unique);

        return $full_account_number;
    }










    public function createNewAccount()
    {
        $GN_account_code = DB::table('GL_accounts')->where('account_name', $this->account_table_name)->value('account_code');
        if (!$GN_account_code) {
            // Handle the case where the account does not exist
        }

        $get_accounts = DB::table($this->category)->get();
        $next_code = $get_accounts->isEmpty() ? $this->next_code_no : intval($get_accounts->first()->category_code) + 1;

        // Ensure unique sub_category_code
        while (DB::table('accounts')->where('sub_category_code', $next_code)->exists()) {
            $next_code++;
        }

        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->customer_name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
        $formattedAccountName = strtoupper($formattedAccountName);

        // Create a new account in the category
        DB::table($this->category)->insert([
            'category_code' => $this->category_code_of_account,
            'sub_category_code' => $next_code,
            'sub_category_name' => $formattedAccountName,
        ]);

        // Generate account number
        $account_number = $this->generate_account_number(auth()->user()->branch, $next_code);

        // Create an entry in the AccountsModel
        AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $GN_account_code,
            'category_code' => $this->category_code_of_account,
            'sub_category_code' => $next_code,
            'account_name' => $this->customer_name,
            'account_number' => $account_number,
            'notes' => $this->customer_name,
            'account_level' => '3',
        ]);

        return $account_number;
    }




    public function createNewAccountNumber($major_category_code,$category_code, $sub_category_code,$parent_account){

        // Format the account name
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->customer_name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
        $formattedAccountName = strtoupper($formattedAccountName);

        // Create a new account in the category
        $account_number = $this->generate_account_number(auth()->user()->branch, $sub_category_code);

        // Create an entry in the AccountsModel
        AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $major_category_code,
            'category_code' => $category_code,
            'sub_category_code' => $sub_category_code,
            'account_name' => $formattedAccountName,
            'account_number' => $account_number,
            'notes' => $this->customer_name,
            'account_level' => '3',
            'parent_account_number'=>$parent_account
        ]);

        return $account_number;
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


        return view('livewire.accounting.trade-payables',

            [
                'gl_accounts' => $gl_accounts,
                'debit_sub_categories' => $debit_sub_categories,
                'debit_accounts' => $debit_accounts,
                'credit_sub_categories' => $credit_sub_categories,
                'credit_accounts' => $credit_accounts
            ]
        );
    }
}
