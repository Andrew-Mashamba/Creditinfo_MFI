<?php

namespace App\Services;

use App\Models\Loan_sub_products;
use App\Models\LoansModel;
use Carbon\Carbon;
use DateTime;
use Exception;

class LoanScheduleServiceVersionTwo
{
    public function generateLoanSchedule($loan_id, $tenure = null, $principle = null, $dayOfMonth = null)
    {
        $loans = $this->loanInfo($loan_id);

        if ($loans->isEmpty()) {
            throw new Exception("Loan not found");
        }

        $loan = $loans->first();
        $principal = $principle ?: $loan->loan_amount;
        $loan_id = $loan->id;

        $loan_product = Loan_sub_products::where('sub_product_id', $loan->loan_sub_product)->first();
        if (!$loan_product) {
            throw new Exception("Loan product not found");
        }

        $monthlyInterest = $loan_product->interest_value;
        $method = $loan_product->interest_method;
        $tenureMonths = $tenure ?: $loan_product->tenure;
        $paymentFrequency = "Monthly";  // Assuming monthly payments for now, this can be dynamic

        if ($method == 'flat') {
            return $this->flatRepaymentSchedule($principal, $monthlyInterest, $tenureMonths, $loan_id, $paymentFrequency, $dayOfMonth);
        } else {
            return $this->reducingRepaymentSchedule($principal, $monthlyInterest, $tenureMonths, $loan_id, $paymentFrequency, $dayOfMonth);
        }
    }

    public function loanInfo($loan_id)
    {
        return LoansModel::where('id', $loan_id)->get();
    }

    public function calculateDate($payment_frequency, $tenure, $dayOfMonth)
    {
        $dates = [];
        $currentDate = Carbon::now()->copy()->addMonths(1);
        $endDate = $currentDate->copy()->addMonths((int)$tenure);

        while ($currentDate->lt($endDate)) {
            $day = min(30, $currentDate->daysInMonth); // Adjust for month-end dates
            $dates[] = ['date' =>  $currentDate->format('Y') . '-' . $currentDate->format('m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT)];
            $currentDate->addMonth();
        }

        return $dates;
    }

    public function reducingRepaymentSchedule($principal, $interest, $tenure, $loan_id, $paymentFrequency, $dayOfMonth)
    {
        $interestRate = $interest;
        $schedule = [];
        $monthlyRate = ($interestRate / 100) / 12;

        $monthlyPayment = $tenure == 0 ? 0 : $principal * ($monthlyRate * pow(1 + $monthlyRate, $tenure)) / (pow(1 + $monthlyRate, $tenure) - 1);
        $currentDate = Carbon::now()->addMonth();
        if ($dayOfMonth) {
            $currentDate->day = $dayOfMonth;
        }

        $remainingBalance = $principal;

        for ($pmt = 1; $pmt <= $tenure; $pmt++) {
            $interestPayment = $remainingBalance * $monthlyRate;
            $principalPayment = $monthlyPayment - $interestPayment;
            $closingBalance = $remainingBalance - $principalPayment;

            if ($closingBalance < 0) {
                $principalPayment += $closingBalance;
                $closingBalance = 0;
            }

            $schedule[] = [
                'Pmt' => $pmt,
                'installment_date' => $currentDate->format('Y-m-d'),
                'opening_balance' => round($remainingBalance, 2),
                'payments' => round($monthlyPayment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestPayment, 2),
                'closing_balance' => round($closingBalance, 2)
            ];

            if (abs($closingBalance) < 0.01) {
                break;
            }

            $remainingBalance = $closingBalance;
            $currentDate->addMonth();
        }

        return [
            'schedule' => $schedule,
            'summary' => [
                'total_payment' => round(array_sum(array_column($schedule, 'payments')), 2),
                'total_interest' => round(array_sum(array_column($schedule, 'interest')), 2),
                'total_principal' => round(array_sum(array_column($schedule, 'principal')), 2),
                'final_closing_balance' => round($remainingBalance, 2)
            ]
        ];
    }

    public function flatRepaymentSchedule($principal, $interest, $tenure, $loan_id, $paymentFrequency, $dayOfMonth)
    {
        $schedule = [];
        $interestAmount = ($principal * $interest) / 1200;
        $principalPayment = $principal / $tenure;
        $remainingBalance = $principal;
        $dates = $this->calculateDate($paymentFrequency, $tenure, $dayOfMonth);

        foreach ($dates as $i => $date) {
            $installment = $interestAmount + $principalPayment;
            $remainingBalance -= $principalPayment;

            $schedule[] = [
                'Pmt' => $i + 1,
                'installment_date' => $date['date'],
                'opening_balance' => round(max($remainingBalance + $principalPayment, 0), 2),
                'payments' => round($installment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($interestAmount, 2),
                'closing_balance' => round(max($remainingBalance, 0), 2)
            ];
        }

        return [
            'schedule' => $schedule,
            'summary' => [
                'total_payment' => round(array_sum(array_column($schedule, 'payments')), 2),
                'total_interest' => round(array_sum(array_column($schedule, 'interest')), 2),
                'total_principal' => round(array_sum(array_column($schedule, 'principal')), 2),
                'final_closing_balance' => round($remainingBalance, 2)
            ]
        ];
    }
}
