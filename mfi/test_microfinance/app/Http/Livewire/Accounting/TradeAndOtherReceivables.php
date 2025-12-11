<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\Receivable;
use App\Models\ClientsModel;
use App\Services\BalanceSheetItemIntegrationService;
use App\Services\AccountCreationService;
use App\Services\TransactionPostingService;
use App\Services\PaymentLinkService;
use App\Services\EmailService;
use App\Services\BillingService;
use App\Services\SmsService;
use App\Jobs\ProcessTradeReceivableInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class TradeAndOtherReceivables extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showPaymentModal = false;
    public $showDetailsModal = false;
    public $showConfirmModal = false;
    public $editMode = false;
    
    // Confirmation modal properties
    public $confirmTitle = '';
    public $confirmMessage = '';
    public $confirmButtonText = 'Confirm';
    public $confirmAction = '';
    public $confirmActionId = null;
    
    // Search and Filters
    public $search = '';
    public $statusFilter = 'all';
    public $ageFilter = 'all';
    public $customerFilter = '';
    public $dateFrom;
    public $dateTo;
    
    // Receivable Form Data
    public $receivableId;
    public $customer_id;
    public $customer_name = '';
    public $customer_company = '';
    public $customer_email = '';
    public $customer_phone = '';
    public $customer_address = '';
    public $invoice_number = '';
    public $invoice_date;
    public $amount = 0;
    public $vat_amount = 0;
    public $total_amount = 0;
    public $currency = 'TZS';
    public $due_date;
    public $payment_terms = 30;
    public $description = '';
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create receivable account under
    public $other_account_id; // The other account for double-entry (Revenue - credit side)
    public $reference_number = '';
    public $account_id;
    public $income_account_id;
    public $notes = '';
    public $status = 'pending';
    
    // Payment Form Data
    public $payment_receivable_id;
    public $selectedBalance = 0; // Track the balance for validation
    public $payment_amount = 0;
    public $payment_date;
    public $payment_method = 'bank_transfer';
    public $payment_reference = '';
    public $payment_account_id;
    public $payment_notes = '';
    public $bank_charges = 0;
    public $paymentAccounts; // Available payment accounts (will be a collection)
    public $discount_amount = 0;
    
    // Bad Debt Provision
    public $provision_rate = 0;
    public $provision_amount = 0;
    public $provision_notes = '';
    
    // File Uploads
    public $invoice_attachment;
    public $payment_receipt;
    public $supporting_documents = [];
    
    // Statistics
    public $totalReceivables = 0;
    public $totalOverdue = 0;
    public $totalCollected = 0;
    public $totalBadDebt = 0;
    public $averageCollectionPeriod = 0;
    public $agingBuckets = [];
    
    // Collections
    public $customers = [];
    public $receivableAccounts = [];
    public $incomeAccounts = [];
    public $bankAccounts = [];
    
    protected $rules = [
        'customer_name' => 'required|min:3',
        'customer_company' => 'nullable|string',
        'customer_email' => 'nullable|email',
        'customer_phone' => 'nullable|string',
        'customer_address' => 'nullable|string',
        'invoice_number' => 'required',
        'invoice_date' => 'required|date',
        'amount' => 'required|numeric|min:0',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'payment_terms' => 'required|integer|min:0',
        'vat_amount' => 'nullable|numeric|min:0',
        'invoice_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ];
    
    protected $listeners = [
        'refreshReceivables' => 'loadStatistics',
        'deleteReceivable' => 'delete',
        'writeOffReceivable' => 'writeOff',
    ];
    
    public function mount()
    {
        $this->initializeData();
        $this->loadStatistics();
        $this->invoice_date = now()->format('Y-m-d');
        $this->payment_date = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        // Auto-calculate due date based on default payment terms
        $this->updatedPaymentTerms();
        // Initialize payment accounts as empty array
        $this->paymentAccounts = [];
    }
    
    public function initializeData()
    {
        // Load customers
        $this->customers = ClientsModel::where('status', 'ACTIVE')
            ->orderBy('first_name')
            ->get();
        
        // Load receivable accounts (Asset accounts)
        $this->receivableAccounts = AccountsModel::where('account_type', 'ASSET')
            ->where(function($query) {
                $query->where('account_name', 'like', '%receivable%')
                      ->orWhere('account_name', 'like', '%debtor%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        // Load income accounts
        $this->incomeAccounts = AccountsModel::where('account_type', 'REVENUE')
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
        $query = DB::table('trade_receivables');
        
        // Apply date filters
        if ($this->dateFrom) {
            $query->where('invoice_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('invoice_date', '<=', $this->dateTo);
        }
        
        // Calculate totals
        $this->totalReceivables = $query->sum('amount');
        
        $this->totalOverdue = DB::table('trade_receivables')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum('balance');
        
        $this->totalCollected = DB::table('trade_receivables')
            ->where('status', 'paid')
            ->sum('paid_amount');
        
        $this->totalBadDebt = DB::table('trade_receivables')
            ->where('status', 'written_off')
            ->sum('balance');
        
        // Calculate average collection period (using PostgreSQL syntax)
        $avgDays = DB::table('trade_receivables')
            ->where('status', 'paid')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(updated_at::date - invoice_date::date) as avg_days')
            ->value('avg_days');
        
        $this->averageCollectionPeriod = round($avgDays ?? 0);
        
        // Calculate aging buckets
        $this->calculateAgingBuckets();
    }
    
    public function calculateAgingBuckets()
    {
        $today = Carbon::now();
        
        $this->agingBuckets = [
            'current' => [
                'label' => 'Current',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '30_days' => [
                'label' => '1-30 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '60_days' => [
                'label' => '31-60 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '90_days' => [
                'label' => '61-90 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            '120_days' => [
                'label' => '91-120 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ],
            'over_120' => [
                'label' => 'Over 120 Days',
                'amount' => 0,
                'count' => 0,
                'percentage' => 0
            ]
        ];
        
        $receivables = DB::table('trade_receivables')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'written_off')
            ->get();
        
        $totalOutstanding = 0;
        
        foreach ($receivables as $receivable) {
            $daysOverdue = $today->diffInDays(Carbon::parse($receivable->due_date), false);
            $amount = $receivable->balance;
            $totalOutstanding += $amount;
            
            if ($daysOverdue >= 0) {
                $this->agingBuckets['current']['amount'] += $amount;
                $this->agingBuckets['current']['count']++;
            } elseif ($daysOverdue >= -30) {
                $this->agingBuckets['30_days']['amount'] += $amount;
                $this->agingBuckets['30_days']['count']++;
            } elseif ($daysOverdue >= -60) {
                $this->agingBuckets['60_days']['amount'] += $amount;
                $this->agingBuckets['60_days']['count']++;
            } elseif ($daysOverdue >= -90) {
                $this->agingBuckets['90_days']['amount'] += $amount;
                $this->agingBuckets['90_days']['count']++;
            } elseif ($daysOverdue >= -120) {
                $this->agingBuckets['120_days']['amount'] += $amount;
                $this->agingBuckets['120_days']['count']++;
            } else {
                $this->agingBuckets['over_120']['amount'] += $amount;
                $this->agingBuckets['over_120']['count']++;
            }
        }
        
        // Calculate percentages
        if ($totalOutstanding > 0) {
            foreach ($this->agingBuckets as $key => &$bucket) {
                $bucket['percentage'] = round(($bucket['amount'] / $totalOutstanding) * 100, 2);
            }
        }
    }
    
    public function updatedAmount()
    {
        $this->calculateTotal();
    }
    
    public function updatedVatAmount()
    {
        $this->calculateTotal();
    }
    
    public function calculateTotal()
    {
        $this->total_amount = $this->amount + $this->vat_amount;
    }
    
    public function updatedPaymentTerms()
    {
        if ($this->invoice_date && $this->payment_terms) {
            $this->due_date = Carbon::parse($this->invoice_date)
                ->addDays($this->payment_terms)
                ->format('Y-m-d');
        }
    }
    
    public function updatedInvoiceDate()
    {
        $this->updatedPaymentTerms();
    }
    
    public function updatedCustomerId()
    {
        if ($this->customer_id) {
            $customer = Client::find($this->customer_id);
            if ($customer) {
                $this->customer_name = $customer->first_name . ' ' . $customer->middle_name . ' ' . $customer->last_name;
                $this->customer_email = $customer->email;
                $this->customer_phone = $customer->phone_number;
            }
        }
    }
    
    public function openCreateModal()
    {
        $this->reset(['receivableId', 'customer_id', 'customer_name', 'customer_company',
                     'customer_email', 'customer_phone', 'customer_address', 
                     'invoice_number', 'amount', 'vat_amount', 
                     'total_amount', 'description', 'reference_number', 'notes']);
        
        $this->editMode = false;
        $this->invoice_date = now()->format('Y-m-d');
        $this->generateInvoiceNumber();
        $this->showCreateModal = true;
    }
    
    public function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        
        $lastInvoice = DB::table('trade_receivables')
            ->where('invoice_number', 'like', "$prefix-$year$month-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        $this->invoice_number = "$prefix-$year$month-$newNumber";
    }
    
    public function save()
    {
        Log::info('Save method called for Trade and Other Receivables', [
            'user_id' => auth()->id(),
            'edit_mode' => $this->editMode,
            'receivable_id' => $this->receivableId
        ]);

        try {
            Log::info('Validating form data', [
                'customer_name' => $this->customer_name,
                'invoice_number' => $this->invoice_number,
                'invoice_date' => $this->invoice_date,
                'amount' => $this->amount,
                'total_amount' => $this->total_amount,
                'vat_amount' => $this->vat_amount
            ]);

            // Auto-calculate due_date if not provided
            if (!$this->due_date && $this->invoice_date && $this->payment_terms) {
                $this->due_date = Carbon::parse($this->invoice_date)
                    ->addDays($this->payment_terms)
                    ->format('Y-m-d');
                Log::info('Auto-calculated due_date', [
                    'invoice_date' => $this->invoice_date,
                    'payment_terms' => $this->payment_terms,
                    'due_date' => $this->due_date
                ]);
            }
            
            $this->validate();
            
            Log::info('Validation passed, starting transaction');
        } catch (\Exception $e) {
            Log::error('Validation failed', [
                'error' => $e->getMessage(),
                'errors' => $this->getErrorBag()->toArray()
            ]);
            throw $e;
        }
        
        DB::beginTransaction();
        try {
            // Get institution configuration for accounts
            $institution = DB::table('institutions')->where('id', 1)->first();
            
            if (!$institution || !$institution->trade_receivables_account) {
                throw new \Exception('Trade receivables account not configured in institution settings');
            }
            
            // Prepare data
            $data = [
                'customer_id' => $this->customer_id,
                'customer_name' => $this->customer_name,
                'customer_company' => $this->customer_company,
                'customer_email' => $this->customer_email,
                'customer_phone' => $this->customer_phone,
                'customer_address' => $this->customer_address,
                'invoice_number' => $this->invoice_number,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'amount' => $this->total_amount,  // total amount maps to amount
                'paid_amount' => 0,  // initially no payment
                'balance' => $this->total_amount,  // initial balance equals total
                'description' => $this->description,
                'status' => 'pending',
                'processing_status' => 'pending', // Will be updated by the job
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            Log::info('Prepared data for saving', ['data' => $data]);
            
            // Handle file upload
            if ($this->invoice_attachment) {
                $path = $this->invoice_attachment->store('receivables/invoices', 'public');
                $data['invoice_attachment'] = $path;
            }
            
            if ($this->editMode && $this->receivableId) {
                Log::info('Updating existing receivable', ['id' => $this->receivableId]);
                // Update existing receivable
                DB::table('trade_receivables')
                    ->where('id', $this->receivableId)
                    ->update($data);
                
                Log::info('Receivable updated successfully', ['id' => $this->receivableId]);
                $message = 'Receivable updated successfully!';
            } else {
                Log::info('Creating new receivable');
                // Create new receivable
                $receivableId = DB::table('trade_receivables')->insertGetId($data);
                Log::info('Receivable created with ID', ['id' => $receivableId]);
                
                // Create child account under Trade Receivables parent and post GL entries
                try {
                    // Get parent account for Trade Receivables
                    $parentAccount = DB::table('accounts')
                        ->where('account_number', $institution->trade_receivables_account)
                        ->first();
                    
                    if (!$parentAccount) {
                        throw new \Exception('Parent Trade Receivables account not found');
                    }
                    
                    // Create account using service
                    $accountService = new AccountCreationService();
                    $newAccount = $accountService->createAccount([
                        'account_use' => 'internal',
                        'account_name' => 'TR - ' . $this->invoice_number . ' - ' . $this->customer_name,
                        'type' => $parentAccount->type,
                        'product_number' => $parentAccount->product_number ?: '1510',
                        'member_number' => '00000', // Set to '00000' for internal accounts
                        'branch_number' => auth()->user()->branch
                    ], $institution->trade_receivables_account);
                    
                    $accountNumber = $newAccount->account_number;
                    
                    // Update the account status to ACTIVE immediately after creation
                    DB::table('accounts')
                        ->where('account_number', $accountNumber)
                        ->update(['status' => 'ACTIVE']);
                    
                    // Update receivable with account number
                    DB::table('trade_receivables')
                        ->where('id', $receivableId)
                        ->update(['account_number' => $accountNumber]);
                    
                    Log::info('Child account created for receivable using AccountCreationService', [
                        'account_number' => $accountNumber,
                        'account_id' => $newAccount->id,
                        'parent_account' => $institution->trade_receivables_account
                    ]);
                    
                    // Post GL entries using TransactionPostingService
                    $transactionService = new TransactionPostingService();
                    
                    // Entry 1: Record the receivable (excluding VAT)
                    if ($this->amount > 0) {
                        $receivableEntry = [
                            'first_account' => $accountNumber, // Debit: Trade Receivable child account
                            'second_account' => $institution->sales_revenue_account, // Credit: Sales Revenue
                            'amount' => $this->amount,
                            'narration' => 'Invoice ' . $this->invoice_number . ' - ' . $this->customer_name,
                            'action' => 'invoice_creation'
                        ];
                        
                        $result = $transactionService->postTransaction($receivableEntry);
                        if ($result['status'] !== 'success') {
                            throw new \Exception('Failed to post receivable entry: ' . ($result['message'] ?? 'Unknown error'));
                        }
                        
                        Log::info('Posted receivable GL entry', [
                            'debit' => $accountNumber,
                            'credit' => $institution->sales_revenue_account,
                            'amount' => $this->amount
                        ]);
                    }
                    
                    // Entry 2: Record VAT if applicable
                    if ($this->vat_amount > 0) {
                        $vatEntry = [
                            'first_account' => $accountNumber, // Debit: Trade Receivable child account
                            'second_account' => $institution->vat_payable_account, // Credit: VAT Payable
                            'amount' => $this->vat_amount,
                            'narration' => 'VAT on Invoice ' . $this->invoice_number,
                            'action' => 'invoice_creation'
                        ];
                        
                        $result = $transactionService->postTransaction($vatEntry);
                        if ($result['status'] !== 'success') {
                            throw new \Exception('Failed to post VAT entry: ' . ($result['message'] ?? 'Unknown error'));
                        }
                        
                        Log::info('Posted VAT GL entry', [
                            'debit' => $accountNumber,
                            'credit' => $institution->vat_payable_account,
                            'amount' => $this->vat_amount
                        ]);
                    }
                    
                    Log::info('Trade receivable fully integrated with accounts and GL', [
                        'receivable_id' => $receivableId,
                        'account_number' => $accountNumber,
                        'customer' => $this->customer_name,
                        'total_amount' => $this->total_amount
                    ]);
                    
                    // Dispatch job to generate and send invoice asynchronously
                    try {
                        ProcessTradeReceivableInvoice::dispatch($receivableId, auth()->id())
                            ->onQueue('invoices')
                            ->delay(now()->addSeconds(2)); // Small delay to ensure DB transaction is committed
                        
                        Log::info('Invoice processing job dispatched', [
                            'receivable_id' => $receivableId,
                            'user_id' => auth()->id()
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to dispatch invoice job', [
                            'error' => $e->getMessage(),
                            'receivable_id' => $receivableId
                        ]);
                        // Don't throw - receivable creation was successful even if job dispatch failed
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to integrate receivable with accounts table', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
                
                $message = 'Receivable created successfully!';
            }
            
            DB::commit();
            
            Log::info('Transaction committed successfully', [
                'message' => $message ?? 'Receivable saved'
            ]);
            
            $this->showCreateModal = false;
            $this->loadStatistics();
            
            // Show success message with info about background processing
            $successMessage = $message;
            if (!$this->editMode) {
                $successMessage .= ' Invoice generation and email/SMS notifications are being processed in the background.';
            }
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => $successMessage
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving receivable', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error saving receivable: ' . $e->getMessage()
            ]);
        }
    }
    
    private function createGLEntries($receivableId, $data)
    {
        // This method is now replaced by TransactionPostingService calls in save() method
        // The GL entries are created through proper double-entry posting
        // This method is kept empty for backward compatibility
        Log::info('createGLEntries called but skipped - GL entries created via TransactionPostingService in save() method');
    }
    
    public function openPaymentModal($receivableId)
    {
        Log::info('Payment modal opened', [
            'receivable_id' => $receivableId,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $receivable = DB::table('trade_receivables')->find($receivableId);
        
        if ($receivable) {
            Log::info('Payment modal data loaded', [
                'receivable_id' => $receivableId,
                'invoice_number' => $receivable->invoice_number,
                'balance' => $receivable->balance,
                'customer' => $receivable->customer_name
            ]);
            
            $this->payment_receivable_id = $receivableId;
            $this->selectedBalance = $receivable->balance; // Set the balance for validation
            $this->payment_amount = $receivable->balance;
            $this->payment_date = now()->format('Y-m-d');
            $this->payment_method = 'bank_transfer';
            
            // Ensure payment accounts is an array before loading
            if (!is_array($this->paymentAccounts)) {
                $this->paymentAccounts = [];
            }
            
            // Load payment accounts
            $this->loadPaymentAccounts();
            
            // Open modal after loading accounts
            $this->showPaymentModal = true;
        }
    }
    
    /**
     * Load available payment accounts from bank_accounts table
     */
    private function loadPaymentAccounts()
    {
        // Get bank accounts for payment - payment will only be by bank
        $accounts = DB::table('bank_accounts')
            ->where('status', 'ACTIVE')
            ->whereNotNull('internal_mirror_account_number') // Must have mirror account
            ->select(
                'internal_mirror_account_number as account_number',
                'bank_name',
                'account_name',
                'account_number as bank_account_number',
                'current_balance',
                'currency'
            )
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get(); // This returns a collection
        
        // Convert to array for Livewire - Livewire converts collections to arrays anyway
        // We explicitly convert to array to ensure consistency
        $this->paymentAccounts = $accounts ? $accounts->map(function ($account) {
            return (array) $account;
        })->toArray() : [];
        
        // Set default payment account if available
        if (!empty($this->paymentAccounts) && !$this->payment_account_id) {
            $this->payment_account_id = $this->paymentAccounts[0]['account_number']; // Use internal_mirror_account_number
        }
    }
    
    public function processPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $this->selectedBalance,
            'payment_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'payment_account_id' => 'required', // This will be the internal_mirror_account_number
        ], [
            'payment_amount.max' => 'Payment amount cannot exceed the outstanding balance of ' . number_format($this->selectedBalance, 2),
            'payment_date.before_or_equal' => 'Payment date cannot be in the future',
            'payment_account_id.required' => 'Please select a bank account for payment'
        ]);
        
        DB::beginTransaction();
        try {
            $receivable = DB::table('trade_receivables')->find($this->payment_receivable_id);
            
            if (!$receivable) {
                throw new \Exception('Receivable not found');
            }
            
            // Validate payment amount doesn't exceed balance
            if ($this->payment_amount > $receivable->balance) {
                throw new \Exception('Payment amount exceeds outstanding balance');
            }
            
            // Process the collection using transaction posting service directly
            // since we need to use the specific bank account
            $transactionResult = $this->processReceivablePaymentTransaction($receivable, $this->payment_amount);
            
            // Calculate new balance
            $newBalance = $receivable->balance - $this->payment_amount - $this->discount_amount;
            $totalPaid = $receivable->paid_amount + $this->payment_amount;
            
            // Determine new status
            $newStatus = 'partial';
            if ($newBalance <= 0) {
                $newStatus = 'paid';
            } elseif ($totalPaid == 0) {
                $newStatus = 'pending';
            }
            
            // Update receivable
            DB::table('trade_receivables')
                ->where('id', $this->payment_receivable_id)
                ->update([
                    'balance' => max(0, $newBalance),
                    'paid_amount' => $totalPaid,
                    'status' => $newStatus,
                    'last_payment_date' => $this->payment_date,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            
            // Update bill status if exists
            if ($receivable->bill_id) {
                $this->updateBillStatus($receivable->bill_id, $newBalance, $this->payment_amount);
            }
            
            // Record payment
            $paymentId = DB::table('receivable_payments')->insertGetId([
                'receivable_id' => $this->payment_receivable_id,
                'payment_date' => $this->payment_date,
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'reference_number' => $this->payment_reference,
                'payment_account_id' => $this->payment_account_id, // Bank account used
                'bank_charges' => $this->bank_charges ?? 0,
                'discount_amount' => $this->discount_amount ?? 0,
                'notes' => $this->payment_notes,
                'transaction_reference' => $transactionResult['reference_number'] ?? null, // GL reference from TransactionPostingService
                'status' => 'completed',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Handle file upload
            if ($this->payment_receipt) {
                $path = $this->payment_receipt->store('receivables/payments', 'public');
                DB::table('receivable_payments')
                    ->where('id', $paymentId)
                    ->update(['receipt_attachment' => $path]);
            }
            
            // GL entries are already created by processReceivablePaymentTransaction
            // No need for additional GL entries
            
            // Log the payment activity
            Log::info('Trade receivable payment processed', [
                'receivable_id' => $this->payment_receivable_id,
                'invoice_number' => $receivable->invoice_number,
                'payment_amount' => $this->payment_amount,
                'new_balance' => $newBalance,
                'new_status' => $newStatus,
                'payment_method' => $this->payment_method,
                'reference' => $this->payment_reference
            ]);
            
            // Send payment confirmation if email exists
            if ($receivable->customer_email) {
                $this->sendPaymentConfirmation($receivable, $this->payment_amount, $newBalance);
            }
            
            DB::commit();
            
            // Reset form and close modal
            $this->showPaymentModal = false;
            $this->reset(['payment_amount', 'payment_reference', 'payment_notes', 
                         'bank_charges', 'discount_amount', 'payment_receipt']);
            
            // Reload data
            $this->loadStatistics();
            $this->render(); // Refresh the component view
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Payment of ' . number_format($this->payment_amount, 2) . ' processed successfully! New balance: ' . number_format($newBalance, 2)
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment: ' . $e->getMessage(), [
                'receivable_id' => $this->payment_receivable_id,
                'payment_amount' => $this->payment_amount
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error processing payment: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Update bill status after payment
     */
    private function updateBillStatus($billId, $newBalance, $paymentAmount)
    {
        try {
            $bill = DB::table('bills')->find($billId);
            
            if (!$bill) {
                return;
            }
            
            // Calculate new bill status
            $newStatus = 'PENDING';
            if ($newBalance <= 0) {
                $newStatus = 'PAID';
            } elseif ($paymentAmount > 0) {
                $newStatus = 'PARTIAL';
            }
            
            // Update bill
            DB::table('bills')
                ->where('id', $billId)
                ->update([
                    'status' => $newStatus,
                    'amount_paid' => DB::raw('amount_paid + ' . $paymentAmount),
                    'balance' => $newBalance,
                    'last_payment_date' => $this->payment_date,
                    'updated_at' => now()
                ]);
            
            Log::info('Bill status updated after payment', [
                'bill_id' => $billId,
                'new_status' => $newStatus,
                'payment_amount' => $paymentAmount,
                'new_balance' => $newBalance
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update bill status', [
                'bill_id' => $billId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create reversal entries for cancelled receivable
     */
    private function createReversalEntries($receivable)
    {
        try {
            // Get the receivable account
            $receivableAccount = AccountsModel::find($receivable->account_id);
            if (!$receivableAccount) {
                throw new \Exception('Receivable account not found');
            }
            
            // Get income account
            $incomeAccount = AccountsModel::find($receivable->income_account_id);
            if (!$incomeAccount) {
                throw new \Exception('Income account not found');
            }
            
            // Use TransactionPostingService for reversal
            $transactionService = new TransactionPostingService();
            
            $reference = 'REV-' . $receivable->invoice_number;
            $description = 'Reversal/Cancellation of Invoice ' . $receivable->invoice_number;
            
            // Post reversal transaction (opposite of original)
            // Original was: Debit Receivables, Credit Income
            // Reversal is: Debit Income, Credit Receivables
            $transactionData = [
                'first_account' => $incomeAccount->account_number,      // Debit: Income Account
                'second_account' => $receivableAccount->account_number,  // Credit: Accounts Receivable
                'amount' => $receivable->amount,
                'narration' => $description,
                'action' => 'receivable_cancellation'
            ];
            
            $result = $transactionService->postTransaction($transactionData);
            
            Log::info('Receivable reversal entries created', [
                'invoice' => $receivable->invoice_number,
                'amount' => $receivable->amount,
                'reference' => $reference
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create reversal entries', [
                'invoice' => $receivable->invoice_number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process receivable payment transaction with specific bank account
     */
    private function processReceivablePaymentTransaction($receivable, $amount)
    {
        try {
            // Get the institution's receivables income account
            $institution = DB::table('institutions')->where('id', 1)->first();
            if (!$institution || !$institution->trade_receivables_income_account) {
                throw new \Exception('Institution receivables income account not configured');
            }
            
            // Get the income account details
            $incomeAccount = DB::table('accounts')
                ->where('account_number', $institution->trade_receivables_income_account)
                ->first();
            
            if (!$incomeAccount) {
                throw new \Exception('Trade receivables income account not found');
            }
            
            // Use TransactionPostingService for double entry
            $transactionService = new TransactionPostingService();
            
            // Post the payment transaction
            // Debit: Bank Account (payment_account_id contains internal_mirror_account_number)
            // Credit: Trade Receivables Income Account
            $reference = 'RCV-PAY-' . $receivable->invoice_number;
            $description = 'Payment received for invoice ' . $receivable->invoice_number . ' from ' . $receivable->customer_name;
            
            // Prepare transaction data in the format expected by postTransaction
            $transactionData = [
                'first_account' => $this->payment_account_id, // Bank account (internal_mirror_account_number) - will be debited
                'second_account' => $institution->trade_receivables_income_account, // Income account - will be credited
                'amount' => $amount,
                'narration' => $description,
                'action' => 'receivable_payment'
            ];
            
            $result = $transactionService->postTransaction($transactionData);
            
            Log::info('Receivable payment transaction posted', [
                'invoice' => $receivable->invoice_number,
                'amount' => $amount,
                'bank_account' => $this->payment_account_id,
                'income_account' => $institution->trade_receivables_income_account,
                'reference' => $result['reference_number'] ?? null
            ]);
            
            return $result; // Return the result for use in payment record
            
        } catch (\Exception $e) {
            Log::error('Failed to process receivable payment transaction', [
                'error' => $e->getMessage(),
                'invoice' => $receivable->invoice_number
            ]);
            throw $e;
        }
    }
    
    /**
     * Send payment confirmation email via queued job
     */
    private function sendPaymentConfirmation($receivable, $paymentAmount, $newBalance)
    {
        try {
            // Dispatch email job to queue
            \App\Jobs\SendPaymentConfirmationEmail::dispatch(
                $receivable,
                $paymentAmount,
                $newBalance,
                $this->payment_date,
                $this->payment_method,
                $this->payment_reference
            )->onQueue('emails');
            
            Log::info('Payment confirmation email job dispatched', [
                'email' => $receivable->customer_email,
                'invoice' => $receivable->invoice_number,
                'amount' => $paymentAmount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch payment confirmation email job', [
                'error' => $e->getMessage(),
                'email' => $receivable->customer_email,
                'invoice' => $receivable->invoice_number
            ]);
            // Don't throw - payment was successful even if email dispatch failed
        }
    }
    
    // REMOVED: createPaymentGLEntries method - replaced by processReceivablePaymentTransaction
    // which uses TransactionPostingService for proper double-entry posting
    
    private function DEPRECATED_createPaymentGLEntries($receivable, $paymentId)
    {
        $reference = 'PAY-' . str_pad($paymentId, 6, '0', STR_PAD_LEFT);
        $description = 'Payment for Invoice ' . $receivable->invoice_number;
        
        // Get the account ID from the internal_mirror_account_number
        $account = DB::table('accounts')
            ->where('account_number', $this->payment_account_id) // payment_account_id contains internal_mirror_account_number
            ->first();
        
        if (!$account) {
            Log::error('Mirror account not found for account number: ' . $this->payment_account_id);
            throw new \Exception('Payment account not found');
        }
        
        // Debit Bank/Cash Account
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYMENT',
            'transaction_date' => $this->payment_date,
            'account_id' => $account->id, // Use the actual account ID
            'debit_amount' => $this->payment_amount - ($this->bank_charges ?? 0),
            'credit_amount' => 0,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $paymentId,
            'source_type' => 'receivable_payments'
        ]);
        
        // Credit Accounts Receivable
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'PAYMENT',
            'transaction_date' => $this->payment_date,
            'account_id' => $receivable->account_id,
            'debit_amount' => 0,
            'credit_amount' => $this->payment_amount,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $paymentId,
            'source_type' => 'receivable_payments'
        ]);
        
        // Record bank charges if any
        if ($this->bank_charges > 0) {
            $bankChargesAccount = AccountsModel::where('account_name', 'like', '%bank charges%')
                ->where('account_type', 'EXPENSE')
                ->first();
            
            if ($bankChargesAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $bankChargesAccount->id,
                    'debit_amount' => $this->bank_charges,
                    'credit_amount' => 0,
                    'description' => 'Bank charges for ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'receivable_payments'
                ]);
            }
        }
        
        // Record discount if any
        if ($this->discount_amount > 0) {
            $discountAccount = AccountsModel::where('account_name', 'like', '%discount%')
                ->where('account_type', 'EXPENSE')
                ->first();
            
            if ($discountAccount) {
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $discountAccount->id,
                    'debit_amount' => $this->discount_amount,
                    'credit_amount' => 0,
                    'description' => 'Discount on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'receivable_payments'
                ]);
                
                // Also credit receivables for discount
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'PAYMENT',
                    'transaction_date' => $this->payment_date,
                    'account_id' => $receivable->account_id,
                    'debit_amount' => 0,
                    'credit_amount' => $this->discount_amount,
                    'description' => 'Discount adjustment for ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $paymentId,
                    'source_type' => 'receivable_payments'
                ]);
            }
        }
    }
    
    public function edit($id)
    {
        Log::info('Edit initiated for receivable', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $receivable = DB::table('trade_receivables')->find($id);
        
        if ($receivable) {
            Log::info('Loading receivable data for editing', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number,
                'customer' => $receivable->customer_name,
                'amount' => $receivable->amount,
                'status' => $receivable->status
            ]);
            
            $this->receivableId = $id;
            $this->customer_id = $receivable->customer_id;
            $this->customer_name = $receivable->customer_name;
            $this->customer_email = $receivable->customer_email;
            $this->customer_phone = $receivable->customer_phone;
            $this->invoice_number = $receivable->invoice_number;
            $this->invoice_date = $receivable->invoice_date;
            $this->due_date = $receivable->due_date;
            $this->amount = $receivable->amount;
            $this->vat_amount = $receivable->vat_amount ?? 0;
            $this->total_amount = $receivable->amount;
            $this->currency = $receivable->currency ?? 'TZS';
            $this->payment_terms = $receivable->payment_terms ?? 30;
            $this->description = $receivable->description;
            $this->reference_number = $receivable->reference_number;
            // These fields don't exist in the database - remove them
            // $this->account_id = $receivable->account_id;
            // $this->income_account_id = $receivable->income_account_id;
            // $this->notes = $receivable->notes;
            
            $this->editMode = true;
            $this->showCreateModal = true;
            
            Log::info('Edit modal opened', [
                'receivable_id' => $id,
                'edit_mode' => true
            ]);
        } else {
            Log::warning('Edit failed - receivable not found', [
                'receivable_id' => $id
            ]);
        }
    }
    
    /**
     * Show confirmation dialog for delete action
     */
    public function confirmDelete($id)
    {
        Log::info('Cancel/Delete confirmation initiated', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $this->confirmActionId = $id;
        $this->confirmAction = 'delete';
        $this->confirmTitle = 'Cancel Invoice';
        $this->confirmMessage = 'Are you sure you want to cancel this invoice? This will create reversal entries in the GL and cannot be undone.';
        $this->confirmButtonText = 'Yes, Cancel Invoice';
        $this->showConfirmModal = true;
    }
    
    /**
     * Show confirmation dialog for write-off action
     */
    public function confirmWriteOff($id)
    {
        Log::info('Write-off confirmation initiated', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $this->confirmActionId = $id;
        $this->confirmAction = 'writeOff';
        $this->confirmTitle = 'Write Off Receivable';
        $this->confirmMessage = 'Are you sure you want to write off this receivable as bad debt? This action cannot be undone.';
        $this->confirmButtonText = 'Yes, Write Off';
        $this->showConfirmModal = true;
    }
    
    /**
     * Proceed with the confirmed action
     */
    public function proceedWithAction()
    {
        Log::info('Proceeding with confirmed action', [
            'action' => $this->confirmAction,
            'receivable_id' => $this->confirmActionId,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        $this->showConfirmModal = false;
        
        if ($this->confirmAction && $this->confirmActionId) {
            switch ($this->confirmAction) {
                case 'delete':
                    Log::info('Executing delete/cancel action', [
                        'receivable_id' => $this->confirmActionId
                    ]);
                    $this->delete($this->confirmActionId);
                    break;
                case 'writeOff':
                    Log::info('Executing write-off action', [
                        'receivable_id' => $this->confirmActionId
                    ]);
                    $this->writeOff($this->confirmActionId);
                    break;
                default:
                    Log::warning('Unknown action attempted', [
                        'action' => $this->confirmAction,
                        'receivable_id' => $this->confirmActionId
                    ]);
            }
        } else {
            Log::warning('Proceed with action called but no action or ID set', [
                'action' => $this->confirmAction,
                'receivable_id' => $this->confirmActionId
            ]);
        }
        
        // Reset confirmation properties
        $this->confirmAction = '';
        $this->confirmActionId = null;
        $this->confirmTitle = '';
        $this->confirmMessage = '';
        $this->confirmButtonText = 'Confirm';
        
        Log::info('Action execution completed, modal properties reset');
    }
    
    public function delete($id)
    {
        Log::info('Starting delete/cancel process', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        DB::beginTransaction();
        try {
            // Check if receivable has payments
            $hasPayments = DB::table('receivable_payments')
                ->where('receivable_id', $id)
                ->exists();
            
            Log::info('Checking for existing payments', [
                'receivable_id' => $id,
                'has_payments' => $hasPayments
            ]);
            
            if ($hasPayments) {
                throw new \Exception('Cannot delete receivable with payments. Please reverse payments first.');
            }
            
            // Get receivable details for reversal
            $receivable = DB::table('trade_receivables')->find($id);
            
            if (!$receivable) {
                throw new \Exception('Receivable not found');
            }
            
            // Only allow deletion if no payments have been made
            if ($receivable->balance != $receivable->amount) {
                throw new \Exception('Cannot delete receivable with partial payments.');
            }
            
            // Create reversal entries instead of deleting GL entries
            // This maintains audit trail
            $this->createReversalEntries($receivable);
            
            // Soft delete receivable (mark as cancelled)
            DB::table('trade_receivables')
                ->where('id', $id)
                ->update([
                    'status' => 'cancelled',
                    'deleted_at' => now(),
                    'updated_by' => auth()->id()
                ]);
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Receivable deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function writeOff($id)
    {
        Log::info('Starting write-off process', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        DB::beginTransaction();
        try {
            $receivable = DB::table('trade_receivables')->find($id);
            
            Log::info('Receivable data retrieved for write-off', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number ?? null,
                'balance' => $receivable->balance ?? null,
                'status' => $receivable->status ?? null
            ]);
            
            if (!$receivable) {
                throw new \Exception('Receivable not found');
            }
            
            // Update receivable status
            DB::table('trade_receivables')
                ->where('id', $id)
                ->update([
                    'status' => 'written_off',
                    'write_off_date' => now(),
                    'write_off_reason' => $this->provision_notes,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            
            // Create GL entries for write-off using TransactionPostingService
            $reference = 'WO-' . $receivable->invoice_number;
            $description = 'Write-off Invoice ' . $receivable->invoice_number;
            
            // Get bad debt expense account
            $badDebtAccount = AccountsModel::where('account_name', 'like', '%bad debt%')
                ->where('account_type', 'EXPENSE')
                ->first();
            
            if (!$badDebtAccount) {
                // Create bad debt account if it doesn't exist
                $badDebtAccount = AccountsModel::where('account_name', 'Bad Debt Expense')->first();
                if (!$badDebtAccount) {
                    throw new \Exception('Bad debt expense account not found. Please configure the account first.');
                }
            }
            
            // Get the receivable account details
            $receivableAccount = AccountsModel::find($receivable->account_id);
            if (!$receivableAccount) {
                throw new \Exception('Receivable account not found');
            }
            
            // Use TransactionPostingService for proper double-entry posting
            $transactionService = new TransactionPostingService();
            
            // Post write-off transaction
            // Debit: Bad Debt Expense
            // Credit: Accounts Receivable
            $transactionData = [
                'first_account' => $badDebtAccount->account_number,        // Debit: Bad Debt Expense
                'second_account' => $receivableAccount->account_number,      // Credit: Accounts Receivable
                'amount' => $receivable->balance,
                'narration' => $description,
                'action' => 'receivable_writeoff'
            ];
            
            $result = $transactionService->postTransaction($transactionData);
            
            Log::info('Receivable written off', [
                'invoice' => $receivable->invoice_number,
                'amount' => $receivable->balance,
                'bad_debt_account' => $badDebtAccount->account_number,
                'receivable_account' => $receivableAccount->account_number
            ]);
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Receivable written off successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error writing off receivable: ' . $e->getMessage()
            ]);
        }
    }
    
    public function exportToExcel()
    {
        // Export functionality would be implemented here
        $this->dispatchBrowserEvent('alert', [
            'type' => 'info',
            'message' => 'Export functionality will be implemented'
        ]);
    }
    
    public function sendReminder($id)
    {
        Log::info('Payment reminder initiated', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        try {
            $receivable = DB::table('trade_receivables')->find($id);
            
            if (!$receivable) {
                Log::warning('Reminder failed - receivable not found', [
                    'receivable_id' => $id
                ]);
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Receivable not found'
                ]);
                return;
            }
            
            Log::info('Sending payment reminder', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number,
                'customer_email' => $receivable->customer_email,
                'days_overdue' => $receivable->days_overdue ?? 0,
                'balance' => $receivable->balance
            ]);
            
            // TODO: Implement actual reminder sending logic here
            // This would dispatch a job to send email/SMS reminder
            
            // Update reminder tracking
            DB::table('trade_receivables')
                ->where('id', $id)
                ->update([
                    'last_reminder_sent_at' => now(),
                    'reminder_count' => DB::raw('reminder_count + 1'),
                    'updated_at' => now()
                ]);
            
            Log::info('Payment reminder sent successfully', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Reminder sent successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder', [
                'receivable_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Failed to send reminder: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Download invoice PDF
     */
    public function downloadInvoice($id)
    {
        Log::info('Invoice download initiated', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        try {
            $receivable = DB::table('trade_receivables')->find($id);
            
            if (!$receivable) {
                Log::warning('Download failed - receivable not found', [
                    'receivable_id' => $id
                ]);
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Receivable not found'
                ]);
                return;
            }
            
            Log::info('Checking invoice file existence', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number,
                'file_path' => $receivable->invoice_file_path
            ]);
            
            // Check if invoice file exists
            if ($receivable->invoice_file_path && file_exists(storage_path('app/' . $receivable->invoice_file_path))) {
                Log::info('Invoice file found, preparing download', [
                    'receivable_id' => $id,
                    'file_path' => $receivable->invoice_file_path
                ]);
                // Store the file path in session for download via route
                session(['download_invoice_path' => storage_path('app/' . $receivable->invoice_file_path)]);
                session(['download_invoice_name' => 'invoice_' . $receivable->invoice_number . '.pdf']);
                
                // Redirect to download route
                return redirect()->route('download.invoice');
            } else {
                // Invoice doesn't exist, offer to regenerate
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'warning',
                    'message' => 'Invoice PDF not found. Regenerating invoice...'
                ]);
                
                $this->regenerateInvoice($id);
            }
        } catch (\Exception $e) {
            Log::error('Error downloading invoice', [
                'receivable_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error downloading invoice: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Regenerate invoice PDF
     */
    public function regenerateInvoice($id)
    {
        try {
            $receivable = DB::table('trade_receivables')->find($id);
            
            if (!$receivable) {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Receivable not found'
                ]);
                return;
            }
            
            // Dispatch job to regenerate invoice
            ProcessTradeReceivableInvoice::dispatch($id, auth()->id())
                ->onQueue('invoices');
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'info',
                'message' => 'Invoice is being regenerated. Please check back in a moment.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error regenerating invoice', [
                'receivable_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error regenerating invoice: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * View invoice online
     */
    public function viewInvoice($id)
    {
        Log::info('Invoice view initiated', [
            'receivable_id' => $id,
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);
        
        try {
            $receivable = DB::table('trade_receivables')->find($id);
            
            if (!$receivable) {
                Log::warning('View failed - receivable not found', [
                    'receivable_id' => $id
                ]);
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Receivable not found'
                ]);
                return;
            }
            
            Log::info('Checking invoice file for viewing', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number,
                'file_path' => $receivable->invoice_file_path
            ]);
            
            // Check if invoice file exists
            if (!$receivable->invoice_file_path) {
                Log::warning('Invoice PDF not yet generated', [
                    'receivable_id' => $id
                ]);
                session()->flash('error', 'Invoice PDF not yet generated. Please wait for processing to complete.');
                return;
            }
            
            $path = storage_path('app/' . $receivable->invoice_file_path);
            if (!file_exists($path)) {
                Log::error('Invoice file not found on disk', [
                    'receivable_id' => $id,
                    'expected_path' => $path
                ]);
                session()->flash('error', 'Invoice file not found on disk. It may need to be regenerated.');
                return;
            }
            
            Log::info('Invoice ready for viewing', [
                'receivable_id' => $id,
                'invoice_number' => $receivable->invoice_number
            ]);
            
            // Store invoice ID in session for viewing via route
            session(['view_invoice_id' => $id]);
            
            // Simply redirect to the view route - browser will handle opening PDF
            return redirect()->route('view.invoice');
            
        } catch (\Exception $e) {
            Log::error('Error viewing invoice', [
                'receivable_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Error viewing invoice: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate and send invoice with payment link
     */
    private function generateAndSendInvoice($receivableId)
    {
        Log::info('Starting invoice generation and email process', ['receivable_id' => $receivableId]);
        
        // Get the receivable data
        $receivable = DB::table('trade_receivables')->find($receivableId);
        if (!$receivable) {
            throw new \Exception('Receivable not found');
        }
        
        // Get institution data
        $institution = DB::table('institutions')->where('id', 1)->first();
        
        // Generate control number and create bill
        $controlNumber = null;
        $billId = null;
        try {
            $billData = $this->createBillForReceivable($receivable);
            $controlNumber = $billData['control_number'];
            $billId = $billData['bill_id'];
            
            // Update receivable with control number
            DB::table('trade_receivables')
                ->where('id', $receivableId)
                ->update([
                    'control_number' => $controlNumber,
                    'bill_id' => $billId
                ]);
            
            Log::info('Bill created for receivable', [
                'receivable_id' => $receivableId,
                'control_number' => $controlNumber,
                'bill_id' => $billId
            ]);
            
            // Reload receivable to get updated control_number and bill_id
            $receivable = DB::table('trade_receivables')->find($receivableId);
        } catch (\Exception $e) {
            Log::error('Failed to create bill for receivable', [
                'error' => $e->getMessage(),
                'receivable_id' => $receivableId
            ]);
            // Continue without bill/control number
        }
        
        // Generate payment link
        $paymentUrl = null;
        try {
            $paymentUrl = $this->generatePaymentLink($receivable);
            
            // Update receivable with payment link
            if ($paymentUrl) {
                DB::table('trade_receivables')
                    ->where('id', $receivableId)
                    ->update([
                        'payment_link' => $paymentUrl,
                        'payment_link_generated_at' => now()
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate payment link', [
                'error' => $e->getMessage(),
                'receivable_id' => $receivableId
            ]);
            // Continue without payment link
        }
        
        // Generate PDF invoice
        $pdfPath = $this->generateInvoicePDF($receivable, $institution, $paymentUrl);
        
        // Send email with invoice
        if ($receivable->customer_email) {
            $this->sendInvoiceEmail($receivable, $pdfPath, $paymentUrl);
        } else {
            Log::warning('Customer email not provided, invoice not sent', [
                'receivable_id' => $receivableId,
                'customer' => $receivable->customer_name
            ]);
        }
        
        // Send SMS notification with payment link
        if ($receivable->customer_phone) {
            try {
                $this->sendInvoiceSms($receivable, $paymentUrl, $controlNumber);
            } catch (\Exception $e) {
                Log::error('Failed to send SMS notification', [
                    'error' => $e->getMessage(),
                    'receivable_id' => $receivableId
                ]);
                // Continue - SMS failure shouldn't stop the process
            }
        }
        
        // Clean up temporary PDF file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
    }
    
    /**
     * Generate payment link for receivable
     */
    private function generatePaymentLink($receivable)
    {
        Log::info('Generating payment link for receivable', [
            'invoice_number' => $receivable->invoice_number,
            'amount' => $receivable->balance
        ]);
        
        $paymentService = new PaymentLinkService();
        
        // Check if we have a bill for this receivable
        $bill = null;
        if (isset($receivable->bill_id)) {
            $bill = DB::table('bills')->find($receivable->bill_id);
        }
        
        // Create single payment item (no splitting needed anymore)
        $items = [[
            'type' => 'service',
            'product_service_reference' => $receivable->invoice_number,
            'product_service_name' => 'Invoice ' . $receivable->invoice_number,
            'description' => $receivable->description ?: 'Invoice payment',
            'amount' => floatval($receivable->balance),
            'control_number' => $bill ? $bill->control_number : null,
            'bill_id' => $bill ? $bill->id : null,
            'is_required' => true,
            'allow_partial' => true
        ]];
        
        $paymentData = [
            'description' => 'Invoice Payment - ' . $receivable->invoice_number,
            'target' => 'individual',
            'customer_reference' => $receivable->invoice_number,
            'customer_name' => $receivable->customer_name,
            'customer_phone' => $receivable->customer_phone ?: '',
            'customer_email' => $receivable->customer_email ?: '',
            'total_amount' => $receivable->balance,
            'expires_at' => Carbon::parse($receivable->due_date)->addDays(7)->toIso8601String(),
            'callback_url' => config('app.url') . '/api/payment-callback/invoice',
            'items' => $items
        ];
        
        $response = $paymentService->generateUniversalPaymentLink($paymentData);
        
        if (isset($response['data']['payment_url'])) {
            Log::info('Payment link generated successfully', [
                'payment_url' => $response['data']['payment_url'],
                'invoice_number' => $receivable->invoice_number
            ]);
            return $response['data']['payment_url'];
        }
        
        throw new \Exception('Payment URL not found in response');
    }
    
    /**
     * Generate invoice PDF
     */
    private function generateInvoicePDF($receivable, $institution, $paymentUrl = null)
    {
        Log::info('Generating invoice PDF', ['invoice_number' => $receivable->invoice_number]);
        
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $receivable,
            'institution' => $institution,
            'paymentUrl' => $paymentUrl
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        
        // Save PDF to temporary location
        $filename = 'invoice_' . $receivable->invoice_number . '_' . time() . '.pdf';
        $path = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $pdf->save($path);
        
        Log::info('Invoice PDF generated', [
            'path' => $path,
            'size' => filesize($path)
        ]);
        
        return $path;
    }
    
    /**
     * Send invoice email with PDF attachment
     */
    private function sendInvoiceEmail($receivable, $pdfPath, $paymentUrl = null)
    {
        Log::info('Sending invoice email', [
            'to' => $receivable->customer_email,
            'invoice_number' => $receivable->invoice_number
        ]);
        
        $emailService = new EmailService();
        
        // Prepare email body
        $emailBody = $this->prepareInvoiceEmailBody($receivable, $paymentUrl);
        
        // Prepare attachments
        $attachments = [];
        if (file_exists($pdfPath)) {
            $attachments[] = [
                'path' => $pdfPath,
                'name' => 'Invoice_' . $receivable->invoice_number . '.pdf',
                'mime' => 'application/pdf'
            ];
        }
        
        $emailData = [
            'to' => $receivable->customer_email,
            'subject' => 'Invoice ' . $receivable->invoice_number . ' - ' . ($receivable->customer_company ?: $receivable->customer_name),
            'body' => $emailBody,
            'attachments' => $attachments,
            'from_name' => 'SACCOS Billing',
            'reply_to' => 'billing@saccos.org'
        ];
        
        try {
            $result = $emailService->sendEmail($emailData, false); // false = no undo feature for system emails
            
            Log::info('Invoice email sent successfully', [
                'email_id' => $result['email_id'] ?? null,
                'invoice_number' => $receivable->invoice_number
            ]);
            
            // Update receivable to mark invoice as sent
            DB::table('trade_receivables')
                ->where('id', $receivable->id)
                ->update([
                    'invoice_sent' => true,
                    'invoice_sent_at' => now(),
                    'invoice_sent_to' => $receivable->customer_email
                ]);
                
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'error' => $e->getMessage(),
                'invoice_number' => $receivable->invoice_number
            ]);
            throw $e;
        }
    }
    
    /**
     * Prepare invoice email body
     */
    private function prepareInvoiceEmailBody($receivable, $paymentUrl = null)
    {
        $dueDate = Carbon::parse($receivable->due_date);
        $isOverdue = $dueDate->isPast();
        
        $body = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">';
        $body .= '<h2 style="color: #333;">Invoice ' . $receivable->invoice_number . '</h2>';
        
        $body .= '<p>Dear ' . $receivable->customer_name . ',</p>';
        
        if ($isOverdue) {
            $body .= '<p style="color: #d9534f;"><strong>This invoice is overdue. Please arrange payment immediately.</strong></p>';
        } else {
            $body .= '<p>Please find attached your invoice for the amount of <strong>' . 
                     ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2) . '</strong>.</p>';
        }
        
        $body .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $body .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Invoice Number:</strong></td>';
        $body .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $receivable->invoice_number . '</td></tr>';
        $body .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Invoice Date:</strong></td>';
        $body .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . Carbon::parse($receivable->invoice_date)->format('d M Y') . '</td></tr>';
        $body .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Due Date:</strong></td>';
        $body .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;">' . $dueDate->format('d M Y') . '</td></tr>';
        $body .= '<tr><td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>Amount Due:</strong></td>';
        $body .= '<td style="padding: 8px; border-bottom: 1px solid #ddd;"><strong>' . 
                 ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2) . '</strong></td></tr>';
        $body .= '</table>';
        
        if ($paymentUrl) {
            $body .= '<div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            $body .= '<h3 style="color: #333; margin-top: 0;">Pay Online</h3>';
            $body .= '<p>For your convenience, you can pay this invoice online using our secure payment portal:</p>';
            $body .= '<p><a href="' . $paymentUrl . '" style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">Pay Now</a></p>';
            $body .= '<p style="font-size: 12px; color: #666;">Or copy this link: ' . $paymentUrl . '</p>';
            $body .= '</div>';
        }
        
        $body .= '<p>If you have any questions about this invoice, please don\'t hesitate to contact us.</p>';
        $body .= '<p>Thank you for your business!</p>';
        
        $body .= '<hr style="margin: 30px 0; border: none; border-top: 1px solid #ddd;">';
        $body .= '<p style="font-size: 12px; color: #666;">This is an automated email. Please do not reply directly to this message.</p>';
        $body .= '</div>';
        
        return $body;
    }
    
    /**
     * Create bill for trade receivable
     */
    private function createBillForReceivable($receivable)
    {
        Log::info('Creating bill for receivable', [
            'invoice_number' => $receivable->invoice_number,
            'amount' => $receivable->balance
        ]);
        
        $billingService = new BillingService();
        
        // We'll use the service ID from the database, not a hardcoded value
        
        // Use the Trade Receivables service we created (code: TRD)
        $service = DB::table('services')
            ->where('code', 'TRD') // Trade Receivables Payment service
            ->first();
            
        if (!$service) {
            // Fallback: create the service if it doesn't exist
            Log::warning('TRD service not found, creating it');
            $serviceId = DB::table('services')->insertGetId([
                'code' => 'TRD',
                'name' => 'Trade Receivables Payment',
                'description' => 'Payment for trade receivables and invoices',
                'is_mandatory' => false,
                'is_recurring' => false,
                'payment_mode' => '1', // Partial payment allowed
                'paymentMode' => '1', // Also set paymentMode column for compatibility
                'lower_limit' => 100,
                'upper_limit' => 100000000,
                'debit_account' => '0101100015001510',
                'credit_account' => DB::table('institutions')->where('id', 1)->value('trade_receivables_income_account') ?: '0101400045004520',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $serviceId = $service->id;
        }
        
        // Generate control number
        // Use customer_id if available, otherwise use a hash of invoice number
        $clientNumber = $receivable->customer_id ?: substr(md5($receivable->invoice_number), 0, 10);
        
        $controlNumber = $billingService->generateControlNumber(
            $clientNumber,
            $serviceId,
            0, // Not recurring
            1  // Partial payment mode
        );
        
        // Create bill
        $billId = $billingService->createBill(
            $clientNumber,
            $serviceId,
            0, // Not recurring  
            1, // Partial payment mode
            $controlNumber,
            $receivable->balance
        );
        
        Log::info('Bill created successfully', [
            'bill_id' => $billId,
            'control_number' => $controlNumber,
            'invoice_number' => $receivable->invoice_number
        ]);
        
        return [
            'bill_id' => $billId,
            'control_number' => $controlNumber
        ];
    }
    
    /**
     * Send invoice SMS notification
     */
    private function sendInvoiceSms($receivable, $paymentUrl, $controlNumber)
    {
        Log::info('Sending invoice SMS', [
            'to' => $receivable->customer_phone,
            'invoice_number' => $receivable->invoice_number
        ]);
        
        $smsService = new SmsService();
        
        // Format amount
        $amount = ($receivable->currency ?: 'TZS') . ' ' . number_format($receivable->balance, 2);
        
        // Prepare SMS message
        $message = "Dear {$receivable->customer_name},\n";
        $message .= "Invoice {$receivable->invoice_number} for {$amount} has been generated.\n";
        
        if ($controlNumber) {
            $message .= "Control No: {$controlNumber}\n";
        }
        
        $message .= "Due: " . Carbon::parse($receivable->due_date)->format('d/m/Y') . "\n";
        
        // Add payment link if available
        if ($paymentUrl) {
            // Shorten the URL if possible or use a URL shortener service
            $message .= "Pay online: {$paymentUrl}\n";
        }
        
        $message .= "Thank you for your business.";
        
        // Send SMS
        try {
            $result = $smsService->send(
                $receivable->customer_phone,
                $message,
                null,
                [
                    'smsType' => 'TRANSACTIONAL',
                    'serviceName' => 'INVOICE',
                    'language' => 'English'
                ]
            );
            
            Log::info('Invoice SMS sent successfully', [
                'phone' => $receivable->customer_phone,
                'invoice_number' => $receivable->invoice_number,
                'result' => $result
            ]);
            
            // Update receivable to track SMS sent
            DB::table('trade_receivables')
                ->where('id', $receivable->id)
                ->update([
                    'sms_sent' => true,
                    'sms_sent_at' => now(),
                    'sms_sent_to' => $receivable->customer_phone
                ]);
                
        } catch (\Exception $e) {
            Log::error('Failed to send invoice SMS', [
                'error' => $e->getMessage(),
                'phone' => $receivable->customer_phone,
                'invoice_number' => $receivable->invoice_number
            ]);
            throw $e;
        }
    }
    
    public function render()
    {
        $query = DB::table('trade_receivables')
            ->select([
                'trade_receivables.*',
                DB::raw("(CURRENT_DATE - trade_receivables.due_date::date) as days_overdue")
            ]);
        
        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('trade_receivables.customer_name', 'like', '%' . $this->search . '%')
                  ->orWhere('trade_receivables.invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('trade_receivables.description', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            $query->where('trade_receivables.status', $this->statusFilter);
        }
        
        if ($this->ageFilter && $this->ageFilter !== 'all') {
            $today = Carbon::now();
            switch($this->ageFilter) {
                case 'current':
                    $query->where('trade_receivables.due_date', '>=', $today);
                    break;
                case '30':
                    $query->whereBetween('trade_receivables.due_date', [$today->copy()->subDays(30), $today]);
                    break;
                case '60':
                    $query->whereBetween('trade_receivables.due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)]);
                    break;
                case '90':
                    $query->whereBetween('trade_receivables.due_date', [$today->copy()->subDays(90), $today->copy()->subDays(61)]);
                    break;
                case 'over90':
                    $query->where('trade_receivables.due_date', '<', $today->copy()->subDays(90));
                    break;
            }
        }
        
        if ($this->dateFrom) {
            $query->where('trade_receivables.invoice_date', '>=', $this->dateFrom);
        }
        
        if ($this->dateTo) {
            $query->where('trade_receivables.invoice_date', '<=', $this->dateTo);
        }
        
        $receivables = $query->orderBy('trade_receivables.created_at', 'desc')
            ->paginate(10);
        
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '1000') // Asset accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%RECEIVABLE%')
                      ->orWhere('account_name', 'LIKE', '%DEBTOR%')
                      ->orWhere('account_name', 'LIKE', '%ASSET%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
            
        $otherAccounts = DB::table('bank_accounts')
            
            



            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.trade-and-other-receivables', [
            'receivables' => $receivables,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}