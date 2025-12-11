<?php

namespace App\Services;

use App\Livewire\Accounting\Account;
use App\Models\Account as Accounts;
use App\Models\Activity;
use App\Models\GeneralLedger;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use App\Models\Loan as Loans;
use App\Models\Loan_sub_products;
use App\Models\LoanProduct;
use App\Models\loans_schedules;
use App\Models\LoanSchedule;
use App\Models\LoansModel;
use Carbon\Carbon;

class LoanScheduleService
{

    public function calculateDate($payment_frequency, $tenure)
    {
        $dates = [];
        
        // Validate inputs
        if (empty($payment_frequency) || empty($tenure) || $tenure <= 0) {
            Log::warning("Invalid calculateDate parameters: frequency={$payment_frequency}, tenure={$tenure}");
            return $dates;
        }
        
        if ($payment_frequency == "daily") {
            $today = Carbon::now();
            $start_date = $today->copy()->addDays(1);
            $end_date = $start_date->copy()->addMonths((int)$tenure);
            $i = 1;
            while ($start_date->lessThanOrEqualTo($end_date)) {
                $dates[] = ['date' => $start_date->toDateString()];
                $start_date->addDay();
                $i++;
            }
            return $dates;
        } elseif ($payment_frequency == "weekly") {
            $currentDate = Carbon::now()->copy()->addDays(7);
            $endDate = $currentDate->copy()->addMonths((int)$tenure);
            $i = 1;
            while ($currentDate->lt($endDate)) {
                $dates[] = ['date' => $currentDate->format('Y-m-d')];
                $currentDate->addWeek();
                $i++;
            }
            return $dates;
        } elseif ($payment_frequency == "monthly") {
            $currentDate = Carbon::now()->copy()->addMonths(1);
            $endDate = $currentDate->copy()->addMonths((int)$tenure);
            $i = 1;
            while ($currentDate->lt($endDate)) {
                $dates[] = ['date' => $currentDate->format('Y-m-d')];
                $currentDate->addMonth();
                $i++;
            }
            return $dates;
        }
        
        Log::warning("Unsupported payment frequency: {$payment_frequency}");
        return $dates;
    }


    public function reducingRepaymentSchedule($principal, $interest, $tenure, $loan_id, $paymentFrequency)
    {
        // Validate inputs to prevent division by zero
        if (empty($principal) || empty($interest) || empty($tenure) || empty($paymentFrequency)) {
            return [
                'body' => [],
                'footer' => []
            ];
        }

        // Count number of iterations
        $date_list = $this->calculateDate($paymentFrequency, $tenure);
        $count_total = count($date_list);
        
        // Prevent division by zero
        if ($count_total <= 0) {
            return [
                'body' => [],
                'footer' => []
            ];
        }
        
        $tenure = $count_total;
        
        // Reducing and flat method
        $principle_payment = $principal / $tenure;

        // For reducing balance
        $array = [];
        $total_installment = 0;
        $total_interest = 0;
        $total_principal = 0;
        $total_balance = 0;
        $remaining_principal = $principal;

        for ($i = 1; $i <= $count_total; $i++) {
            $interest_amount = $remaining_principal * $interest / 100;
            $installment = (float)($interest_amount + $principle_payment);
            $amount_remain = (float)($remaining_principal - $principle_payment);

            if ($amount_remain < 0) {
                $installment = $remaining_principal + (float)($interest_amount);
                $amount_remain = 0;
            }
            
            $array[] = [
                'Payment' => $installment,
                'balance' => $amount_remain,
                'interest' => $interest_amount,
                'Principle' => $principle_payment,
                'date' => $date_list[$i - 1]['date'],
            ];

            $remaining_principal = (float)($remaining_principal - $principle_payment);

            $total_installment = (float)($installment + $total_installment);
            $total_interest = (float)($total_interest + $interest_amount);
            $total_principal = (float)($total_principal + $principle_payment);
            $total_balance = (float)($remaining_principal);
        }

        $footer[] = [
            'total_installment' => $total_installment,
            'total_interest' => $total_interest,
            'total_principal' => $total_principal,
            'total_balance' => $total_balance,
        ];

        return [
            'body' => $array,
            'footer' => $footer
        ];
    }

    public function flatRepaymentSchedule($principal, $interest, $tenure, $loan_id, $paymentFrequency)
    {
        // Validate inputs to prevent division by zero
        if (empty($principal) || empty($interest) || empty($tenure) || empty($paymentFrequency)) {
            return [
                'body' => [],
                'footer' => []
            ];
        }

        // Count number of iterations
        $date_list = $this->calculateDate($paymentFrequency, $tenure);
        $count_total = count($date_list);
        
        // Prevent division by zero
        if ($count_total <= 0) {
            return [
                'body' => [],
                'footer' => []
            ];
        }
        
        $tenure = $count_total;
        
        // Flat method
        $interest_amount = $principal * $interest / 100;
        $principle_payment = $principal / $tenure;

        // For flat
        $array = [];
        $total_installment = 0;
        $total_interest = 0;
        $total_principal = 0;
        $total_balance = 0;
        $remaining_principal = $principal;

        for ($i = 1; $i <= $count_total; $i++) {
            $installment = (float)($interest_amount + $principle_payment);
            $amount_remain = (float)($remaining_principal - $principle_payment);

            if ($amount_remain < 0) {
                $installment = $remaining_principal + (float)($interest_amount);
                $amount_remain = 0;
            }
            
            $array[] = [
                'Payment' => $installment,
                'balance' => $amount_remain,
                'interest' => $interest_amount,
                'Principle' => $principle_payment,
                'date' => $date_list[$i - 1]['date'],
            ];

            $remaining_principal = (float)($remaining_principal - $principle_payment);
            $total_installment = (float)($installment + $total_installment);
            $total_interest = (float)($total_interest + $interest_amount);
            $total_principal = (float)($total_principal + $principle_payment);
            $total_balance = (float)($remaining_principal);
        }

        $footer[] = [
            'total_installment' => $total_installment,
            'total_interest' => $total_interest,
            'total_principal' => $total_principal,
            'total_balance' => $total_balance,
        ];

        return [
            'body' => $array,
            'footer' => $footer
        ];
    }



    function generateLoanSchedule($loan_id)
    {
        try {
            // Validate loan_id
            if (empty($loan_id)) {
                return [
                    'body' => [],
                    'footer' => []
                ];
            }

            $loans = $this->loanInfo($loan_id);
            
            if ($loans->isEmpty()) {
                return [
                    'body' => [],
                    'footer' => []
                ];
            }

            $principal = 0;
            $monthlyInterest = 0;
            $method = '';
            $tenureMonths = 0;
            $paymentFrequency = 'monthly';

            foreach ($loans as $loan) {
                $principal = (float)($loan->loan_amount ?? 0);
                $loan_id = $loan->id;
                $loan_product = Loan_sub_products::where('sub_product_id', $loan->loan_sub_product)->first();

                if ($loan_product) {
                    $monthlyInterest = (float)($loan_product->interest_value ?? 0);
                    $method = $loan_product->interest_method ?? 'flat';
                    
                    // Handle interest_tenure which can be string or numeric
                    $tenureValue = $loan_product->interest_tenure ?? 12;
                    $tenureMonths = is_numeric($tenureValue) ? (int)$tenureValue : (int)trim($tenureValue);
                    
                    // Ensure minimum tenure
                    if ($tenureMonths <= 0) {
                        $tenureMonths = 12; // Default to 12 months
                    }
                }
            }

            // Validate required values
            if ($principal <= 0 || $monthlyInterest <= 0 || $tenureMonths <= 0) {
                Log::warning("Invalid loan parameters: principal={$principal}, interest={$monthlyInterest}, tenure={$tenureMonths}");
                return [
                    'body' => [],
                    'footer' => []
                ];
            }

            if ($method == 'flat') {
                return $this->flatRepaymentSchedule($principal, $monthlyInterest, $tenureMonths, $loan_id, $paymentFrequency);
            } else {
                return $this->reducingRepaymentSchedule($principal, $monthlyInterest, $tenureMonths, $loan_id, $paymentFrequency);
            }
        } catch (\Exception $e) {
            Log::error('Error generating loan schedule: ' . $e->getMessage());
            return [
                'body' => [],
                'footer' => []
            ];
        }
    }


    function storeLoanSchedule($array,$loan_id){

        foreach ($array as $key => $body ){

          loans_schedules::create([

            'loan_id'=>$loan_id,
             'payment_date'=>$body['date'],
             'installment_amount'=>$body['installment'],
             'principle_amount'=>$body['principal'],
             'interest_amount'=>$body['interest'],
             'outstanding_amount'=>$body['balance'],
             'status'=>'ACTIVE'
          ]);
    }
    }

    function loanInfo($loan_id){
        return LoansModel::where('id',$loan_id)->get();
    }


}
