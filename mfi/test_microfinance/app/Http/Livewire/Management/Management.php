<?php

namespace App\Http\Livewire\Management;

use App\Models\ApprovalAction;
use App\Services\LoanScheduleService;
use Illuminate\Support\Facades\DB;
use App\Models\LoansModel;
use Illuminate\Support\Facades\Session;
use App\Models\Loan_sub_products;
use Livewire\Component;
use App\Models\Charges;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\MembersModel;
use App\Models\loans_summary;
use App\Models\general_ledger;
use App\Models\loans_schedules;
class Management extends Component
{
    public $tab_id=1;
    public $has_reject=false;

    public $view_loan=false;
    public $description;

    public $photo, $futureInterest = false, $collateral_type, $collateral_description, $daily_sales, $loan, $collateral_value, $loan_sub_product;
    public $tenure = 0, $principle = 0, $member, $guarantor, $disbursement_account, $collection_account_loan_interest;
    public $collection_account_loan_principle, $collection_account_loan_charges, $collection_account_loan_penalties;
    public $principle_min_value, $principle_max_value, $min_term, $max_term, $interest_value;
    public $principle_grace_period, $interest_grace_period, $amortization_method;
    public $days_in_a_month = 30, $loan_id, $loan_account_number, $member_number, $topUpBoolena, $new_principle;
    public $interest = 0, $business_licence_number, $business_tin_number, $business_inventory, $cash_at_hand;
    public $cost_of_goods_sold, $operating_expenses, $monthly_taxes, $other_expenses, $monthly_sales;
    public $gross_profit, $table = [], $tablefooter = [], $recommended_tenure, $recommended_installment;
    public $recommended = true, $business_age, $bank1 = 123456, $available_funds;
    public $interest_method, $future_interests, $futureInsteresAmount, $valueAmmount, $net_profit, $status, $products;

    public $coverage;
    public $idx;
    public $sub_product_id;
    public $product;
    public $charges;
    public $manager;

    protected $listeners=['backHomePage','backHomePage'];


    function backHomePage(){
        $this->view_loan=!$this->view_loan;
    }

    function setView($id){
        $this->tab_id=$id;
    }

    public function boot(): void
    {

        $loan = LoansModel::find(Session::get('loan_table_id'));

        if ($loan) {
            $this->idx = $loan->id;
            $this->loan_id = $loan->loan_id;
            $this->member_number = $loan->member_number;
            $this->sub_product_id = $loan->loan_sub_product;

            // Assuming LoanSubProduct is an Eloquent model
            $this->product = Loan_sub_products::where('sub_product_id', $this->sub_product_id)->first();

            if ($this->product) {
                // Assuming Charges is related to LoanSubProduct via a 'product_id' foreign key
                $this->charges = Charges::where('product_id', $this->product->sub_product_id)->get();
            }
        }


        $this->interest_method = "flat";
        $this->loadLoanDetails();
        $this->loadProductDetails();
       $this->loadMemberDetails();
        $this->receiveData();
    }
    public function receiveData()
    {
        $this->generateSchedule((float)$this->principle, (float)$this->interest, (float)$this->tenure);

    }

    private function loadMemberDetails(): void
    {
        $this->guarantor = MembersModel::where('member_number', $this->guarantor)->first();
        $this->member = MembersModel::where('member_number', $this->member_number)->first();
    }

    private function handleFutureInterests($loan_data)
    {
        $total_principle_amount = loans_schedules::where('loan_id', $loan_data->loan_id)
            ->where('installment_date', '>', Carbon::today()->format('Y-m-d'))
            ->sum('principle');

        $total_interest_amount = loans_schedules::where('loan_id', $loan_data->loan_id)
            ->where('installment_date', '>', Carbon::today()->format('Y-m-d'))
            ->sum('interest');

        $principle_collection_account = $this->getCollectionAccountDetails($loan_data->loan_sub_product, 'collection_account_loan_principle');
        $interest_collection_account = $this->getCollectionAccountDetails($loan_data->loan_sub_product, 'collection_account_loan_interest');

        general_ledger::create($this->createLedgerData($loan_data, $total_principle_amount, $total_interest_amount, $principle_collection_account, $interest_collection_account));

        loans_schedules::where('loan_id', $loan_data->loan_id)->where('installment_date', '>', Carbon::today()->format('Y-m-d'))->delete();

        $this->emit('refreshAssessment');
    }

    private function getCollectionAccountDetails($loan_sub_product, $account_type)
    {
        return Loan_sub_products::where('sub_product_id', $loan_sub_product)->first()[$account_type];
    }


    public function updatedFutureInsteresAmount()
    {
        if ($this->futureInsteresAmount > $this->valueAmmount) {
            return $this->futureInsteresAmount = round($this->valueAmmount, 2);
        }
        return $this->futureInsteresAmount;
    }

    private function createLedgerData($loan_data, $total_principle_amount, $total_interest_amount, $principle_collection_account, $interest_collection_account)
    {
        return [
            [
                'gl_code' => $principle_collection_account,
                'description' => 'LOAN PRINCIPLE RECEIVED ON CLOSURE',
                'narrative' => 'LOAN PRINCIPLE RECEIVED ON CLOSURE',
                'debit' => $total_principle_amount,
                'credit' => 0,
                'branch' => $loan_data->branch,
                'date' => Carbon::now(),
                'teller' => Auth::user()->id,
            ],
            [
                'gl_code' => $interest_collection_account,
                'description' => 'LOAN INTEREST RECEIVED ON CLOSURE',
                'narrative' => 'LOAN INTEREST RECEIVED ON CLOSURE',
                'debit' => $total_interest_amount,
                'credit' => 0,
                'branch' => $loan_data->branch,
                'date' => Carbon::now(),
                'teller' => Auth::user()->id,
            ]
        ];
    }


    private function loadLoanDetails(): void
    {
        $this->loan = LoansModel::find(Session::get('loan_table_id'));
        if ($this->loan) {
            $this->fill($this->loan->toArray());

            $this->collateral_value;
            $this->collateral_type = "";
            // $this->guarantors =DB::table('guarantors')->where('loan_id',$this->loan->loan_id)->get();

            // foreach ($this->guarantors as $guarantors){
            $collaterals = DB::table('collaterals')->where('loan_id', session('currentloanID'))->get();

            // foreach ($collaterals as $collateral){

            //     $this->collateral_value = $this->collateral_value + $collateral->collateral_value;
            //     $this->collateral_type = $this->collateral_type ." / ".$collateral->collateral_type ;

            // }

            $this->collateral_type = $this->collateralType(session('currentloanID'));
            $this->collateral_value = $this->calculateCollateralValue(session('currentloanID'));

            //  }
            $this->coverage = (($this->collateral_value / $this->loan->principle) * 100);
            $this->monthly_sales = $this->loan->daily_sales * 30;
            $this->gross_profit = $this->monthly_sales - $this->cost_of_goods_sold;
            $this->net_profit = $this->gross_profit - $this->monthly_taxes;
            $this->available_funds = ($this->net_profit - $this->other_expenses) / 2;
        }
    }

    public function collateralType($loan_id)
    {
        $collateral_type = '';
        $collaterals = DB::table('collaterals')->where('loan_id', $loan_id)->get();
        foreach ($collaterals as $value) {
            if ($value->collateral_category == "salary" || $value->collateral_category == "saving" || $value->collateral_category == "deposit" || $value->collateral_category == "shares") {
                $collateral_type = $value->collateral_category . '/' . $collateral_type;
            } else {
                $collateral_type = $value->collateral_type . '/' . $collateral_type;
            }
        }
        return $collateral_type;
    }

    public function calculateCollateralValue($loan_id)
    {
        $amount = 0;
        $collaterals = DB::table('collaterals')->where('loan_id', $loan_id)->get();
        foreach ($collaterals as $value) {
            $amount = $value->collateral_value + $amount;
        }
        return $amount;
    }

    private function loadProductDetails(): void
    {
        $this->products = Loan_sub_products::where('sub_product_id', $this->loan_sub_product)->get();
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
        }
    }

    private function generateSchedule($disbursed_amount, $interest_rate, $tenure): void
    {
        $principal = $disbursed_amount;
        $dailyInterestRate = $interest_rate / 100;
        $termDays = $tenure;
        $balance = $principal;
        $date = Carbon::now()->addDay();
        $datalist = [];
        $totPayment = 0;
        $totInterest = 0;
        $totPrincipal = 0;

        for ($i = 0; $i < $termDays; $i++) {
            $dailyInstallment = ($principal + ($principal * $dailyInterestRate)) / $termDays;
            $principalPayment = $principal / $termDays;
            $interest = $dailyInstallment - $principalPayment;
            $balance -= $principalPayment;
            $totPayment += $dailyInstallment;
            $totInterest += $interest;
            $totPrincipal += $principalPayment;

            $datalist[] = [
                "Payment" => $dailyInstallment,
                "Interest" => $interest,
                "Principle" => $principalPayment,
                "balance" => $balance,
                "Date" => $date->format('Y-m-d')
            ];

            $date->modify('+30 day');
        }

        $this->table = $datalist;

        $this->tablefooter = [[
            "Payment" => $totPayment,
            "Interest" => $totInterest,
            "Principle" => $totPrincipal,
            "balance" => $balance,
        ]];

        $this->recommended_tenure = $termDays;
        //$this->recommended_installment = $dailyInstallment;
    }




    public $guarantors=[];
    public $loan_schedules=[];
    public $collaterals=[];
    public $businessInfo=[];
    public $loan_general_info=[];
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
        $loan_id= DB::table('loans')->where('id',$loan_id)->value('loan_id');
        return DB::table('loans_schedules')->where('loan_id',$loan_id)->orderBy('created_at','asc')->get();
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

    public $table_data=[];
    function generateScedule($loan_id){

     $loanSchedule= new  LoanScheduleService();
     return  $loanSchedule->generateLoanSchedule($loan_id);
    }
    public function render()
    {
        $loan = LoansModel::find(Session::get('loan_table_id'));



        if ($loan) {
            $this->idx = $loan->id;
            $this->loan_id = $loan->loan_id;
            $this->member_number = $loan->client_number;
            $this->sub_product_id = $loan->loan_sub_product;

            // Assuming LoanSubProduct is an Eloquent model
            $this->product = Loan_sub_products::where('sub_product_id', $this->sub_product_id)->first();

            if ($this->product) {
                // Assuming Charges is related to LoanSubProduct via a 'product_id' foreign key
                $this->charges = Charges::where('product_id', $this->product->sub_product_id)->get();
            }
        }


        $this->interest_method = "flat";
        $this->loadLoanDetails();
        $this->loadProductDetails();
       $this->loadMemberDetails();
        $this->receiveData();

        $this->loanDataReport();

        //get signatory
        $signatoriesId = DB::table('leaderships')
        ->where('is_signatory', 1)
        ->pluck('member_number')
        ->toArray();

    // Check if we have signatory IDs before querying clients
    if (!empty($signatoriesId)) {
        // Retrieve emails of clients whose client_number is in the list of signatory IDs
        $user_emails = DB::table('clients')
            ->whereIn('client_number', $signatoriesId)
            ->pluck('email')
            ->toArray();

        // If we have emails, retrieve the users
        if (!empty($user_emails)) {


            $this->manager = DB::table('users')
                ->whereIn('email', $user_emails)
                ->get();
        } else {
            // Handle case where no emails were found
            $this->manager = collect(); // or handle as needed
        }
    } else {
        // Handle case where no signatories were found
        $this->manager = collect(); // or handle as needed
    }

        return view('livewire.management.management');
    }


    function getStage($loan_id)
    {

        $loan = DB::table('loans')->where('id', $loan_id)->first();
        $product_id = $loan->loan_sub_product;
        $stage_id = $loan->stage_id;
        $loan_product_id = DB::table('loan_sub_products')->where('sub_product_id', $product_id)->value('id');
        $current_stage = DB::table('loan_stages')->where('committee_id', $stage_id)
            ->where('loan_product_id', $loan_product_id)->value('stage_id');

        $next_statge = $current_stage + 1;

        if (DB::table('loan_stages')->where('stage_id', $next_statge)
            ->where('loan_product_id', $loan_product_id)->exists()
        ) {

            $committee_id = DB::table('loan_stages')->where('stage_id', $next_statge)
                ->where('loan_product_id', $loan_product_id)->value('committee_id');
        } else {
            $committee_id = $stage_id;
        }

        return $committee_id;
    }


    function getBackStage($loan_id)
    {

        $loan = DB::table('loans')->where('id', $loan_id)->first();
        $product_id = $loan->loan_sub_product;
        $stage_id = $loan->stage_id;
        $loan_product_id = DB::table('loan_sub_products')->where('sub_product_id', $product_id)->value('id');
        $current_stage = DB::table('loan_stages')->where('committee_id', $stage_id)
            ->where('loan_product_id', $loan_product_id)->value('stage_id');

        $next_statge = $current_stage - 1;

        if (DB::table('loan_stages')->where('stage_id', $next_statge)
            ->where('loan_product_id', $loan_product_id)->exists()
        ) {

            $committee_id = DB::table('loan_stages')->where('stage_id', $next_statge)
                ->where('loan_product_id', $loan_product_id)->value('committee_id');
        } else {
            $committee_id = $stage_id;
        }

        return $committee_id;
    }



    public function commit()
    {
        $currentStage = Session::get('LoanStage');
        //dd($currentStage);
        $status = 'ONPROGRESS';
        if ($currentStage == 'ONPROGRESS') {
            $status = 'BRANCH COMMITTEE';
        }
        if ($currentStage == 'BRANCH COMMITTEE') {
            $status = 'CREDIT ANALYST';
        }
        if ($currentStage == 'CREDIT ANALYST') {
            $status = 'HQ COMMITTEE';
        }
        if ($currentStage == 'HQ COMMITTEE') {
            $status = 'CREDIT ADMINISTRATION';
        }
        if ($currentStage == 'CREDIT ADMINISTRATION') {
            $status = 'AWAITING DISBURSEMENT';
        }

        $stage_id= $this->getStage(Session::get('currentloanID'));
        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' => $status,
            'interest_method' => $this->interest_method,
            'stage_id'=>$stage_id,
            'stage'=>DB::table('committees')->where('id',$stage_id)->value('name'),
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }


    function returnBack(){

        $stage_id= $this->getBackStage(Session::get('currentloanID'));
        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' =>DB::table('committees')->where('id',$stage_id)->value('name'),
            'interest_method' => $this->interest_method,
            'stage_id'=>$stage_id,
            'stage'=>DB::table('committees')->where('id',$stage_id)->value('name'),
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');

    }

    function rejectLoan(){

        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' =>"REJECTED",
            'interest_method' => $this->interest_method,
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }

    function approveLoan(){
        $stage_id= $this->getStage(Session::get('currentloanID'));
        $data = [
            'principle' => $this->principle,
            'interest' => $this->interest,
            'tenure' => $this->recommended ? $this->recommended_tenure : $this->tenure,
            'available_funds' => $this->available_funds,
            'status' =>DB::table('committees')->where('id',$stage_id)->value('name'),
            'interest_method' => $this->interest_method,
            'stage_id'=>$stage_id,
            'stage'=>DB::table('committees')->where('id',$stage_id)->value('name'),
        ];

        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');

    }


    public function closeLoan()
    {
        $loan_data = LoansModel::where('id', Session::get('currentloanID'))->first();
        LoansModel::where('id', Session::get('currentloanID'))->update(['status' => "CLOSED"]);

        if ($this->future_interests) {
            $this->handleFutureInterests($loan_data);
        } else {
            $this->emit('refreshAssessment');
        }
    }


    public function Approve($user_id, $loan_id){



        ApprovalAction::updateOrCreate(

            ['approver_id'=>$user_id,'loan_id'=>$loan_id],
            ['status'=>"APPROVED"]

    );

    $action=ApprovalAction::where('loan_id',$loan_id)->where('status',"APPROVED")->count();
    $max=DB::table('leaderships')->where('is_signatory',1)->count();
        if($action ==$max){


            // disbursement
            $this->approveLoanApplication($loan_id);

            DB::table('loans')->where('id',$loan_id)->update([
                'status'=>"ACTIVE"
               ]);

        }


    }


    public function approveLoanApplication($id){
       // $this->loadData($id);

        LoansModel::where('id', $id)->update([
            'status'=> 'ACTIVE',
           // 'bank_account_number'=> $this->bank1
        ]);

        $next_due_date = Carbon::now()->toDateTimeString();

        foreach ($this->table as $installment) {
            $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
            $product = new loans_schedules;
            $product->loan_id = $this->loan_id;
            $product->installment = $installment['Payment'];
            $product->interest = $installment['Interest'];
            $product->principle = $installment['Principle'];
            $product->balance = $installment['balance'];
           // $product->bank_account_number = $this->bank1;
            $product->completion_status = "ACTIVE";
            $product->status = "ACTIVE";
            $product->installment_date = $next_due_date;
            $product->save();
        }

        foreach ($this->tablefooter as $installment) {
            $next_due_date = date('Y-m-d', strtotime($next_due_date. ' +30 days'));
            $product = new loans_summary;
            $product->loan_id = $this->loan_id;
            $product->installment = $installment['Payment'];
            $product->interest = $installment['Interest'];
            $product->principle = $installment['Principle'];
            $product->balance = $installment['balance'];
            $product->bank_account_number = $this->bank1;
            $product->completion_status = "ACTIVE";
            $product->status = "ACTIVE";
            $product->save();
        }

        $this->processPayment();
        Session::flash('loan_commit', 'The loan has been Approved!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID',null);
        Session::put('currentloanMember',null);
        $this->emit('currentloanID');
    }


    public function Disbursement(){

        // debit


        // credit



    }


    function RejectAction(){
        $this->has_reject=!$this->has_reject;
    }


    public function Reject($user_id, $loan_id){

        $this->validate(['description'=>'required']);

        $max=DB::table('leaderships')->where('is_signatory',1)->count();
        ApprovalAction::updateOrCreate(
            ['approver_id'=>$user_id,'loan_id'=>$loan_id],
            ['status'=>"REJECTED",
                      'description'=>$this->description
            ]

    );

    $action=ApprovalAction::where('loan_id',$loan_id)->count();
    $max=DB::table('leaderships')->where('is_signatory',1)->count();
        if($action ==$max){

             DB::table('loans')->where('id',$loan_id)->update([
              'status'=>"REJECTED"
             ]);

        }

    }







}
