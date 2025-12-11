<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service for calculating and managing loan interest receivable
 *
 * This service handles:
 * - Calculating interest receivable for active loans
 * - Tracking accrued vs paid interest
 * - Managing overdue interest
 */
class LoanInterestReceivableService
{
    /**
     * Calculate interest receivable for all active loans
     *
     * @param string|null $date The date to calculate for (defaults to today)
     * @return array Summary of interest receivable
     */
    public function calculateInterestReceivable($date = null)
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');

        try {
            $loanInterest = DB::table('loans as l')
                ->select(
                    'l.id',
                    'l.loan_id',
                    'l.loan_account_number',
                    'l.client_id',
                    'l.client_number',
                    'l.principle',
                    'l.interest',
                    'l.total_interest',
                    'l.tenure',
                    'l.status',
                    'l.loan_status',
                    'l.disbursement_date',
                    'l.interest_method',
                    DB::raw('COALESCE(SUM(ls.interest), 0) as total_interest_scheduled'),
                    DB::raw('COALESCE(SUM(ls.interest_payment), 0) as total_interest_paid'),
                    DB::raw('COALESCE(SUM(ls.interest - COALESCE(ls.interest_payment, 0)), 0) as total_interest_receivable'),
                    DB::raw('COALESCE(SUM(CASE WHEN ls.installment_date <= \'' . $date . '\' AND ls.completion_status != \'PAID\' THEN ls.interest - COALESCE(ls.interest_payment, 0) ELSE 0 END), 0) as overdue_interest'),
                    DB::raw('COALESCE(SUM(CASE WHEN ls.installment_date > \'' . $date . '\' THEN ls.interest ELSE 0 END), 0) as future_interest'),
                    DB::raw('COUNT(DISTINCT ls.id) as total_installments'),
                    DB::raw('SUM(CASE WHEN ls.completion_status = \'PAID\' THEN 1 ELSE 0 END) as paid_installments')
                )
                ->leftJoin('loans_schedules as ls', 'l.loan_id', '=', 'ls.loan_id')
                ->where('l.status', 'ACTIVE')
                ->groupBy(
                    'l.id',
                    'l.loan_id',
                    'l.loan_account_number',
                    'l.client_id',
                    'l.client_number',
                    'l.principle',
                    'l.interest',
                    'l.total_interest',
                    'l.tenure',
                    'l.status',
                    'l.loan_status',
                    'l.disbursement_date',
                    'l.interest_method'
                )
                ->get();

            $summary = [
                'total_loans' => $loanInterest->count(),
                'total_principal' => $loanInterest->sum('principle'),
                'total_interest_scheduled' => $loanInterest->sum('total_interest_scheduled'),
                'total_interest_paid' => $loanInterest->sum('total_interest_paid'),
                'total_interest_receivable' => $loanInterest->sum('total_interest_receivable'),
                'overdue_interest' => $loanInterest->sum('overdue_interest'),
                'future_interest' => $loanInterest->sum('future_interest'),
                'calculation_date' => $date
            ];

            return [
                'loans' => $loanInterest,
                'summary' => $summary
            ];

        } catch (\Exception $e) {
            Log::error('LoanInterestReceivableService: Error calculating interest receivable', [
                'error' => $e->getMessage(),
                'date' => $date
            ]);
            throw $e;
        }
    }

    /**
     * Calculate interest receivable for a specific loan
     *
     * @param string $loanId The loan ID
     * @return array Detailed interest receivable breakdown
     */
    public function calculateLoanInterestReceivable($loanId)
    {
        try {
            $loan = DB::table('loans')
                ->where('loan_id', $loanId)
                ->first();

            if (!$loan) {
                throw new \Exception("Loan not found: {$loanId}");
            }

            $schedules = DB::table('loans_schedules')
                ->where('loan_id', $loanId)
                ->orderBy('installment_date')
                ->get();

            $totalInterestScheduled = 0;
            $totalInterestPaid = 0;
            $overdueInterest = 0;
            $futureInterest = 0;
            $today = Carbon::now()->format('Y-m-d');

            $scheduleDetails = [];

            foreach ($schedules as $schedule) {
                $interestDue = $schedule->interest ?? 0;
                $interestPaid = $schedule->interest_payment ?? 0;
                $interestReceivable = $interestDue - $interestPaid;

                $totalInterestScheduled += $interestDue;
                $totalInterestPaid += $interestPaid;

                if ($schedule->installment_date <= $today && $schedule->completion_status != 'PAID') {
                    $overdueInterest += $interestReceivable;
                } elseif ($schedule->installment_date > $today) {
                    $futureInterest += $interestDue;
                }

                $scheduleDetails[] = [
                    'installment_date' => $schedule->installment_date,
                    'interest_due' => $interestDue,
                    'interest_paid' => $interestPaid,
                    'interest_receivable' => $interestReceivable,
                    'status' => $schedule->completion_status,
                    'days_overdue' => $schedule->days_in_arrears ?? 0
                ];
            }

            return [
                'loan_id' => $loanId,
                'loan_account_number' => $loan->loan_account_number,
                'client_number' => $loan->client_number,
                'principal' => $loan->principle,
                'interest_rate' => $loan->interest,
                'total_interest_scheduled' => $totalInterestScheduled,
                'total_interest_paid' => $totalInterestPaid,
                'total_interest_receivable' => $totalInterestScheduled - $totalInterestPaid,
                'overdue_interest' => $overdueInterest,
                'future_interest' => $futureInterest,
                'schedule_details' => $scheduleDetails
            ];

        } catch (\Exception $e) {
            Log::error('LoanInterestReceivableService: Error calculating loan interest receivable', [
                'error' => $e->getMessage(),
                'loan_id' => $loanId
            ]);
            throw $e;
        }
    }

    /**
     * Get summary statistics for interest receivable
     *
     * @return array Summary statistics
     */
    public function getSummaryStatistics()
    {
        try {
            $data = $this->calculateInterestReceivable();

            $summary = $data['summary'];
            $loans = $data['loans'];

            // Calculate additional metrics
            $performingLoans = $loans->filter(function($loan) {
                return $loan->overdue_interest == 0;
            })->count();

            $nonPerformingLoans = $loans->filter(function($loan) {
                return $loan->overdue_interest > 0;
            })->count();

            $averageInterestRate = $loans->avg('interest');

            return [
                'total_loans' => $summary['total_loans'],
                'performing_loans' => $performingLoans,
                'non_performing_loans' => $nonPerformingLoans,
                'total_principal' => $summary['total_principal'],
                'total_interest_receivable' => $summary['total_interest_receivable'],
                'overdue_interest' => $summary['overdue_interest'],
                'future_interest' => $summary['future_interest'],
                'average_interest_rate' => round($averageInterestRate, 2),
                'collection_rate' => $summary['total_interest_scheduled'] > 0
                    ? round(($summary['total_interest_paid'] / $summary['total_interest_scheduled']) * 100, 2)
                    : 0
            ];

        } catch (\Exception $e) {
            Log::error('LoanInterestReceivableService: Error getting summary statistics', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate accrued interest for a specific period
     *
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return array Accrued interest details
     */
    public function calculateAccruedInterest($startDate, $endDate)
    {
        try {
            $accruedInterest = DB::table('loans_schedules as ls')
                ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
                ->select(
                    'l.loan_id',
                    'l.loan_account_number',
                    'l.client_number',
                    DB::raw('SUM(ls.interest) as accrued_interest'),
                    DB::raw('SUM(ls.interest_payment) as interest_paid'),
                    DB::raw('SUM(ls.interest - COALESCE(ls.interest_payment, 0)) as interest_receivable')
                )
                ->where('l.status', 'ACTIVE')
                ->whereBetween('ls.installment_date', [$startDate, $endDate])
                ->groupBy('l.loan_id', 'l.loan_account_number', 'l.client_number')
                ->get();

            $totalAccrued = $accruedInterest->sum('accrued_interest');
            $totalPaid = $accruedInterest->sum('interest_paid');
            $totalReceivable = $accruedInterest->sum('interest_receivable');

            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_accrued_interest' => $totalAccrued,
                'total_interest_paid' => $totalPaid,
                'total_interest_receivable' => $totalReceivable,
                'loans' => $accruedInterest
            ];

        } catch (\Exception $e) {
            Log::error('LoanInterestReceivableService: Error calculating accrued interest', [
                'error' => $e->getMessage(),
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            throw $e;
        }
    }

    /**
     * Get aged interest receivable analysis
     *
     * @return array Aged receivables by bucket
     */
    public function getAgedInterestReceivable()
    {
        try {
            $today = Carbon::now()->format('Y-m-d');

            $agedReceivables = DB::table('loans_schedules as ls')
                ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
                ->select(
                    'l.loan_id',
                    'l.loan_account_number',
                    'l.client_number',
                    'ls.installment_date',
                    'ls.days_in_arrears',
                    DB::raw('ls.interest - COALESCE(ls.interest_payment, 0) as interest_receivable')
                )
                ->where('l.status', 'ACTIVE')
                ->where('ls.completion_status', '!=', 'PAID')
                ->where('ls.installment_date', '<=', $today)
                ->where(DB::raw('ls.interest - COALESCE(ls.interest_payment, 0)'), '>', 0)
                ->get();

            // Categorize by age
            $current = 0; // 0-30 days
            $days31to60 = 0;
            $days61to90 = 0;
            $days91to180 = 0;
            $over180Days = 0;

            foreach ($agedReceivables as $receivable) {
                $daysOverdue = $receivable->days_in_arrears ?? 0;
                $amount = $receivable->interest_receivable;

                if ($daysOverdue <= 30) {
                    $current += $amount;
                } elseif ($daysOverdue <= 60) {
                    $days31to60 += $amount;
                } elseif ($daysOverdue <= 90) {
                    $days61to90 += $amount;
                } elseif ($daysOverdue <= 180) {
                    $days91to180 += $amount;
                } else {
                    $over180Days += $amount;
                }
            }

            return [
                'current' => round($current, 2),
                '31_60_days' => round($days31to60, 2),
                '61_90_days' => round($days61to90, 2),
                '91_180_days' => round($days91to180, 2),
                'over_180_days' => round($over180Days, 2),
                'total' => round($current + $days31to60 + $days61to90 + $days91to180 + $over180Days, 2)
            ];

        } catch (\Exception $e) {
            Log::error('LoanInterestReceivableService: Error getting aged receivables', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
