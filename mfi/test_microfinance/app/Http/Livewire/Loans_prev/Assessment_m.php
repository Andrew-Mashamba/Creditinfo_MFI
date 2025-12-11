<?php


namespace App\Http\Livewire\Loans;

use App\Models\ClientsModel;
use App\Models\Employee;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;


use App\Mail\LoanProgress;
use Illuminate\Support\Facades\Session;
use Livewire\WithFileUploads;
use App\Models\Clients;
use App\Models\AccountsModel;
use App\Models\Branches;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\loan_images;
use App\Models\LoansModel;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use Carbon\Carbon;

use App\Models\issured_shares;
use App\Models\general_ledger;
use App\Services\TransactionPostingService;

class Assessment_m extends Component
{


    use WithFileUploads;

    public $photo;
    public $futureInterest=false;

    public $collateral_type;
    public $collateral_description;
    public $daily_sales;
    public $loan;
    public $collateral_value;
    public $loan_sub_product;
    public $tenure = '12';
    public $principle;
    public $member;


    public $guarantor;
    public $disbursement_account;
    public $collection_account_loan_interest;
    public $collection_account_loan_principle;
    public $collection_account_loan_charges;
    public $collection_account_loan_penalties;
    public $principle_min_value;
    public $principle_max_value;
    public $min_term;
    public $max_term;
    public $interest_value;
    public $principle_grace_period;
    public $interest_grace_period;
    public $amortization_method;
    public $days_in_a_month = 30;
    public $loan_id;
    public $loan_account_number;

    public $member_number;
    public $topUpBoolena;
    public $new_principle;


    public $interest;
    public $business_licence_number;
    public $business_tin_number;
    public $business_inventory;
    public $cash_at_hand;

    public $cost_of_goods_sold;
    public $operating_expenses;
    public $monthly_taxes;
    public $other_expenses;
    public $monthly_sales;
    public $gross_profit;
    public $table;
    public $tablefooter;
    public $recommended_tenure;
    public $recommended_installment;
    public $recommended = true;
    public $business_age;
    public $bank1=123456;
    public  $available_funds;

    public $interest_method;
    public $future_interests;
    public $futureInsteresAmount;
    public $valueAmmount;


    protected $listeners=['refreshAssessment'=>'$refresh'];


    public function boot(): void
    {
        $loan_id=LoansModel::where('id',Session::get('currentloanID'))->first();
        $date=Carbon::today()->format('Y-m-d');
        $this->valueAmmount=loans_schedules::where('loan_id',$loan_id->loan_id)->where('installment_date','>=',$date)->sum('interest');
         $this->futureInsteresAmount=round($this->valueAmmount,2);


        $this->interest_method="flat";

        $this->loan = LoansModel::where('id', Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan) {
            $this->loan_id = $theloan->loan_id;
            $this->loan_account_number = $theloan->loan_account_number;
            $this->loan_sub_product = $theloan->loan_sub_product;
            $this->member_number = $theloan->member_number;

            $this->guarantor = $theloan->guarantor;
            $this->principle = $theloan->principle;
            $this->interest = $theloan->interest;

            $this->business_licence_number = $theloan->business_licence_number;
            $this->business_tin_number = $theloan->business_tin_number;
            $this->business_inventory = $theloan->business_inventory;

            $this->cash_at_hand = $theloan->cash_at_hand;
            $this->daily_sales = $theloan->daily_sales;
            $this->cost_of_goods_sold = $theloan->cost_of_goods_sold;

            $this->operating_expenses = $theloan->operating_expenses;
            $this->monthly_taxes = $theloan->monthly_taxes;
            $this->other_expenses = $theloan->other_expenses;

            $this->collateral_value = $theloan->collateral_value;
            $this->collateral_type = $theloan->collateral_type;
            $this->tenure = $theloan->tenure;
            $this->business_age = $theloan->business_age;
            $this->interest_method = $theloan->interest_method;

            $this->status = $theloan->status;

        }
        $this->products = Loan_sub_products::where('product_id', $this->loan_sub_product)->get();


        foreach ($this->products as $product) {

            $this->disbursement_account = $product->disbursement_account;
            $this->collection_account_loan_interest = $product->collection_account_loan_interest;
            $this->collection_account_loan_principle = $product->collection_account_loan_principle;
            $this->collection_account_loan_charges = $product->collection_account_loan_charges;

            $this->collection_account_loan_penalties = $product->collection_account_loan_penalties;
            $this->principle_min_value = $product->principle_min_value;
            $this->principle_max_value = $product->principle_max_value;

            $this->min_term = $product->min_term;
            $this->max_term = $product->max_term;
            $this->interest_value = $product->interest_value;

            $this->principle_grace_period = $product->principle_grace_period;
            $this->interest_grace_period = $product->interest_grace_period;
            $this->amortization_method = $product->amortization_method;

            $this->days_in_a_month = $this->days_in_a_month;


        }

        $this->guarantor = ClientsModel::where('client_number', $this->guarantor)->get();
        $this->member = ClientsModel::where('client_number', $this->member_number)->get();


    }

    public function render(): Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {


         if($this->interest){
             $this->interest_value=$this->interest;
         }

        if($this->tenure){

        }else{
            $this->tenure = '12';
        }
        if($this->principle){
            LoansModel::where('id', Session::get('currentloanID'))->update([
                'principle' => $this->principle,
                'tenure' => $this->tenure,
                'interest_method'=>$this->interest_method,
                'interest'=>$this->interest,
            ]);
        }


        $this->loan = LoansModel::where('id', Session::get('currentloanID'))->get();

        foreach ($this->loan as $theloan) {

            $this->principle = $theloan->principle;

            $this->tenure = $theloan->tenure;
            $this->interest_method=$theloan->interest_method;


        }


        $this->monthly_sales = (double)$this->daily_sales * (double)$this->days_in_a_month;


        $this->gross_profit = $this->monthly_sales - (double)$this->cost_of_goods_sold;
        $this->net_profit = $this->gross_profit - (double)$this->monthly_taxes;
        $this->available_funds = ($this->net_profit - (double)$this->other_expenses) / 2;

        $interest = $this->interest_value ;


        if((session()->get('loanStatus')=="AWAITING APPROVAL") || (session()->get('loanStatus')=="ACTIVE") || session()->get('loanStatus')=="AWAITING DISBURSEMENT" || session()->get('loanStatus')=="REJECTED"  ){

            $payment = $this->calc_payment($this->principle, $this->tenure, $interest, 2);
            $this->print_schedule($this->principle, $interest, $payment);

        }

        else{

            if ($this->recommended) {
                $this->print_schedule($this->principle, $interest, $this->available_funds);
            } else {
                $payment = $this->calc_payment($this->principle, $this->tenure, $interest, 2);
                $this->print_schedule($this->principle, $interest, $payment);
            }
        }




        return view('livewire.loans.assessment');
    }


    public function actionBtns($x)
    {
        if ($x == 1) {
            $this->recommended = false;
        }
        if ($x == 2) {
            $this->recommended = true;
        }
        if ($x == 3) {
            $this->commit();
        }
        if ($x == 4) {
            $this->approve();



        }
        if ($x == 5) {
            $this->reject();
        }
        if ($x == 6) {
            $this->disburse();
        }
        if($x==33){
            $this->topUpBoolena=true;
            $this->topUp();
        }if($x==44){
            $this->restructure();
         }
        if($x==55){
            $this->futureInterest=true;
            $this->closeLoan();
        }
    }


    public function commit()
    {
        if ($this->recommended) {

            LoansModel::where('id', Session::get('currentloanID'))->update([
                'principle' => $this->principle,
                'tenure' => $this->recommended_tenure,
                'available_funds'=> $this->available_funds,
                'status'=> 'AWAITING APPROVAL',
                'interest_method'=>$this->interest_method,
            ]);
            Session::flash('loan_commit', 'The loan has been committed!');
            Session::flash('alert-class', 'alert-success');

        } else {

            LoansModel::where('id', Session::get('currentloanID'))->update([
                'principle' => $this->principle,
                'tenure' => $this->tenure,
                'available_funds'=> $this->available_funds,
                'status'=> 'AWAITING APPROVAL',
                'interest_method'=>$this->interest_method,

            ]);

            Session::flash('loan_commit', 'The loan has been committed!');
            Session::flash('alert-class', 'alert-success');
        }

        Session::put('currentloanID',null);
        Session::put('currentloanClient',null);
        $this->emit('currentloanID');

    }


    public function updatedFutureInsteresAmount(){

        if($this->futureInsteresAmount > $this->valueAmmount){
            return $this->futureInsteresAmount= round($this->valueAmmount,2);
        }else{
            return $this->futureInsteresAmount;
        }
    }

    public function closeLoan(){

        $loan_data=LoansModel::where('id',Session::get('currentloanID'))->first();

        LoansModel::where('id',Session::get('currentloanID'))->update(['status'=>"CLOSED"]);


        if($this->future_interests){
            // get total amount to be debited/credited as principle
            $total_principle_amount=loans_schedules::where('loan_id',$loan_data->loan_id)->where('installment_date','>',Carbon::today()->format('Y-m-d'))->sum('principle');

            // get total amount to be debited/credited as interest
            $total_interest_amount=loans_schedules::where('loan_id',$loan_data->loan_id)->where('installment_date','>',Carbon::today()->format('Y-m-d'))->sum('interest');


            // get principle collection account
            $loan_product_account_id=Loan_sub_products::where('sub_product_id',$loan_data->loan_sub_product)->value('collection_account_loan_principle');
            $principle_collection_account=AccountsModel::where('id',$loan_product_account_id)->first();
            $principle_collection_account_number=$principle_collection_account->account_number;
            $principle_collection_prev_balance=$principle_collection_account->balance;


            // get interest account collections
            $loan_product_account_interest_id=Loan_sub_products::where('sub_product_id',$loan_data->loan_sub_product)->value('collection_account_loan_interest');
            $interest_collection_account=AccountsModel::where('id',$loan_product_account_interest_id)->first();
            $interest_collection_account_number=$interest_collection_account->account_number;
            $interest_collection_prev_balance=$interest_collection_account->balance;


            if($this->future_interests=="YES")
            {
                   // other process here
                $debit_account_number=$loan_data->loan_account_number;

                // check if there is a balance
                $balance=AccountsModel::where('account_number',$debit_account_number)->value('balance');
                if($balance >= $total_principle_amount ){
                    // debit client account
                    $client_new_balance=(double)$balance-(double)$total_principle_amount;

                    // update amount which is debited
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $debit_account_number,
                        'second_account' => $principle_collection_account_number,
                        'amount' => $total_principle_amount,
                        'narration' => 'loan repayment on close loan option',
                    ]);


                    // credit section  with no interest
                    $principle_collection_account_new_balance=(double)$total_principle_amount + (double)$principle_collection_prev_balance;
                    // update balance
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $principle_collection_account_number,
                        'second_account' => $debit_account_number,
                        'amount' => $total_principle_amount,
                        'narration' => 'loan repayment on close loan option',
                    ]);

                    // record on the general ledger
                    $record_on_general_ledger=new general_ledger();
                     //debit
                    $record_on_general_ledger->debit($debit_account_number,$client_new_balance
                     ,$principle_collection_account_number,$total_principle_amount,'loan repayment on close loan option',$loan_data->loan_id);

                    // credit
                    $record_on_general_ledger->credit($principle_collection_account_number,$principle_collection_account_new_balance,
                        $debit_account_number,$total_principle_amount,'loan repayment on close loan option',$loan_data->loan_id);




                    Session::put('currentloanID',null);
                    Session::put('currentloanClient',null);
                    $this->emit('currentloanID');
                }else{

                  session()->flash('message_fail','insufficient balance');
                }

            }
            elseif($this->future_interests=="NO"){
                $this->validate(['futureInsteresAmount'=>'required']);

             // other process here
                $total_interest_amount=$this->futureInsteresAmount;

                // debit user account balance// but check if has enough balance
                // other process here
                $debit_account_number=$loan_data->loan_account_number;

                $total_amount=(double)$total_interest_amount + (double)$total_principle_amount;

                // check if there is a balance
                $balance=AccountsModel::where('account_number',$debit_account_number)->value('balance');
                if($balance >= $total_amount ){

                    // principle transactions

                    // debit client account
                    $client_new_balance=(double)$balance-(double)$total_principle_amount;

                    // update amount which is debited
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $debit_account_number,
                        'second_account' => $principle_collection_account_number,
                        'amount' => $total_principle_amount,
                        'narration' => 'loan repayment on close loan for principle amount',
                    ]);

                    // credit section  with  principle
                    $principle_collection_account_new_balance=(double)$total_principle_amount + (double)$principle_collection_prev_balance;
                    // update balance
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $principle_collection_account_number,
                        'second_account' => $debit_account_number,
                        'amount' => $total_principle_amount,
                        'narration' => 'loan repayment on close loan for principle amount',
                    ]);


                    // record on the general ledger
                    $record_on_general_ledger=new general_ledger();
                    //debit
                    $record_on_general_ledger->debit($debit_account_number,$client_new_balance
                        ,$principle_collection_account_number,$total_principle_amount,'loan repayment on close loan for principle amount',$loan_data->loan_id);

                    // credit
                    $record_on_general_ledger->credit($principle_collection_account_number,$principle_collection_account_new_balance,
                        $debit_account_number,$total_principle_amount,'loan repayment on close loan for principle amount',$loan_data->loan_id);




                    // interest transactions
                    $client_new_balance=$client_new_balance-(double)$total_interest_amount;
                    //update amount
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $debit_account_number,
                        'second_account' => $interest_collection_account_number,
                        'amount' => $total_interest_amount,
                        'narration' => 'loan repayment on close loan for interest amount',
                    ]);

                    // credit section with interest
                    $interest_collection_new_balance=(double)$interest_collection_prev_balance + (double)$total_interest_amount;
                    // update balance
                    $transactionService = new TransactionPostingService();
                    $transactionService->postTransaction([
                        'first_account' => $interest_collection_account_number,
                        'second_account' => $debit_account_number,
                        'amount' => $total_interest_amount,
                        'narration' => 'loan repayment on close loan for interest amount',
                    ]);



                    Session::put('currentloanID',null);
                    Session::put('currentloanClient',null);
                    $this->emit('currentloanID');
                }else{
                    session()->flash('message_fail','insufficient balance');
                }


            }
        }else{
            $this->emit('refreshAssessment');
        }


    }

    public function restructure(){


        $loanData=LoansModel::where('id',Session::get('currentloanID'))->first();
        // get tatol amount remaining

        if(LoansModel::where('loan_status','RESTRUCTURED')->where('restructure_loanId',$loanData->loan_id)->exists()) {

            session()->flash('message_fail','process failed');
        }else{



        $loanSchedules= loans_schedules::where('loan_id',$loanData->loan_id)->where('completion_status','ACTIVE')->get();

        $amount=0;

        foreach ($loanSchedules as $loan){
            $amount=$amount +$loan->installment;
        }


        $loanId=time();

        LoansModel::create([
            'restructure_loanId'=>$loanData->loan_id,
            'loan_id'=>$loanId,
            'loan_account_number'=>$loanData->loan_account_number,
            'loan_sub_product'=>$loanData->loan_sub_product,
            'client_number'=>$loanData->client_number,
            'guarantor'=>$loanData->guarantor,
            'institution_id'=>$loanData->institution_id,
            'branch_id'=>$loanData->branch_id,
            'principle'=>round($amount,2),
            'interest'=>$loanData->interest,
            'business_name'=>$loanData->business_name,
            'business_age'=>$loanData->business_age,
            'business_category'=>$loanData->business_category,
            'business_type'=>$loanData->business_type,
            'business_licence_number'=>$loanData->business_licence_number,
            'business_tin_number'=>$loanData->business_tin_number,
            'business_inventory'=>$loanData->business_inventory,
            'cash_at_hand'=>$loanData->cash_at_hand,
            'daily_sales'=>$loanData->daily_sales,
            'cost_of_goods_sold'=>$loanData->cost_of_goods_sold,
            'available_funds'=>$loanData->available_funds,
            'operating_expenses'=>$loanData->operating_expenses,
            'monthly_taxes'=>$loanData->monthly_taxes,
            'other_expenses'=>$loanData->other_expenses,
            'collateral_value'=>$loanData->collateral_value,
            'collateral_location'=>$loanData->collateral_location,
            'collateral_description'=>$loanData->collateral_description,
            'collateral_type'=>$loanData->collateral_type,
            'tenure'=>$loanData->tenure,
            'principle_amount'=>round($amount,2),
            'bank_account_number'=>$loanData->bank_account_number,
            'bank'=>$loanData->bank,
            'LoanPhoneNo'=>$loanData->LoanPhoneNo,
            'status'=>"ONPROGRESS",
            'loan_status'=>"RESTRUCTURED",
            'heath'=>'GOOD',
            'phone_number'=>$loanData->phone_number,
            'pay_method'=>$loanData->pay_method,
            'days_in_arrears'=>0,
            'supervisor_id'=>$loanData->supervisor_id,
            'client_id'=>$loanData->client_id,
            'relationship'=>$loanData->relationship,
             'loan_type'=>$loanData->loan_type,


        ]);
            Session::put('currentloanID',null);
            Session::put('currentloanClient',null);
            $this->emit('currentloanID');

        }




    }


    public function topUp(){

        $this->validate(['new_principle'=>'required']);
        $loanData=LoansModel::where('id',Session::get('currentloanID'))->first();
        // get tatol amount remaining


        if(LoansModel::where('loan_status','TOPUP')->where('restructure_loanId',$loanData->loan_id)->exists()) {

            session()->flash('message_fail','process failed');
        }else {

            $loanSchedules = loans_schedules::where('loan_id', $loanData->loan_id)->where('completion_status', 'ACTIVE')->get();

            $amount=0;

            foreach ($loanSchedules as $loan){
                $amount=$amount +$loan->principle;
            }


            if( $this->new_principle > $amount){



            $loanId = time();

            LoansModel::create([
                'future_interest'=>$this->futureInsteresAmount,
                'total_principle'=>$amount,
                'restructure_loanId' => $loanData->loan_id,
                'loan_id' => $loanId,
                'loan_account_number' => $loanData->loan_account_number,
                'loan_sub_product' => $loanData->loan_sub_product,
                'client_number' => $loanData->client_number,
                'guarantor' => $loanData->guarantor,
                'institution_id' => $loanData->institution_id,
                'branch_id' => $loanData->branch_id,
                'principle' => $this->new_principle,
                'interest' => $loanData->interest,
                'business_name' => $loanData->business_name,
                'business_age' => $loanData->business_age,
                'business_category' => $loanData->business_category,
                'business_type' => $loanData->business_type,
                'business_licence_number' => $loanData->business_licence_number,
                'business_tin_number' => $loanData->business_tin_number,
                'business_inventory' => $loanData->business_inventory,
                'cash_at_hand' => $loanData->cash_at_hand,
                'daily_sales' => $loanData->daily_sales,
                'cost_of_goods_sold' => $loanData->cost_of_goods_sold,
                'available_funds' => $loanData->available_funds,
                'operating_expenses' => $loanData->operating_expenses,
                'monthly_taxes' => $loanData->monthly_taxes,
                'other_expenses' => $loanData->other_expenses,
                'collateral_value' => $loanData->collateral_value,
                'collateral_location' => $loanData->collateral_location,
                'collateral_description' => $loanData->collateral_description,
                'collateral_type' => $loanData->collateral_type,
                'tenure' => $loanData->tenure,
                'principle_amount' => $this->new_principle,
                'bank_account_number' => $loanData->bank_account_number,
                'bank' => $loanData->bank,
                'LoanPhoneNo' => $loanData->LoanPhoneNo,
                'status' => "ONPROGRESS",
                'loan_status' => "TOPUP",
                'heath' => 'GOOD',
                'phone_number' => $loanData->phone_number,
                'pay_method' => $loanData->pay_method,
                'days_in_arrears' => 0,
                'supervisor_id' => $loanData->supervisor_id,
                'client_id' => $loanData->client_id,
                'relationship' => $loanData->relationship,
                'loan_type' => $loanData->loan_type,


            ]);


            Session::put('currentloanID',null);
            Session::put('currentloanClient',null);
            $this->emit('currentloanID');
        }else{
                session()->flash('message_fail','invalid amount');
            }
        }


    }

    public function approve(){


        // CREATE LOAN ACCOUNT
      $loan=  LoansModel::where('id',session()->get('currentloanID'))->first();

      $client_email=ClientsModel::where('client_number',$loan->client_number)->first();
      $client_name=$client_email->first_name.' '.$client_email->middle_name.' '.$client_email->last_name;
        $officer_phone_number=Employee::where('id',$client_email->loan_officer)->value('email');

        Mail::to($client_email->email)->send(new LoanProgress($officer_phone_number,$client_name,'Your loan has been approved! We are now finalizing the disbursement process'));


        if(LoansModel::where('id',session()->get('currentloanID'))->value('loan_status')=="RESTRUCTURED"){

            loans_schedules::where('loan_id',$loan->restructure_loanId)->where('completion_status','ACTIVE')->update([
                'completion_status'=>'CLOSED',
                'status'=>'CLOSED'
            ]);


          //  LoansModel::where('id',session()->get('currentloanID'))->update(['status'=>"CLOSED"]);
            // source account number

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->save();
            }



            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status'=> 'AWAITING DISBURSEMENT',
//
            ]);



        }


        elseif(LoansModel::where('id',session()->get('currentloanID'))->value('loan_status')=="TOPUP"){
            // top up process here  TOPUP

            $loanValues=LoansModel::where('id',session()->get('currentloanID'))->where('loan_status','TOPUP')->first();


            //principle
            $prev_loan=$loanValues->restructure_loanId;
// close loan
            LoansModel::where('loan_id',$loanValues->restructure_loanId)->update(['status'=>"CLOSED"]);

            // close installment
            loans_schedules::where('loan_id',$prev_loan)->update(['completion_status'=>'CLOSED']);

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->save();
            }



            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status'=> 'AWAITING DISBURSEMENT',
//
            ]);





            Session::flash('loan_commit', 'The loan has been Approved!');
            Session::flash('alert-class', 'alert-success');

            Session::put('currentloanID',null);
            Session::put('currentloanClient',null);
            $this->emit('currentloanID');


        }


        else{

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_schedules;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->installment_date = $next_due_date;
                $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                $product->save();
            }

            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status'=> 'AWAITING DISBURSEMENT',

            ]);


        }

        Session::flash('loan_commit', 'The loan has been Approved!');
        Session::flash('alert-class', 'alert-success');

        Session::put('currentloanID',null);
        Session::put('currentloanClient',null);
        $this->emit('currentloanID');
    }

    public function reject(){
        LoansModel::where('id', Session::get('currentloanID'))->update([
            'status'=> 'REJECTED'
        ]);
        ClientsModel::where('id',DB::table('loans')->where('id',Session::get('currentloanID'))->value('client_id'))->update([
            'client_status'=> 'REJECTED',
        ]);

        Session::flash('loan_commit', 'The loan has been Rejected!');
        Session::flash('alert-class', 'alert-danger');

        Session::put('currentloanID',null);
        Session::put('currentloanClient',null);
        $this->emit('currentloanID');
    }












    function calc_principal($payno, $int, $pmt)
    {
// check that required values have been supplied
        if (empty($payno)) {
            echo "<p class='error'>a value for NUMBER of PAYMENTS is required</p>";
            exit;
        } // if
        if (empty($int)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now do the calculation using this formula:

//******************************************
//             ((1 + INT) ** PAYNO) - 1
// PV = PMT * --------------------------
//            INT * ((1 + INT) ** PAYNO)
//******************************************

        $int = $int / 100;        //convert to percentage
        $value1 = (pow((1 + $int), $payno)) - 1;
        $value2 = $int * pow((1 + $int), $payno);
        $pv = $pmt * ($value1 / $value2);
        $pv = number_format($pv, 2, ".", "");

        return $pv;

    } // calc_principal ==================================================================


    function calc_number($pv, $int, $pmt)
    {
// check that required values have been supplied
        if (empty($pv)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($int)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now do the calculation using this formula:

//******************************************
//         log(1 - INT * PV/PMT)
// PAYNO = ---------------------
//             log(1 + INT)
//******************************************

        $int = $int / 100;
        $value1 = log(1 - $int * ($pv / $pmt));
        $value2 = log(1 + $int);
        $payno = $value1 / $value2;
        $payno = abs($payno);
        $payno = number_format($payno, 0, ".", "");

        return $payno;

    } // calc_number =====================================================================

    function calc_rate($pv, $payno, $pmt)
    {
// check that required values have been supplied
        if (empty($pv)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($payno)) {
            echo "<p class='error'>a value for NUMBER of PAYMENTS is required</p>";
            exit;
        } // if
        if (empty($pmt)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

// now try and guess the value using the binary chop technique
        $GuessHigh = (float)100;    // maximum value
        $GuessMiddle = (float)2.5;    // first guess
        $GuessLow = (float)0;      // minimum value
        $GuessPMT = (float)0;      // result of test calculation

        do {
            // use current value for GuessMiddle as the interest rate,
            // and set level of accurracy to 6 decimal places
            $GuessPMT = (float)calc_payment($pv, $payno, $GuessMiddle, 6);

            if ($GuessPMT > $pmt) {    // guess is too high
                $GuessHigh = $GuessMiddle;
                $GuessMiddle = $GuessMiddle + $GuessLow;
                $GuessMiddle = $GuessMiddle / 2;
            } // if

            if ($GuessPMT < $pmt) {    // guess is too low
                $GuessLow = $GuessMiddle;
                $GuessMiddle = $GuessMiddle + $GuessHigh;
                $GuessMiddle = $GuessMiddle / 2;
            } // if

            if ($GuessMiddle == $GuessHigh) break;
            if ($GuessMiddle == $GuessLow) break;

            $int = number_format($GuessMiddle, 9, ".", "");    // round it to 9 decimal places
            if ($int == 0) {
                echo "<p class='error'>Interest rate has reached zero - calculation error</p>";
                exit;
            } // if

        } while ($GuessPMT !== $pmt);

        return $int;

    } // calc_rate =======================================================================

    function calc_payment($pv, $payno, $int, $accuracy)
    {


// now do the calculation using this formula:

//******************************************
//            INT * ((1 + INT) ** PAYNO)
// PMT = PV * --------------------------
//             ((1 + INT) ** PAYNO) - 1
//******************************************

        $int = $int / 100;    // convert to a percentage
        $value1 = $int * pow((1 + $int), $payno);
        $value2 = pow((1 + $int), $payno) - 1;
        $pmt = $pv * ($value1 / $value2);
// $accuracy specifies the number of decimal places required in the result
        $pmt = number_format($pmt, $accuracy, ".", "");

        return $pmt;

    } // calc_payment ====================================================================

    function print_schedulex($balance, $rate, $payment, $totPayment, $totInterest, $totPrincipal)
    {
        // check that required values have been supplied
        if (empty($balance)) {
            echo "<p class='error'>a value for PRINCIPAL is required</p>";
            exit;
        } // if
        if (empty($rate)) {
            echo "<p class='error'>a value for INTEREST RATE is required</p>";
            exit;
        } // if
        if (empty($payment)) {
            echo "<p class='error'>a value for PAYMENT is required</p>";
            exit;
        } // if

        echo '<table border="1">';
        echo '<colgroup align="right" width="20">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<colgroup align="right" width="115">';
        echo '<tr><th>#</th><th>PAYMENT</th><th>INTEREST</th><th>PRINCIPAL</th><th>BALANCE</th></tr>';

        $count = 0;
        do {
            $count++;

            // calculate interest on outstanding balance
            $interest = $balance * $rate / 100;

            // what portion of payment applies to principal?
            $principal = $payment - $interest;

            // watch out for balance < payment
            if ($balance < $payment) {
                $principal = $balance;
                $payment = $interest + $principal;
            } // if

            // reduce balance by principal paid
            $balance = $balance - $principal;

            // watch for rounding error that leaves a tiny balance
            if ($balance < 0) {
                $principal = $principal + $balance;
                $interest = $interest - $balance;
                $balance = 0;
            } // if

            echo "<tr>";
            echo "<td>$count</td>";
            echo "<td>" . number_format($payment, 2, ".", ",") . "</td>";
            echo "<td>" . number_format($interest, 2, ".", ",") . "</td>";
            echo "<td>" . number_format($principal, 2, ".", ",") . "</td>";
            echo "<td>" . number_format($balance, 2, ".", ",") . "</td>";
            echo "</tr>";

            @$totPayment = $totPayment + $payment;
            @$totInterest = $totInterest + $interest;
            @$totPrincipal = $totPrincipal + $principal;
            if ($payment < $interest) {
                echo "</table>";
                echo "<p>Payment < Interest amount - rate is too high, or payment is too low</p>";
                exit;
            } // if
        } while ($balance > 0);
        echo "<tr>";
        echo "<td>&nbsp;</td>";
        echo "<td><b>" . number_format($totPayment, 2, ".", ",") . "</b></td>";
        echo "<td><b>" . number_format($totInterest, 2, ".", ",") . "</b></td>";
        echo "<td><b>" . number_format($totPrincipal, 2, ".", ",") . "</b></td>";
        echo "<td>&nbsp;</td>";
        echo "</tr>";
        echo "</table>";

    } // print_schedule ==================================================================



    function print_schedule($balance, $rate, $payment)
    {


        $totPayment =0;
        $totInterest =0;
        $totPrincipal =0;
        $datalist = array();
        $count = 0;


        if($rate <=0){
            $rate=12;
        }

        if($balance){

        }else{
            $balance = 0;
        }
        if($payment > 0){

        }else{
            $payment = 0;
        }



     if($this->interest_method=="decline_balance"){
         while($balance > 0) {
             $count++;

             // calculate interest on outstanding balance
             $interest = $balance * $rate / 100;

             // what portion of payment applies to principal?
             $principal = $payment - $interest;

             // watch out for balance < payment
             if ($balance < $payment) {
                 $principal = $balance;
                 $payment = $interest + $principal;
             } // if

             // reduce balance by principal paid
             if($principal < 0 ){
                 $balance = 0;
             }else{
                 $balance = $balance - $principal;
             }


             // watch for rounding error that leaves a tiny balance
             if ($balance < 0) {
                 $principal = $principal + $balance;
                 $interest = $interest - $balance;
                 $balance = 0;
             } // if


//   dd($payment,$interest,$principal,$balance);

             $datalist[] = array(
                 "Payment" => $payment,
                 "Interest" => $interest,
                 "Principle" => $principal,
                 "balance" => $balance
             );




             @$totPayment = $totPayment + $payment;

             @$totInterest = $totInterest + $interest;

             @$totPrincipal = $totPrincipal + $principal;



             if ($payment < $interest) {

             } // if

         }

     }



     elseif($this->interest_method=="flat"){

         // calculate interest on outstanding balance
         $interest = $balance * $rate / 100;
         while($balance > 0) {

             $count++ ;

             // what portion of payment applies to principal?
             $principal = $payment - $interest;

             // watch out for balance < payment
             if ($balance < $payment) {
                 $principal = $balance;
                 $payment = $interest + $principal;
             } // if

             // reduce balance by principal paid
             if($principal < 0 ){
                 $balance = 0;
             }else{
                 $balance = $balance - $principal;
             }


             // watch for rounding error that leaves a tiny balance
             if ($balance < 0) {
                 $principal = $principal + $balance;
                 $interest = $interest - $balance;
                 $balance = 0;
             } // if


//   dd($payment,$interest,$principal,$balance);

             $datalist[] = array(
                 "Payment" => $payment,
                 "Interest" => $interest,
                 "Principle" => $principal,
                 "balance" => $balance
             );




             @$totPayment = $totPayment + $payment;

             @$totInterest = $totInterest + $interest;

             @$totPrincipal = $totPrincipal + $principal;



             if ($payment < $interest) {

             } // if

         }

     }



        $datalistfooter[] = array(
            "Payment" => $totPayment,
            "Interest" => $totInterest,
            "Principle" => $totPrincipal,
            "balance" => $balance
        );




        $this->table = $datalist;
        $this->tablefooter = $datalistfooter;
        $this->recommended_tenure = $count;
        $this->recommended_installment = $payment;


    } // print_schedule ==================================================================



}

