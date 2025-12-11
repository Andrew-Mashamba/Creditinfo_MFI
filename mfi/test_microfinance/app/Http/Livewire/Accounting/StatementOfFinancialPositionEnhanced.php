<?php

namespace App\Http\Livewire\Accounting;

use App\Models\StatementFinancialPositionItem;
use App\Models\AccountsModel;
use App\Services\BalanceSheetItemIntegrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class StatementOfFinancialPositionEnhanced extends Component
{
    use WithPagination;

    // View Control
    public $activeTab = 'statement';
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showImportModal = false;
    public $editMode = false;
    
    // Period selection
    public $selectedYear;
    public $selectedMonth;
    public $comparisonYears = [];
    public $reportingDate;
    public $reportingPeriod;
    public $companyName;
    
    // Display options
    public $viewLevel = 2;
    public $showComparison = true;
    public $showPercentages = true;
    public $expandedCategories = [];
    
    // Form fields for CRUD
    public $itemId;
    public $item_code;
    public $item_name;
    public $category = 'assets';
    public $sub_category = 'current_assets';
    public $account_number;
    public $amount = 0;
    public $previous_year_amount = 0;
    public $description;
    public $display_order = 0;
    public $is_calculated = false;
    public $calculation_formula;
    public $status = 'active';
    
    // Financial data
    public $assetsData = [];
    public $liabilitiesData = [];
    public $equityData = [];
    public $totalAssets = 0;
    public $totalLiabilities = 0;
    public $totalEquity = 0;
    public $previousYearTotalAssets = 0;
    public $previousYearTotalLiabilities = 0;
    public $previousYearTotalEquity = 0;
    
    // Search and filters
    public $search = '';
    public $categoryFilter = 'all';
    public $statusFilter = 'active';
    
    protected $rules = [
        'item_name' => 'required|min:3',
        'category' => 'required|in:assets,liabilities,equity',
        'sub_category' => 'required',
        'amount' => 'required|numeric',
        'display_order' => 'required|integer',
        'status' => 'required|in:active,inactive'
    ];

    public function mount()
    {
        $this->companyName = DB::table('institution')->first()->name ?? 'SACCOS LTD';
        $this->selectedYear = date('Y');
        $this->selectedMonth = date('m');
        $this->updateReportingPeriod();
        $this->loadFinancialData();
    }

    public function updateReportingPeriod()
    {
        $this->reportingDate = Carbon::create($this->selectedYear, $this->selectedMonth, 1)->endOfMonth();
        $this->reportingPeriod = $this->selectedYear . '-' . str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT);
        $this->comparisonYears = [$this->selectedYear, $this->selectedYear - 1];
    }

    public function loadFinancialData()
    {
        $this->syncWithAccounts();
        
        // Load assets
        $this->assetsData = $this->loadCategoryData('assets');
        $this->totalAssets = $this->calculateTotal($this->assetsData, 'amount');
        $this->previousYearTotalAssets = $this->calculateTotal($this->assetsData, 'previous_year_amount');
        
        // Load liabilities
        $this->liabilitiesData = $this->loadCategoryData('liabilities');
        $this->totalLiabilities = $this->calculateTotal($this->liabilitiesData, 'amount');
        $this->previousYearTotalLiabilities = $this->calculateTotal($this->liabilitiesData, 'previous_year_amount');
        
        // Load equity
        $this->equityData = $this->loadCategoryData('equity');
        $this->totalEquity = $this->calculateTotal($this->equityData, 'amount');
        $this->previousYearTotalEquity = $this->calculateTotal($this->equityData, 'previous_year_amount');
    }

    private function loadCategoryData($category)
    {
        $items = StatementFinancialPositionItem::where('category', $category)
            ->where('reporting_period', $this->reportingPeriod)
            ->where('status', 'active')
            ->orderBy('display_order')
            ->get();

        // Group by subcategory
        $grouped = $items->groupBy('sub_category')->map(function ($group) {
            return [
                'items' => $group,
                'total' => $group->where('is_calculated', false)->sum('amount'),
                'previous_total' => $group->where('is_calculated', false)->sum('previous_year_amount')
            ];
        });

        return $grouped;
    }

    private function calculateTotal($data, $field)
    {
        $total = 0;
        foreach ($data as $subcategory => $subcategoryData) {
            foreach ($subcategoryData['items'] as $item) {
                if (!$item->is_calculated) {
                    $total += $item->$field;
                }
            }
        }
        return $total;
    }

    public function syncWithAccounts()
    {
        try {
            // Get all accounts from the accounts table
            $accounts = AccountsModel::where('status', 'ACTIVE')->get();
            
            foreach ($accounts as $account) {
                // Determine category and subcategory based on account type
                $category = $this->determineCategory($account);
                $subCategory = $this->determineSubCategory($account);
                
                if ($category && $subCategory) {
                    // Check if item exists for this period
                    $item = StatementFinancialPositionItem::where('account_number', $account->account_number)
                        ->where('reporting_period', $this->reportingPeriod)
                        ->first();
                    
                    if (!$item) {
                        // Create new item
                        $item = new StatementFinancialPositionItem();
                        $item->item_name = $account->account_name;
                        $item->category = $category;
                        $item->sub_category = $subCategory;
                        $item->account_number = $account->account_number;
                        $item->reporting_period = $this->reportingPeriod;
                        $item->reporting_date = $this->reportingDate;
                        $item->status = 'active';
                    }
                    
                    // Update amount from account balance
                    $item->amount = abs($account->balance);
                    
                    // Get previous year amount
                    $previousPeriod = ($this->selectedYear - 1) . '-' . str_pad($this->selectedMonth, 2, '0', STR_PAD_LEFT);
                    $previousItem = StatementFinancialPositionItem::where('account_number', $account->account_number)
                        ->where('reporting_period', $previousPeriod)
                        ->first();
                    
                    if ($previousItem) {
                        $item->previous_year_amount = $previousItem->amount;
                    }
                    
                    $item->save();
                }
            }
            
            Log::info('Financial position synced with accounts', [
                'period' => $this->reportingPeriod,
                'accounts_processed' => $accounts->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error syncing financial position: ' . $e->getMessage());
        }
    }

    private function determineCategory($account)
    {
        // Based on major_category_code or account_type
        if (in_array($account->major_category_code, [1000, 1100, 1200, 1300, 1400, 1500, 1600])) {
            return 'assets';
        } elseif (in_array($account->major_category_code, [2000, 2100, 2200, 2300, 2400, 2500])) {
            return 'liabilities';
        } elseif (in_array($account->major_category_code, [3000, 3100, 3200, 3300])) {
            return 'equity';
        }
        
        // Fallback to account_type
        return match(strtoupper($account->account_type)) {
            'ASSET' => 'assets',
            'LIABILITY' => 'liabilities',
            'EQUITY', 'CAPITAL' => 'equity',
            default => null
        };
    }

    private function determineSubCategory($account)
    {
        // Based on sub_category_code or account details
        $code = $account->sub_category_code;
        
        // Assets subcategories
        if ($code >= 1000 && $code < 1100) return 'current_assets';
        if ($code >= 1100 && $code < 1200) return 'non_current_assets';
        if ($code >= 1600 && $code < 1700) return 'property_plant_equipment';
        if ($code >= 1700 && $code < 1800) return 'intangible_assets';
        if ($code >= 1103 && $code < 1110) return 'investments';
        
        // Liabilities subcategories
        if ($code >= 2000 && $code < 2100) return 'current_liabilities';
        if ($code >= 2100 && $code < 2500) return 'non_current_liabilities';
        
        // Equity subcategories
        if ($code >= 3000 && $code < 3100) return 'share_capital';
        if ($code >= 3100 && $code < 3200) return 'retained_earnings';
        if ($code >= 3200 && $code < 3300) return 'reserves';
        
        // Default based on account name
        $name = strtolower($account->account_name);
        if (str_contains($name, 'cash') || str_contains($name, 'bank')) return 'current_assets';
        if (str_contains($name, 'receivable')) return 'current_assets';
        if (str_contains($name, 'payable')) return 'current_liabilities';
        if (str_contains($name, 'loan')) return str_contains($name, 'long') ? 'non_current_liabilities' : 'current_liabilities';
        
        return 'current_assets'; // Default
    }

    public function openCreateModal()
    {
        $this->reset(['itemId', 'item_code', 'item_name', 'category', 'sub_category', 
                     'account_number', 'amount', 'previous_year_amount', 'description',
                     'display_order', 'is_calculated', 'calculation_formula']);
        
        $this->editMode = false;
        $this->status = 'active';
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->validate();
        
        DB::beginTransaction();
        try {
            $data = [
                'item_name' => $this->item_name,
                'category' => $this->category,
                'sub_category' => $this->sub_category,
                'account_number' => $this->account_number,
                'amount' => $this->amount,
                'previous_year_amount' => $this->previous_year_amount,
                'description' => $this->description,
                'display_order' => $this->display_order,
                'is_calculated' => $this->is_calculated,
                'calculation_formula' => $this->calculation_formula,
                'status' => $this->status,
                'reporting_period' => $this->reportingPeriod,
                'reporting_date' => $this->reportingDate,
            ];
            
            if ($this->editMode && $this->itemId) {
                StatementFinancialPositionItem::find($this->itemId)->update($data);
                $message = 'Item updated successfully!';
            } else {
                $item = StatementFinancialPositionItem::create($data);
                
                // If account number is provided, link to accounts table
                if ($this->account_number) {
                    $this->linkToAccount($item);
                }
                
                $message = 'Item created successfully!';
            }
            
            DB::commit();
            
            $this->showCreateModal = false;
            $this->loadFinancialData();
            
            session()->flash('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving financial position item: ' . $e->getMessage());
            session()->flash('error', 'Error saving item: ' . $e->getMessage());
        }
    }

    private function linkToAccount($item)
    {
        // Check if account exists
        $account = AccountsModel::where('account_number', $item->account_number)->first();
        
        if (!$account && !$item->is_calculated) {
            // Create account using integration service
            $integrationService = new BalanceSheetItemIntegrationService();
            
            try {
                // Create appropriate account based on category
                $accountData = (object)[
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'amount' => $item->amount,
                    'category' => $item->category,
                    'sub_category' => $item->sub_category
                ];
                
                // This will create the account and post to GL
                $integrationService->createFinancialStatementAccount($accountData);
                
            } catch (\Exception $e) {
                Log::error('Failed to create account for financial position item: ' . $e->getMessage());
            }
        }
    }

    public function edit($id)
    {
        $item = StatementFinancialPositionItem::find($id);
        
        if ($item) {
            $this->itemId = $id;
            $this->item_code = $item->item_code;
            $this->item_name = $item->item_name;
            $this->category = $item->category;
            $this->sub_category = $item->sub_category;
            $this->account_number = $item->account_number;
            $this->amount = $item->amount;
            $this->previous_year_amount = $item->previous_year_amount;
            $this->description = $item->description;
            $this->display_order = $item->display_order;
            $this->is_calculated = $item->is_calculated;
            $this->calculation_formula = $item->calculation_formula;
            $this->status = $item->status;
            
            $this->editMode = true;
            $this->showCreateModal = true;
        }
    }

    public function delete($id)
    {
        try {
            StatementFinancialPositionItem::find($id)->delete();
            $this->loadFinancialData();
            session()->flash('success', 'Item deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting item: ' . $e->getMessage());
        }
    }

    public function toggleCategory($category)
    {
        if (in_array($category, $this->expandedCategories)) {
            $this->expandedCategories = array_diff($this->expandedCategories, [$category]);
        } else {
            $this->expandedCategories[] = $category;
        }
    }

    public function exportStatement()
    {
        // Export to Excel/PDF
        // Implementation depends on your export package
    }

    public function approveStatement()
    {
        DB::beginTransaction();
        try {
            // Update all items for this period as approved
            StatementFinancialPositionItem::where('reporting_period', $this->reportingPeriod)
                ->whereNull('approved_by')
                ->update([
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);
            
            // Create version snapshot
            $this->createVersionSnapshot('approved');
            
            DB::commit();
            
            session()->flash('success', 'Statement approved successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error approving statement: ' . $e->getMessage());
        }
    }

    private function createVersionSnapshot($status)
    {
        $data = [
            'assets' => $this->assetsData,
            'liabilities' => $this->liabilitiesData,
            'equity' => $this->equityData,
            'totals' => [
                'assets' => $this->totalAssets,
                'liabilities' => $this->totalLiabilities,
                'equity' => $this->totalEquity
            ]
        ];
        
        DB::table('financial_statement_versions')->insert([
            'statement_type' => 'position',
            'version_number' => $this->generateVersionNumber(),
            'reporting_period' => $this->reportingPeriod,
            'reporting_date' => $this->reportingDate,
            'status' => $status,
            'statement_data' => json_encode($data),
            'prepared_by' => auth()->id(),
            'prepared_at' => now(),
            'created_at' => now()
        ]);
    }

    private function generateVersionNumber()
    {
        $lastVersion = DB::table('financial_statement_versions')
            ->where('statement_type', 'position')
            ->where('reporting_period', $this->reportingPeriod)
            ->orderBy('version_number', 'desc')
            ->first();
        
        if ($lastVersion) {
            $versionParts = explode('.', $lastVersion->version_number);
            $versionParts[count($versionParts) - 1]++;
            return implode('.', $versionParts);
        }
        
        return '1.0.0';
    }

    public function updatedSelectedYear()
    {
        $this->updateReportingPeriod();
        $this->loadFinancialData();
    }

    public function updatedSelectedMonth()
    {
        $this->updateReportingPeriod();
        $this->loadFinancialData();
    }

    public function render()
    {
        $items = StatementFinancialPositionItem::query()
            ->where('reporting_period', $this->reportingPeriod);
        
        if ($this->search) {
            $items->where(function($query) {
                $query->where('item_name', 'like', '%' . $this->search . '%')
                      ->orWhere('item_code', 'like', '%' . $this->search . '%')
                      ->orWhere('account_number', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->categoryFilter !== 'all') {
            $items->where('category', $this->categoryFilter);
        }
        
        if ($this->statusFilter !== 'all') {
            $items->where('status', $this->statusFilter);
        }
        
        $items = $items->orderBy('category')
                      ->orderBy('sub_category')
                      ->orderBy('display_order')
                      ->paginate(20);
        
        return view('livewire.accounting.statement-of-financial-position-enhanced', [
            'items' => $items,
            'accountsList' => AccountsModel::where('status', 'ACTIVE')->orderBy('account_name')->get()
        ]);
    }
}