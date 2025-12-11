<?php

namespace App\Services;

use App\Models\BudgetManagement;
use App\Models\BudgetTransaction;
use App\Models\GeneralLedger;
use App\Models\BudgetCommitment;
use App\Models\BudgetTransfer;
use App\Models\BudgetVersion;
use App\Models\BudgetScenario;
use App\Models\BudgetDepartment;
use App\Models\BudgetCustomAllocation;
use App\Models\BudgetCarryForward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnhancedBudgetService
{
    protected $monitoringService;
    
    public function __construct()
    {
        $this->monitoringService = new BudgetMonitoringService();
    }
    
    /**
     * Link GL transaction to budget
     */
    public function linkGLTransactionToBudget($glEntryId, $budgetId = null)
    {
        try {
            $glEntry = GeneralLedger::find($glEntryId);
            
            if (!$glEntry) {
                throw new \Exception('GL entry not found');
            }
            
            // Auto-detect budget if not provided
            if (!$budgetId) {
                $budgetId = $this->detectBudgetFromGL($glEntry);
            }
            
            if (!$budgetId) {
                return null; // No budget found for this transaction
            }
            
            DB::beginTransaction();
            
            $budget = BudgetManagement::find($budgetId);
            
            if (!$budget) {
                throw new \Exception('Budget not found');
            }
            
            // Create budget transaction
            $budgetTransaction = BudgetTransaction::create([
                'budget_id' => $budgetId,
                'transaction_id' => $glEntry->id,
                'transaction_type' => 'EXPENSE',
                'reference_number' => $glEntry->reference_number,
                'amount' => $glEntry->debit ?? 0,
                'description' => $glEntry->narration,
                'transaction_date' => $glEntry->created_at,
                'status' => 'POSTED',
                'created_by' => auth()->id() ?? 1,
                'posted_by' => auth()->id() ?? 1,
                'posted_at' => now()
            ]);
            
            // Update GL with budget links
            $glEntry->update([
                'budget_id' => $budgetId,
                'budget_transaction_id' => $budgetTransaction->id
            ]);
            
            // Update budget spent amount
            $budget->spent_amount += $budgetTransaction->amount;
            $budget->last_transaction_date = now();
            $budget->calculateBudgetMetrics();
            
            // Check for alerts
            $this->monitoringService->checkAndCreateAlerts($budget);
            
            DB::commit();
            
            Log::info('GL transaction linked to budget', [
                'gl_id' => $glEntryId,
                'budget_id' => $budgetId,
                'amount' => $budgetTransaction->amount
            ]);
            
            return $budgetTransaction;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to link GL to budget', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Auto-detect budget from GL entry
     */
    private function detectBudgetFromGL($glEntry)
    {
        // Try to match by expense account
        if ($glEntry->major_category_code == '5000') {
            $account = $glEntry->account;
            if ($account) {
                $budget = BudgetManagement::where('expense_account_id', $account->id)
                    ->where('status', 'ACTIVE')
                    ->first();
                
                if ($budget) {
                    return $budget->id;
                }
            }
        }
        
        // Try to match by department/cost center if available
        if ($glEntry->branch_id) {
            $budget = BudgetManagement::where('department', $glEntry->branch_id)
                ->where('status', 'ACTIVE')
                ->first();
            
            if ($budget) {
                return $budget->id;
            }
        }
        
        return null;
    }
    
    /**
     * Create budget commitment (PO, Contract, etc.)
     */
    public function createCommitment($budgetId, $data)
    {
        try {
            DB::beginTransaction();
            
            $budget = BudgetManagement::findOrFail($budgetId);
            
            // Check if commitment would exceed budget
            $totalCommitted = $budget->spent_amount + $budget->committed_amount + $data['amount'];
            if ($totalCommitted > $budget->allocated_amount) {
                throw new \Exception('Commitment would exceed budget allocation');
            }
            
            // Generate commitment number
            $commitmentNumber = $this->generateCommitmentNumber($data['type']);
            
            // Create commitment
            $commitment = BudgetCommitment::create([
                'budget_id' => $budgetId,
                'commitment_type' => $data['type'],
                'commitment_number' => $commitmentNumber,
                'vendor_name' => $data['vendor_name'] ?? null,
                'description' => $data['description'],
                'committed_amount' => $data['amount'],
                'remaining_amount' => $data['amount'],
                'commitment_date' => $data['commitment_date'] ?? now(),
                'expected_delivery_date' => $data['expected_delivery_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'status' => 'COMMITTED',
                'created_by' => auth()->id() ?? 1,
                'line_items' => $data['line_items'] ?? null
            ]);
            
            // Update budget committed amount
            $budget->committed_amount += $data['amount'];
            $budget->calculateBudgetMetrics();
            
            // Create budget transaction
            $this->monitoringService->recordCommitment(
                $budgetId,
                $data['amount'],
                $data['description'],
                $commitmentNumber
            );
            
            DB::commit();
            
            return $commitment;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create commitment', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate commitment number
     */
    private function generateCommitmentNumber($type)
    {
        $prefix = match($type) {
            'PURCHASE_ORDER' => 'PO',
            'CONTRACT' => 'CTR',
            'REQUISITION' => 'REQ',
            default => 'CMT'
        };
        
        $year = date('Y');
        $lastNumber = BudgetCommitment::where('commitment_number', 'like', "$prefix-$year-%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastNumber) {
            $parts = explode('-', $lastNumber->commitment_number);
            $sequence = (int)end($parts) + 1;
        }
        
        return sprintf("%s-%s-%05d", $prefix, $year, $sequence);
    }
    
    /**
     * Create budget version
     */
    public function createBudgetVersion($budgetId, $data)
    {
        try {
            DB::beginTransaction();
            
            $budget = BudgetManagement::findOrFail($budgetId);
            
            // Get next version number
            $lastVersion = BudgetVersion::where('budget_id', $budgetId)
                ->orderBy('version_number', 'desc')
                ->first();
            
            $versionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;
            
            // Create budget snapshot
            $budgetSnapshot = $budget->toArray();
            unset($budgetSnapshot['created_at'], $budgetSnapshot['updated_at']);
            
            // Create version
            $version = BudgetVersion::create([
                'budget_id' => $budgetId,
                'version_number' => $versionNumber,
                'version_name' => $data['version_name'] ?? "Revision $versionNumber",
                'version_type' => $data['version_type'] ?? 'REVISED',
                'allocated_amount' => $data['allocated_amount'] ?? $budget->allocated_amount,
                'budget_data' => $budgetSnapshot,
                'revision_reason' => $data['revision_reason'] ?? null,
                'status' => 'DRAFT',
                'created_by' => auth()->id() ?? 1
            ]);
            
            // Update budget if this version should be active
            if ($data['make_active'] ?? false) {
                $budget->update([
                    'current_version' => $versionNumber,
                    'allocated_amount' => $version->allocated_amount
                ]);
                
                $version->update(['status' => 'ACTIVE']);
            }
            
            DB::commit();
            
            return $version;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create budget version', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Create budget scenario
     */
    public function createBudgetScenario($budgetId, $data)
    {
        try {
            $budget = BudgetManagement::findOrFail($budgetId);
            
            // Calculate projected amount based on scenario type
            $projectedAmount = $this->calculateScenarioAmount($budget, $data);
            
            // Calculate projected utilization
            $projectedUtilization = $budget->spent_amount > 0 
                ? round(($budget->spent_amount / $projectedAmount) * 100, 2)
                : 0;
            
            // Create scenario
            $scenario = BudgetScenario::create([
                'budget_id' => $budgetId,
                'scenario_name' => $data['scenario_name'],
                'scenario_type' => $data['scenario_type'],
                'adjustment_percentage' => $data['adjustment_percentage'] ?? 0,
                'assumptions' => $data['assumptions'] ?? [],
                'projected_amount' => $projectedAmount,
                'projected_utilization' => $projectedUtilization,
                'is_active' => $data['is_active'] ?? false,
                'created_by' => auth()->id() ?? 1
            ]);
            
            // Update budget if this scenario should be active
            if ($scenario->is_active) {
                // Deactivate other scenarios
                BudgetScenario::where('budget_id', $budgetId)
                    ->where('id', '!=', $scenario->id)
                    ->update(['is_active' => false]);
                
                $budget->update(['active_scenario_id' => $scenario->id]);
            }
            
            return $scenario;
            
        } catch (\Exception $e) {
            Log::error('Failed to create budget scenario', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Calculate scenario amount
     */
    private function calculateScenarioAmount($budget, $data)
    {
        $baseAmount = $budget->allocated_amount;
        $adjustment = $data['adjustment_percentage'] ?? 0;
        
        switch ($data['scenario_type']) {
            case 'BEST_CASE':
                return $baseAmount * (1 + ($adjustment ?: 20) / 100);
            case 'WORST_CASE':
                return $baseAmount * (1 - ($adjustment ?: 20) / 100);
            case 'EXPECTED':
                return $baseAmount;
            case 'CUSTOM':
                return $baseAmount * (1 + $adjustment / 100);
            default:
                return $baseAmount;
        }
    }
    
    /**
     * Create custom allocation pattern
     */
    public function createCustomAllocation($budgetId, $allocations)
    {
        try {
            DB::beginTransaction();
            
            $budget = BudgetManagement::findOrFail($budgetId);
            $totalAmount = $budget->allocated_amount;
            
            // Delete existing allocations
            BudgetCustomAllocation::where('budget_id', $budgetId)->delete();
            
            // Create new allocations
            foreach ($allocations as $allocation) {
                $amount = isset($allocation['percentage']) 
                    ? $totalAmount * ($allocation['percentage'] / 100)
                    : $allocation['amount'];
                
                BudgetCustomAllocation::create([
                    'budget_id' => $budgetId,
                    'period_number' => $allocation['period'],
                    'period_type' => $allocation['type'] ?? 'MONTHLY',
                    'allocated_amount' => $amount,
                    'allocated_percentage' => isset($allocation['percentage']) 
                        ? $allocation['percentage']
                        : round(($amount / $totalAmount) * 100, 2),
                    'notes' => $allocation['notes'] ?? null
                ]);
            }
            
            // Update budget allocation pattern
            $budget->update([
                'allocation_pattern' => 'CUSTOM',
                'monthly_allocations' => $allocations
            ]);
            
            DB::commit();
            
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create custom allocation', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process budget transfer
     */
    public function createBudgetTransfer($fromBudgetId, $toBudgetId, $amount, $reason)
    {
        try {
            DB::beginTransaction();
            
            $fromBudget = BudgetManagement::findOrFail($fromBudgetId);
            $toBudget = BudgetManagement::findOrFail($toBudgetId);
            
            // Validate transfer
            if ($fromBudget->available_amount < $amount) {
                throw new \Exception('Insufficient available funds in source budget');
            }
            
            // Generate transfer reference
            $reference = 'TRF-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // Create transfer record
            $transfer = BudgetTransfer::create([
                'transfer_reference' => $reference,
                'from_budget_id' => $fromBudgetId,
                'to_budget_id' => $toBudgetId,
                'transfer_amount' => $amount,
                'transfer_reason' => $reason,
                'status' => 'PENDING',
                'requested_by' => auth()->id() ?? 1
            ]);
            
            DB::commit();
            
            return $transfer;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create budget transfer', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Approve budget transfer
     */
    public function approveBudgetTransfer($transferId, $notes = null)
    {
        try {
            DB::beginTransaction();
            
            $transfer = BudgetTransfer::findOrFail($transferId);
            
            if ($transfer->status !== 'PENDING') {
                throw new \Exception('Transfer is not in pending status');
            }
            
            $fromBudget = $transfer->fromBudget;
            $toBudget = $transfer->toBudget;
            
            // Validate again
            if ($fromBudget->available_amount < $transfer->transfer_amount) {
                throw new \Exception('Insufficient available funds in source budget');
            }
            
            // Update budgets
            $fromBudget->allocated_amount -= $transfer->transfer_amount;
            $fromBudget->calculateBudgetMetrics();
            
            $toBudget->allocated_amount += $transfer->transfer_amount;
            $toBudget->calculateBudgetMetrics();
            
            // Update transfer
            $transfer->update([
                'status' => 'APPROVED',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'approval_notes' => $notes
            ]);
            
            // Create transactions for audit trail
            $this->monitoringService->transferBudget(
                $fromBudget->id,
                $toBudget->id,
                $transfer->transfer_amount,
                $transfer->transfer_reason
            );
            
            DB::commit();
            
            return $transfer;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve budget transfer', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Process carry forward
     */
    public function processCarryForward($budgetId, $targetYear)
    {
        try {
            DB::beginTransaction();
            
            $sourceBudget = BudgetManagement::findOrFail($budgetId);
            
            if (!$sourceBudget->allow_carry_forward) {
                throw new \Exception('Carry forward not allowed for this budget');
            }
            
            // Calculate carry forward amount
            $carryAmount = $sourceBudget->available_amount;
            
            if ($sourceBudget->carry_forward_limit) {
                $maxCarry = $sourceBudget->allocated_amount * ($sourceBudget->carry_forward_limit / 100);
                $carryAmount = min($carryAmount, $maxCarry);
            }
            
            if ($carryAmount <= 0) {
                throw new \Exception('No amount available for carry forward');
            }
            
            // Create carry forward record
            $carryForward = BudgetCarryForward::create([
                'from_budget_id' => $budgetId,
                'carry_forward_amount' => $carryAmount,
                'from_year' => $sourceBudget->budget_year,
                'to_year' => $targetYear,
                'status' => 'PENDING',
                'justification' => 'Year-end carry forward'
            ]);
            
            DB::commit();
            
            return $carryForward;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process carry forward', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate comprehensive budget report
     */
    public function generateBudgetReport($type, $parameters)
    {
        try {
            $data = [];
            
            switch ($type) {
                case 'BUDGET_VS_ACTUAL':
                    $data = $this->generateBudgetVsActualReport($parameters);
                    break;
                case 'VARIANCE_ANALYSIS':
                    $data = $this->generateVarianceReport($parameters);
                    break;
                case 'DEPARTMENT_SUMMARY':
                    $data = $this->generateDepartmentReport($parameters);
                    break;
                case 'TREND_ANALYSIS':
                    $data = $this->generateTrendReport($parameters);
                    break;
                case 'COMMITMENT_STATUS':
                    $data = $this->generateCommitmentReport($parameters);
                    break;
                default:
                    throw new \Exception('Unknown report type');
            }
            
            // Save report
            $report = BudgetReport::create([
                'report_name' => $parameters['name'] ?? "$type Report",
                'report_type' => $type,
                'report_parameters' => $parameters,
                'report_data' => $data,
                'created_by' => auth()->id() ?? 1,
                'last_generated_at' => now()
            ]);
            
            return $report;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate report', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Generate Budget vs Actual report
     */
    private function generateBudgetVsActualReport($parameters)
    {
        $query = BudgetManagement::with(['transactions', 'expenseAccount']);
        
        // Apply filters
        if (isset($parameters['budget_id'])) {
            $query->where('id', $parameters['budget_id']);
        }
        
        if (isset($parameters['department_id'])) {
            $query->where('budget_department_id', $parameters['department_id']);
        }
        
        if (isset($parameters['date_from']) && isset($parameters['date_to'])) {
            $query->whereBetween('created_at', [$parameters['date_from'], $parameters['date_to']]);
        }
        
        $budgets = $query->get();
        
        $report = [];
        foreach ($budgets as $budget) {
            $actualSpent = GeneralLedger::where('budget_id', $budget->id)
                ->whereBetween('created_at', [$parameters['date_from'], $parameters['date_to']])
                ->sum('debit');
            
            $report[] = [
                'budget_id' => $budget->id,
                'budget_name' => $budget->budget_name,
                'department' => $budget->budgetDepartment->department_name ?? 'N/A',
                'allocated' => $budget->allocated_amount,
                'spent' => $actualSpent,
                'committed' => $budget->committed_amount,
                'available' => $budget->available_amount,
                'variance' => $budget->allocated_amount - $actualSpent,
                'utilization' => $budget->utilization_percentage,
                'status' => $budget->health_status
            ];
        }
        
        return $report;
    }
    
    /**
     * Generate variance report
     */
    private function generateVarianceReport($parameters)
    {
        $budgets = BudgetManagement::active()->get();
        $report = [];
        
        foreach ($budgets as $budget) {
            $variance = $this->monitoringService->calculateVariance(
                $budget,
                $parameters['period'] ?? 'current_month'
            );
            
            $report[] = array_merge([
                'budget_id' => $budget->id,
                'budget_name' => $budget->budget_name
            ], $variance);
        }
        
        return $report;
    }
    
    /**
     * Generate department report
     */
    private function generateDepartmentReport($parameters)
    {
        $departments = BudgetDepartment::with('budgets')->get();
        $report = [];
        
        foreach ($departments as $dept) {
            $budgets = BudgetManagement::where('budget_department_id', $dept->id)->get();
            
            $report[] = [
                'department_id' => $dept->id,
                'department_name' => $dept->department_name,
                'total_allocated' => $budgets->sum('allocated_amount'),
                'total_spent' => $budgets->sum('spent_amount'),
                'total_committed' => $budgets->sum('committed_amount'),
                'total_available' => $budgets->sum('available_amount'),
                'average_utilization' => $budgets->avg('utilization_percentage'),
                'budget_count' => $budgets->count(),
                'at_risk_count' => $budgets->where('utilization_percentage', '>=', 80)->count()
            ];
        }
        
        return $report;
    }
    
    /**
     * Generate trend report
     */
    private function generateTrendReport($parameters)
    {
        $months = $parameters['months'] ?? 12;
        $budgetId = $parameters['budget_id'] ?? null;
        
        $report = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            $query = GeneralLedger::expenses()
                ->forPeriod($startDate, $endDate);
            
            if ($budgetId) {
                $query->where('budget_id', $budgetId);
            }
            
            $report[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('F Y'),
                'total_spent' => $query->sum('debit'),
                'transaction_count' => $query->count()
            ];
        }
        
        return $report;
    }
    
    /**
     * Generate commitment report
     */
    private function generateCommitmentReport($parameters)
    {
        $query = BudgetCommitment::with('budget');
        
        if (isset($parameters['status'])) {
            $query->where('status', $parameters['status']);
        }
        
        if (isset($parameters['budget_id'])) {
            $query->where('budget_id', $parameters['budget_id']);
        }
        
        $commitments = $query->get();
        
        $report = [];
        foreach ($commitments as $commitment) {
            $report[] = [
                'commitment_number' => $commitment->commitment_number,
                'type' => $commitment->commitment_type,
                'budget_name' => $commitment->budget->budget_name,
                'vendor' => $commitment->vendor_name,
                'description' => $commitment->description,
                'committed_amount' => $commitment->committed_amount,
                'utilized_amount' => $commitment->utilized_amount,
                'remaining_amount' => $commitment->remaining_amount,
                'status' => $commitment->status,
                'commitment_date' => $commitment->commitment_date,
                'expiry_date' => $commitment->expiry_date
            ];
        }
        
        return $report;
    }
}