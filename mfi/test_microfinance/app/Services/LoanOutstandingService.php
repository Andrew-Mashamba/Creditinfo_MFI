<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service for calculating and managing loan outstanding balances
 *
 * This service handles:
 * - Calculating outstanding principal and interest for active loans
 * - Tracking overdue amounts
 * - Managing loan aging analysis
 * - Providing detailed loan breakdowns
 */
class LoanOutstandingService
{
    /**
     * Calculate outstanding balances for all active loans
     *
     * @param string|null $date The date to calculate for (defaults to today)
     * @return array Summary of loan outstanding
     */
    public function calculateLoanOutstanding($date = null)
    {
        $date = $date ?? Carbon::now()->format('Y-m-d');

        try {
            $loanOutstanding = DB::table('loans as l')
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
                    DB::raw('COALESCE(c.first_name, \'\') as first_name'),
                    DB::raw('COALESCE(c.middle_name, \'\') as middle_name'),
                    DB::raw('COALESCE(c.last_name, \'\') as last_name'),
                    DB::raw('COALESCE(SUM(ls.installment), 0) as total_installment_scheduled'),
                    DB::raw('COALESCE(SUM(ls.payment), 0) as total_paid'),
                    DB::raw('COALESCE(SUM(ls.installment - COALESCE(ls.payment, 0)), 0) as total_outstanding'),
                    DB::raw('COALESCE(SUM(ls.principle), 0) as total_principal_scheduled'),
                    DB::raw('COALESCE(SUM(ls.principle_payment), 0) as total_principal_paid'),
                    DB::raw('COALESCE(SUM(ls.principle - COALESCE(ls.principle_payment, 0)), 0) as principal_outstanding'),
                    DB::raw('COALESCE(SUM(ls.interest), 0) as total_interest_scheduled'),
                    DB::raw('COALESCE(SUM(ls.interest_payment), 0) as total_interest_paid'),
                    DB::raw('COALESCE(SUM(ls.interest - COALESCE(ls.interest_payment, 0)), 0) as interest_outstanding'),
                    DB::raw('COALESCE(SUM(CASE WHEN ls.installment_date <= \'' . $date . '\' AND ls.completion_status != \'PAID\' THEN ls.installment - COALESCE(ls.payment, 0) ELSE 0 END), 0) as overdue_amount'),
                    DB::raw('MAX(CASE WHEN ls.completion_status != \'PAID\' THEN ls.days_in_arrears ELSE 0 END) as days_in_arrears'),
                    DB::raw('MIN(ls.installment_date) as first_installment_date'),
                    DB::raw('MAX(ls.installment_date) as last_installment_date'),
                    DB::raw('COUNT(DISTINCT ls.id) as total_installments'),
                    DB::raw('SUM(CASE WHEN ls.completion_status = \'PAID\' THEN 1 ELSE 0 END) as paid_installments')
                )
                ->leftJoin('loans_schedules as ls', 'l.loan_id', '=', 'ls.loan_id')
                ->leftJoin('clients as c', 'l.client_number', '=', 'c.client_number')
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
                    'l.interest_method',
                    'c.first_name',
                    'c.middle_name',
                    'c.last_name'
                )
                ->get();

            // Add computed fields
            foreach ($loanOutstanding as $loan) {
                $loan->client_name = trim($loan->first_name . ' ' . $loan->middle_name . ' ' . $loan->last_name);
                if (empty($loan->client_name)) {
                    $loan->client_name = 'Unknown Client (' . $loan->client_number . ')';
                }

                $loan->repayment_progress = $loan->total_installments > 0
                    ? round(($loan->paid_installments / $loan->total_installments) * 100, 2)
                    : 0;

                $loan->is_overdue = $loan->overdue_amount > 0;

                // Calculate loan age
                if ($loan->disbursement_date) {
                    $disbursementDate = Carbon::parse($loan->disbursement_date);
                    $loan->loan_age_days = $disbursementDate->diffInDays(Carbon::now());
                } else {
                    $loan->loan_age_days = 0;
                }
            }

            $summary = [
                'total_loans' => $loanOutstanding->count(),
                'total_principal_outstanding' => $loanOutstanding->sum('principal_outstanding'),
                'total_interest_outstanding' => $loanOutstanding->sum('interest_outstanding'),
                'total_outstanding' => $loanOutstanding->sum('total_outstanding'),
                'total_overdue' => $loanOutstanding->sum('overdue_amount'),
                'overdue_loans_count' => $loanOutstanding->where('is_overdue', true)->count(),
                'performing_loans_count' => $loanOutstanding->where('is_overdue', false)->count(),
                'average_outstanding' => $loanOutstanding->count() > 0
                    ? round($loanOutstanding->sum('total_outstanding') / $loanOutstanding->count(), 2)
                    : 0,
                'calculation_date' => $date
            ];

            return [
                'loans' => $loanOutstanding,
                'summary' => $summary
            ];

        } catch (\Exception $e) {
            Log::error('LoanOutstandingService: Error calculating loan outstanding', [
                'error' => $e->getMessage(),
                'date' => $date
            ]);
            throw $e;
        }
    }

    /**
     * Calculate outstanding for a specific loan
     *
     * @param string $loanId The loan ID
     * @return array Detailed outstanding breakdown
     */
    public function calculateLoanOutstandingDetail($loanId)
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

            $totalPrincipalScheduled = 0;
            $totalPrincipalPaid = 0;
            $totalInterestScheduled = 0;
            $totalInterestPaid = 0;
            $totalOverdue = 0;
            $today = Carbon::now()->format('Y-m-d');

            $scheduleDetails = [];

            foreach ($schedules as $schedule) {
                $principalDue = $schedule->principle ?? 0;
                $principalPaid = $schedule->principle_payment ?? 0;
                $interestDue = $schedule->interest ?? 0;
                $interestPaid = $schedule->interest_payment ?? 0;
                $totalDue = $schedule->installment ?? 0;
                $totalPaid = $schedule->payment ?? 0;

                $principalOutstanding = $principalDue - $principalPaid;
                $interestOutstanding = $interestDue - $interestPaid;
                $totalOutstanding = $totalDue - $totalPaid;

                $totalPrincipalScheduled += $principalDue;
                $totalPrincipalPaid += $principalPaid;
                $totalInterestScheduled += $interestDue;
                $totalInterestPaid += $interestPaid;

                $isOverdue = $schedule->installment_date <= $today && $schedule->completion_status != 'PAID';
                if ($isOverdue) {
                    $totalOverdue += $totalOutstanding;
                }

                $scheduleDetails[] = [
                    'installment_date' => $schedule->installment_date,
                    'principal_due' => $principalDue,
                    'principal_paid' => $principalPaid,
                    'principal_outstanding' => $principalOutstanding,
                    'interest_due' => $interestDue,
                    'interest_paid' => $interestPaid,
                    'interest_outstanding' => $interestOutstanding,
                    'total_due' => $totalDue,
                    'total_paid' => $totalPaid,
                    'total_outstanding' => $totalOutstanding,
                    'status' => $schedule->completion_status,
                    'days_in_arrears' => $schedule->days_in_arrears ?? 0,
                    'is_overdue' => $isOverdue
                ];
            }

            return [
                'loan_id' => $loanId,
                'loan_account_number' => $loan->loan_account_number,
                'client_number' => $loan->client_number,
                'principal_amount' => $loan->principle,
                'interest_rate' => $loan->interest,
                'total_principal_scheduled' => $totalPrincipalScheduled,
                'total_principal_paid' => $totalPrincipalPaid,
                'principal_outstanding' => $totalPrincipalScheduled - $totalPrincipalPaid,
                'total_interest_scheduled' => $totalInterestScheduled,
                'total_interest_paid' => $totalInterestPaid,
                'interest_outstanding' => $totalInterestScheduled - $totalInterestPaid,
                'total_outstanding' => ($totalPrincipalScheduled - $totalPrincipalPaid) + ($totalInterestScheduled - $totalInterestPaid),
                'total_overdue' => $totalOverdue,
                'schedule_details' => $scheduleDetails
            ];

        } catch (\Exception $e) {
            Log::error('LoanOutstandingService: Error calculating loan outstanding detail', [
                'error' => $e->getMessage(),
                'loan_id' => $loanId
            ]);
            throw $e;
        }
    }

    /**
     * Get aged outstanding analysis
     *
     * @return array Aged outstanding by bucket
     */
    public function getAgedOutstanding()
    {
        try {
            $today = Carbon::now()->format('Y-m-d');

            $agedOutstanding = DB::table('loans_schedules as ls')
                ->join('loans as l', 'ls.loan_id', '=', 'l.loan_id')
                ->select(
                    'l.loan_id',
                    'l.loan_account_number',
                    'l.client_number',
                    'ls.installment_date',
                    'ls.days_in_arrears',
                    DB::raw('ls.installment - COALESCE(ls.payment, 0) as outstanding_amount')
                )
                ->where('l.status', 'ACTIVE')
                ->where('ls.completion_status', '!=', 'PAID')
                ->where('ls.installment_date', '<=', $today)
                ->where(DB::raw('ls.installment - COALESCE(ls.payment, 0)'), '>', 0)
                ->get();

            // Categorize by age following standard loan classification
            $standard = 0;      // 0-30 days
            $watch = 0;         // 31-90 days
            $substandard = 0;   // 91-180 days
            $doubtful = 0;      // 181-365 days
            $loss = 0;          // >365 days

            foreach ($agedOutstanding as $outstanding) {
                $daysOverdue = $outstanding->days_in_arrears ?? 0;
                $amount = $outstanding->outstanding_amount;

                if ($daysOverdue <= 30) {
                    $standard += $amount;
                } elseif ($daysOverdue <= 90) {
                    $watch += $amount;
                } elseif ($daysOverdue <= 180) {
                    $substandard += $amount;
                } elseif ($daysOverdue <= 365) {
                    $doubtful += $amount;
                } else {
                    $loss += $amount;
                }
            }

            return [
                'standard' => round($standard, 2),
                'watch' => round($watch, 2),
                'substandard' => round($substandard, 2),
                'doubtful' => round($doubtful, 2),
                'loss' => round($loss, 2),
                'total' => round($standard + $watch + $substandard + $doubtful + $loss, 2)
            ];

        } catch (\Exception $e) {
            Log::error('LoanOutstandingService: Error getting aged outstanding', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get summary statistics for loan outstanding
     *
     * @return array Summary statistics
     */
    public function getSummaryStatistics()
    {
        try {
            $data = $this->calculateLoanOutstanding();

            $summary = $data['summary'];
            $loans = $data['loans'];

            // Calculate additional metrics
            $highRiskLoans = $loans->filter(function($loan) {
                return $loan->days_in_arrears > 90;
            })->count();

            $mediumRiskLoans = $loans->filter(function($loan) {
                return $loan->days_in_arrears > 30 && $loan->days_in_arrears <= 90;
            })->count();

            $lowRiskLoans = $loans->filter(function($loan) {
                return $loan->days_in_arrears <= 30;
            })->count();

            return [
                'total_loans' => $summary['total_loans'],
                'performing_loans' => $summary['performing_loans_count'],
                'overdue_loans' => $summary['overdue_loans_count'],
                'total_principal_outstanding' => $summary['total_principal_outstanding'],
                'total_interest_outstanding' => $summary['total_interest_outstanding'],
                'total_outstanding' => $summary['total_outstanding'],
                'total_overdue' => $summary['total_overdue'],
                'average_outstanding' => $summary['average_outstanding'],
                'high_risk_loans' => $highRiskLoans,
                'medium_risk_loans' => $mediumRiskLoans,
                'low_risk_loans' => $lowRiskLoans,
                'portfolio_at_risk' => $summary['total_outstanding'] > 0
                    ? round(($summary['total_overdue'] / $summary['total_outstanding']) * 100, 2)
                    : 0
            ];

        } catch (\Exception $e) {
            Log::error('LoanOutstandingService: Error getting summary statistics', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
