<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\ARModel;
use App\Models\general_ledger;
use App\Services\CreditAndDebitService;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Exception;
use App\Models\Receivable;
use Livewire\WithPagination;

class AccountsReceivable extends Component
{
    use WithPagination;

    // Form state
    public $showForm = false;
    public $isEdit = false;
    public $payModal = false;
    public $search = '';
    public $statusFilter = '';
    public $selectedAccountId = null;

    // Basic receivable information
    public $customer_name = '';
    public $invoice_number = '';
    public $amount = 0;
    public $due_date = '';
    public $notes = '';
    public $income_sub_category_code = '';
    public $asset_sub_category_code = '';

    // File Upload Properties
    public $invoice_file;
    public $payment_receipt_file;
    public $credit_note_file;
    public $collection_letter_file;

    public $parent_account_number;
    public $income_account;
    public $parentAccounts;
    public $incomeAccounts;

    // Form properties
    public $payment_amount = 0;
    public $payment_date = '';
    public $payment_method = '';
    public $reference_number = '';
    public $payment_notes = '';

    // Computed properties
    public $totalReceivables = 0;
    public $paidReceivables = 0;
    public $overdueReceivables = 0;
    public $badDebt = 0;
    public $accountsReceivable;

    protected $rules = [
        'parent_account_number' => 'required',
        'income_account' => 'required',
        'customer_name' => 'required|min:3',
        'invoice_number' => 'required|unique:receivables,invoice_number',
        'amount' => 'required|numeric|min:0',
        'due_date' => 'required|date',
        'notes' => 'nullable|string',
        'invoice_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        'payment_receipt_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        'credit_note_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        'collection_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'
    ];

    private CreditAndDebitService $creditAndDebitService;

    public function mount()
    {
        $this->loadAccounts();
        $this->showForm = false;
        $this->isEdit = false;
        $this->loadData();
    }

    private function loadAccounts()
    {
        // Load parent receivables accounts (level 2 accounts)
        $this->parentAccounts = AccountsModel::where('type', 'asset_accounts')
            ->where('account_level', 2)
            ->where('account_name', 'like', '%receivable%')
            ->get();

        // Load income accounts
        $this->incomeAccounts = AccountsModel::where('type', 'income_accounts')
            ->where('account_level', 3)
            ->get();
    }

    public function loadData()
    {
        // Calculate total receivables
        $this->totalReceivables = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->where('category_code', '1100')
            ->sum('balance');

        // Calculate paid receivables
        $this->paidReceivables = DB::table('general_ledger')
            ->where('transaction_type', 'IFT')
            ->where('trans_status', 'Successful')
            ->where('payment_status', 'Successful')
            ->where('credit', '>', 0)
            ->sum('credit');

        // Calculate overdue receivables
        $this->overdueReceivables = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->where('category_code', '1100')
            ->where('status', 'OVERDUE')
            ->sum('balance');

        // Calculate bad debt
        $this->badDebt = DB::table('accounts')
            ->where('major_category_code', '1000')
            ->where('category_code', '1100')
            ->where('status', 'BAD_DEBT')
            ->sum('balance');

        // Load accounts receivable data
        $this->loadAccountsReceivable();
    }

    public function loadAccountsReceivable()
    {
        $this->accountsReceivable = DB::table('accounts as a')
            ->leftJoin('clients as c', 'a.client_number', '=', 'c.client_number')
            ->leftJoin('general_ledger as gl', function($join) {
                $join->on('a.account_number', '=', 'gl.record_on_account_number')
                    ->where('gl.trans_status', '=', 'Successful');
            })
            ->select([
                'a.id',
                'a.account_number',
                'a.account_name',
                'a.balance as amount',
                'a.status',
                'a.created_at as due_date',
                DB::raw("CONCAT(c.first_name, ' ', COALESCE(c.middle_name, ''), ' ', c.last_name) as customer_name"),
                DB::raw('a.account_number as invoice_number'),
                DB::raw("CASE 
                    WHEN a.status = 'PAID' THEN 1 
                    ELSE 0 
                END as is_paid"),
                DB::raw("CASE 
                    WHEN a.status = 'OVERDUE' THEN 1 
                    ELSE 0 
                END as is_overdue")
            ])
            ->where('a.major_category_code', '1000')
            ->where('a.category_code', '1100')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('a.account_name', 'like', '%' . $this->search . '%')
                        ->orWhere('a.account_number', 'like', '%' . $this->search . '%')
                        ->orWhere('c.first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('c.last_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'paid') {
                    $query->where('a.status', 'PAID');
                } elseif ($this->statusFilter === 'unpaid') {
                    $query->where('a.status', 'PENDING');
                } elseif ($this->statusFilter === 'overdue') {
                    $query->where('a.status', 'OVERDUE');
                }
            })
            ->orderBy('a.created_at', 'desc')
            ->paginate(10);
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->loadAccountsReceivable();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
        $this->loadAccountsReceivable();
    }

    public function resetForm()
    {
        Log::info('resetForm method called');
        
        try {
            $this->reset([
                'isEdit',
                'customer_name',
                'invoice_number',
                'amount',
                'due_date',
                'notes',
                'invoice_file',
                'payment_receipt_file',
                'credit_note_file',
                'collection_letter_file',
                'parent_account_number',
                'income_account'
            ]);
            
            $this->showForm = true;
            $this->isEdit = false;
            
            Log::info('Form reset successfully', [
                'showForm' => $this->showForm,
                'isEdit' => $this->isEdit
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Form ready for new receivable'
            ]);
            
        } catch (Exception $e) {
            Log::error('Error in resetForm method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error resetting form: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        Log::info('Edit method called', ['id' => $id]);
        
        try {
            $account = AccountsModel::find($id);
            Log::info('Account found', ['account' => $account ? $account->toArray() : null]);
            
            if (!$account) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'Account not found. Please refresh the page and try again.'
                ]);
                return;
            }

            // Check if account is a receivable account
            if ($account->type !== 'asset_accounts' || $account->category_code !== '1100') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'This account is not a receivable account. Cannot edit.'
                ]);
                return;
            }

            $receivable = Receivable::where('account_number', $account->account_number)->first();
            Log::info('Receivable found', ['receivable' => $receivable ? $receivable->toArray() : null]);
            
            if (!$receivable) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No receivable record found for this account. Please create a receivable record first.'
                ]);
                return;
            }

            // Check if receivable is already paid
            if ($receivable->status === 'paid') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'warning',
                    'message' => 'This receivable has already been paid. Cannot edit.'
                ]);
                return;
            }

            $this->selectedAccountId = $receivable->id;
            $this->customer_name = $receivable->customer_name;
            $this->invoice_number = $receivable->invoice_number;
            $this->amount = $receivable->amount;
            $this->due_date = $receivable->due_date;
            $this->notes = $receivable->notes;
            $this->isEdit = true;
            $this->showForm = true;
            
            Log::info('Form data populated successfully', [
                'selectedAccountId' => $this->selectedAccountId,
                'isEdit' => $this->isEdit,
                'showForm' => $this->showForm
            ]);

        } catch (Exception $e) {
            Log::error('Error in edit method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error loading receivable: ' . $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Get the parent receivables account
            $parentAccount = AccountsModel::where('account_number', $this->parent_account_number)
                ->where('type', 'asset_accounts')
                ->first();

            if (!$parentAccount) {
                throw new Exception('Parent receivables account not found');
            }

            // Create a new sub-account for this receivable
            $subAccount = new AccountsModel();
            $subAccount->account_number = $this->generateAccountNumber($parentAccount->account_number);
            $subAccount->account_name = $this->customer_name . ' - ' . $this->invoice_number;
            $subAccount->type = 'asset_accounts';
            $subAccount->account_level = 3; // Sub-account level
            $subAccount->parent_account_number = $parentAccount->account_number;
            $subAccount->balance = 0;
            $subAccount->debit = 0;
            $subAccount->credit = 0;
            $subAccount->save();

            // Create the receivable record
            $receivable = new Receivable();
            $receivable->account_number = $subAccount->account_number;
            $receivable->customer_name = $this->customer_name;
            $receivable->invoice_number = $this->invoice_number;
            $receivable->amount = $this->amount;
            $receivable->due_date = $this->due_date;
            $receivable->status = 'PENDING';
            $receivable->notes = $this->notes;
            $receivable->save();

            // Handle file uploads
            $this->handleFileUploads($receivable);

            // Post the transaction using TransactionPostingService
            $transactionService = new TransactionPostingService();
            $transactionData = [
                'first_account' => $subAccount->account_number,
                'second_account' => $this->income_account,
                'amount' => $this->amount,
                'narration' => "Receivable created for {$this->customer_name} - Invoice #{$this->invoice_number}",
                'action' => 'create_receivable'
            ];

            $result = $transactionService->postTransaction($transactionData);

            DB::commit();

            $this->resetForm();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Receivable created successfully!'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error creating receivable: ' . $e->getMessage()
            ]);
        }
    }

    private function handleFileUploads($receivable)
    {
        $uploadPath = 'receivables/' . $receivable->id;

        // Handle Invoice File
        if ($this->invoice_file) {
            $invoicePath = $this->invoice_file->store($uploadPath . '/invoice');
            $receivable->invoice_file_path = $invoicePath;
        }

        // Handle Payment Receipt File
        if ($this->payment_receipt_file) {
            $receiptPath = $this->payment_receipt_file->store($uploadPath . '/payment_receipt');
            $receivable->payment_receipt_file_path = $receiptPath;
        }

        // Handle Credit Note File
        if ($this->credit_note_file) {
            $creditNotePath = $this->credit_note_file->store($uploadPath . '/credit_note');
            $receivable->credit_note_file_path = $creditNotePath;
        }

        // Handle Collection Letter File
        if ($this->collection_letter_file) {
            $letterPath = $this->collection_letter_file->store($uploadPath . '/collection_letter');
            $receivable->collection_letter_file_path = $letterPath;
        }

        $receivable->save();
    }

    private function generateAccountNumber($parentAccountNumber)
    {
        // Get the last sub-account number for this parent
        $lastSubAccount = AccountsModel::where('parent_account_number', $parentAccountNumber)
            ->orderBy('account_number', 'desc')
            ->first();

        if ($lastSubAccount) {
            // Extract the sequence number and increment
            $sequence = (int)substr($lastSubAccount->account_number, -3);
            $newSequence = str_pad($sequence + 1, 3, '0', STR_PAD_LEFT);
            return $parentAccountNumber . $newSequence;
        }

        // If no sub-accounts exist, start with 001
        return $parentAccountNumber . '001';
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $receivable = Receivable::find($this->selectedAccountId);
            if (!$receivable) {
                throw new Exception('Receivable not found');
            }

            // Update the account
            $account = AccountsModel::where('account_number', $receivable->account_number)->first();
            if ($account) {
                $account->update([
                    'account_name' => $this->customer_name . ' - ' . $this->invoice_number,
                    'balance' => $this->amount
                ]);
            }

            // Update the receivable record
            $receivable->update([
                'customer_name' => $this->customer_name,
                'invoice_number' => $this->invoice_number,
                'amount' => $this->amount,
                'due_date' => $this->due_date,
                'notes' => $this->notes
            ]);

            // Handle file uploads
            $this->handleFileUploads($receivable);

            DB::commit();
            $this->resetForm();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Receivable updated successfully!'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error updating receivable: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        Log::info('Delete method called', ['id' => $id]);
        
        try {
            DB::beginTransaction();
            Log::info('Transaction started');

            $account = AccountsModel::find($id);
            Log::info('Account found', ['account' => $account ? $account->toArray() : null]);
            
            if (!$account) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'Account not found. Please refresh the page and try again.'
                ]);
                return;
            }

            // Check if account is a receivable account
            if ($account->type !== 'asset_accounts' || $account->category_code !== '1100') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'This account is not a receivable account. Cannot delete.'
                ]);
                return;
            }

            $receivable = Receivable::where('account_number', $account->account_number)->first();
            Log::info('Receivable found', ['receivable' => $receivable ? $receivable->toArray() : null]);
            
            if (!$receivable) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No receivable record found for this account.'
                ]);
                return;
            }

            // Check if receivable is already paid
            if ($receivable->status === 'paid') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'warning',
                    'message' => 'Cannot delete a paid receivable. Please contact the administrator if this is necessary.'
                ]);
                return;
            }

            // Check if account has balance
            if ($account->balance > 0) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'warning',
                    'message' => 'Cannot delete a receivable with an outstanding balance.'
                ]);
                return;
            }

            // Delete the account
            $account->delete();
            Log::info('Account deleted successfully');

            // Delete the receivable record
            $receivable->delete();
            Log::info('Receivable record deleted successfully');

            DB::commit();
            Log::info('Transaction committed successfully');
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Receivable deleted successfully!'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in delete method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error deleting receivable: ' . $e->getMessage()
            ]);
        }
    }

    public function markAsPaid($id)
    {
        Log::info('markAsPaid method called', ['id' => $id]);
        
        try {
            $account = AccountsModel::find($id);
            Log::info('Account found', ['account' => $account ? $account->toArray() : null]);
            
            if (!$account) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'Account not found. Please refresh the page and try again.'
                ]);
                return;
            }

            // Check if account is a receivable account
            if ($account->type !== 'asset_accounts' || $account->category_code !== '1100') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'This account is not a receivable account. Cannot process payment.'
                ]);
                return;
            }

            $receivable = Receivable::where('account_number', $account->account_number)->first();
            Log::info('Receivable found', ['receivable' => $receivable ? $receivable->toArray() : null]);
            
            if (!$receivable) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'error',
                    'message' => 'No receivable record found for this account. Please create a receivable record first.'
                ]);
                return;
            }

            // Check if receivable is already paid
            if ($receivable->status === 'paid') {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'warning',
                    'message' => 'This receivable has already been paid.'
                ]);
                return;
            }

            // Check if account has balance
            if ($account->balance <= 0) {
                $this->dispatchBrowserEvent('notify', [
                    'type' => 'warning',
                    'message' => 'This receivable has no balance to pay.'
                ]);
                return;
            }

            $this->selectedAccountId = $receivable->id;
            $this->payModal = true;
            
            Log::info('Payment modal opened', [
                'selectedAccountId' => $this->selectedAccountId,
                'payModal' => $this->payModal
            ]);

        } catch (Exception $e) {
            Log::error('Error in markAsPaid method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error opening payment modal: ' . $e->getMessage()
            ]);
        }
    }

    public function makePayment()
    {
        Log::info('makePayment method called', [
            'selectedAccountId' => $this->selectedAccountId,
            'payment_amount' => $this->payment_amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'reference_number' => $this->reference_number
        ]);

        $this->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required',
            'reference_number' => 'required',
        ]);

        try {
            DB::beginTransaction();
            Log::info('Transaction started');

            $receivable = Receivable::find($this->selectedAccountId);
            Log::info('Receivable found', ['receivable' => $receivable ? $receivable->toArray() : null]);
            
            if (!$receivable) {
                Log::warning('Receivable not found', ['id' => $this->selectedAccountId]);
                throw new Exception('Receivable not found');
            }

            $account = AccountsModel::where('account_number', $receivable->account_number)->first();
            Log::info('Account found', ['account' => $account ? $account->toArray() : null]);
            
            if (!$account) {
                Log::warning('Account not found', ['account_number' => $receivable->account_number]);
                throw new Exception('Account not found');
            }

            // Get the cash/bank account based on payment method
            $cashAccount = AccountsModel::where('type', 'asset_accounts')
                ->where('account_name', 'like', '%' . $this->payment_method . '%')
                ->first();
            Log::info('Cash/Bank account found', ['cashAccount' => $cashAccount ? $cashAccount->toArray() : null]);

            if (!$cashAccount) {
                Log::warning('Cash/Bank account not found', ['payment_method' => $this->payment_method]);
                throw new Exception('Cash/Bank account not found for payment method: ' . $this->payment_method);
            }

            // Update account status
            $account->update([
                'status' => 'PAID',
                'balance' => 0
            ]);
            Log::info('Account status updated');

            // Update receivable record
            $receivable->update([
                'status' => 'paid',
                'last_payment_date' => $this->payment_date,
                'last_payment_amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'reference_number' => $this->reference_number,
            ]);
            Log::info('Receivable record updated');

            // Create transaction entries
            $transactionService = new TransactionPostingService();
            
            // First entry: Credit the receivable account
            $transactionData1 = [
                'first_account' => $cashAccount->account_number,
                'second_account' => $account->account_number,
                'amount' => $this->payment_amount,
                'narration' => "Payment received for {$receivable->customer_name} - Invoice #{$receivable->invoice_number}",
                'action' => 'payment_received'
            ];
            $transactionService->postTransaction($transactionData1);
            Log::info('First transaction posted', ['transactionData' => $transactionData1]);

            // Second entry: Debit the cash/bank account
            $transactionData2 = [
                'first_account' => $account->account_number,
                'second_account' => $cashAccount->account_number,
                'amount' => $this->payment_amount,
                'narration' => "Payment made for {$receivable->customer_name} - Invoice #{$receivable->invoice_number}",
                'action' => 'payment_made'
            ];
            $transactionService->postTransaction($transactionData2);
            Log::info('Second transaction posted', ['transactionData' => $transactionData2]);

            DB::commit();
            Log::info('Transaction committed successfully');

            $this->payModal = false;
            $this->reset(['payment_amount', 'payment_date', 'payment_method', 'reference_number', 'payment_notes']);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Payment recorded successfully!'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in makePayment method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error recording payment: ' . $e->getMessage()
            ]);
        }
    }

    public function exportToExcel()
    {
        return response()->streamDownload(function () {
            $accounts = DB::table('receivables as r')
                ->leftJoin('accounts as a', 'r.account_number', '=', 'a.account_number')
                ->leftJoin('clients as c', 'a.client_number', '=', 'c.client_number')
                ->select([
                    'r.customer_name',
                    'r.invoice_number',
                    'r.amount',
                    'r.due_date',
                    'r.status',
                    'r.receivable_type',
                    'r.payment_terms',
                    'r.collection_status',
                    'r.revenue_category',
                    'r.department',
                    'r.document_reference',
                    'r.approval_status'
                ])
                ->get();

            $csv = fopen('php://output', 'w');
            fputcsv($csv, [
                'Customer',
                'Invoice',
                'Amount',
                'Due Date',
                'Status',
                'Type',
                'Payment Terms',
                'Collection Status',
                'Revenue Category',
                'Department',
                'Document Reference',
                'Approval Status'
            ]);

            foreach ($accounts as $account) {
                fputcsv($csv, [
                    $account->customer_name,
                    $account->invoice_number,
                    $account->amount,
                    $account->due_date,
                    $account->status,
                    $account->receivable_type,
                    $account->payment_terms,
                    $account->collection_status,
                    $account->revenue_category,
                    $account->department,
                    $account->document_reference,
                    $account->approval_status
                ]);
            }

            fclose($csv);
        }, 'accounts_receivable.csv');
    }

    public function viewPaymentDetails($id)
    {
        $account = AccountsModel::find($id);
        if ($account) {
            $receivable = Receivable::where('account_number', $account->account_number)->first();
            if ($receivable) {
                // Get payment details from general ledger
                $paymentDetails = general_ledger::where('record_on_account_number', $account->account_number)
                    ->where('transaction_type', 'IFT')
                    ->where('trans_status', 'Successful')
                    ->where('payment_status', 'Successful')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($paymentDetails) {
                    $this->dispatchBrowserEvent('show-payment-details', [
                        'customer_name' => $receivable->customer_name,
                        'invoice_number' => $receivable->invoice_number,
                        'amount' => $receivable->amount,
                        'payment_date' => $paymentDetails->created_at,
                        'payment_method' => $receivable->payment_method,
                        'reference_number' => $receivable->reference_number,
                        'narration' => $paymentDetails->narration
                    ]);
                } else {
                    $this->dispatchBrowserEvent('notify', [
                        'type' => 'error',
                        'message' => 'Payment details not found'
                    ]);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.accounting.accounts-receivable', [
            'receivables' => $this->accountsReceivable,
            'parentAccounts' => $this->parentAccounts,
            'incomeAccounts' => $this->incomeAccounts
        ]);
    }
}
