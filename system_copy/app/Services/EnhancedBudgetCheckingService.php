<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Expense;
use App\Models\BudgetManagement;
use App\Models\BudgetAllocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EnhancedBudgetCheckingService extends BudgetCheckingService
{
    /**
     * Check budget for expense with allocation awareness
     */
    public function checkBudgetForExpense($accountId, $amount, $expenseMonth = null)
    {
        $expenseMonth = $expenseMonth ? Carbon::parse($expenseMonth) : Carbon::now();
        $currentMonth = $expenseMonth->format('Y-m');
        
        Log::channel('budget_management')->info('════════════════════════════════════════════════════════', []);
        Log::channel('budget_management')->info('🔍 STARTING BUDGET CHECK FOR EXPENSE', [
            'account_id' => $accountId,
            'amount' => number_format($amount, 2),
            'expense_month' => $currentMonth,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name ?? 'Unknown',
            'timestamp' => now()->toIso8601String()
        ]);
        
        // First, find the budget item for this expense account
        $budgetItem = $this->findBudgetItem($accountId, $expenseMonth);
        
        if (!$budgetItem) {
            Log::channel('budget_management')->error('❌ NO BUDGET FOUND - EXPENSE BLOCKED', [
                'account_id' => $accountId,
                'expense_month' => $currentMonth,
                'message' => 'No budget allocation exists for this expense account',
                'action' => 'EXPENSE_SUBMISSION_BLOCKED'
            ]);
            
            // Block expense submission without budget
            return [
                'has_budget' => false,
                'can_proceed' => false,  // STRICT: No expense without budget
                'message' => 'No budget found for this expense account. Budget allocation must be created before expenses can be submitted.',
                'budget_status' => 'NO_BUDGET',
                'would_exceed' => true,  // Treat no budget as exceeding budget
                'error_type' => 'NO_BUDGET_ALLOCATION'
            ];
        }
        
        Log::channel('budget_management')->info('✅ BUDGET ITEM FOUND', [
            'budget_id' => $budgetItem->id,
            'budget_name' => $budgetItem->budget_name,
            'total_budget' => number_format($budgetItem->expenditure ?? 0, 2),
            'budget_period' => $budgetItem->start_date . ' to ' . $budgetItem->end_date,
            'status' => $budgetItem->status,
            'approval_status' => $budgetItem->approval_status
        ]);
        
        // Get the allocation for the specific month
        $allocation = $this->getMonthlyAllocation($budgetItem->id, $expenseMonth);
        
        if (!$allocation) {
            Log::channel('budget_management')->warning('⚠️ NO ALLOCATION FOUND - Creating default allocation', [
                'budget_id' => $budgetItem->id,
                'month' => $currentMonth,
                'action' => 'CREATING_DEFAULT_ALLOCATION'
            ]);
            
            // Try to create allocation if it doesn't exist
            try {
                $flexService = new BudgetFlexibilityService();
                // Extract year from expense month (format: YYYY-MM)
                $year = (int) substr($expenseMonth, 0, 4);
                $flexService->createMonthlyAllocations($budgetItem->id, $year, []);
                $allocation = $this->getMonthlyAllocation($budgetItem->id, $expenseMonth);
            } catch (\Exception $e) {
                Log::channel('budget_management')->error('Failed to create allocation', [
                    'error' => $e->getMessage()
                ]);
            }
            
            if (!$allocation) {
                // If still no allocation, fall back to simple monthly division
                Log::channel('budget_management')->warning('Using fallback budget calculation');
                return parent::checkBudgetForExpense($accountId, $amount, $expenseMonth);
            }
        }
        
        Log::channel('budget_management')->info('📊 BUDGET ALLOCATION DETAILS', [
            'allocation_id' => $allocation->id,
            'period' => $allocation->period . '/' . $allocation->year,
            'allocated_amount' => number_format($allocation->allocated_amount, 2),
            'utilized_amount' => number_format($allocation->utilized_amount, 2),
            'available_amount' => number_format($allocation->available_amount, 2),
            'rollover_amount' => number_format($allocation->rollover_amount, 2),
            'advance_amount' => number_format($allocation->advance_amount, 2),
            'supplementary_amount' => number_format($allocation->supplementary_amount, 2),
            'total_available' => number_format($allocation->total_available, 2),
            'utilization_percentage' => number_format($allocation->utilization_percentage, 2) . '%'
        ]);
        
        // Calculate the comprehensive budget status
        $result = $this->calculateComprehensiveBudgetStatus($allocation, $amount, $expenseMonth);
        
        // Add strict enforcement
        if ($result['would_exceed'] ?? false) {
            $result['can_proceed'] = false; // STRICT: Block if budget exceeded
            
            Log::channel('budget_management')->critical('🚫 EXPENSE BLOCKED - INSUFFICIENT BUDGET', [
                'account_id' => $accountId,
                'requested_amount' => number_format($amount, 2),
                'available_amount' => number_format($result['total_available'] ?? 0, 2),
                'shortage' => number_format($result['over_budget_amount'] ?? 0, 2),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name ?? 'Unknown',
                'action' => 'EXPENSE_SUBMISSION_BLOCKED',
                'required_action' => 'One of: Request supplementary budget, Request advance, Reduce expense amount, or Transfer from another budget'
            ]);
        } else {
            Log::channel('budget_management')->info('✅ EXPENSE APPROVED FOR SUBMISSION', [
                'account_id' => $accountId,
                'amount' => number_format($amount, 2),
                'remaining_budget' => number_format(($result['total_available'] ?? 0) - $amount, 2),
                'utilization_after' => number_format($result['new_utilization_percentage'] ?? 0, 2) . '%',
                'action' => 'EXPENSE_CAN_PROCEED'
            ]);
        }
        
        Log::channel('budget_management')->info('📋 BUDGET CHECK RESULT SUMMARY', [
            'decision' => ($result['would_exceed'] ?? false) ? '❌ BLOCKED' : '✅ APPROVED',
            'can_proceed' => $result['can_proceed'] ?? false,
            'budget_status' => $result['budget_status'] ?? 'UNKNOWN',
            'available_options' => count($result['available_options'] ?? []) . ' alternative options available'
        ]);
        
        Log::channel('budget_management')->info('════════════════════════════════════════════════════════', []);
        
        return $result;
    }
    
    /**
     * Find the budget item for an expense account
     */
    private function findBudgetItem($accountId, $expenseMonth)
    {
        Log::channel('budget_management')->debug('Searching for budget item', [
            'account_id' => $accountId,
            'expense_month' => $expenseMonth->format('Y-m-d')
        ]);
        
        // Check if account is directly linked as expense account
        $budgetItem = BudgetManagement::where('expense_account_id', $accountId)
            ->where('approval_status', 'APPROVED')
            ->where('status', 'ACTIVE')
            ->where(function ($query) use ($expenseMonth) {
                $query->where('start_date', '<=', $expenseMonth)
                      ->where('end_date', '>=', $expenseMonth);
            })
            ->first();
        
        if ($budgetItem) {
            Log::channel('budget_management')->debug('Found budget item by expense_account_id', [
                'budget_id' => $budgetItem->id,
                'budget_name' => $budgetItem->budget_name
            ]);
            return $budgetItem;
        }
        
        // Check if account matches budget name pattern
        $account = Account::find($accountId);
        if ($account) {
            $budgetItem = BudgetManagement::where('budget_name', 'LIKE', '%' . $account->account_name . '%')
                ->where('approval_status', 'APPROVED')
                ->where('status', 'ACTIVE')
                ->where(function ($query) use ($expenseMonth) {
                    $query->where('start_date', '<=', $expenseMonth)
                          ->where('end_date', '>=', $expenseMonth);
                })
                ->first();
        }
        
        if ($budgetItem) {
            Log::channel('budget_management')->debug('Found budget item by name pattern', [
                'budget_id' => $budgetItem->id,
                'budget_name' => $budgetItem->budget_name,
                'matched_account_name' => $account->account_name
            ]);
        } else {
            Log::channel('budget_management')->debug('No budget item found');
        }
        
        return $budgetItem;
    }
    
    /**
     * Get monthly allocation for a budget
     */
    private function getMonthlyAllocation($budgetId, $expenseMonth)
    {
        return BudgetAllocation::where('budget_id', $budgetId)
            ->where('year', $expenseMonth->year)
            ->where('period', $expenseMonth->month)
            ->first();
    }
    
    /**
     * Get no budget response
     */
    private function getNoBudgetResponse()
    {
        return [
            'has_budget' => false,
            'can_proceed' => true,
            'message' => 'No budget found for this expense account'
        ];
    }
    
    /**
     * Calculate comprehensive budget status including allocations, advances, and supplementary
     */
    private function calculateComprehensiveBudgetStatus($allocation, $amount, $expenseMonth)
    {
        Log::channel('budget_management')->info('📈 CALCULATING BUDGET STATUS', [
            'allocation_id' => $allocation->id,
            'requested_amount' => number_format($amount, 2),
            'current_utilized' => number_format($allocation->utilized_amount, 2),
            'total_available' => number_format($allocation->total_available, 2)
        ]);
        
        // Get total available budget (includes rollover, advances, supplementary)
        $totalAvailable = $allocation->total_available;
        
        // Calculate current month's expenses (excluding this one)
        $monthlySpent = $this->getMonthlyExpenses($allocation->budget_id, $expenseMonth);
        
        // Calculate remaining budget
        $remainingBudget = $totalAvailable - $monthlySpent;
        $utilizationPercentage = $totalAvailable > 0 ? ($monthlySpent / $totalAvailable) * 100 : 0;
        
        // Check if this expense would exceed the budget
        $wouldExceed = ($monthlySpent + $amount) > $totalAvailable;
        $overBudgetAmount = $wouldExceed ? ($monthlySpent + $amount) - $totalAvailable : 0;
        
        Log::channel('budget_management')->info('📊 BUDGET CALCULATION RESULTS', [
            'monthly_spent' => number_format($monthlySpent, 2),
            'expense_amount' => number_format($amount, 2),
            'total_after_expense' => number_format($monthlySpent + $amount, 2),
            'available_budget' => number_format($totalAvailable, 2),
            'remaining_budget' => number_format($remainingBudget, 2),
            'would_exceed' => $wouldExceed ? 'YES' : 'NO',
            'over_budget_amount' => number_format($overBudgetAmount, 2),
            'current_utilization' => number_format($utilizationPercentage, 2) . '%',
            'new_utilization' => number_format($totalAvailable > 0 ? (($monthlySpent + $amount) / $totalAvailable) * 100 : 0, 2) . '%'
        ]);
        
        // Determine budget status and available options
        $budgetStatus = $this->determineBudgetStatus($utilizationPercentage, $wouldExceed);
        $availableOptions = $this->getAvailableOptions($allocation, $overBudgetAmount);
        
        if (count($availableOptions) > 0) {
            Log::channel('budget_management')->info('💡 AVAILABLE OPTIONS FOR BUDGET OVERRUN', [
                'options_count' => count($availableOptions),
                'options' => array_map(function($opt) {
                    return [
                        'type' => $opt['type'],
                        'amount' => number_format($opt['amount'], 2),
                        'covers_overrun' => $opt['covers_overrun'] ? 'Yes' : 'No'
                    ];
                }, $availableOptions)
            ]);
        }
        
        return [
            'has_budget' => true,
            'budget_item_id' => $allocation->budget_id,
            'allocation_id' => $allocation->id,
            'budget_status' => $budgetStatus,
            'monthly_budget' => $allocation->allocated_amount,
            'total_available' => $totalAvailable,
            'monthly_spent' => $monthlySpent,
            'remaining_budget' => $remainingBudget,
            'utilization_percentage' => $utilizationPercentage,
            'would_exceed' => $wouldExceed,
            'over_budget_amount' => $overBudgetAmount,
            'new_utilization_percentage' => $totalAvailable > 0 ? (($monthlySpent + $amount) / $totalAvailable) * 100 : 0,
            'available_options' => $availableOptions,
            'allocation_details' => [
                'allocated' => $allocation->allocated_amount,
                'rollover' => $allocation->rollover_amount,
                'advance' => $allocation->advance_amount,
                'supplementary' => $allocation->supplementary_amount,
                'transferred_in' => $allocation->transferred_in,
                'transferred_out' => $allocation->transferred_out
            ],
            'can_proceed' => !$wouldExceed, // STRICT: Only proceed if within budget
            'message' => $this->getEnhancedBudgetMessage($budgetStatus, $overBudgetAmount, $totalAvailable, $availableOptions)
        ];
    }
    
    /**
     * Get monthly expenses for a budget
     */
    private function getMonthlyExpenses($budgetId, $expenseMonth)
    {
        $expenses = Expense::where('budget_item_id', $budgetId)
            ->whereIn('status', ['APPROVED', 'PAID'])
            ->whereYear('expense_month', $expenseMonth->year)
            ->whereMonth('expense_month', $expenseMonth->month)
            ->sum('amount');
            
        Log::channel('budget_management')->debug('Monthly expenses calculated', [
            'budget_id' => $budgetId,
            'month' => $expenseMonth->format('Y-m'),
            'total_expenses' => number_format($expenses, 2)
        ]);
        
        return $expenses;
    }
    
    /**
     * Determine budget status based on utilization and overrun
     */
    private function determineBudgetStatus($utilization, $wouldExceed)
    {
        if ($wouldExceed) {
            return 'OVER_BUDGET';
        } elseif ($utilization >= 90) {
            return 'CRITICAL';
        } elseif ($utilization >= 75) {
            return 'WARNING';
        } elseif ($utilization >= 50) {
            return 'MODERATE';
        } else {
            return 'HEALTHY';
        }
    }
    
    /**
     * Get available options when budget would be exceeded
     */
    private function getAvailableOptions($allocation, $overBudgetAmount)
    {
        $options = [];
        
        Log::channel('budget_management')->debug('Checking available options for budget overrun', [
            'allocation_id' => $allocation->id,
            'overrun_amount' => number_format($overBudgetAmount, 2)
        ]);
        
        // Check if rollover from previous months is available
        if ($allocation->rollover_policy === 'AUTOMATIC' || $allocation->rollover_policy === 'APPROVAL_REQUIRED') {
            // Need to find the account ID for this budget
            $budget = BudgetManagement::find($allocation->budget_id);
            if ($budget && $budget->expense_account_id) {
                $previousUnused = $this->calculateUnusedFromAllocations(
                    $allocation->budget_id, 
                    $allocation->year, 
                    $allocation->period
                );
                if ($previousUnused > 0) {
                    $options[] = [
                        'type' => 'USE_ROLLOVER',
                        'amount' => $previousUnused,
                        'description' => 'Use rollover budget from previous months',
                        'covers_overrun' => $previousUnused >= $overBudgetAmount
                    ];
                    
                    Log::channel('budget_management')->debug('Rollover option available', [
                        'amount' => number_format($previousUnused, 2),
                        'covers_overrun' => $previousUnused >= $overBudgetAmount
                    ]);
                }
            }
        }
        
        // Check if advance from future months is possible
        $futureAvailable = $this->getFutureMonthsAvailableBudget($allocation->budget_id, $allocation->year, $allocation->period);
        if ($futureAvailable > 0) {
            $options[] = [
                'type' => 'REQUEST_ADVANCE',
                'amount' => min($futureAvailable, $overBudgetAmount * 1.2), // Allow up to 20% buffer
                'description' => 'Request advance from future months',
                'covers_overrun' => $futureAvailable >= $overBudgetAmount
            ];
            
            Log::channel('budget_management')->debug('Advance option available', [
                'amount' => number_format(min($futureAvailable, $overBudgetAmount * 1.2), 2),
                'covers_overrun' => $futureAvailable >= $overBudgetAmount
            ]);
        }
        
        // Check if transfer from other budgets is possible
        $transferableAmount = $this->getTransferableAmount($allocation->budget_id);
        if ($transferableAmount > 0) {
            $options[] = [
                'type' => 'TRANSFER_BUDGET',
                'amount' => min($transferableAmount, $overBudgetAmount),
                'description' => 'Transfer from another budget item',
                'covers_overrun' => $transferableAmount >= $overBudgetAmount
            ];
            
            Log::channel('budget_management')->debug('Transfer option available', [
                'amount' => number_format(min($transferableAmount, $overBudgetAmount), 2),
                'covers_overrun' => $transferableAmount >= $overBudgetAmount
            ]);
        }
        
        // Always offer supplementary budget request as an option
        $options[] = [
            'type' => 'REQUEST_SUPPLEMENTARY',
            'amount' => $overBudgetAmount,
            'description' => 'Request supplementary budget approval',
            'covers_overrun' => true
        ];
        
        return $options;
    }
    
    /**
     * Calculate unused budget from previous allocations
     */
    private function calculateUnusedFromAllocations($budgetId, $currentYear, $currentPeriod)
    {
        $previousAllocations = BudgetAllocation::where('budget_id', $budgetId)
            ->where(function ($query) use ($currentYear, $currentPeriod) {
                $query->where('year', '<', $currentYear)
                      ->orWhere(function ($q) use ($currentYear, $currentPeriod) {
                          $q->where('year', $currentYear)
                            ->where('period', '<', $currentPeriod);
                      });
            })
            ->get();
        
        $totalUnused = 0;
        foreach ($previousAllocations as $alloc) {
            $totalUnused += max(0, $alloc->available_amount);
        }
        
        Log::channel('budget_management')->debug('Calculated unused from previous periods', [
            'budget_id' => $budgetId,
            'periods_checked' => count($previousAllocations),
            'total_unused' => number_format($totalUnused, 2)
        ]);
        
        return $totalUnused;
    }
    
    /**
     * Get enhanced budget message
     */
    private function getEnhancedBudgetMessage($status, $overAmount, $available, $options)
    {
        switch ($status) {
            case 'OVER_BUDGET':
                $msg = "Budget exceeded by " . number_format($overAmount, 2);
                if (count($options) > 0) {
                    $msg .= ". Available options: ";
                    $optionTexts = [];
                    foreach ($options as $opt) {
                        if ($opt['covers_overrun']) {
                            $optionTexts[] = $opt['description'] . " (" . number_format($opt['amount'], 2) . ")";
                        }
                    }
                    $msg .= implode(', ', $optionTexts);
                } else {
                    $msg .= ". No alternative funding available - expense cannot proceed.";
                }
                return $msg;
                
            case 'CRITICAL':
                return "Budget utilization is critical (>90%). Available: " . number_format($available, 2);
                
            case 'WARNING':
                return "Budget utilization is high (>75%). Available: " . number_format($available, 2);
                
            case 'MODERATE':
                return "Budget utilization is moderate. Available: " . number_format($available, 2);
                
            default:
                return "Budget is healthy. Available: " . number_format($available, 2);
        }
    }
    
    /**
     * Get budget status for month
     */
    public function getBudgetStatusForMonth($budgetItemId, $month = null)
    {
        $month = $month ?: now();
        $allocation = $this->getMonthlyAllocation($budgetItemId, $month);
        
        if (!$allocation) {
            return [
                'status' => 'NO_ALLOCATION',
                'message' => 'No budget allocation exists for this period'
            ];
        }
        
        return [
            'status' => $this->determineBudgetStatus($allocation->utilization_percentage, false),
            'utilization' => $allocation->utilization_percentage,
            'available' => $allocation->total_available,
            'allocated' => $allocation->allocated_amount,
            'utilized' => $allocation->utilized_amount
        ];
    }
    
    /**
     * Get unused budget from previous months
     */
    public function getPreviousMonthsUnusedBudget($accountId, $currentMonth = null)
    {
        $currentMonth = $currentMonth ?: now();
        $budgetItem = $this->findBudgetItem($accountId, $currentMonth);
        
        if (!$budgetItem) {
            return 0;
        }
        
        return $this->calculateUnusedFromAllocations(
            $budgetItem->id,
            $currentMonth->year,
            $currentMonth->month
        );
    }
    
    /**
     * Get future months available budget
     */
    private function getFutureMonthsAvailableBudget($budgetId, $currentYear, $currentPeriod)
    {
        $futureAllocations = BudgetAllocation::where('budget_id', $budgetId)
            ->where(function ($query) use ($currentYear, $currentPeriod) {
                $query->where('year', '>', $currentYear)
                      ->orWhere(function ($q) use ($currentYear, $currentPeriod) {
                          $q->where('year', $currentYear)
                            ->where('period', '>', $currentPeriod);
                      });
            })
            ->get();
        
        $totalAvailable = 0;
        foreach ($futureAllocations as $alloc) {
            $totalAvailable += max(0, $alloc->available_amount);
        }
        
        Log::channel('budget_management')->debug('Calculated future months available', [
            'budget_id' => $budgetId,
            'periods_checked' => count($futureAllocations),
            'total_available' => number_format($totalAvailable, 2)
        ]);
        
        return $totalAvailable;
    }
    
    /**
     * Get transferable amount from other budgets
     */
    private function getTransferableAmount($budgetId)
    {
        // This would check other budget items in the same department/category
        // For now, return a placeholder value
        return 0;
    }
    
    /**
     * Process budget resolution
     */
    public function processBudgetResolution($expenseId, $resolutionType, $data = [])
    {
        $expense = Expense::find($expenseId);
        if (!$expense) {
            throw new \Exception('Expense not found');
        }
        
        Log::channel('budget_management')->info('PROCESSING BUDGET RESOLUTION', [
            'expense_id' => $expenseId,
            'resolution_type' => $resolutionType,
            'amount' => number_format($expense->amount, 2),
            'data' => $data
        ]);
        
        switch ($resolutionType) {
            case 'USE_ROLLOVER':
                return $this->processRolloverUsage($expense, $data);
                
            case 'REQUEST_ADVANCE':
                return $this->processAdvanceRequest($expense, $data);
                
            case 'TRANSFER_BUDGET':
                return $this->processBudgetTransfer($expense, $data);
                
            case 'REQUEST_SUPPLEMENTARY':
                return $this->processSupplementaryRequest($expense, $data);
                
            default:
                throw new \Exception('Invalid resolution type');
        }
    }
    
    /**
     * Process rollover usage
     */
    private function processRolloverUsage($expense, $data)
    {
        // Implementation would update the allocation to use rollover funds
        Log::channel('budget_management')->info('Processing rollover usage for expense', [
            'expense_id' => $expense->id,
            'amount' => $expense->amount
        ]);
        
        return true;
    }
    
    /**
     * Process advance request
     */
    private function processAdvanceRequest($expense, $data)
    {
        $flexService = new BudgetFlexibilityService();
        
        $advance = $flexService->requestBudgetAdvance(
            $expense->budget_item_id,
            $data['current_period'] ?? now()->month,
            $data['current_year'] ?? now()->year,
            $expense->amount,
            $data['from_period'] ?? (now()->month + 1),
            $data['from_year'] ?? now()->year,
            $data['reason'] ?? 'Budget advance for expense #' . $expense->id
        );
        
        $expense->update([
            'budget_resolution' => 'ADVANCE_REQUESTED',
            'budget_notes' => 'Advance request ID: ' . $advance->id
        ]);
        
        Log::channel('budget_management')->info('Budget advance requested', [
            'expense_id' => $expense->id,
            'advance_id' => $advance->id,
            'amount' => number_format($expense->amount, 2)
        ]);
        
        return $advance;
    }
    
    /**
     * Process budget transfer
     */
    private function processBudgetTransfer($expense, $data)
    {
        // Implementation would create a budget transfer request
        Log::channel('budget_management')->info('Processing budget transfer for expense', [
            'expense_id' => $expense->id,
            'amount' => $expense->amount,
            'from_budget' => $data['from_budget_id'] ?? null
        ]);
        
        return true;
    }
    
    /**
     * Process supplementary request
     */
    private function processSupplementaryRequest($expense, $data)
    {
        $flexService = new BudgetFlexibilityService();
        
        $request = $flexService->requestSupplementaryBudget(
            $expense->budget_item_id,
            $expense->amount,
            $data['reason'] ?? 'Supplementary budget for expense #' . $expense->id,
            $data['justification'] ?? 'Budget insufficient for expense'
        );
        
        $expense->update([
            'budget_resolution' => 'SUPPLEMENTARY_REQUESTED',
            'budget_notes' => 'Supplementary request ID: ' . $request->id
        ]);
        
        Log::channel('budget_management')->info('Supplementary budget requested', [
            'expense_id' => $expense->id,
            'request_id' => $request->id,
            'amount' => number_format($expense->amount, 2)
        ]);
        
        return $request;
    }
    
    /**
     * Update expense tracking
     */
    public function updateExpenseTracking($expense)
    {
        if (!$expense->budget_item_id || !$expense->expense_month) {
            return;
        }
        
        $expenseMonth = Carbon::parse($expense->expense_month);
        
        // Find the allocation
        $allocation = BudgetAllocation::where('budget_id', $expense->budget_item_id)
            ->where('year', $expenseMonth->year)
            ->where('period', $expenseMonth->month)
            ->first();
        
        if ($allocation) {
            // Update utilized amount
            $allocation->utilized_amount += $expense->amount;
            $allocation->available_amount = max(0, $allocation->available_amount - $expense->amount);
            $allocation->save();
            
            Log::channel('budget_management')->info('Updated expense tracking', [
                'expense_id' => $expense->id,
                'allocation_id' => $allocation->id,
                'new_utilized' => number_format($allocation->utilized_amount, 2),
                'new_available' => number_format($allocation->available_amount, 2)
            ]);
            
            // Check for alerts
            $monitoringService = new BudgetMonitoringService();
            $monitoringService->checkBudgetAlerts($expense->budget_item_id);
        }
    }
    
    /**
     * Enhanced budget check logging
     */
    public function logBudgetCheck($accountId, $amount, $result, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        $logLevel = ($result['would_exceed'] ?? false) ? 'warning' : 'info';
        $status = ($result['would_exceed'] ?? false) ? 'BUDGET_EXCEEDED' : 'BUDGET_AVAILABLE';
        
        Log::channel('budget_management')->{$logLevel}('════════════════════════════════════════════════════════', []);
        Log::channel('budget_management')->{$logLevel}('📋 BUDGET CHECK FINAL SUMMARY', [
            'account_id' => $accountId,
            'amount' => number_format($amount, 2),
            'status' => $status,
            'budget_item_id' => $result['budget_item_id'] ?? null,
            'allocation_id' => $result['allocation_id'] ?? null,
            'available_budget' => number_format($result['total_available'] ?? 0, 2),
            'over_budget_amount' => number_format($result['over_budget_amount'] ?? 0, 2),
            'utilization' => number_format($result['new_utilization_percentage'] ?? 0, 2) . '%',
            'can_proceed' => $result['can_proceed'] ?? false,
            'available_options' => count($result['available_options'] ?? []),
            'user_id' => $userId,
            'user_name' => $userId ? \App\Models\User::find($userId)?->name : 'Unknown',
            'timestamp' => now()->toIso8601String()
        ]);
        
        // Additional detailed logging for budget exceeded cases
        if ($result['would_exceed'] ?? false) {
            Log::channel('budget_management')->error('❌ EXPENSE SUBMISSION BLOCKED - ACTION REQUIRED', [
                'reason' => 'INSUFFICIENT_BUDGET',
                'account_id' => $accountId,
                'requested_amount' => number_format($amount, 2),
                'available_amount' => number_format($result['total_available'] ?? 0, 2),
                'shortage' => number_format($result['over_budget_amount'] ?? 0, 2),
                'user_id' => $userId,
                'user_name' => $userId ? \App\Models\User::find($userId)?->name : 'Unknown',
                'recommendations' => [
                    '1' => 'Request supplementary budget approval',
                    '2' => 'Request advance from future months',
                    '3' => 'Reduce expense amount to fit budget',
                    '4' => 'Transfer budget from another item',
                    '5' => 'Use rollover from previous months (if available)'
                ],
                'next_steps' => 'User must select one of the available options or cancel the expense'
            ]);
        } else {
            Log::channel('budget_management')->info('✅ EXPENSE APPROVED FOR SUBMISSION', [
                'account_id' => $accountId,
                'amount' => number_format($amount, 2),
                'remaining_budget' => number_format(($result['total_available'] ?? 0) - $amount, 2),
                'utilization_before' => number_format($result['utilization_percentage'] ?? 0, 2) . '%',
                'utilization_after' => number_format($result['new_utilization_percentage'] ?? 0, 2) . '%',
                'status' => 'READY_FOR_APPROVAL_WORKFLOW'
            ]);
        }
        
        Log::channel('budget_management')->{$logLevel}('════════════════════════════════════════════════════════', []);
    }
}