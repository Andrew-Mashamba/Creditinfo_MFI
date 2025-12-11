<?php
namespace App\Http\Livewire\Accounting;

use App\Jobs\CalculatePpeDepreciation;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\PPE;
use App\Models\PpeMaintenanceRecord;
use App\Models\PpeTransfer;
use App\Models\PpeRevaluation;
use App\Models\PpeInsurance;
use App\Services\TransactionPostingService;
use App\Services\BalanceSheetItemIntegrationService;
use App\Services\PpeLifecycleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Services\AccountCreationService;

class PpeManagement extends Component
{
    use WithPagination;

    // Navigation and UI state
    public $selectedMenuItem = 1;
    public $search = '';
    public $isEditMode = false;
    public $ppeId;

    // PPE form properties
    public $name, $purchase_price = 0.0, $purchase_date, $salvage_value = 0.0, $useful_life = 1, $quantity = 1;
    public $additions, $status = 'active', $location, $notes;
    public $categoryx;
    public $category = 'property_plant_and_equipment';
    public $asset_sub_category_code;
    public $cash_account;
    public $category_code;
    
    // Additional costs for proper capitalization
    public $legal_fees = 0.0, $registration_fees = 0.0, $renovation_costs = 0.0;
    public $transportation_costs = 0.0, $installation_costs = 0.0, $other_costs = 0.0;
    
    // Payment method and related accounts
    public $payment_method = 'cash';
    public $payment_account_number;
    public $payable_account_number;
    
    // Account selection for proper flow
    public $parent_account_number; // Parent account to create PPE account under
    public $other_account_id; // The other account for double-entry (Cash/Bank/Payable - credit side)
    
    // Additional useful fields
    public $supplier_name, $invoice_number, $invoice_date, $additional_notes;

    // Disposal tracking fields
    public $disposal_date, $disposal_method = 'sold', $disposal_proceeds = 0.0, $disposal_notes;
    public $disposal_approval_status = 'pending', $disposal_rejection_reason;
    public $showDisposalForm = false;
    public $disposalAssetId = null;
    public $showRejectionModal = false;
    public $rejectionAssetId = null;

    // Computed fields
    public $initial_value = 0.0, $depreciation_rate = 0.0, $accumulated_depreciation = 0.0, $depreciation_for_year = 0.0, $closing_value = 0.0;

    // Table functionality
    public $selectedPpes = [];
    public $selectAll = false;
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $conditionFilter = '';
    
    // Report properties
    public $reportType = 'summary';
    public $reportDateRange = 'this_month';
    public $reportStartDate = '';
    public $reportEndDate = '';
    public $reportCategory = 'all';
    public $reportStatus = 'all';

    // Category management
    public $showCategoryForm = false;
    public $categoryName = '';
    public $categoryDepreciationRate = '';
    public $editingCategoryId = null;

    // Reports and exports (removed duplicates - already defined above)

    // Chart data
    public $chartLabels = [];
    public $chartData = [];

    // Account properties
    public $account_table_name = 'asset_accounts';
    public $next_code_no = 1701;
    public $category_code_of_account = 1700;
    public $cash_account_sub_code;
    public $narration;

    // Enhanced PPE fields
    public $asset_code, $barcode, $serial_number, $manufacturer, $model;
    public $depreciation_method = 'straight_line';
    public $condition = 'excellent';
    public $warranty_start_date, $warranty_end_date, $warranty_provider, $warranty_terms;
    public $department_id, $custodian_id, $assigned_to;
    
    // Maintenance properties
    public $showMaintenanceForm = false;
    public $maintenanceId;
    public $maintenance_type = 'preventive';
    public $maintenance_date, $maintenance_description, $maintenance_vendor;
    public $maintenance_cost = 0, $maintenance_parts_replaced;
    public $next_maintenance_date;
    
    // Transfer properties
    public $showTransferForm = false;
    public $transferId;
    public $transfer_to_location, $transfer_to_department, $transfer_to_custodian;
    public $transfer_date, $transfer_reason, $transfer_notes;
    
    // Insurance properties
    public $showInsuranceForm = false;
    public $insuranceId;
    public $policy_number, $insurance_company, $coverage_type = 'comprehensive';
    public $insured_value, $premium_amount, $insurance_start_date, $insurance_end_date;
    public $deductible, $coverage_details, $agent_name, $agent_contact;
    
    // Revaluation properties
    public $showRevaluationForm = false;
    public $revaluationId;
    public $revaluation_date, $new_value, $revaluation_reason;
    public $valuation_method, $supporting_documents;
    
    // Import for pre-existing assets
    public $importMode = false;
    public $importData = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'categoryx' => 'required|string|exists:accounts,account_number',
        'purchase_price' => 'required|numeric|min:0',
        'purchase_date' => 'required|date',
        'salvage_value' => 'required|numeric|min:0',
        'useful_life' => 'required|numeric|min:1',
        'quantity' => 'required|numeric|min:1',
        'location' => 'required|string|max:255',
        'notes' => 'nullable|string|max:1000',
        'status' => 'required|in:active,disposed,under_repair,pending_disposal',
        // Account selection validation
        'parent_account_number' => 'required|string|exists:accounts,account_number',
        'other_account_id' => 'required|string',
        // Additional costs validation
        'legal_fees' => 'nullable|numeric|min:0',
        'registration_fees' => 'nullable|numeric|min:0',
        'renovation_costs' => 'nullable|numeric|min:0',
        'transportation_costs' => 'nullable|numeric|min:0',
        'installation_costs' => 'nullable|numeric|min:0',
        'other_costs' => 'nullable|numeric|min:0',
        // Payment method validation
        'payment_method' => 'required|in:cash,credit,loan,lease',
        'payment_account_number' => 'nullable|string|max:255',
        'payable_account_number' => 'nullable|string|max:255',
        // Additional fields validation
        'supplier_name' => 'nullable|string|max:255',
        'invoice_number' => 'nullable|string|max:255',
        'invoice_date' => 'nullable|date',
        'additional_notes' => 'nullable|string|max:1000',
        // Disposal fields validation
        'disposal_date' => 'nullable|date',
        'disposal_method' => 'nullable|in:sold,scrapped,donated,lost,stolen,other',
        'disposal_proceeds' => 'nullable|numeric|min:0',
        'disposal_notes' => 'nullable|string|max:1000',
        'disposal_rejection_reason' => 'nullable|string|max:500',
    ];

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'ppeUpdated' => 'refreshData',
        'bulkActionCompleted' => 'refreshData'
    ];

    public function mount()
    {
        Log::info('PPE Management - Component mounting', [
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            $this->refreshData();
            $this->generateChartData();
            
            Log::info('PPE Management - Component mounted successfully', [
                'selected_menu' => $this->selectedMenuItem,
                'data_refreshed' => true,
                'chart_generated' => true
            ]);
        } catch (\Exception $e) {
            Log::error('PPE Management - Mount failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function selectMenu($menuItem)
    {
        Log::info('PPE Management - selectMenu called', [
            'menuItem' => $menuItem,
            'previous_menu' => $this->selectedMenuItem,
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            $this->selectedMenuItem = $menuItem;
            $this->resetForm();
            $this->resetPage();
            
            Log::info('PPE Management - Menu selection successful', [
                'selected_menu' => $this->selectedMenuItem,
                'form_reset' => true
            ]);
        } catch (\Exception $e) {
            Log::error('PPE Management - Menu selection failed', [
                'menuItem' => $menuItem,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to switch menu: ' . $e->getMessage());
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedCategoryx($value)
    {
        Log::info('PPE Management - Category updated', [
            'new_value' => $value,
            'user_id' => auth()->id()
        ]);
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedPpes = PPE::query()->pluck('id')->toArray();
        } else {
            $this->selectedPpes = [];
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function refreshData()
    {
        $this->resetPage();
        $this->generateChartData();
    }

    public function getPpes()
    {
        $query = PPE::query();

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
                  ->orWhere('location', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply category filter
        if ($this->categoryFilter) {
            $query->where('category', $this->categoryFilter);
        }

        // Apply condition filter
        if ($this->conditionFilter) {
            $query->where('condition', $this->conditionFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    // Bulk Actions
    public function bulkDelete()
    {
        if (!Gate::allows('delete-ppe')) {
            $this->addError('permission', 'You do not have permission to delete PPE assets.');
            return;
        }

        $deletedCount = PPE::whereIn('id', $this->selectedPpes)->delete();
        $this->selectedPpes = [];
        $this->refreshData();
        $this->emit('showNotification', "Successfully deleted {$deletedCount} PPE assets", 'success');
    }

    public function bulkExport()
    {
        $assets = PPE::whereIn('id', $this->selectedPpes)->get();
        
        return response()->streamDownload(function () use ($assets) {
            $this->formatExportData($assets);
        }, 'ppe_assets_' . date('Y-m-d') . '.csv');
    }

    public function bulkDepreciation()
    {
        CalculatePpeDepreciation::dispatch($this->selectedPpes);
        $this->selectedPpes = [];
        $this->refreshData();
        $this->emit('showNotification', 'Depreciation calculation job has been dispatched!', 'success');
    }

    // Individual asset actions
    public function viewAsset($id)
    {
        $this->ppeId = $id;
        $this->selectedMenuItem = 2;
        $this->edit($id);
    }

    public function editAsset($id)
    {
        if (!Gate::allows('edit-ppe')) {
            $this->addError('permission', 'You do not have permission to edit PPE assets.');
            return;
        }
        
        $this->ppeId = $id;
        $this->selectedMenuItem = 2;
        $this->edit($id);
    }

    public function deleteAsset($id)
    {
        if (!Gate::allows('delete-ppe')) {
            $this->addError('permission', 'You do not have permission to delete PPE assets.');
            return;
        }

        $asset = PPE::find($id);
        if ($asset) {
            $asset->delete();
            $this->refreshData();
            $this->emit('showNotification', 'PPE asset deleted successfully', 'success');
        }
    }

    public function initiateDisposal($id)
    {
        $asset = PPE::find($id);
        if ($asset) {
            $asset->update(['status' => 'pending_disposal']);
            
            // Create approval record
            \App\Models\approvals::create([

                'process_name' => 'Dispose Asset',
                'process_description' => auth()->user()->name . ' has requested to dispose asset: ' . $asset->name,
                'approval_process_description' => 'has approved asset disposal',
                'process_code' => 'ASSET_DISP',
                'process_id' => $asset->id,
                'process_status' => 'PENDING',
                'user_id' => auth()->user()->id,
                'team_id' => 1,
                'approver_id' => 1,
                'approval_status' => 'PENDING',
                'edit_package' => null
            ]);
            
            $this->refreshData();
            $this->emit('showNotification', 'Disposal initiated successfully. Awaiting approval.', 'success');
        }
    }

    public function showDisposalForm($id)
    {
        $asset = PPE::find($id);
        if ($asset && ($asset->status === 'approved_for_disposal' || $asset->disposal_approval_status === 'approved')) {
            $this->disposalAssetId = $id;
            $this->disposal_date = now()->format('Y-m-d');
            $this->disposal_method = 'sold';
            $this->disposal_proceeds = 0.0;
            $this->disposal_notes = '';
            $this->showDisposalForm = true;
        } else {
            $this->emit('showNotification', 'Asset is not approved for disposal', 'error');
        }
    }

    public function completeDisposal()
    {
        $this->validate([
            'disposal_date' => 'required|date',
            'disposal_method' => 'required|in:sold,scrapped,donated,lost,stolen,other',
            'disposal_proceeds' => 'required|numeric|min:0',
            'disposal_notes' => 'nullable|string|max:1000'
        ]);

        $asset = PPE::find($this->disposalAssetId);
        if ($asset && ($asset->status === 'approved_for_disposal' || $asset->disposal_approval_status === 'approved')) {
            $asset->update([
                'status' => 'disposed',
                'disposal_date' => $this->disposal_date,
                'disposal_method' => $this->disposal_method,
                'disposal_proceeds' => $this->disposal_proceeds,
                'disposal_notes' => $this->disposal_notes,
                'disposal_approval_status' => 'completed',
                'disposal_approved_by' => auth()->id(),
                'disposal_approved_at' => now()
            ]);

            // Create accounting entry for disposal
            $this->createDisposalAccountingEntry($asset);

            $this->resetDisposalForm();
            $this->refreshData();
            $this->emit('showNotification', 'Disposal completed successfully', 'success');
        } else {
            $this->emit('showNotification', 'Asset is not approved for disposal', 'error');
        }
    }

    private function createDisposalAccountingEntry($asset)
    {
        try {
            DB::beginTransaction();
            
            $transactionService = new TransactionPostingService();
            
            // Get institution accounts configuration
            $institution = DB::table('institutions')->where('id', 1)->first();
            
            // Calculate values
            $originalCost = $asset->initial_value ?? $asset->purchase_price;
            $accumulatedDepreciation = $asset->accumulated_depreciation ?? 0;
            $netBookValue = $originalCost - $accumulatedDepreciation;
            $proceeds = $asset->disposal_proceeds ?? 0;
            $gainLoss = $proceeds - $netBookValue;
            
            Log::info('PPE Disposal Accounting', [
                'asset_id' => $asset->id,
                'original_cost' => $originalCost,
                'accumulated_depreciation' => $accumulatedDepreciation,
                'net_book_value' => $netBookValue,
                'proceeds' => $proceeds,
                'gain_loss' => $gainLoss
            ]);
            
            // Entry 1: Remove accumulated depreciation
            if ($accumulatedDepreciation > 0 && $asset->depreciation_account_number) {
                $depreciationEntry = [
                    'first_account' => $asset->depreciation_account_number, // Debit: Accumulated Depreciation
                    'second_account' => $asset->account_number, // Credit: PPE Asset Account (partial)
                    'amount' => $accumulatedDepreciation,
                    'narration' => "PPE Disposal: {$asset->name} - Reverse Accumulated Depreciation",
                    'action' => 'asset_disposal'
                ];
                
                $result1 = $transactionService->postTransaction($depreciationEntry);
                if ($result1['status'] !== 'success') {
                    throw new \Exception('Failed to post depreciation reversal: ' . ($result1['message'] ?? 'Unknown error'));
                }
            }
            
            // Entry 2: Record cash/receivable from disposal
            if ($proceeds > 0) {
                // Determine the account based on disposal method
                $proceedsAccount = match($asset->disposal_method) {
                    'sold' => $this->other_account_id ?? $institution->cash_account ?? '0101100010001010',
                    'scrapped' => $institution->cash_account ?? '0101100010001010',
                    'donated' => $institution->donation_expense_account ?? $institution->other_expenses_account,
                    default => $institution->cash_account ?? '0101100010001010'
                };
                
                $proceedsEntry = [
                    'first_account' => $proceedsAccount, // Debit: Cash/Bank/Receivable
                    'second_account' => $asset->account_number, // Credit: PPE Asset Account
                    'amount' => $proceeds,
                    'narration' => "PPE Disposal: {$asset->name} - Proceeds from {$asset->disposal_method}",
                    'action' => 'asset_disposal'
                ];
                
                $result2 = $transactionService->postTransaction($proceedsEntry);
                if ($result2['status'] !== 'success') {
                    throw new \Exception('Failed to post proceeds entry: ' . ($result2['message'] ?? 'Unknown error'));
                }
            }
            
            // Entry 3: Record gain or loss on disposal
            if (abs($gainLoss) > 0.01) { // Only if significant gain/loss
                $gainLossAccount = $gainLoss > 0 
                    ? ($institution->gain_on_disposal_account ?? $institution->other_income_account ?? '0101140000')
                    : ($institution->loss_on_disposal_account ?? $institution->other_expenses_account ?? '0101150000');
                
                if ($gainLoss > 0) {
                    // Gain on disposal
                    $gainEntry = [
                        'first_account' => $asset->account_number, // Debit: PPE Asset (remaining balance)
                        'second_account' => $gainLossAccount, // Credit: Gain on Disposal
                        'amount' => abs($gainLoss),
                        'narration' => "PPE Disposal: {$asset->name} - Gain on Disposal",
                        'action' => 'asset_disposal'
                    ];
                } else {
                    // Loss on disposal
                    $gainEntry = [
                        'first_account' => $gainLossAccount, // Debit: Loss on Disposal
                        'second_account' => $asset->account_number, // Credit: PPE Asset (remaining balance)
                        'amount' => abs($gainLoss),
                        'narration' => "PPE Disposal: {$asset->name} - Loss on Disposal",
                        'action' => 'asset_disposal'
                    ];
                }
                
                $result3 = $transactionService->postTransaction($gainEntry);
                if ($result3['status'] !== 'success') {
                    throw new \Exception('Failed to post gain/loss entry: ' . ($result3['message'] ?? 'Unknown error'));
                }
            }
            
            // Entry 4: Clear the remaining asset balance (should be original cost now)
            $clearingEntry = [
                'first_account' => $asset->account_number, // This will zero out the account
                'second_account' => $asset->account_number, // Self-balancing to clear
                'amount' => $originalCost - $accumulatedDepreciation - $proceeds,
                'narration' => "PPE Disposal: {$asset->name} - Clear Asset Account",
                'action' => 'asset_disposal'
            ];
            
            // Only post if there's a remaining balance
            $assetAccount = AccountsModel::where('account_number', $asset->account_number)->first();
            if ($assetAccount && $assetAccount->balance != 0) {
                // We need to properly clear this - let's skip the self-balancing and log instead
                Log::info('PPE Asset account should now be zero', [
                    'account_number' => $asset->account_number,
                    'current_balance' => $assetAccount->balance
                ]);
            }
            
            DB::commit();
            
            Log::info('PPE disposal accounting entries completed successfully', [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PPE disposal accounting failed', [
                'error' => $e->getMessage(),
                'asset_id' => $asset->id
            ]);
            throw $e;
        }
    }

    public function resetDisposalForm()
    {
        $this->showDisposalForm = false;
        $this->disposalAssetId = null;
        $this->disposal_date = '';
        $this->disposal_method = 'sold';
        $this->disposal_proceeds = 0.0;
        $this->disposal_notes = '';
        $this->disposal_rejection_reason = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // Category Management
    public function toggleCategoryForm()
    {
        $this->showCategoryForm = !$this->showCategoryForm;
        $this->resetCategoryForm();
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|string|max:255',
            'categoryDescription' => 'nullable|string|max:500'
        ]);

        // Create new category
        PPE::create([
            'name' => $this->categoryName,
            'category' => $this->categoryName,
            'description' => $this->categoryDescription,
            'status' => 'active'
        ]);

        $this->resetCategoryForm();
        $this->refreshData();
        $this->emit('showNotification', 'Category created successfully', 'success');
    }

    public function editCategory($id)
    {
        $this->editingCategoryId = $id;
        // Load category data
        $this->showCategoryForm = true;
    }

    public function deleteCategory($id)
    {
        if (!Gate::allows('delete-category')) {
            $this->addError('permission', 'You do not have permission to delete categories.');
            return;
        }
        
        $category = PPE::find($id);
        if ($category) {
            $category->delete();
            $this->refreshData();
            $this->emit('showNotification', 'Category deleted successfully', 'success');
        }
    }

    public function resetCategoryForm()
    {
        $this->categoryName = '';
        $this->categoryDepreciationRate = '';
        $this->editingCategoryId = null;
    }

    // Reports and Exports
    public function generateReport()
    {
        // Check if reportType is set and handle accordingly
        if ($this->reportType) {
            // Validate report parameters
            $this->validate([
                'reportType' => 'required|in:summary,disposal,depreciation,valuation,maintenance,register',
                'reportDateRange' => 'required|in:this_month,last_month,this_quarter,this_year,custom,30,60,90',
            ]);
            
            // Generate report based on type
            switch ($this->reportType) {
                case 'summary':
                    return $this->generateSummaryReport();
                case 'register':
                    return $this->generateAssetRegisterReport();
                case 'disposal':
                    return $this->generateDisposalReport();
                case 'depreciation':
                    return $this->generateDepreciationReport();
                case 'valuation':
                    return $this->generateValuationReport();
                case 'maintenance':
                    return $this->generateMaintenanceReport();
                default:
                    // Fall back to default report
                    break;
            }
        }
        
        // Default report generation (existing logic)
        $assets = PPE::when($this->statusFilter, function ($query) {
            return $query->where('status', $this->statusFilter);
        })->get();

        return response()->streamDownload(function () use ($assets) {
            $this->formatExportData($assets);
        }, 'ppe_report_' . date('Y-m-d') . '.csv');
    }

    public function exportExcel()
    {
        // Generate Excel-compatible CSV based on selected report type
        return $this->generateReport();
    }

    public function exportPdf()
    {
        // For now, generate CSV (PDF generation requires additional packages)
        $this->emit('showNotification', 'Downloading report as CSV (PDF export coming soon)', 'info');
        return $this->generateReport();
    }

    private function formatExportData($assets)
    {
        $data = [];
        $data[] = ['Name', 'Category', 'Purchase Date', 'Initial Value', 'Accumulated Depreciation', 'Net Book Value', 'Status'];
        
        foreach ($assets as $asset) {
            $data[] = [
                $asset->name,
                $asset->category,
                $asset->purchase_date,
                $asset->initial_value,
                $asset->accumulated_depreciation,
                $asset->closing_value,
                $asset->status
            ];
        }
        
        return $data;
    }

    // Chart Data Generation
    public function generateChartData()
    {
        $months = [];
        $depreciation = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Calculate depreciation for assets created up to this month
            // Using depreciation_for_year divided by 12 for monthly depreciation
            $assetsUpToDate = PPE::where('created_at', '<=', $date->endOfMonth())->get();
            $monthlyDep = 0;
            
            foreach ($assetsUpToDate as $asset) {
                // Calculate monthly depreciation from yearly depreciation
                $monthlyDep += ($asset->depreciation_for_year ?? 0) / 12;
            }
            
            $depreciation[] = round($monthlyDep, 2);
        }
        
        $this->chartLabels = $months;
        $this->chartData = $depreciation;
        
        $this->dispatchBrowserEvent('chart-updated', [
            'labels' => $this->chartLabels,
            'data' => $this->chartData
        ]);
    }

    // Computed Properties for Dashboard
    public function getTotalPpeValueProperty()
    {
        // Calculate total PPE value (initial value minus accumulated depreciation)
        $totalInitialValue = PPE::sum('initial_value') ?? 0;
        $totalAccumulatedDepreciation = PPE::sum('accumulated_depreciation') ?? 0;
        return $totalInitialValue - $totalAccumulatedDepreciation;
    }

    public function getTotalAccumulatedDepreciationProperty()
    {
        return PPE::sum('accumulated_depreciation');
    }

    public function getNetBookValueProperty()
    {
        return $this->totalPpeValue - $this->totalAccumulatedDepreciation;
    }

    public function getMonthlyDepreciationProperty()
    {
        // Calculate current month's depreciation from yearly depreciation
        $totalYearlyDepreciation = PPE::sum('depreciation_for_year') ?? 0;
        return round($totalYearlyDepreciation / 12, 2);
    }

    public function getRecentPpesProperty()
    {
        return PPE::orderBy('created_at', 'desc')->limit(5)->get();
    }

    public function getPendingDisposalsProperty()
    {
        return PPE::where('status', 'pending_disposal')->limit(5)->get();
    }

    public function getPendingApprovalDisposalsProperty()
    {
        return PPE::where('status', 'pending_disposal')
                  ->where('disposal_approval_status', 'pending')
                  ->limit(5)->get();
    }

    public function getApprovedForDisposalProperty()
    {
        return PPE::where('status', 'approved_for_disposal')
                  ->limit(5)->get();
    }

    public function getApprovedDisposalsProperty()
    {
        return PPE::where('disposal_approval_status', 'approved')
                  ->where('status', 'pending_disposal')
                  ->limit(5)->get();
    }

    public function getRejectedDisposalsProperty()
    {
        return PPE::where('disposal_approval_status', 'rejected')
                  ->limit(5)->get();
    }

    public function getCompletedDisposalsProperty()
    {
        return PPE::where('status', 'disposed')
                  ->whereNotNull('disposal_date')
                  ->orderBy('disposal_date', 'desc')
                  ->limit(5)->get();
    }

    public function getCategoriesProperty()
    {
        // Get category statistics from actual PPE data
        $categories = PPE::select('category', 
                                 DB::raw('COUNT(*) as asset_count'),
                                 DB::raw('SUM(initial_value) as total_value'),
                                 DB::raw('AVG(depreciation_rate) as avg_depreciation_rate'))
                        ->groupBy('category')
                        ->get()
                        ->map(function($cat, $index) {
                            return [
                                'id' => $index + 1,
                                'name' => ucfirst($cat->category),
                                'depreciation_rate' => round($cat->avg_depreciation_rate, 2),
                                'asset_count' => $cat->asset_count,
                                'total_value' => $cat->total_value
                            ];
                        })
                        ->toArray();

        // Add default categories if none exist
        if (empty($categories)) {
            return [
                ['id' => 1, 'name' => 'Buildings', 'depreciation_rate' => 2.5, 'asset_count' => 0, 'total_value' => 0],
                ['id' => 2, 'name' => 'Vehicles', 'depreciation_rate' => 20, 'asset_count' => 0, 'total_value' => 0],
                ['id' => 3, 'name' => 'Equipment', 'depreciation_rate' => 15, 'asset_count' => 0, 'total_value' => 0],
                ['id' => 4, 'name' => 'Furniture', 'depreciation_rate' => 10, 'asset_count' => 0, 'total_value' => 0],
                ['id' => 5, 'name' => 'Software', 'depreciation_rate' => 33.33, 'asset_count' => 0, 'total_value' => 0]
            ];
        }

        return $categories;
    }

    public function getPpeCategoriesProperty()
    {
        // Get PPE categories from accounts table based on institution's property_and_equipment_account
        $institution = \App\Models\institutions::find(1); // Assuming institution ID 1, adjust as needed
        $propertyAccount = $institution ? $institution->property_and_equipment_account : null;
        
        if ($propertyAccount) {
            return AccountsModel::where('parent_account_number', $propertyAccount)
                               ->where('account_use', 'internal')
                               ->get();
        }
        
        return collect();
    }

    public function getSampleReportProperty()
    {
        return PPE::limit(10)->get();
    }
    
    // Additional computed properties for dashboard
    public function getTotalAssetCountProperty()
    {
        return PPE::count();
    }
    
    public function getActiveAssetsCountProperty()
    {
        return PPE::where('status', 'active')->count();
    }
    
    public function getPendingDisposalCountProperty()
    {
        // Count assets marked for disposal but not yet approved
        return PPE::where('status', 'pending_disposal')
                  ->orWhere(function($query) {
                      $query->where('disposal_approval_status', 'pending')
                            ->whereNotIn('status', ['disposed']);
                  })
                  ->count();
    }
    
    public function getPendingApprovalCountProperty()
    {
        // Count assets with pending disposal approval
        return PPE::where('disposal_approval_status', 'pending')
                  ->whereNotIn('status', ['disposed'])
                  ->count();
    }
    
    public function getApprovedDisposalCountProperty()
    {
        // Count assets approved for disposal but not yet disposed
        return PPE::where('disposal_approval_status', 'approved')
                  ->whereNotIn('status', ['disposed'])
                  ->count();
    }
    
    public function getRejectedDisposalCountProperty()
    {
        return PPE::where('disposal_approval_status', 'rejected')->count();
    }
    
    public function getCompletedDisposalCountProperty()
    {
        // Count assets that have been fully disposed
        return PPE::where('status', 'disposed')
                  ->orWhere('disposal_approval_status', 'completed')
                  ->count();
    }
    
    public function getAssetsForDisposalProperty()
    {
        // Show active assets that can be disposed, or assets already marked for disposal
        return PPE::whereIn('status', ['active', 'pending_disposal', 'under_repair'])
                  ->where(function($query) {
                      // Exclude assets that have already been disposed
                      $query->whereNull('disposal_approval_status')
                            ->orWhere('disposal_approval_status', '!=', 'completed');
                  })
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    // Existing methods (updated versions)
    public function updated($field)
    {
        $this->validateOnly($field);
        
        // Cast numeric fields to float
        $numericFields = [
            'purchase_price', 'salvage_value', 'useful_life', 'quantity',
            'legal_fees', 'registration_fees', 'renovation_costs',
            'transportation_costs', 'installation_costs', 'other_costs'
        ];
        
        if (in_array($field, $numericFields)) {
            $this->$field = (float)($this->$field ?? 0);
        }
        
        $this->calculateValues();
    }

    public function calculateValues()
    {
        // Calculate total capitalized cost including all additional costs
        $total_capitalized_cost = (float)$this->purchase_price + 
                                 (float)$this->legal_fees + 
                                 (float)$this->registration_fees + 
                                 (float)$this->renovation_costs + 
                                 (float)$this->transportation_costs + 
                                 (float)$this->installation_costs + 
                                 (float)$this->other_costs;
        
        $this->initial_value = $total_capitalized_cost * (int)$this->quantity;

        if ($this->useful_life > 0 && $this->salvage_value >= 0) {
            $this->depreciation_rate = (($total_capitalized_cost - $this->salvage_value) / $this->useful_life) * 100;
        }

        $years_in_use = max((date('Y') - date('Y', strtotime($this->purchase_date))), 0);

        if ($this->depreciation_rate > 0) {
            $this->accumulated_depreciation = ($this->depreciation_rate / 100) * $this->initial_value * $years_in_use;
        } else {
            $this->accumulated_depreciation = 0;
        }

        if ($this->useful_life > 0) {
            $this->depreciation_for_year = ($total_capitalized_cost - $this->salvage_value) / $this->useful_life;
        } else {
            $this->depreciation_for_year = 0;
        }

        $this->closing_value = $this->initial_value - $this->accumulated_depreciation;
    }

    public function store()
    {
        Log::info('PPE Management - Store method called', [
            'user_id' => auth()->id(),
            'form_data' => [
                'name' => $this->name,
                'categoryx' => $this->categoryx,
                'purchase_price' => $this->purchase_price,
                'purchase_date' => $this->purchase_date,
                'parent_account_number' => $this->parent_account_number,
                'other_account_id' => $this->other_account_id,
                'payment_method' => $this->payment_method,
                'location' => $this->location,
                'useful_life' => $this->useful_life,
                'salvage_value' => $this->salvage_value,
            ],
            'timestamp' => now()->toDateTimeString()
        ]);
        
        try {
            // Validate form data
            Log::info('PPE Management - Starting validation');
            $this->validate();
            Log::info('PPE Management - Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PPE Management - Validation failed', [
                'errors' => $e->errors(),
                'form_data' => [
                    'name' => $this->name,
                    'categoryx' => $this->categoryx,
                    'parent_account_number' => $this->parent_account_number,
                    'other_account_id' => $this->other_account_id,
                ]
            ]);
            throw $e;
        }

        // Calculate values before storing
        Log::info('PPE Management - Calculating values');
        $this->calculateValues();

        // Use PpeLifecycleService for comprehensive asset creation
        $lifecycleService = new PpeLifecycleService();
        
        // Get the account name for the selected category
        $account = AccountsModel::where('account_number', $this->categoryx)->first();
        $category_name = $account ? $account->account_name : $this->categoryx;
        
        Log::info('PPE Management - Account lookup', [
            'categoryx' => $this->categoryx,
            'account_found' => $account ? true : false,
            'category_name' => $category_name
        ]);

        // Prepare comprehensive asset data
        $assetData = [
            'name' => ucwords($this->name),
            'category' => $category_name,
            'purchase_price' => $this->purchase_price,
            'purchase_date' => $this->purchase_date,
            'salvage_value' => $this->salvage_value,
            'useful_life' => $this->useful_life,
            'quantity' => $this->quantity,
            'initial_value' => $this->initial_value,
            'depreciation_rate' => $this->depreciation_rate,
            'accumulated_depreciation' => $this->accumulated_depreciation,
            'depreciation_for_year' => $this->depreciation_for_year,
            'depreciation_for_month' => round($this->depreciation_for_year / 12, 2),
            'closing_value' => $this->closing_value,
            'status' => $this->status,
            'location' => $this->location,
            'account_number' => $this->categoryx,
            'notes' => $this->notes,
            // Additional costs for capitalization
            'legal_fees' => $this->legal_fees,
            'registration_fees' => $this->registration_fees,
            'renovation_costs' => $this->renovation_costs,
            'transportation_costs' => $this->transportation_costs,
            'installation_costs' => $this->installation_costs,
            'other_costs' => $this->other_costs,
            // Payment and supplier details
            'payment_method' => $this->payment_method,
            'payment_account_number' => !empty($this->payment_account_number) ? $this->payment_account_number : null,
            'payable_account_number' => !empty($this->payable_account_number) ? $this->payable_account_number : null,
            'supplier_name' => !empty($this->supplier_name) ? $this->supplier_name : null,
            'invoice_number' => !empty($this->invoice_number) ? $this->invoice_number : null,
            'invoice_date' => !empty($this->invoice_date) ? $this->invoice_date : null,
            'additional_notes' => !empty($this->additional_notes) ? $this->additional_notes : null,
            // Enhanced lifecycle fields
            'asset_code' => $this->asset_code ?? PPE::generateAssetCode($category_name),
            'barcode' => $this->barcode,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'depreciation_method' => $this->depreciation_method,
            'condition' => $this->condition,
            'warranty_start_date' => !empty($this->warranty_start_date) ? $this->warranty_start_date : null,
            'warranty_end_date' => !empty($this->warranty_end_date) ? $this->warranty_end_date : null,
            'warranty_provider' => $this->warranty_provider,
            'warranty_terms' => $this->warranty_terms,
            'department_id' => $this->department_id,
            'custodian_id' => $this->custodian_id,
            'assigned_to' => $this->assigned_to,
        ];

        // Add insurance data if provided
        if ($this->policy_number && $this->insurance_company) {
            $assetData['insurance'] = [
                'policy_number' => $this->policy_number,
                'insurance_company' => $this->insurance_company,
                'coverage_type' => $this->coverage_type,
                'insured_value' => $this->insured_value ?? $this->initial_value,
                'premium_amount' => $this->premium_amount,
                'start_date' => !empty($this->insurance_start_date) ? $this->insurance_start_date : now(),
                'end_date' => !empty($this->insurance_end_date) ? $this->insurance_end_date : null,
                'deductible' => $this->deductible,
                'coverage_details' => $this->coverage_details,
                'agent_name' => $this->agent_name,
                'agent_contact' => $this->agent_contact,
            ];
        }

        // Create asset using lifecycle service
        try {
            Log::info('PPE Management - Creating asset with lifecycle service', [
                'asset_data' => $assetData
            ]);
            
            $ppe = $lifecycleService->createAsset($assetData);
            
            Log::info('PPE Management - Asset created successfully', [
                'ppe_id' => $ppe->id ?? null,
                'asset_name' => $this->name
            ]);
        } catch (\Exception $e) {
            Log::error('PPE Management - Failed to create asset', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'asset_data' => $assetData
            ]);
            
            session()->flash('error', 'Failed to create PPE asset: ' . $e->getMessage());
            return;
        }

        // Use Balance Sheet Integration Service to create accounts and post to GL
        $integrationService = new BalanceSheetItemIntegrationService();
        
        try {
            Log::info('PPE Management - Starting integration with accounts', [
                'parent_account' => $this->parent_account_number,
                'other_account' => $this->other_account_id
            ]);
            
            // Create PPE account and post to GL with custom accounts if provided
            $integrationService->createPPEAccount(
                (object)[
                    'id' => $ppe->id,
                    'asset_name' => $this->name,
                    'cost' => $this->initial_value,
                    'category' => $category_name
                ],
                $this->parent_account_number,  // Parent account to create PPE account under
                $this->other_account_id        // The other account for double-entry (Cash/Bank/Payable)
            );
            
            Log::info('PPE asset created and integrated with accounts table', [
                'ppe_id' => $ppe->id,
                'asset_name' => $this->name,
                'cost' => $this->initial_value,
                'parent_account' => $this->parent_account_number,
                'other_account' => $this->other_account_id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to integrate PPE with accounts table', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ppe_id' => $ppe->id ?? null
            ]);
            // Don't fail the entire operation, but log the error
            session()->flash('warning', 'PPE created but account integration failed: ' . $e->getMessage());
        }


        // Process additional costs
        $additional_costs = ['legal_fees', 'registration_fees', 'renovation_costs', 'transportation_costs', 'installation_costs', 'other_costs'];
        
        // Initialize transaction service for additional costs processing
        $transactionService = new TransactionPostingService();
        
        // Get the PPE account that was just created
        $ppeAccount = null;
        if (isset($ppe) && $ppe->account_number) {
            $ppeAccount = AccountsModel::where('account_number', $ppe->account_number)->first();
        }
        
        // Process additional costs if we have valid services and accounts
        if ($transactionService && $ppeAccount) {
            foreach ($additional_costs as $cost) {
                $totalAmount = $this->$cost;
                if ($totalAmount > 0) {
                    Log::info('PPE Management - Processing additional cost', [
                        'cost_type' => $cost,
                        'amount' => $totalAmount
                    ]);
                    
                    // For additional costs, we debit the PPE account (increase asset value)
                    // and credit the payment source (cash/bank/payable)
                    $transactionData = [
                        'first_account' => $ppeAccount->account_number, // Debit: PPE account (increase asset)
                        'second_account' => $this->other_account_id, // Credit: Cash/Bank/Payable account
                        'amount' => $totalAmount, // amount is the additional cost
                        'narration' => 'PPE Additional Cost - ' . ucwords(str_replace('_', ' ', $cost)) . ' for ' . ucwords($this->name),
                        'action' => 'asset_purchase'
                    ];

                    $result = $transactionService->postTransaction($transactionData);

                    if ($result['status'] !== 'success') {
                        Log::error('Transaction posting failed', [
                            'error' => $result['message'] ?? 'Unknown error',
                            'transaction_data' => $transactionData
                        ]);
                        throw new \Exception('Failed to post transaction: ' . ($result['message'] ?? 'Unknown error'));
                    }

                    Log::info('PPE Management - Additional cost posted successfully', [
                        'cost_type' => $cost,
                        'transaction_reference' => $result['reference'] ?? null,
                        'amount' => $totalAmount,
                        'ppe_account' => $ppeAccount->account_number,
                        'payment_account' => $this->other_account_id
                    ]);
                }
            }
            
            // Log summary of additional costs processed
            $totalAdditionalCosts = $this->legal_fees + $this->registration_fees + $this->renovation_costs + 
                                   $this->transportation_costs + $this->installation_costs + $this->other_costs;
            if ($totalAdditionalCosts > 0) {
                Log::info('PPE Management - All additional costs processed', [
                    'total_additional_costs' => $totalAdditionalCosts,
                    'ppe_id' => $ppe->id,
                    'ppe_account' => $ppeAccount->account_number
                ]);
            }
        } else {
            Log::warning('PPE Management - Skipping additional costs processing', [
                'reason' => 'Transaction service or PPE account not available',
                'has_transaction_service' => isset($transactionService),
                'has_ppe_account' => isset($ppeAccount),
                'ppe_account_number' => $ppe->account_number ?? 'not set'
            ]);
        }
        
        // Final cleanup and success message
        Log::info('PPE Management - Store method completing', [
            'ppe_id' => $ppe->id ?? null,
            'asset_name' => $this->name,
            'switching_to_menu' => 3
        ]);
      
        $this->resetForm();
        $this->refreshData();
        $this->selectedMenuItem = 3;
        
        session()->flash('message', 'PPE asset created successfully');
        $this->emit('formSubmitted');
        $this->emit('showNotification', 'PPE asset created successfully', 'success');
        
        Log::info('PPE Management - Store method completed successfully', [
            'ppe_id' => $ppe->id ?? null,
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString()
        ]);
    }

    public function edit($id)
    {
        $ppe = PPE::find($id);
        if ($ppe) {
            $this->ppeId = $ppe->id;
            $this->name = $ppe->name;
            $this->categoryx = $ppe->account_number; // Use account_number instead of category
            $this->purchase_price = (float)$ppe->purchase_price;
            $this->purchase_date = $ppe->purchase_date;
            $this->salvage_value = (float)$ppe->salvage_value;
            $this->useful_life = (int)$ppe->useful_life;
            $this->quantity = (int)$ppe->quantity;
            $this->additions = $ppe->additions;
            $this->status = $ppe->status;
            $this->location = $ppe->location;
            $this->notes = $ppe->notes;
            
            // Load additional costs
            $this->legal_fees = (float)($ppe->legal_fees ?? 0.0);
            $this->registration_fees = (float)($ppe->registration_fees ?? 0.0);
            $this->renovation_costs = (float)($ppe->renovation_costs ?? 0.0);
            $this->transportation_costs = (float)($ppe->transportation_costs ?? 0.0);
            $this->installation_costs = (float)($ppe->installation_costs ?? 0.0);
            $this->other_costs = (float)($ppe->other_costs ?? 0.0);
            
            // Load payment method and related fields
            $this->payment_method = $ppe->payment_method ?? 'cash';
            $this->payment_account_number = $ppe->payment_account_number ?? '';
            $this->payable_account_number = $ppe->payable_account_number ?? '';
            
            // Load additional fields
            $this->supplier_name = $ppe->supplier_name ?? '';
            $this->invoice_number = $ppe->invoice_number ?? '';
            $this->invoice_date = $ppe->invoice_date ?? '';
            $this->additional_notes = $ppe->additional_notes ?? '';
            
            // Load disposal fields
            $this->disposal_date = $ppe->disposal_date ?? '';
            $this->disposal_method = $ppe->disposal_method ?? 'sold';
            $this->disposal_proceeds = (float)($ppe->disposal_proceeds ?? 0.0);
            $this->disposal_notes = $ppe->disposal_notes ?? '';
            $this->disposal_approval_status = $ppe->disposal_approval_status ?? 'pending';
            $this->disposal_rejection_reason = $ppe->disposal_rejection_reason ?? '';
            
            $this->isEditMode = true;
        }
    }

    public function update()
    {
        $this->validate();

        // Calculate values before updating
        $this->calculateValues();

        $ppe = PPE::find($this->ppeId);
        
        if ($ppe) {
            // Get the account name for the selected category
            $account = AccountsModel::where('account_number', $this->categoryx)->first();
            $category_name = $account ? $account->account_name : $this->categoryx;

            $ppe->update([
                'name' => ucwords($this->name), //to uppercase
                'category' => $category_name,
                'purchase_price' => $this->purchase_price,
                'purchase_date' => $this->purchase_date,
                'salvage_value' => $this->salvage_value,
                'useful_life' => $this->useful_life,
                'quantity' => $this->quantity,
                'initial_value' => $this->initial_value,
                'depreciation_rate' => $this->depreciation_rate,
                'accumulated_depreciation' => $this->accumulated_depreciation,
                'depreciation_for_year' => $this->depreciation_for_year,
                'depreciation_for_month' => round($this->depreciation_for_year / 12, 2),
                'closing_value' => $this->closing_value,
                'status' => $this->status,
                'location' => $this->location,
                'account_number' => $this->categoryx, // Use the selected account number
                'notes' => $this->notes,
            ]);
        }

        $this->resetForm();
        $this->refreshData();
        $this->selectedMenuItem = 3;
        
        session()->flash('message', 'PPE asset updated successfully');
        $this->emit('formSubmitted');
        $this->emit('showNotification', 'PPE asset updated successfully', 'success');
    }

    public function delete($id)
    {
        if (!Gate::allows('delete-ppe')) {
            $this->addError('permission', 'You do not have permission to delete PPE assets.');
            return;
        }

        $ppe = PPE::find($id);
        if ($ppe) {
            $ppe->delete();
            $this->refreshData();
            $this->emit('showNotification', 'PPE asset deleted successfully', 'success');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->categoryx = '';
        $this->purchase_price = 0.0;
        $this->purchase_date = '';
        $this->salvage_value = 0.0;
        $this->useful_life = 1;
        $this->quantity = 1;
        $this->additions = '';
        $this->status = 'active';
        $this->location = '';
        $this->notes = '';
        
        // Reset additional costs
        $this->legal_fees = 0.0;
        $this->registration_fees = 0.0;
        $this->renovation_costs = 0.0;
        $this->transportation_costs = 0.0;
        $this->installation_costs = 0.0;
        $this->other_costs = 0.0;
        
        // Reset payment method and related fields
        $this->payment_method = 'cash';
        $this->payment_account_number = '';
        $this->payable_account_number = '';
        
        // Reset additional fields
        $this->supplier_name = '';
        $this->invoice_number = '';
        $this->invoice_date = '';
        $this->additional_notes = '';
        
        // Reset disposal fields
        $this->disposal_date = '';
        $this->disposal_method = 'sold';
        $this->disposal_proceeds = 0.0;
        $this->disposal_notes = '';
        $this->disposal_approval_status = 'pending';
        $this->disposal_rejection_reason = '';
        $this->showDisposalForm = false;
        $this->disposalAssetId = null;
        $this->showRejectionModal = false;
        $this->rejectionAssetId = null;
        
        // Reset computed fields
        $this->initial_value = 0.0;
        $this->depreciation_rate = 0.0;
        $this->accumulated_depreciation = 0.0;
        $this->depreciation_for_year = 0.0;
        $this->closing_value = 0.0;
        
        $this->isEditMode = false;
        $this->ppeId = null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function runDepreciationJob()
    {
        CalculatePpeDepreciation::dispatch();
        $this->emit('showNotification', 'Depreciation calculation job has been dispatched!', 'success');
    }
    
    public function runDepreciation()
    {
        $this->runDepreciationJob();
    }

    // Helper methods
    public function createNewAccountNumber($major_category_code, $category_code, $sub_category_code, $parent_account)
    {
        $formattedAccountName = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $this->name)));
        $formattedAccountName = str_replace(' ', '_', $formattedAccountName);
        $formattedAccountName = strtoupper($formattedAccountName);

        $account_number = $this->generate_account_number(auth()->user()->branch, $sub_category_code);

        AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => auth()->user()->institution_id,
            'branch_number' => auth()->user()->branch,
            'major_category_code' => $major_category_code,
            'category_code' => $category_code,
            'sub_category_code' => $sub_category_code,
            'account_name' => $formattedAccountName,
            'account_number' => $account_number,
            'notes' => $this->name,
            'account_level' => '3',
            'parent_account_number' => $parent_account,
            'type' => 'asset_account'
        ]);

        return $account_number;
    }

    function luhn_checksum($number)
    {
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
        do {
            $unique_identifier = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $partial_account_number = $branch_code . $unique_identifier . $product_code;
            $checksum = (10 - $this->luhn_checksum($partial_account_number . '0')) % 10;
            $full_account_number = $partial_account_number . $checksum;
            $is_unique = !AccountsModel::where('account_number', $full_account_number)->exists();
        } while (!$is_unique);

        return $full_account_number;
    }


    // Maintenance Management Methods
    public function openMaintenanceForm($ppeId = null)
    {
        $this->showMaintenanceForm = true;
        if ($ppeId) {
            $this->ppeId = $ppeId;
            // Load existing PPE data if needed
            $ppe = PPE::find($ppeId);
            if ($ppe) {
                // You can pre-populate form fields here if needed
            }
        }
        $this->maintenance_date = now()->format('Y-m-d');
        $this->maintenance_type = 'preventive';
    }
    
    public function scheduleMaintenance($ppeId)
    {
        $this->showMaintenanceForm = true;
        $this->ppeId = $ppeId;
        $this->maintenance_date = now()->addMonth()->format('Y-m-d');
        $this->maintenance_type = 'preventive';
    }

    public function saveMaintenance()
    {
        $this->validate([
            'maintenance_type' => 'required',
            'maintenance_date' => 'required|date',
            'maintenance_description' => 'required|string',
        ]);

        // Convert empty date strings to null
        $nextMaintenanceDate = !empty($this->next_maintenance_date) ? $this->next_maintenance_date : null;
        
        $lifecycleService = new PpeLifecycleService();
        $ppe = PPE::find($this->ppeId);
        
        $lifecycleService->scheduleMaintenance($ppe, [
            'maintenance_type' => $this->maintenance_type,
            'maintenance_date' => $this->maintenance_date,
            'description' => $this->maintenance_description,
            'performed_by' => $this->maintenance_vendor ?? 'TBD',
            'next_maintenance_date' => $nextMaintenanceDate,
            'notes' => $this->maintenance_notes ?? null,
        ]);

        $this->resetMaintenanceForm();
        $this->emit('showNotification', 'Maintenance scheduled successfully', 'success');
    }

    public function completeMaintenance($maintenanceId)
    {
        try {
            DB::beginTransaction();
            
            $maintenance = PpeMaintenanceRecord::find($maintenanceId);
            $lifecycleService = new PpeLifecycleService();
            
            // Complete the maintenance record
            $lifecycleService->completeMaintenance($maintenance, [
                'vendor_name' => $this->maintenance_vendor,
                'parts_replaced' => $this->maintenance_parts_replaced,
                'cost' => $this->maintenance_cost,
                'notes' => $this->maintenance_description,
            ]);
            
            // Create accounting entries for maintenance cost
            if ($this->maintenance_cost > 0) {
                $this->createMaintenanceAccountingEntry($maintenance->ppe, $this->maintenance_cost);
            }
            
            DB::commit();
            
            $this->refreshData();
            $this->emit('showNotification', 'Maintenance completed successfully with accounting entries', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Maintenance completion failed', ['error' => $e->getMessage()]);
            $this->emit('showNotification', 'Failed to complete maintenance: ' . $e->getMessage(), 'error');
        }
    }
    
    private function createMaintenanceAccountingEntry($ppe, $cost)
    {
        $transactionService = new TransactionPostingService();
        $institution = DB::table('institutions')->where('id', 1)->first();
        
        // Determine if this is a capital or expense maintenance
        $isCapital = $this->maintenance_type === 'major_repair' || $this->maintenance_type === 'overhaul';
        
        if ($isCapital) {
            // Capital maintenance - increases asset value
            $transactionData = [
                'first_account' => $ppe->account_number, // Debit: PPE Asset (increase value)
                'second_account' => $this->other_account_id ?? $institution->cash_account ?? '0101100010001010', // Credit: Cash/Payable
                'amount' => $cost,
                'narration' => "Capital Maintenance: {$ppe->name} - {$this->maintenance_description}",
                'action' => 'asset_maintenance'
            ];
            
            // Update PPE closing value
            $ppe->update([
                'closing_value' => $ppe->closing_value + $cost,
                'maintenance_cost_to_date' => $ppe->maintenance_cost_to_date + $cost
            ]);
            
        } else {
            // Regular maintenance - expense
            $maintenanceExpenseAccount = $institution->maintenance_expense_account ?? 
                                        $institution->repair_maintenance_account ?? 
                                        '0101150010'; // Default maintenance expense account
            
            $transactionData = [
                'first_account' => $maintenanceExpenseAccount, // Debit: Maintenance Expense
                'second_account' => $this->other_account_id ?? $institution->cash_account ?? '0101100010001010', // Credit: Cash/Payable
                'amount' => $cost,
                'narration' => "Maintenance Expense: {$ppe->name} - {$this->maintenance_description}",
                'action' => 'expense'
            ];
            
            // Update only maintenance cost tracking
            $ppe->update([
                'maintenance_cost_to_date' => $ppe->maintenance_cost_to_date + $cost
            ]);
        }
        
        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post maintenance transaction: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        Log::info('Maintenance accounting entry created', [
            'ppe_id' => $ppe->id,
            'cost' => $cost,
            'type' => $isCapital ? 'capital' : 'expense',
            'reference' => $result['reference'] ?? null
        ]);
    }

    // Transfer Management Methods
    public function openTransferForm($ppeId = null)
    {
        $this->showTransferForm = true;
        if ($ppeId) {
            $this->ppeId = $ppeId;
            // Load existing PPE data
            $ppe = PPE::find($ppeId);
            if ($ppe) {
                // Pre-populate if needed
            }
        }
        $this->transfer_date = now()->format('Y-m-d');
    }
    
    public function initiateTransfer($ppeId)
    {
        $this->showTransferForm = true;
        $this->ppeId = $ppeId;
        $this->transfer_date = now()->format('Y-m-d');
    }

    public function saveTransfer()
    {
        $this->validate([
            'transfer_to_location' => 'required|string',
            'transfer_reason' => 'required|string',
            'transfer_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            
            $lifecycleService = new PpeLifecycleService();
            $ppe = PPE::find($this->ppeId);
            
            $transfer = $lifecycleService->transferAsset($ppe, [
                'to_location' => $this->transfer_to_location,
                'to_department_id' => $this->transfer_to_department,
                'to_custodian_id' => $this->transfer_to_custodian,
                'transfer_date' => $this->transfer_date,
                'reason' => $this->transfer_reason,
                'notes' => $this->transfer_notes,
                'requires_approval' => false, // Process immediately if no approval needed
            ]);
            
            // Log the transfer for audit purposes
            Log::info('PPE Transfer initiated', [
                'ppe_id' => $ppe->id,
                'from_location' => $ppe->location,
                'to_location' => $this->transfer_to_location,
                'transfer_id' => $transfer->id
            ]);
            
            DB::commit();
            
            $this->resetTransferForm();
            $this->emit('showNotification', 'Transfer completed successfully', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PPE Transfer failed', ['error' => $e->getMessage()]);
            $this->emit('showNotification', 'Failed to transfer asset: ' . $e->getMessage(), 'error');
        }
    }

    public function approveTransfer($transferId)
    {
        $transfer = PpeTransfer::find($transferId);
        $transfer->approve(auth()->user()->name);
        $this->emit('showNotification', 'Transfer approved successfully', 'success');
    }

    // Insurance Management Methods
    public function openInsuranceForm($ppeId = null)
    {
        Log::info('PPE Management - openInsuranceForm called', [
            'ppeId' => $ppeId,
            'showInsuranceForm_before' => $this->showInsuranceForm,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String()
        ]);
        
        $this->showInsuranceForm = true;
        $this->insurance_start_date = now()->format('Y-m-d');
        $this->insurance_end_date = now()->addYear()->format('Y-m-d');
        $this->coverage_type = 'comprehensive';
        
        if ($ppeId) {
            $this->ppeId = $ppeId;
            // Load existing PPE data
            $ppe = PPE::find($ppeId);
            if ($ppe) {
                $this->insured_value = $ppe->closing_value ?: $ppe->purchase_price;
                Log::info('PPE Management - Insurance form PPE loaded', [
                    'ppe_name' => $ppe->name,
                    'ppe_id' => $ppeId,
                    'insured_value' => $this->insured_value
                ]);
            } else {
                Log::warning('PPE Management - PPE not found for insurance', [
                    'ppeId' => $ppeId
                ]);
            }
        }
        
        Log::info('PPE Management - Insurance form opened', [
            'showInsuranceForm_after' => $this->showInsuranceForm,
            'start_date' => $this->insurance_start_date,
            'end_date' => $this->insurance_end_date,
            'coverage_type' => $this->coverage_type
        ]);
    }
    
    public function addInsurance($ppeId)
    {
        $this->showInsuranceForm = true;
        $this->ppeId = $ppeId;
        $this->insurance_start_date = now()->format('Y-m-d');
        $this->insurance_end_date = now()->addYear()->format('Y-m-d');
    }

    public function saveInsurance()
    {
        Log::info('PPE Management - saveInsurance called', [
            'ppeId' => $this->ppeId,
            'policy_number' => $this->policy_number,
            'insurance_company' => $this->insurance_company,
            'premium_amount' => $this->premium_amount,
            'start_date' => $this->insurance_start_date,
            'end_date' => $this->insurance_end_date,
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            $this->validate([
                'policy_number' => 'required|string',
                'insurance_company' => 'required|string',
                'premium_amount' => 'required|numeric|min:0',
                'insurance_end_date' => 'required|date|after:insurance_start_date',
            ]);
            
            Log::info('PPE Management - Insurance validation passed', [
                'ppeId' => $this->ppeId,
                'policy_number' => $this->policy_number
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('PPE Management - Insurance validation failed', [
                'errors' => $e->errors(),
                'ppeId' => $this->ppeId
            ]);
            throw $e;
        }

        try {
            // Pre-check account balance
            Log::info('PPE Management - Checking account balance', [
                'other_account_id' => $this->other_account_id
            ]);
            
            $institution = DB::table('institutions')->where('id', 1)->first();
            $cashAccount = $this->other_account_id ?? 
                          $institution->cash_account ?? 
                          '0101100010001010';
            
            Log::info('PPE Management - Using cash account', [
                'cash_account' => $cashAccount,
                'premium_amount' => $this->premium_amount
            ]);
            
            $account = DB::table('accounts')
                ->where('account_number', $cashAccount)
                ->first();
            
            if ($account && $account->balance < $this->premium_amount) {
                Log::warning('PPE Management - Insufficient balance', [
                    'account' => $cashAccount,
                    'account_name' => $account->account_name,
                    'available' => $account->balance,
                    'required' => $this->premium_amount
                ]);
                
                $this->emit('showNotification', 
                    "Insufficient balance in account {$account->account_name}. Available: " . 
                    number_format($account->balance, 2) . ", Required: " . 
                    number_format($this->premium_amount, 2), 
                    'error'
                );
                return;
            }
            
            Log::info('PPE Management - Balance check passed', [
                'account' => $cashAccount,
                'available' => $account->balance ?? 'N/A',
                'required' => $this->premium_amount
            ]);
            
            DB::beginTransaction();
            Log::info('PPE Management - Transaction started');
            
            $lifecycleService = new PpeLifecycleService();
            $ppe = PPE::find($this->ppeId);
            
            if (!$ppe) {
                Log::error('PPE Management - PPE not found', ['ppeId' => $this->ppeId]);
                throw new \Exception("PPE with ID {$this->ppeId} not found");
            }
            
            Log::info('PPE Management - Creating insurance policy', [
                'ppe_name' => $ppe->name,
                'policy_data' => [
                    'policy_number' => $this->policy_number,
                    'insurance_company' => $this->insurance_company,
                    'premium_amount' => $this->premium_amount
                ]
            ]);
            
            $insurance = $lifecycleService->createInsurancePolicy($ppe, [
                'policy_number' => $this->policy_number,
                'insurance_company' => $this->insurance_company,
                'coverage_type' => $this->coverage_type,
                'insured_value' => $this->insured_value ?? $ppe->closing_value,
                'premium_amount' => $this->premium_amount,
                'start_date' => $this->insurance_start_date,
                'end_date' => $this->insurance_end_date,
                'deductible' => $this->deductible,
                'coverage_details' => $this->coverage_details,
                'agent_name' => $this->agent_name,
                'agent_contact' => $this->agent_contact,
            ]);
            
            Log::info('PPE Management - Insurance policy created', [
                'insurance_id' => $insurance->id ?? 'N/A'
            ]);
            
            // Create accounting entries for insurance premium
            if ($this->premium_amount > 0) {
                Log::info('PPE Management - Creating accounting entries', [
                    'premium_amount' => $this->premium_amount
                ]);
                $this->createInsuranceAccountingEntry($ppe, $this->premium_amount);
                Log::info('PPE Management - Accounting entries created');
            }
            
            DB::commit();
            Log::info('PPE Management - Transaction committed successfully');
            
            $this->resetInsuranceForm();
            $this->emit('showNotification', 'Insurance policy added with accounting entries', 'success');
            
            Log::info('PPE Management - Insurance save completed successfully', [
                'ppeId' => $this->ppeId,
                'policy_number' => $this->policy_number
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PPE Management - Insurance policy creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ppeId' => $this->ppeId,
                'policy_number' => $this->policy_number
            ]);
            $this->emit('showNotification', 'Failed to add insurance: ' . $e->getMessage(), 'error');
        }
    }
    
    private function createInsuranceAccountingEntry($ppe, $premiumAmount)
    {
        $transactionService = new TransactionPostingService();
        $institution = DB::table('institutions')->where('id', 1)->first();
        
        // Insurance premium is typically a prepaid expense (asset) that will be expensed over the policy period
        // First try to find a specific prepaid insurance child account
        $prepaidInsuranceAccount = null;
        
        // Check if there's a specific prepaid insurance account under prepaid expenses
        if ($institution->prepaid_expenses_account) {
            $insuranceAccount = DB::table('accounts')
                ->where('parent_account_number', $institution->prepaid_expenses_account)
                ->where('account_name', 'LIKE', '%INSURANCE%')
                ->where('account_level', '>=', 3)
                ->first();
            
            if ($insuranceAccount) {
                $prepaidInsuranceAccount = $insuranceAccount->account_number;
                Log::info('PPE Management - Using prepaid insurance account', [
                    'account' => $prepaidInsuranceAccount,
                    'name' => $insuranceAccount->account_name
                ]);
            }
        }
        
        // Fallback to the known prepaid insurance account
        if (!$prepaidInsuranceAccount) {
            $prepaidInsuranceAccount = '0101100018001810'; // Prepaid Insurance (Level 3 account)
            Log::info('PPE Management - Using default prepaid insurance account', [
                'account' => $prepaidInsuranceAccount
            ]);
        }
        
        $cashAccount = $this->other_account_id ?? 
                      $institution->cash_account ?? 
                      '0101100010001010';
        
        // Entry 1: Record prepaid insurance
        $prepaidEntry = [
            'first_account' => $prepaidInsuranceAccount, // Debit: Prepaid Insurance (Asset)
            'second_account' => $cashAccount, // Credit: Cash/Bank
            'amount' => $premiumAmount,
            'narration' => "Insurance Premium Payment: {$ppe->name} - Policy #{$this->policy_number}",
            'action' => 'prepaid_expense'
        ];
        
        $result = $transactionService->postTransaction($prepaidEntry);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post insurance premium transaction: ' . ($result['message'] ?? 'Unknown error'));
        }
        
        // Calculate monthly insurance expense for amortization
        $startDate = Carbon::parse($this->insurance_start_date);
        $endDate = Carbon::parse($this->insurance_end_date);
        $monthsDiff = $startDate->diffInMonths($endDate) ?: 1;
        $monthlyExpense = $premiumAmount / $monthsDiff;
        
        Log::info('Insurance accounting entry created', [
            'ppe_id' => $ppe->id,
            'premium_amount' => $premiumAmount,
            'policy_number' => $this->policy_number,
            'monthly_expense' => $monthlyExpense,
            'policy_months' => $monthsDiff,
            'reference' => $result['reference'] ?? null
        ]);
        
        // Note: Monthly amortization should be handled by a scheduled job
        // that moves from Prepaid Insurance to Insurance Expense each month
    }

    public function renewInsurance($insuranceId)
    {
        $insurance = PpeInsurance::find($insuranceId);
        $insurance->renew(now()->addYear());
        $this->emit('showNotification', 'Insurance renewed successfully', 'success');
    }

    // Revaluation Management Methods
    public function openRevaluationForm($ppeId = null)
    {
        $this->showRevaluationForm = true;
        $this->revaluation_date = now()->format('Y-m-d');
        $this->valuation_method = 'market_value';
        
        if ($ppeId) {
            $this->ppeId = $ppeId;
            // Load existing PPE data
            $ppe = PPE::find($ppeId);
            if ($ppe) {
                $this->new_value = $ppe->closing_value ?: $ppe->purchase_price;
            }
        }
    }
    
    public function initiateRevaluation($ppeId)
    {
        $this->showRevaluationForm = true;
        $this->ppeId = $ppeId;
        $this->revaluation_date = now()->format('Y-m-d');
    }

    public function saveRevaluation()
    {
        $this->validate([
            'new_value' => 'required|numeric|min:0',
            'revaluation_reason' => 'required|string',
            'revaluation_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();
            
            $lifecycleService = new PpeLifecycleService();
            $ppe = PPE::find($this->ppeId);
            
            $revaluation = $lifecycleService->revalueAsset($ppe, [
                'new_value' => $this->new_value,
                'revaluation_date' => $this->revaluation_date,
                'reason' => $this->revaluation_reason,
                'performed_by' => auth()->user()->name,
                'valuation_method' => $this->valuation_method,
                'supporting_documents' => $this->supporting_documents,
                'requires_approval' => false, // Process immediately
            ]);
            
            // Create accounting entries for revaluation
            $this->createRevaluationAccountingEntry($ppe, $revaluation);
            
            DB::commit();
            
            $this->resetRevaluationForm();
            $this->emit('showNotification', 'Revaluation completed with accounting entries', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revaluation failed', ['error' => $e->getMessage()]);
            $this->emit('showNotification', 'Failed to revalue asset: ' . $e->getMessage(), 'error');
        }
    }
    
    private function createRevaluationAccountingEntry($ppe, $revaluation)
    {
        $transactionService = new TransactionPostingService();
        $institution = DB::table('institutions')->where('id', 1)->first();
        
        $oldValue = $revaluation->old_value;
        $newValue = $revaluation->new_value;
        $revaluationAmount = $newValue - $oldValue;
        
        if (abs($revaluationAmount) < 0.01) {
            return; // No significant change
        }
        
        // Get the proper PPE account (level 3)
        $ppeAccount = $ppe->account_number;
        
        // If the PPE account is a parent account (level 2), find appropriate child account
        $account = DB::table('accounts')->where('account_number', $ppeAccount)->first();
        if ($account && $account->account_level < 3) {
            // Try to find a general PPE child account or use Land and Buildings as default
            $childAccount = DB::table('accounts')
                ->where('parent_account_number', $ppeAccount)
                ->where('account_level', '3')
                ->orderBy('account_number')
                ->first();
            
            if ($childAccount) {
                $ppeAccount = $childAccount->account_number;
                Log::info('PPE Management - Using child PPE account for revaluation', [
                    'original' => $ppe->account_number,
                    'child' => $ppeAccount,
                    'name' => $childAccount->account_name
                ]);
            } else {
                // Default to Land and Buildings if no child found
                $ppeAccount = '0101100016001610';
                Log::info('PPE Management - Using default PPE account for revaluation', [
                    'account' => $ppeAccount
                ]);
            }
        }
        
        if ($revaluationAmount > 0) {
            // Asset appreciation (increase in value)
            // Debit: PPE Asset Account
            // Credit: Revaluation Surplus (Equity)
            
            // Use the proper Property Revaluation Reserve account (level 3)
            $revaluationSurplusAccount = $institution->revaluation_surplus_account ?? 
                                        '0101300034003410'; // Property Revaluation Reserve (Level 3)
            
            $appreciationEntry = [
                'first_account' => $ppeAccount, // Debit: PPE Asset (Level 3)
                'second_account' => $revaluationSurplusAccount, // Credit: Revaluation Surplus (Level 3)
                'amount' => abs($revaluationAmount),
                'narration' => "PPE Revaluation (Appreciation): {$ppe->name}",
                'action' => 'ppe_revaluation'
            ];
            
            $result = $transactionService->postTransaction($appreciationEntry);
            
        } else {
            // Asset impairment (decrease in value)
            // Debit: Impairment Loss (Expense) or Revaluation Surplus (if exists)
            // Credit: PPE Asset Account or Accumulated Depreciation
            
            // First check if there's existing revaluation surplus for this asset
            $existingSurplus = DB::table('ppe_revaluations')
                ->where('ppe_id', $ppe->id)
                ->where('revaluation_type', 'appreciation')
                ->sum('revaluation_amount');
            
            if ($existingSurplus > 0) {
                // Use revaluation surplus first
                $amountAgainstSurplus = min(abs($revaluationAmount), $existingSurplus);
                $amountAgainstPL = abs($revaluationAmount) - $amountAgainstSurplus;
                
                if ($amountAgainstSurplus > 0) {
                    // Use the proper Property Revaluation Reserve account (level 3)
                    $revaluationSurplusAccount = $institution->revaluation_surplus_account ?? 
                                                '0101300034003410'; // Property Revaluation Reserve (Level 3)
                    
                    $surplusEntry = [
                        'first_account' => $revaluationSurplusAccount, // Debit: Revaluation Surplus
                        'second_account' => $ppeAccount, // Credit: PPE Asset (Level 3)
                        'amount' => $amountAgainstSurplus,
                        'narration' => "PPE Revaluation (Impairment against surplus): {$ppe->name}",
                        'action' => 'ppe_revaluation'
                    ];
                    
                    $transactionService->postTransaction($surplusEntry);
                }
                
                if ($amountAgainstPL > 0) {
                    // Find a proper impairment loss account (level 3)
                    $impairmentLossAccount = $institution->impairment_loss_account ?? 
                                           $institution->other_expenses_account ?? 
                                           '0101150020';
                    
                    // Ensure we have a level 3 account
                    $lossAccount = DB::table('accounts')->where('account_number', $impairmentLossAccount)->first();
                    if ($lossAccount && $lossAccount->account_level < 3) {
                        // Find a child account
                        $childLoss = DB::table('accounts')
                            ->where('parent_account_number', $impairmentLossAccount)
                            ->where('account_level', '3')
                            ->first();
                        if ($childLoss) {
                            $impairmentLossAccount = $childLoss->account_number;
                        }
                    }
                    
                    $lossEntry = [
                        'first_account' => $impairmentLossAccount, // Debit: Impairment Loss
                        'second_account' => $ppeAccount, // Credit: PPE Asset (Level 3)
                        'amount' => $amountAgainstPL,
                        'narration' => "Asset Impairment Loss: {$ppe->name}",
                        'action' => 'ppe_revaluation'
                    ];
                    
                    $transactionService->postTransaction($lossEntry);
                }
                
            } else {
                // No surplus, entire impairment goes to P&L
                $impairmentLossAccount = $institution->impairment_loss_account ?? 
                                       $institution->other_expenses_account ?? 
                                       '0101150020';
                
                // Ensure we have a level 3 account
                $lossAccount = DB::table('accounts')->where('account_number', $impairmentLossAccount)->first();
                if ($lossAccount && $lossAccount->account_level < 3) {
                    // Find a child account
                    $childLoss = DB::table('accounts')
                        ->where('parent_account_number', $impairmentLossAccount)
                        ->where('account_level', '3')
                        ->first();
                    if ($childLoss) {
                        $impairmentLossAccount = $childLoss->account_number;
                    }
                }
                
                $impairmentEntry = [
                    'first_account' => $impairmentLossAccount, // Debit: Impairment Loss
                    'second_account' => $ppeAccount, // Credit: PPE Asset (Level 3)
                    'amount' => abs($revaluationAmount),
                    'narration' => "Asset Impairment: {$ppe->name}",
                    'action' => 'ppe_revaluation'
                ];
                
                $result = $transactionService->postTransaction($impairmentEntry);
            }
        }
        
        // Update PPE closing value
        $ppe->update([
            'closing_value' => $newValue,
            'last_valuation_date' => $this->revaluation_date,
            'valuation_by' => auth()->user()->name
        ]);
        
        Log::info('Revaluation accounting entries created', [
            'ppe_id' => $ppe->id,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'revaluation_amount' => $revaluationAmount,
            'type' => $revaluationAmount > 0 ? 'appreciation' : 'impairment'
        ]);
    }

    // Import Pre-existing Assets
    public function importPreExistingAsset()
    {
        $this->importMode = true;
        $this->selectedMenuItem = 2;
        // Pre-fill with existing asset data
        $this->purchase_date = now()->subYears(2)->format('Y-m-d'); // Assume 2 years old
        $this->accumulated_depreciation = 0; // Will be calculated
    }

    public function processImport()
    {
        // This method can be extended to handle bulk imports from CSV/Excel
        $this->validate();
        
        // Calculate initial depreciation for pre-existing asset
        if ($this->purchase_date < now()->format('Y-m-d')) {
            $yearsInUse = Carbon::parse($this->purchase_date)->diffInYears(now());
            $this->accumulated_depreciation = ($this->purchase_price - $this->salvage_value) / $this->useful_life * $yearsInUse;
        }
        
        $this->store();
        $this->importMode = false;
        $this->emit('showNotification', 'Pre-existing asset imported successfully', 'success');
    }

    // Reset methods for forms
    public function resetMaintenanceForm()
    {
        $this->showMaintenanceForm = false;
        $this->maintenanceId = null;
        $this->maintenance_type = 'preventive';
        $this->maintenance_date = '';
        $this->maintenance_description = '';
        $this->maintenance_vendor = '';
        $this->maintenance_cost = 0;
        $this->maintenance_parts_replaced = '';
        $this->next_maintenance_date = '';
    }

    public function resetTransferForm()
    {
        $this->showTransferForm = false;
        $this->transferId = null;
        $this->transfer_to_location = '';
        $this->transfer_to_department = '';
        $this->transfer_to_custodian = '';
        $this->transfer_date = '';
        $this->transfer_reason = '';
        $this->transfer_notes = '';
    }

    public function resetInsuranceForm()
    {
        $this->showInsuranceForm = false;
        $this->insuranceId = null;
        $this->policy_number = '';
        $this->insurance_company = '';
        $this->coverage_type = 'comprehensive';
        $this->insured_value = '';
        $this->premium_amount = '';
        $this->insurance_start_date = '';
        $this->insurance_end_date = '';
        $this->deductible = '';
        $this->coverage_details = '';
        $this->agent_name = '';
        $this->agent_contact = '';
    }

    public function resetRevaluationForm()
    {
        $this->showRevaluationForm = false;
        $this->revaluationId = null;
        $this->revaluation_date = '';
        $this->new_value = '';
        $this->revaluation_reason = '';
        $this->valuation_method = '';
        $this->supporting_documents = '';
    }

    // Computed Properties for new features
    public function getMaintenanceScheduleProperty()
    {
        return PpeMaintenanceRecord::with('ppe')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('maintenance_date')
            ->limit(10)
            ->get();
    }

    public function getMaintenanceDueCountProperty()
    {
        return PPE::where('next_maintenance_date', '<=', now()->endOfMonth())
            ->where('next_maintenance_date', '>=', now()->startOfMonth())
            ->count();
    }

    public function getMaintenanceOverdueCountProperty()
    {
        return PPE::where('next_maintenance_date', '<', now())->count();
    }

    public function getMaintenanceCompletedCountProperty()
    {
        return PpeMaintenanceRecord::where('status', 'completed')
            ->whereMonth('maintenance_date', now()->month)
            ->count();
    }

    public function getMaintenanceCostMTDProperty()
    {
        return PpeMaintenanceRecord::where('status', 'completed')
            ->whereMonth('maintenance_date', now()->month)
            ->sum('cost');
    }

    public function getTransfersProperty()
    {
        return PpeTransfer::with('ppe')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getInsurancePoliciesProperty()
    {
        return PpeInsurance::with('ppe')
            ->where('status', 'active')
            ->orderBy('end_date')
            ->get();
    }

    public function getActivePoliciesCountProperty()
    {
        return PpeInsurance::active()->count();
    }

    public function getExpiringPoliciesCountProperty()
    {
        return PpeInsurance::expiring(30)->count();
    }

    public function getTotalAnnualPremiumProperty()
    {
        return PpeInsurance::active()->sum('premium_amount');
    }

    public function getRevaluationsProperty()
    {
        return PpeRevaluation::with('ppe')
            ->orderBy('revaluation_date', 'desc')
            ->limit(10)
            ->get();
    }
    
    // Report generation helper methods
    private function generateAssetRegisterReport()
    {
        // Get date range
        $startDate = now()->subDays($this->reportDateRange == '30' ? 30 : ($this->reportDateRange == '60' ? 60 : 90));
        $endDate = now();
        
        // Query PPE assets (removed non-existent relationships)
        $assets = PPE::when($this->reportStatus && $this->reportStatus != 'all', function($q) {
                return $q->where('status', $this->reportStatus);
            })
            ->when($this->reportCategory && $this->reportCategory != 'all', function($q) {
                return $q->where('categoryx', $this->reportCategory);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('id', 'desc')
            ->get();
        
        // Generate CSV content
        $csv = "Asset Code,Name,Category,Purchase Date,Purchase Price,Depreciation,Book Value,Status,Location\n";
        
        foreach ($assets as $asset) {
            $csv .= sprintf(
                '"%s","%s","%s","%s",%.2f,%.2f,%.2f,"%s","%s"' . "\n",
                $asset->asset_code ?? 'N/A',
                $asset->name,
                $asset->categoryx ?? 'N/A',
                $asset->purchase_date,
                $asset->purchase_price,
                $asset->accumulated_depreciation ?? 0,
                $asset->closing_value ?? ($asset->purchase_price - ($asset->accumulated_depreciation ?? 0)),
                $asset->status,
                $asset->location ?? 'N/A'
            );
        }
        
        // Return download response
        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            'asset_register_' . date('Y-m-d') . '.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
    
    private function generateSummaryReport()
    {
        $assets = PPE::all();
        
        $summary = [
            'total_assets' => $assets->count(),
            'total_value' => $assets->sum('purchase_price'),
            'total_depreciation' => $assets->sum('accumulated_depreciation'),
            'net_book_value' => $assets->sum('closing_value'),
            'active_assets' => $assets->where('status', 'active')->count(),
            'disposed_assets' => $assets->where('status', 'disposed')->count(),
        ];
        
        $csv = "PPE Summary Report - Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $csv .= "Metric,Value\n";
        $csv .= "Total Assets," . $summary['total_assets'] . "\n";
        $csv .= "Total Purchase Value," . number_format($summary['total_value'], 2) . "\n";
        $csv .= "Total Depreciation," . number_format($summary['total_depreciation'], 2) . "\n";
        $csv .= "Net Book Value," . number_format($summary['net_book_value'], 2) . "\n";
        $csv .= "Active Assets," . $summary['active_assets'] . "\n";
        $csv .= "Disposed Assets," . $summary['disposed_assets'] . "\n";
        
        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            'ppe_summary_' . date('Y-m-d') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
    
    private function generateDisposalReport()
    {
        $disposals = PPE::whereIn('status', ['disposed', 'pending_disposal'])
            ->orderBy('disposal_date', 'desc')
            ->get();
        
        $csv = "Disposal Report - Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $csv .= "Asset Code,Asset Name,Purchase Price,Book Value,Disposal Date,Disposal Method,Proceeds,Gain/Loss,Status\n";
        
        foreach ($disposals as $asset) {
            $bookValue = $asset->purchase_price - ($asset->accumulated_depreciation ?? 0);
            $gainLoss = ($asset->disposal_proceeds ?? 0) - $bookValue;
            
            $csv .= sprintf(
                '"%s","%s",%.2f,%.2f,"%s","%s",%.2f,%.2f,"%s"' . "\n",
                $asset->asset_code ?? 'N/A',
                $asset->name,
                $asset->purchase_price,
                $bookValue,
                $asset->disposal_date ?? 'Pending',
                $asset->disposal_method ?? 'N/A',
                $asset->disposal_proceeds ?? 0,
                $gainLoss,
                $asset->status
            );
        }
        
        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            'disposal_report_' . date('Y-m-d') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
    
    private function generateDepreciationReport()
    {
        $assets = PPE::where('status', 'active')->get();
        
        $csv = "Depreciation Schedule - Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $csv .= "Asset Code,Asset Name,Purchase Price,Purchase Date,Useful Life,Salvage Value,Monthly Depreciation,Accumulated Depreciation,Book Value\n";
        
        foreach ($assets as $asset) {
            $monthlyDepreciation = ($asset->purchase_price - $asset->salvage_value) / ($asset->useful_life * 12);
            
            $csv .= sprintf(
                '"%s","%s",%.2f,"%s",%d,%.2f,%.2f,%.2f,%.2f' . "\n",
                $asset->asset_code ?? 'N/A',
                $asset->name,
                $asset->purchase_price,
                $asset->purchase_date,
                $asset->useful_life,
                $asset->salvage_value,
                $monthlyDepreciation,
                $asset->accumulated_depreciation ?? 0,
                $asset->closing_value ?? ($asset->purchase_price - ($asset->accumulated_depreciation ?? 0))
            );
        }
        
        return response()->streamDownload(
            function () use ($csv) {
                echo $csv;
            },
            'depreciation_schedule_' . date('Y-m-d') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
    
    private function generateValuationReport()
    {
        $this->emit('showNotification', 'Generating Valuation Report...', 'info');
        // Similar implementation as above
        return $this->generateSummaryReport(); // For now, return summary
    }
    
    private function generateMaintenanceReport()
    {
        $this->emit('showNotification', 'Generating Maintenance Report...', 'info');
        // Similar implementation as above
        return $this->generateSummaryReport(); // For now, return summary
    }
    
    public function exportReport()
    {
        $this->emit('showNotification', 'Exporting report...', 'info');
        // TODO: Implement report export functionality
    }

    public function render()
    {
        // Get bank accounts for other account selection
        $otherAccounts = DB::table('bank_accounts')
            ->select('internal_mirror_account_number', 'bank_name', 'account_number')
            ->where('status', 'ACTIVE')
            ->orderBy('bank_name')
            ->get();
            
        // Get departments for dropdowns
        $departments = DB::table('departments')
            ->select('id', 'department_name as name')
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('department_name')
            ->get();
            
        // Get users for custodian selection
        $users = DB::table('users')
            ->select('id', 'name')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
            
        return view('livewire.accounting.ppe-management', [
            'ppes' => $this->getPpes(),
            'otherAccounts' => $otherAccounts,
            'departments' => $departments,
            'users' => $users,
        ]);
    }
}
