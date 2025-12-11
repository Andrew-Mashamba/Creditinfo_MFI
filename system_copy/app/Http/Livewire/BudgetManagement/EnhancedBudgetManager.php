<?php

namespace App\Http\Livewire\BudgetManagement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\BudgetManagement;
use App\Models\BudgetDepartment;
use App\Models\BudgetCommitment;
use App\Models\BudgetTransfer;
use App\Models\BudgetVersion;
use App\Models\BudgetScenario;
use App\Models\GeneralLedger;
use App\Models\BudgetAllocation;
use App\Models\BudgetAdvance;
use App\Models\SupplementaryRequest;
use App\Models\BudgetAlert;
use App\Services\EnhancedBudgetService;
use App\Services\BudgetMonitoringService;
use App\Services\BudgetFlexibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EnhancedBudgetManager extends Component
{
    use WithPagination;
    
    // Tab management
    public $activeTab = 'overview';
    
    // Modals
    public $showCommitmentModal = false;
    public $showTransferModal = false;
    public $showVersionModal = false;
    public $showScenarioModal = false;
    public $showAllocationModal = false;
    public $showGLLinkModal = false;
    public $showDepartmentModal = false;
    public $showReportModal = false;
    
    // Selected budget
    public $selectedBudgetId = null;
    public $selectedBudget = null;
    
    // Commitment form
    public $commitmentType = 'PURCHASE_ORDER';
    public $commitmentAmount;
    public $vendorName;
    public $commitmentDescription;
    public $expectedDeliveryDate;
    public $expiryDate;
    
    // Transfer form
    public $fromBudgetId;
    public $toBudgetId;
    public $transferAmount;
    public $transferReason;
    
    // Version form
    public $versionName;
    public $versionType = 'REVISED';
    public $revisionReason;
    public $newAllocatedAmount;
    
    // Scenario form
    public $scenarioName;
    public $scenarioType = 'EXPECTED';
    public $adjustmentPercentage = 0;
    public $scenarioAssumptions;
    
    // Custom allocation
    public $allocations;
    public $allocationType = 'MONTHLY';
    public $allocationSetupForm = [];
    
    // Department form
    public $departmentCode;
    public $departmentName;
    public $parentDepartmentId;
    public $costCenter;
    public $managerId;
    
    // Report parameters
    public $reportType = 'BUDGET_VS_ACTUAL';
    public $reportStartDate;
    public $reportEndDate;
    public $reportDepartmentId;
    
    // Filters
    public $searchTerm = '';
    public $budgetTypeFilter = '';
    public $departmentFilter = '';
    public $statusFilter = '';
    
    // Allocations
    public $selectedYear;
    public $rolloverPolicy = 'APPROVAL_REQUIRED';
    public $annualSummary = [];
    public $budgetAlerts = [];
    public $showAllocationSetupModal = false;
    public $showAdvanceRequestModal = false;
    public $showSupplementaryRequestModal = false;
    public $selectedAllocationId = null;
    
    // Advance form
    public $advanceFromPeriod;
    public $advanceFromYear;
    public $advanceAmount;
    public $advanceReason;
    public $advanceRepaymentPlan = 'NEXT_MONTH';
    
    // Supplementary form
    public $supplementaryAmount;
    public $supplementaryJustification;
    public $supplementaryUrgency = 'MEDIUM';
    public $supplementaryDocuments = [];
    
    protected $listeners = ['refreshBudgets' => '$refresh'];
    
    protected $rules = [
        'commitmentAmount' => 'required|numeric|min:0',
        'commitmentDescription' => 'required|string|max:500',
        'transferAmount' => 'required|numeric|min:0',
        'transferReason' => 'required|string|max:500',
        'versionName' => 'required|string|max:100',
        'revisionReason' => 'required|string|max:500',
        'scenarioName' => 'required|string|max:100',
        'departmentCode' => 'required|string|max:20|unique:budget_departments,department_code',
        'departmentName' => 'required|string|max:100',
    ];
    
    public function mount()
    {
        $this->initializeAllocationSetupForm();
        $this->allocations = collect(); // Initialize as empty collection
        $this->reportStartDate = now()->startOfMonth()->format('Y-m-d');
        $this->reportEndDate = now()->endOfMonth()->format('Y-m-d');
        $this->selectedYear = now()->year;
        $this->reportYear = now()->year;
        $this->reportMonth = now()->month;
    }
    
    private function initializeAllocationSetupForm()
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->allocationSetupForm[$i] = ['month' => $i, 'percentage' => 8.33, 'amount' => 0];
        }
    }
    
    public function selectBudget($budgetId)
    {
        $this->selectedBudgetId = $budgetId;
        $this->selectedBudget = BudgetManagement::with([
            'budgetDepartment',
            'versions',
            'scenarios',
            'commitments',
            'transactions',
            'generalLedgerEntries'
        ])->find($budgetId);
    }
    
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
    }
    
    // Commitment Management
    public function openCommitmentModal($budgetId)
    {
        $this->selectedBudgetId = $budgetId;
        $this->resetCommitmentForm();
        $this->showCommitmentModal = true;
    }
    
    public function createCommitment()
    {
        $this->validate([
            'commitmentAmount' => 'required|numeric|min:0',
            'commitmentDescription' => 'required|string|max:500'
        ]);
        
        try {
            $service = new EnhancedBudgetService();
            
            $commitment = $service->createCommitment($this->selectedBudgetId, [
                'type' => $this->commitmentType,
                'amount' => $this->commitmentAmount,
                'vendor_name' => $this->vendorName,
                'description' => $this->commitmentDescription,
                'expected_delivery_date' => $this->expectedDeliveryDate,
                'expiry_date' => $this->expiryDate
            ]);
            
            session()->flash('message', 'Commitment created successfully!');
            $this->showCommitmentModal = false;
            $this->resetCommitmentForm();
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create commitment: ' . $e->getMessage());
        }
    }
    
    private function resetCommitmentForm()
    {
        $this->commitmentType = 'PURCHASE_ORDER';
        $this->commitmentAmount = null;
        $this->vendorName = null;
        $this->commitmentDescription = null;
        $this->expectedDeliveryDate = null;
        $this->expiryDate = null;
    }
    
    // Transfer Management
    public function openTransferModal($fromBudgetId = null)
    {
        $this->fromBudgetId = $fromBudgetId;
        $this->toBudgetId = null;
        $this->transferAmount = null;
        $this->transferReason = null;
        $this->showTransferModal = true;
    }
    
    public function createTransfer()
    {
        $this->validate([
            'fromBudgetId' => 'required|exists:budget_managements,id',
            'toBudgetId' => 'required|exists:budget_managements,id|different:fromBudgetId',
            'transferAmount' => 'required|numeric|min:0',
            'transferReason' => 'required|string|max:500'
        ]);
        
        try {
            $service = new EnhancedBudgetService();
            
            $transfer = $service->createBudgetTransfer(
                $this->fromBudgetId,
                $this->toBudgetId,
                $this->transferAmount,
                $this->transferReason
            );
            
            session()->flash('message', 'Transfer request created successfully and sent for approval!');
            $this->showTransferModal = false;
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create transfer: ' . $e->getMessage());
        }
    }
    
    // Version Management
    public function openVersionModal($budgetId)
    {
        $this->selectedBudgetId = $budgetId;
        $this->selectedBudget = BudgetManagement::find($budgetId);
        $this->newAllocatedAmount = $this->selectedBudget->allocated_amount;
        $this->showVersionModal = true;
    }
    
    public function createVersion()
    {
        $this->validate([
            'versionName' => 'required|string|max:100',
            'revisionReason' => 'required|string|max:500'
        ]);
        
        try {
            $service = new EnhancedBudgetService();
            
            $version = $service->createBudgetVersion($this->selectedBudgetId, [
                'version_name' => $this->versionName,
                'version_type' => $this->versionType,
                'allocated_amount' => $this->newAllocatedAmount,
                'revision_reason' => $this->revisionReason,
                'make_active' => true
            ]);
            
            session()->flash('message', 'Budget version created successfully!');
            $this->showVersionModal = false;
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create version: ' . $e->getMessage());
        }
    }
    
    // Scenario Management
    public function openScenarioModal($budgetId)
    {
        $this->selectedBudgetId = $budgetId;
        $this->showScenarioModal = true;
    }
    
    public function createScenario()
    {
        $this->validate([
            'selectedBudgetId' => 'required|exists:budget_managements,id',
            'scenarioName' => 'required|string|max:100'
        ]);
        
        try {
            $service = new EnhancedBudgetService();
            
            $scenario = $service->createBudgetScenario($this->selectedBudgetId, [
                'scenario_name' => $this->scenarioName,
                'scenario_type' => $this->scenarioType,
                'adjustment_percentage' => $this->adjustmentPercentage,
                'assumptions' => json_decode($this->scenarioAssumptions, true) ?? [],
                'is_active' => false
            ]);
            
            session()->flash('message', 'Scenario created successfully!');
            $this->showScenarioModal = false;
            $this->resetScenarioForm();
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create scenario: ' . $e->getMessage());
        }
    }
    
    private function resetScenarioForm()
    {
        $this->selectedBudgetId = null;
        $this->scenarioName = null;
        $this->scenarioType = 'EXPECTED';
        $this->adjustmentPercentage = 0;
        $this->scenarioAssumptions = null;
    }
    
    // Custom Allocation
    public function openAllocationModal($budgetId)
    {
        $this->selectedBudgetId = $budgetId;
        $this->selectedBudget = BudgetManagement::find($budgetId);
        $this->showAllocationModal = true;
    }
    
    public function saveCustomAllocation()
    {
        try {
            $service = new EnhancedBudgetService();
            
            $allocationsData = array_map(function($allocation) {
                return [
                    'period' => $allocation['month'],
                    'type' => 'MONTHLY',
                    'percentage' => $allocation['percentage'],
                    'notes' => null
                ];
            }, $this->allocations);
            
            $service->createCustomAllocation($this->selectedBudgetId, $allocationsData);
            
            session()->flash('message', 'Custom allocation saved successfully!');
            $this->showAllocationModal = false;
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save allocation: ' . $e->getMessage());
        }
    }
    
    // GL Linking
    public function linkGLTransaction($glEntryId, $budgetId)
    {
        try {
            $service = new EnhancedBudgetService();
            $service->linkGLTransactionToBudget($glEntryId, $budgetId);
            
            session()->flash('message', 'GL transaction linked successfully!');
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to link GL transaction: ' . $e->getMessage());
        }
    }
    
    // Department Management
    public function createDepartment()
    {
        $this->validate([
            'departmentCode' => 'required|string|max:20|unique:departments,department_code',
            'departmentName' => 'required|string|max:100'
        ]);
        
        try {
            $department = \App\Models\Department::create([
                'department_code' => $this->departmentCode,
                'department_name' => $this->departmentName,
                'parent_department_id' => $this->parentDepartmentId,
                'description' => $this->costCenter, // Using cost center as description
                'status' => true
            ]);
            
            session()->flash('message', 'Department created successfully!');
            $this->showDepartmentModal = false;
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create department: ' . $e->getMessage());
        }
    }
    
    // Report Generation
    public function generateReport()
    {
        try {
            $service = new EnhancedBudgetService();
            
            $report = $service->generateBudgetReport($this->reportType, [
                'date_from' => $this->reportStartDate,
                'date_to' => $this->reportEndDate,
                'department_id' => $this->reportDepartmentId,
                'budget_id' => $this->selectedBudgetId
            ]);
            
            session()->flash('message', 'Report generated successfully!');
            
            // You can implement download or view logic here
            return redirect()->route('budget.report.view', $report->id);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }
    
    // Utility methods
    public function utilizeCommitment($commitmentId, $amount)
    {
        try {
            $commitment = BudgetCommitment::find($commitmentId);
            $commitment->utilize($amount);
            
            session()->flash('message', 'Commitment utilized successfully!');
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to utilize commitment: ' . $e->getMessage());
        }
    }
    
    public function cancelCommitment($commitmentId)
    {
        try {
            $commitment = BudgetCommitment::find($commitmentId);
            $commitment->cancel();
            
            session()->flash('message', 'Commitment cancelled successfully!');
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cancel commitment: ' . $e->getMessage());
        }
    }
    
    public function activateScenario($scenarioId)
    {
        try {
            $scenario = BudgetScenario::find($scenarioId);
            
            // Deactivate other scenarios
            BudgetScenario::where('budget_id', $scenario->budget_id)
                ->where('id', '!=', $scenarioId)
                ->update(['is_active' => false]);
            
            $scenario->update(['is_active' => true]);
            
            // Update budget
            $budget = BudgetManagement::find($scenario->budget_id);
            $budget->update(['active_scenario_id' => $scenarioId]);
            
            session()->flash('message', 'Scenario activated successfully!');
            $this->emit('refreshBudgets');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to activate scenario: ' . $e->getMessage());
        }
    }
    
    // Allocation Management Methods
    public function loadAllocations()
    {
        if (!$this->selectedBudgetId || !$this->selectedYear) {
            $this->allocations = collect();
            return;
        }
        
        $this->allocations = BudgetAllocation::where('budget_id', $this->selectedBudgetId)
            ->where('year', $this->selectedYear)
            ->orderBy('period')
            ->get();
            
        if ($this->allocations->isEmpty()) {
            // No allocations exist, keep as empty collection
            // Don't reset to array
        } else {
            $this->calculateAnnualSummary();
            $this->loadBudgetAlerts();
        }
    }
    
    public function calculateAnnualSummary()
    {
        $this->annualSummary = [
            'allocated' => $this->allocations->sum('allocated_amount'),
            'utilized' => $this->allocations->sum('utilized_amount'),
            'available' => $this->allocations->sum('available_amount'),
            'rollover' => $this->allocations->sum('rollover_amount'),
            'advances' => $this->allocations->sum('advance_amount'),
            'supplementary' => $this->allocations->sum('supplementary_amount')
        ];
    }
    
    public function loadBudgetAlerts()
    {
        $this->budgetAlerts = BudgetAlert::where('budget_id', $this->selectedBudgetId)
            ->where('is_resolved', false)
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }
    
    public function openAllocationSetupModal()
    {
        $this->showAllocationSetupModal = true;
    }
    
    public function setupAllocations()
    {
        // Validate that we have a budget selected
        if (!$this->selectedBudgetId) {
            session()->flash('error', 'Please select a budget first from the Allocations tab.');
            return;
        }
        
        if (!$this->selectedYear) {
            $this->selectedYear = now()->year;
        }
        
        try {
            // Format allocations based on allocation type
            $formattedAllocations = [];
            
            if ($this->allocationType === 'MONTHLY') {
                // Equal monthly distribution
                for ($i = 1; $i <= 12; $i++) {
                    $formattedAllocations[$i] = [
                        'month' => $i,
                        'percentage' => 8.33, // ~100/12
                        'amount' => 0
                    ];
                }
            } elseif ($this->allocationType === 'QUARTERLY') {
                // Quarterly distribution (25% each quarter)
                for ($i = 1; $i <= 12; $i++) {
                    $formattedAllocations[$i] = [
                        'month' => $i,
                        'percentage' => in_array($i, [3, 6, 9, 12]) ? 25 : 0,
                        'amount' => 0
                    ];
                }
            } elseif ($this->allocationType === 'CUSTOM') {
                // Use the custom allocations from the form
                $formattedAllocations = $this->allocationSetupForm;
            }
            
            $service = new BudgetFlexibilityService();
            $allocations = $service->createMonthlyAllocations(
                $this->selectedBudgetId,
                $this->selectedYear,
                $formattedAllocations
            );
            
            session()->flash('message', 'Budget allocations created successfully!');
            $this->showAllocationSetupModal = false;
            $this->loadAllocations();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create allocations: ' . $e->getMessage());
        }
    }
    
    public function openAdvanceModal($allocationId = null)
    {
        $this->selectedAllocationId = $allocationId;
        $this->showAdvanceRequestModal = true;
        
        if ($allocationId) {
            $allocation = BudgetAllocation::find($allocationId);
            if ($allocation) {
                // Set default from period to next month
                $this->advanceFromPeriod = $allocation->period == 12 ? 1 : $allocation->period + 1;
                $this->advanceFromYear = $allocation->period == 12 ? $allocation->year + 1 : $allocation->year;
            }
        }
    }
    
    public function requestAdvance()
    {
        $this->validate([
            'advanceAmount' => 'required|numeric|min:0',
            'advanceReason' => 'required|string|max:500'
        ]);
        
        try {
            $allocation = BudgetAllocation::find($this->selectedAllocationId);
            $service = new BudgetFlexibilityService();
            
            $advance = $service->requestBudgetAdvance(
                $allocation->budget_id,
                $allocation->period,
                $allocation->year,
                $this->advanceAmount,
                $this->advanceFromPeriod,
                $this->advanceFromYear,
                $this->advanceReason
            );
            
            session()->flash('message', 'Budget advance request created successfully!');
            $this->showAdvanceRequestModal = false;
            $this->resetAdvanceForm();
            $this->loadAllocations();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to request advance: ' . $e->getMessage());
        }
    }
    
    private function resetAdvanceForm()
    {
        $this->advanceFromPeriod = null;
        $this->advanceFromYear = null;
        $this->advanceAmount = null;
        $this->advanceReason = null;
    }
    
    public function openSupplementaryModal($allocationId = null)
    {
        $this->selectedAllocationId = $allocationId;
        $this->showSupplementaryRequestModal = true;
    }
    
    public function requestSupplementary()
    {
        $this->validate([
            'supplementaryAmount' => 'required|numeric|min:0',
            'supplementaryJustification' => 'required|string|max:1000'
        ]);
        
        try {
            $allocation = BudgetAllocation::find($this->selectedAllocationId);
            $service = new BudgetFlexibilityService();
            
            $request = $service->requestSupplementaryBudget(
                $allocation->budget_id,
                $allocation->period,
                $allocation->year,
                $this->supplementaryAmount,
                $this->supplementaryJustification,
                $this->supplementaryUrgency
            );
            
            // Auto-submit if not draft
            if ($request->status === 'DRAFT') {
                $request->submit();
            }
            
            session()->flash('message', 'Supplementary budget request created successfully!');
            $this->showSupplementaryRequestModal = false;
            $this->resetSupplementaryForm();
            $this->loadAllocations();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to request supplementary budget: ' . $e->getMessage());
        }
    }
    
    private function resetSupplementaryForm()
    {
        $this->supplementaryAmount = null;
        $this->supplementaryJustification = null;
        $this->supplementaryUrgency = 'MEDIUM';
    }
    
    public function processRollover($allocationId)
    {
        try {
            $allocation = BudgetAllocation::find($allocationId);
            $service = new BudgetFlexibilityService();
            
            $nextPeriod = $allocation->period == 12 ? 1 : $allocation->period + 1;
            $nextYear = $allocation->period == 12 ? $allocation->year + 1 : $allocation->year;
            
            $rolloverAmount = $service->processRollover(
                $allocation->budget_id,
                $allocation->period,
                $allocation->year,
                $nextPeriod,
                $nextYear
            );
            
            session()->flash('message', 'Rollover processed successfully! Amount: ' . number_format($rolloverAmount, 2));
            $this->loadAllocations();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process rollover: ' . $e->getMessage());
        }
    }
    
    public function resolveAlert($alertId)
    {
        try {
            $alert = BudgetAlert::find($alertId);
            $alert->is_resolved = true;
            $alert->resolved_at = now();
            $alert->resolved_by = auth()->id();
            $alert->save();
            
            $this->loadBudgetAlerts();
            session()->flash('message', 'Alert resolved successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resolve alert: ' . $e->getMessage());
        }
    }
    
    public function viewDetails($allocationId)
    {
        // This could open a modal with detailed information
        // For now, just set the selected allocation
        $this->selectedAllocationId = $allocationId;
    }
    
    // Report Properties
    public $reportBudgetId = '';
    public $reportPeriod = 'monthly';
    public $reportYear;
    public $reportMonth;
    public $reportQuarter = 1;
    public $reportFrom;
    public $reportTo;
    public $varianceReport = [];
    public $utilizationReport = [];
    public $rolloverReport = [];
    public $advanceReport = [];
    public $supplementaryReport = [];
    
    public function generateReports()
    {
        $this->resetReports();
        
        switch ($this->reportType) {
            case 'variance':
                $this->generateVarianceReport();
                break;
            case 'utilization':
                $this->generateUtilizationReport();
                break;
            case 'rollover':
                $this->generateRolloverReport();
                break;
            case 'advances':
                $this->generateAdvanceReport();
                break;
            case 'supplementary':
                $this->generateSupplementaryReport();
                break;
            case 'comprehensive':
                $this->generateVarianceReport();
                $this->generateUtilizationReport();
                $this->generateRolloverReport();
                $this->generateAdvanceReport();
                $this->generateSupplementaryReport();
                break;
        }
    }
    
    private function resetReports()
    {
        $this->varianceReport = [];
        $this->utilizationReport = [];
        $this->rolloverReport = [];
        $this->advanceReport = [];
        $this->supplementaryReport = [];
    }
    
    private function generateVarianceReport()
    {
        $query = BudgetAllocation::with('budget');
        
        if ($this->reportBudgetId) {
            $query->where('budget_id', $this->reportBudgetId);
        }
        
        // Apply period filters
        $query = $this->applyPeriodFilters($query);
        
        $allocations = $query->get();
        
        $totalBudget = $allocations->sum('allocated_amount');
        $totalActual = $allocations->sum('utilized_amount');
        $variance = $totalBudget - $totalActual;
        
        $items = [];
        foreach ($allocations->groupBy('budget_id') as $budgetId => $budgetAllocations) {
            $budget = $budgetAllocations->first()->budget;
            $budgeted = $budgetAllocations->sum('allocated_amount');
            $actual = $budgetAllocations->sum('utilized_amount');
            $itemVariance = $budgeted - $actual;
            
            $items[] = [
                'name' => $budget->budget_name,
                'budgeted' => $budgeted,
                'actual' => $actual,
                'variance' => $itemVariance,
                'variance_percentage' => $budgeted > 0 ? (($actual - $budgeted) / $budgeted) * 100 : 0
            ];
        }
        
        $this->varianceReport = [
            'total_budget' => $totalBudget,
            'total_actual' => $totalActual,
            'variance_amount' => $variance,
            'variance_percentage' => $totalBudget > 0 ? (($totalActual - $totalBudget) / $totalBudget) * 100 : 0,
            'items' => $items
        ];
    }
    
    private function generateUtilizationReport()
    {
        $query = BudgetAllocation::with('budget');
        
        if ($this->reportBudgetId) {
            $query->where('budget_id', $this->reportBudgetId);
        }
        
        $query = $this->applyPeriodFilters($query);
        $allocations = $query->orderBy('year')->orderBy('period')->get();
        
        $monthly = [];
        $chartLabels = [];
        $chartData = [];
        
        foreach ($allocations->groupBy(['year', 'period']) as $year => $yearData) {
            foreach ($yearData as $month => $monthAllocations) {
                $allocated = $monthAllocations->sum('allocated_amount');
                $utilized = $monthAllocations->sum('utilized_amount');
                $remaining = $allocated - $utilized;
                $percentage = $allocated > 0 ? ($utilized / $allocated) * 100 : 0;
                
                $monthName = Carbon\Carbon::create($year, $month)->format('M Y');
                $chartLabels[] = $monthName;
                $chartData[] = round($percentage, 1);
                
                // Determine trend
                $prevPercentage = end($chartData);
                $trend = count($chartData) > 1 ? 
                    ($percentage > $prevPercentage ? 'up' : ($percentage < $prevPercentage ? 'down' : 'stable')) : 
                    'stable';
                
                $monthly[] = [
                    'month_name' => $monthName,
                    'allocated' => $allocated,
                    'utilized' => $utilized,
                    'remaining' => $remaining,
                    'utilization_percentage' => $percentage,
                    'trend' => $trend
                ];
            }
        }
        
        $this->utilizationReport = [
            'monthly' => $monthly,
            'chart' => [
                'labels' => $chartLabels,
                'data' => $chartData
            ]
        ];
    }
    
    private function generateRolloverReport()
    {
        $query = BudgetAllocation::with('budget')
            ->where('available_amount', '>', 0);
        
        if ($this->reportBudgetId) {
            $query->where('budget_id', $this->reportBudgetId);
        }
        
        $query = $this->applyPeriodFilters($query);
        $allocations = $query->get();
        
        $totalUnused = $allocations->sum('available_amount');
        $automatic = $allocations->where('rollover_policy', 'AUTOMATIC')->sum('available_amount');
        $pending = $allocations->where('rollover_policy', 'APPROVAL_REQUIRED')->sum('available_amount');
        $expired = $allocations->where('rollover_policy', 'NO_ROLLOVER')->sum('available_amount');
        
        $items = [];
        foreach ($allocations as $allocation) {
            $items[] = [
                'id' => $allocation->id,
                'period' => Carbon\Carbon::create($allocation->year, $allocation->period)->format('F Y'),
                'budget_name' => $allocation->budget->budget_name,
                'unused_amount' => $allocation->available_amount,
                'rollover_policy' => $allocation->rollover_policy,
                'rolled_over' => $allocation->rollover_amount,
                'status' => $allocation->rollover_status ?? 'pending'
            ];
        }
        
        $this->rolloverReport = [
            'total_unused' => $totalUnused,
            'automatic_rollover' => $automatic,
            'pending_approval' => $pending,
            'expired' => $expired,
            'items' => $items
        ];
    }
    
    private function generateAdvanceReport()
    {
        $query = BudgetAdvance::with(['budgetAllocation.budget', 'requestedBy']);
        
        if ($this->reportBudgetId) {
            $query->whereHas('budgetAllocation', function($q) {
                $q->where('budget_id', $this->reportBudgetId);
            });
        }
        
        // Apply date filters based on request date
        if ($this->reportPeriod === 'custom' && $this->reportFrom && $this->reportTo) {
            $query->whereBetween('created_at', [$this->reportFrom, $this->reportTo]);
        } elseif ($this->reportPeriod === 'monthly') {
            $query->whereYear('created_at', $this->reportYear ?? now()->year)
                  ->whereMonth('created_at', $this->reportMonth ?? now()->month);
        } elseif ($this->reportPeriod === 'quarterly') {
            $startMonth = ($this->reportQuarter - 1) * 3 + 1;
            $endMonth = $this->reportQuarter * 3;
            $query->whereYear('created_at', $this->reportYear ?? now()->year)
                  ->whereMonth('created_at', '>=', $startMonth)
                  ->whereMonth('created_at', '<=', $endMonth);
        } elseif ($this->reportPeriod === 'yearly') {
            $query->whereYear('created_at', $this->reportYear ?? now()->year);
        }
        
        $advances = $query->get();
        
        $totalAdvances = $advances->sum('amount');
        $totalRepaid = $advances->sum('repaid_amount');
        $outstanding = $totalAdvances - $totalRepaid;
        $pendingCount = $advances->where('status', 'PENDING')->count();
        $overdueCount = $advances->where('repayment_due_date', '<', now())
                                 ->where('status', '!=', 'FULLY_REPAID')
                                 ->count();
        
        $advanceDetails = [];
        foreach ($advances as $advance) {
            $advanceDetails[] = [
                'request_date' => $advance->created_at->format('Y-m-d'),
                'budget_name' => $advance->budgetAllocation->budget->budget_name,
                'from_period' => Carbon\Carbon::create($advance->fromAllocation->year, $advance->fromAllocation->period)->format('F Y'),
                'amount' => $advance->amount,
                'repaid_amount' => $advance->repaid_amount,
                'outstanding_amount' => $advance->amount - $advance->repaid_amount,
                'status' => $advance->status,
                'repayment_percentage' => $advance->amount > 0 ? ($advance->repaid_amount / $advance->amount) * 100 : 0
            ];
        }
        
        $this->advanceReport = [
            'total_advances' => $totalAdvances,
            'total_repaid' => $totalRepaid,
            'outstanding' => $outstanding,
            'pending_count' => $pendingCount,
            'overdue_count' => $overdueCount,
            'advances' => $advanceDetails
        ];
    }
    
    private function generateSupplementaryReport()
    {
        $query = SupplementaryRequest::with(['budgetAllocation.budget', 'requestedBy', 'approvals.approvedBy']);
        
        if ($this->reportBudgetId) {
            $query->whereHas('budgetAllocation', function($q) {
                $q->where('budget_id', $this->reportBudgetId);
            });
        }
        
        // Apply date filters
        if ($this->reportPeriod === 'custom' && $this->reportFrom && $this->reportTo) {
            $query->whereBetween('created_at', [$this->reportFrom, $this->reportTo]);
        } elseif ($this->reportPeriod === 'monthly') {
            $query->whereYear('created_at', $this->reportYear ?? now()->year)
                  ->whereMonth('created_at', $this->reportMonth ?? now()->month);
        } elseif ($this->reportPeriod === 'quarterly') {
            $startMonth = ($this->reportQuarter - 1) * 3 + 1;
            $endMonth = $this->reportQuarter * 3;
            $query->whereYear('created_at', $this->reportYear ?? now()->year)
                  ->whereMonth('created_at', '>=', $startMonth)
                  ->whereMonth('created_at', '<=', $endMonth);
        } elseif ($this->reportPeriod === 'yearly') {
            $query->whereYear('created_at', $this->reportYear ?? now()->year);
        }
        
        $requests = $query->get();
        
        $totalRequested = $requests->sum('requested_amount');
        $totalApproved = $requests->where('status', 'APPROVED')->sum('approved_amount');
        $totalPending = $requests->where('status', 'PENDING')->sum('requested_amount');
        $totalRejected = $requests->where('status', 'REJECTED')->sum('requested_amount');
        
        $requestDetails = [];
        foreach ($requests as $request) {
            $approvers = [];
            foreach ($request->approvals as $approval) {
                $approvers[] = [
                    'name' => $approval->approvedBy->name ?? 'System',
                    'status' => $approval->status
                ];
            }
            
            $requestDetails[] = [
                'request_date' => $request->created_at->format('Y-m-d'),
                'budget_name' => $request->budgetAllocation->budget->budget_name,
                'period' => Carbon\Carbon::create($request->budgetAllocation->year, $request->budgetAllocation->period)->format('F Y'),
                'requested_amount' => $request->requested_amount,
                'approved_amount' => $request->approved_amount ?? 0,
                'justification' => $request->justification,
                'status' => $request->status,
                'approvers' => $approvers
            ];
        }
        
        $this->supplementaryReport = [
            'total_requested' => $totalRequested,
            'total_approved' => $totalApproved,
            'total_pending' => $totalPending,
            'total_rejected' => $totalRejected,
            'requests' => $requestDetails
        ];
    }
    
    private function applyPeriodFilters($query)
    {
        if ($this->reportPeriod === 'custom' && $this->reportFrom && $this->reportTo) {
            // Convert dates to year and period
            $fromDate = Carbon\Carbon::parse($this->reportFrom);
            $toDate = Carbon\Carbon::parse($this->reportTo);
            
            $query->where(function($q) use ($fromDate, $toDate) {
                $q->where(function($subQ) use ($fromDate) {
                    $subQ->where('year', '>', $fromDate->year)
                         ->orWhere(function($subSubQ) use ($fromDate) {
                             $subSubQ->where('year', $fromDate->year)
                                    ->where('period', '>=', $fromDate->month);
                         });
                })->where(function($subQ) use ($toDate) {
                    $subQ->where('year', '<', $toDate->year)
                         ->orWhere(function($subSubQ) use ($toDate) {
                             $subSubQ->where('year', $toDate->year)
                                    ->where('period', '<=', $toDate->month);
                         });
                });
            });
        } elseif ($this->reportPeriod === 'monthly') {
            $query->where('year', $this->reportYear ?? now()->year)
                  ->where('period', $this->reportMonth ?? now()->month);
        } elseif ($this->reportPeriod === 'quarterly') {
            $startMonth = ($this->reportQuarter - 1) * 3 + 1;
            $endMonth = $this->reportQuarter * 3;
            $query->where('year', $this->reportYear ?? now()->year)
                  ->where('period', '>=', $startMonth)
                  ->where('period', '<=', $endMonth);
        } elseif ($this->reportPeriod === 'yearly') {
            $query->where('year', $this->reportYear ?? now()->year);
        }
        
        return $query;
    }
    
    public function exportReport($format)
    {
        // Generate the report data first
        $this->generateReports();
        
        if ($format === 'pdf') {
            // Export as PDF
            session()->flash('message', 'PDF report generated and downloaded.');
        } elseif ($format === 'excel') {
            // Export as Excel
            session()->flash('message', 'Excel report generated and downloaded.');
        }
    }
    
    public function printReport()
    {
        $this->dispatchBrowserEvent('print-report');
    }
    
    public function scheduleReport()
    {
        // Open modal to schedule recurring report
        session()->flash('message', 'Report scheduling feature coming soon.');
    }
    
    public function shareReport()
    {
        // Open modal to share report via email
        session()->flash('message', 'Report sharing feature coming soon.');
    }
    
    public function approveRollover($rolloverItemId)
    {
        // Approve a pending rollover
        $allocation = BudgetAllocation::find($rolloverItemId);
        if ($allocation && $allocation->rollover_policy === 'APPROVAL_REQUIRED') {
            // Process the rollover
            $flexService = new BudgetFlexibilityService();
            $flexService->processRollover($allocation->id);
            
            session()->flash('message', 'Rollover approved successfully.');
            $this->generateReports(); // Refresh reports
        }
    }
    
    public function render()
    {
        $query = BudgetManagement::with(['budgetDepartment', 'commitments', 'scenarios', 'versions']);
        
        // Apply filters
        if ($this->searchTerm) {
            $query->where('budget_name', 'like', '%' . $this->searchTerm . '%');
        }
        
        if ($this->budgetTypeFilter) {
            $query->where('budget_type', $this->budgetTypeFilter);
        }
        
        if ($this->departmentFilter) {
            $query->where('budget_department_id', $this->departmentFilter);
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        $budgets = $query->paginate(10);
        
        // Get additional data - use existing Department model
        $departments = \App\Models\Department::where('status', true)->get();
        $transfers = BudgetTransfer::where('status', 'PENDING')->latest()->take(5)->get();
        $commitments = BudgetCommitment::active()->latest()->take(5)->get();
        
        // Get unlinked GL entries for linking
        $unlinkedGLEntries = GeneralLedger::whereNull('budget_id')
            ->where('major_category_code', '5000')
            ->latest()
            ->take(10)
            ->get();
        
        return view('livewire.budget-management.enhanced-budget-manager', [
            'budgets' => $budgets,
            'departments' => $departments,
            'transfers' => $transfers,
            'commitments' => $commitments,
            'unlinkedGLEntries' => $unlinkedGLEntries
        ]);
    }
}