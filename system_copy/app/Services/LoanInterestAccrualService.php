<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountsModel;
use App\Services\TransactionPostingService;
use App\Services\AccountCreationService;

/**
 * Loan Interest Accrual Service
 *
 * Handles accrual-based recognition of interest income on loans according to IFRS/GAAP standards.
 *
 * Key Features:
 * - Daily interest accrual calculation based on outstanding principal
 * - NPL interest suspension for loans >90 days in arrears (per BoT regulations)
 * - Journal entry posting to GL (DR: Interest Receivable, CR: Interest Income)
 * - Suspended interest tracking for non-performing loans
 * - Monthly summary generation for financial reporting
 *
 * Loan Classification Thresholds (per BoT regulations):
 * - 0-30 days: CURRENT (0% provision)
 * - 31-90 days: WATCH (10% provision)
 * - 91-180 days: SUBSTANDARD (30% provision) - Interest suspension starts here
 * - 181-365 days: DOUBTFUL (50% provision)
 * - >365 days: LOSS (100% provision)
 *
 * GL Accounts Used:
 * - Interest Receivable - Current (Asset): 0101100014001410
 * - Interest Receivable - Overdue (Asset): 0101100014001420
 * - Interest Income - Current (Income): 0101400040004010
 * - Interest Income - Overdue (Income): 0101400040004020
 * - Suspended Interest (Contra-Asset): Created if not exists under 010110001400
 *
 * @author System
 * @version 1.1
 */
class LoanInterestAccrualService
{
    // NPL Classification Thresholds (per BoT regulations)
    // 0-30 days: Current (0% provision)
    // 31-90 days: Watch (10% provision)
    // 91-180 days: Substandard (30% provision)
    // 181-365 days: Doubtful (50% provision)
    // >365 days: Loss (100% provision)
    const CURRENT_THRESHOLD_DAYS = 30;
    const WATCH_THRESHOLD_DAYS = 31;
    const SUBSTANDARD_THRESHOLD_DAYS = 91;
    const DOUBTFUL_THRESHOLD_DAYS = 181;
    const LOSS_THRESHOLD_DAYS = 366;

    // NPL threshold for classification (but we still recognize income)
    const NPL_THRESHOLD_DAYS = 91;

    protected $transactionService;
    protected $accountService;
    protected $suspendedInterestAccountNumber;
    protected $statistics;
    protected $institution;

    // GL Account Numbers (loaded from institutions table)
    protected $interestReceivableCurrent;
    protected $interestReceivableOverdue;
    protected $interestIncomeCurrent;
    protected $interestIncomeOverdue;

    public function __construct()
    {
        $this->transactionService = new TransactionPostingService();
        $this->accountService = new AccountCreationService();
        $this->loadInstitutionAccounts();
        $this->statistics = [
            'total_loans' => 0,
            'performing_loans' => 0,
            'npl_loans' => 0,
            'total_interest_accrued' => 0,
            'performing_interest' => 0,
            'overdue_interest' => 0,
            'journal_entries_created' => 0,
            'errors' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * Load GL accounts from institutions table
     */
    protected function loadInstitutionAccounts(): void
    {
        $this->institution = DB::table('institutions')->where('id', 1)->first();

        if ($this->institution) {
            $this->interestReceivableCurrent = $this->institution->interest_receivable_current_account ?? '0101100014001410';
            $this->interestReceivableOverdue = $this->institution->interest_receivable_overdue_account ?? '0101100014001420';
            $this->interestIncomeCurrent = $this->institution->interest_income_current_account ?? '0101400040004010';
            $this->interestIncomeOverdue = $this->institution->interest_income_overdue_account ?? '0101400040004020';

            Log::info('[LOAN_INTEREST_ACCRUAL] Loaded GL accounts from institutions', [
                'interest_receivable_current' => $this->interestReceivableCurrent,
                'interest_receivable_overdue' => $this->interestReceivableOverdue,
                'interest_income_current' => $this->interestIncomeCurrent,
                'interest_income_overdue' => $this->interestIncomeOverdue
            ]);
        } else {
            // Fallback to defaults if institution not found
            $this->interestReceivableCurrent = '0101100014001410';
            $this->interestReceivableOverdue = '0101100014001420';
            $this->interestIncomeCurrent = '0101400040004010';
            $this->interestIncomeOverdue = '0101400040004020';

            Log::warning('[LOAN_INTEREST_ACCRUAL] Institution not found, using default GL accounts');
        }
    }

    /**
     * Process daily interest accrual for all active loans
     */
    public function processDailyAccrual(?Carbon $accrualDate = null): array
    {
        $accrualDate = $accrualDate ?? Carbon::yesterday();
        $dateString = $accrualDate->format('Y-m-d');

        Log::info('=============================================');
        Log::info('[LOAN_INTEREST_ACCRUAL] Starting Loan Interest Accrual Process');
        Log::info('[LOAN_INTEREST_ACCRUAL] Accrual Date: ' . $dateString);
        Log::info('[LOAN_INTEREST_ACCRUAL] Timestamp: ' . now()->toIso8601String());
        Log::info('=============================================');

        try {
            $existingAccruals = DB::table('loan_interest_accruals')
                ->where('accrual_date', $dateString)
                ->where('posted_to_gl', true)
                ->count();

            if ($existingAccruals > 0) {
                Log::info('Daily interest accrual already processed for this date', [
                    'date' => $dateString,
                    'existing_records' => $existingAccruals
                ]);

                return [
                    'status' => 'skipped',
                    'message' => "Interest accrual already processed for {$dateString}",
                    'date' => $dateString,
                    'existing_records' => $existingAccruals
                ];
            }

            $this->ensureSuspendedInterestAccount();

            DB::beginTransaction();

            $loans = $this->getActiveLoans();
            $this->statistics['total_loans'] = count($loans);

            Log::info("Processing {$this->statistics['total_loans']} active loans for interest accrual");

            foreach ($loans as $loan) {
                try {
                    $this->processLoanAccrual($loan, $accrualDate);
                } catch (\Exception $e) {
                    $this->statistics['errors']++;
                    Log::error("Error processing loan {$loan->loan_id}: " . $e->getMessage());
                }
            }

            $this->createDailySummary($accrualDate);

            // Post performing interest to GL (current loans)
            if ($this->statistics['performing_interest'] > 0) {
                $this->postPerformingInterestToGL($accrualDate);
            }

            // Post overdue interest to GL (loans in arrears) - Income realization
            // We recognize interest income even for loans in arrears
            if ($this->statistics['overdue_interest'] > 0) {
                $this->postOverdueInterestToGL($accrualDate);
            }

            DB::commit();

            Log::info('=============================================');
            Log::info('[LOAN_INTEREST_ACCRUAL] Process Completed');
            Log::info('[LOAN_INTEREST_ACCRUAL] Total Loans: ' . $this->statistics['total_loans']);
            Log::info('[LOAN_INTEREST_ACCRUAL] Performing Loans: ' . $this->statistics['performing_loans']);
            Log::info('[LOAN_INTEREST_ACCRUAL] NPL Loans: ' . $this->statistics['npl_loans']);
            Log::info('[LOAN_INTEREST_ACCRUAL] Total Interest Accrued: TZS ' . number_format($this->statistics['total_interest_accrued'], 4));
            Log::info('[LOAN_INTEREST_ACCRUAL] Performing Interest: TZS ' . number_format($this->statistics['performing_interest'], 4));
            Log::info('[LOAN_INTEREST_ACCRUAL] Overdue Interest (Recognized): TZS ' . number_format($this->statistics['overdue_interest'], 4));
            Log::info('[LOAN_INTEREST_ACCRUAL] Journal Entries Created: ' . $this->statistics['journal_entries_created']);
            Log::info('[LOAN_INTEREST_ACCRUAL] Errors: ' . $this->statistics['errors']);
            Log::info('[LOAN_INTEREST_ACCRUAL] Skipped: ' . $this->statistics['skipped']);
            Log::info('=============================================');

            return [
                'status' => 'success',
                'date' => $dateString,
                'statistics' => $this->statistics
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Loan Interest Accrual Process Failed', [
                'date' => $dateString,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'date' => $dateString,
                'message' => $e->getMessage(),
                'statistics' => $this->statistics
            ];
        }
    }

    protected function getActiveLoans()
    {
        // Join with accounts table to get the loan balance from loan_account_number
        // Join with loan_sub_products to get the interest rate
        return DB::table('loans as l')
            ->leftJoin('accounts as a', 'l.loan_account_number', '=', 'a.account_number')
            ->leftJoin('loan_sub_products as lsp', 'l.loan_sub_product', '=', 'lsp.sub_product_id')
            ->where('l.status', 'DISBURSED')
            ->whereIn('l.loan_status', ['active', 'ACTIVE', 'DISBURSED', 'NORMAL', 'WATCH', 'SUBSTANDARD', 'DOUBTFUL'])
            ->whereNotIn('l.loan_status', ['closed', 'CLOSED', 'LOSS', 'written_off'])
            ->whereNotNull('l.disbursement_date')
            ->where(function ($query) {
                // Loan has outstanding balance either in accounts table or calculated from principle
                $query->where('a.balance', '>', 0)
                      ->orWhere(DB::raw('COALESCE(l.principle, 0) - COALESCE(l.total_principal_paid, 0)'), '>', 0);
            })
            ->select([
                'l.id', 'l.loan_id', 'l.loan_account_number', 'l.client_number', 'l.principle',
                DB::raw("CAST(COALESCE(lsp.interest_value, '0') AS NUMERIC) as interest_rate"), // Get annual interest rate from loan_sub_products
                'lsp.interest_tenure', // To know if rate is monthly/yearly
                DB::raw('COALESCE(a.balance, 0) as loan_balance'), // Get balance from accounts table
                'l.total_principal_paid',
                'l.total_interest_paid', 'l.total_interest', 'l.days_in_arrears',
                'l.total_days_in_arrears', 'l.loan_status', 'l.loan_classification',
                'l.disbursement_date', 'l.interest_method', 'l.tenure'
            ])
            ->get();
    }

    protected function processLoanAccrual($loan, Carbon $accrualDate)
    {
        $outstandingPrincipal = $this->calculateOutstandingPrincipal($loan);

        if ($outstandingPrincipal <= 0) {
            $this->statistics['skipped']++;
            return;
        }

        // Get interest rate and convert to annual rate if needed
        $rate = floatval($loan->interest_rate);
        $tenure = strtolower($loan->interest_tenure ?? 'yearly');

        // Convert rate to annual rate based on tenure
        // If rate is monthly (e.g., 24% per annum quoted as monthly), it's already annual
        // Most SACCOS quote annual rate but label it as "monthly" meaning monthly interest calculation
        $annualRate = $rate; // Assume rate is already annual (common in SACCOS)

        $dailyRate = $annualRate / 365 / 100;
        $dailyInterest = round($outstandingPrincipal * $dailyRate, 4);

        $daysInArrears = max(intval($loan->days_in_arrears), intval($loan->total_days_in_arrears));
        $classification = $this->determineLoanClassification($daysInArrears);
        $isSuspended = $daysInArrears >= self::NPL_THRESHOLD_DAYS;

        $previousAccrual = DB::table('loan_interest_accruals')
            ->where('loan_id', $loan->loan_id)
            ->where('accrual_date', '<', $accrualDate->format('Y-m-d'))
            ->orderBy('accrual_date', 'desc')
            ->first();

        $previousCumulative = $previousAccrual ? floatval($previousAccrual->cumulative_accrued) : 0;
        $interestReceived = floatval($loan->total_interest_paid ?? 0);
        $cumulativeAccrued = $previousCumulative + $dailyInterest;
        $interestReceivable = $cumulativeAccrued - $interestReceived;

        DB::table('loan_interest_accruals')->insert([
            'loan_id' => $loan->loan_id,
            'loan_account_number' => $loan->loan_account_number,
            'member_number' => $loan->client_number,
            'accrual_date' => $accrualDate->format('Y-m-d'),
            'opening_balance' => $outstandingPrincipal,
            'daily_interest' => $dailyInterest,
            'cumulative_accrued' => $cumulativeAccrued,
            'interest_received' => $interestReceived,
            'interest_receivable' => max(0, $interestReceivable),
            'annual_rate' => $annualRate,
            'daily_rate' => $dailyRate,
            'accrual_method' => 'DAILY',
            'status' => $isSuspended ? 'SUSPENDED' : 'ACCRUED',
            'year' => $accrualDate->year,
            'month' => $accrualDate->month,
            'day_of_month' => $accrualDate->day,
            'loan_classification' => $classification,
            'is_suspended' => $isSuspended,
            'suspension_reason' => $isSuspended ? "NPL - {$daysInArrears} days in arrears" : null,
            'posted_to_gl' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->statistics['total_interest_accrued'] += $dailyInterest;

        // Track loans in arrears separately but still recognize income
        if ($isSuspended) {
            $this->statistics['npl_loans']++;
            // Interest for NPL loans goes to overdue accounts but income is STILL recognized
            $this->statistics['overdue_interest'] += $dailyInterest;
        } else {
            $this->statistics['performing_loans']++;
            $this->statistics['performing_interest'] += $dailyInterest;
        }

        Log::info("[LOAN_INTEREST_ACCRUAL] Loan {$loan->loan_id}: Principal=" . number_format($outstandingPrincipal, 2) . ", Rate={$annualRate}%, Daily Interest=" . number_format($dailyInterest, 4) . ", Classification={$classification}, InArrears=" . ($isSuspended ? 'YES' : 'NO'));
    }

    protected function calculateOutstandingPrincipal($loan): float
    {
        // First check loan_balance from accounts table (joined in getActiveLoans)
        if (isset($loan->loan_balance) && floatval($loan->loan_balance) > 0) {
            return floatval($loan->loan_balance);
        }
        // Fallback: calculate from principle - total_principal_paid
        $principal = floatval($loan->principle ?? 0);
        $principalPaid = floatval($loan->total_principal_paid ?? 0);
        return max(0, $principal - $principalPaid);
    }

    /**
     * Determine loan classification based on days in arrears (per BoT regulations)
     *
     * Classification Thresholds:
     * - 0-30 days: CURRENT (0% provision)
     * - 31-90 days: WATCH (10% provision)
     * - 91-180 days: SUBSTANDARD (30% provision)
     * - 181-365 days: DOUBTFUL (50% provision)
     * - >365 days: LOSS (100% provision)
     *
     * @param int $daysInArrears
     * @return string Classification category
     */
    protected function determineLoanClassification(int $daysInArrears): string
    {
        if ($daysInArrears >= self::LOSS_THRESHOLD_DAYS) return 'LOSS';           // >365 days - 100%
        if ($daysInArrears >= self::DOUBTFUL_THRESHOLD_DAYS) return 'DOUBTFUL';   // 181-365 days - 50%
        if ($daysInArrears >= self::SUBSTANDARD_THRESHOLD_DAYS) return 'SUBSTANDARD'; // 91-180 days - 30%
        if ($daysInArrears >= self::WATCH_THRESHOLD_DAYS) return 'WATCH';         // 31-90 days - 10%
        return 'CURRENT';  // 0-30 days - 0%
    }

    protected function ensureSuspendedInterestAccount()
    {
        $suspendedAccount = AccountsModel::where('account_name', 'LIKE', '%SUSPENDED INTEREST%')
            ->where('type', 'asset_accounts')
            ->first();

        if ($suspendedAccount) {
            $this->suspendedInterestAccountNumber = $suspendedAccount->account_number;
            return;
        }

        Log::info('Creating Suspended Interest account...');

        try {
            $newAccount = $this->accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => 'SUSPENDED INTEREST - NPL',
                'product_number' => '1400',
                'branch_number' => '01',
                'type' => 'asset_accounts',
                'major_category_code' => '1000',
                'category_code' => '1400',
            ], '010110001400');

            $this->suspendedInterestAccountNumber = $newAccount->account_number;
            Log::info("Created Suspended Interest account: {$this->suspendedInterestAccountNumber}");
        } catch (\Exception $e) {
            Log::warning("Could not create Suspended Interest account: " . $e->getMessage());
            $this->suspendedInterestAccountNumber = $this->interestReceivableOverdue;
        }
    }

    /**
     * Post performing (current) interest to GL
     * DR Interest Receivable - Current / CR Interest Income - Current
     */
    protected function postPerformingInterestToGL(Carbon $accrualDate)
    {
        $amount = round($this->statistics['performing_interest'], 2);

        if ($amount <= 0) return;

        $referenceNumber = 'INT-ACCR-' . $accrualDate->format('Ymd') . '-' . time();
        $narration = "Daily loan interest accrual (current) for " . $accrualDate->format('Y-m-d') .
                     " ({$this->statistics['performing_loans']} loans, TZS " . number_format($amount, 2) . ")";

        try {
            $result = $this->transactionService->postTransaction([
                'first_account' => $this->interestReceivableCurrent,
                'second_account' => $this->interestIncomeCurrent,
                'amount' => $amount,
                'narration' => $narration,
                'action' => 'interest_accrual',
            ]);

            if ($result['status'] === 'success') {
                DB::table('loan_interest_accruals')
                    ->where('accrual_date', $accrualDate->format('Y-m-d'))
                    ->where('is_suspended', false)
                    ->update([
                        'posted_to_gl' => true,
                        'posted_at' => now(),
                        'journal_entry_reference' => $referenceNumber,
                        'updated_at' => now()
                    ]);

                $this->statistics['journal_entries_created']++;

                Log::info("[INTEREST_ACCRUAL] Performing interest posted to GL", [
                    'reference' => $referenceNumber,
                    'amount' => $amount,
                    'debit_account' => $this->interestReceivableCurrent,
                    'credit_account' => $this->interestIncomeCurrent
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to post performing interest accrual to GL: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Post overdue (arrears) interest to GL - Income realization even for NPL loans
     * DR Interest Receivable - Overdue / CR Interest Income - Overdue
     */
    protected function postOverdueInterestToGL(Carbon $accrualDate)
    {
        $amount = round($this->statistics['overdue_interest'], 2);

        if ($amount <= 0) return;

        $referenceNumber = 'INT-ACCR-OD-' . $accrualDate->format('Ymd') . '-' . time();
        $narration = "Daily loan interest accrual (overdue/arrears) for " . $accrualDate->format('Y-m-d') .
                     " ({$this->statistics['npl_loans']} loans in arrears, TZS " . number_format($amount, 2) . ")";

        try {
            $result = $this->transactionService->postTransaction([
                'first_account' => $this->interestReceivableOverdue,
                'second_account' => $this->interestIncomeOverdue,
                'amount' => $amount,
                'narration' => $narration,
                'action' => 'interest_accrual_overdue',
            ]);

            if ($result['status'] === 'success') {
                DB::table('loan_interest_accruals')
                    ->where('accrual_date', $accrualDate->format('Y-m-d'))
                    ->where('is_suspended', true)
                    ->update([
                        'posted_to_gl' => true,
                        'posted_at' => now(),
                        'journal_entry_reference' => $referenceNumber,
                        'updated_at' => now()
                    ]);

                $this->statistics['journal_entries_created']++;

                Log::info("[INTEREST_ACCRUAL] Overdue interest posted to GL (income recognized)", [
                    'reference' => $referenceNumber,
                    'amount' => $amount,
                    'debit_account' => $this->interestReceivableOverdue,
                    'credit_account' => $this->interestIncomeOverdue,
                    'npl_loans' => $this->statistics['npl_loans']
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to post overdue interest accrual to GL: " . $e->getMessage());
            throw $e;
        }
    }

    protected function createDailySummary(Carbon $accrualDate)
    {
        $performingBalance = DB::table('loan_interest_accruals')
            ->where('accrual_date', $accrualDate->format('Y-m-d'))
            ->where('is_suspended', false)
            ->sum('opening_balance');

        $nplBalance = DB::table('loan_interest_accruals')
            ->where('accrual_date', $accrualDate->format('Y-m-d'))
            ->where('is_suspended', true)
            ->sum('opening_balance');

        $totalReceivable = DB::table('loan_interest_accruals')
            ->where('accrual_date', $accrualDate->format('Y-m-d'))
            ->sum('interest_receivable');

        $totalReceived = DB::table('loan_interest_accruals')
            ->where('accrual_date', $accrualDate->format('Y-m-d'))
            ->sum('interest_received');

        DB::table('loan_interest_accrual_summaries')->updateOrInsert(
            ['year' => $accrualDate->year, 'month' => $accrualDate->month, 'accrual_date' => $accrualDate->format('Y-m-d')],
            [
                'total_loans_processed' => $this->statistics['total_loans'],
                'performing_loans_count' => $this->statistics['performing_loans'],
                'npl_loans_count' => $this->statistics['npl_loans'],
                'total_interest_accrued' => $this->statistics['total_interest_accrued'],
                'performing_interest_accrued' => $this->statistics['performing_interest'],
                'suspended_interest' => $this->statistics['suspended_interest'],
                'total_interest_received' => $totalReceived,
                'total_interest_receivable' => $totalReceivable,
                'total_loan_balance' => $performingBalance + $nplBalance,
                'performing_loan_balance' => $performingBalance,
                'npl_loan_balance' => $nplBalance,
                'status' => 'PROCESSED',
                'updated_at' => now()
            ]
        );
    }

    public function getAccrualSummary(Carbon $startDate, Carbon $endDate): array
    {
        $summaries = DB::table('loan_interest_accrual_summaries')
            ->whereBetween('accrual_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('accrual_date')
            ->get();

        $totals = [
            'total_loans_processed' => $summaries->max('total_loans_processed'),
            'performing_loans_count' => $summaries->avg('performing_loans_count'),
            'npl_loans_count' => $summaries->avg('npl_loans_count'),
            'total_interest_accrued' => $summaries->sum('total_interest_accrued'),
            'performing_interest_accrued' => $summaries->sum('performing_interest_accrued'),
            'suspended_interest' => $summaries->sum('suspended_interest'),
            'total_interest_receivable' => $summaries->last()?->total_interest_receivable ?? 0,
            'npl_ratio' => 0
        ];

        $lastSummary = $summaries->last();
        if ($lastSummary && $lastSummary->total_loan_balance > 0) {
            $totals['npl_ratio'] = ($lastSummary->npl_loan_balance / $lastSummary->total_loan_balance) * 100;
        }

        return [
            'period' => ['start' => $startDate->format('Y-m-d'), 'end' => $endDate->format('Y-m-d')],
            'daily_summaries' => $summaries,
            'totals' => $totals
        ];
    }

    public function getMonthlyReport(int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $summary = $this->getAccrualSummary($startDate, $endDate);

        $loanDetails = DB::table('loan_interest_accruals')
            ->where('year', $year)
            ->where('month', $month)
            ->select([
                'loan_id', 'loan_account_number', 'member_number', 'loan_classification',
                DB::raw('SUM(daily_interest) as monthly_interest'),
                DB::raw('MAX(interest_receivable) as ending_receivable'),
                DB::raw('MAX(is_suspended::int) as is_suspended'),
                DB::raw('COUNT(*) as days_accrued')
            ])
            ->groupBy('loan_id', 'loan_account_number', 'member_number', 'loan_classification')
            ->orderBy('monthly_interest', 'desc')
            ->get();

        return [
            'period' => ['year' => $year, 'month' => $month, 'month_name' => $startDate->format('F Y')],
            'summary' => $summary['totals'],
            'daily_trend' => $summary['daily_summaries'],
            'loan_details' => $loanDetails
        ];
    }

    public function reverseAccrual(string $loanId, Carbon $reversalDate, string $reason, int $userId): array
    {
        try {
            DB::beginTransaction();

            $accruals = DB::table('loan_interest_accruals')
                ->where('loan_id', $loanId)
                ->where('accrual_date', '>=', $reversalDate->format('Y-m-d'))
                ->where('posted_to_gl', true)
                ->whereNull('reversed_at')
                ->get();

            if ($accruals->isEmpty()) {
                return ['status' => 'skipped', 'message' => 'No accruals to reverse'];
            }

            $totalToReverse = $accruals->sum('daily_interest');
            $reversalReference = 'INT-REV-' . $loanId . '-' . time();

            $this->transactionService->postTransaction([
                'first_account' => self::INTEREST_INCOME_CURRENT,
                'second_account' => self::INTEREST_RECEIVABLE_CURRENT,
                'amount' => $totalToReverse,
                'narration' => "Interest accrual reversal for Loan {$loanId}: {$reason}",
                'action' => 'interest_reversal',
                'source_account' => self::INTEREST_INCOME_CURRENT,
                'destination_account' => self::INTEREST_RECEIVABLE_CURRENT,
            ]);

            DB::table('loan_interest_accruals')
                ->where('loan_id', $loanId)
                ->where('accrual_date', '>=', $reversalDate->format('Y-m-d'))
                ->whereNull('reversed_at')
                ->update([
                    'status' => 'REVERSED',
                    'reversal_reference' => $reversalReference,
                    'reversed_at' => now(),
                    'reversed_by' => $userId,
                    'notes' => $reason,
                    'updated_at' => now()
                ]);

            DB::commit();

            Log::info("Interest accrual reversed for loan {$loanId}", [
                'amount' => $totalToReverse, 'reference' => $reversalReference, 'reason' => $reason
            ]);

            return [
                'status' => 'success',
                'amount_reversed' => $totalToReverse,
                'reference' => $reversalReference,
                'accruals_reversed' => $accruals->count()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reverse interest accrual for loan {$loanId}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getStatistics(): array
    {
        return $this->statistics;
    }

    public function processBacklog(Carbon $startDate, Carbon $endDate): array
    {
        $results = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $results[$currentDate->format('Y-m-d')] = $this->processDailyAccrual($currentDate->copy());
            $currentDate->addDay();
        }

        return $results;
    }
}
