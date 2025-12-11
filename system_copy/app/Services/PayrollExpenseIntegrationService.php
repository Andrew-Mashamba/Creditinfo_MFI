<?php

namespace App\Services;

use App\Models\PayRolls;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Institution;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollExpenseIntegrationService
{
    // GL posting is handled by TransactionPostingService in PayrollPaymentService
    // This service only creates expense records for tracking purposes

    /**
     * Create expense entries from approved payroll
     *
     * @param int $payrollId
     * @return array
     */
    public function createExpenseFromPayroll(int $payrollId)
    {
        DB::beginTransaction();
        try {
            $payroll = PayRolls::with('employee')->findOrFail($payrollId);

            if ($payroll->status !== 'approved') {
                throw new \Exception('Only approved payroll can be converted to expenses');
            }

            Log::channel('budget_management')->info('💼 CREATING EXPENSE FROM PAYROLL', [
                'payroll_id' => $payrollId,
                'employee_id' => $payroll->employee_id,
                'net_salary' => number_format($payroll->net_salary, 2)
            ]);

            $expenses = [];

            // 1. Create expense for Net Salary Payment
            $salaryExpense = $this->createSalaryExpense($payroll);
            $expenses[] = $salaryExpense;

            // 2. Create expense for Statutory Deductions (PAYE, NSSF, NHIF)
            if ($payroll->paye > 0) {
                $expenses[] = $this->createStatutoryExpense($payroll, 'PAYE', $payroll->paye);
            }

            if ($payroll->nssf > 0) {
                $expenses[] = $this->createStatutoryExpense($payroll, 'NSSF', $payroll->nssf);
            }

            if ($payroll->nhif > 0) {
                $expenses[] = $this->createStatutoryExpense($payroll, 'NHIF', $payroll->nhif);
            }

            // NOTE: GL posting is handled by TransactionPostingService in PayrollPaymentService
            // This service only creates expense records for tracking purposes

            // Update payroll status
            $payroll->update(['expense_created' => true]);

            DB::commit();

            Log::channel('budget_management')->info('✅ PAYROLL EXPENSES CREATED', [
                'payroll_id' => $payrollId,
                'expenses_count' => count($expenses),
                'total_amount' => number_format(collect($expenses)->sum('amount'), 2)
            ]);

            return [
                'success' => true,
                'expenses' => $expenses,
                'message' => 'Payroll expenses created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR CREATING PAYROLL EXPENSES', [
                'payroll_id' => $payrollId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create payroll expenses: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create salary expense entry
     */
    private function createSalaryExpense($payroll)
    {
        $salaryAccount = $this->getOrCreateSalaryExpenseAccount();

        $expense = Expense::create([
            'account_id' => $salaryAccount->id,
            'amount' => $payroll->net_salary,
            'description' => 'Salary payment for ' . ($payroll->employee->first_name ?? 'Employee') . ' ' .
                           ($payroll->employee->last_name ?? '') . ' - ' .
                           Carbon::create($payroll->year, $payroll->month, 1)->format('F Y'),
            'payment_type' => 'bank_transfer',
            'user_id' => Auth::id() ?? 1,
            'status' => 'APPROVED', // Auto-approve payroll expenses
            'expense_month' => Carbon::create($payroll->year, $payroll->month, 1),
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id
        ]);

        Log::channel('budget_management')->info('💰 SALARY EXPENSE CREATED', [
            'expense_id' => $expense->id,
            'employee_id' => $payroll->employee_id,
            'amount' => number_format($expense->amount, 2)
        ]);

        return $expense;
    }

    /**
     * Create statutory deduction expense
     */
    private function createStatutoryExpense($payroll, $type, $amount)
    {
        $account = $this->getOrCreateStatutoryExpenseAccount($type);

        $expense = Expense::create([
            'account_id' => $account->id,
            'amount' => $amount,
            'description' => $type . ' payment for ' . ($payroll->employee->first_name ?? 'Employee') . ' ' .
                           ($payroll->employee->last_name ?? '') . ' - ' .
                           Carbon::create($payroll->year, $payroll->month, 1)->format('F Y'),
            'payment_type' => 'bill_payment',
            'user_id' => Auth::id() ?? 1,
            'status' => 'APPROVED',
            'expense_month' => Carbon::create($payroll->year, $payroll->month, 1),
            'payroll_id' => $payroll->id,
            'employee_id' => $payroll->employee_id
        ]);

        Log::channel('budget_management')->info('📋 ' . $type . ' EXPENSE CREATED', [
            'expense_id' => $expense->id,
            'employee_id' => $payroll->employee_id,
            'amount' => number_format($amount, 2)
        ]);

        return $expense;
    }

    /**
     * Get salary expense account (BASE SALARIES - 0101500051005110)
     */
    private function getOrCreateSalaryExpenseAccount()
    {
        // Get from institutions table configuration
        $institution = Institution::first();

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
     * Get or create statutory expense account
     */
    private function getOrCreateStatutoryExpenseAccount($type)
    {
        $accountNames = [
            'PAYE' => 'PAYE TAX EXPENSE',
            'NSSF' => 'NSSF CONTRIBUTION EXPENSE',
            'NHIF' => 'NHIF CONTRIBUTION EXPENSE'
        ];

        $accountName = $accountNames[$type] ?? $type . ' EXPENSE';

        $account = Account::where('account_name', 'LIKE', '%' . $type . '%')
            ->where('major_category_code', '5000')
            ->where('account_level', 3)
            ->first();

        if (!$account) {
            $baseNumber = [
                'PAYE' => '0105500020002010',
                'NSSF' => '0105500030003010',
                'NHIF' => '0105500040004010'
            ][$type] ?? '0105500050005010';

            $account = Account::create([
                'account_name' => $accountName,
                'account_number' => $baseNumber,
                'major_category_code' => '5000',
                'type' => 'expense_accounts',
                'account_type' => 'expense',
                'status' => 'ACTIVE',
                'balance' => 0,
                'account_level' => 3
            ]);
        }

        return $account;
    }

    /**
     * Batch create expenses from multiple approved payroll records
     */
    public function batchCreateExpensesFromPayroll($month, $year)
    {
        $payrolls = PayRolls::where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->where('expense_created', false)
            ->get();

        $results = [
            'successful' => [],
            'failed' => [],
            'total_amount' => 0
        ];

        foreach ($payrolls as $payroll) {
            $result = $this->createExpenseFromPayroll($payroll->id);

            if ($result['success']) {
                $results['successful'][] = $payroll->id;
                $results['total_amount'] += collect($result['expenses'])->sum('amount');
            } else {
                $results['failed'][$payroll->id] = $result['message'];
            }
        }

        return $results;
    }
}
