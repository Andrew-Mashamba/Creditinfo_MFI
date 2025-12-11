<?php

namespace App\Http\Livewire\Services;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\LoansModel;
use App\Models\AccountsModel;
use App\Models\Employee;
use App\Mail\LoanProgress;
use Illuminate\Support\Facades\Mail;

class EmployeeLoan extends Component
{
    public $national_id;
    public $loan_product;
    public $amount2;
    public $pay_method;
    public $bank5;
    public $bankAcc;
    public $LoanPhoneNo;
    public $loan_officer;
    public $accountSelected1;




    public function LoanProcess(){




        //  register full name in client table

        $this->validate(['national_id'=>'required','pay_method'=>'required']);

        try{



        $check_user=DB::table('clients')->where('nida_number',$this->national_id)->exists();
        if($check_user){

            $this->validate(['national_id'=>'required','amount2'=>'required','loan_officer'=>'required']);

            $client_id=DB::table('clients')->where('nida_number',$this->national_id)->first();
            //DB::table('accounts')->where('client_number',$client_id->client_number)->first();
            $loan_id=time();
            DB::transaction(function () use ($client_id) {

            LoansModel::create([
                'principle'=>$this->amount2,
                'client_id'=>$client_id->id,
                'client_number'=>$client_id->client_number,
                'loan_sub_product'=>$this->loan_product,
                'pay_method'=>$this->pay_method,
                'branch_id'=>auth()->user()->branch,
                'supervisor_id'=> $this->loan_officer,
                'tenure'=>DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('interest_tenure'),
                'interest'=>DB::table('loan_sub_products')->where('sub_product_id',$this->loan_product)->value('interest_value'),
                'status'=>"ONPROGRESS"]);

            // create loan account

              });

            $officer_phone_number=Employee::where('id',$this->loan_officer)->value('email');
            $client_name=$client_id->first_name.' '.$client_id->middle_name.' '.$client_id->last_name;

          Mail::to(auth()->user()->email)->send(new LoanProgress($officer_phone_number,$client_name,'We acknowledge the successful receipt of your loan application. Our team is now processing it and will be in touch shortly regarding further stages '));

            session()->flash('message_2','Successfully saved');
            $this->resetLoanRepayment();



        }

        else {


            session()->flash('message_fail2','invalid member ');


        }

    }catch(\Exception $e){
        session()->flash('message_fail2','error'. $e->getMessage());

    }




    }


    function resetLoanRepayment(){
        $this->loan_product=null;
        $this->national_id=null;
        $this->amount2=null;
        $this->pay_method=null;

    }


    public function render()
    {
        return view('livewire.services.employee-loan');
    }


}
