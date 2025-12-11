<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountsModel;
use Exception;

class SavingsInterestAccrualService
{
    protected $taxRate;
    protected $transactionService;

    // GL Accounts (loaded from institutions table)
    protected $savingsInterestExpenseAccount;
    protected $depositsInterestExpenseAccount;
    protected $interestPayableAccount;

    public function __construct()
    {
        // Tax withholding rate (Tanzania: 15%)
        $this->taxRate = config('interest.tax_rate', 15.00);

        $this->transactionService = new TransactionPostingService();
        $this->loadInstitutionAccounts();
    }

    /**
     * Load GL accounts from institutions table
     */
    protected function loadInstitutionAccounts(): void
    {
        $institution = DB::table('institutions')->where('id', 1)->first();

        if ($institution) {
            $this->savingsInterestExpenseAccount = $institution->savings_interest_expense_account ?? '0101500050005030';
            $this->depositsInterestExpenseAccount = $institution->deposits_interest_expense_account ?? '0101500050005025';
            $this->interestPayableAccount = $institution->savings_interest_payable_account ?? '0101200029002910';
        } else {
            // Fallback to defaults
            $this->savingsInterestExpenseAccount = '0101500050005030'; // INTEREST ON SAVINGS
            $this->depositsInterestExpenseAccount = '0101500050005025'; // INTEREST ON DEPOSITS
            $this->interestPayableAccount = '0101200029002910'; // ACCRUED INTEREST PAYABLE
        }

        Log::info('[INTEREST_ACCRUAL] GL accounts loaded', [
            'savings_interest_expense' => $this->savingsInterestExpenseAccount,
            'deposits_interest_expense' => $this->depositsInterestExpenseAccount,
            'interest_payable' => $this->interestPayableAccount
        ]);
    }

    /**
     * Process daily interest accrual for all active savings accounts
     * This should run once per day at end of business day
     *
     * @param Carbon|null $date The date to process (defaults to yesterday)
     * @return array Processing results
     */
    public function processDailyAccrual($date = null)
    {
        try {
            // Default to yesterday (end of previous business day)
            $accrualDate = $date ?? Carbon::yesterday();

            Log::info('📊 Starting daily interest accrual processing', [
                'date' => $accrualDate->format('Y-m-d')
            ]);

            DB::beginTransaction();

            // Check if already processed for this date
            $existingAccruals = DB::table('interest_accruals')
                ->where('accrual_date', $accrualDate->format('Y-m-d'))
                ->count();

            if ($existingAccruals > 0) {
                Log::warning('⚠️ Interest accrual already processed for this date', [
                    'date' => $accrualDate->format('Y-m-d'),
                    'existing_records' => $existingAccruals
                ]);
                DB::rollBack();
                return [
                    'status' => 'skipped',
                    'message' => 'Already processed for this date',
                    'date' => $accrualDate->format('Y-m-d'),
                    'existing_records' => $existingAccruals
                ];
            }

            // Get all active savings accounts (product_number = 2000)
            // AND all active deposit accounts (product_number = 3000)
            $savingsAccounts = AccountsModel::whereIn('product_number', [2000, 3000])
                ->where('status', 'ACTIVE')
                ->where('client_number', '!=', '0000')
                ->get();

            $processedCount = 0;
            $totalInterestAccrued = 0;
            $savingsInterest = 0;
            $depositsInterest = 0;
            $savingsCount = 0;
            $depositsCount = 0;
            $errors = [];

            foreach ($savingsAccounts as $account) {
                try {
                    $result = $this->accrueInterestForAccount($account, $accrualDate);

                    if ($result['status'] === 'success') {
                        $processedCount++;
                        $totalInterestAccrued += $result['interest_amount'];

                        // Track savings vs deposits separately
                        if ($account->product_number == 2000) {
                            $savingsInterest += $result['interest_amount'];
                            $savingsCount++;
                        } else if ($account->product_number == 3000) {
                            $depositsInterest += $result['interest_amount'];
                            $depositsCount++;
                        }
                    }
                } catch (Exception $e) {
                    $errors[] = [
                        'account_number' => $account->account_number,
                        'product_type' => $account->product_number == 2000 ? 'SAVINGS' : 'DEPOSIT',
                        'error' => $e->getMessage()
                    ];
                    Log::error('Error processing interest for account', [
                        'account_number' => $account->account_number,
                        'product_number' => $account->product_number,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            // Post separate GL entries for savings and deposits
            if ($savingsInterest > 0) {
                $this->postSavingsInterestToGL($accrualDate, $savingsInterest, $savingsCount);
            }
            if ($depositsInterest > 0) {
                $this->postDepositsInterestToGL($accrualDate, $depositsInterest, $depositsCount);
            }

            $summary = [
                'status' => 'success',
                'date' => $accrualDate->format('Y-m-d'),
                'total_accounts' => $savingsAccounts->count(),
                'processed_accounts' => $processedCount,
                'total_interest_accrued' => round($totalInterestAccrued, 2),
                'savings_accounts' => $savingsCount,
                'savings_interest' => round($savingsInterest, 2),
                'deposit_accounts' => $depositsCount,
                'deposit_interest' => round($depositsInterest, 2),
                'errors_count' => count($errors),
                'errors' => $errors
            ];

            Log::info('✅ Daily interest accrual completed', $summary);

            return $summary;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ Daily interest accrual failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Accrue interest for a single account
     *
     * @param AccountsModel $account
     * @param Carbon $accrualDate
     * @return array Result of accrual
     */
    protected function accrueInterestForAccount($account, $accrualDate)
    {
        // Get interest rate for this account
        $annualRate = $this->getInterestRateForAccount($account);

        // Skip if rate is zero or negative
        if ($annualRate <= 0) {
            return [
                'status' => 'skipped',
                'account_number' => $account->account_number,
                'reason' => 'Zero or no interest rate configured',
                'interest_amount' => 0
            ];
        }

        // Get the closing balance for the accrual date
        $closingBalance = $this->getClosingBalance($account, $accrualDate);

        // Skip if balance is zero or negative
        if ($closingBalance <= 0) {
            return [
                'status' => 'skipped',
                'account_number' => $account->account_number,
                'reason' => 'Zero or negative balance',
                'interest_amount' => 0
            ];
        }

        // Calculate if it's a leap year
        $daysInYear = $accrualDate->isLeapYear() ? 366 : 365;

        // Calculate daily rate using Actual/365 (or 366) method
        $dailyRate = $annualRate / $daysInYear / 100;

        // Calculate daily interest
        $interestAmount = $closingBalance * $dailyRate;

        // Determine product type (SAVINGS or DEPOSIT)
        $productType = $account->product_number == 2000 ? 'SAVINGS' : 'DEPOSIT';

        // Store accrual record
        DB::table('interest_accruals')->insert([
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'accrual_date' => $accrualDate->format('Y-m-d'),
            'opening_balance' => $closingBalance, // In this simple version, opening = closing
            'closing_balance' => $closingBalance,
            'annual_rate' => $annualRate,
            'daily_rate' => $dailyRate,
            'interest_amount' => round($interestAmount, 2),
            'year' => $accrualDate->year,
            'month' => $accrualDate->month,
            'day' => $accrualDate->day,
            'status' => 'ACCRUED',
            'product_type' => $productType,
            'notes' => "Daily {$productType} interest accrual - Rate: {$annualRate}% p.a., Days in year: {$daysInYear}",
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return [
            'status' => 'success',
            'account_number' => $account->account_number,
            'balance' => $closingBalance,
            'interest_amount' => round($interestAmount, 2),
            'rate' => $annualRate,
            'product_type' => $productType
        ];
    }

    /**
     * Get closing balance for an account on a specific date
     * In a more sophisticated system, you would track intraday transactions
     *
     * @param AccountsModel $account
     * @param Carbon $date
     * @return float
     */
    protected function getClosingBalance($account, $date)
    {
        // For now, we use the current balance
        // In a full implementation, you would:
        // 1. Get the balance as of the specific date from general_ledger
        // 2. Or maintain a daily_balances table

        // Simple approach: Use current balance
        return $account->balance;

        // Advanced approach (if you have general_ledger with dates):
        /*
        $balance = DB::table('general_ledger')
            ->where('account_number', $account->account_number)
            ->whereDate('created_at', '<=', $date)
            ->orderBy('created_at', 'desc')
            ->value('gl_balance');

        return $balance ?? $account->balance;
        */
    }

    /**
     * Get interest rate for a specific account
     * Checks product settings - returns 0 if no rate configured
     *
     * @param AccountsModel $account
     * @return float
     */
    protected function getInterestRateForAccount($account)
    {
        // Try to get rate from sub_products table using 'interest' field (annual rate)
        if ($account->sub_product_number) {
            $productRate = DB::table('sub_products')
                ->where('sub_product_id', $account->sub_product_number)
                ->value('interest');

            if ($productRate && $productRate > 0) {
                return (float) $productRate;
            }
        }

        // Try to get rate from products table based on account's product_number
        // (2000 for savings, 3000 for deposits)
        $productRate = DB::table('sub_products')
            ->where('product_type', $account->product_number)
            ->where('status', 'ACTIVE')
            ->value('interest');

        if ($productRate && $productRate > 0) {
            return (float) $productRate;
        }

        // Return 0 - account will be skipped (no rate configured)
        return 0;
    }

    /**
     * Post SAVINGS interest expense to GL
     * DR: Interest on Savings (P&L Expense)
     * CR: Interest Payable (Liability to members)
     */
    protected function postSavingsInterestToGL(Carbon $accrualDate, float $totalInterest, int $accountCount): void
    {
        $amount = round($totalInterest, 2);

        if ($amount <= 0) {
            return;
        }

        $narration = sprintf(
            "Daily savings interest accrual for %s (%d accounts, TZS %s)",
            $accrualDate->format('Y-m-d'),
            $accountCount,
            number_format($amount, 2)
        );

        try {
            $result = $this->transactionService->postTransaction([
                'first_account' => $this->interestPayableAccount, // CR: Liability (owed to members)
                'second_account' => $this->savingsInterestExpenseAccount, // DR: Interest on Savings Expense
                'amount' => $amount,
                'narration' => $narration,
                'action' => 'savings_interest_accrual'
            ]);

            Log::info('[SAVINGS_INTEREST] GL entry posted', [
                'amount' => $amount,
                'reference' => $result['reference_number'] ?? null,
                'debit' => $this->savingsInterestExpenseAccount,
                'credit' => $this->interestPayableAccount
            ]);

        } catch (Exception $e) {
            Log::error('[SAVINGS_INTEREST] Failed to post GL entry', [
                'error' => $e->getMessage(),
                'amount' => $amount
            ]);
            // Don't throw - accruals are recorded, GL can be fixed manually
        }
    }

    /**
     * Post DEPOSITS interest expense to GL
     * DR: Interest on Deposits (P&L Expense)
     * CR: Interest Payable (Liability to members)
     */
    protected function postDepositsInterestToGL(Carbon $accrualDate, float $totalInterest, int $accountCount): void
    {
        $amount = round($totalInterest, 2);

        if ($amount <= 0) {
            return;
        }

        $narration = sprintf(
            "Daily deposits interest accrual for %s (%d accounts, TZS %s)",
            $accrualDate->format('Y-m-d'),
            $accountCount,
            number_format($amount, 2)
        );

        try {
            $result = $this->transactionService->postTransaction([
                'first_account' => $this->interestPayableAccount, // CR: Liability (owed to members)
                'second_account' => $this->depositsInterestExpenseAccount, // DR: Interest on Deposits Expense
                'amount' => $amount,
                'narration' => $narration,
                'action' => 'deposits_interest_accrual'
            ]);

            Log::info('[DEPOSITS_INTEREST] GL entry posted', [
                'amount' => $amount,
                'reference' => $result['reference_number'] ?? null,
                'debit' => $this->depositsInterestExpenseAccount,
                'credit' => $this->interestPayableAccount
            ]);

        } catch (Exception $e) {
            Log::error('[DEPOSITS_INTEREST] Failed to post GL entry', [
                'error' => $e->getMessage(),
                'amount' => $amount
            ]);
            // Don't throw - accruals are recorded, GL can be fixed manually
        }
    }

    /**
     * Calculate total accrued interest for an account for a given period
     *
     * @param string $accountNumber
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function getAccruedInterestForPeriod($accountNumber, $startDate, $endDate)
    {
        $accruals = DB::table('interest_accruals')
            ->where('account_number', $accountNumber)
            ->whereBetween('accrual_date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ])
            ->where('status', 'ACCRUED')
            ->get();

        $totalInterest = $accruals->sum('interest_amount');
        $daysCount = $accruals->count();

        return [
            'account_number' => $accountNumber,
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d'),
            'days_count' => $daysCount,
            'total_interest' => round($totalInterest, 2),
            'average_daily_interest' => $daysCount > 0 ? round($totalInterest / $daysCount, 2) : 0,
            'accruals' => $accruals
        ];
    }

    /**
     * Get summary of accrued interest for all accounts
     *
     * @param int $year
     * @param int|null $month
     * @return array
     */
    public function getAccrualSummary($year, $month = null)
    {
        $query = DB::table('interest_accruals')
            ->where('year', $year);

        if ($month) {
            $query->where('month', $month);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT account_number) as total_accounts,
            SUM(interest_amount) as total_interest,
            AVG(interest_amount) as average_daily_interest,
            MIN(accrual_date) as period_start,
            MAX(accrual_date) as period_end
        ')->first();

        return [
            'year' => $year,
            'month' => $month,
            'total_accounts' => $summary->total_accounts ?? 0,
            'total_interest_accrued' => round($summary->total_interest ?? 0, 2),
            'average_daily_interest' => round($summary->average_daily_interest ?? 0, 2),
            'period_start' => $summary->period_start,
            'period_end' => $summary->period_end
        ];
    }

    /**
     * Reverse/delete accruals for a specific date (for corrections)
     *
     * @param Carbon $date
     * @return array
     */
    public function reverseAccrualsForDate($date)
    {
        try {
            DB::beginTransaction();

            $deletedCount = DB::table('interest_accruals')
                ->where('accrual_date', $date->format('Y-m-d'))
                ->where('status', 'ACCRUED')
                ->delete();

            DB::commit();

            Log::info('Interest accruals reversed', [
                'date' => $date->format('Y-m-d'),
                'records_deleted' => $deletedCount
            ]);

            return [
                'status' => 'success',
                'date' => $date->format('Y-m-d'),
                'records_deleted' => $deletedCount
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reverse accruals', [
                'date' => $date->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
