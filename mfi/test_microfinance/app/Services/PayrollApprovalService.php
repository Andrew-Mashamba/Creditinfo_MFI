<?php

namespace App\Services;

use App\Models\PayRolls;
use App\Models\Approval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollApprovalService
{
    private $budgetCheckingService;

    public function __construct()
    {
        $this->budgetCheckingService = new PayrollBudgetCheckingService();
    }

    /**
     * Submit payroll for approval (bulk submit for a month)
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function submitPayrollForApproval(int $month, int $year)
    {
        DB::beginTransaction();
        try {
            // 1. Check if payroll exists
            $payrolls = PayRolls::where('month', $month)
                ->where('year', $year)
                ->where('status', 'pending')
                ->get();

            if ($payrolls->isEmpty()) {
                throw new \Exception('No pending payroll found for this period');
            }

            Log::channel('budget_management')->info('📋 SUBMITTING PAYROLL FOR APPROVAL', [
                'month' => $month,
                'year' => $year,
                'payroll_count' => $payrolls->count(),
                'submitted_by' => Auth::id()
            ]);

            // 2. Check budget availability
            $budgetCheck = $this->budgetCheckingService->checkPayrollBudget($month, $year);

            if (!$budgetCheck['success']) {
                throw new \Exception('Insufficient budget: ' . $budgetCheck['message']);
            }

            // 3. Create approval record for the entire payroll batch
            $approval = Approval::create([
                'process_code' => 'PAYROLL',
                'process_id' => $month . '-' . $year, // Unique identifier for month/year
                'process_name' => 'Payroll Approval',
                'process_description' => 'Payroll approval for ' . Carbon::create($year, $month, 1)->format('F Y'),
                'user_id' => Auth::id(),
                'approval_status' => 'PENDING',
                'process_status' => 'PENDING',
                'approval_category' => 'PAYROLL',
                'additional_data' => [
                    'month' => $month,
                    'year' => $year,
                    'payroll_count' => $payrolls->count(),
                    'total_gross' => $payrolls->sum('gross_salary'),
                    'total_net' => $payrolls->sum('net_salary'),
                    'budget_check' => $budgetCheck
                ],
                'submitted_at' => now()
            ]);

            // 4. Update individual payroll records
            foreach ($payrolls as $payroll) {
                $payroll->update([
                    'status' => 'submitted_for_approval'
                ]);
            }

            DB::commit();

            Log::channel('budget_management')->info('✅ PAYROLL SUBMITTED FOR APPROVAL', [
                'approval_id' => $approval->id,
                'month' => $month,
                'year' => $year,
                'total_amount' => number_format($payrolls->sum('gross_salary'), 2)
            ]);

            return [
                'success' => true,
                'message' => 'Payroll submitted for approval successfully',
                'approval_id' => $approval->id,
                'payroll_count' => $payrolls->count(),
                'total_amount' => $payrolls->sum('gross_salary')
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR SUBMITTING PAYROLL', [
                'month' => $month,
                'year' => $year,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to submit payroll: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Approve payroll (single approval)
     *
     * @param string $processId (format: "month-year")
     * @param string $comments
     * @return array
     */
    public function approvePayroll(string $processId, string $comments = null)
    {
        DB::beginTransaction();
        try {
            // 1. Find approval record
            $approval = Approval::where('process_code', 'PAYROLL')
                ->where('process_id', $processId)
                ->where('approval_status', 'PENDING')
                ->firstOrFail();

            $additionalData = $approval->additional_data ?? [];
            $month = $additionalData['month'];
            $year = $additionalData['year'];

            Log::channel('budget_management')->info('✔️ APPROVING PAYROLL', [
                'approval_id' => $approval->id,
                'month' => $month,
                'year' => $year,
                'approved_by' => Auth::id()
            ]);

            // 2. Re-check budget (in case allocations changed)
            $budgetCheck = $this->budgetCheckingService->checkPayrollBudget($month, $year);

            if (!$budgetCheck['success']) {
                throw new \Exception('Budget no longer sufficient: ' . $budgetCheck['message']);
            }

            // 3. Update approval record
            $approval->update([
                'approval_status' => 'APPROVED',
                'process_status' => 'APPROVED',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $comments
            ]);

            // 4. Update payroll records to approved
            PayRolls::where('month', $month)
                ->where('year', $year)
                ->where('status', 'submitted_for_approval')
                ->update([
                    'status' => 'approved',
                    'updated_at' => now()
                ]);

            // 5. Reserve budget
            $reserveResult = $this->budgetCheckingService->reservePayrollBudget($month, $year);

            if (!$reserveResult['success']) {
                throw new \Exception('Failed to reserve budget: ' . $reserveResult['message']);
            }

            DB::commit();

            Log::channel('budget_management')->info('✅ PAYROLL APPROVED', [
                'approval_id' => $approval->id,
                'month' => $month,
                'year' => $year,
                'budget_reserved' => $reserveResult['amount_reserved']
            ]);

            return [
                'success' => true,
                'message' => 'Payroll approved successfully',
                'approval_id' => $approval->id,
                'budget_reserved' => $reserveResult['amount_reserved']
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR APPROVING PAYROLL', [
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to approve payroll: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reject payroll
     *
     * @param string $processId
     * @param string $reason
     * @return array
     */
    public function rejectPayroll(string $processId, string $reason)
    {
        DB::beginTransaction();
        try {
            $approval = Approval::where('process_code', 'PAYROLL')
                ->where('process_id', $processId)
                ->where('approval_status', 'PENDING')
                ->firstOrFail();

            $additionalData = $approval->additional_data ?? [];
            $month = $additionalData['month'];
            $year = $additionalData['year'];

            Log::channel('budget_management')->info('❌ REJECTING PAYROLL', [
                'approval_id' => $approval->id,
                'month' => $month,
                'year' => $year,
                'rejected_by' => Auth::id(),
                'reason' => $reason
            ]);

            // Update approval record
            $approval->update([
                'approval_status' => 'REJECTED',
                'process_status' => 'REJECTED',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => 'REJECTED: ' . $reason
            ]);

            // Revert payroll records to pending
            PayRolls::where('month', $month)
                ->where('year', $year)
                ->where('status', 'submitted_for_approval')
                ->update([
                    'status' => 'pending',
                    'updated_at' => now()
                ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payroll rejected',
                'approval_id' => $approval->id
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR REJECTING PAYROLL', [
                'process_id' => $processId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to reject payroll: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get payroll approval status
     */
    public function getPayrollApprovalStatus(int $month, int $year)
    {
        $processId = $month . '-' . $year;

        $approval = Approval::where('process_code', 'PAYROLL')
            ->where('process_id', $processId)
            ->first();

        if (!$approval) {
            return [
                'status' => 'NOT_SUBMITTED',
                'process_status' => 'NOT_SUBMITTED',
                'message' => 'Payroll has not been submitted for approval'
            ];
        }

        $additionalData = $approval->additional_data ?? [];

        return [
            'status' => $approval->approval_status,
            'process_status' => $approval->process_status,
            'approval_id' => $approval->id,
            'submitted_by' => $approval->user_id,
            'submitted_at' => $approval->submitted_at,
            'approved_by' => $approval->approved_by,
            'approved_at' => $approval->approved_at,
            'comments' => $approval->approval_notes,
            'payroll_count' => $additionalData['payroll_count'] ?? 0,
            'total_amount' => $additionalData['total_gross'] ?? 0
        ];
    }
}
