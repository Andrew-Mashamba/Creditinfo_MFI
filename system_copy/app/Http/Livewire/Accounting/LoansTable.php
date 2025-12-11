<?php
namespace App\Http\Livewire\Accounting;
use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\general_ledger;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use App\Models\LoansModel;
use App\Models\ClientsModel;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use App\Services\TransactionPostingService;


class LoansTable extends LivewireDatatable
{

    public $photo;
    public $exportable=true;

    public $collateral_type;
    public $collateral_description;
    public $daily_sales;
    public $loan;
    public $products;
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
    public $days_in_a_month;
    public $loan_id;
    public $status;
    public $loan_account_number;

    public $member_number;
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
    public $bank1="921673621";
    public  $available_funds;




    public function builder()
    {

        return LoansModel::query()->where('status','ACCOUNTING');
        //->leftJoin('branches', 'branches.id', 'members.branch')
    }

    public function viewMember($memberId){
        Session::put('memberToViewId',$memberId);
        $this->emit('refreshMembersListComponent');
    }
    public function editMember($memberId,$name){
        Session::put('memberToEditId',$memberId);
        Session::put('memberToEditName',$name);
        $this->emit('refreshMembersListComponent');
    }

    /**
     * Write code on Method
     *
     * @return array()
     */
    public function columns(): array
    {
        return [

            Column::callback(['client_number'], function ($member_number) {

                return ClientsModel::where('client_number',$member_number)->value('first_name').' '.ClientsModel::where('client_number',$member_number)->value('middle_name').' '.ClientsModel::where('client_number',$member_number)->value('last_name');
            })->label('Member name'),

            Column::callback(['guarantor'], function ($guarantor) {

                return ClientsModel::where('client_number',$guarantor)->value('first_name').' '.ClientsModel::where('client_number',$guarantor)->value('middle_name').' '.ClientsModel::where('client_number',$guarantor)->value('last_name');
            })->label('Guarantor'),
            Column::name('loan_id')
                ->label('loan id'),

            Column::name('loan_account_number')
                ->label('loan account number'),


            Column::name('principle')
                ->label('principle'),

            Column::name('interest')
                ->label('interest'),

            Column::name('heath')
                ->label('health'),


            Column::name('status')
                ->label('Status'),
            Column::callback(['id'], function ($id) {
                return view('livewire.approvals.action', ['id' => $id, 'move' => false]);
            })->unsortable()->label('Decision'),

        ];



    }


    public function reject($id){
        LoansModel::where('id',$id)->update([
            'status'=> 'REJECTED'
        ]);

        Session::flash('loan_commit', 'The loan has been Rejected!');
        Session::flash('alert-class', 'alert-danger');

        Session::put('currentloanID',null);
        Session::put('currentloanMember',null);
        $this->emit('currentloanID');
    }



    public function approve($id){
        $this->loadData($id);

        LoansModel::where('id', $id)->update([
            'status'=> 'ACTIVE',
            'bank_account_number'=> $this->bank1
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
            $product->bank_account_number = $this->bank1;
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





    public function processPayment()
    {

        $institution_id = '';
        $id = auth()->user()->id;

        $mirror_account = AccountsModel::where('account_number', $this->bank1)->value('mirror_account');

        $savings_account_new_balance = (double)AccountsModel::where('account_number', $this->loan_account_number)->value('balance') + (double)$this->principle;

        $savings_ledger_account_new_balance = (double)AccountsModel::where('account_number', $mirror_account)->value('balance') - (double)$this->principle;

        $partner_bank_account_new_balance = (double)AccountsModel::where('account_number', $this->bank1)->value('balance') + (double)$this->principle;

        AccountsModel::where('account_number', $this->loan_account_number)->update(['balance' => $savings_account_new_balance]);
        AccountsModel::where('account_number', $mirror_account)->update(['balance' => $savings_ledger_account_new_balance]);
        AccountsModel::where('account_number', $this->bank1)->update(['balance' => $partner_bank_account_new_balance]);

        $reference_number = time();

        // Refactored: Use TransactionPostingService for double-entry
        $transactionService = new TransactionPostingService();
        $transactionService->postTransaction([
            'first_account' => $mirror_account, // Ledger account (credit)
            'second_account' => $this->loan_account_number, // Loan account (debit)
            'amount' => $this->principle,
            'narration' => 'Loan Disbursement',
        ]);
        $transactionService->postTransaction([
            'first_account' => $this->bank1, // Partner bank (credit)
            'second_account' => $mirror_account, // Ledger account (debit)
            'amount' => $this->principle,
            'narration' => 'Loan Disbursement',
        ]);

        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number' => $this->loan_account_number,
            'record_on_account_number_balance' => $savings_account_new_balance,
            'sender_branch_id' =>1,
            'beneficiary_branch_id' => 1,
           // 'sender_product_id' => AccountsModel::where('account_number', $mirror_account)->value('product_number'),
            //'sender_sub_product_id' => AccountsModel::where('account_number', $mirror_account)->value('sub_product_number'),
           // 'beneficiary_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('product_number'),
            'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('sub_product_number'),
            'sender_id' => '999999',
            'beneficiary_id' => $this->member_number,
            'sender_name' => 'Organization',
            'beneficiary_name' => ClientsModel::where('client_number', $this->member_number)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('last_name'),
            'sender_account_number' => $mirror_account,
            'beneficiary_account_number' => $this->loan_account_number,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => 'Loan disbursement',
            'credit' => (double)$this->principle,
            'debit' => 0,
            'reference_number' => $reference_number,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => '',
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
            'partner_bank' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            'partner_bank_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            'partner_bank_account_number' => $this->bank1,
            'partner_bank_transaction_reference_number' => $reference_number,

        ]);

        //CREDIT RECORD SHARE ACCOUNT
        general_ledger::create([
            'record_on_account_number' => $this->bank1,
            'record_on_account_number_balance' => $partner_bank_account_new_balance,
            'sender_branch_id' => 1,
            'beneficiary_branch_id' => 1,
           // 'sender_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('product_number'),
            //'sender_sub_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('sub_product_number'),
            //'beneficiary_product_id' => AccountsModel::where('account_number', $this->bank1)->value('product_number'),
            //'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->bank1)->value('sub_product_number'),
            'sender_id' => $this->member_number,
            'beneficiary_id' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            'sender_name' => ClientsModel::where('client_number', $this->member_number)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('last_name'),
            'beneficiary_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            'sender_account_number' => $this->loan_account_number,
            'beneficiary_account_number' => $this->bank1,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => 'Loan Disbursement',
            'credit' => (double)$this->principle,
            'debit' => 0,
            'reference_number' => $reference_number,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => '',
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
            'partner_bank' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            'partner_bank_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            'partner_bank_account_number' => $this->bank1,
            'partner_bank_transaction_reference_number' => $reference_number,
        ]);

        //CREDIT RECORD GL
        general_ledger::create([
            'record_on_account_number' => $mirror_account,
            'record_on_account_number_balance' => $savings_ledger_account_new_balance,
            'sender_branch_id' => 1,
            'beneficiary_branch_id' => 1,
           // 'sender_product_id' => AccountsModel::where('account_number', $mirror_account)->value('product_number'),
            //'sender_sub_product_id' => AccountsModel::where('account_number', $mirror_account)->value('sub_product_number'),
           // 'beneficiary_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('product_number'),
            //'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->loan_account_number)->value('sub_product_number'),
            'sender_id' => '999999',
            'beneficiary_id' => $this->loan_account_number,
            'sender_name' => AccountsModel::where('account_number', $mirror_account)->value('account_name'),
            'beneficiary_name' => ClientsModel::where('client_number', $this->member_number)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member_number)->value('last_name'),
            'sender_account_number' => $mirror_account,
            'beneficiary_account_number' => $this->loan_account_number,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => 'Loan Disbursement',
            'credit' => 0,
            'debit' => (double)$this->principle,
            'reference_number' => $reference_number,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => '',
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
            'partner_bank' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
            'partner_bank_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
            'partner_bank_account_number' => $this->bank1,
            'partner_bank_transaction_reference_number' => $reference_number,
        ]);



    }


    public function saveLoanPayments(array $datalist)
    {
        $startDate=now();
        $installmentDate = Carbon::parse($startDate); // Convert the start date to a Carbon instance

        // Insert each record from the detailed payment list into the loans_schedules table
        foreach ($datalist as $data) {
            DB::table('loans_schedules')->insert([
                'payment' => $data['Payment'],
                'interest' => $data['Interest'],
                'principal' => $data['Principle'],
                'balance' => $data['balance'],
                'installment_date' => $installmentDate->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Increment the installment date by 30 days
            $installmentDate->addDays(30);
        }


    }


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



    public function loadData($id): void
    {

        $this->loan = LoansModel::where('id', $id)->get();

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

            $this->status = $theloan->status;

        }
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

            $this->days_in_a_month = $product->days_in_a_month;


        }

        $this->guarantor = ClientsModel::where('client_number', $this->guarantor)->get();
        $this->member = ClientsModel::where('client_number', $this->member_number)->get();

     $this->proccessData($id);
    }



    public function proccessData($id)   {

        if($this->tenure){

        }else{
            $this->tenure = '12';
        }
        if($this->principle){
            LoansModel::where('id',$id)->update([
                'principle' => $this->principle,
                'tenure' => $this->tenure
            ]);
        }


        $this->loan = LoansModel::where('id', $id)->get();

        foreach ($this->loan as $theloan) {

            $this->principle = $theloan->principle;

            $this->tenure = $theloan->tenure;

        }


        $this->monthly_sales = (double)$this->daily_sales * (double)$this->days_in_a_month;


        $this->gross_profit = $this->monthly_sales - (double)$this->cost_of_goods_sold;
        $this->net_profit = $this->gross_profit - (double)$this->monthly_taxes;
        $this->available_funds = ($this->net_profit - (double)$this->other_expenses) / 2;

        $interest = $this->interest_value / 12;



            $payment = $this->calc_payment($this->principle, $this->tenure, $interest, 2);

            $this->print_schedule($this->principle, $interest, $payment);



    }


    function calc_payment($pv, $payno, $int, $accuracy)
    {


// now do the calculation using this formula:

//******************************************
//            INT * ((1 + INT) ** PAYNO)
// PMT = PV * --------------------------
//             ((1 + INT) ** PAYNO) - 1
//******************************************

        $int = $int / 100;

        // convert to a percentage
        $value1 = $int * pow((1 + $int), $payno);
        $value2 = pow((1 + $int), $payno) - 1;
        $pmt = $pv * ($value1 / $value2);
// $accuracy specifies the number of decimal places required in the result
        $pmt = number_format($pmt, $accuracy, ".", "");

        return $pmt;

    } // calc_payment ====================================================================



}









