<?php

namespace App\Services;

class LoanRepaymentSchedule
{
    private $loanId;
    private $approvedTerm;
    private $updatedPrinciple;
    private $dayOfMonth;
    private $disbursement_datex;
    private $interestRate;
    private $principle_grace_period;
    private $member_category;
    private $addMonth;
    private $optionDisbursementDate;

    public function __construct($loanId, $approvedTerm, $updatedPrinciple, $dayOfMonth, $interestRate,$principle_grace_period, $member_category)
    {
        $this->loanId = $loanId;
        $this->approvedTerm = $approvedTerm;
        $this->updatedPrinciple = $updatedPrinciple;
        $this->dayOfMonth = $dayOfMonth;
        $this->interestRate = $interestRate; // Default 12% annual interest rate
        $this->principle_grace_period=$principle_grace_period;
    }

    public function generateSchedule($disbursementDate)
    {


        $this->disbursement_datex=$disbursementDate;

        //dd($this->loanId,$this->approvedTerm,$this->updatedPrinciple,$this->dayOfMonth,$this->interestRate);
        $schedule = [];
        $balance = $this->updatedPrinciple;

        // Grace Period Interest Calculation
        $daysInGracePeriod = $this->calculateGracePeriod($disbursementDate);



        $dailyInterestRate = $this->interestRate / 365 / 100; // Convert annual rate to daily decimal
        $gracePeriodInterest = $balance * $dailyInterestRate * $daysInGracePeriod;

        // First Installment - Interest Only
        $schedule[] = [
            'installment' => 1,
            'installment_date' => $disbursementDate,
            'opening_balance' => $balance,
            'payment' => $gracePeriodInterest,
            'principal' => 0,
            'interest' => $gracePeriodInterest,
            'closing_balance' => $balance,

        ];

        $graceData[]=[
         'days'=> $daysInGracePeriod ,
         'balance'=>$gracePeriodInterest,
        ];

        //dd($balance);

        // Move to the next installment date
        $firstInstallmentDate = date('Y-m-' . $this->dayOfMonth, strtotime("+$this->addMonth month", strtotime($disbursementDate)));

        // dd($this->dayOfMonth ,$firstInstallmentDate);
        // Remaining Installments
        for ($installment = 2; $installment <= $this->approvedTerm + 1; $installment++) {
            $monthlyInterest = $balance * ($this->interestRate / 12 / 100); // Convert to decimal
            $monthlyPayment = $this->calculateMonthlyPayment();
            $principalPayment = $monthlyPayment - $monthlyInterest;
            $closingBalance = $balance - $principalPayment;

            $schedule[] = [
                'installment' => $installment,
                'installment_date' => $firstInstallmentDate,
                'opening_balance' => round($balance, 2),
                'payment' => round($monthlyPayment, 2),
                'principal' => round($principalPayment, 2),
                'interest' => round($monthlyInterest, 2),
                'closing_balance' => round($closingBalance, 2),
            ];

            $balance = $closingBalance;
            $firstInstallmentDate = date('Y-m-d', strtotime("+1 month", strtotime($firstInstallmentDate)));
        }

        // Ensure final balance is zero (handle rounding errors)
        if (count($schedule) > 0) {
            $lastIndex = count($schedule) - 1;
            $schedule[$lastIndex]['closing_balance'] = 0;
            $schedule[$lastIndex]['principal'] = round($schedule[$lastIndex]['opening_balance'], 2);
            $schedule[$lastIndex]['payment'] = round($schedule[$lastIndex]['principal'] + $schedule[$lastIndex]['interest'], 2);
        }

        // Calculate footer totals
        $footer = $this->calculateFooterTotals($schedule);

        // Validate schedule accuracy
        $this->validateSchedule($schedule, $footer);

        return ['schedule' => $schedule, 'footer' => $footer,'graceData'=>$graceData];
    }

    /**
     * Validate schedule calculations for accuracy
     */
    private function validateSchedule($schedule, $footer)
    {
        $totalPrincipal = 0;
        $totalInterest = 0;
        $totalPayment = 0;

        foreach ($schedule as $row) {
            $totalPrincipal += floatval($row['principal']);
            $totalInterest += floatval($row['interest']);
            $totalPayment += floatval($row['payment']);
        }

        // Check if totals match
        $principalDiff = abs($totalPrincipal - $this->updatedPrinciple);
        $paymentDiff = abs($totalPayment - $footer['total_payment']);
        $interestDiff = abs($totalInterest - $footer['total_interest']);

        if ($principalDiff > 0.01 || $paymentDiff > 0.01 || $interestDiff > 0.01) {
            \Log::warning('Schedule validation failed', [
                'principal_diff' => $principalDiff,
                'payment_diff' => $paymentDiff,
                'interest_diff' => $interestDiff,
                'expected_principal' => $this->updatedPrinciple,
                'calculated_principal' => $totalPrincipal
            ]);
        }
    }

    private function calculateFooterTotals($schedule)
    {
        $totals = [
            'total_payment' => 0,
            'total_principal' => 0,
            'total_interest' => 0,
            'final_closing_balance' => 0,
        ];

        foreach ($schedule as $row) {
            $totals['total_payment'] += floatval($row['payment']);
            $totals['total_principal'] += floatval($row['principal']);
            $totals['total_interest'] += floatval($row['interest']);
        }

        // Round all totals to 2 decimal places for accuracy
        $totals['total_payment'] = round($totals['total_payment'], 2);
        $totals['total_principal'] = round($totals['total_principal'], 2);
        $totals['total_interest'] = round($totals['total_interest'], 2);
        $totals['final_closing_balance'] = round(end($schedule)['closing_balance'] ?? 0, 2);

        return $totals;
    }

    private function calculateGracePeriod($disbursementDate)
    {
        // Convert disbursement date to a day of the month
        $disbursementDay = (int)date('d', strtotime($disbursementDate));

        // Ensure $this->dayOfMonth is set and valid (default to 1 if not set)
        $this->dayOfMonth = $this->dayOfMonth ?? 1;


        // First, calculate the difference in the same month
        $gracePeriodCurrentMonth = $this->dayOfMonth - $disbursementDay;

        $this->addMonth=1;

        // $this->principle_grace_period=0;
        // $this->member_category=0;

        if($this->principle_grace_period==1 || $this->member_category != 0){
            if ($gracePeriodCurrentMonth > 0) {
                // If the difference is positive, it means the grace period is within the same month
                return $gracePeriodCurrentMonth ;

            }elseif($gracePeriodCurrentMonth ==0){
                return 0;
            }

                // Otherwise, calculate for the next month

                $disbursementDay = $this->disbursement_datex;

                 $this->addMonth++;
                // Calculate the 20th of next month
                $nextMonthDate = date('Y-m-'.$this->dayOfMonth, strtotime('+1 month'));

                // Find the difference in days
                $diffInDays = (strtotime($nextMonthDate) - strtotime($disbursementDay)) / (60 * 60 * 24);

                // dd( $diffInDays );
                return $diffInDays;


        }


        $this->dayOfMonth=  date('j');;

        return 0;




    }


    private function calculateMonthlyPayment()
    {
        $monthlyInterestRate = $this->interestRate / 12 / 100; // Convert annual rate to monthly decimal
        if ($monthlyInterestRate > 0) {
            // Use proper amortization formula: PMT = P * (r * (1 + r)^n) / ((1 + r)^n - 1)
            return ($this->updatedPrinciple * $monthlyInterestRate * pow(1 + $monthlyInterestRate, $this->approvedTerm)) / (pow(1 + $monthlyInterestRate, $this->approvedTerm) - 1);
        } else {
            // If no interest, equal principal payments
            return $this->updatedPrinciple / $this->approvedTerm;
        }
    }

    private function calculateFooterTotalsx($schedule)
    {
        $totals = [
            'installment' => 'Total',
            'installment_date' => '',
            'opening_balance' => '',
            'payment' => 0,
            'principal' => 0,
            'interest' => 0,
            'closing_balance' => '',
        ];

        foreach ($schedule as $row) {
            $totals['payment'] += floatval($row['payment']);
            $totals['principal'] += floatval($row['principal']);
            $totals['interest'] += floatval($row['interest']);
        }

        // Format the totals
        $totals['payment'] = number_format($totals['payment'], 2);
        $totals['principal'] = number_format($totals['principal'], 2);
        $totals['interest'] = number_format($totals['interest'], 2);

        return $totals;
    }
}

