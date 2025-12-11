<?php

namespace App\Services;

use App\Models\PayRolls;
use App\Models\Employee;
use App\Models\Account;
use App\Models\AccountsModel;
use App\Services\PayrollExpenseIntegrationService;
use App\Services\TransactionPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayrollPaymentService
{
    private $expenseIntegrationService;
    private $transactionPostingService;

    public function __construct()
    {
        $this->expenseIntegrationService = new PayrollExpenseIntegrationService();
        $this->transactionPostingService = new TransactionPostingService();
    }

    /**
     * Process payment for approved payroll (entire month)
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public function processMonthlyPayroll(int $month, int $year)
    {
        Log::channel('budget_management')->info('════════════════════════════════════════════════════════');
        Log::channel('budget_management')->info('🚀 STARTING MONTHLY PAYROLL PROCESSING', [
            'month' => $month,
            'year' => $year
        ]);

        $payrolls = PayRolls::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->get();

        Log::channel('budget_management')->info('📋 FOUND APPROVED PAYROLLS', [
            'count' => $payrolls->count(),
            'payroll_ids' => $payrolls->pluck('id')->toArray()
        ]);

        if ($payrolls->isEmpty()) {
            Log::channel('budget_management')->warning('⚠️ NO APPROVED PAYROLLS FOUND', [
                'month' => $month,
                'year' => $year
            ]);
            return [
                'success' => false,
                'message' => 'No approved payroll found for this period'
            ];
        }

        $results = [
            'successful' => [],
            'failed' => [],
            'total_amount' => 0,
            'total_count' => $payrolls->count()
        ];

        foreach ($payrolls as $payroll) {
            Log::channel('budget_management')->info('➡️ PROCESSING PAYROLL', [
                'payroll_id' => $payroll->id,
                'employee_id' => $payroll->employee_id,
                'employee_name' => ($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->last_name ?? ''),
                'net_salary' => number_format($payroll->net_salary, 2)
            ]);

            $result = $this->processPayrollPayment($payroll->id);

            if ($result['success']) {
                Log::channel('budget_management')->info('✅ PAYROLL PROCESSED SUCCESSFULLY', [
                    'payroll_id' => $payroll->id,
                    'amount' => $payroll->net_salary
                ]);
                $results['successful'][] = [
                    'payroll_id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'amount' => $payroll->net_salary
                ];
                $results['total_amount'] += $payroll->net_salary;
            } else {
                Log::channel('budget_management')->error('❌ PAYROLL PROCESSING FAILED', [
                    'payroll_id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'error' => $result['message']
                ]);
                $results['failed'][] = [
                    'payroll_id' => $payroll->id,
                    'employee_id' => $payroll->employee_id,
                    'error' => $result['message']
                ];
            }
        }

        Log::channel('budget_management')->info('📊 MONTHLY PAYROLL PROCESSING COMPLETED', [
            'successful' => count($results['successful']),
            'failed' => count($results['failed']),
            'total' => $results['total_count'],
            'total_amount' => number_format($results['total_amount'], 2)
        ]);
        Log::channel('budget_management')->info('════════════════════════════════════════════════════════');

        return [
            'success' => count($results['failed']) === 0,
            'message' => sprintf(
                'Processed %d/%d payrolls successfully',
                count($results['successful']),
                $results['total_count']
            ),
            'results' => $results
        ];
    }

    /**
     * Process payment for single payroll entry
     *
     * @param int $payrollId
     * @return array
     */
    public function processPayrollPayment(int $payrollId)
    {
        DB::beginTransaction();
        try {
            Log::channel('budget_management')->info('🔍 STEP 1: Loading payroll record', [
                'payroll_id' => $payrollId
            ]);

            $payroll = PayRolls::with('employee')->findOrFail($payrollId);

            Log::channel('budget_management')->info('✓ Payroll loaded', [
                'status' => $payroll->status,
                'employee_id' => $payroll->employee_id,
                'has_employee' => $payroll->employee ? 'yes' : 'NO'
            ]);

            if ($payroll->status !== 'approved') {
                throw new \Exception('Payroll must be approved before payment. Current status: ' . $payroll->status);
            }

            if (!$payroll->employee) {
                throw new \Exception('Employee record not found for payroll ID ' . $payrollId);
            }

            Log::channel('budget_management')->info('💳 PROCESSING PAYROLL PAYMENT', [
                'payroll_id' => $payrollId,
                'employee_id' => $payroll->employee_id,
                'employee_name' => ($payroll->employee->first_name ?? '') . ' ' . ($payroll->employee->last_name ?? ''),
                'net_salary' => number_format($payroll->net_salary, 2)
            ]);

            // 1. Get or create employee deposit account
            Log::channel('budget_management')->info('🔍 STEP 2: Getting employee deposit account');
            $employeeAccount = $this->getOrCreateEmployeeDepositAccount($payroll->employee);
            Log::channel('budget_management')->info('✓ Employee account retrieved', [
                'account_number' => $employeeAccount->account_number,
                'account_name' => $employeeAccount->account_name
            ]);

            // 2. Get salary expense account (not bank - this is expense to liability/asset flow)
            Log::channel('budget_management')->info('🔍 STEP 3: Getting salary expense account');
            $salaryExpenseAccount = $this->getSalaryExpenseAccount();
            Log::channel('budget_management')->info('✓ Salary expense account retrieved', [
                'account_number' => $salaryExpenseAccount->account_number,
                'account_name' => $salaryExpenseAccount->account_name
            ]);

            // 3. Post salary expense to employee deposit account
            // Transaction 1: Dr: BASE SALARIES (GROSS), Cr: Employee Deposit (GROSS)
            Log::channel('budget_management')->info('🔍 STEP 4: Posting gross salary transaction', [
                'expense_account' => $salaryExpenseAccount->account_number,
                'employee_account' => $employeeAccount->account_number,
                'gross_amount' => $payroll->gross_salary
            ]);

            $transactionResult = $this->transactionPostingService->postTransaction([
                'first_account' => $salaryExpenseAccount->account_number,  // BASE SALARIES (expense - will be debited)
                'second_account' => $employeeAccount->account_number,  // Employee deposit (will be credited)
                'amount' => $payroll->gross_salary,
                'narration' => 'Salary payment for ' . Carbon::create($payroll->year, $payroll->month, 1)->format('F Y'),
                'action' => 'salary_payment'
            ]);

            Log::channel('budget_management')->info('✓ Gross salary posted', [
                'reference_number' => $transactionResult['reference_number'] ?? 'N/A',
                'gross_salary' => number_format($payroll->gross_salary, 2)
            ]);

            // 4. Post statutory deductions as liabilities
            $this->postStatutoryDeductions($payroll, $employeeAccount);

            Log::channel('budget_management')->info('✓ All transactions posted');

            // 5. Create expense entries via PayrollExpenseIntegrationService
            Log::channel('budget_management')->info('🔍 STEP 5: Creating expense entries');
            $expenseResult = $this->expenseIntegrationService->createExpenseFromPayroll($payrollId);

            if (!$expenseResult['success']) {
                throw new \Exception('Failed to create expense entries: ' . $expenseResult['message']);
            }

            Log::channel('budget_management')->info('✓ Expense entries created');

            // 6. Update payroll status to paid
            Log::channel('budget_management')->info('🔍 STEP 6: Updating payroll status to paid');
            $payroll->update([
                'status' => 'paid',
                'payment_date' => now(),
                'payment_method' => 'internal_transfer'
            ]);

            Log::channel('budget_management')->info('✓ Payroll status updated');

            DB::commit();

            Log::channel('budget_management')->info('✅ PAYROLL PAYMENT PROCESSED SUCCESSFULLY', [
                'payroll_id' => $payrollId,
                'employee_account' => $employeeAccount->account_number,
                'reference_number' => $transactionResult['reference_number'] ?? 'N/A',
                'net_salary' => number_format($payroll->net_salary, 2)
            ]);

            return [
                'success' => true,
                'message' => 'Payroll payment processed successfully',
                'payroll_id' => $payrollId,
                'reference_number' => $transactionResult['reference_number'] ?? null,
                'employee_account' => $employeeAccount->account_number,
                'amount' => $payroll->net_salary
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ PAYROLL PAYMENT FAILED', [
                'payroll_id' => $payrollId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Post statutory deductions (PAYE, NSSF, NHIF) to GL as liabilities
     *
     * For each deduction:
     * Dr: Employee Deposit Account (reduces what we owe employee)
     * Cr: Statutory Payable Account (increases what we owe government)
     */
    private function postStatutoryDeductions($payroll, $employeeAccount)
    {
        $institution = \App\Models\Institution::first();

        if (!$institution) {
            Log::channel('budget_management')->error('Institution configuration not found');
            throw new \Exception('Institution configuration not found');
        }

        // Post PAYE deduction
        if ($payroll->paye > 0) {
            $this->postDeductionToGL(
                'PAYE',
                $payroll->paye,
                $employeeAccount,
                $institution->paye_payable_account,
                $payroll
            );
        }

        // Post NSSF deduction
        if ($payroll->nssf > 0) {
            $this->postDeductionToGL(
                'NSSF',
                $payroll->nssf,
                $employeeAccount,
                $institution->nssf_payable_account,
                $payroll
            );
        }

        // Post NHIF deduction
        if ($payroll->nhif > 0) {
            $this->postDeductionToGL(
                'NHIF',
                $payroll->nhif,
                $employeeAccount,
                $institution->nhif_payable_account,
                $payroll
            );
        }
    }

    /**
     * Post a single deduction to GL
     */
    private function postDeductionToGL($deductionType, $amount, $employeeAccount, $payableAccountNumber, $payroll)
    {
        if (!$payableAccountNumber) {
            Log::channel('budget_management')->error('Payable account not configured', [
                'deduction_type' => $deductionType
            ]);
            throw new \Exception($deductionType . ' payable account not configured in institutions table');
        }

        $payableAccount = Account::where('account_number', $payableAccountNumber)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$payableAccount) {
            throw new \Exception($deductionType . ' payable account (' . $payableAccountNumber . ') not found or not active');
        }

        Log::channel('budget_management')->info('📋 Posting ' . $deductionType . ' deduction', [
            'amount' => number_format($amount, 2),
            'employee_account' => $employeeAccount->account_number,
            'payable_account' => $payableAccount->account_number
        ]);

        // Dr: Employee Deposit (reduces what we owe employee)
        // Cr: Statutory Payable (increases what we owe government)
        // Both are liabilities, so we need to specify source/destination
        $result = $this->transactionPostingService->postTransaction([
            'first_account' => $employeeAccount->account_number,  // Employee deposit (will be debited - source)
            'second_account' => $payableAccount->account_number,  // Statutory payable (will be credited - destination)
            'amount' => $amount,
            'narration' => $deductionType . ' deduction for ' . Carbon::create($payroll->year, $payroll->month, 1)->format('F Y'),
            'action' => 'statutory_deduction',
            'source_account' => $employeeAccount->account_number,  // Source: reduce what we owe employee
            'destination_account' => $payableAccount->account_number  // Destination: increase what we owe government
        ]);

        Log::channel('budget_management')->info('✓ ' . $deductionType . ' posted', [
            'reference_number' => $result['reference_number'] ?? 'N/A',
            'amount' => number_format($amount, 2)
        ]);
    }

    /**
     * Get or create employee deposit account
     */
    private function getOrCreateEmployeeDepositAccount(Employee $employee)
    {
        Log::channel('budget_management')->info('🔍 Searching for employee deposit account', [
            'employee_id' => $employee->id,
            'member_number' => $employee->member_number,
            'employee_name' => ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')
        ]);

        // Try to find existing deposit account for the employee (client)
        // Use member_number which matches clients.client_number and accounts.client_number
        $account = AccountsModel::where('client_number', $employee->member_number)
            ->where('type', 'liability_accounts')
            ->where('status', 'ACTIVE')
            ->first();

        if ($account) {
            Log::channel('budget_management')->info('✓ Found existing member deposit account', [
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'balance' => $account->balance ?? 0
            ]);
            return $account;
        }

        // Account not found - employee must be registered as member first with deposit account
        throw new \Exception('Employee ' . $employee->first_name . ' ' . $employee->last_name . ' (member_number: ' . $employee->member_number . ') does not have a member deposit account. Please create member deposit account first.');
    }

    /**
     * Get salary expense account (BASE SALARIES - 0101500051005110)
     */
    private function getSalaryExpenseAccount()
    {
        // Get from institutions table configuration
        $institution = \App\Models\Institution::first();

        if (!$institution || !$institution->personnel_expenses_account) {
            throw new \Exception('Institution personnel_expenses_account not configured. Please configure in institutions table.');
        }

        $account = Account::where('account_number', $institution->personnel_expenses_account)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$account) {
            throw new \Exception('Personnel expenses account (' . $institution->personnel_expenses_account . ') not found or not active in accounts table.');
        }

        Log::channel('budget_management')->info('✓ Using BASE SALARIES expense account', [
            'account_number' => $account->account_number,
            'account_name' => $account->account_name
        ]);

        return $account;
    }

    /**
     * Generate unique account number for member deposit under MEMBER DEPOSITS parent
     * Format: 0101200021 (parent prefix) + client_id (padded) + sequence
     */
    private function generateMemberDepositAccountNumber(Employee $employee)
    {
        $clientCode = str_pad($employee->client_id, 5, '0', STR_PAD_LEFT);

        // Count existing accounts for this client under MEMBER DEPOSITS
        $existingCount = AccountsModel::where('client_number', $employee->client_id)
            ->where('parent_account_number', '010120002100')
            ->count();

        $sequence = str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        // Format: 0101200021 (parent) + 00 (sub) + clientCode + sequence
        return '0101200021' . '00' . $clientCode . $sequence;
    }

    /**
     * Get payroll payment summary
     */
    public function getPayrollPaymentSummary(int $month, int $year)
    {
        $payrolls = PayRolls::with('employee')
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        $summary = [
            'total_payrolls' => $payrolls->count(),
            'approved' => $payrolls->where('status', 'approved')->count(),
            'paid' => $payrolls->where('status', 'paid')->count(),
            'pending' => $payrolls->where('status', 'pending')->count(),
            'total_gross' => $payrolls->sum('gross_salary'),
            'total_net' => $payrolls->sum('net_salary'),
            'total_deductions' => $payrolls->sum('total_deductions')
        ];

        return $summary;
    }
}
