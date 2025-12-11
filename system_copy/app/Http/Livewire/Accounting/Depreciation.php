<?php

namespace App\Http\Livewire\Accounting;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\Log;

class Depreciation extends Component
{
    // Company details
    public $companyName = 'NBC SACCOS LTD';
    
    // Period selection
    public $selectedYear;
    public $selectedMonth;
    public $depreciationPeriod = 'monthly'; // monthly, quarterly, yearly
    
    // Display options
    public $viewMode = 'summary'; // summary, detailed, journal, history
    public $assetCategory = 'all'; // all, ppe, intangible, other
    public $searchTerm = '';
    public $postingHistory = [];
    
    // Depreciation data
    public $assets = [];
    public $depreciationSummary = [];
    public $journalEntries = [];
    public $totals = [
        'asset_cost' => 0,
        'accumulated_depreciation' => 0,
        'current_depreciation' => 0,
        'net_book_value' => 0
    ];
    
    // Depreciation methods
    public $depreciationMethods = [
        'straight_line' => 'Straight Line',
        'declining_balance' => 'Declining Balance',
        'sum_of_years' => 'Sum of Years Digits',
        'units_of_production' => 'Units of Production'
    ];
    
    public function mount()
    {
        $this->selectedYear = date('Y');
        $this->selectedMonth = date('n');
        $this->loadDepreciationData();
    }
    
    public function updatedSelectedYear()
    {
        $this->loadDepreciationData();
    }
    
    public function updatedSelectedMonth()
    {
        $this->loadDepreciationData();
    }
    
    public function updatedDepreciationPeriod()
    {
        $this->loadDepreciationData();
    }
    
    public function updatedAssetCategory()
    {
        $this->loadDepreciationData();
    }
    
    public function updatedSearchTerm()
    {
        $this->loadDepreciationData();
    }
    
    public function changeViewMode($mode)
    {
        $this->viewMode = $mode;
        $this->loadDepreciationData();
    }
    
    public function loadDepreciationData()
    {
        $this->assets = [];
        $this->depreciationSummary = [];
        $this->journalEntries = [];
        $this->postingHistory = [];
        $this->resetTotals();
        
        // If viewing history, load posting history instead
        if ($this->viewMode === 'history') {
            $this->postingHistory = $this->getPostingHistory();
            return;
        }
        
        // Get assets that need depreciation
        $query = DB::table('ppes');
        
        // Apply category filter
        if ($this->assetCategory !== 'all') {
            $query->where('category', $this->assetCategory);
        }
        
        // Apply search filter
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'LIKE', '%' . $this->searchTerm . '%')
                  ->orWhere('account_number', 'LIKE', '%' . $this->searchTerm . '%');
            });
        }
        
        // Get active assets only
        $query->where('status', 'active');
        
        $assets = $query->get();
        
        // Calculate depreciation for each asset
        foreach ($assets as $asset) {
            $depreciationData = $this->calculateDepreciation($asset);
            
            if ($depreciationData) {
                $this->assets[] = $depreciationData;
                
                // Update totals
                $this->totals['asset_cost'] += $depreciationData['initial_value'];
                $this->totals['accumulated_depreciation'] += $depreciationData['accumulated_depreciation'];
                $this->totals['current_depreciation'] += $depreciationData['current_period_depreciation'];
                $this->totals['net_book_value'] += $depreciationData['net_book_value'];
            }
        }
        
        // Prepare journal entries if in journal view
        if ($this->viewMode === 'journal') {
            $this->prepareJournalEntries();
        }
        
        // Prepare summary by category
        $this->prepareSummaryData();
    }
    
    private function calculateDepreciation($asset)
    {
        // Basic depreciation calculation (straight-line method)
        $purchaseDate = Carbon::parse($asset->purchase_date);
        $currentDate = Carbon::now();
        
        // Check if asset should be depreciated
        if ($purchaseDate->isFuture()) {
            return null;
        }
        
        // Skip fully depreciated assets
        if ($asset->status === 'fully_depreciated' || $asset->status === 'disposed') {
            return null;
        }
        
        // Calculate initial value (including capitalized costs)
        $initialValue = $asset->purchase_price + 
                       ($asset->legal_fees ?? 0) + 
                       ($asset->registration_fees ?? 0) + 
                       ($asset->renovation_costs ?? 0) + 
                       ($asset->transportation_costs ?? 0) + 
                       ($asset->installation_costs ?? 0) + 
                       ($asset->other_costs ?? 0);
        
        // Calculate annual depreciation based on method
        $usefulLife = $asset->useful_life ?? 5; // Default 5 years
        $salvageValue = $asset->salvage_value ?? 0;
        $depreciableAmount = $initialValue - $salvageValue;
        
        // Support different depreciation methods
        $depreciationMethod = $asset->depreciation_method ?? 'straight_line';
        $annualDepreciation = 0;
        
        switch ($depreciationMethod) {
            case 'straight_line':
            default:
                $annualDepreciation = $usefulLife > 0 ? $depreciableAmount / $usefulLife : 0;
                break;
                
            case 'declining_balance':
                // Double declining balance method
                $rate = $usefulLife > 0 ? (2 / $usefulLife) : 0;
                $bookValue = $initialValue - ($asset->accumulated_depreciation ?? 0);
                $annualDepreciation = $bookValue * $rate;
                // Don't depreciate below salvage value
                if ($bookValue - $annualDepreciation < $salvageValue) {
                    $annualDepreciation = $bookValue - $salvageValue;
                }
                break;
        }
        
        // Calculate monthly depreciation
        $monthlyDepreciation = $annualDepreciation / 12;
        
        // Use existing accumulated depreciation if available, otherwise calculate
        $accumulatedDepreciation = $asset->accumulated_depreciation ?? 0;
        if ($accumulatedDepreciation == 0) {
            $monthsOwned = $purchaseDate->diffInMonths($currentDate);
            $accumulatedDepreciation = min($monthsOwned * $monthlyDepreciation, $depreciableAmount);
        }
        
        // Calculate current period depreciation based on selected period
        $currentPeriodDepreciation = 0;
        
        // Get the period start date
        $periodStartDate = null;
        switch ($this->depreciationPeriod) {
            case 'monthly':
                $periodStartDate = Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1);
                $periodEndDate = $periodStartDate->copy()->endOfMonth();
                break;
            case 'quarterly':
                $quarter = ceil($this->selectedMonth / 3);
                $periodStartDate = Carbon::createFromDate($this->selectedYear, ($quarter - 1) * 3 + 1, 1);
                $periodEndDate = $periodStartDate->copy()->addMonths(3)->subDay();
                break;
            case 'yearly':
                $periodStartDate = Carbon::createFromDate($this->selectedYear, 1, 1);
                $periodEndDate = Carbon::createFromDate($this->selectedYear, 12, 31);
                break;
        }
        
        // Only calculate depreciation if the asset was owned during this period
        if ($purchaseDate <= $periodEndDate) {
            switch ($this->depreciationPeriod) {
                case 'monthly':
                    // Pro-rate if purchased during the month
                    if ($purchaseDate->year == $this->selectedYear && $purchaseDate->month == $this->selectedMonth) {
                        $daysInMonth = $purchaseDate->daysInMonth;
                        $daysOwned = $daysInMonth - $purchaseDate->day + 1;
                        $currentPeriodDepreciation = ($monthlyDepreciation * $daysOwned) / $daysInMonth;
                    } else {
                        $currentPeriodDepreciation = $monthlyDepreciation;
                    }
                    break;
                    
                case 'quarterly':
                    $monthsInQuarter = 3;
                    if ($purchaseDate >= $periodStartDate) {
                        // Pro-rate for partial quarter
                        $monthsOwned = $purchaseDate->diffInMonths($periodEndDate) + 1;
                        $monthsOwned = min($monthsOwned, $monthsInQuarter);
                        $currentPeriodDepreciation = $monthlyDepreciation * $monthsOwned;
                    } else {
                        $currentPeriodDepreciation = $monthlyDepreciation * $monthsInQuarter;
                    }
                    break;
                    
                case 'yearly':
                    if ($purchaseDate->year == $this->selectedYear) {
                        // Pro-rate for partial year
                        $monthsOwned = 12 - $purchaseDate->month + 1;
                        $currentPeriodDepreciation = ($annualDepreciation * $monthsOwned) / 12;
                    } else {
                        $currentPeriodDepreciation = $annualDepreciation;
                    }
                    break;
            }
        }
        
        // Ensure we don't exceed depreciable amount
        if ($accumulatedDepreciation + $currentPeriodDepreciation > $depreciableAmount) {
            $currentPeriodDepreciation = max(0, $depreciableAmount - $accumulatedDepreciation);
        }
        
        // Calculate net book value
        $netBookValue = $initialValue - $accumulatedDepreciation - $currentPeriodDepreciation;
        
        // Determine proper account based on asset's actual account or category
        $assetAccount = $asset->account_number;
        if (!$assetAccount || $assetAccount === 'N/A') {
            // Try to determine from category
            $assetAccount = $this->getAssetAccountFromCategory($asset->category);
        }
        
        return [
            'id' => $asset->id,
            'name' => $asset->name,
            'account_number' => $assetAccount,
            'category' => $asset->category ?? 'PPE',
            'purchase_date' => $purchaseDate->format('d/m/Y'),
            'useful_life' => $usefulLife,
            'initial_value' => $initialValue,
            'salvage_value' => $salvageValue,
            'depreciable_amount' => $depreciableAmount,
            'annual_depreciation' => $annualDepreciation,
            'monthly_depreciation' => $monthlyDepreciation,
            'accumulated_depreciation' => $accumulatedDepreciation,
            'current_period_depreciation' => $currentPeriodDepreciation,
            'net_book_value' => $netBookValue,
            'depreciation_method' => $this->depreciationMethods[$depreciationMethod] ?? 'Straight Line',
            'months_owned' => $purchaseDate->diffInMonths($currentDate)
        ];
    }
    
    private function getAssetAccountFromCategory($category)
    {
        // Map categories to asset accounts
        $categoryUpper = strtoupper($category);
        
        if (strpos($categoryUpper, 'BUILDING') !== false || strpos($categoryUpper, 'LAND') !== false) {
            return '0101100016001610'; // LAND AND BUILDINGS
        } elseif (strpos($categoryUpper, 'VEHICLE') !== false || strpos($categoryUpper, 'MOTOR') !== false) {
            return '0101100016001620'; // MOTOR VEHICLES
        } elseif (strpos($categoryUpper, 'COMPUTER') !== false || strpos($categoryUpper, 'HARDWARE') !== false) {
            return '0101100016001640'; // COMPUTER HARDWARE
        } elseif (strpos($categoryUpper, 'FURNITURE') !== false) {
            return '0101100016001650'; // FURNITURE AND FIXTURES
        } elseif (strpos($categoryUpper, 'EQUIPMENT') !== false || strpos($categoryUpper, 'OFFICE') !== false) {
            return '0101100016001630'; // OFFICE EQUIPMENT
        }
        
        // Default to Office Equipment for generic PPE
        return '0101100016001630';
    }
    
    private function prepareJournalEntries()
    {
        $this->journalEntries = [];
        
        // Group depreciation by category
        $depreciationByCategory = [];
        foreach ($this->assets as $asset) {
            $category = $asset['category'];
            if (!isset($depreciationByCategory[$category])) {
                $depreciationByCategory[$category] = 0;
            }
            $depreciationByCategory[$category] += $asset['current_period_depreciation'];
        }
        
        // Create journal entries
        $entryNumber = 1;
        foreach ($depreciationByCategory as $category => $amount) {
            if ($amount > 0) {
                // Debit: Depreciation Expense
                $this->journalEntries[] = [
                    'entry_number' => $entryNumber,
                    'account_name' => "Depreciation Expense - $category",
                    'account_code' => $this->getDepreciationExpenseAccount($category),
                    'debit' => $amount,
                    'credit' => 0,
                    'description' => "Depreciation for $category - " . $this->getPeriodDescription()
                ];
                
                // Credit: Accumulated Depreciation
                $this->journalEntries[] = [
                    'entry_number' => $entryNumber,
                    'account_name' => "Accumulated Depreciation - $category",
                    'account_code' => $this->getAccumulatedDepreciationAccount($category),
                    'debit' => 0,
                    'credit' => $amount,
                    'description' => "Accumulated depreciation for $category"
                ];
                
                $entryNumber++;
            }
        }
    }
    
    private function prepareSummaryData()
    {
        $this->depreciationSummary = [];
        
        // Group by category
        $summaryByCategory = [];
        foreach ($this->assets as $asset) {
            $category = $asset['category'];
            if (!isset($summaryByCategory[$category])) {
                $summaryByCategory[$category] = [
                    'category' => $category,
                    'asset_count' => 0,
                    'total_cost' => 0,
                    'accumulated_depreciation' => 0,
                    'current_depreciation' => 0,
                    'net_book_value' => 0
                ];
            }
            
            $summaryByCategory[$category]['asset_count']++;
            $summaryByCategory[$category]['total_cost'] += $asset['initial_value'];
            $summaryByCategory[$category]['accumulated_depreciation'] += $asset['accumulated_depreciation'];
            $summaryByCategory[$category]['current_depreciation'] += $asset['current_period_depreciation'];
            $summaryByCategory[$category]['net_book_value'] += $asset['net_book_value'];
        }
        
        $this->depreciationSummary = array_values($summaryByCategory);
    }
    
    private function getDepreciationExpenseAccount($category)
    {
        // Map categories to proper depreciation expense accounts (Level 3)
        $categoryUpper = strtoupper($category);
        
        // First try to find a specific account based on category
        if (strpos($categoryUpper, 'BUILDING') !== false || strpos($categoryUpper, 'PROPERTY') !== false) {
            return '0101500061006110'; // BUILDING DEPRECIATION
        } elseif (strpos($categoryUpper, 'VEHICLE') !== false || strpos($categoryUpper, 'MOTOR') !== false) {
            return '0101500061006120'; // VEHICLE DEPRECIATION
        } elseif (strpos($categoryUpper, 'EQUIPMENT') !== false || strpos($categoryUpper, 'COMPUTER') !== false || strpos($categoryUpper, 'HARDWARE') !== false) {
            return '0101500061006130'; // EQUIPMENT DEPRECIATION
        } elseif (strpos($categoryUpper, 'FURNITURE') !== false) {
            return '0101500061006140'; // FURNITURE DEPRECIATION
        }
        
        // Default to Equipment Depreciation for PPE and others
        if ($category === 'PPE' || $category === 'PROPERTY AND EQUIPMENT') {
            return '0101500061006130'; // EQUIPMENT DEPRECIATION as default
        }
        
        // For intangible assets, try to find or create appropriate account
        if ($category === 'intangible') {
            // Check if intangible depreciation account exists
            $intangibleAccount = DB::table('accounts')
                ->where('account_name', 'LIKE', '%INTANGIBLE%DEPRECIATION%')
                ->where('account_level', '3')
                ->first();
            
            if ($intangibleAccount) {
                return $intangibleAccount->account_number;
            }
        }
        
        // Default to Equipment Depreciation
        return '0101500061006130';
    }
    
    private function getAccumulatedDepreciationAccount($category)
    {
        // Use the provision for depreciation account for all categories
        // This is a contra-asset account that accumulates depreciation
        return '0101200028002820'; // PROVISION FOR DEPRECIATION (Level 3)
    }
    
    private function getPeriodDescription()
    {
        switch ($this->depreciationPeriod) {
            case 'monthly':
                return Carbon::createFromDate($this->selectedYear, $this->selectedMonth, 1)->format('F Y');
            case 'quarterly':
                $quarter = ceil($this->selectedMonth / 3);
                return "Q$quarter " . $this->selectedYear;
            case 'yearly':
                return "Year " . $this->selectedYear;
            default:
                return Carbon::now()->format('F Y');
        }
    }
    
    private function resetTotals()
    {
        $this->totals = [
            'asset_cost' => 0,
            'accumulated_depreciation' => 0,
            'current_depreciation' => 0,
            'net_book_value' => 0
        ];
    }
    
    public function runDepreciation()
    {
        try {
            DB::beginTransaction();
            
            // Check for duplicate postings first
            $duplicateCheck = $this->checkForDuplicatePostings();
            if ($duplicateCheck['has_duplicates']) {
                DB::rollBack();
                session()->flash('error', $duplicateCheck['message']);
                $this->emit('showNotification', $duplicateCheck['message'], 'error');
                return;
            }
            
            // Validate all accounts exist and are level 3 before posting
            if (!$this->validateDepreciationAccounts()) {
                throw new \Exception('Invalid or missing depreciation accounts. Please check account configuration.');
            }
            
            $transactionService = new TransactionPostingService();
            $successCount = 0;
            $failedEntries = [];
            $postedAssets = [];
            
            // Group journal entries by matching debit/credit pairs
            $entryPairs = [];
            $currentPair = [];
            
            foreach ($this->journalEntries as $entry) {
                $currentPair[] = $entry;
                
                // When we have both debit and credit for same entry number, process the pair
                if (count($currentPair) == 2) {
                    $debitEntry = $currentPair[0]['debit'] > 0 ? $currentPair[0] : $currentPair[1];
                    $creditEntry = $currentPair[0]['credit'] > 0 ? $currentPair[0] : $currentPair[1];
                    
                    // Post using TransactionPostingService
                    $transactionData = [
                        'first_account' => $debitEntry['account_code'],  // Debit account
                        'second_account' => $creditEntry['account_code'], // Credit account
                        'amount' => $debitEntry['debit'],
                        'narration' => $debitEntry['description'],
                        'action' => 'depreciation'
                    ];
                    
                    Log::info('Posting depreciation transaction', $transactionData);
                    
                    $result = $transactionService->postTransaction($transactionData);
                    
                    if ($result['status'] === 'success') {
                        $successCount++;
                        
                        // Track which assets were posted for this entry
                        $categoryAssets = $this->getAssetsForCategory($debitEntry['description']);
                        foreach ($categoryAssets as $asset) {
                            if (!in_array($asset['id'], $postedAssets)) {
                                $postedAssets[] = [
                                    'asset_id' => $asset['id'],
                                    'amount' => $asset['current_period_depreciation'],
                                    'reference' => $result['reference'] ?? null
                                ];
                            }
                        }
                        
                        Log::info('Depreciation transaction posted successfully', [
                            'reference' => $result['reference'] ?? 'N/A'
                        ]);
                    } else {
                        $failedEntries[] = [
                            'entry' => $debitEntry['description'],
                            'error' => $result['message'] ?? 'Unknown error'
                        ];
                        Log::error('Failed to post depreciation transaction', [
                            'error' => $result['message'] ?? 'Unknown error',
                            'data' => $transactionData
                        ]);
                    }
                    
                    $currentPair = [];
                }
            }
            
            // Update PPE records with new accumulated depreciation
            foreach ($this->assets as $asset) {
                // Get current accumulated depreciation from database
                $currentPpe = DB::table('ppes')->where('id', $asset['id'])->first();
                $newAccumulatedDepreciation = ($currentPpe->accumulated_depreciation ?? 0) + $asset['current_period_depreciation'];
                
                DB::table('ppes')
                    ->where('id', $asset['id'])
                    ->update([
                        'accumulated_depreciation' => $newAccumulatedDepreciation,
                        'depreciation_for_year' => $asset['current_period_depreciation'],
                        'closing_value' => $asset['net_book_value'],
                        'last_depreciation_date' => now(),
                        'updated_at' => now()
                    ]);
                
                Log::info('Updated PPE depreciation values', [
                    'ppe_id' => $asset['id'],
                    'accumulated' => $asset['accumulated_depreciation'],
                    'current_period' => $asset['current_period_depreciation'],
                    'nbv' => $asset['net_book_value']
                ]);
            }
            
            // Record successful postings in the tracker table
            $this->recordDepreciationPostings($postedAssets);
            
            DB::commit();
            
            // Calculate total amount posted
            $totalAmountPosted = array_sum(array_column($postedAssets, 'amount'));
            $periodDescription = $this->getPeriodDescription();
            
            if (count($failedEntries) > 0) {
                $errorMsg = 'Some depreciation entries failed: ' . 
                           implode(', ', array_map(function($e) { 
                               return $e['entry'] . ' - ' . $e['error']; 
                           }, $failedEntries));
                           
                session()->flash('warning', "⚠️ Depreciation partially posted for {$periodDescription}.\n\n" .
                              "✓ {$successCount} transactions succeeded\n" .
                              "✗ " . count($failedEntries) . " transactions failed\n\n" .
                              $errorMsg);
                $this->emit('showNotification', 'Depreciation partially posted', 'warning');
            } else {
                $formattedAmount = number_format($totalAmountPosted, 2);
                $assetCount = count($postedAssets);
                
                session()->flash('message', "✅ Depreciation successfully posted for {$periodDescription}!\n\n" .
                              "• Total Amount: " . config('app.currency', 'TZS') . " {$formattedAmount}\n" .
                              "• Assets Depreciated: {$assetCount}\n" .
                              "• Journal Entries: {$successCount}\n" .
                              "• Status: Posted to General Ledger\n\n" .
                              "The depreciation has been recorded and cannot be posted again for this period.");
                              
                $this->emit('showNotification', 'Depreciation posted successfully!', 'success');
            }
            
            // Refresh data after posting
            $this->loadDepreciationData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to post depreciation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $periodDescription = $this->getPeriodDescription();
            $errorMessage = "❌ Failed to post depreciation for {$periodDescription}\n\n";
            
            // Provide user-friendly error messages
            if (strpos($e->getMessage(), 'DUPLICATE POSTING PREVENTED') !== false) {
                $errorMessage = $e->getMessage(); // Use the detailed duplicate message
            } elseif (strpos($e->getMessage(), 'Invalid or missing depreciation accounts') !== false) {
                $errorMessage .= "Error: Account configuration issue\n\n" .
                              "Some required depreciation accounts are missing or incorrectly configured.\n" .
                              "Please contact your system administrator to verify the chart of accounts.";
            } elseif (strpos($e->getMessage(), 'SQLSTATE') !== false) {
                $errorMessage .= "Error: Database issue encountered\n\n" .
                              "A database error occurred while posting depreciation.\n" .
                              "Please try again or contact support if the problem persists.\n\n" .
                              "Technical details: " . substr($e->getMessage(), 0, 200);
            } else {
                $errorMessage .= "Error: " . $e->getMessage() . "\n\n" .
                              "Please verify your data and try again.\n" .
                              "If this problem continues, contact your system administrator.";
            }
            
            session()->flash('error', $errorMessage);
            $this->emit('showNotification', 'Failed to post depreciation', 'error');
        }
    }
    
    private function validateDepreciationAccounts()
    {
        // Get unique account codes from journal entries
        $accountCodes = [];
        foreach ($this->journalEntries as $entry) {
            $accountCodes[] = $entry['account_code'];
        }
        $accountCodes = array_unique($accountCodes);
        
        // Validate each account
        foreach ($accountCodes as $accountCode) {
            $account = DB::table('accounts')
                ->where('account_number', $accountCode)
                ->first();
            
            if (!$account) {
                Log::error('Depreciation account not found', ['account_code' => $accountCode]);
                return false;
            }
            
            // Check if it's a level 3 account (detail level)
            if ($account->account_level < 3) {
                Log::error('Depreciation account is not a detail account (level 3)', [
                    'account_code' => $accountCode,
                    'account_level' => $account->account_level
                ]);
                return false;
            }
            
            // Check if account is active
            if ($account->status !== 'ACTIVE') {
                Log::error('Depreciation account is not active', [
                    'account_code' => $accountCode,
                    'status' => $account->status
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    public function exportToExcel()
    {
        $exportData = $this->prepareExportData();
        
        $export = new class($exportData) implements FromArray {
            private $data;
            
            public function __construct(array $data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        };
        
        return Excel::download($export, 'depreciation_' . $this->selectedYear . '_' . $this->selectedMonth . '.xlsx');
    }
    
    public function exportToPDF()
    {
        session()->flash('message', 'PDF export will be implemented');
    }
    
    private function prepareExportData()
    {
        $data = [];
        
        // Header
        $data[] = [$this->companyName];
        $data[] = ['DEPRECIATION SCHEDULE'];
        $data[] = ['Period: ' . $this->getPeriodDescription()];
        $data[] = [];
        
        // Column headers
        $data[] = [
            'Asset Name',
            'Category',
            'Purchase Date',
            'Initial Value',
            'Salvage Value',
            'Useful Life',
            'Monthly Depreciation',
            'Accumulated Depreciation',
            'Current Period',
            'Net Book Value'
        ];
        
        // Add asset data
        foreach ($this->assets as $asset) {
            $data[] = [
                $asset['name'],
                $asset['category'],
                $asset['purchase_date'],
                $asset['initial_value'],
                $asset['salvage_value'],
                $asset['useful_life'] . ' years',
                $asset['monthly_depreciation'],
                $asset['accumulated_depreciation'],
                $asset['current_period_depreciation'],
                $asset['net_book_value']
            ];
        }
        
        // Add totals
        $data[] = [];
        $data[] = [
            'TOTALS',
            '',
            '',
            $this->totals['asset_cost'],
            '',
            '',
            '',
            $this->totals['accumulated_depreciation'],
            $this->totals['current_depreciation'],
            $this->totals['net_book_value']
        ];
        
        return $data;
    }
    
    public function formatNumber($number)
    {
        if ($number < 0) {
            return '(' . number_format(abs($number), 2) . ')';
        }
        return number_format($number, 2);
    }
    
    public function render()
    {
        return view('livewire.accounting.depreciation', [
            'assets' => $this->assets,
            'depreciationSummary' => $this->depreciationSummary,
            'journalEntries' => $this->journalEntries,
            'totals' => $this->totals
        ]);
    }
    
    /**
     * Check if depreciation has already been posted for the selected period
     */
    private function checkForDuplicatePostings()
    {
        $year = $this->selectedYear;
        $month = $this->selectedMonth;
        $periodType = $this->depreciationPeriod;
        
        // Get all assets that would be depreciated
        $assetIds = array_column($this->assets, 'id');
        
        if (empty($assetIds)) {
            return ['has_duplicates' => false];
        }
        
        // Check if any of these assets have already been posted for this period
        $existingPostings = DB::table('depreciation_postings')
            ->whereIn('asset_id', $assetIds)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->where('period_type', $periodType)
            ->where('status', 'posted')
            ->get();
        
        if ($existingPostings->count() > 0) {
            $assetNames = DB::table('ppes')
                ->whereIn('id', $existingPostings->pluck('asset_id'))
                ->pluck('name')
                ->implode(', ');
            
            $periodDesc = $this->getPeriodDescription();
            
            return [
                'has_duplicates' => true,
                'message' => "⚠️ DUPLICATE POSTING PREVENTED!\n\n" .
                           "Depreciation has already been posted for {$periodDesc}.\n\n" .
                           "Assets already posted: {$assetNames}\n\n" .
                           "If you need to adjust depreciation:\n" .
                           "1. Reverse the existing entries first\n" .
                           "2. Then post the new depreciation\n\n" .
                           "Running depreciation multiple times for the same period will create duplicate expenses."
            ];
        }
        
        return ['has_duplicates' => false];
    }
    
    /**
     * Record successful depreciation postings in the tracker table
     */
    private function recordDepreciationPostings($postedAssets)
    {
        $year = $this->selectedYear;
        $month = $this->selectedMonth;
        $periodType = $this->depreciationPeriod;
        $userId = auth()->id();
        
        foreach ($postedAssets as $posting) {
            // Check if this asset already has a posting for this period
            $existingPosting = DB::table('depreciation_postings')
                ->where('asset_id', $posting['asset_id'])
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->where('period_type', $periodType)
                ->where('status', 'posted')
                ->first();
            
            if ($existingPosting) {
                Log::warning('Skipping duplicate depreciation posting record', [
                    'asset_id' => $posting['asset_id'],
                    'period' => "{$year}-{$month}",
                    'existing_id' => $existingPosting->id
                ]);
                continue;
            }
            
            // Only insert if no existing record
            DB::table('depreciation_postings')->insert([
                'asset_id' => $posting['asset_id'],
                'period_year' => $year,
                'period_month' => $month,
                'period_type' => $periodType,
                'amount_posted' => $posting['amount'],
                'posting_date' => now(),
                'reference_number' => $posting['reference'],
                'posted_by' => $userId,
                'status' => 'posted',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            Log::info('Recorded depreciation posting', [
                'asset_id' => $posting['asset_id'],
                'period' => "{$year}-{$month}",
                'amount' => $posting['amount']
            ]);
        }
    }
    
    /**
     * Get assets that belong to a specific category based on journal description
     */
    private function getAssetsForCategory($description)
    {
        // Extract category from description (e.g., "Depreciation for PROPERTY AND EQUIPMENT - November 2025")
        preg_match('/Depreciation for (.+?) -/', $description, $matches);
        $category = $matches[1] ?? null;
        
        if (!$category) {
            return [];
        }
        
        return array_filter($this->assets, function($asset) use ($category) {
            return $asset['category'] === $category;
        });
    }
    
    /**
     * Check if a specific asset has been depreciated for a given period
     */
    public function isAssetAlreadyPosted($assetId, $year, $month)
    {
        return DB::table('depreciation_postings')
            ->where('asset_id', $assetId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->where('period_type', $this->depreciationPeriod)
            ->where('status', 'posted')
            ->exists();
    }
    
    /**
     * Get posting history for the current view
     */
    public function getPostingHistory()
    {
        return DB::table('depreciation_postings as dp')
            ->join('ppes', 'ppes.id', '=', 'dp.asset_id')
            ->join('users', 'users.id', '=', 'dp.posted_by')
            ->leftJoin('users as reverser', 'reverser.id', '=', 'dp.reversed_by')
            ->select(
                'dp.*',
                'ppes.name as asset_name',
                'ppes.category as asset_category',
                'users.name as posted_by_name',
                'reverser.name as reversed_by_name'
            )
            ->where('dp.period_year', $this->selectedYear)
            ->where('dp.period_month', $this->selectedMonth)
            ->where('dp.period_type', $this->depreciationPeriod)
            ->orderBy('dp.posting_date', 'desc')
            ->get();
    }
    
    /**
     * Reverse depreciation for a specific period
     */
    public function reverseDepreciation($reason = null)
    {
        try {
            DB::beginTransaction();
            
            // Get all posted depreciation for this period
            $postedDepreciation = DB::table('depreciation_postings')
                ->where('period_year', $this->selectedYear)
                ->where('period_month', $this->selectedMonth)
                ->where('period_type', $this->depreciationPeriod)
                ->where('status', 'posted')
                ->get();
            
            if ($postedDepreciation->isEmpty()) {
                throw new \Exception('No posted depreciation found for ' . $this->getPeriodDescription());
            }
            
            $transactionService = new TransactionPostingService();
            $reversalReference = 'REV-' . uniqid();
            $successCount = 0;
            $totalReversed = 0;
            
            // Group by unique assets to create reversal entries
            $assetGroups = $postedDepreciation->groupBy('asset_id');
            
            foreach ($assetGroups as $assetId => $postings) {
                $asset = DB::table('ppes')->where('id', $assetId)->first();
                if (!$asset) continue;
                
                $totalAmount = $postings->sum('amount_posted');
                
                // Determine accounts based on asset category
                $depreciationExpenseAccount = $this->getDepreciationExpenseAccount($asset->category);
                $accumulatedDepreciationAccount = $this->getAccumulatedDepreciationAccount($asset->category);
                
                // Create reversal entry (opposite of original)
                // Original was: DR Depreciation Expense, CR Accumulated Depreciation
                // Reversal is: DR Accumulated Depreciation, CR Depreciation Expense
                $transactionData = [
                    'first_account' => $accumulatedDepreciationAccount,  // Debit
                    'second_account' => $depreciationExpenseAccount,     // Credit
                    'amount' => $totalAmount,
                    'narration' => "Reversal of depreciation for {$asset->name} - " . $this->getPeriodDescription(),
                    'action' => 'depreciation_reversal'
                ];
                
                Log::info('Posting depreciation reversal', $transactionData);
                
                $result = $transactionService->postTransaction($transactionData);
                
                if ($result['status'] === 'success') {
                    $successCount++;
                    $totalReversed += $totalAmount;
                    
                    // Update the depreciation_postings records
                    DB::table('depreciation_postings')
                        ->where('asset_id', $assetId)
                        ->where('period_year', $this->selectedYear)
                        ->where('period_month', $this->selectedMonth)
                        ->where('period_type', $this->depreciationPeriod)
                        ->where('status', 'posted')
                        ->update([
                            'status' => 'reversed',
                            'reversed_by' => auth()->id(),
                            'reversed_at' => now(),
                            'reversal_reason' => $reason ?? 'Manual reversal by user',
                            'reversal_reference' => $reversalReference
                        ]);
                    
                    // Reduce accumulated depreciation on the asset
                    DB::table('ppes')
                        ->where('id', $assetId)
                        ->decrement('accumulated_depreciation', $totalAmount);
                    
                    // Update closing value
                    $currentPpe = DB::table('ppes')->where('id', $assetId)->first();
                    $newClosingValue = $currentPpe->initial_value - $currentPpe->accumulated_depreciation;
                    
                    DB::table('ppes')
                        ->where('id', $assetId)
                        ->update([
                            'closing_value' => $newClosingValue,
                            'updated_at' => now()
                        ]);
                        
                    Log::info('Reversed depreciation for asset', [
                        'asset_id' => $assetId,
                        'amount_reversed' => $totalAmount,
                        'reversal_reference' => $reversalReference
                    ]);
                }
            }
            
            DB::commit();
            
            $periodDescription = $this->getPeriodDescription();
            $formattedAmount = number_format($totalReversed, 2);
            
            session()->flash('message', "✅ Depreciation reversal completed for {$periodDescription}!\n\n" .
                          "• Total Amount Reversed: " . config('app.currency', 'TZS') . " {$formattedAmount}\n" .
                          "• Reversal Entries: {$successCount}\n" .
                          "• Reference: {$reversalReference}\n\n" .
                          "You can now post new depreciation for this period if needed.");
            
            // Refresh data
            $this->loadDepreciationData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reverse depreciation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', "❌ Failed to reverse depreciation\n\n" .
                          "Error: " . $e->getMessage() . "\n\n" .
                          "Please contact your system administrator if this problem persists.");
        }
    }
    
    /**
     * Check if current period has posted depreciation
     */
    public function hasPostedDepreciation()
    {
        return DB::table('depreciation_postings')
            ->where('period_year', $this->selectedYear)
            ->where('period_month', $this->selectedMonth)
            ->where('period_type', $this->depreciationPeriod)
            ->where('status', 'posted')
            ->exists();
    }
}