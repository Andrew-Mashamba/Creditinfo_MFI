<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Services\BalanceSheetItemIntegrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class Creditors extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showCreditorForm = false; // Added for view compatibility
    public $showTransactionForm = false; // Added for view compatibility
    public $showPaymentModal = false;
    public $showStatementModal = false;
    public $editMode = false;
    public $editingCreditorId = null; // Added for view compatibility
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create creditor account under
    public $other_account_id; // The other account for double-entry (Cash/Expense - debit side)
    
    // Search and Filters
    public $search = '';
    public $typeFilter = 'all';
    public $statusFilter = 'all';
    public $filterCreditorId = ''; // Added for transaction filtering
    public $dateFrom;
    public $dateTo;
    
    // Creditor Form Data
    public $creditorId;
    public $creditor_type = 'supplier';
    public $creditor_name = '';
    public $creditor_code = '';
    public $registration_number = '';
    public $tax_number = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $country = 'Tanzania';
    public $credit_limit = 0;
    public $payment_terms = 30;
    public $discount_rate = 0;
    public $account_id;
    public $status = 'active';
    public $notes = '';
    
    // Transaction Data
    public $transactionId;
    public $transaction_type = 'invoice';
    public $transaction_date;
    public $reference_number = '';
    public $description = '';
    public $amount = 0;
    public $tax_amount = 0;
    public $total_amount = 0;
    public $due_date;
    
    // Payment Data
    public $payment_creditor_id;
    public $payment_amount = 0;
    public $payment_date;
    public $payment_method = 'bank_transfer';
    public $payment_reference = '';
    public $payment_account_id;
    public $payment_notes = '';
    
    // Statistics
    public $totalCreditors = 0;
    public $totalOutstanding = 0;
    public $totalOverdue = 0;
    public $totalPaidThisMonth = 0;
    public $averagePaymentDays = 0;
    public $creditorAging = [];
    
    // Collections
    public $creditorTypes = [];
    public $creditorAccounts = [];
    public $bankAccounts = [];
    public $creditorsWithBalances = [];
    
    protected $rules = [
        'creditor_name' => 'required|min:3',
        'creditor_type' => 'required',
        'email' => 'nullable|email',
        'phone' => 'nullable', // Changed to nullable as column doesn't exist
        'credit_limit' => 'nullable|numeric|min:0',
        'payment_terms' => 'nullable|integer|min:0', // Changed to nullable
        // 'account_id' => 'required|exists:accounts,id', // Removed - column doesn't exist
    ];
    
    protected $listeners = [
        'refreshCreditors' => 'loadStatistics',
        'deleteCreditor' => 'delete',
        'generateStatement' => 'generateCreditorStatement',
    ];
    
    public function mount()
    {
        $this->initializeData();
        $this->loadStatistics();
        $this->transaction_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }
    
    public function initializeData()
    {
        // Define creditor types
        $this->creditorTypes = [
            'supplier' => 'Supplier',
            'service_provider' => 'Service Provider',
            'contractor' => 'Contractor',
            'consultant' => 'Consultant',
            'utility' => 'Utility Company',
            'government' => 'Government Agency',
            'financial_institution' => 'Financial Institution',
            'landlord' => 'Landlord',
            'other' => 'Other'
        ];
        
        // Load creditor accounts (Liability accounts)
        $this->creditorAccounts = AccountsModel::where('account_type', 'LIABILITY')
            ->where(function($query) {
                $query->where('account_name', 'like', '%creditor%')
                      ->orWhere('account_name', 'like', '%payable%')
                      ->orWhere('account_name', 'like', '%supplier%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load bank accounts for payments
        $this->bankAccounts = AccountsModel::where(function($query) {
                $query->where('account_name', 'LIKE', '%BANK%')
                      ->orWhere('account_name', 'LIKE', '%CASH%')
                      ->orWhere('major_category_code', '1000');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
    }
    
    public function loadStatistics()
    {
        if (!Schema::hasTable('creditors')) {
            $this->totalCreditors = 0;
            $this->totalOutstanding = 0;
            $this->totalOverdue = 0;
            $this->totalPaidThisMonth = 0;
            $this->averagePaymentDays = 0;
            $this->creditorAging = [];
            $this->creditorsWithBalances = [];
            return;
        }
        
        // Total creditors
        $this->totalCreditors = DB::table('creditors')
            ->where('status', 'active')
            ->count();
        
        // Total outstanding balance from creditors table
        $this->totalOutstanding = DB::table('creditors')
            ->where('status', 'active')
            ->sum('outstanding_amount') ?? 0;
        
        // Total overdue - creditors past maturity date
        $this->totalOverdue = DB::table('creditors')
            ->where('status', 'active')
            ->where('maturity_date', '<', now())
            ->sum('outstanding_amount') ?? 0;
        
        // Total paid this month - approximate based on payment frequency
        // Since we don't have transactions table, calculate from payment_amount
        $this->totalPaidThisMonth = DB::table('creditors')
            ->where('status', 'active')
            ->where('payment_frequency', 'monthly')
            ->sum('payment_amount') ?? 0;
        
        // Average payment days - not available without transactions
        $this->averagePaymentDays = 0;
        
        // Calculate aging
        $this->calculateCreditorAging();
        
        // Load creditors with outstanding balances
        $this->loadCreditorsWithBalances();
    }
    
    public function calculateCreditorAging()
    {
        $this->creditorAging = [
            'current' => 0,
            '30_days' => 0,
            '60_days' => 0,
            '90_days' => 0,
            'over_90' => 0
        ];
        
        if (!Schema::hasTable('creditors')) {
            return;
        }
        
        // Calculate aging based on maturity dates - simplified for PostgreSQL
        $agingData = DB::table('creditors')
            ->where('status', 'active')
            ->where('outstanding_amount', '>', 0)
            ->select([
                DB::raw("CASE 
                    WHEN maturity_date >= CURRENT_DATE THEN 'current'
                    WHEN maturity_date >= CURRENT_DATE - INTERVAL '30 days' THEN '30_days'
                    WHEN maturity_date >= CURRENT_DATE - INTERVAL '60 days' THEN '60_days'
                    WHEN maturity_date >= CURRENT_DATE - INTERVAL '90 days' THEN '90_days'
                    ELSE 'over_90'
                END as age_bucket"),
                DB::raw('SUM(outstanding_amount) as amount')
            ])
            ->groupBy('age_bucket')
            ->get();
        
        foreach ($agingData as $data) {
            $this->creditorAging[$data->age_bucket] = $data->amount;
        }
    }
    
    public function loadCreditorsWithBalances()
    {
        if (!Schema::hasTable('creditors')) {
            $this->creditorsWithBalances = [];
            return;
        }
        
        $this->creditorsWithBalances = DB::table('creditors')
            ->select([
                'id',
                'creditor_name',
                'creditor_code',
                'outstanding_amount as balance',
                'updated_at as last_transaction_date'
            ])
            ->where('status', 'active')
            ->where('outstanding_amount', '>', 0)
            ->orderBy('outstanding_amount', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function openCreateModal()
    {
        $this->reset(['creditorId', 'editingCreditorId', 'creditor_name', 'creditor_code', 'registration_number',
                     'tax_number', 'contact_person', 'email', 'phone', 'address', 'city',
                     'credit_limit', 'discount_rate', 'notes']);
        
        $this->editMode = false;
        $this->editingCreditorId = null; // Clear editing ID
        $this->creditor_type = 'supplier';
        $this->country = 'Tanzania';
        $this->payment_terms = 30;
        $this->status = 'active';
        $this->generateCreditorCode();
        $this->showCreateModal = true;
        $this->showCreditorForm = true; // Set both for compatibility
    }
    
    public function generateCreditorCode()
    {
        $prefix = 'CR';
        $year = date('Y');
        
        if (Schema::hasTable('creditors')) {
            $lastCreditor = DB::table('creditors')
                ->where('creditor_code', 'like', "$prefix$year%")
                ->orderBy('creditor_code', 'desc')
                ->first();
            
            if ($lastCreditor) {
                $lastNumber = intval(substr($lastCreditor->creditor_code, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        $this->creditor_code = "$prefix$year$newNumber";
    }
    
    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            // Prepare data - only include columns that exist in creditors table
            $data = [
                'creditor_type' => $this->creditor_type,
                'creditor_name' => $this->creditor_name,
                'creditor_code' => $this->creditor_code,
                'principal_amount' => $this->credit_limit ?? 0, // Map credit_limit to principal_amount
                'interest_rate' => $this->discount_rate ?? 0, // Map discount_rate to interest_rate
                'outstanding_amount' => $this->credit_limit ?? 0, // Initial outstanding equals principal
                'payment_frequency' => 'monthly', // Default
                'payment_amount' => 0, // Will be calculated based on terms
                'terms_conditions' => $this->notes,
                'status' => $this->status,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
            ];
            
            if ($this->editMode && $this->creditorId) {
                // Update existing creditor
                DB::table('creditors')
                    ->where('id', $this->creditorId)
                    ->update($data);
                
                $message = 'Creditor updated successfully!';
            } else {
                // Create new creditor
                $data['created_by'] = auth()->id();
                $data['created_at'] = now();
                $data['opening_balance'] = 0;
                
                $creditorId = DB::table('creditors')->insertGetId($data);
                
                // Use Balance Sheet Integration Service to create accounts and post to GL
                $integrationService = new BalanceSheetItemIntegrationService();
                
                try {
                    $creditor = (object) array_merge($data, [
                        'id' => $creditorId,
                        'name' => $this->creditor_name,
                        'current_balance' => $this->amount ?? 0
                    ]);
                    
                    $integrationService->createCreditorAccount(
                        $creditor,
                        $this->parent_account_number,  // Parent account to create creditor account under
                        $this->other_account_id        // The other account for double-entry (Cash/Expense - debit side)
                    );
                    
                    Log::info('Creditor integrated with accounts table', [
                        'creditor_id' => $creditorId,
                        'name' => $this->creditor_name,
                        'balance' => $this->amount ?? 0
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to integrate creditor with accounts table: ' . $e->getMessage());
                }
                
                $message = 'Creditor created successfully!';
            }
            
            DB::commit();
            
            $this->showCreateModal = false;
            $this->showCreditorForm = false; // Close both for compatibility
            $this->loadStatistics();
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving creditor: ' . $e->getMessage());
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error saving creditor: ' . $e->getMessage()
            ]);
        }
    }
    
    private function createOpeningBalance($creditorId)
    {
        // Skip recording in creditor_transactions as table doesn't exist
        // Just create GL entry
        $creditor = DB::table('creditors')->find($creditorId);
        
        // Skip GL entry as account_id doesn't exist in creditors table
        // Would need to get a default liability account for creditors
        Log::info('Opening balance creation skipped - account_id not available');
    }
    
    public function recordTransaction($creditorId)
    {
        $this->validate([
            'transaction_type' => 'required',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|min:5',
        ]);
        
        // Skip if creditor_transactions table doesn't exist
        if (!Schema::hasTable('creditor_transactions')) {
            $this->dispatchBrowserEvent('alert', [
                'type' => 'warning',
                'message' => 'Transaction recording is not available.'
            ]);
            return;
        }
        
        DB::beginTransaction();
        try {
            $creditor = DB::table('creditors')->find($creditorId);
            
            if (!$creditor) {
                throw new \Exception('Creditor not found');
            }
            
            // Calculate total with tax
            $totalAmount = $this->amount + $this->tax_amount;
            
            // Get last balance
            $lastBalance = DB::table('creditor_transactions')
                ->where('creditor_id', $creditorId)
                ->orderBy('id', 'desc')
                ->value('balance') ?? 0;
            
            // Calculate new balance based on transaction type
            if (in_array($this->transaction_type, ['invoice', 'debit_note'])) {
                $newBalance = $lastBalance + $totalAmount;
                $debitAmount = 0;
                $creditAmount = $totalAmount;
            } else {
                $newBalance = $lastBalance - $totalAmount;
                $debitAmount = $totalAmount;
                $creditAmount = 0;
            }
            
            // Record transaction
            $transactionId = DB::table('creditor_transactions')->insertGetId([
                'creditor_id' => $creditorId,
                'transaction_type' => $this->transaction_type,
                'transaction_date' => $this->transaction_date,
                'reference_number' => $this->reference_number,
                'description' => $this->description,
                'amount' => $this->amount,
                'tax_amount' => $this->tax_amount,
                'total_amount' => $totalAmount,
                'debit_amount' => $debitAmount,
                'credit_amount' => $creditAmount,
                'balance' => $newBalance,
                'due_date' => $this->due_date,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Create GL entries
            $this->createTransactionGLEntries($creditor, $transactionId);
            
            DB::commit();
            
            $this->reset(['transaction_type', 'reference_number', 'description', 
                         'amount', 'tax_amount', 'total_amount', 'due_date']);
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Transaction recorded successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error recording transaction: ' . $e->getMessage()
            ]);
        }
    }
    
    private function createTransactionGLEntries($creditor, $transactionId)
    {
        $transaction = DB::table('creditor_transactions')->find($transactionId);
        $reference = $transaction->reference_number;
        $description = $transaction->description;
        
        if ($transaction->transaction_type === 'invoice') {
            // For purchases/invoices
            // Debit Expense/Asset Account (depends on nature)
            // Credit Creditor Account
            
            general_ledger::create([
                'reference_number' => $reference,
                'transaction_type' => 'CREDITOR_INVOICE',
                'transaction_date' => $transaction->transaction_date,
                'account_id' => $creditor->account_id,
                'debit_amount' => 0,
                'credit_amount' => $transaction->total_amount,
                'description' => $description,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $transactionId,
                'source_type' => 'creditor_transactions'
            ]);
            
        } elseif ($transaction->transaction_type === 'payment') {
            // For payments
            // Debit Creditor Account
            // Credit Bank/Cash Account
            
            general_ledger::create([
                'reference_number' => $reference,
                'transaction_type' => 'CREDITOR_PAYMENT',
                'transaction_date' => $transaction->transaction_date,
                'account_id' => $creditor->account_id,
                'debit_amount' => $transaction->total_amount,
                'credit_amount' => 0,
                'description' => $description,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $transactionId,
                'source_type' => 'creditor_transactions'
            ]);
        }
    }
    
    public function openPaymentModal($creditorId)
    {
        $creditor = DB::table('creditors')->find($creditorId);
        
        if ($creditor) {
            // Get current balance
            $balance = DB::table('creditor_transactions')
                ->where('creditor_id', $creditorId)
                ->selectRaw('SUM(credit_amount - debit_amount) as balance')
                ->value('balance') ?? 0;
            
            $this->payment_creditor_id = $creditorId;
            $this->payment_amount = $balance;
            $this->payment_date = now()->format('Y-m-d');
            $this->payment_method = 'bank_transfer';
            $this->showPaymentModal = true;
        }
    }
    
    public function processPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required',
            'payment_account_id' => 'required|exists:accounts,id',
        ]);
        
        DB::beginTransaction();
        try {
            $creditor = DB::table('creditors')->find($this->payment_creditor_id);
            
            if (!$creditor) {
                throw new \Exception('Creditor not found');
            }
            
            // Get last balance
            $lastBalance = DB::table('creditor_transactions')
                ->where('creditor_id', $this->payment_creditor_id)
                ->orderBy('id', 'desc')
                ->value('balance') ?? 0;
            
            $newBalance = $lastBalance - $this->payment_amount;
            
            // Record payment transaction
            $transactionId = DB::table('creditor_transactions')->insertGetId([
                'creditor_id' => $this->payment_creditor_id,
                'transaction_type' => 'payment',
                'transaction_date' => $this->payment_date,
                'reference_number' => $this->payment_reference ?: 'PAY-' . date('YmdHis'),
                'description' => 'Payment to ' . $creditor->creditor_name,
                'amount' => $this->payment_amount,
                'total_amount' => $this->payment_amount,
                'debit_amount' => $this->payment_amount,
                'credit_amount' => 0,
                'balance' => $newBalance,
                'payment_method' => $this->payment_method,
                'notes' => $this->payment_notes,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Create GL entries
            general_ledger::create([
                'reference_number' => $this->payment_reference ?: 'PAY-' . date('YmdHis'),
                'transaction_type' => 'CREDITOR_PAYMENT',
                'transaction_date' => $this->payment_date,
                'account_id' => $creditor->account_id,
                'debit_amount' => $this->payment_amount,
                'credit_amount' => 0,
                'description' => 'Payment to ' . $creditor->creditor_name,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $transactionId,
                'source_type' => 'creditor_transactions'
            ]);
            
            general_ledger::create([
                'reference_number' => $this->payment_reference ?: 'PAY-' . date('YmdHis'),
                'transaction_type' => 'CREDITOR_PAYMENT',
                'transaction_date' => $this->payment_date,
                'account_id' => $this->payment_account_id,
                'debit_amount' => 0,
                'credit_amount' => $this->payment_amount,
                'description' => 'Payment to ' . $creditor->creditor_name,
                'created_by' => auth()->id(),
                'status' => 'POSTED',
                'source_id' => $transactionId,
                'source_type' => 'creditor_transactions'
            ]);
            
            DB::commit();
            
            $this->showPaymentModal = false;
            $this->reset(['payment_amount', 'payment_reference', 'payment_notes']);
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Payment processed successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ]);
        }
    }
    
    public function generateCreditorStatement($creditorId)
    {
        if (!Schema::hasTable('creditor_transactions')) {
            return;
        }
        
        $creditor = DB::table('creditors')->find($creditorId);
        
        if (!$creditor) {
            return;
        }
        
        $transactions = DB::table('creditor_transactions')
            ->where('creditor_id', $creditorId)
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
        
        // Here you would generate PDF or Excel statement
        // For now, just show the data
        $this->dispatchBrowserEvent('show-statement', [
            'creditor' => $creditor,
            'transactions' => $transactions,
            'period' => [
                'from' => $this->dateFrom,
                'to' => $this->dateTo
            ]
        ]);
    }
    
    public function edit($id)
    {
        $creditor = DB::table('creditors')->find($id);
        
        if ($creditor) {
            $this->creditorId = $id;
            $this->editingCreditorId = $id; // Set editingCreditorId for view
            $this->creditor_type = $creditor->creditor_type;
            $this->creditor_name = $creditor->creditor_name;
            $this->creditor_code = $creditor->creditor_code;
            $this->registration_number = $creditor->registration_number ?? '';
            $this->tax_number = $creditor->tax_number ?? '';
            $this->contact_person = $creditor->contact_person ?? '';
            $this->email = $creditor->email ?? '';
            $this->phone = $creditor->phone ?? '';
            $this->address = $creditor->address ?? '';
            $this->city = $creditor->city ?? '';
            $this->country = $creditor->country ?? '';
            $this->credit_limit = $creditor->credit_limit ?? 0;
            $this->payment_terms = $creditor->payment_frequency ?? 'monthly';
            $this->discount_rate = $creditor->discount_rate ?? 0;
            $this->account_id = null; // account_id doesn't exist in creditors table
            $this->status = $creditor->status;
            $this->notes = $creditor->terms_conditions ?? '';
            
            $this->editMode = true;
            $this->showCreateModal = true;
            $this->showCreditorForm = true; // Set both for compatibility
        }
    }
    
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Check if creditor has transactions
            if (Schema::hasTable('creditor_transactions')) {
                $hasTransactions = DB::table('creditor_transactions')
                    ->where('creditor_id', $id)
                    ->exists();
                
                if ($hasTransactions) {
                    // Soft delete - just change status
                    DB::table('creditors')
                        ->where('id', $id)
                        ->update(['status' => 'inactive', 'updated_at' => now()]);
                    
                    $message = 'Creditor deactivated successfully!';
                } else {
                    // Hard delete if no transactions
                    DB::table('creditors')->where('id', $id)->delete();
                    $message = 'Creditor deleted successfully!';
                }
            } else {
                DB::table('creditors')->where('id', $id)->delete();
                $message = 'Creditor deleted successfully!';
            }
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error deleting creditor: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        $creditors = collect();
        
        if (Schema::hasTable('creditors')) {
            $query = DB::table('creditors')
                ->select([
                    'creditors.*',
                    'creditors.outstanding_amount as current_balance',
                    'creditors.updated_at as last_transaction_date'
                ]);
            
            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('creditors.creditor_name', 'like', '%' . $this->search . '%')
                      ->orWhere('creditors.creditor_code', 'like', '%' . $this->search . '%')
                      ->orWhere('creditors.email', 'like', '%' . $this->search . '%')
                      ->orWhere('creditors.phone', 'like', '%' . $this->search . '%');
                });
            }
            
            if ($this->typeFilter && $this->typeFilter !== 'all') {
                $query->where('creditors.creditor_type', $this->typeFilter);
            }
            
            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $query->where('creditors.status', $this->statusFilter);
            }
            
            $creditors = $query->orderBy('creditors.creditor_name')
                ->paginate(10);
        } else {
            $creditors = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10
            );
        }
        
        // Get all creditors for dropdown lists
        $creditorsData = collect();
        if (Schema::hasTable('creditors')) {
            $creditorsData = DB::table('creditors')
                ->select('id', 'creditor_name as name', 'creditor_code')
                ->where('status', 'active')
                ->orderBy('creditor_name')
                ->get();
        }
        
        // Get transactions - empty since creditor_transactions table doesn't exist
        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            10,
            1,
            ['path' => request()->url()]
        );
        
        // If creditor_transactions table exists in future, uncomment this:
        // if (Schema::hasTable('creditor_transactions')) {
        //     $transactionsQuery = DB::table('creditor_transactions')
        //         ->leftJoin('creditors', 'creditor_transactions.creditor_id', '=', 'creditors.id')
        //         ->select([
        //             'creditor_transactions.*',
        //             'creditors.creditor_name'
        //         ]);
        //     
        //     if ($this->filterCreditorId) {
        //         $transactionsQuery->where('creditor_transactions.creditor_id', $this->filterCreditorId);
        //     }
        //     
        //     $transactions = $transactionsQuery->orderBy('transaction_date', 'desc')
        //         ->paginate(10);
        // }
        
        // Get recent payments - empty since payment tables don't exist
        $recentPayments = collect();
        
        // If payment tables exist in future, uncomment this:
        // if (Schema::hasTable('creditor_payments')) {
        //     $recentPayments = DB::table('creditor_payments')
        //         ->leftJoin('creditors', 'creditor_payments.creditor_id', '=', 'creditors.id')
        //         ->select([
        //             'creditor_payments.*',
        //             'creditors.creditor_name'
        //         ])
        //         ->orderBy('payment_date', 'desc')
        //         ->limit(10)
        //         ->get();
        // }
        
        // Get aging data - empty for now
        $agingData = collect();
        
        // Could be calculated from creditors table if needed:
        // if (Schema::hasTable('creditors')) {
        //     $agingData = DB::table('creditors')
        //         ->where('status', 'active')
        //         ->where('outstanding_amount', '>', 0)
        //         ->select('creditor_name', 'outstanding_amount', 'maturity_date')
        //         ->get()
        //         ->map(function($creditor) {
        //             // Calculate aging buckets based on maturity date
        //             return [
        //                 'creditor_name' => $creditor->creditor_name,
        //                 'current' => 0,
        //                 '30_days' => 0,
        //                 '60_days' => 0,
        //                 '90_days' => 0,
        //                 'over_90' => 0,
        //                 'total' => $creditor->outstanding_amount
        //             ];
        //         });
        // }
        
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '2000') // Liability accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%CREDITOR%')
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

        return view('livewire.accounting.creditors', [
            'creditors' => $creditors,
            'creditorsData' => $creditorsData,
            'transactions' => $transactions,
            'recentPayments' => $recentPayments,
            'agingData' => $agingData,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}