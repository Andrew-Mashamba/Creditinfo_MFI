<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Share Dividend Accrual Service
 *
 * Calculates and accrues daily dividends on share capital accounts.
 * Uses Actual/365 (or 366 for leap years) method.
 *
 * Formula: Daily Dividend = (Share Balance × Price per Share × Annual Rate) / Days in Year
 *
 * GL Entries:
 *   DR: Retained Earnings - Current Year (reduces equity)
 *   CR: Dividends Payable (liability for dividend obligation)
 *
 * @package App\Services
 * @version 1.0
 */
class ShareDividendAccrualService
{
    /**
     * GL account for dividend expense (Retained Earnings)
     */
    protected $dividendExpenseAccount;

    /**
     * GL account for dividends payable liability
     */
    protected $dividendsPayableAccount;

    /**
     * Annual dividend rate (percentage)
     */
    protected $annualDividendRate;

    /**
     * Transaction posting service
     */
    protected $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionPostingService();
        $this->loadInstitutionSettings();
    }

    /**
     * Load institution settings for dividend accounts and rate
     */
    protected function loadInstitutionSettings(): void
    {
        $institution = DB::table('institutions')->where('id', 1)->first();

        if ($institution) {
            $this->dividendExpenseAccount = $institution->dividend_expense_account ?? '0101300031003110';
            $this->dividendsPayableAccount = $institution->dividends_payable_account ?? '0101300036003640';
            $this->annualDividendRate = (float) ($institution->share_dividend_rate ?? 10.00);
        } else {
            // Defaults
            $this->dividendExpenseAccount = '0101300031003110';  // Retained Earnings - Current Year
            $this->dividendsPayableAccount = '0101300036003640'; // Dividends Payable
            $this->annualDividendRate = 10.00; // 10% default
        }

        Log::info('[SHARE_DIVIDEND_ACCRUAL] Loaded settings', [
            'dividend_expense_account' => $this->dividendExpenseAccount,
            'dividends_payable_account' => $this->dividendsPayableAccount,
            'annual_rate' => $this->annualDividendRate
        ]);
    }

    /**
     * Process daily dividend accrual for all active share registers
     */
    public function processDailyAccrual(Carbon $accrualDate): array
    {
        Log::info('[SHARE_DIVIDEND_ACCRUAL] Starting daily accrual', [
            'date' => $accrualDate->format('Y-m-d'),
            'annual_rate' => $this->annualDividendRate
        ]);

        // Check if already processed for this date
        $existingRecords = DB::table('share_dividend_accruals')
            ->where('accrual_date', $accrualDate->toDateString())
            ->count();

        if ($existingRecords > 0) {
            Log::warning('[SHARE_DIVIDEND_ACCRUAL] Accrual already processed for date', [
                'date' => $accrualDate->format('Y-m-d'),
                'existing_records' => $existingRecords
            ]);

            return [
                'status' => 'skipped',
                'message' => 'Accrual already processed for this date',
                'date' => $accrualDate->format('Y-m-d'),
                'existing_records' => $existingRecords
            ];
        }

        // Get all active share registers with shares
        $shareRegisters = $this->getActiveShareRegisters();
        $totalRegisters = $shareRegisters->count();

        Log::info('[SHARE_DIVIDEND_ACCRUAL] Found active share registers', [
            'count' => $totalRegisters
        ]);

        $processedCount = 0;
        $skippedCount = 0;
        $totalDividendAccrued = 0;
        $errors = [];

        foreach ($shareRegisters as $register) {
            try {
                $result = $this->accrueDividendForRegister($register, $accrualDate);

                if ($result['status'] === 'success') {
                    $processedCount++;
                    $totalDividendAccrued += $result['dividend_amount'];
                } else {
                    $skippedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'member_number' => $register->member_number,
                    'error' => $e->getMessage()
                ];
                Log::error('[SHARE_DIVIDEND_ACCRUAL] Error processing register', [
                    'member_number' => $register->member_number,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Post GL entry for total dividends accrued
        if ($totalDividendAccrued > 0) {
            $this->postDividendToGL($accrualDate, $totalDividendAccrued, $processedCount);
        }

        $result = [
            'status' => 'success',
            'date' => $accrualDate->format('Y-m-d'),
            'total_registers' => $totalRegisters,
            'processed_count' => $processedCount,
            'skipped_count' => $skippedCount,
            'total_dividend_accrued' => round($totalDividendAccrued, 2),
            'annual_rate' => $this->annualDividendRate,
            'errors_count' => count($errors),
            'errors' => $errors
        ];

        Log::info('[SHARE_DIVIDEND_ACCRUAL] Daily accrual completed', $result);

        return $result;
    }

    /**
     * Get all active share registers with positive balances
     */
    protected function getActiveShareRegisters()
    {
        return DB::table('share_registers')
            ->where('status', 'ACTIVE')
            ->where('current_share_balance', '>', 0)
            ->get();
    }

    /**
     * Calculate and accrue dividend for a single share register
     */
    protected function accrueDividendForRegister($register, Carbon $accrualDate): array
    {
        // Skip if zero rate
        if ($this->annualDividendRate <= 0) {
            return [
                'status' => 'skipped',
                'reason' => 'Zero dividend rate configured',
                'dividend_amount' => 0
            ];
        }

        // Skip if no shares
        if ($register->current_share_balance <= 0) {
            return [
                'status' => 'skipped',
                'reason' => 'No shares held',
                'dividend_amount' => 0
            ];
        }

        // Calculate share value
        $shareBalance = (int) $register->current_share_balance;
        $pricePerShare = (float) $register->current_price;
        $shareValue = $shareBalance * $pricePerShare;

        // Skip if zero value
        if ($shareValue <= 0) {
            return [
                'status' => 'skipped',
                'reason' => 'Zero share value',
                'dividend_amount' => 0
            ];
        }

        // Calculate daily dividend using Actual/365 method
        $daysInYear = $accrualDate->isLeapYear() ? 366 : 365;
        $dailyRate = $this->annualDividendRate / $daysInYear / 100;
        $dailyDividend = $shareValue * $dailyRate;

        // Round to 2 decimal places
        $dailyDividend = round($dailyDividend, 2);

        // Skip if dividend is zero after rounding
        if ($dailyDividend <= 0) {
            return [
                'status' => 'skipped',
                'reason' => 'Dividend rounds to zero',
                'dividend_amount' => 0
            ];
        }

        // Insert accrual record
        DB::table('share_dividend_accruals')->insert([
            'share_register_id' => $register->id,
            'member_number' => $register->member_number,
            'member_name' => $register->member_name,
            'accrual_date' => $accrualDate->toDateString(),
            'share_balance' => $shareBalance,
            'price_per_share' => $pricePerShare,
            'share_value' => $shareValue,
            'annual_rate' => $this->annualDividendRate,
            'daily_rate' => $dailyRate,
            'dividend_amount' => $dailyDividend,
            'status' => 'ACCRUED',
            'fiscal_year' => $accrualDate->year,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Update share register accumulated dividends
        DB::table('share_registers')
            ->where('id', $register->id)
            ->increment('accumulated_dividends', $dailyDividend);

        // Also update total_pending_dividends
        DB::table('share_registers')
            ->where('id', $register->id)
            ->increment('total_pending_dividends', $dailyDividend);

        Log::debug('[SHARE_DIVIDEND_ACCRUAL] Accrued dividend for member', [
            'member_number' => $register->member_number,
            'share_balance' => $shareBalance,
            'share_value' => $shareValue,
            'daily_dividend' => $dailyDividend
        ]);

        return [
            'status' => 'success',
            'member_number' => $register->member_number,
            'share_balance' => $shareBalance,
            'share_value' => $shareValue,
            'dividend_amount' => $dailyDividend
        ];
    }

    /**
     * Post dividend accrual to GL
     * DR: Retained Earnings (reduces equity to fund dividend)
     * CR: Dividends Payable (liability - owed to shareholders)
     */
    protected function postDividendToGL(Carbon $accrualDate, float $totalDividend, int $memberCount): void
    {
        $narration = sprintf(
            "Daily share dividend accrual for %s - %d members - TZS %s",
            $accrualDate->format('Y-m-d'),
            $memberCount,
            number_format($totalDividend, 2)
        );

        try {
            // DR: Retained Earnings (reduce equity - source of funds)
            // CR: Dividends Payable (increase liability - destination of funds)
            $result = $this->transactionService->postTransaction([
                'first_account' => $this->dividendsPayableAccount,  // CR - Dividends Payable
                'second_account' => $this->dividendExpenseAccount,  // DR - Retained Earnings
                'amount' => $totalDividend,
                'narration' => $narration,
                'action' => 'share_dividend_accrual',
                'source_account' => $this->dividendExpenseAccount,       // Retained Earnings (debited)
                'destination_account' => $this->dividendsPayableAccount  // Dividends Payable (credited)
            ]);

            Log::info('[SHARE_DIVIDEND_ACCRUAL] GL entry posted', [
                'date' => $accrualDate->format('Y-m-d'),
                'total_dividend' => $totalDividend,
                'reference' => $result['reference_number'] ?? null,
                'dr_account' => $this->dividendExpenseAccount,
                'cr_account' => $this->dividendsPayableAccount
            ]);

        } catch (\Exception $e) {
            Log::error('[SHARE_DIVIDEND_ACCRUAL] Failed to post GL entry', [
                'error' => $e->getMessage(),
                'total_dividend' => $totalDividend
            ]);
            throw $e;
        }
    }

    /**
     * Get accrual summary for reporting
     */
    public function getAccrualSummary(int $year, ?int $month = null): array
    {
        $query = DB::table('share_dividend_accruals')
            ->where('fiscal_year', $year);

        if ($month) {
            $query->whereMonth('accrual_date', $month);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT member_number) as total_members,
            COUNT(*) as total_accruals,
            SUM(dividend_amount) as total_dividend_accrued,
            AVG(dividend_amount) as average_daily_dividend,
            MIN(accrual_date) as period_start,
            MAX(accrual_date) as period_end
        ')->first();

        return [
            'year' => $year,
            'month' => $month,
            'total_members' => $summary->total_members ?? 0,
            'total_accruals' => $summary->total_accruals ?? 0,
            'total_dividend_accrued' => (float) ($summary->total_dividend_accrued ?? 0),
            'average_daily_dividend' => (float) ($summary->average_daily_dividend ?? 0),
            'period_start' => $summary->period_start,
            'period_end' => $summary->period_end
        ];
    }

    /**
     * Get member dividend summary
     */
    public function getMemberDividendSummary(string $memberNumber, int $year): array
    {
        $accruals = DB::table('share_dividend_accruals')
            ->where('member_number', $memberNumber)
            ->where('fiscal_year', $year)
            ->orderBy('accrual_date', 'desc')
            ->get();

        $totalAccrued = $accruals->sum('dividend_amount');
        $daysAccrued = $accruals->count();

        return [
            'member_number' => $memberNumber,
            'year' => $year,
            'total_accrued' => round($totalAccrued, 2),
            'days_accrued' => $daysAccrued,
            'average_daily' => $daysAccrued > 0 ? round($totalAccrued / $daysAccrued, 2) : 0,
            'projected_annual' => $daysAccrued > 0 ? round(($totalAccrued / $daysAccrued) * 365, 2) : 0,
            'accruals' => $accruals
        ];
    }

    /**
     * Get current dividend rate
     */
    public function getDividendRate(): float
    {
        return $this->annualDividendRate;
    }
}
