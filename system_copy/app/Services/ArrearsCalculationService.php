<?php

namespace App\Services;

use App\Models\LoansModel;
use App\Models\loans_schedules;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArrearsCalculationService
{
    /**
     * Calculate arrears for a specific loan
     */
    public function calculateLoanArrears($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return null;
        }

        $schedules = $loan->schedules()
            ->where('installment_date', '<=', now())
            ->where('completion_status', '!=', 'COMPLETED')
            ->get();

        $maxDaysInArrears = 0;
        $totalAmountInArrears = 0;
        $overdueSchedules = [];

        foreach ($schedules as $schedule) {
            $installmentDate = Carbon::parse($schedule->installment_date);
            $daysInArrears = $installmentDate->isPast() ? now()->diffInDays($installmentDate) : 0;
            
            if ($daysInArrears > 0) {
                $amountInArrears = $schedule->installment - ($schedule->payment ?? 0);
                
                if ($amountInArrears > 0) {
                    $maxDaysInArrears = max($maxDaysInArrears, $daysInArrears);
                    $totalAmountInArrears += $amountInArrears;
                    
                    $overdueSchedules[] = [
                        'schedule_id' => $schedule->id,
                        'installment_date' => $schedule->installment_date,
                        'installment_amount' => $schedule->installment,
                        'payment_made' => $schedule->payment ?? 0,
                        'amount_in_arrears' => $amountInArrears,
                        'days_in_arrears' => $daysInArrears
                    ];
                }
            }
        }

        return [
            'loan_id' => $loanId,
            'loan_number' => $loan->loan_number,
            'max_days_in_arrears' => $maxDaysInArrears,
            'total_amount_in_arrears' => $totalAmountInArrears,
            'overdue_schedules_count' => count($overdueSchedules),
            'overdue_schedules' => $overdueSchedules,
            'calculation_date' => now()->toDateString()
        ];
    }

    /**
     * Calculate arrears for all loans of a client
     */
    public function calculateClientArrears($clientNumber)
    {
        $loans = LoansModel::where('client_number', $clientNumber)
            ->where('status', 'ACTIVE')
            ->get();

        $clientArrears = [];
        $totalClientArrears = 0;
        $maxClientDaysInArrears = 0;

        foreach ($loans as $loan) {
            $loanArrears = $this->calculateLoanArrears($loan->id);
            if ($loanArrears) {
                $clientArrears[] = $loanArrears;
                $totalClientArrears += $loanArrears['total_amount_in_arrears'];
                $maxClientDaysInArrears = max($maxClientDaysInArrears, $loanArrears['max_days_in_arrears']);
            }
        }

        return [
            'client_number' => $clientNumber,
            'total_loans' => count($loans),
            'loans_with_arrears' => count($clientArrears),
            'total_amount_in_arrears' => $totalClientArrears,
            'max_days_in_arrears' => $maxClientDaysInArrears,
            'loans_arrears' => $clientArrears,
            'calculation_date' => now()->toDateString()
        ];
    }

    /**
     * Update arrears information in loans_schedules table
     */
    public function updateArrearsInDatabase($loanId)
    {
        $loan = LoansModel::find($loanId);
        if (!$loan) {
            return false;
        }

        $schedules = $loan->schedules()
            ->where('installment_date', '<=', now())
            ->where('completion_status', '!=', 'COMPLETED')
            ->get();

        foreach ($schedules as $schedule) {
            $installmentDate = Carbon::parse($schedule->installment_date);
            $daysInArrears = $installmentDate->isPast() ? now()->diffInDays($installmentDate) : 0;
            $amountInArrears = max(0, $schedule->installment - ($schedule->payment ?? 0));

            $schedule->update([
                'days_in_arrears' => $daysInArrears,
                'amount_in_arrears' => $amountInArrears,
                'next_check_date' => now()
            ]);
        }

        return true;
    }

    /**
     * Get overdue schedules for a loan
     */
    public function getOverdueSchedules($loanId)
    {
        return loans_schedules::where('loan_id', $loanId)
            ->where('installment_date', '<=', now())
            ->where('completion_status', '!=', 'COMPLETED')
            ->where('days_in_arrears', '>', 0)
            ->orderBy('installment_date', 'asc')
            ->get();
    }

    /**
     * Get arrears summary for dashboard
     */
    public function getArrearsSummary()
    {
        $summary = DB::table('loans_schedules')
            ->join('loans', 'loans_schedules.loan_id', '=', 'loans.loan_id')
            ->where('loans.status', 'ACTIVE')
            ->where('loans_schedules.days_in_arrears', '>', 0)
            ->select(
                DB::raw('COUNT(DISTINCT loans.loan_id) as loans_in_arrears'),
                DB::raw('COUNT(DISTINCT loans.client_number) as clients_in_arrears'),
                DB::raw('SUM(loans_schedules.amount_in_arrears) as total_arrears_amount'),
                DB::raw('MAX(loans_schedules.days_in_arrears) as max_days_in_arrears'),
                DB::raw('AVG(loans_schedules.days_in_arrears) as avg_days_in_arrears')
            )
            ->first();

        return $summary;
    }
}
