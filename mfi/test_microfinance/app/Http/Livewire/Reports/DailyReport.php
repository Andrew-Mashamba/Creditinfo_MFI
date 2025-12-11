<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\BranchesModel;
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use Illuminate\Support\Facades\DB;

class DailyReport extends Component
{

    public $tab_id;
    public $endDate;
    public $total_principle;
    public $total_disbursement_amount;
    public $total_repayment;
    public $loan_applications;


    public $day_date;

    protected $listerner=['refreshComponent'=>'$refresh'];
    public function boot(){
        $this->day_date=Carbon::today();
    }

    public function selectedBranch($id){

         $this->tab_id=$id;



         //
         $query=LoansModel::query()-> whereDate('updated_at',$this->day_date)->where('status','ACTIVE')->where('branch_id',$id);

         if($this->endDate){ $query->whereDate('updated_at','<=',$this->endDate); }
         $this->total_principle =  $query->sum('principle');

         // get first the loan id
         $query2=loans_schedules::query()->where('created_at',$this->day_date);
         $loan_id=$query2 ->distinct('loan_id')->pluck('loan_id');


        $loan_ids=  LoansModel::query()->whereDate('updated_at',$this->day_date)
                  ->where('branch_id',$id)
              ->where('status','ACTIVE')->pluck('id')->toArray();


        $this->emit('sortLoanId', $loan_ids);

        $charges= $this->calculateCharges( $loan_ids);

         $this->total_disbursement_amount= $this->total_principle -  $charges;

         $loan_id_repayment=loans_schedules::whereDate('created_at',$this->day_date)->where('completion_status','CLOSED')->pluck('loan_id');



         $this->loan_applications=LoansModel::whereDate('updated_at',$this->day_date)->where('status','ACTIVE')->count();

       //  dd($this->total_disbursement_amount, $this->total_principle,$this->day_date);


    }

    function calculateCharges($loan_ids) {

        $total_amount = 0; // Initialize total amount

        //$charges=DB::table('loan_has_charges')->whereIn('loan_id',$loan_ids)->sum('amount');

        // foreach($loan_ids as $id) {

        //     $principle = DB::table('loans')->where('id', $id)->value('principle');

        //     $charge_ids = DB::table('loan_has_charges')->where('loan_id', $id)->pluck('charge_id')->toArray();

        //     $charges = DB::table('charges')->whereIn('id', $charge_ids)->get();

        //     $loan_amount = 0; // Initialize loan amount for each loan

        //     foreach($charges as $charge) {

        //         if ($charge->percentage_charge_amount === null) {
        //             // Use flat charge if percentage is null
        //             $loan_amount += $charge->flat_charge_amount;
        //         } else {
        //             // Calculate percentage charge based on principle
        //             $loan_amount += ((double)$charge->percentage_charge_amount * (double)$principle) / 100;
        //         }
        //     }

        //     // Add the loan's calculated charges to the total amount
        //     $total_amount += $loan_amount;
        // }

        return $total_amount;
    }




    function updatedDayDate($date){

        $this->day_date=$date;
        $this->selectedBranch($this->tab_id);

    }

    function updatedEndDate($endDate){
        $this->endDate=$endDate;
        $this->selectedBranch($this->tab_id);


    }
    public function render()
    {
        return view('livewire.reports.daily-report');
    }
}
