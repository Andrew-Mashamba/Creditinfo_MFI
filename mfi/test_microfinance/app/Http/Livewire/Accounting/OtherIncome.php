<?php

namespace App\Http\Livewire\Accounting;

use App\Models\AccountsModel;
use App\Models\general_ledger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use App\Services\BalanceSheetItemIntegrationService;
use App\Services\IncomeManagementService;

class OtherIncome extends Component
{
    use WithPagination, WithFileUploads;

    // View Control
    public $activeTab = 'overview';
    public $showCreateModal = false;
    public $showDetailsModal = false;
    public $showReversalModal = false;
    public $showAccruedIncomeModal = false;
    public $showAuditTrailModal = false;
    public $showVerificationModal = false;
    public $editMode = false;
    public $accruedMode = false;
    
    // Search and Filters
    public $search = '';
    public $categoryFilter = 'all';
    public $statusFilter = 'all';
    public $dateFrom;
    public $dateTo;
    
    // Income Form Data
    public $incomeId;
    public $income_date;
    public $income_category = '';
    public $income_source = '';
    public $description = '';
    public $reference_number = '';
    public $amount = 0;
    public $tax_amount = 0;
    public $net_amount = 0;
    public $currency = 'TZS';
    public $payment_method = 'bank_transfer';
    public $bank_account_id;
    public $income_account_id;
    public $received_from = '';
    public $receipt_number = '';
    public $notes = '';
    public $status = 'received';
    public $recurring = false;
    public $recurring_frequency = 'monthly';
    public $recurring_end_date;
    
    // File Uploads
    public $receipt_attachment;
    public $supporting_documents = [];

    // Account selection for proper flow
    public $parent_account_number; // Parent account to create income account under
    public $other_account_id; // The other account for double-entry (Cash/Bank - debit side)

    // Reversal
    public $reversalIncomeId;
    public $reversalReason = '';

    // Accrued Income
    public $accrual_date;
    public $expected_receipt_date;
    public $is_accrued = false;
    public $accrued_income_receivable_account_id;

    // Audit Trail
    public $auditTrailData = [];
    public $auditTrailIncomeId;

    // Verification
    public $verificationData = [];
    
    // Statistics
    public $totalIncome = 0;
    public $totalThisMonth = 0;
    public $totalThisYear = 0;
    public $averageMonthlyIncome = 0;
    public $incomeByCategory = [];
    public $incomeGrowthRate = 0;
    
    // Collections
    public $incomeCategories = [];
    public $incomeAccounts = [];
    public $bankAccounts = [];
    
    // Income Categories
    protected $predefinedCategories = [
        'rental_income' => 'Rental Income',
        'investment_income' => 'Investment Income',
        'commission_income' => 'Commission Income',
        'dividend_income' => 'Dividend Income',
        'grant_income' => 'Grant Income',
        'donation_income' => 'Donation Income',
        'foreign_exchange_gain' => 'Foreign Exchange Gain',
        'asset_disposal_gain' => 'Gain on Asset Disposal',
        'insurance_claim' => 'Insurance Claim Settlement',
        'penalty_income' => 'Penalty & Fine Income',
        'miscellaneous_income' => 'Miscellaneous Income',
        'consultancy_fees' => 'Consultancy Fees',
        'training_fees' => 'Training Fees',
        'service_charges' => 'Service Charges',
        'other' => 'Other Income'
    ];
    
    protected $rules = [
        'income_date' => 'required|date',
        'income_category' => 'required',
        'income_source' => 'required|min:3',
        'amount' => 'required|numeric|min:0',
        'payment_method' => 'required',
        'bank_account_id' => 'required|exists:accounts,id',
        'income_account_id' => 'required|exists:accounts,id',
        'description' => 'required|min:5',
        'currency' => 'required|in:TZS,USD,EUR,GBP',
        'tax_amount' => 'nullable|numeric|min:0',
        'receipt_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
    ];
    
    protected $listeners = [
        'refreshIncome' => 'loadStatistics',
        'deleteIncome' => 'delete',
        'generateRecurring' => 'processRecurringIncome',
        'reverseIncome' => 'confirmReversal',
        'viewAuditTrail' => 'showAuditTrail',
        'verifyIncome' => 'verifyIncomeRecognition',
    ];
    
    public function mount()
    {
        $this->initializeData();
        $this->loadStatistics();
        $this->income_date = now()->format('Y-m-d');
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }
    
    public function initializeData()
    {
        // Load income categories
        $this->incomeCategories = collect($this->predefinedCategories);

        // Load income accounts (using major_category_code = '4000' for income accounts)
        // Only load detail accounts (level 3 and above) to prevent posting to parent accounts
        $this->incomeAccounts = AccountsModel::where('major_category_code', '4000')
            ->where('type', 'income_accounts')
            ->where('status', 'ACTIVE')
            ->whereRaw('CAST(account_level AS INTEGER) >= ?', [3])
            ->orderBy('account_name')
            ->get();
        
        // Load bank accounts for receipts
        // Only load detail accounts (level 3 and above) to prevent posting to parent accounts
        // Must be asset accounts only (major_category_code 1000) to prevent confusion with income accounts
        $this->bankAccounts = AccountsModel::where(function($query) {
                $query->where('account_name', 'LIKE', '%BANK%')
                      ->orWhere('account_name', 'LIKE', '%CASH%');
            })
            ->where('major_category_code', '1000')
            ->where('type', 'asset_accounts')
            ->where('status', 'ACTIVE')
            ->whereRaw('CAST(account_level AS INTEGER) >= ?', [3])
            ->orderBy('account_name')
            ->get();

        // Load accrued income receivable accounts (Asset accounts)
        // Only load detail accounts (level 3 and above) to prevent posting to parent accounts
        $accruedReceivableAccount = AccountsModel::where('account_name', 'LIKE', '%accrued%income%receivable%')
            ->where('major_category_code', '1000') // Asset account
            ->where('type', 'asset_accounts')
            ->where('status', 'ACTIVE')
            ->whereRaw('CAST(account_level AS INTEGER) >= ?', [3])
            ->first();

        if ($accruedReceivableAccount) {
            $this->accrued_income_receivable_account_id = $accruedReceivableAccount->id;
        }
    }
    
    public function loadStatistics()
    {
        // Check if other_income table exists
        if (!Schema::hasTable('other_income')) {
            $this->totalIncome = 0;
            $this->totalThisMonth = 0;
            $this->totalThisYear = 0;
            $this->averageMonthlyIncome = 0;
            $this->incomeGrowthRate = 0;
            $this->incomeByCategory = [];
            return;
        }
        
        $query = DB::table('other_income');
        
        // Apply date filters
        if ($this->dateFrom) {
            $query->where('income_date', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->where('income_date', '<=', $this->dateTo);
        }
        
        // Calculate totals
        $this->totalIncome = $query->sum('net_amount') ?? 0;
        
        // This month's income
        $this->totalThisMonth = DB::table('other_income')
            ->whereRaw('EXTRACT(YEAR FROM income_date) = ?', [now()->year])
            ->whereRaw('EXTRACT(MONTH FROM income_date) = ?', [now()->month])
            ->sum('net_amount') ?? 0;
        
        // This year's income
        $this->totalThisYear = DB::table('other_income')
            ->whereRaw('EXTRACT(YEAR FROM income_date) = ?', [now()->year])
            ->sum('net_amount') ?? 0;
        
        // Average monthly income (last 12 months)
        $monthlyIncome = DB::table('other_income')
            ->where('income_date', '>=', now()->subMonths(12))
            ->selectRaw('EXTRACT(YEAR FROM income_date) as year, EXTRACT(MONTH FROM income_date) as month, SUM(net_amount) as total')
            ->groupBy('year', 'month')
            ->get();
        
        $this->averageMonthlyIncome = $monthlyIncome->avg('total') ?? 0;
        
        // Income by category
        $this->incomeByCategory = DB::table('other_income')
            ->select('income_category', DB::raw('SUM(net_amount) as total'))
            ->where('income_date', '>=', now()->startOfYear())
            ->groupBy('income_category')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->income_category => $item->total];
            })
            ->toArray();
        
        // Calculate growth rate (compared to previous period)
        $currentPeriod = DB::table('other_income')
            ->whereBetween('income_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('net_amount') ?? 0;
        
        $previousPeriod = DB::table('other_income')
            ->whereBetween('income_date', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('net_amount') ?? 0;
        
        if ($previousPeriod > 0) {
            $this->incomeGrowthRate = (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
        } else {
            $this->incomeGrowthRate = $currentPeriod > 0 ? 100 : 0;
        }
    }
    
    public function updatedAmount()
    {
        $this->calculateNetAmount();
    }

    public function updatedTaxAmount()
    {
        $this->calculateNetAmount();
    }

    public function calculateNetAmount()
    {
        $amount = floatval($this->amount ?? 0);
        $taxAmount = floatval($this->tax_amount ?? 0);
        $this->net_amount = $amount - $taxAmount;
    }
    
    public function openCreateModal()
    {
        $this->reset(['incomeId', 'income_category', 'income_source', 'description', 
                     'reference_number', 'amount', 'tax_amount', 'net_amount', 
                     'received_from', 'receipt_number', 'notes', 'recurring', 
                     'recurring_frequency', 'recurring_end_date']);
        
        $this->editMode = false;
        $this->income_date = now()->format('Y-m-d');
        $this->payment_method = 'bank_transfer';
        $this->status = 'received';
        $this->generateReceiptNumber();
        $this->showCreateModal = true;
    }
    
    public function generateReceiptNumber()
    {
        $prefix = 'OI';
        $year = date('Y');
        $month = date('m');
        
        if (Schema::hasTable('other_income')) {
            $lastReceipt = DB::table('other_income')
                ->where('receipt_number', 'like', "$prefix-$year$month-%")
                ->orderBy('receipt_number', 'desc')
                ->first();
            
            if ($lastReceipt) {
                $lastNumber = intval(substr($lastReceipt->receipt_number, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }
        } else {
            $newNumber = '0001';
        }
        
        $this->receipt_number = "$prefix-$year$month-$newNumber";
    }
    
    public function save()
    {
        Log::info('💰 Starting income save operation', [
            'user_id' => auth()->id(),
            'edit_mode' => $this->editMode,
            'income_id' => $this->incomeId ?? 'new',
            'amount' => $this->amount,
            'income_category' => $this->income_category,
            'receipt_number' => $this->receipt_number
        ]);

        try {
            // Validate input
            $this->validate();
            Log::info('✓ Validation passed');

            $incomeService = app(IncomeManagementService::class);

            // Prepare data
            $data = [
                'income_date' => $this->income_date,
                'income_category' => $this->income_category,
                'income_source' => $this->income_source,
                'description' => $this->description,
                'reference_number' => $this->reference_number,
                'amount' => $this->amount,
                'tax_amount' => $this->tax_amount,
                'currency' => $this->currency,
                'payment_method' => $this->payment_method,
                'bank_account_id' => $this->bank_account_id,
                'income_account_id' => $this->income_account_id,
                'received_from' => $this->received_from,
                'receipt_number' => $this->receipt_number,
                'notes' => $this->notes,
                'status' => $this->status,
                'recurring' => $this->recurring,
                'recurring_frequency' => $this->recurring ? $this->recurring_frequency : null,
                'recurring_end_date' => $this->recurring ? $this->recurring_end_date : null,
            ];

            Log::info('📝 Income data prepared', [
                'receipt_number' => $data['receipt_number'],
                'amount' => $data['amount'],
                'tax_amount' => $data['tax_amount'],
                'net_amount' => $data['amount'] - $data['tax_amount'],
                'bank_account_id' => $data['bank_account_id'],
                'income_account_id' => $data['income_account_id']
            ]);

            // Handle file upload
            if ($this->receipt_attachment) {
                Log::info('📎 Uploading receipt attachment');
                $path = $this->receipt_attachment->store('other_income/receipts', 'public');
                $data['receipt_attachment'] = $path;
                Log::info('✓ Receipt attachment uploaded', ['path' => $path]);
            }

            if ($this->editMode && $this->incomeId) {
                Log::info('✏️ Updating existing income record', ['income_id' => $this->incomeId]);

                // Update existing income - use direct DB update for now
                // IncomeManagementService doesn't have update method, so we keep legacy behavior
                DB::beginTransaction();
                try {
                    $data['updated_by'] = auth()->id();
                    $data['updated_at'] = now();

                    DB::table('other_income')
                        ->where('id', $this->incomeId)
                        ->update($data);

                    DB::commit();

                    Log::info('✅ Income record updated successfully', [
                        'income_id' => $this->incomeId,
                        'receipt_number' => $data['receipt_number']
                    ]);

                    $message = 'Income record updated successfully!';
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('❌ Failed to update income record', [
                        'income_id' => $this->incomeId,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            } else {
                Log::info('➕ Creating new income record');

                // Create new income using IncomeManagementService
                $result = $incomeService->recordIncome($data);

                if (!$result['success']) {
                    Log::error('❌ IncomeManagementService returned failure', [
                        'message' => $result['message'] ?? 'Unknown error'
                    ]);
                    throw new \Exception($result['message'] ?? 'Failed to record income');
                }

                Log::info('✅ Income record created successfully', [
                    'income_id' => $result['income_id'],
                    'reference_number' => $result['reference_number']
                ]);

                // If recurring, create schedule
                if ($this->recurring) {
                    Log::info('🔄 Creating recurring income schedule', [
                        'income_id' => $result['income_id'],
                        'frequency' => $this->recurring_frequency,
                        'end_date' => $this->recurring_end_date
                    ]);

                    $this->createRecurringSchedule($result['income_id'], $data);

                    Log::info('✓ Recurring schedule created');
                }

                $message = 'Income recorded successfully!';
            }

            $this->showCreateModal = false;
            $this->loadStatistics();

            Log::info('✅ Income save operation completed successfully', [
                'message' => $message,
                'user_id' => auth()->id()
            ]);

            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => $message
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('⚠️ Validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            // Re-throw validation exception so Livewire can handle it
            throw $e;

        } catch (\Exception $e) {
            Log::error('❌ Error saving other income', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'edit_mode' => $this->editMode,
                'income_id' => $this->incomeId ?? null
            ]);

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error saving income: ' . $e->getMessage()
            ]);
        }
    }
    
    private function createGLEntries($incomeId, $data)
    {
        $reference = 'OI-' . $data['receipt_number'];
        $description = $data['income_category'] . ' - ' . $data['description'];
        
        // Debit Bank/Cash Account
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'OTHER_INCOME',
            'transaction_date' => $data['income_date'],
            'account_id' => $data['bank_account_id'],
            'debit_amount' => $data['net_amount'],
            'credit_amount' => 0,
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $incomeId,
            'source_type' => 'other_income'
        ]);
        
        // Credit Income Account
        general_ledger::create([
            'reference_number' => $reference,
            'transaction_type' => 'OTHER_INCOME',
            'transaction_date' => $data['income_date'],
            'account_id' => $data['income_account_id'],
            'debit_amount' => 0,
            'credit_amount' => $data['amount'],
            'description' => $description,
            'created_by' => auth()->id(),
            'status' => 'POSTED',
            'source_id' => $incomeId,
            'source_type' => 'other_income'
        ]);
        
        // If tax was withheld
        if ($data['tax_amount'] > 0) {
            $taxAccount = AccountsModel::where(function($query) {
                    $query->where('account_name', 'like', '%tax%payable%')
                          ->orWhere('account_name', 'like', '%withholding%tax%');
                })
                ->where('major_category_code', '2000') // Liability accounts
                ->where('type', 'liability_accounts')
                ->where('status', 'ACTIVE')
                ->first();
            
            if ($taxAccount) {
                // Debit Bank (additional for tax withheld)
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'OTHER_INCOME',
                    'transaction_date' => $data['income_date'],
                    'account_id' => $data['bank_account_id'],
                    'debit_amount' => $data['tax_amount'],
                    'credit_amount' => 0,
                    'description' => 'Tax withheld on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $incomeId,
                    'source_type' => 'other_income'
                ]);
                
                // Credit Tax Payable
                general_ledger::create([
                    'reference_number' => $reference,
                    'transaction_type' => 'OTHER_INCOME',
                    'transaction_date' => $data['income_date'],
                    'account_id' => $taxAccount->id,
                    'debit_amount' => 0,
                    'credit_amount' => $data['tax_amount'],
                    'description' => 'Tax withheld on ' . $description,
                    'created_by' => auth()->id(),
                    'status' => 'POSTED',
                    'source_id' => $incomeId,
                    'source_type' => 'other_income'
                ]);
            }
        }
    }
    
    private function createRecurringSchedule($incomeId, $data)
    {
        if (!Schema::hasTable('recurring_income_schedules')) {
            Log::warning('⚠️ recurring_income_schedules table does not exist, skipping schedule creation');
            return;
        }

        try {
            $nextDate = Carbon::parse($data['income_date']);
            $endDate = $data['recurring_end_date'] ? Carbon::parse($data['recurring_end_date']) : null;
            $calculatedNextDate = $this->getNextRecurringDate($nextDate, $data['recurring_frequency']);

            Log::info('📅 Preparing recurring schedule', [
                'income_id' => $incomeId,
                'frequency' => $data['recurring_frequency'],
                'next_date' => $calculatedNextDate->format('Y-m-d'),
                'end_date' => $endDate ? $endDate->format('Y-m-d') : 'no end date'
            ]);

            // Create recurring schedule
            DB::table('recurring_income_schedules')->insert([
                'other_income_id' => $incomeId,
                'frequency' => $data['recurring_frequency'],
                'next_date' => $calculatedNextDate,
                'end_date' => $endDate,
                'is_active' => true,
                'created_by' => auth()->id(),
                'created_at' => now(),
            ]);

            Log::info('✅ Recurring schedule created successfully', [
                'income_id' => $incomeId,
                'next_occurrence' => $calculatedNextDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to create recurring schedule', [
                'income_id' => $incomeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    private function getNextRecurringDate($currentDate, $frequency)
    {
        switch ($frequency) {
            case 'weekly':
                return $currentDate->addWeek();
            case 'monthly':
                return $currentDate->addMonth();
            case 'quarterly':
                return $currentDate->addQuarter();
            case 'semi_annually':
                return $currentDate->addMonths(6);
            case 'annually':
                return $currentDate->addYear();
            default:
                return $currentDate->addMonth();
        }
    }
    
    public function processRecurringIncome()
    {
        if (!Schema::hasTable('recurring_income_schedules')) {
            return;
        }
        
        $recurringItems = DB::table('recurring_income_schedules')
            ->join('other_income', 'recurring_income_schedules.other_income_id', '=', 'other_income.id')
            ->where('recurring_income_schedules.is_active', true)
            ->where('recurring_income_schedules.next_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('recurring_income_schedules.end_date')
                      ->orWhere('recurring_income_schedules.end_date', '>=', now());
            })
            ->select('other_income.*', 'recurring_income_schedules.id as schedule_id', 
                    'recurring_income_schedules.frequency', 'recurring_income_schedules.next_date')
            ->get();
        
        foreach ($recurringItems as $item) {
            DB::beginTransaction();
            try {
                // Create new income entry
                $newData = [
                    'income_date' => now(),
                    'income_category' => $item->income_category,
                    'income_source' => $item->income_source,
                    'description' => $item->description . ' (Recurring)',
                    'reference_number' => $item->reference_number . '-' . now()->format('Ymd'),
                    'amount' => $item->amount,
                    'tax_amount' => $item->tax_amount,
                    'net_amount' => $item->net_amount,
                    'currency' => $item->currency,
                    'payment_method' => $item->payment_method,
                    'bank_account_id' => $item->bank_account_id,
                    'income_account_id' => $item->income_account_id,
                    'received_from' => $item->received_from,
                    'receipt_number' => $this->generateNewReceiptNumber(),
                    'notes' => 'Auto-generated from recurring income',
                    'status' => 'received',
                    'recurring' => false,
                    'created_by' => $item->created_by,
                    'created_at' => now(),
                ];
                
                $newIncomeId = DB::table('other_income')->insertGetId($newData);
                
                // Create GL entries
                $this->createGLEntries($newIncomeId, $newData);
                
                // Update next date
                $nextDate = $this->getNextRecurringDate(Carbon::parse($item->next_date), $item->frequency);
                DB::table('recurring_income_schedules')
                    ->where('id', $item->schedule_id)
                    ->update(['next_date' => $nextDate, 'updated_at' => now()]);
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing recurring income: ' . $e->getMessage());
            }
        }
    }
    
    private function generateNewReceiptNumber()
    {
        $prefix = 'OI';
        $timestamp = now()->format('YmdHis');
        return "$prefix-$timestamp";
    }
    
    public function edit($id)
    {
        $income = DB::table('other_income')->find($id);
        
        if ($income) {
            $this->incomeId = $id;
            $this->income_date = $income->income_date;
            $this->income_category = $income->income_category;
            $this->income_source = $income->income_source;
            $this->description = $income->description;
            $this->reference_number = $income->reference_number;
            $this->amount = $income->amount;
            $this->tax_amount = $income->tax_amount;
            $this->net_amount = $income->net_amount;
            $this->currency = $income->currency;
            $this->payment_method = $income->payment_method;
            $this->bank_account_id = $income->bank_account_id;
            $this->income_account_id = $income->income_account_id;
            $this->received_from = $income->received_from;
            $this->receipt_number = $income->receipt_number;
            $this->notes = $income->notes;
            $this->recurring = $income->recurring;
            $this->recurring_frequency = $income->recurring_frequency;
            $this->recurring_end_date = $income->recurring_end_date;
            
            $this->editMode = true;
            $this->showCreateModal = true;
        }
    }
    
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            // Delete GL entries
            general_ledger::where('source_type', 'other_income')
                ->where('source_id', $id)
                ->delete();
            
            // Delete recurring schedule if exists
            if (Schema::hasTable('recurring_income_schedules')) {
                DB::table('recurring_income_schedules')
                    ->where('other_income_id', $id)
                    ->delete();
            }
            
            // Delete income record
            DB::table('other_income')->where('id', $id)->delete();
            
            DB::commit();
            
            $this->loadStatistics();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'success',
                'message' => 'Income record deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error deleting income: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Open reversal confirmation modal
     */
    public function confirmReversal($id)
    {
        $income = DB::table('other_income')->find($id);

        if ($income) {
            // Check if already reversed
            if ($income->status === 'reversed') {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'This income has already been reversed!'
                ]);
                return;
            }

            $this->reversalIncomeId = $id;
            $this->reversalReason = '';
            $this->showReversalModal = true;
        }
    }

    /**
     * Reverse income transaction using IncomeManagementService
     */
    public function reverseIncome()
    {
        $this->validate([
            'reversalReason' => 'required|min:10'
        ]);

        try {
            $incomeService = app(IncomeManagementService::class);
            $result = $incomeService->reverseIncome($this->reversalIncomeId, $this->reversalReason);

            if ($result['success']) {
                $this->showReversalModal = false;
                $this->loadStatistics();

                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Income transaction reversed successfully!'
                ]);
            } else {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Failed to reverse income transaction'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error reversing income: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error reversing income: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Open accrued income modal
     */
    public function openAccruedIncomeModal()
    {
        $this->reset(['incomeId', 'income_category', 'income_source', 'description',
                     'reference_number', 'amount', 'tax_amount', 'net_amount',
                     'received_from', 'notes', 'accrual_date', 'expected_receipt_date']);

        $this->accruedMode = true;
        $this->editMode = false;
        $this->is_accrued = true;
        $this->income_date = now()->format('Y-m-d');
        $this->accrual_date = now()->format('Y-m-d');
        $this->status = 'accrued';
        $this->showAccruedIncomeModal = true;
    }

    /**
     * Save accrued income using IncomeManagementService
     */
    public function saveAccruedIncome()
    {
        $this->validate([
            'income_date' => 'required|date',
            'income_category' => 'required',
            'income_source' => 'required|min:3',
            'amount' => 'required|numeric|min:0',
            'income_account_id' => 'required|exists:accounts,id',
            'accrued_income_receivable_account_id' => 'required|exists:accounts,id',
            'description' => 'required|min:5',
            'accrual_date' => 'required|date',
            'expected_receipt_date' => 'required|date|after:accrual_date',
        ]);

        try {
            $incomeService = app(IncomeManagementService::class);

            $data = [
                'income_date' => $this->income_date,
                'income_category' => $this->income_category,
                'income_source' => $this->income_source,
                'description' => $this->description,
                'reference_number' => $this->reference_number,
                'amount' => $this->amount,
                'tax_amount' => $this->tax_amount,
                'currency' => $this->currency,
                'income_account_id' => $this->income_account_id,
                'accrued_income_receivable_account_id' => $this->accrued_income_receivable_account_id,
                'received_from' => $this->received_from,
                'notes' => $this->notes,
                'accrual_date' => $this->accrual_date,
                'expected_receipt_date' => $this->expected_receipt_date,
            ];

            $result = $incomeService->recordAccruedIncome($data);

            if ($result['success']) {
                $this->showAccruedIncomeModal = false;
                $this->loadStatistics();

                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Accrued income recorded successfully!'
                ]);
            } else {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Failed to record accrued income'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error recording accrued income: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error recording accrued income: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Realize accrued income when received
     */
    public function realizeAccruedIncome($accruedIncomeId)
    {
        try {
            $incomeService = app(IncomeManagementService::class);

            // Get bank account and realized amount
            $income = DB::table('other_income')->find($accruedIncomeId);

            if (!$income || $income->status !== 'accrued') {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => 'Invalid accrued income record'
                ]);
                return;
            }

            $data = [
                'bank_account_id' => $this->bank_account_id ?? $income->bank_account_id,
                'realized_amount' => $income->amount,
                'realization_date' => now(),
                'payment_method' => $this->payment_method ?? 'bank_transfer',
                'receipt_number' => $this->generateReceiptNumber(),
            ];

            $result = $incomeService->realizeAccruedIncome($accruedIncomeId, $data);

            if ($result['success']) {
                $this->loadStatistics();

                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Accrued income realized successfully!'
                ]);
            } else {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Failed to realize accrued income'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error realizing accrued income: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error realizing accrued income: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show audit trail for an income record
     */
    public function showAuditTrail($incomeId = null)
    {
        try {
            $incomeService = app(IncomeManagementService::class);

            $filters = [];
            if ($incomeId) {
                $this->auditTrailIncomeId = $incomeId;
                $filters['income_id'] = $incomeId;
            }

            // Apply date filters if set
            if ($this->dateFrom) {
                $filters['date_from'] = $this->dateFrom;
            }
            if ($this->dateTo) {
                $filters['date_to'] = $this->dateTo;
            }

            $this->auditTrailData = $incomeService->getAuditTrail($incomeId, $filters);
            $this->showAuditTrailModal = true;

        } catch (\Exception $e) {
            Log::error('Error loading audit trail: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error loading audit trail: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verify income recognition
     */
    public function verifyIncomeRecognition($incomeId)
    {
        try {
            $incomeService = app(IncomeManagementService::class);
            $this->verificationData = $incomeService->verifyIncomeRecognition($incomeId);
            $this->showVerificationModal = true;

        } catch (\Exception $e) {
            Log::error('Error verifying income: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error verifying income: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export income report to Excel using IncomeManagementService
     */
    public function exportToExcel()
    {
        try {
            $incomeService = app(IncomeManagementService::class);

            $filters = [];

            // Apply filters
            if ($this->search) {
                $filters['search'] = $this->search;
            }

            if ($this->categoryFilter && $this->categoryFilter !== 'all') {
                $filters['income_category'] = $this->categoryFilter;
            }

            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $filters['status'] = $this->statusFilter;
            }

            if ($this->dateFrom) {
                $filters['date_from'] = $this->dateFrom;
            }

            if ($this->dateTo) {
                $filters['date_to'] = $this->dateTo;
            }

            $result = $incomeService->generateIncomeReport($filters);

            if ($result['success']) {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'success',
                    'message' => 'Report generated successfully! Downloading...'
                ]);

                // Trigger download
                return response()->download($result['file_path'], $result['file_name'])
                    ->deleteFileAfterSend(true);
            } else {
                $this->dispatchBrowserEvent('alert', [
                    'type' => 'error',
                    'message' => $result['message'] ?? 'Failed to generate report'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating income report: ' . $e->getMessage());

            $this->dispatchBrowserEvent('alert', [
                'type' => 'error',
                'message' => 'Error generating report: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        $incomeRecords = collect();
        
        if (Schema::hasTable('other_income')) {
            $query = DB::table('other_income')
                ->leftJoin('accounts as bank_account', 'other_income.bank_account_id', '=', 'bank_account.id')
                ->leftJoin('accounts as income_account', 'other_income.income_account_id', '=', 'income_account.id')
                ->select([
                    'other_income.*',
                    'bank_account.account_name as bank_account_name',
                    'income_account.account_name as income_account_name'
                ]);
            
            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('other_income.income_source', 'like', '%' . $this->search . '%')
                      ->orWhere('other_income.description', 'like', '%' . $this->search . '%')
                      ->orWhere('other_income.reference_number', 'like', '%' . $this->search . '%')
                      ->orWhere('other_income.receipt_number', 'like', '%' . $this->search . '%')
                      ->orWhere('other_income.received_from', 'like', '%' . $this->search . '%');
                });
            }
            
            if ($this->categoryFilter && $this->categoryFilter !== 'all') {
                $query->where('other_income.income_category', $this->categoryFilter);
            }
            
            if ($this->statusFilter && $this->statusFilter !== 'all') {
                $query->where('other_income.status', $this->statusFilter);
            }
            
            if ($this->dateFrom) {
                $query->where('other_income.income_date', '>=', $this->dateFrom);
            }
            
            if ($this->dateTo) {
                $query->where('other_income.income_date', '<=', $this->dateTo);
            }
            
            $incomeRecords = $query->orderBy('other_income.income_date', 'desc')
                ->orderBy('other_income.created_at', 'desc')
                ->paginate(10);
        } else {
            $incomeRecords = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                10
            );
        }
        
        // Get accounts for account selection
        $parentAccounts = DB::table('accounts')
            ->where('major_category_code', '4000') // Income accounts
            ->where('account_level', '<=', 2) // Parent level accounts only
            ->where(function($query) {
                $query->where('account_name', 'LIKE', '%INCOME%')
                      ->orWhere('account_name', 'LIKE', '%REVENUE%')
                      ->orWhere('account_name', 'LIKE', '%OTHER%');
            })
            ->where('status', 'ACTIVE')
            ->orderBy('account_name')
            ->get();
        
        $otherAccounts = DB::table('bank_accounts')
            
            


            
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();

        return view('livewire.accounting.other-income', [
            'incomeRecords' => $incomeRecords,
            'parentAccounts' => $parentAccounts,
            'otherAccounts' => $otherAccounts
        ]);
    }
}