<?php

namespace App\Http\Livewire\Shares;

use Livewire\Component;
use App\Models\ShareRegister;
use App\Models\ClientsModel;
use App\Models\AccountsModel;
use App\Services\DividendCalculationService;
use App\Services\NbcPayments\InternalFundTransferService;
use App\Jobs\ProcessDividendPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class DividendOverview extends Component
{
    use WithPagination;

    // Properties for dividend declaration
    public $year;
    public $dividendRate;
    public $paymentMode = 'bank';
    public $narration;
    public $showDeclareModal = false;

    // Properties for dividend listing
    public $search = '';
    public $statusFilter = '';
    public $yearFilter = '';
    public $sortField = 'member_name';
    public $sortDirection = 'asc';

    // Properties for dividend details
    public $selectedDividend;
    public $showDetailsModal = false;

    // Properties for payment processing state
    public $isProcessing = false;
    public $processingProgress = 0;
    public $processingStatus = '';
    public $processingMessage = '';
    public $totalToProcess = 0;
    public $processedCount = 0;
    public $failedCount = 0;
    public $currentProcessId = '';


    // Summary data
    public $summary = [
        'total_dividends' => 0,
        'total_paid' => 0,
        'total_pending' => 0,
        'member_count' => 0,
        'total_share_capital' => 0,
        'total_share_value' => 0,
        'active_members' => 0,
        'distributable_profit' => 0,
        'years' => []
    ];

    protected $rules = [
        'year' => 'required|integer|min:2000|max:2100',
        'dividendRate' => 'required|numeric|min:0|max:100',
        'paymentMode' => 'required|in:bank,cash,shares',
        'narration' => 'required|string|min:10'
    ];

    public function mount()
    {
        $this->year = date('Y');
        $this->calculateSummary();
    }

    public function render()
    {
        $query = ShareRegister::query()
            ->select([
                'id',
                'member_id',
                'member_number',
                'member_name',
                'current_share_balance as total_shares',
                'current_price as share_value',
                'last_dividend_rate as dividend_rate',
                'accumulated_dividends as dividend_amount',
                'total_paid_dividends',
                'total_pending_dividends',
                'last_dividend_date as calculated_at',
                'last_dividend_date as paid_at',
                DB::raw("CASE 
                    WHEN CAST(total_pending_dividends AS DECIMAL(20,6)) > 0 THEN 'pending'
                    WHEN CAST(total_paid_dividends AS DECIMAL(20,6)) > 0 THEN 'paid'
                    ELSE 'pending'
                END as status"),
                DB::raw("CASE 
                    WHEN CAST(total_pending_dividends AS DECIMAL(20,6)) > 0 THEN CAST(total_pending_dividends AS DECIMAL(20,6))
                    ELSE CAST(accumulated_dividends AS DECIMAL(20,6))
                END as current_dividend_amount")
            ])
            ->where('status', 'ACTIVE')
            ->where('current_share_balance', '>', 0)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('member_number', 'like', '%' . $this->search . '%')
                        ->orWhere('member_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'paid') {
                    $query->whereRaw('CAST(total_paid_dividends AS DECIMAL(20,6)) > 0');
                } elseif ($this->statusFilter === 'pending') {
                    $query->whereRaw('CAST(total_pending_dividends AS DECIMAL(20,6)) > 0');
                }
            })
            ->when($this->yearFilter, function ($query) {
                $query->whereYear('last_dividend_date', $this->yearFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $dividends = $query->paginate(10);

        // Recalculate summary before rendering
        $this->calculateSummary();

        return view('livewire.shares.dividend-overview', [
            'dividends' => $dividends,
            'summary' => $this->summary
        ]);
    }

    public function calculateSummary()
    {
        // Get share register statistics for dividend calculations
        $shareStats = ShareRegister::select([
            DB::raw('SUM(current_share_balance * current_price) as total_share_value'),
            DB::raw('SUM(current_share_balance * nominal_price) as total_share_capital'),
            DB::raw('COUNT(DISTINCT member_id) as total_members'),
            DB::raw('SUM(CAST(accumulated_dividends AS DECIMAL(20,6))) as total_accumulated_dividends'),
            DB::raw('SUM(CAST(total_paid_dividends AS DECIMAL(20,6))) as total_paid_dividends'),
            DB::raw('SUM(CAST(total_pending_dividends AS DECIMAL(20,6))) as total_pending_dividends')
        ])
        ->where('status', 'ACTIVE')
        ->where('current_share_balance', '>', 0)
        ->first();

        // Get active members count from clients table
        $activeMembers = ClientsModel::where('status', 'ACTIVE')->count();

        // Get available years from share registers
        $years = ShareRegister::select(DB::raw('EXTRACT(YEAR FROM last_dividend_date) as year'))
            ->whereNotNull('last_dividend_date')
            ->where('status', 'ACTIVE')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->toArray();

        // Get total distributable profit from accounts table
        $distributableProfit = AccountsModel::where('major_category_code', '4000')
            ->where('sub_category_code', '4001')
            ->sum('balance');

        $this->summary = [
            'total_dividends' => $shareStats->total_accumulated_dividends ?? 0,
            'total_paid' => $shareStats->total_paid_dividends ?? 0,
            'total_pending' => $shareStats->total_pending_dividends ?? 0,
            'member_count' => $shareStats->total_members ?? 0,
            'total_share_capital' => $shareStats->total_share_capital ?? 0,
            'total_share_value' => $shareStats->total_share_value ?? 0,
            'active_members' => $activeMembers,
            'distributable_profit' => $distributableProfit,
            'years' => $years
        ];
    }

    public function declareDividend()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Calculate dividends for all active share registers
            $shareRegisters = ShareRegister::where('status', 'ACTIVE')
                ->where('current_share_balance', '>', 0)
                ->get();

            foreach ($shareRegisters as $shareRegister) {
                // Calculate dividend amount based on current share balance and rate
                $dividendAmount = ($shareRegister->current_share_balance * $shareRegister->current_price * $this->dividendRate) / 100;
                
                // Get current values and cast them to numeric
                $currentAccumulated = floatval($shareRegister->accumulated_dividends ?? 0);
                $currentPending = floatval($shareRegister->total_pending_dividends ?? 0);
                
                // Update the share register with new dividend information
                $shareRegister->update([
                    'last_dividend_rate' => $this->dividendRate,
                    'last_dividend_amount' => $dividendAmount,
                    'last_dividend_date' => now(),
                    'accumulated_dividends' => $currentAccumulated + $dividendAmount,
                    'total_pending_dividends' => $currentPending + $dividendAmount,
                    'last_transaction_type' => 'DIVIDEND_DECLARED',
                    'last_transaction_reference' => "DIV_{$this->year}_{$this->dividendRate}%",
                    'last_transaction_date' => now(),
                    'last_activity_date' => now()
                ]);
            }

            DB::commit();
            $this->calculateSummary();
            $this->showDeclareModal = false;
            $this->reset(['dividendRate', 'paymentMode', 'narration']);
            session()->flash('message', 'Dividend declared successfully for ' . $shareRegisters->count() . ' members.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to declare dividend: ' . $e->getMessage());
        }
    }

    public function processPayments()
    {
        $this->isProcessing = true;
        $this->processingProgress = 0;
        $this->processingStatus = 'initializing';
        $this->processingMessage = 'Initializing payment processing...';
        $this->processedCount = 0;
        $this->failedCount = 0;
        
        $processId = uniqid('DIV_PAY_');
        $this->currentProcessId = $processId;
        
        $logContext = ['processId' => $processId, 'function' => 'processPayments'];
        
        Log::info('Starting dividend payment processing', $logContext);
        
        try {
            $this->processingStatus = 'fetching_data';
            $this->processingMessage = 'Fetching pending dividends...';
            
            // Get all share registers with pending dividends
            $shareRegisters = ShareRegister::where('status', 'ACTIVE')
                ->whereRaw('CAST(total_pending_dividends AS DECIMAL(20,6)) > 0')
                ->get();
            
            $this->totalToProcess = $shareRegisters->count();
            
            Log::info('Found share registers with pending dividends', [
                'processId' => $processId,
                'totalRegisters' => $shareRegisters->count()
            ]);
            
            if ($shareRegisters->isEmpty()) {
                $this->processingStatus = 'completed';
                $this->processingMessage = 'No pending dividends found to process.';
                $this->processingProgress = 100;
                $this->isProcessing = false;
                
                Log::info('No pending dividends found to process', $logContext);
                session()->flash('message', 'No pending dividends found to process.');
                return;
            }
            
            $this->processingStatus = 'configuring';
            $this->processingMessage = 'Loading institution configuration...';
            
            // Get institution and bank account details once
            $institution = DB::table('institutions')->where('id', '1')->first();
            if (!$institution) {
                throw new \Exception('Institution configuration not found');
            }
            
            $bankAccount = DB::table('bank_accounts')
                ->where('internal_mirror_account_number', $institution->operations_account)
                ->first();
            if (!$bankAccount) {
                throw new \Exception('Bank account configuration not found for operations account');
            }
            
            $debitAccount = $bankAccount->account_number;
            
            Log::info('Retrieved institution configuration', [
                'processId' => $processId,
                'operationsAccount' => $institution->operations_account,
                'debitAccount' => $debitAccount
            ]);
            
            $jobsDispatched = 0;
            $totalAmount = 0;
            
            $this->processingStatus = 'dispatching';
            $this->processingMessage = 'Dispatching payment jobs...';
            
            foreach ($shareRegisters as $index => $shareRegister) {
                $currentPending = floatval($shareRegister->total_pending_dividends ?? 0);
                
                if ($currentPending <= 0) {
                    Log::warning('Skipping member with zero pending dividends', [
                        'processId' => $processId,
                        'memberId' => $shareRegister->member_id
                    ]);
                    continue;
                }
                
                // Update progress
                $progress = (($index + 1) / $shareRegisters->count()) * 100;
                $this->processingProgress = round($progress);
                $this->processingMessage = "Processing member {$shareRegister->member_name} ({$shareRegister->member_number})...";
                
                // Get member's account number
                $client = DB::table('clients')->where('client_number', $shareRegister->member_number)->first();
                if (!$client || !$client->account_number) {
                    Log::error('Client account not found', [
                        'processId' => $processId,
                        'memberId' => $shareRegister->member_number
                    ]);
                    $this->failedCount++;
                    continue;
                }
                
                // Get the member's savings account for dividend payment
                $savingsAccount = DB::table('accounts')
                    ->where('client_number', $shareRegister->member_number)
                    ->where('product_number', '2000') // Savings account
                    ->first();
                
                if (!$savingsAccount) {
                    Log::warning('Savings account not found for member', [
                        'processId' => $processId,
                        'memberNumber' => $shareRegister->member_number
                    ]);
                    continue;
                }
                
                $creditAccount = $savingsAccount->account_number;
                
                // Prepare transfer data
                $transferData = [
                    'creditAccount'   => $creditAccount,
                    'creditCurrency'  => 'TZS',
                    'debitAccount'    => $debitAccount,
                    'debitCurrency'   => 'TZS',
                    'amount'          => $currentPending,
                    'narration'       => "Dividend payment for {$shareRegister->member_name} - {$this->year}",
                    'pyrName'         => $shareRegister->member_name
                ];
                
                Log::info('Dispatching dividend payment job', [
                    'processId' => $processId,
                    'memberId' => $shareRegister->member_id,
                    'memberName' => $shareRegister->member_name,
                    'amount' => $currentPending
                ]);
                
                //Dispatch background job
                ProcessDividendPayment::dispatch(
                    $shareRegister->id,
                    $transferData,
                    $processId,
                    $this->year
                );

                $jobsDispatched++;
                $totalAmount += $currentPending;
                $this->processedCount++;
            }
            
            $this->processingStatus = 'completed';
            $this->processingProgress = 100;
            $this->processingMessage = "Successfully dispatched {$jobsDispatched} payment jobs. Total amount: " . number_format($totalAmount, 2) . " TZS";
            $this->isProcessing = false;
            
            Log::info('Dividend payment jobs dispatched', [
                'processId' => $processId,
                'jobsDispatched' => $jobsDispatched,
                'totalAmount' => $totalAmount
            ]);
            
            // Update summary
            $this->calculateSummary();
            
            // Prepare user message
            $message = "Dividend payment jobs dispatched for {$jobsDispatched} members. Total amount: " . number_format($totalAmount, 2) . " TZS. Payments will be processed in the background.";
            
            session()->flash('message', $message);
            session()->flash('info', "Process ID: {$processId}. Check the job queue and transaction logs for processing status.");
            
        } catch (\Exception $e) {
            $this->processingStatus = 'error';
            $this->processingMessage = 'Error: ' . $e->getMessage();
            $this->isProcessing = false;
            
            Log::error('Dividend payment job dispatch failed', [
                'processId' => $processId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to dispatch dividend payment jobs: ' . $e->getMessage());
        }
    }

    public function viewDetails($id)
    {
        $this->selectedDividend = ShareRegister::select([
            'id',
            'member_id',
            'member_number',
            'member_name',
            'current_share_balance as total_shares',
            'current_price as share_value',
            'last_dividend_rate as dividend_rate',
            'accumulated_dividends as dividend_amount',
            'total_paid_dividends',
            'total_pending_dividends',
            'last_dividend_date as calculated_at',
            'last_dividend_date as paid_at',
            DB::raw("CASE 
                WHEN CAST(total_pending_dividends AS DECIMAL(20,6)) > 0 THEN 'pending'
                WHEN CAST(total_paid_dividends AS DECIMAL(20,6)) > 0 THEN 'paid'
                ELSE 'pending'
            END as status"),
            'dividend_payment_account as payment_mode'
        ])->findOrFail($id);
        
        $this->showDetailsModal = true;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetProcessingState()
    {
        $this->isProcessing = false;
        $this->processingProgress = 0;
        $this->processingStatus = '';
        $this->processingMessage = '';
        $this->totalToProcess = 0;
        $this->processedCount = 0;
        $this->failedCount = 0;
        $this->currentProcessId = '';
    }
}
