<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Loan Classification Service (NPL Classification)
 *
 * Classifies loans based on days in arrears according to BOT/IFRS standards:
 * - CURRENT: 0-30 days (0% provision)
 * - WATCH: 31-90 days (10% provision)
 * - SUBSTANDARD: 91-180 days (30% provision)
 * - DOUBTFUL: 181-365 days (50% provision)
 * - LOSS: >365 days (100% provision)
 *
 * @package App\Services
 * @version 1.0
 */
class LoanClassificationService
{
    /**
     * Classification thresholds and provision rates
     */
    const CLASSIFICATIONS = [
        'CURRENT' => [
            'min_days' => 0,
            'max_days' => 30,
            'provision_rate' => 0,
            'description' => 'Performing loan - no arrears or up to 30 days'
        ],
        'WATCH' => [
            'min_days' => 31,
            'max_days' => 90,
            'provision_rate' => 10,
            'description' => 'Watch list - early warning signs of credit deterioration'
        ],
        'SUBSTANDARD' => [
            'min_days' => 91,
            'max_days' => 180,
            'provision_rate' => 30,
            'description' => 'Substandard - well-defined credit weaknesses'
        ],
        'DOUBTFUL' => [
            'min_days' => 181,
            'max_days' => 365,
            'provision_rate' => 50,
            'description' => 'Doubtful - collection in full is highly questionable'
        ],
        'LOSS' => [
            'min_days' => 366,
            'max_days' => PHP_INT_MAX,
            'provision_rate' => 100,
            'description' => 'Loss - considered uncollectible, write-off candidate'
        ]
    ];

    protected $statistics = [
        'total_loans' => 0,
        'classified' => 0,
        'upgraded' => 0,
        'downgraded' => 0,
        'unchanged' => 0,
        'by_classification' => [
            'CURRENT' => 0,
            'WATCH' => 0,
            'SUBSTANDARD' => 0,
            'DOUBTFUL' => 0,
            'LOSS' => 0
        ],
        'total_provision' => 0,
        'provision_change' => 0
    ];

    protected $transactionService;

    public function __construct()
    {
        $this->transactionService = new TransactionPostingService();
    }

    /**
     * Run daily loan classification
     */
    public function runDailyClassification(?Carbon $processDate = null): array
    {
        $processDate = $processDate ?? Carbon::today();

        Log::info('[LOAN_CLASSIFICATION] Starting daily NPL classification', [
            'process_date' => $processDate->format('Y-m-d')
        ]);

        $this->resetStatistics();

        try {
            // Get all active loans
            $loans = $this->getActiveLoans();
            $this->statistics['total_loans'] = $loans->count();

            Log::info('[LOAN_CLASSIFICATION] Found active loans', [
                'count' => $loans->count()
            ]);

            foreach ($loans as $loan) {
                $this->classifyLoan($loan, $processDate);
            }

            // Post provision expense if there's a change
            if ($this->statistics['provision_change'] != 0) {
                $this->postProvisionAdjustment($processDate);
            }

            // Log summary
            $this->logClassificationSummary($processDate);

            return [
                'status' => 'success',
                'date' => $processDate->format('Y-m-d'),
                'statistics' => $this->statistics
            ];

        } catch (\Exception $e) {
            Log::error('[LOAN_CLASSIFICATION] Classification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'statistics' => $this->statistics
            ];
        }
    }

    /**
     * Classify a single loan
     */
    protected function classifyLoan($loan, Carbon $processDate): void
    {
        // Calculate days in arrears from schedules
        $daysInArrears = $this->calculateDaysInArrears($loan);

        // Get outstanding balances
        $outstandingBalances = $this->getOutstandingBalances($loan);

        // Determine new classification
        $newClassification = $this->determineClassification($daysInArrears);
        $newProvisionRate = self::CLASSIFICATIONS[$newClassification]['provision_rate'];

        // Calculate provision amount based on outstanding principal
        $outstandingPrincipal = $outstandingBalances['principal'];
        $newProvisionAmount = round($outstandingPrincipal * ($newProvisionRate / 100), 2);

        // Get previous classification
        $previousClassification = $loan->npl_classification ?? 'CURRENT';
        $previousProvisionRate = $loan->provision_rate ?? 0;
        $previousProvisionAmount = $loan->provision_amount ?? 0;

        // Check if classification changed
        $classificationChanged = ($previousClassification !== $newClassification);

        // Track statistics
        $this->statistics['classified']++;
        $this->statistics['by_classification'][$newClassification]++;
        $this->statistics['total_provision'] += $newProvisionAmount;
        $this->statistics['provision_change'] += ($newProvisionAmount - $previousProvisionAmount);

        if ($classificationChanged) {
            $classificationOrder = ['CURRENT' => 1, 'WATCH' => 2, 'SUBSTANDARD' => 3, 'DOUBTFUL' => 4, 'LOSS' => 5];
            $previousOrder = $classificationOrder[$previousClassification] ?? 1;
            $newOrder = $classificationOrder[$newClassification];

            if ($newOrder > $previousOrder) {
                $this->statistics['downgraded']++;
                $direction = 'DOWNGRADED';
            } else {
                $this->statistics['upgraded']++;
                $direction = 'UPGRADED';
            }

            Log::info("[LOAN_CLASSIFICATION] Loan {$direction}", [
                'loan_id' => $loan->loan_id,
                'from' => $previousClassification,
                'to' => $newClassification,
                'days_in_arrears' => $daysInArrears,
                'provision_change' => $newProvisionAmount - $previousProvisionAmount
            ]);

            // Record history
            $this->recordClassificationHistory($loan, $previousClassification, $newClassification,
                $previousProvisionRate, $newProvisionRate, $previousProvisionAmount, $newProvisionAmount,
                $daysInArrears, $outstandingBalances, $processDate);
        } else {
            $this->statistics['unchanged']++;
        }

        // Update loan record
        DB::table('loans')
            ->where('id', $loan->id)
            ->update([
                'npl_classification' => $newClassification,
                'provision_rate' => $newProvisionRate,
                'provision_amount' => $newProvisionAmount,
                'previous_classification' => $previousClassification,
                'classified_at' => now(),
                'days_in_arrears' => $daysInArrears,
                'updated_at' => now()
            ]);
    }

    /**
     * Calculate days in arrears from loan schedules
     */
    protected function calculateDaysInArrears($loan): int
    {
        // Find the oldest unpaid/partial schedule that is past due
        $oldestOverdueSchedule = DB::table('loans_schedules')
            ->where(function($query) use ($loan) {
                $query->where('loan_id', (string)$loan->id)
                      ->orWhere('loan_id', $loan->loan_id);
            })
            ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID'])
            ->where('installment_date', '<', Carbon::today())
            ->orderBy('installment_date', 'asc')
            ->first();

        if (!$oldestOverdueSchedule) {
            return 0;
        }

        return Carbon::parse($oldestOverdueSchedule->installment_date)->diffInDays(Carbon::today());
    }

    /**
     * Get outstanding balances for a loan
     */
    protected function getOutstandingBalances($loan): array
    {
        $schedules = DB::table('loans_schedules')
            ->where(function($query) use ($loan) {
                $query->where('loan_id', (string)$loan->id)
                      ->orWhere('loan_id', $loan->loan_id);
            })
            ->whereIn('completion_status', ['PENDING', 'PARTIAL', 'ACTIVE', 'NOT PAID'])
            ->get();

        $principal = 0;
        $interest = 0;

        foreach ($schedules as $schedule) {
            $principal += ($schedule->principle - ($schedule->principle_payment ?? 0));
            $interest += ($schedule->interest - ($schedule->interest_payment ?? 0));
        }

        return [
            'principal' => round($principal, 2),
            'interest' => round($interest, 2),
            'total' => round($principal + $interest, 2)
        ];
    }

    /**
     * Determine classification based on days in arrears
     */
    protected function determineClassification(int $daysInArrears): string
    {
        foreach (self::CLASSIFICATIONS as $classification => $criteria) {
            if ($daysInArrears >= $criteria['min_days'] && $daysInArrears <= $criteria['max_days']) {
                return $classification;
            }
        }

        return 'LOSS'; // Default to LOSS if over 365 days
    }

    /**
     * Record classification history
     */
    protected function recordClassificationHistory($loan, $previousClassification, $newClassification,
        $previousProvisionRate, $newProvisionRate, $previousProvisionAmount, $newProvisionAmount,
        $daysInArrears, $outstandingBalances, $processDate): void
    {
        DB::table('loan_classification_history')->insert([
            'loan_id' => $loan->loan_id,
            'previous_classification' => $previousClassification,
            'new_classification' => $newClassification,
            'previous_provision_rate' => $previousProvisionRate,
            'new_provision_rate' => $newProvisionRate,
            'previous_provision_amount' => $previousProvisionAmount,
            'new_provision_amount' => $newProvisionAmount,
            'days_in_arrears' => $daysInArrears,
            'outstanding_principal' => $outstandingBalances['principal'],
            'outstanding_interest' => $outstandingBalances['interest'],
            'outstanding_total' => $outstandingBalances['total'],
            'classification_date' => $processDate->toDateString(),
            'notes' => "Auto-classified based on {$daysInArrears} days in arrears",
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Post provision adjustment to GL
     */
    protected function postProvisionAdjustment(Carbon $processDate): void
    {
        $provisionChange = $this->statistics['provision_change'];

        if ($provisionChange == 0) {
            return;
        }

        // Get provision accounts from institutions table
        $institution = DB::table('institutions')->where('id', 1)->first();

        // Default accounts if not configured
        $provisionExpenseAccount = $institution->provision_expense_account ?? '0101500050005010';
        $provisionReserveAccount = $institution->provision_reserve_account ?? '0101200020002010';

        $narration = sprintf(
            "Loan loss provision adjustment for %s - Net change: TZS %s",
            $processDate->format('Y-m-d'),
            number_format(abs($provisionChange), 2)
        );

        if ($provisionChange > 0) {
            // Increase in provision (expense)
            // DR: Provision Expense (P&L)
            // CR: Provision Reserve (Balance Sheet - contra asset)
            $result = $this->transactionService->postTransaction([
                'first_account' => $provisionReserveAccount,
                'second_account' => $provisionExpenseAccount,
                'amount' => abs($provisionChange),
                'narration' => $narration,
                'action' => 'loan_provision_increase'
            ]);
        } else {
            // Decrease in provision (reversal)
            // DR: Provision Reserve
            // CR: Provision Expense (recovery)
            $result = $this->transactionService->postTransaction([
                'first_account' => $provisionExpenseAccount,
                'second_account' => $provisionReserveAccount,
                'amount' => abs($provisionChange),
                'narration' => $narration,
                'action' => 'loan_provision_decrease'
            ]);
        }

        Log::info('[LOAN_CLASSIFICATION] Provision adjustment posted', [
            'change' => $provisionChange,
            'type' => $provisionChange > 0 ? 'INCREASE' : 'DECREASE',
            'reference' => $result['reference_number'] ?? null
        ]);
    }

    /**
     * Get all active loans for classification
     */
    protected function getActiveLoans()
    {
        return DB::table('loans')
            ->whereIn('status', ['ACTIVE', 'RESTRUCTURED', 'DISBURSED'])
            ->get();
    }

    /**
     * Reset statistics
     */
    protected function resetStatistics(): void
    {
        $this->statistics = [
            'total_loans' => 0,
            'classified' => 0,
            'upgraded' => 0,
            'downgraded' => 0,
            'unchanged' => 0,
            'by_classification' => [
                'CURRENT' => 0,
                'WATCH' => 0,
                'SUBSTANDARD' => 0,
                'DOUBTFUL' => 0,
                'LOSS' => 0
            ],
            'total_provision' => 0,
            'provision_change' => 0
        ];
    }

    /**
     * Log classification summary
     */
    protected function logClassificationSummary(Carbon $processDate): void
    {
        Log::info('[LOAN_CLASSIFICATION] Daily classification completed', [
            'date' => $processDate->format('Y-m-d'),
            'total_loans' => $this->statistics['total_loans'],
            'classified' => $this->statistics['classified'],
            'upgraded' => $this->statistics['upgraded'],
            'downgraded' => $this->statistics['downgraded'],
            'unchanged' => $this->statistics['unchanged'],
            'by_classification' => $this->statistics['by_classification'],
            'total_provision' => number_format($this->statistics['total_provision'], 2),
            'provision_change' => number_format($this->statistics['provision_change'], 2)
        ]);
    }

    /**
     * Get classification summary report
     */
    public function getClassificationReport(): array
    {
        $report = DB::table('loans')
            ->whereIn('status', ['ACTIVE', 'RESTRUCTURED', 'DISBURSED'])
            ->select(
                'npl_classification',
                DB::raw('COUNT(*) as loan_count'),
                DB::raw('SUM(principle) as total_principal'),
                DB::raw('SUM(provision_amount) as total_provision'),
                DB::raw('AVG(days_in_arrears) as avg_days_in_arrears')
            )
            ->groupBy('npl_classification')
            ->get()
            ->keyBy('npl_classification');

        $summary = [];
        foreach (self::CLASSIFICATIONS as $classification => $criteria) {
            $data = $report[$classification] ?? null;
            $summary[$classification] = [
                'loan_count' => $data->loan_count ?? 0,
                'total_principal' => $data->total_principal ?? 0,
                'total_provision' => $data->total_provision ?? 0,
                'provision_rate' => $criteria['provision_rate'],
                'avg_days_in_arrears' => round($data->avg_days_in_arrears ?? 0),
                'description' => $criteria['description']
            ];
        }

        return $summary;
    }

    /**
     * Get loans by classification
     */
    public function getLoansByClassification(string $classification, int $limit = 100): \Illuminate\Support\Collection
    {
        return DB::table('loans as l')
            ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
            ->where('l.npl_classification', $classification)
            ->whereIn('l.status', ['ACTIVE', 'RESTRUCTURED', 'DISBURSED'])
            ->select(
                'l.id',
                'l.loan_id',
                'l.client_number',
                DB::raw("CONCAT(c.first_name, ' ', c.last_name) as client_name"),
                'l.principle',
                'l.days_in_arrears',
                'l.provision_rate',
                'l.provision_amount',
                'l.classified_at',
                'l.previous_classification'
            )
            ->orderBy('l.days_in_arrears', 'desc')
            ->limit($limit)
            ->get();
    }
}
