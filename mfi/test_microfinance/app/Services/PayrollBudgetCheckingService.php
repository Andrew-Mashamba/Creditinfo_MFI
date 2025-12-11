<?php

namespace App\Services;

use App\Models\PayRolls;
use App\Models\BudgetManagement;
use App\Models\BudgetAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollBudgetCheckingService
{
    /**
     * Check if there's sufficient budget for payroll
     * Checks against Salaries & Wages budget items
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function checkPayrollBudget(int $month, int $year)
    {
        try {
            Log::channel('budget_management')->info('💰 CHECKING PAYROLL BUDGET', [
                'month' => $month,
                'year' => $year
            ]);

            // Calculate total payroll amount for the month
            $totalPayroll = $this->calculateMonthlyPayrollAmount($month, $year);

            if ($totalPayroll === 0) {
                return [
                    'success' => false,
                    'message' => 'No pending payroll found for this period',
                    'total_required' => 0
                ];
            }

            // Get salary budget allocation for the month
            $salaryBudget = $this->getSalaryBudgetAllocation($month, $year);

            if (!$salaryBudget) {
                return [
                    'success' => false,
                    'message' => 'No salary budget allocation found for this period',
                    'total_required' => $totalPayroll,
                    'available_budget' => 0,
                    'shortfall' => $totalPayroll
                ];
            }

            // Calculate available budget
            $availableBudget = $salaryBudget->allocated_amount - ($salaryBudget->utilized_amount ?? 0);

            // Check if sufficient budget
            $hasSufficientBudget = $availableBudget >= $totalPayroll;

            $result = [
                'success' => $hasSufficientBudget,
                'total_required' => $totalPayroll,
                'available_budget' => $availableBudget,
                'budget_allocation_id' => $salaryBudget->id,
                'budget_item_id' => $salaryBudget->budget_item_id,
                'utilization_percentage' => ($salaryBudget->allocated_amount > 0)
                    ? (($salaryBudget->utilized_amount + $totalPayroll) / $salaryBudget->allocated_amount) * 100
                    : 0
            ];

            if (!$hasSufficientBudget) {
                $result['message'] = 'Insufficient budget for payroll';
                $result['shortfall'] = $totalPayroll - $availableBudget;
            } else {
                $result['message'] = 'Sufficient budget available';
                $result['surplus'] = $availableBudget - $totalPayroll;
            }

            Log::channel('budget_management')->info(
                $hasSufficientBudget ? '✅ BUDGET CHECK PASSED' : '❌ BUDGET CHECK FAILED',
                $result
            );

            return $result;

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('❌ ERROR CHECKING PAYROLL BUDGET', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error checking budget: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate total payroll amount for the month (including all components)
     */
    private function calculateMonthlyPayrollAmount(int $month, int $year)
    {
        // Sum of gross salaries for all pending payroll
        $total = PayRolls::where('month', $month)
            ->where('year', $year)
            ->whereIn('status', ['pending', 'approved'])
            ->sum('gross_salary');

        return floatval($total);
    }

    /**
     * Get salary budget allocation for the month
     */
    private function getSalaryBudgetAllocation(int $month, int $year)
    {
        // Find budget item for salaries and wages
        $salaryBudgetItem = BudgetManagement::where('budget_name', 'LIKE', '%SALARIES%')
            ->orWhere('budget_name', 'LIKE', '%WAGES%')
            ->orWhere('budget_name', 'LIKE', '%PAYROLL%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$salaryBudgetItem) {
            Log::channel('budget_management')->warning('⚠️ NO SALARY BUDGET ITEM FOUND', [
                'suggestion' => 'Create a budget item for Salaries and Wages'
            ]);
            return null;
        }

        // Get allocation for the specific month
        $allocation = BudgetAllocation::where('budget_id', $salaryBudgetItem->id)
            ->where('period', $month)
            ->where('year', $year)
            ->first();

        if (!$allocation) {
            Log::channel('budget_management')->warning('⚠️ NO BUDGET ALLOCATION FOR PERIOD', [
                'budget_item' => $salaryBudgetItem->budget_name,
                'period' => $month,
                'year' => $year
            ]);
        }

        return $allocation;
    }

    /**
     * Reserve budget for approved payroll (before payment)
     */
    public function reservePayrollBudget(int $month, int $year)
    {
        DB::beginTransaction();
        try {
            $budgetCheck = $this->checkPayrollBudget($month, $year);

            if (!$budgetCheck['success']) {
                throw new \Exception($budgetCheck['message']);
            }

            // Update budget allocation
            $allocation = BudgetAllocation::find($budgetCheck['budget_allocation_id']);

            $newUtilized = ($allocation->utilized_amount ?? 0) + $budgetCheck['total_required'];
            $newAvailable = $allocation->allocated_amount - $newUtilized;

            $allocation->update([
                'utilized_amount' => $newUtilized,
                'available_amount' => $newAvailable
            ]);

            DB::commit();

            Log::channel('budget_management')->info('✅ PAYROLL BUDGET RESERVED', [
                'month' => $month,
                'year' => $year,
                'amount_reserved' => number_format($budgetCheck['total_required'], 2),
                'new_utilized' => number_format($newUtilized, 2),
                'new_available' => number_format($newAvailable, 2)
            ]);

            return [
                'success' => true,
                'message' => 'Budget reserved successfully',
                'amount_reserved' => $budgetCheck['total_required']
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR RESERVING BUDGET', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reserve budget: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get detailed budget breakdown for payroll
     */
    public function getPayrollBudgetBreakdown(int $month, int $year)
    {
        $payrolls = PayRolls::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        $breakdown = [
            'total_gross' => 0,
            'total_basic' => 0,
            'total_allowances' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'by_department' => [],
            'by_employee' => []
        ];

        foreach ($payrolls as $payroll) {
            $breakdown['total_gross'] += $payroll->gross_salary;
            $breakdown['total_basic'] += $payroll->basic_salary ?? 0;
            $breakdown['total_allowances'] += ($payroll->house_allowance ?? 0) + ($payroll->transport_allowance ?? 0);
            $breakdown['total_deductions'] += $payroll->total_deductions;
            $breakdown['total_net'] += $payroll->net_salary;

            // By employee
            $breakdown['by_employee'][] = [
                'employee_id' => $payroll->employee_id,
                'employee_name' => ($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->last_name ?? ''),
                'gross_salary' => $payroll->gross_salary,
                'net_salary' => $payroll->net_salary
            ];
        }

        return $breakdown;
    }
}
