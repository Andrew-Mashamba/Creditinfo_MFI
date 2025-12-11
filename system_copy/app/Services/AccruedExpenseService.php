<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccruedExpenseService
{
    private $transactionPostingService;

    public function __construct()
    {
        $this->transactionPostingService = new TransactionPostingService();
    }
    /**
     * Record accrued expense (expense incurred but not yet paid)
     *
     * @param array $data
     * @return array
     */
    public function recordAccruedExpense(array $data)
    {
        DB::beginTransaction();
        try {
            Log::channel('budget_management')->info('📋 RECORDING ACCRUED EXPENSE', [
                'account_id' => $data['account_id'],
                'amount' => number_format($data['amount'], 2),
                'description' => $data['description']
            ]);

            // Create expense record with accrued status
            $expenseData = [
                'account_id' => $data['account_id'],
                'amount' => $data['amount'],
                'description' => $data['description'] . ' (Accrued)',
                'payment_type' => 'accrual',
                'user_id' => Auth::id(),
                'status' => 'ACCRUED',
                'expense_month' => $data['expense_month'] ?? now()->format('Y-m-d'),
                'is_accrued' => true,
                'accrual_date' => $data['accrual_date'] ?? now(),
                'expected_payment_date' => $data['expected_payment_date'] ?? null,
                'budget_item_id' => $data['budget_item_id'] ?? null,
                'budget_allocation_id' => $data['budget_allocation_id'] ?? null
            ];

            $expense = Expense::create($expenseData);

            // Post accrual to general ledger
            // Debit: Expense Account (Increase Expense)
            // Credit: Accrued Expenses Payable (Increase Liability)
            $this->postAccrualToGL($expense, $data);

            DB::commit();

            Log::channel('budget_management')->info('✅ ACCRUED EXPENSE RECORDED', [
                'expense_id' => $expense->id,
                'amount' => number_format($expense->amount, 2),
                'accrual_date' => $expense->accrual_date
            ]);

            return [
                'success' => true,
                'expense_id' => $expense->id,
                'message' => 'Accrued expense recorded successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR RECORDING ACCRUED EXPENSE', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to record accrued expense: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Post accrual to general ledger via TransactionPostingService
     * Transaction: Debit Expense, Credit Accrued Expenses Payable (Liability)
     */
    private function postAccrualToGL($expense, $data)
    {
        $expenseAccount = Account::find($expense->account_id);
        $accruedExpensesAccount = $this->getOrCreateAccruedExpensesAccount();

        $narration = 'Accrued expense: ' . $expense->description;

        Log::channel('budget_management')->info('📒 POSTING ACCRUAL TO GENERAL LEDGER VIA TRANSACTION POSTING SERVICE', [
            'expense_id' => $expense->id,
            'expense_account' => $expenseAccount->account_number,
            'accrued_account' => $accruedExpensesAccount->account_number,
            'amount' => number_format($expense->amount, 2)
        ]);

        try {
            // Use TransactionPostingService for GL posting
            $result = $this->transactionPostingService->postTransaction([
                'first_account' => $expenseAccount->account_number,          // Expense (will be debited)
                'second_account' => $accruedExpensesAccount->account_number, // Liability (will be credited)
                'amount' => $expense->amount,
                'narration' => $narration,
                'action' => 'accrued_expense'
            ]);

            Log::channel('budget_management')->info('✅ ACCRUAL POSTED TO GENERAL LEDGER', [
                'expense_id' => $expense->id,
                'reference_number' => $result['reference_number'],
                'status' => $result['status']
            ]);

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('❌ ACCRUAL GL POSTING FAILED', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Realize accrued expense (when payment is made)
     *
     * @param int $expenseId
     * @param array $paymentData
     * @return array
     */
    public function realizeAccruedExpense(int $expenseId, array $paymentData)
    {
        DB::beginTransaction();
        try {
            $expense = Expense::findOrFail($expenseId);

            if (!$expense->is_accrued || $expense->status !== 'ACCRUED') {
                throw new \Exception('This expense is not an accrued expense');
            }

            Log::channel('budget_management')->info('💰 REALIZING ACCRUED EXPENSE', [
                'expense_id' => $expenseId,
                'amount' => number_format($expense->amount, 2),
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer'
            ]);

            // Update expense status
            $expense->update([
                'status' => 'PAID',
                'payment_date' => now(),
                'payment_method' => $paymentData['payment_method'] ?? 'bank_transfer',
                'payment_reference' => $this->generatePaymentReference($expense),
                'paid_by_user_id' => Auth::id(),
                'realization_date' => now()
            ]);

            // Post realization to GL
            // Debit: Accrued Expenses Payable (Decrease Liability)
            // Credit: Cash/Bank (Decrease Asset)
            $this->postRealizationToGL($expense, $paymentData);

            DB::commit();

            Log::channel('budget_management')->info('✅ ACCRUED EXPENSE REALIZED', [
                'expense_id' => $expense->id,
                'payment_reference' => $expense->payment_reference,
                'amount' => number_format($expense->amount, 2)
            ]);

            return [
                'success' => true,
                'expense_id' => $expense->id,
                'payment_reference' => $expense->payment_reference,
                'message' => 'Accrued expense payment processed successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('budget_management')->error('❌ ERROR REALIZING ACCRUED EXPENSE', [
                'expense_id' => $expenseId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to realize accrued expense: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Post realization to general ledger via TransactionPostingService
     * Transaction: Debit Accrued Expenses Payable (Liability), Credit Cash/Bank (Asset)
     */
    private function postRealizationToGL($expense, $paymentData)
    {
        $accruedExpensesAccount = $this->getOrCreateAccruedExpensesAccount();
        $paymentAccount = $this->getPaymentAccount($paymentData['payment_method'] ?? 'bank_transfer');

        $narration = 'Payment of accrued expense: ' . $expense->description;

        Log::channel('budget_management')->info('📒 POSTING REALIZATION TO GENERAL LEDGER VIA TRANSACTION POSTING SERVICE', [
            'expense_id' => $expense->id,
            'accrued_account' => $accruedExpensesAccount->account_number,
            'payment_account' => $paymentAccount->account_number,
            'amount' => number_format($expense->amount, 2)
        ]);

        try {
            // Use TransactionPostingService for GL posting
            $result = $this->transactionPostingService->postTransaction([
                'first_account' => $accruedExpensesAccount->account_number, // Liability (will be debited)
                'second_account' => $paymentAccount->account_number,        // Asset (will be credited)
                'amount' => $expense->amount,
                'narration' => $narration,
                'action' => 'accrued_expense_payment'
            ]);

            Log::channel('budget_management')->info('✅ REALIZATION POSTED TO GENERAL LEDGER', [
                'expense_id' => $expense->id,
                'reference_number' => $result['reference_number'],
                'status' => $result['status']
            ]);

        } catch (\Exception $e) {
            Log::channel('budget_management')->error('❌ REALIZATION GL POSTING FAILED', [
                'expense_id' => $expense->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get or create Accrued Expenses Payable account
     */
    private function getOrCreateAccruedExpensesAccount()
    {
        $account = Account::where('account_name', 'LIKE', '%ACCRUED%EXPENSES%PAYABLE%')
            ->where('major_category_code', '2000') // Liability accounts
            ->where('type', 'liability_accounts')
            ->first();

        if (!$account) {
            // Create the account if it doesn't exist
            $account = Account::create([
                'account_name' => 'ACCRUED EXPENSES PAYABLE',
                'account_number' => '0102200015001510', // Adjust based on your chart of accounts
                'major_category_code' => '2000',
                'type' => 'liability_accounts',
                'account_type' => 'liability',
                'status' => 'ACTIVE',
                'balance' => 0,
                'account_level' => 3
            ]);

            Log::channel('budget_management')->info('✨ CREATED ACCRUED EXPENSES PAYABLE ACCOUNT', [
                'account_id' => $account->id,
                'account_number' => $account->account_number
            ]);
        }

        return $account;
    }

    /**
     * Get payment account based on payment method
     */
    private function getPaymentAccount($paymentMethod)
    {
        $accountPattern = str_contains($paymentMethod, 'cash') ? '%Cash%' : '%Bank%';

        $account = Account::where('major_category_code', '1000') // Asset accounts
            ->where('account_name', 'LIKE', $accountPattern)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$account) {
            throw new \Exception('No payment account found');
        }

        return $account;
    }

    /**
     * Generate payment reference
     */
    private function generatePaymentReference($expense)
    {
        return 'PAY-ACCR-' . date('Ymd') . '-' . str_pad($expense->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get all accrued expenses
     */
    public function getAccruedExpenses(array $filters = [])
    {
        $query = Expense::query()
            ->with(['account', 'user', 'budgetItem'])
            ->where('is_accrued', true)
            ->where('status', 'ACCRUED');

        if (!empty($filters['account_id'])) {
            $query->where('account_id', $filters['account_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('accrual_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('accrual_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('accrual_date', 'desc')->get();
    }

    /**
     * Get accrued expenses aging report
     */
    public function getAccruedExpensesAging()
    {
        $today = Carbon::now();

        $aging = Expense::query()
            ->where('is_accrued', true)
            ->where('status', 'ACCRUED')
            ->select([
                DB::raw('CASE
                    WHEN EXTRACT(DAY FROM (NOW() - accrual_date)) <= 30 THEN \'0-30 days\'
                    WHEN EXTRACT(DAY FROM (NOW() - accrual_date)) <= 60 THEN \'31-60 days\'
                    WHEN EXTRACT(DAY FROM (NOW() - accrual_date)) <= 90 THEN \'61-90 days\'
                    ELSE \'Over 90 days\'
                END as age_group'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            ])
            ->groupBy('age_group')
            ->get();

        return $aging;
    }
}
