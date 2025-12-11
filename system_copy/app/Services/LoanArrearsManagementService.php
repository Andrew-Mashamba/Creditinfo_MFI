<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\SmsService;
use App\Services\TransactionPostingService;
use App\Services\PaymentLinkService;

/**
 * Loan Arrears Management Service
 *
 * Comprehensive service for managing loan repayments, arrears, and collections
 *
 * Key Features:
 * - Upcoming repayment reminders (3, 2, 1 days before due)
 * - Due date notifications
 * - Arrears notifications (days 5, 10, 15, 20, 25, 30)
 * - Penalty calculation and accrual (after 30 days)
 * - NPL classification updates per BoT regulations
 * - Legal document generation (demand letters, notices)
 *
 * NPL Classification Thresholds (per BoT regulations):
 * - 0-30 days: CURRENT (0% provision)
 * - 31-90 days: WATCH (10% provision)
 * - 91-180 days: SUBSTANDARD (30% provision)
 * - 181-365 days: DOUBTFUL (50% provision)
 * - >365 days: LOSS (100% provision)
 *
 * @author System
 * @version 1.0
 */
class LoanArrearsManagementService
{
    // NPL Classification Thresholds
    const CURRENT_THRESHOLD_DAYS = 30;
    const WATCH_THRESHOLD_DAYS = 31;
    const SUBSTANDARD_THRESHOLD_DAYS = 91;
    const DOUBTFUL_THRESHOLD_DAYS = 181;
    const LOSS_THRESHOLD_DAYS = 366;

    // Penalty calculation starts after this many days
    const PENALTY_START_DAYS = 30;

    // Notification schedules
    const UPCOMING_REMINDER_DAYS = [3, 2, 1]; // Days before due date
    // Arrears reminders: every 5 days up to 120 days
    const ARREARS_REMINDER_DAYS = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100, 105, 110, 115, 120];

    // SMS character limit (single SMS = 160 chars, concatenated = 153 per part)
    const SMS_SINGLE_LIMIT = 160;
    const SMS_CONCAT_PART_LIMIT = 153;
    const SMS_MAX_PARTS = 4; // Maximum 4 SMS parts (612 chars)

    // Legal document thresholds
    const WARNING_LETTER_DAYS = 60;
    const DEMAND_LETTER_DAYS = 90;
    const FINAL_NOTICE_DAYS = 120;
    const LEGAL_NOTICE_DAYS = 180;

    protected $smsService;
    protected $transactionService;
    protected $paymentLinkService;
    protected $statistics;
    protected $institution;

    // GL Accounts for penalty accrual (fetched from institutions table)
    protected $penaltyReceivableAccount;
    protected $penaltyIncomeAccount;

    public function __construct()
    {
        $this->smsService = new SmsService();
        $this->transactionService = new TransactionPostingService();
        $this->paymentLinkService = new PaymentLinkService();
        $this->loadInstitutionAccounts();
        $this->resetStatistics();
    }

    /**
     * Load GL accounts from institutions table
     */
    protected function loadInstitutionAccounts(): void
    {
        $this->institution = DB::table('institutions')->where('id', 1)->first();

        if ($this->institution) {
            $this->penaltyReceivableAccount = $this->institution->penalty_receivable_account ?? '010000014020';
            $this->penaltyIncomeAccount = $this->institution->penalty_income_account ?? '0101400041004110';

            Log::info('[LOAN_ARREARS] Loaded GL accounts from institutions', [
                'penalty_receivable' => $this->penaltyReceivableAccount,
                'penalty_income' => $this->penaltyIncomeAccount
            ]);
        } else {
            // Fallback to defaults if institution not found
            $this->penaltyReceivableAccount = '010000014020';
            $this->penaltyIncomeAccount = '0101400041004110';

            Log::warning('[LOAN_ARREARS] Institution not found, using default GL accounts');
        }
    }

    /**
     * Get penalty receivable account number
     */
    public function getPenaltyReceivableAccount(): string
    {
        return $this->penaltyReceivableAccount;
    }

    /**
     * Get penalty income account number
     */
    public function getPenaltyIncomeAccount(): string
    {
        return $this->penaltyIncomeAccount;
    }

    protected function resetStatistics(): void
    {
        $this->statistics = [
            'total_loans_processed' => 0,
            'upcoming_reminders_sent' => 0,
            'due_day_reminders_sent' => 0,
            'arrears_reminders_sent' => 0,
            'loans_in_arrears' => 0,
            'penalties_calculated' => 0,
            'total_penalty_amount' => 0,
            'classifications_updated' => 0,
            'legal_documents_generated' => 0,
            'errors' => 0,
        ];
    }

    /**
     * Main entry point - process all loan repayments and arrears
     */
    public function processAllLoans(?Carbon $processDate = null): array
    {
        $processDate = $processDate ?? Carbon::today();

        Log::info('=============================================');
        Log::info('[LOAN_ARREARS_MANAGEMENT] Starting Daily Processing');
        Log::info('[LOAN_ARREARS_MANAGEMENT] Process Date: ' . $processDate->format('Y-m-d'));
        Log::info('[LOAN_ARREARS_MANAGEMENT] Timestamp: ' . now()->toIso8601String());
        Log::info('=============================================');

        try {
            // 1. Send upcoming repayment reminders
            $this->sendUpcomingRepaymentReminders($processDate);

            // 2. Send due day reminders
            $this->sendDueDayReminders($processDate);

            // 3. Process loans in arrears
            $this->processArrearsLoans($processDate);

            // 4. Process legal actions for severely delinquent loans
            $this->processLegalActions($processDate);

            // 5. Generate daily summary report
            $this->generateDailySummary($processDate);

            Log::info('=============================================');
            Log::info('[LOAN_ARREARS_MANAGEMENT] Processing Completed');
            Log::info('[LOAN_ARREARS_MANAGEMENT] Total Loans: ' . $this->statistics['total_loans_processed']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Upcoming Reminders: ' . $this->statistics['upcoming_reminders_sent']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Due Day Reminders: ' . $this->statistics['due_day_reminders_sent']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Arrears Reminders: ' . $this->statistics['arrears_reminders_sent']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Loans in Arrears: ' . $this->statistics['loans_in_arrears']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Penalties Calculated: ' . $this->statistics['penalties_calculated']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Total Penalty Amount: TZS ' . number_format($this->statistics['total_penalty_amount'], 2));
            Log::info('[LOAN_ARREARS_MANAGEMENT] Legal Documents: ' . $this->statistics['legal_documents_generated']);
            Log::info('[LOAN_ARREARS_MANAGEMENT] Errors: ' . $this->statistics['errors']);
            Log::info('=============================================');

            return [
                'status' => 'success',
                'date' => $processDate->format('Y-m-d'),
                'statistics' => $this->statistics
            ];

        } catch (\Exception $e) {
            Log::error('[LOAN_ARREARS_MANAGEMENT] Processing Failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'date' => $processDate->format('Y-m-d'),
                'message' => $e->getMessage(),
                'statistics' => $this->statistics
            ];
        }
    }

    /**
     * Send reminders for upcoming repayments (3, 2, 1 days before due)
     */
    protected function sendUpcomingRepaymentReminders(Carbon $processDate): void
    {
        Log::info('[LOAN_ARREARS_MANAGEMENT] Processing upcoming repayment reminders');

        foreach (self::UPCOMING_REMINDER_DAYS as $daysBefore) {
            $dueDate = $processDate->copy()->addDays($daysBefore);

            $upcomingSchedules = $this->getSchedulesDueOn($dueDate);

            foreach ($upcomingSchedules as $schedule) {
                try {
                    // Check if notification already sent
                    if ($this->notificationAlreadySent($schedule->loan_id, "upcoming_{$daysBefore}days", $processDate)) {
                        continue;
                    }

                    $client = $this->getClientDetails($schedule->member_number ?? $schedule->client_number);
                    if (!$client || !$client->phone_number) {
                        continue;
                    }

                    $amountDue = $schedule->installment - ($schedule->payment ?? 0);
                    if ($amountDue <= 0) {
                        continue;
                    }

                    // Get loan for payment link
                    $loan = $this->getLoanBySchedule($schedule);
                    $paymentLink = $loan ? $this->getOrCreatePaymentLink($loan, $client, $amountDue) : null;

                    $message = $this->buildUpcomingReminderMessage($client, $schedule, $daysBefore, $amountDue, $paymentLink);
                    $this->sendNotificationWithLink($schedule, $client, $message, "upcoming_{$daysBefore}days", $processDate, $daysBefore, null, $paymentLink);
                    $this->statistics['upcoming_reminders_sent']++;

                } catch (\Exception $e) {
                    Log::error("[LOAN_ARREARS_MANAGEMENT] Error sending upcoming reminder for loan {$schedule->loan_id}: " . $e->getMessage());
                    $this->statistics['errors']++;
                }
            }
        }
    }

    /**
     * Send reminders on the due date
     */
    protected function sendDueDayReminders(Carbon $processDate): void
    {
        Log::info('[LOAN_ARREARS_MANAGEMENT] Processing due day reminders');

        $dueSchedules = $this->getSchedulesDueOn($processDate);

        foreach ($dueSchedules as $schedule) {
            try {
                if ($this->notificationAlreadySent($schedule->loan_id, 'due_day', $processDate)) {
                    continue;
                }

                $client = $this->getClientDetails($schedule->member_number ?? $schedule->client_number);
                if (!$client || !$client->phone_number) {
                    continue;
                }

                $amountDue = $schedule->installment - ($schedule->payment ?? 0);
                if ($amountDue <= 0) {
                    continue;
                }

                // Get loan for payment link
                $loan = $this->getLoanBySchedule($schedule);
                $paymentLink = $loan ? $this->getOrCreatePaymentLink($loan, $client, $amountDue) : null;

                $message = $this->buildDueDayMessage($client, $schedule, $amountDue, $paymentLink);
                $this->sendNotificationWithLink($schedule, $client, $message, 'due_day', $processDate, 0, null, $paymentLink);
                $this->statistics['due_day_reminders_sent']++;

            } catch (\Exception $e) {
                Log::error("[LOAN_ARREARS_MANAGEMENT] Error sending due day reminder for loan {$schedule->loan_id}: " . $e->getMessage());
                $this->statistics['errors']++;
            }
        }
    }

    /**
     * Process loans that are in arrears
     */
    protected function processArrearsLoans(Carbon $processDate): void
    {
        Log::info('[LOAN_ARREARS_MANAGEMENT] Processing loans in arrears');

        $loansInArrears = $this->getLoansInArrears($processDate);

        foreach ($loansInArrears as $loan) {
            try {
                $this->statistics['total_loans_processed']++;
                $this->statistics['loans_in_arrears']++;

                // Calculate days in arrears
                $daysInArrears = $this->calculateDaysInArrears($loan, $processDate);

                // Update loan's days in arrears
                $this->updateLoanArrearsStatus($loan, $daysInArrears, $processDate);

                // Update NPL classification
                $this->updateLoanClassification($loan, $daysInArrears);

                // Send arrears notification if applicable
                $this->sendArrearsNotification($loan, $daysInArrears, $processDate);

                // Calculate and post penalties (after 30 days)
                if ($daysInArrears > self::PENALTY_START_DAYS) {
                    $this->calculateAndPostPenalty($loan, $daysInArrears, $processDate);
                }

            } catch (\Exception $e) {
                Log::error("[LOAN_ARREARS_MANAGEMENT] Error processing arrears for loan {$loan->loan_id}: " . $e->getMessage());
                $this->statistics['errors']++;
            }
        }
    }

    /**
     * Send arrears notifications at specified intervals (every 5 days up to 120 days)
     */
    protected function sendArrearsNotification($loan, int $daysInArrears, Carbon $processDate): void
    {
        // Check if we should send notification for this day
        if (!in_array($daysInArrears, self::ARREARS_REMINDER_DAYS)) {
            return;
        }

        $notificationType = "arrears_{$daysInArrears}days";

        if ($this->notificationAlreadySent($loan->loan_id, $notificationType, $processDate)) {
            return;
        }

        $client = $this->getClientDetails($loan->client_number);
        if (!$client || !$client->phone_number) {
            return;
        }

        $outstandingAmount = $this->calculateOutstandingAmount($loan);

        // Get or create payment link
        $paymentLink = $this->getOrCreatePaymentLink($loan, $client, $outstandingAmount);

        $message = $this->buildArrearsMessage($client, $loan, $daysInArrears, $outstandingAmount, $paymentLink);

        $schedule = (object)[
            'loan_id' => $loan->loan_id,
            'member_number' => $loan->client_number,
            'installment_date' => null,
            'branch_id' => $loan->branch_id ?? 1
        ];

        $this->sendNotificationWithLink($schedule, $client, $message, $notificationType, $processDate, null, $daysInArrears, $paymentLink);
        $this->statistics['arrears_reminders_sent']++;

        Log::info("[LOAN_ARREARS_MANAGEMENT] Arrears notification sent for loan {$loan->loan_id} ({$daysInArrears} days)");
    }

    /**
     * Calculate and post penalty for overdue loans
     */
    protected function calculateAndPostPenalty($loan, int $daysInArrears, Carbon $processDate): void
    {
        // Check if already processed for today
        $existingAccrual = DB::table('loan_penalty_accruals')
            ->where('loan_id', $loan->loan_id)
            ->whereDate('accrual_date', $processDate)
            ->first();

        if ($existingAccrual) {
            return;
        }

        // Get penalty rate from loan product
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();

        if (!$product || !$product->penalty_value) {
            return;
        }

        $penaltyRate = floatval($product->penalty_value);
        $outstandingAmount = $this->calculateOutstandingAmount($loan);

        if ($outstandingAmount <= 0) {
            return;
        }

        // Calculate daily penalty (monthly rate / 30)
        $dailyPenaltyRate = $penaltyRate / 30 / 100;
        $dailyPenalty = round($outstandingAmount * $dailyPenaltyRate, 4);

        // Get previous cumulative penalty
        $previousAccrual = DB::table('loan_penalty_accruals')
            ->where('loan_id', $loan->loan_id)
            ->where('accrual_date', '<', $processDate->format('Y-m-d'))
            ->orderBy('accrual_date', 'desc')
            ->first();

        $previousCumulative = $previousAccrual ? floatval($previousAccrual->cumulative_penalty) : 0;
        $cumulativePenalty = $previousCumulative + $dailyPenalty;

        // Cap penalty if configured
        if ($product->penalty_max_cap && $cumulativePenalty > floatval($product->penalty_max_cap)) {
            $cumulativePenalty = floatval($product->penalty_max_cap);
            $dailyPenalty = $cumulativePenalty - $previousCumulative;
        }

        // Record penalty accrual
        DB::table('loan_penalty_accruals')->insert([
            'loan_id' => $loan->loan_id,
            'loan_account_number' => $loan->loan_account_number,
            'client_number' => $loan->client_number,
            'accrual_date' => $processDate->format('Y-m-d'),
            'outstanding_amount' => $outstandingAmount,
            'days_in_arrears' => $daysInArrears,
            'penalty_rate' => $penaltyRate,
            'rate_type' => 'monthly',
            'daily_penalty' => $dailyPenalty,
            'cumulative_penalty' => $cumulativePenalty,
            'penalty_paid' => 0,
            'penalty_balance' => $cumulativePenalty,
            'loan_classification' => $this->determineLoanClassification($daysInArrears),
            'posted_to_gl' => false,
            'year' => $processDate->year,
            'month' => $processDate->month,
            'branch_id' => $loan->branch_id ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->statistics['penalties_calculated']++;
        $this->statistics['total_penalty_amount'] += $dailyPenalty;

        // Post to GL (consolidated posting will be done at end of day)
        Log::info("[LOAN_ARREARS_MANAGEMENT] Penalty accrued for loan {$loan->loan_id}: TZS " . number_format($dailyPenalty, 4) .
            " (Rate: {$penaltyRate}%, Days: {$daysInArrears}, Cumulative: TZS " . number_format($cumulativePenalty, 2) . ")");
    }

    /**
     * Process legal actions for severely delinquent loans
     */
    protected function processLegalActions(Carbon $processDate): void
    {
        Log::info('[LOAN_ARREARS_MANAGEMENT] Processing legal actions');

        $delinquentLoans = DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE', 'RESTRUCTURED'])
            ->whereNotIn('loan_status', ['closed', 'CLOSED', 'LOSS', 'written_off'])
            ->where('days_in_arrears', '>=', self::WARNING_LETTER_DAYS)
            ->whereNotNull('disbursement_date')
            ->get();

        foreach ($delinquentLoans as $loan) {
            try {
                $daysInArrears = intval($loan->days_in_arrears);
                $documentType = $this->determineRequiredLegalDocument($daysInArrears);

                if (!$documentType) {
                    continue;
                }

                // Check if document already exists for this loan
                $existingDoc = DB::table('loan_legal_documents')
                    ->where('loan_id', $loan->loan_id)
                    ->where('document_type', $documentType)
                    ->whereIn('status', ['draft', 'approved', 'sent'])
                    ->first();

                if ($existingDoc) {
                    continue;
                }

                // Generate legal document
                $this->generateLegalDocument($loan, $documentType, $daysInArrears, $processDate);
                $this->statistics['legal_documents_generated']++;

            } catch (\Exception $e) {
                Log::error("[LOAN_ARREARS_MANAGEMENT] Error processing legal action for loan {$loan->loan_id}: " . $e->getMessage());
                $this->statistics['errors']++;
            }
        }
    }

    /**
     * Determine which legal document is required based on days in arrears
     */
    protected function determineRequiredLegalDocument(int $daysInArrears): ?string
    {
        if ($daysInArrears >= self::LEGAL_NOTICE_DAYS) {
            return 'legal_notice';
        } elseif ($daysInArrears >= self::FINAL_NOTICE_DAYS) {
            return 'final_notice';
        } elseif ($daysInArrears >= self::DEMAND_LETTER_DAYS) {
            return 'demand_letter';
        } elseif ($daysInArrears >= self::WARNING_LETTER_DAYS) {
            return 'warning_letter';
        }
        return null;
    }

    /**
     * Generate a legal document
     */
    protected function generateLegalDocument($loan, string $documentType, int $daysInArrears, Carbon $processDate): void
    {
        $documentNumber = $this->generateDocumentNumber($documentType);
        $outstandingAmount = $this->calculateOutstandingAmount($loan);
        $outstandingPrincipal = floatval($loan->principle ?? 0) - floatval($loan->total_principal_paid ?? 0);
        $outstandingInterest = floatval($loan->total_interest ?? 0) - floatval($loan->total_interest_paid ?? 0);

        // Get penalty balance
        $penaltyBalance = DB::table('loan_penalty_accruals')
            ->where('loan_id', $loan->loan_id)
            ->orderBy('accrual_date', 'desc')
            ->value('cumulative_penalty') ?? 0;

        // Calculate response deadline (14 days for warning, 7 days for others)
        $deadlineDays = $documentType === 'warning_letter' ? 14 : 7;
        $responseDeadline = $processDate->copy()->addDays($deadlineDays);

        DB::table('loan_legal_documents')->insert([
            'document_number' => $documentNumber,
            'loan_id' => $loan->loan_id,
            'client_number' => $loan->client_number,
            'document_type' => $documentType,
            'days_in_arrears' => $daysInArrears,
            'outstanding_amount' => $outstandingAmount,
            'principal_outstanding' => $outstandingPrincipal,
            'interest_outstanding' => $outstandingInterest,
            'penalty_outstanding' => $penaltyBalance,
            'generated_date' => $processDate->format('Y-m-d'),
            'response_deadline' => $responseDeadline->format('Y-m-d'),
            'status' => 'draft',
            'branch_id' => $loan->branch_id ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Log history
        $docId = DB::table('loan_legal_documents')
            ->where('document_number', $documentNumber)
            ->value('id');

        if ($docId) {
            DB::table('loan_legal_document_history')->insert([
                'document_id' => $docId,
                'action' => 'created',
                'old_status' => null,
                'new_status' => 'draft',
                'notes' => "Auto-generated for loan {$loan->loan_id} at {$daysInArrears} days in arrears",
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        Log::info("[LOAN_ARREARS_MANAGEMENT] Legal document generated: {$documentType} for loan {$loan->loan_id} ({$documentNumber})");
    }

    /**
     * Update loan classification based on days in arrears
     */
    protected function updateLoanClassification($loan, int $daysInArrears): void
    {
        $newClassification = $this->determineLoanClassification($daysInArrears);
        $currentClassification = $loan->loan_classification ?? 'PERFORMING';

        if ($newClassification !== $currentClassification) {
            DB::table('loans')
                ->where('id', $loan->id)
                ->update([
                    'loan_classification' => $newClassification,
                    'updated_at' => now()
                ]);

            $this->statistics['classifications_updated']++;

            Log::info("[LOAN_ARREARS_MANAGEMENT] Classification updated for loan {$loan->loan_id}: {$currentClassification} -> {$newClassification}");
        }
    }

    /**
     * Determine loan classification based on days in arrears
     */
    protected function determineLoanClassification(int $daysInArrears): string
    {
        if ($daysInArrears >= self::LOSS_THRESHOLD_DAYS) return 'LOSS';
        if ($daysInArrears >= self::DOUBTFUL_THRESHOLD_DAYS) return 'DOUBTFUL';
        if ($daysInArrears >= self::SUBSTANDARD_THRESHOLD_DAYS) return 'SUBSTANDARD';
        if ($daysInArrears >= self::WATCH_THRESHOLD_DAYS) return 'WATCH';
        return 'CURRENT';
    }

    /**
     * Update loan's arrears status
     */
    protected function updateLoanArrearsStatus($loan, int $daysInArrears, Carbon $processDate): void
    {
        $arrearsAmount = $this->calculateArrearsAmount($loan, $processDate);

        DB::table('loans')
            ->where('id', $loan->id)
            ->update([
                'days_in_arrears' => $daysInArrears,
                'total_days_in_arrears' => max($daysInArrears, intval($loan->total_days_in_arrears ?? 0)),
                'arrears_in_amount' => $arrearsAmount,
                'updated_at' => now()
            ]);
    }

    // ==================== HELPER METHODS ====================

    protected function getSchedulesDueOn(Carbon $dueDate)
    {
        return DB::table('loans_schedules as ls')
            ->join('loans as l', function($join) {
                $join->on('ls.loan_id', '=', DB::raw("l.id::text"))
                     ->orOn('ls.loan_id', '=', 'l.loan_id');
            })
            ->whereDate('ls.installment_date', $dueDate)
            ->whereIn('ls.completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID'])
            ->whereIn('l.status', ['DISBURSED', 'ACTIVE', 'RESTRUCTURED'])
            ->select('ls.*', 'l.client_number', 'l.loan_account_number', 'l.branch_id')
            ->get();
    }

    protected function getLoansInArrears(Carbon $processDate)
    {
        return DB::table('loans')
            ->whereIn('status', ['DISBURSED', 'ACTIVE', 'RESTRUCTURED'])
            ->whereNotIn('loan_status', ['closed', 'CLOSED', 'LOSS', 'written_off'])
            ->whereNotNull('disbursement_date')
            ->whereExists(function ($query) use ($processDate) {
                $query->select(DB::raw(1))
                    ->from('loans_schedules')
                    ->where(function($q) {
                        $q->whereColumn('loans_schedules.loan_id', 'loans.loan_id')
                          ->orWhereRaw('loans_schedules.loan_id = loans.id::text');
                    })
                    ->where('installment_date', '<', $processDate)
                    ->whereIn('completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID']);
            })
            ->get();
    }

    protected function calculateDaysInArrears($loan, Carbon $processDate): int
    {
        $oldestOverdue = DB::table('loans_schedules')
            ->where(function($query) use ($loan) {
                $query->where('loan_id', $loan->loan_id)
                      ->orWhere('loan_id', (string)$loan->id);
            })
            ->where('installment_date', '<', $processDate)
            ->whereIn('completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID'])
            ->orderBy('installment_date', 'asc')
            ->first();

        if (!$oldestOverdue) {
            return 0;
        }

        return Carbon::parse($oldestOverdue->installment_date)->diffInDays($processDate);
    }

    protected function calculateOutstandingAmount($loan): float
    {
        $schedules = DB::table('loans_schedules')
            ->where(function($query) use ($loan) {
                $query->where('loan_id', $loan->loan_id)
                      ->orWhere('loan_id', (string)$loan->id);
            })
            ->whereIn('completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID'])
            ->get();

        $total = 0;
        foreach ($schedules as $schedule) {
            $total += ($schedule->installment ?? 0) - ($schedule->payment ?? 0);
        }

        return max(0, round($total, 2));
    }

    protected function calculateArrearsAmount($loan, Carbon $processDate): float
    {
        $schedules = DB::table('loans_schedules')
            ->where(function($query) use ($loan) {
                $query->where('loan_id', $loan->loan_id)
                      ->orWhere('loan_id', (string)$loan->id);
            })
            ->where('installment_date', '<', $processDate)
            ->whereIn('completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID'])
            ->get();

        $total = 0;
        foreach ($schedules as $schedule) {
            $total += ($schedule->installment ?? 0) - ($schedule->payment ?? 0);
        }

        return max(0, round($total, 2));
    }

    protected function getClientDetails($clientNumber)
    {
        return DB::table('clients')
            ->where('client_number', $clientNumber)
            ->first();
    }

    protected function notificationAlreadySent(string $loanId, string $notificationType, Carbon $processDate): bool
    {
        return DB::table('loan_notification_schedule')
            ->where('loan_id', $loanId)
            ->where('notification_type', $notificationType)
            ->whereDate('scheduled_date', $processDate)
            ->where('status', 'sent')
            ->exists();
    }

    protected function sendNotification($schedule, $client, string $message, string $notificationType, Carbon $processDate, ?int $daysBefore = null, ?int $daysInArrears = null): void
    {
        // Record notification
        $notificationId = DB::table('loan_notification_schedule')->insertGetId([
            'loan_id' => $schedule->loan_id,
            'client_number' => $client->client_number ?? null,
            'notification_type' => $notificationType,
            'scheduled_date' => $processDate->format('Y-m-d'),
            'due_date' => $schedule->installment_date ?? null,
            'amount_due' => $schedule->installment ?? 0,
            'outstanding_amount' => ($schedule->installment ?? 0) - ($schedule->payment ?? 0),
            'days_before_due' => $daysBefore,
            'days_in_arrears' => $daysInArrears,
            'channel' => 'sms',
            'status' => 'pending',
            'message_content' => $message,
            'phone_number' => $client->phone_number ?? null,
            'email' => $client->email ?? null,
            'branch_id' => $schedule->branch_id ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        try {
            // Send SMS
            $result = $this->smsService->send($client->phone_number, $message, $client, [
                'smsType' => 'TRANSACTIONAL',
                'serviceName' => 'SACCOSS',
                'language' => 'English'
            ]);

            // Update notification status
            DB::table('loan_notification_schedule')
                ->where('id', $notificationId)
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'external_reference' => $result['notification_ref'] ?? null,
                    'updated_at' => now()
                ]);

        } catch (\Exception $e) {
            DB::table('loan_notification_schedule')
                ->where('id', $notificationId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => 1,
                    'updated_at' => now()
                ]);

            throw $e;
        }
    }

    /**
     * Send notification with payment link - handles SMS, email, and long message splitting
     */
    protected function sendNotificationWithLink($schedule, $client, string $message, string $notificationType, Carbon $processDate, ?int $daysBefore = null, ?int $daysInArrears = null, ?string $paymentLink = null): void
    {
        // Record notification
        $notificationId = DB::table('loan_notification_schedule')->insertGetId([
            'loan_id' => $schedule->loan_id,
            'client_number' => $client->client_number ?? null,
            'notification_type' => $notificationType,
            'scheduled_date' => $processDate->format('Y-m-d'),
            'due_date' => $schedule->installment_date ?? null,
            'amount_due' => $schedule->installment ?? 0,
            'outstanding_amount' => ($schedule->installment ?? 0) - ($schedule->payment ?? 0),
            'days_before_due' => $daysBefore,
            'days_in_arrears' => $daysInArrears,
            'channel' => 'sms_email',
            'status' => 'pending',
            'message_content' => $message,
            'phone_number' => $client->phone_number ?? null,
            'email' => $client->email ?? null,
            'branch_id' => $schedule->branch_id ?? 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $smsSent = false;
        $emailSent = false;
        $smsError = null;

        try {
            // Split message for SMS if needed, keeping payment link intact
            $smsParts = $this->splitMessageForSms($message, $paymentLink);

            // Send each SMS part
            foreach ($smsParts as $partIndex => $smsPart) {
                $result = $this->smsService->send($client->phone_number, $smsPart, $client, [
                    'smsType' => 'TRANSACTIONAL',
                    'serviceName' => 'SACCOSS',
                    'language' => 'English'
                ]);

                if ($partIndex === 0) {
                    // Update with first part's reference
                    DB::table('loan_notification_schedule')
                        ->where('id', $notificationId)
                        ->update([
                            'external_reference' => $result['notification_ref'] ?? null,
                            'updated_at' => now()
                        ]);
                }
            }
            $smsSent = true;

        } catch (\Exception $e) {
            $smsError = $e->getMessage();
            Log::warning("[LOAN_ARREARS_MANAGEMENT] SMS failed for {$client->phone_number}: " . $e->getMessage());
        }

        // Send email notification
        try {
            $subject = $this->getEmailSubject($notificationType, $daysInArrears);
            $this->sendEmailNotification($client, $subject, $message, $paymentLink);
            $emailSent = true;
        } catch (\Exception $e) {
            Log::warning("[LOAN_ARREARS_MANAGEMENT] Email failed for {$client->email}: " . $e->getMessage());
        }

        // Update notification status
        $status = ($smsSent || $emailSent) ? 'sent' : 'failed';
        $errorMessage = !$smsSent && !$emailSent ? $smsError : null;

        DB::table('loan_notification_schedule')
            ->where('id', $notificationId)
            ->update([
                'status' => $status,
                'sent_at' => ($smsSent || $emailSent) ? now() : null,
                'error_message' => $errorMessage,
                'updated_at' => now()
            ]);

        if (!$smsSent && !$emailSent) {
            throw new \Exception("Both SMS and email failed: " . $smsError);
        }
    }

    /**
     * Get email subject based on notification type
     */
    protected function getEmailSubject(string $notificationType, ?int $daysInArrears = null): string
    {
        if (str_starts_with($notificationType, 'upcoming_')) {
            return 'Loan Repayment Reminder - Payment Due Soon';
        } elseif ($notificationType === 'due_day') {
            return 'Loan Repayment Due Today - Action Required';
        } elseif (str_starts_with($notificationType, 'arrears_')) {
            if ($daysInArrears >= 90) {
                return 'URGENT: Loan Payment Severely Overdue - Immediate Action Required';
            } elseif ($daysInArrears >= 60) {
                return 'URGENT: Loan Payment Overdue - Action Required';
            } elseif ($daysInArrears >= 30) {
                return 'Important: Loan Payment Overdue - Please Pay Now';
            }
            return 'Loan Payment Reminder - Account Overdue';
        }
        return 'Loan Payment Notification';
    }

    /**
     * Get loan by schedule
     */
    protected function getLoanBySchedule($schedule)
    {
        return DB::table('loans')
            ->where(function($query) use ($schedule) {
                $query->where('loan_id', $schedule->loan_id)
                      ->orWhere('id', is_numeric($schedule->loan_id) ? $schedule->loan_id : 0);
            })
            ->first();
    }

    // ==================== MESSAGE TEMPLATES ====================

    /**
     * Build upcoming reminder message with payment link
     */
    protected function buildUpcomingReminderMessage($client, $schedule, int $daysBefore, float $amountDue, ?string $paymentLink = null): string
    {
        $dueDate = Carbon::parse($schedule->installment_date)->format('d/m/Y');
        $daysText = $daysBefore === 1 ? 'tomorrow' : "in {$daysBefore} days";

        $message = "Dear {$client->first_name}, your loan repayment of TZS " . number_format($amountDue, 0) .
               " is due {$daysText} ({$dueDate}). Please pay to avoid penalties.";

        if ($paymentLink) {
            $message .= " Pay now: {$paymentLink}";
        }

        return $message;
    }

    /**
     * Build due day message with payment link
     */
    protected function buildDueDayMessage($client, $schedule, float $amountDue, ?string $paymentLink = null): string
    {
        $message = "Dear {$client->first_name}, your loan repayment of TZS " . number_format($amountDue, 0) .
               " is due TODAY. Please pay now to avoid penalties.";

        if ($paymentLink) {
            $message .= " Pay here: {$paymentLink}";
        }

        return $message;
    }

    /**
     * Build arrears message with payment link
     */
    protected function buildArrearsMessage($client, $loan, int $daysInArrears, float $outstandingAmount, ?string $paymentLink = null): string
    {
        $penaltyWarning = $daysInArrears >= self::PENALTY_START_DAYS
            ? " Penalties apply daily."
            : "";

        $message = "Dear {$client->first_name}, loan {$loan->loan_id} is {$daysInArrears} days overdue. " .
               "Outstanding: TZS " . number_format($outstandingAmount, 0) . ".{$penaltyWarning} Pay urgently.";

        if ($paymentLink) {
            $message .= " Pay: {$paymentLink}";
        }

        return $message;
    }

    /**
     * Get or create payment link for a loan
     */
    protected function getOrCreatePaymentLink($loan, $client, float $amount): ?string
    {
        try {
            // Check for existing active payment link
            $existingLink = DB::table('payment_links')
                ->where('loan_id', $loan->id)
                ->where('status', 'ACTIVE')
                ->where('expires_at', '>', now())
                ->first();

            if ($existingLink && $existingLink->payment_url) {
                return $existingLink->payment_url;
            }

            // Get loan schedules for generating new payment link
            $schedules = DB::table('loans_schedules')
                ->where(function($query) use ($loan) {
                    $query->where('loan_id', $loan->loan_id)
                          ->orWhere('loan_id', (string)$loan->id);
                })
                ->whereIn('completion_status', ['PENDING', 'ACTIVE', 'PARTIAL', 'NOT PAID'])
                ->orderBy('installment_date', 'asc')
                ->get();

            if ($schedules->isEmpty()) {
                return null;
            }

            // Generate payment link
            $response = $this->paymentLinkService->generateLoanInstallmentsPaymentLink(
                $loan->id,
                $client,
                $schedules,
                [
                    'description' => "Loan Repayment - {$loan->loan_id}",
                    'expires_at' => Carbon::now()->addDays(30)->toIso8601String()
                ]
            );

            if (isset($response['data']['payment_url'])) {
                // Store payment link
                DB::table('payment_links')->insert([
                    'loan_id' => $loan->id,
                    'client_number' => $client->client_number,
                    'link_id' => $response['data']['link_id'] ?? null,
                    'short_code' => $response['data']['short_code'] ?? null,
                    'payment_url' => $response['data']['payment_url'],
                    'qr_code_data' => $response['data']['qr_code_data'] ?? null,
                    'total_amount' => $amount,
                    'currency' => 'TZS',
                    'description' => "Loan Repayment - {$loan->loan_id}",
                    'expires_at' => Carbon::now()->addDays(30),
                    'response_data' => json_encode($response),
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info("[LOAN_ARREARS_MANAGEMENT] Payment link created for loan {$loan->loan_id}");
                return $response['data']['payment_url'];
            }

            return null;

        } catch (\Exception $e) {
            Log::warning("[LOAN_ARREARS_MANAGEMENT] Failed to get/create payment link for loan {$loan->loan_id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Split long message into multiple SMS parts while preserving the payment link
     */
    protected function splitMessageForSms(string $message, ?string $paymentLink = null): array
    {
        // If message fits in single SMS
        if (strlen($message) <= self::SMS_SINGLE_LIMIT) {
            return [$message];
        }

        // Calculate available space for content (reserving space for payment link in last part)
        $linkLength = $paymentLink ? strlen(" Pay: {$paymentLink}") : 0;
        $maxContentLength = (self::SMS_CONCAT_PART_LIMIT * self::SMS_MAX_PARTS) - $linkLength;

        // If full message (without link) is too long, truncate the content part
        $contentWithoutLink = $paymentLink ? str_replace(" Pay: {$paymentLink}", "", $message) : $message;
        $contentWithoutLink = str_replace([" Pay now: {$paymentLink}", " Pay here: {$paymentLink}"], "", $contentWithoutLink);

        if (strlen($contentWithoutLink) > $maxContentLength) {
            $contentWithoutLink = substr($contentWithoutLink, 0, $maxContentLength - 3) . "...";
        }

        // Rebuild message with payment link at the end
        $fullMessage = $paymentLink ? $contentWithoutLink . " Pay: {$paymentLink}" : $contentWithoutLink;

        // If it now fits in concatenated SMS limits, return as single message
        // (the SMS service will handle the concatenation)
        if (strlen($fullMessage) <= self::SMS_CONCAT_PART_LIMIT * self::SMS_MAX_PARTS) {
            return [$fullMessage];
        }

        // Otherwise split into parts, keeping payment link only in the last part
        $parts = [];
        $remainingContent = $contentWithoutLink;
        $partNumber = 1;
        $maxParts = self::SMS_MAX_PARTS;

        while (strlen($remainingContent) > 0 && $partNumber <= $maxParts) {
            $isLastPart = $partNumber === $maxParts || strlen($remainingContent) <= self::SMS_CONCAT_PART_LIMIT;

            if ($isLastPart) {
                // Last part includes remaining content + payment link
                $availableForContent = self::SMS_CONCAT_PART_LIMIT - $linkLength;
                $partContent = substr($remainingContent, 0, $availableForContent);
                $parts[] = $partContent . ($paymentLink ? " Pay: {$paymentLink}" : "");
                break;
            } else {
                // Regular part - just content with continuation marker
                $partContent = substr($remainingContent, 0, self::SMS_CONCAT_PART_LIMIT - 6) . " ({$partNumber}/" . $maxParts . ")";
                $parts[] = $partContent;
                $remainingContent = substr($remainingContent, self::SMS_CONCAT_PART_LIMIT - 6);
            }

            $partNumber++;
        }

        return $parts;
    }

    /**
     * Send email notification with payment link
     */
    protected function sendEmailNotification($client, string $subject, string $message, ?string $paymentLink = null): void
    {
        if (!$client->email) {
            return;
        }

        try {
            $htmlMessage = nl2br(htmlspecialchars($message));

            if ($paymentLink) {
                $htmlMessage .= "<br><br><a href='{$paymentLink}' style='background-color:#366092;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Pay Now</a>";
            }

            Mail::html($htmlMessage, function($mail) use ($client, $subject) {
                $mail->to($client->email)
                     ->subject($subject);
            });

            Log::info("[LOAN_ARREARS_MANAGEMENT] Email sent to {$client->email}");

        } catch (\Exception $e) {
            Log::warning("[LOAN_ARREARS_MANAGEMENT] Email failed to {$client->email}: " . $e->getMessage());
        }
    }

    protected function generateDocumentNumber(string $documentType): string
    {
        $prefixes = [
            'warning_letter' => 'WL',
            'demand_letter' => 'DL',
            'final_notice' => 'FN',
            'legal_notice' => 'LN'
        ];

        $prefix = $prefixes[$documentType] ?? 'DOC';
        $date = now()->format('Ymd');
        $sequence = DB::table('loan_legal_documents')
            ->whereDate('created_at', now())
            ->count() + 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }

    protected function generateDailySummary(Carbon $processDate): void
    {
        // Store daily summary for reporting
        DB::table('loan_penalty_accrual_summaries')->updateOrInsert(
            ['accrual_date' => $processDate->format('Y-m-d')],
            [
                'total_loans_processed' => $this->statistics['loans_in_arrears'],
                'loans_with_penalty' => $this->statistics['penalties_calculated'],
                'total_penalty_accrued' => $this->statistics['total_penalty_amount'],
                'journal_entries_created' => 0, // Will be updated by batch GL posting
                'status' => 'completed',
                'updated_at' => now()
            ]
        );
    }

    /**
     * Post consolidated penalty accruals to GL
     * Called separately after all individual accruals are recorded
     */
    public function postPenaltyAccrualsToGL(Carbon $processDate): array
    {
        $unpoostedAccruals = DB::table('loan_penalty_accruals')
            ->whereDate('accrual_date', $processDate)
            ->where('posted_to_gl', false)
            ->where('daily_penalty', '>', 0)
            ->get();

        if ($unpoostedAccruals->isEmpty()) {
            return ['status' => 'skipped', 'message' => 'No unposted penalty accruals'];
        }

        $totalPenalty = $unpoostedAccruals->sum('daily_penalty');
        $loansCount = $unpoostedAccruals->count();

        try {
            // Post consolidated journal entry using accounts from institutions table
            $transactionData = [
                'first_account' => $this->penaltyReceivableAccount,
                'second_account' => $this->penaltyIncomeAccount,
                'amount' => round($totalPenalty, 2),
                'narration' => "Daily loan penalty accrual for {$processDate->format('Y-m-d')} ({$loansCount} loans, TZS " . number_format($totalPenalty, 2) . ")"
            ];

            $result = $this->transactionService->postTransaction($transactionData);

            // Update accruals as posted
            DB::table('loan_penalty_accruals')
                ->whereDate('accrual_date', $processDate)
                ->where('posted_to_gl', false)
                ->update([
                    'posted_to_gl' => true,
                    'journal_reference' => $result['reference_number'] ?? null,
                    'posted_at' => now(),
                    'updated_at' => now()
                ]);

            // Update summary
            DB::table('loan_penalty_accrual_summaries')
                ->where('accrual_date', $processDate->format('Y-m-d'))
                ->update([
                    'journal_entries_created' => 1,
                    'updated_at' => now()
                ]);

            Log::info("[LOAN_ARREARS_MANAGEMENT] Penalty GL posting completed: TZS " . number_format($totalPenalty, 2) .
                " for {$loansCount} loans, Ref: " . ($result['reference_number'] ?? 'N/A'));

            return [
                'status' => 'success',
                'total_penalty' => $totalPenalty,
                'loans_count' => $loansCount,
                'reference' => $result['reference_number'] ?? null
            ];

        } catch (\Exception $e) {
            Log::error("[LOAN_ARREARS_MANAGEMENT] Penalty GL posting failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
