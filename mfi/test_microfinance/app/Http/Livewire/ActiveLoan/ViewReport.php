<?php

namespace App\Http\Livewire\ActiveLoan;

use DateTime;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ViewReport extends Component
{

    public $guarantors;
    public $loan_schedules;
    public $collaterals;
    public $businessInfo;
    public $loan_general_info;
    public function loanDataReport(){
        $loan_id=session()->get('client_number');
        $loan_table_id=session()->get('loan_table_id');

      $this->guarantors=  $this->guarantorInfo($loan_table_id);
      $this->collaterals=$this->collateralsInformation($loan_table_id);
      $this->businessInfo=$this->buinessInformation($loan_id);
       $this->loan_schedules=$this->loanInformation($loan_table_id);
       $this->loan_general_info=$this->loanGeneralInformation($loan_table_id);


    }

    public function loanGeneralInformation($loan_id){

       return DB::table('loans')->where('id',$loan_id)->get();
    }


    function loanInformation($loan_id){
        //$loan_id= DB::table('loans')->where('id',$loan_id)->value('loan_id');
        return DB::table('loans_schedules')->where('loan_id',$loan_id)->orderBy('id','asc')->get();
    }

    function buinessInformation($loan_id){
        return DB::table('loans')->where('id',$loan_id)->get();
    }


    function guarantorInfo($loan_id){
        $guarantor_id=DB::table('collaterals')->where('loan_id',$loan_id)
          ->where('main_collateral_type','external_guaranted')->pluck('member_number')->toArray();
        $guarantors=DB::table('clients')->whereIn('client_number',$guarantor_id)->get();
      return  $guarantors;
    }


    function collateralsInformation($loan_id){
         $collaterals=DB::table('collaterals')->where('loan_id',$loan_id)
        ->where('main_collateral_type','self_guaranted')->get();
    return  $collaterals;

    }

    public function closeView(){
        $this->emit('displayLoanReport',1);
    }
    public function render()
    {
        $this->loanDataReport();
        return view('livewire.active-loan.view-report');
    }


    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    function calculateArrearsAndPenaltiesxxxx() {

        $loan_id = session()->get('loan_table_id');

        // Fetch loan schedules from the database
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', $loan_id)
            ->orderBy('id', 'asc')
            ->get();

        // Loop through each installment schedule
        foreach ($schedules as $schedule) {
//            $today = '2025-06-12'; // Assuming today is 2025-11-20
//            $today = new DateTime($today);

            $today = new DateTime();
            $penaltyRate = 0.05; // 5% penalty rate on Amount in Arrears
            $penalties = 0;
            $amountInArrears = 0;
            $daysInArrears = 0;



            $installmentDate = new DateTime($schedule->installment_date);
            $completionStatus = $schedule->completion_status;
            $installment = $schedule->installment;
            $payment = $schedule->payment;

            // Check if the installment is unpaid or partially paid
            if ($completionStatus != 'PAID') {
                // Calculate amount in arrears (unpaid portion of installment)
                $amountInArrears = $installment - $payment;

                // Calculate days in arrears if it's past the due date
                if ($installmentDate < $today) {
                    $daysInArrears = $installmentDate->diff($today)->days;

                    // Calculate penalties based on the penalty rate and amount in arrears
                    $penalties = $amountInArrears * $penaltyRate * ($daysInArrears / 30); // Penalty for the month
                }

                // Update the schedule with calculated penalties, amount in arrears, and days in arrears
                DB::table('loans_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'penalties' => $penalties,
                        'amount_in_arrears' => $amountInArrears,
                        'days_in_arrears' => $daysInArrears
                    ]);
            }
        }
    }



    function calculateArrearsAndPenalties() {
        $loan_id = session()->get('loan_table_id');

        // Fetch loan schedules from the database
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', $loan_id)
            ->orderBy('id', 'asc')
            ->get();

        $totalAmountInArrears = 0;
        $maxDaysInArrears = 0;
        $penaltyRate = 0.05; // 5% penalty rate on Amount in Arrears

        // Loop through each installment schedule
        foreach ($schedules as $schedule) {

            //$today = '2025-07-20'; // Assuming today is 2025-11-20
            //$today = new DateTime($today);
            $today = new DateTime();
            $penalties = 0;
            $amountInArrears = 0;
            $daysInArrears = 0;

            $installmentDate = new DateTime($schedule->installment_date);
            $completionStatus = $schedule->completion_status;
            $installment = $schedule->installment;
            $payment = $schedule->payment;

            // Check if the installment is unpaid or partially paid
            if ($completionStatus != 'PAID') {
                // Calculate amount in arrears (unpaid portion of installment)
                $amountInArrears = $installment - $payment;
                $totalAmountInArrears += $amountInArrears;

                // Calculate days in arrears if it's past the due date
                if ($installmentDate < $today) {
                    $daysInArrears = $installmentDate->diff($today)->days;
                    $maxDaysInArrears = max($maxDaysInArrears, $daysInArrears);

                    // Calculate penalties based on the penalty rate and amount in arrears
                    $penalties = $amountInArrears * $penaltyRate * ($daysInArrears / 30); // Penalty for the month
                }

                // Update the schedule with calculated penalties, amount in arrears, and days in arrears
                DB::table('loans_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'penalties' => $penalties,
                        'amount_in_arrears' => $amountInArrears,
                        'days_in_arrears' => $daysInArrears
                    ]);
            }
        }

        $loanStatus = 'ACTIVE';
        if($maxDaysInArrears > 0){
            // Determine loan status based on maximum days in arrears
            $loanStatus = $maxDaysInArrears > 360 ? 'WRITEOFF' : 'DELINQUENT';
        }


        // Update loan record with total amount in arrears and status
        DB::table('loans')
            ->where('id', $loan_id)
            ->update([
                'days_in_arrears' => $maxDaysInArrears,
                'arrears_in_amount' => $totalAmountInArrears,
                'status' => $loanStatus,
                'updated_at' => now()
            ]);
    }






}
