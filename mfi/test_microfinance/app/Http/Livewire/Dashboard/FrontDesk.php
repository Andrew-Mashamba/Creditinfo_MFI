<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Services\BankTransactionService;
use App\Services\TransactionPostingService;
use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\ClientsModel;
use App\Models\Employee;
use App\Models\general_ledger;
use App\Models\institutions;
use App\Models\loans_schedules;
use App\Exports\LoanRepayment;
use App\Mail\LoanProgress;
use App\Models\LoansModel;
use App\Helper\NewMemberUpdateStatusHelper;

use App\Models\Teller;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Livewire\Document\StatementReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use App\Console\Commands\endOfDayJob;
use App\Exports\CustomExport;
use App\Models\MembersModel;
use Illuminate\Support\Facades\Config;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer;
use App\Exports\ExportTransactions;
use App\Exports\ExportCheque;

use App\Models\Grant;

use App\Models\PendingRegistration;
use App\OutBoundRequests\HttpOutRequest;

use DOMDocument;
use Exception;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Models\sub_products;

use App\Models\issured_shares;

use App\Models\TeamUser;
use App\Models\ChequeModel;

use Symfony\Component\Mime\Crypto\SMimeSigner;

use App\Http\Traits\CurrentUserTrait;
use App\Models\LoanStage;
use function PHPUnit\Framework\isNull;

class FrontDesk extends Component
{


    public $deposit_type;
    public $member;
    public $depositType;
    public $member1;
    public $member_number1;
    public $LoanPhoneNo;
    public $accountSelected1;
   // public $stage_type;
    public $onboarding_process;
    public $item;
    public $notes,$service_category;
    public $payment_type,$cash_bank_account,$expense_account,$cash_amount,$description;
    public $bank;
    public $reference_number;
    public $phone_number;
    public $accountSelected=null;
    public $amount;



    // new loan parameters
    public $full_name;
    public $phone_number2;
    public $national_id;
    public $amount2;
    public $loan_officer;
    public $pay_method;
    public $loan_product;
    public $start_date;
    public   $end_date;

    // Enhanced loan application fields
    public $tenure = 12;
    public $collateral_type;
    public $collateral_value;
    public $collateral_description;
    public $guarantor;
    public $tips_bank_code;
    public $tips_bank_account;
    
    // Step navigation for loan application
    public $currentStep = 1;
    public $totalSteps = 5;
    
    // Validation rules for loan application
    protected $rules = [
        'member_number1' => 'required|numeric',
        'loan_type_2' => 'required|in:New,TopUp,Restructuring',
        'loan_product' => 'required_if:loan_type_2,New,TopUp',
        'amount2' => 'required|numeric|min:1000',
        'tenure' => 'nullable|integer|min:1|max:60',
        'pay_method' => 'required|in:cash,internal_transfer,tips_mno,tips_bank',
        'loan_officer' => 'required|exists:users,id',
        'tips_bank_code' => 'required_if:pay_method,tips_bank',
        'tips_bank_account' => 'required_if:pay_method,tips_bank',
        'payment_type' => 'required',
        'nationalId1' => 'required',
        'amount' => 'required',
        'reference_number' => 'required_if:payment_type,BANK',
        'bank' => 'required_if:payment_type,BANK',
    ];
    
    protected $messages = [
        'member_number1.required' => 'Member number is required.',
        'member_number1.numeric' => 'Member number must be a number.',
        'loan_type_2.required' => 'Please select a loan type.',
        'loan_type_2.in' => 'Please select a valid loan type.',
        'loan_product.required_if' => 'Please select a loan product.',
        'amount2.required' => 'Loan amount is required.',
        'amount2.numeric' => 'Loan amount must be a number.',
        'amount2.min' => 'Loan amount must be at least 1,000 TZS.',
        'tenure.integer' => 'Loan term must be a whole number.',
        'tenure.min' => 'Loan term must be at least 1 month.',
        'tenure.max' => 'Loan term cannot exceed 60 months.',
        'pay_method.required' => 'Please select a payment method.',
        'pay_method.in' => 'Please select a valid payment method.',
        'loan_officer.required' => 'Please assign a loan officer.',
        'loan_officer.exists' => 'Selected loan officer is invalid.',
        'tips_bank_code.required_if' => 'Please select a bank for other bank transfer.',
        'tips_bank_account.required_if' => 'Please enter bank account number for other bank transfer.',
    ];

    public $loan_product1;
    public $bank3;
    public $bank5;
    public $bankAcc;
    public $nida_number;
    public $amount3;
    public $bank_account;
    public $check_account_number;
    public $daterange;
    public $start_date_input;
    public $end_date_input;
    public $cheque_values;
    public bool $enableCheque=false;
    public $nationalId1;
    public $amount4;

    public $phone_number4;
    public $nida_number4;
    public $tab_id = '1';
    public $title = 'Deposits report';
    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $products;

    public $count=1;
    public $transaction_reference_number;
    public $bank_account_number;

    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $deposit_charge_min_value;

    public $product_number;

    public $numberOfProducts1;
    public $products1;
    public $item1;
    public $retry;

    // Loan application properties
    public $loan_member;
    public $loan_type;
    public $loan_amount;
    public $loan_purpose;
    public $collateral;
    public $guarantor1;
    public $guarantor2;


    public $ExternalAccounts;
    public $days;
    public $deposits;

    public $results;
    public $new_member_deposit_notes;
    public $registrationFee;
    public $initial_shares_value;
    public $member_number;
    public $mno;


    public $inputValue = "Initial value";
    public $loan_type_2;
    public $service;

    public $restructuring_loan_id;
public $bankz;

    // Additional properties that might be missing
    public $payment_method;
    public $phone_number3;
    public $bank1;
    public $amount1;
    public $notes1;
    public $reference_number1;

    public $config;

    protected $listeners = ['refreshMembersListComponent' => '$refresh',
        'dateRange'=>'dateRange'
    ];





    //protected $rules=['payment_type'=>'required', 'nationalId1'=>'required','amount'=>'required','reference_number'=>'required_if:payment_type,BANK','bank'=>'required_if:payment_type,BANK'];




    public function boot()
    {


        //dd($this->config);

        $this->item = 1;

        $daysLoop = [];

        $date = date('F Y');//Current Month Year
        while (strtotime($date) <= strtotime(date('Y-m') . '-' . date('t', strtotime($date)))) {
            $day_num = date('j', strtotime($date));//Day number
            $day = $day_num;


            $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));//Adds 1 day onto current date

            $daysLoop[] = $day;

        }

        $this->days = $daysLoop;

        $this->registrationFee = institutions::where('id',1)->value('registration_fees');
        //dd($this->registrationFee);
//        $this->registrationFee = institutions::where('institution_id',Session::get('institution'))->value('application_fee');
        $initial_shares = institutions::where('id',1)->value('initial_shares');
        $value_per_share = institutions::where('id',1)->value('value_per_share');
        if($initial_shares && $value_per_share){

            $this->initial_shares_value = $initial_shares * $value_per_share;
            //dd($this->initial_shares_value);
        }

    }



    public function downloadPDFFile(){
     $id=1;

     $value=new StatementReport();
     $value->Download(1);
      $this->emitTo('document.statement-report','downloadPDF',$id);

    }


    public  function downloadExcelFile(){

        // Set the timezone to Africa/Dar_es_Salaam (Tanzania)
        $timezone = 'Africa/Dar_es_Salaam';
        (new \Carbon\Carbon)->setTimeZone($timezone);

        // Get the current date and time
        $currentDateTime = Carbon::now();

        $this->validate(['check_account_number'=>'required']);

        return    Excel::download(new  LoanRepayment(
            $this->check_account_number,
            'Lubia Saccos',
        'images/nbc.png',
        'Andrew Mashamba',
        '10500',
        '6500',
            $currentDateTime->toDateTimeString(),
        ) , 'Statement.xlsx');

        //Session::flash('error1', 'invalid input account number /client number');



    }


    /**
     * Navigate to the next step in the loan application process
     */
    public function nextStep()
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->validateStep($this->currentStep);
            $this->currentStep++;
        }
    }

    /**
     * Navigate to the previous step in the loan application process
     */
    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Validate the current step before proceeding
     */
    private function validateStep($step)
    {
        switch ($step) {
            case 1:
                $this->validate([
                    'member_number1' => 'required|numeric'
                ], [
                    'member_number1.required' => 'Member number is required.',
                    'member_number1.numeric' => 'Member number must be a number.'
                ]);
                
                // Check if member exists
                if (!DB::table('clients')->where('client_number', $this->member_number1)->exists()) {
                    throw ValidationException::withMessages([
                        'member_number1' => 'Member not found. Please check the member number.'
                    ]);
                }
                break;
                
            case 2:
                $this->validate([
                    'loan_type_2' => 'required|in:New,TopUp,Restructuring',
                    'loan_product' => 'required_if:loan_type_2,New,TopUp',
                    'amount2' => 'required|numeric|min:1000',
                    'tenure' => 'nullable|integer|min:1|max:60'
                ]);
                break;
                
            case 3:
                $this->validate([
                    'pay_method' => 'required|in:cash,internal_transfer,tips_mno,tips_bank',
                    'tips_bank_code' => 'required_if:pay_method,tips_bank',
                    'tips_bank_account' => 'required_if:pay_method,tips_bank'
                ]);
                break;
                
            case 4:
                $this->validate([
                    'loan_officer' => 'required|exists:users,id'
                ]);
                break;
        }
    }

    /**
     * Process the loan application with comprehensive validation and error handling
     */
    public function LoanProcess()
    {
        try {
            // Validate all required fields for final submission
            $this->validate([
                'member_number1' => 'required|numeric',
                'loan_type_2' => 'required|in:New,TopUp,Restructuring',
                'loan_product' => 'required_if:loan_type_2,New,TopUp',
                'amount2' => 'required|numeric|min:1000',
                'tenure' => 'nullable|integer|min:1|max:60',
                'pay_method' => 'required|in:cash,internal_transfer,tips_mno,tips_bank',
                'loan_officer' => 'required|exists:users,id',
                'tips_bank_code' => 'required_if:pay_method,tips_bank',
                'tips_bank_account' => 'required_if:pay_method,tips_bank'
            ], $this->messages);


            

            // Check if member exists
            $client = DB::table('clients')->where('client_number', $this->member_number1)->first();
            if (!$client) {
                throw ValidationException::withMessages([
                    'member_number1' => 'Member not found. Please check the member number.'
                ]);
            }

            // Validate loan amount against product limits
            if ($this->loan_product) {
                $product = DB::table('loan_sub_products')->where('sub_product_id', $this->loan_product)->first();
                if ($product) {
                    if ($this->amount2 < $product->principle_min_value) {
                        throw ValidationException::withMessages([
                            'amount2' => "Loan amount must be at least " . number_format($product->principle_min_value) . " TZS for this product."
                        ]);
                    }
                    if ($this->amount2 > $product->principle_max_value) {
                        throw ValidationException::withMessages([
                            'amount2' => "Loan amount cannot exceed " . number_format($product->principle_max_value) . " TZS for this product."
                        ]);
                    }
                }
            }

            // Process loan based on type
            if ($this->loan_type_2 !== "Restructuring") {
                $this->processNewLoan($client);
            } else {
                $this->processRestructuringLoan();
            }

            // Success message and reset
            session()->flash('message_2', 'Loan application submitted successfully!');
            $this->resetLoanApplication();

        } catch (ValidationException $e) {
            session()->flash('message_fail2', 'Please correct the errors and try again.');
            throw $e;
        } catch (\Exception $e) {
            session()->flash('message_fail2', 'An error occurred while processing your application. Please try again.');
            Log::error('Loan application error: ' . $e->getMessage());
        }
    }

    /**
     * Process a new loan application
     */
    private function processNewLoan($client)
    {
        DB::transaction(function () use ($client) {
            // Fetch product ID and initial stage
            $product_id = DB::table('loan_sub_products')->where('sub_product_id', $this->loan_product)->value('id');
     
            //dd($this->member_number1,$this->loan_type_2,$this->loan_product,$this->amount2,$this->tenure,$this->pay_method,$this->loan_officer,$this->tips_bank_code,$this->tips_bank_account);

            // Create loan record
            $loanID = LoansModel::create([
                'principle' => $this->amount2,
                'client_id' => $client->id,
                'client_number' => $client->client_number,
                'loan_sub_product' => $this->loan_product,
                'pay_method' => $this->pay_method,
                'branch_id' => auth()->user()->branch,
                'supervisor_id' => $this->loan_officer,
                'loan_id' => time(),
                'selectedLoan' => $this->accountSelected ?? null,
                'loan_type_2' => $this->loan_type_2,
               
                'tenure' => $this->tenure ?? 12,
                'interest' => DB::table('loan_sub_products')->where('sub_product_id', $this->loan_product)->value('interest_value'),
                'status' => 'ONPROGRESS',
                // Payment method specific fields
                'bank_account_number' => $this->pay_method === 'internal_transfer' ? $this->bankAcc : 
                                       ($this->pay_method === 'tips_bank' ? $this->tips_bank_account : null),
                'bank' => $this->pay_method === 'internal_transfer' ? DB::table('banks')->where('id', $this->bank5)->value('bank_name') :
                        ($this->pay_method === 'tips_bank' ? $this->tips_bank_code : null),
                'phone_number' => $this->pay_method === 'tips_mno' ? $this->LoanPhoneNo : null,
            ])->id;

            // Process loan stages
            //$this->processLoanStages($loanID, $this->loan_product, $client);
        });
    }

    /**
     * Process a restructuring loan application
     */
    private function processRestructuringLoan()
    {
        DB::transaction(function () {
            $loanId = $this->restructuring_loan_id;
            $loan = LoansModel::find($loanId);

            if (!$loan) {
                throw new \Exception('Loan not found for restructuring.');
            }

            // Get the first stage to reset the loan to initial stage
            $first_stage = DB::table('current_loans_stages')
                ->where('loan_id', $loanId)
                ->orderBy('id', 'asc')
                ->first();

            if ($first_stage) {
                // Update loan status to PENDING (initial stage) and set loan_status to RESTRUCTURE
                $loan->loan_type_2 = 'Restructuring';
                $loan->status = $first_stage->stage_name; // Reset to first stage
                $loan->loan_status = 'RESTRUCTURE'; // Set loan_status to RESTRUCTURE
                $loan->save();

                // Reset all approvers to PENDING status
                DB::table('approvers_of_loans_stages')
                    ->where('loan_id', $loanId)
                    ->update(['status' => 'PENDING']);
                    
                // Reset current loan stages to PENDING
                DB::table('current_loans_stages')
                    ->where('loan_id', $loanId)
                    ->update(['status' => 'PENDING']);
            }
        });
    }

    /**
     * Reset the loan application form
     */
    public function resetLoanApplication()
    {
        $this->currentStep = 1;
        $this->member_number1 = null;
        $this->loan_type_2 = null;
        $this->loan_product = null;
        $this->amount2 = null;
        $this->tenure = 12;
        $this->pay_method = null;
        $this->loan_officer = null;
        $this->tips_bank_code = null;
        $this->tips_bank_account = null;
        $this->accountSelected = null;
        $this->bank5 = null;
        $this->bankAcc = null;
        $this->mno = null;
        $this->LoanPhoneNo = null;
    }


    /**
     * Process loan stages for the created loan.
     */
    private function processLoanStages($loanID, $loan_sub_product, $client)
    {
        // Fetch product ID and stages for the loan sub-product
        $product_id = DB::table('loan_sub_products')->where('sub_product_id', $loan_sub_product)->value('id');
        $loanStages = DB::table('loan_stages')->where('loan_product_id', $product_id)->get();



        foreach ($loanStages as $index => $stage) {
            if ($stage->stage_type == 'Department') {
                $name = DB::table('departments')
                    ->where('id', $stage->stage_id)
                    ->value('department_name');
            } elseif ($stage->stage_type == 'Committee') {
                $name = DB::table('committees')
                    ->where('id', $stage->stage_id)
                    ->value('name');
            }
            if($index == 0){
                $affectedRows = LoansModel::where('id', $loanID)
                    ->update([
                        'status' => $name
                    ]);
            }
            // Insert current loan stage
            $current_loans_stages_id = DB::table('current_loans_stages')->insertGetId([
                'loan_id' => $loanID,
                'product_id' => $loan_sub_product,
                'stage_id' => $stage->stage_id,
                'stage_type' => $stage->stage_type,
                'stage_name' => $name,
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Handle approvers based on stage type (Department or Committee)
            $this->processStageApprovers($stage, $loanID, $current_loans_stages_id);
        }
    }

    /**
     * Process stage approvers based on stage type.
     */
    private function processStageApprovers($stage, $loanID, $current_loans_stages_id)
    {
        if ($stage->stage_type == 'Department') {

            // Department stage: fetch department name and insert approver
            $department_name = DB::table('departments')->where('id', $stage->stage_id)->value('department_name');

            DB::table('approvers_of_loans_stages')->insert([
                'loan_id' => $loanID,
                'stage_id' => $stage->stage_id,
                'current_loans_stages_id' => $current_loans_stages_id,
                'stage_type' => 'Department',
                'stage_name' => $department_name,
                'user_id' => null, // User ID is empty in this case
                'user_name' => null, // User name is empty
                'status' => 'PENDING',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } elseif ($stage->stage_type == 'Committee') {
            // Committee stage: fetch committee members and insert each as approvers
            $committee_members = DB::table('committee_users')->where('committee_id', $stage->stage_id)->get();

            $name = DB::table('committees')
                ->where('id', $stage->stage_id)
                ->value('name');

            foreach ($committee_members as $committee_member) {


                $user_name = User::where('id', $committee_member->user_id)->value('name');

                DB::table('approvers_of_loans_stages')->insert([
                    'loan_id' => $loanID,
                    'stage_id' => $stage->stage_id,
                    'current_loans_stages_id' => $current_loans_stages_id,
                    'stage_type' => 'Committee',
                    'stage_name' => $name,
                    'user_id' => $committee_member->user_id,
                    'user_name' => $user_name,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }








    public function process2()
    {
        $this->validate([
            'loan_member' => 'required',
            'loan_type' => 'required',
            'loan_amount' => 'required|numeric|min:1000',
            'loan_purpose' => 'required|min:3',
            'collateral' => 'required|min:3',
            'guarantor1' => 'required|min:2',
            'guarantor2' => 'required|min:2',
        ], [
            'loan_member.required' => 'Please select a member.',
            'loan_type.required' => 'Please select a loan type.',
            'loan_amount.required' => 'Loan amount is required.',
            'loan_amount.numeric' => 'Loan amount must be a number.',
            'loan_amount.min' => 'Loan amount must be at least 1,000 TZS.',
            'loan_purpose.required' => 'Loan purpose is required.',
            'loan_purpose.min' => 'Loan purpose must be at least 3 characters.',
            'collateral.required' => 'Collateral information is required.',
            'collateral.min' => 'Collateral information must be at least 3 characters.',
            'guarantor1.required' => 'Primary guarantor is required.',
            'guarantor1.min' => 'Primary guarantor name must be at least 2 characters.',
            'guarantor2.required' => 'Secondary guarantor is required.',
            'guarantor2.min' => 'Secondary guarantor name must be at least 2 characters.',
        ]);

        try {
            // Get loan sub-product details
            $loanSubProduct = \App\Models\Loan_sub_products::find($this->loan_type);
            if (!$loanSubProduct) {
                session()->flash('message_fail_loan', 'Invalid loan type selected.');
                return;
            }

            // Create loan application
            $loan = new \App\Models\LoansModel();
            $loan->client_number = $this->loan_member;
            $loan->loan_sub_product = $this->loan_type;
            $loan->principle = $this->loan_amount;
            $loan->purpose = $this->loan_purpose;
            $loan->collateral = $this->collateral;
            $loan->guarantor1 = $this->guarantor1;
            $loan->guarantor2 = $this->guarantor2;
            $loan->status = 'PENDING';
            $loan->created_by = auth()->id();
            $loan->save();

            // Reset form
            $this->reset(['loan_member', 'loan_type', 'loan_amount', 'loan_purpose', 'collateral', 'guarantor1', 'guarantor2']);

            session()->flash('message2', 'Loan application submitted successfully. Application ID: ' . $loan->id);

        } catch (\Exception $e) {
            session()->flash('message_fail_loan', 'Error submitting loan application: ' . $e->getMessage());
        }
    }

    public function process3()
{

   if($this->service_category=="Office_expenses"){


    $this->validate([

        'cash_bank_account'=>'required',
        'expense_account'=>'required',
        'cash_amount'=>'required',
        'description'=>'required'

    ]);
    try{
        $this->cashTransaction();
        session()->flash('message_3','completed');

    }catch(\Exception $e){


        session()->flash('message_fail_withdraw',$e->getMessage());

    }


   }else{
    $this->validate([
        'payment_method' => 'required',
        'member_number' => 'required',
        'amount4' => 'required',
        'accountSelected' => 'required|numeric'
    ]);




    if ($this->payment_method === "CASH") {

        $this->processCashPayment();
    } elseif (in_array($this->payment_method, ["BANK", "MOBILE"])) {
        $this->processBankOrMobilePayment();
    } elseif ($this->payment_method === "CHEQUE") {
        $this->processChequePayment();
    }


   }



}







private function processCashPayment()
{
    DB::beginTransaction();


    try {
        // Get teller account details

        //dd($this->accountSelected,$this->bankz);
        $debited_account = AccountsModel::where('account_number', $this->accountSelected)->first();
        $credited_account  =AccountsModel::where('account_number', $this->bankz)->first();

         if($debited_account >= $this->amount4 ){

//        $tellerNewBalance = (double)$tellerBalance - (double)$this->amount4;
//
//        // Updateteller account balance
//        AccountsModel::where('id', $tellerAccountDetails->id)->update(['balance' => $tellerNewBalance]);
//
//        // Record in general ledger
//        $this->recordGeneralLedger($tellerAccountDetails->account_number, $tellerNewBalance, $this->amount4, 'cash withdraw', 'debit');
//
//        // Update customer loan account
//        $customerLoanAccount = DB::table('loans')->where('loan_account_number', $this->accountSelected)->first();
//        $customer_new_balance= ($customerBalance - $this->amount4) ;
//        AccountsModel::where('account_number', $this->accountSelected)->update(['balance' => $customer_new_balance]);
//
//        // Record in general ledger for customer loan account
//
//        $this->recordGeneralLedger($this->accountSelected, 0, $this->amount4, 'cash withdraw', 'debit');



             // debit suspense account
             $data = [
                 'first_account' => $credited_account,
                 'second_account' => $debited_account,
                 'amount' => $this->amount4,
                 'narration' => "Funds withdrawal",
                 'action' => 'withdraw'
             ];
             // dd($data);
             //  Ensure $this->transactionService is initialized
             $transactionServicex = new TransactionPostingService();

             $response = $transactionServicex->postTransaction($data);



        DB::commit();
        $this->resetLoanRepayment();



        session()->flash('message_3', 'Transaction successfully completed.');
    }
    else{
        if($customerBalance >= $this->amount4){
            session()->flash('message_fail_withdraw', 'Transaction failed:Teller insufficient balance ');

        }else{
            session()->flash('message_fail_withdraw', 'Transaction failed: customer insufficient balance ');

        }

    }

    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('message_fail_withdraw', 'Transaction failed: ' . $e->getMessage());
    }
}


public function cashTransaction(){


    $debited_account =AccountsModel::where('account_number',$this->cash_bank_account)->first();
    $credited_account =AccountsModel::where('account_number', $this->expense_account)->first();



    $data = [

        'first_account' => $debited_account,
        'second_account' => $credited_account,
        'amount' => $this->cash_amount,
        'narration' =>  $this->description,

    ];
    $transactionServicex = new TransactionPostingService();


    $response = $transactionServicex->postTransaction($data);

}




private function processBankOrMobilePayment()
{
    DB::beginTransaction();

    try {

       // '/selectedAccount-customer/payment_method/source_bank/payload_bank-phone-number/amount';


       $credit_account_number =$this->bank;
       $debit_account_number =$this->accountSelected;
       $payload_account=$this->phone_number;
        $amount= $this->amount4;

         // debit
         $account= DB::table('accounts')->where('account_number',$debit_account_number)->first();

         if( $account->balance >= $this->amount4 ){

         $new_balance= (double)( $account->balance - $this->amount4 );
         DB::table('accounts')->where('account_number',$debit_account_number)->update([ 'balance'=>$new_balance]);
         $this->recordGeneralLedger($credit_account_number, $new_balance, $this->amount4, 'bank/mobile transaction', 'debit');

         //parent debit
         //$new_parent_balancee= (double)($account->balance -$this->amount4)
          // credit
          $dest_account= DB::table('accounts')->where('account_number',$credit_account_number)->first();
          $new_dest_balance= (double)( $dest_account->balance + $this->amount4);
          AccountsModel::where('account_number', $credit_account_number)->update([ 'balance'=>$new_dest_balance ]);
          $this->recordGeneralLedger($credit_account_number, $new_dest_balance, $this->amount3, 'bank/mobile transaction', 'debit');

             // now Bank Transactions

        $data = [
            'phone_number' => $this->phone_number,
            'account_number' => $this->account_number,
            'reference_id' => time(),
            'amount' => $this->amount4,
            'currency' => 'TZS',
            'description' => "bank_transactions",
        ];

        $transaction_type="MOBILE";

        $bank_service=new BankTransactionService() ;
        $result = $bank_service->sendTransactionData($transaction_type, $data);

        if ($result['status'] === 'success') {

            dd('success');
            session()->flash('message', 'Transaction successful!');
        } else {

            session()->flash('message_fail_withdraw', 'Transaction failed: ' . $result['message']);
            die;
        }



        // $destinationAccountNumber = $this->bank_account;
        // $mirrorAccount = AccountsModel::where('account_number', $credit_account_number)->first();
        // $mirrorNewBalance = (double)$mirrorAccount->balance + (double)$this->amount3;
        // AccountsModel::where('id', $this->bank3)->update(['balance' => $mirrorNewBalance]);

        // $customerLoanAccount =$this->accountSelected ;



        // Record in general ledger

        DB::commit();
        $this->resetLoanRepayment();
        session()->flash('message_3', 'Transaction successfully completed.');


    }
    else{
        session()->flash('message_fail_withdraw', 'insufficient balance ' );

    }
    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('message_fail_withdraw', 'Transaction failed: ' . $e->getMessage());
    }
}

private function processChequePayment()
{

    // dd($this->amount3);
    $this->validate([
        'bank3' => 'required',
        'accountSelected' => 'required',
        'amount4' => 'required'
    ]);



    DB::beginTransaction();

    try {
        $customerAccount = AccountsModel::where('account_number', $this->accountSelected)->first();
        $customerNewBalance = (double)$customerAccount->balance - (double)$this->amount4;
        AccountsModel::where('account_number', $customerAccount->account_number)->update(['balance' => $customerNewBalance]);

        $mirrorAccount = AccountsModel::where('account_number', $this->bank3)->first();
        $mirrorNewBalance = (double)$mirrorAccount->balance + (double)$this->amount4;
        AccountsModel::where('account_number', $mirrorAccount->account_number)->update(['balance' => $mirrorNewBalance]);
        // Record in general ledger
        $this->recordGeneralLedger($customerAccount->account_number, $customerNewBalance, $this->amount4, 'Cheque Transaction', 'debit');
        $this->recordGeneralLedger($mirrorAccount->account_number, $mirrorNewBalance, $this->amount4, 'Cheque Transaction', 'credit');

        // Update cheque table
        $chequeNumber = "CHQ" . substr(time(), 4);
        $chequeId = DB::table('cheques')->insertGetId([
            'customer_account' => $customerAccount->account_number,
            'amount' => $this->amount4,
            'cheque_number' => $chequeNumber,
            'branch' => $customerAccount->branch_number,
            'bank_account' => $mirrorAccount->account_number,
            'finance_approver' => auth()->user()->employeeId,
            'status' => "PENDING",
        ]);

        $this->cheque_values = DB::table('cheques')->where('id', $chequeId)->get();
        $this->enableCheque = true;

        DB::commit();
        $this->resetLoanRepayment();
        session()->flash('message_3', 'Cheque Issued Successfully');
    } catch (\Exception $e) {
        DB::rollBack();
        session()->flash('message_fail_withdraw', 'Transaction failed: ' . $e->getMessage());
    }
}

private function recordGeneralLedger($accountNumber, $newBalance, $amount, $description, $type)
{
    $referenceNumber = time();
    $entryData = [
        'record_on_account_number' => $accountNumber,
        'record_on_account_number_balance' => $newBalance,
        'sender_branch_id' => auth()->user()->branch,
        'beneficiary_branch_id' => DB::table('clients')->where('client_number', AccountsModel::where('account_number', $accountNumber)->value('client_number'))->value('branch') ?: '0000',
        'sender_product_id' => '0000',
        'sender_sub_product_id' => '0000',
        'beneficiary_product_id' => AccountsModel::where('account_number', '0000')->value('product_number'),
        'beneficiary_sub_product_id' => AccountsModel::where('account_number', '0000')->value('sub_product_number'),
        'sender_id' => 0,
        'beneficiary_id' => 0,
        'sender_name' => '0000',
        'beneficiary_name' => '0000',
        'sender_account_number' => '0000',
        'beneficiary_account_number' => '0000',
        'transaction_type' => ' ',
        'sender_account_currency_type' => 'TZS',
        'beneficiary_account_currency_type' => 'TZS',
        'reference_number' => $referenceNumber,
        'trans_status' => 'successfully',
        'trans_status_description' => $description,
        'swift_code' => null,
        'destination_bank_name' => 'NBC',
        'destination_bank_number' => '0000',
        'recon_status' => 'Pending',
        'institution_id' => 2,
    ];

    if ($type === 'debit') {
        $entryData['debit'] = $amount;
        $entryData['credit'] = 0;
    } else {
        $entryData['credit'] = $amount;
        $entryData['debit'] = 0;
    }

    general_ledger::create($entryData);
}






    public function setAccount($account, $service = 'OTHER'){
        $account_number = DB::table('sub_accounts')->where('id',$account)->value('account_number');
        $this->accountSelected=$account_number;

        if($service == 'LOAN'){
            $this->service = $service;
            $this->accountSelected=$account;
        }

        if($this->loan_type_2=="Restructuring"){
           $this->restructuring_loan_id = $account;
        }
        
        // For TopUp loans, validate that the selected loan has the same product
        if($this->loan_type_2=="TopUp" && $this->loan_product){
            $selectedLoan = DB::table('loans')->where('id', $account)->first();
            if($selectedLoan && $selectedLoan->loan_sub_product != $this->loan_product){
                $this->addError('loan_product', 'TopUp loans must use the same product as the existing loan.');
                $this->accountSelected = null;
                return;
            }
        }
    }

    public function resetLoanRepayment(){
        $this->accountSelected=null;
        $this->bank=null;
        $this->reference_number=null;
        $this->phone_number=null;
        $this->member_number1=null;
       // $this->pay_type=null;
        $this->phone_number2=null;
        $this->phone_number3=null;
        $this->amount3=null;
        $this->amount=null;
        $this->bank_account=null;
        $this->amount2=null;
        $this->loan_product=null;
        $this->loan_officer=null;
        $this->pay_method=null;
        $this->national_id=null;
        $this->member_number=null;
        $this->payment_method=null;
         $this->bank3=null;
         $this->nationalId1=null;
         $this->payment_type=null;
         
         // Reset enhanced loan application fields
         $this->tenure = 12;
         $this->tips_bank_code = null;
         $this->tips_bank_account = null;
         $this->loan_type_2 = null;
         $this->bank5 = null;
         $this->bankAcc = null;
         $this->mno = null;
         $this->LoanPhoneNo = null;
         
         // Reset step navigation
         $this->currentStep = 1;
    }




    public function render()
    {

        if( $this->start_date  &&  $this->end_date){

        }else{
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            $this->start_date = $startOfMonth->toDateString();
            $this->end_date = $endOfMonth->toDateString();

            $this->start_date_input = $startOfMonth->format('m-d-Y');
            $this->end_date_input = $endOfMonth->format('m-d-Y');

        }
        return view('livewire.dashboard.front-desk');
    }


    public function resetNewMemberRegistrationData(){

        // reset your data
        $this->phone_number=null;
        $this->reference_number=null;
          $this->deposit_type=null;
            $this->depositType=null;
           $this->phone_number4=null;
          $this->nida_number4=null;
            $this->new_member_deposit_notes=null;
            //$this->initial_shares_value=null;
            $this->nida_number=null;
            $this->amount=null;
            $this->member= null;
            $this->amount3=null;
            $this->account_number=null;


    }



public $narration;

public function process()
{
    $this->validate([
        'deposit_type' => 'required',
        'depositType'=>'required_if:member,new',
        'phone_number4'=>'required_if:member,new',
        //'nida_number4'=>'required_if:member,new',
        'new_member_deposit_notes'=>'required_if:member,new',
        'registrationFee'=>'required_if:depositType,RegistrationFee',
        'initial_shares_value'=>'required_if:depositType,MandatoryShares',
        //bank
        'reference_number'=>'required_if:deposit_type,BANK'


    ]);

    if($this->service == 'LOAN'){




        $loan_id = DB::table('loans')->where('loan_account_number',$this->accountSelected)->value('id');

        $this->update_repayment($loan_id, (double)$this->amount);

    }else{
        //  dd($this->depositType,$this->new_member_deposit_notes,$this->bank);
        DB::beginTransaction();

        try {
            if ($this->deposit_type === "BANK") {

                $this->narration=$this->new_member_deposit_notes ? : $this->notes;
                $this->handleBankDeposit();


            } else if ($this->deposit_type == "CASH") {


                $this->handleCashDeposit();
            }


            DB::commit();
            $this->resetNewMemberRegistrationData();
        } catch (ValidationException $e) {
            DB::rollBack();
            session()->flash('message_fail', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
        } catch (\Exception $e) {

            session()->flash('message_fail', 'An error occurred. Please try again.');
        }

    }






}


function handleBankDeposit(){

   // dd($this->all());

   // $this->amount = $this->initial_shares_value; //

   if($this->depositType=='RegistrationFee' || $this->depositType =='MandatoryShares'){

    $payment_type= $this->depositType=='MandatoryShares' ? 'shares' : 'fee';

    switch($this->depositType){

        case('RegistrationFee') :    $this->amount= $this->registrationFee; break;

        case('MandatoryShares') :    $this->amount= $this->initial_shares_value; break;

    }


    if($this->registrationPayments($this->nida_number4 ? : $this->nida_number, $this->amount,$payment_type))
    {

    $debited_account=AccountsModel::where('id',$this->bank)->first(); // cash account;
    switch($this->depositType){
        case('RegistrationFee') :  $credited_account=AccountsModel::where('account_number',DB::table('setup_accounts')->where('item','entry')->value('account_number'))->first();
    break;
    case('MandatoryShares') :

            $credited_account=AccountsModel::where('account_number',
                        DB::table('setup_accounts')->where('item','suspense_shares')
                         ->value('account_number'))->first();
                                             break;
    }


    try {
        $data = [

            'first_account' => $debited_account,
            'second_account' => $credited_account,
            'amount' => $this->amount,
            'narration' =>  $this->narration,

        ];



      // dd($data);

        $transactionServicex = new TransactionPostingService();
        $response = $transactionServicex->postTransaction($data);

        session()->flash('message1', json_encode($response));


    } catch (\Exception $e) {
        // Flash an error message to the session
        session()->flash('message_fail', 'Transaction failed: ' . $e->getMessage());
    }

  //  session()->flash('message1', 'Successfully processed.');

    }else{

        // $var=0;
        // $var2=2;

        // $var2/$var;


    }


   }





}

private function postTransaction($reference_number, $debited_account, $credited_account)
{
    //dd($reference_number, $debited_account, $credited_account);
    // Debit entry

    try{


    $debited_new_balance = $debited_account->balance - $this->amount;
    $this->debit($reference_number, $debited_account, $credited_account, $debited_new_balance);


    // Credit entry
    $credited_new_balance = $credited_account->balance + $this->amount;
    $this->credit($reference_number, $debited_account, $credited_account, $credited_new_balance);

    // Update account balances
   // $product_account_balance = AccountsModel::where('account_number', operator: $this->product_account)->value('balance');
  //  $product_account_new_balance = (int)$product_account_balance - (int)$this->creditableAmount;
    AccountsModel::where('account_number',  $debited_account->account_number)->update(['balance' => $debited_new_balance]);
    //AccountsModel::where('account_number', $debited_account->account_number)->update(['balance' => $debited_new_balance]);
    AccountsModel::where('account_number', $credited_account->account_number)->update(['balance' => $credited_new_balance]);


}catch(\Exception $error){

    dd($error);
}


}

private function debit($reference_number, $debited_account, $credited_account, $debited_new_balance)
{
    general_ledger::create([
        'record_on_account_number' => $debited_account->account_number,
        'record_on_account_number_balance' => $debited_new_balance,
        'major_category_code' => $debited_account->major_category_code,
        'category_code' => $debited_account->category_code,
        'sub_category_code' => $debited_account->sub_category_code,
        'sender_name' => $debited_account->account_name,
        'beneficiary_name' => $credited_account->account_name,
        'sender_account_number' => $debited_account->account_number,
        'beneficiary_account_number' => $credited_account->account_number,
        'narration' => $this->narration,
        'credit' => 0,
        'debit' => $this->amount,
        'reference_number' => $reference_number,
        'trans_status' => 'Pending Approval', // Status updated once approved
        'trans_status_description' => 'Awaiting Approval',
        'payment_status' => 'Pending',
        'recon_status' => 'Pending',
    ]);
}

private function credit($reference_number, $debited_account, $credited_account, $credited_new_balance)
{
    general_ledger::create([
        'record_on_account_number' => $credited_account->account_number,
        'record_on_account_number_balance' => $credited_new_balance,
        'major_category_code' => $credited_account->major_category_code,
        'category_code' => $credited_account->category_code,
        'sub_category_code' => $credited_account->sub_category_code,
        'sender_name' => $debited_account->account_name,
        'beneficiary_name' => $credited_account->account_name,
        'sender_account_number' => $debited_account->account_number,
        'beneficiary_account_number' => $credited_account->account_number,
        'narration' =>  $this->narration,
        'credit' => $this->amount,
        'debit' => 0,
        'reference_number' => $reference_number,
        'trans_status' => 'Pending Approval',
        'trans_status_description' => 'Awaiting Approval',
        'payment_status' => 'Pending',
        'recon_status' => 'Pending',
    ]);
}


public function registrationPayments($nida_number, $amount, $payment_type)
{
    // Check if the nida_number exists in PendingRegistration table
    $pendingExists = PendingRegistration::where('nida_number', $nida_number)->exists();
  //  dd($pendingExists,PendingRegistration::where('nida_number', $nida_number)->get()  );

    if ($payment_type == "shares") {
        // If the user exists, insert the shares payment record
        if ($pendingExists) {
            PendingRegistration::insert([
                'status' => 'ACTIVE',
                'amount' => $amount,
                'nida_number' => $nida_number
            ]);
            return true;
        } else {

            session()->flash('message_fail', 'Pay Registration Fee First ');

            return false;
        }
    }

    // For non-share payments
    if ($pendingExists) {

        session()->flash('message_fail', 'Nida Number is existing ');

        return false;
    }

    // Insert initial payment if the user doesn't exist
    PendingRegistration::insert([
        'status' => 'INITIAL PAY',
        'amount' => $amount,
        'nida_number' => $nida_number
    ]);
    return true;
}







private function handleCashDeposit()
{


    if ($this->member === 'new') {

        if ($this->depositType === 'RegistrationFee') {


//            $this->validate([
//                //'phone_number4' => 'required|string|min:9'
//            ]);

            //dd('ggg1');



//            $check_phone_number = PendingRegistration::where('phone_number', $this->phone_number4)->first();
//
//            if ($check_phone_number) {
//
//
//                session()->flash('message_fail', 'Phone number has been taken');
//            }
//
//


            PendingRegistration::create([
                'reference_number' => $this->reference_number,
                'amount' => $this->registrationFee,
                'account_id' => $this->bank,
                'branch_id' => auth()->user()->branch,
                'phone_number' => $this->phone_number4,
                'nida_number' => $this->nida_number4,
                'status' => "INITIAL PAY",
            ]);





            $this->cashRegistrationFees($this->phone_number4, $this->registrationFee, $this->bank);
        } else if ($this->depositType === "MandatoryShares") {

        //dd($this->nida_number);

            // Check if the NIDA number exists
            $doesAccountExist = PendingRegistration::where('nida_number', $this->nida_number)->exists();

            if ($doesAccountExist) { // If a record exists
                // Create new registration
                PendingRegistration::create([
                    'reference_number' => $this->reference_number,
                    'amount' => $this->initial_shares_value,
                    'account_id' => $this->bank,
                    'branch_id' => auth()->user()->branch,
                    'phone_number' => $this->phone_number4,
                    'nida_number' => $this->nida_number,
                    'status' => "ACTIVE",
                ]);

                // Process mandatory share deposit for the new member
                $this->mandatoryShareCashDepositNewMember($this->nida_number, $this->initial_shares_value);

                session()->flash('message_success', 'Registration successful. Mandatory shares deposited.');

            } else {
                // NIDA number does not exist, display an error message
                session()->flash('message_fail', 'Pay registration fees before mandatory shares');
            }




        }

    } else {
        $this->cashDeposit();
    }




}



    public function process1(){
        if($this->payment_method=="BANK"){

              if($this->retry){

            }
            else{
                $this->withDrawDeposits();

            }

            $bank_request=new HttpOutRequest();
            $request_body=[
                'account_number'=> AccountsModel::where('account_number',$this->bank1)->value('mirror_account'),
                'amount'=>$this->amount1,
                'currency'=>"TZS",
                'destination_account'=>$this->bank_account_number,
                'reference_number'=>$this->transaction_reference_number,
                'call_back_url'=>'http://96.46.181.165/projects/Saccoss/saccos/public/api/bank_funds_transfer_request_call_back'
            ];

            $response=$bank_request->internalFundsTransfer("POST",'http://96.46.181.111/api/bank_funds_transfer_request',$request_body,12);

            if($response['status']==200){
                $this->retry=false;

                DB::table('general_ledger')->where('reference_number',$this->transaction_reference_number)->update([
                    'partner_bank_transaction_reference_number'=>null,
                    'payment_status'=>"SUSPECT",
                ]);
                Session::flash('message2', 'Funds withdraw successfully!');
                Session::flash('alert-class', 'alert-success');

                // update table successfully;  suspect
            }
            else{
                sleep('4');
                if($this->count> 4){

                    DB::table('general_ledger')->where('reference_number',$this->transaction_reference_number)->update(['partner_bank_transaction_reference_number'=>null,
                        'payment_status'=>"FAILED",
                    ]);


                    $this->reverseBankTransaction($this->amount1,AccountsModel::where('id',$this->bank1)->value('account_number'),$this->accountSelected1,$this->transaction_reference_number,$this->transaction_reference_number);
                    session()->flash('message_fail1','Transaction failed and successfully reversed');

                    // update table== failed
                    // send email== transaction details
                }
                else{
                    $this->count++;
                    $this->retry=true;
                    $this->process1();
                }
            }


            $this->resetData1();
        }

        else if($this->payment_method=="CASH"){
            $this->witdrawCashNow();
        }
        else if($this->payment_method=="CHEQUE"){
// first you have to make sure that a selected account has enough balannce

            $customer_balance=AccountsModel::where('account_number',$this->accountSelected1)->value('balance');

            if($customer_balance > $this->amount1){

                //send to finance for issue cheque
                $this->validate(['amount1' => 'required','notes1'=>'required']);

                ChequeModel::create([
                    'customer_account'=>$this->accountSelected1,
                    'amount'=>$this->amount1 ,
                    'member_number'=>AccountsModel::where('account_number',$this->accountSelected1)->value('member_number'),
                    'institution_id'=>auth()->user()->institution_id,
                    'finance_approver'=>'',
                    'manager_approver'=>'',
                    'status'=>'PENDING',
                    'branch'=>auth()->user()->branch,
                ]);

                session()->flash('message2','awaiting approvals');
                session()->flash('alert-class','successfully');

                $this->resetData1();
            }
            else{
                session()->flash('message_fail1','sorry you dont have enough balance');
            }

        }
//        $this->withDrawDeposits();

    }



    public function witdrawCashNow(){
        $this->validate(['accountSelected1'=>'required','amount1'=>'required']);
        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');
        // teller account
        $teller_account=DB::table('accounts')->where('id',$get_account_id)->first();

        // customer account
        $customer_account_balance=DB::table('accounts')->where('account_number',$this->accountSelected1)->value('balance');

        // check if qualified for withdraw

        if($teller_account->balance  >= (double)$this->amount1  && $customer_account_balance >=(double)$this->amount1 ){
//    dd($teller_account->account_number, $this->amount1, $this->accountSelected1);
            // customer new balance
            $customer_account_new_balance=$customer_account_balance- (double)$this->amount1;
            // update customer account
            DB::table('accounts')->where('account_number',$this->accountSelected1)->update(['balance'=>$customer_account_new_balance]);

            // teller new balance
            $teller_account_new_balance_on_cash_withdraw = $teller_account->balance -(double)$this->amount1;
            // update customer account
            DB::table('accounts')->where('account_number',$teller_account->account_number)->update(['balance'=>$teller_account_new_balance_on_cash_withdraw]);


            $reference_number=time();
            $institution_id=1;


            //DEBIT RECORD ON TELLER ACCOUNT
            general_ledger::create([
                'record_on_account_number' => $teller_account->account_number,
                'record_on_account_number_balance' => $teller_account_new_balance_on_cash_withdraw,
                'sender_branch_id' => $institution_id,
                'beneficiary_branch_id' => $institution_id,
                'sender_product_id' =>'0000',
                'sender_sub_product_id' =>'0000',
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('sub_product_number'),
                'sender_id' => '999999',
                'beneficiary_id' => $this->member,
                'sender_name' => 'Organization',
                'beneficiary_name' =>  0000,
                'sender_account_number' => '0000',
                'beneficiary_account_number' => $this->accountSelected1,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' =>'cash withdraw',
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
                'institution_id'=>$institution_id,
            ]);

            //DEBIT RECORD ON CUSTOMER ACCOUNT
            general_ledger::create([
                'record_on_account_number' => $this->accountSelected1,
                'record_on_account_number_balance' => $customer_account_new_balance,
                'sender_branch_id' => $institution_id,
                'beneficiary_branch_id' => $institution_id,
                'sender_product_id' =>'0000',
                'sender_sub_product_id' =>'0000',
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('sub_product_number'),
                'sender_id' => '999999',
                'beneficiary_id' => $this->member,
                'sender_name' => 'Organization',
                'beneficiary_name' =>  0000,
                'sender_account_number' => '0000',
                'beneficiary_account_number' => $this->accountSelected1,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' =>'cash withdraw',
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
                'institution_id'=>$institution_id,

            ]);



//            $this->sendApproval($id,'New withDraw transaction','08');

            $this->resetData1();

            Session::flash('message2', 'Funds deposited successfully!');
            Session::flash('alert-class', 'alert-success');

        }

        else{
            session()->flash('message_teller_fails','you do not have enough  balance');
        }





    }


    public function sendApproval($id,$msg,$code){


        approvals::create([
            'institution' => ' ',
            'process_name' => 'createBranch',
            'process_description' => $msg,
            'approval_process_description' => 'has approved a transaction',
            'process_code' => $code,
            'process_id' => $id,
            'process_status' => 'Pending',
            'user_id'  => Auth::user()->id,
            'team_id'  => ""
        ]);

    }


    public function saveSavings()
    {

        $this->validate(['deposit_type'=>'required','bank'=>'required','reference_number'=>'required','accountSelected'=>'required']);



        $institution_id=auth()->user()->institution_id;

        $this->bank=DB::table('accounts')->where('id',$this->bank)->value('account_number');

        //dd($this->accountSelected);
        /////////////////credit ////////
        $savings_account_new_balance = (double)AccountsModel::where('account_number',$this->accountSelected)->value('balance')+(double)$this->amount;
        /////////debit/////////////////
        $savings_ledger_account_new_balance = (double)AccountsModel::where('account_number', $this->bank)->value('balance')-(double)$this->amount;

        //dd($savings_ledger_account_new_balance);

//        $partner_bank_account_new_balance = (double)AccountsModel::where('account_number',$this->bank)->value('balance')+(double)$this->amount;

        AccountsModel::where('account_number',$this->accountSelected)->update(['balance'=>$savings_account_new_balance]);
        AccountsModel::where('account_number', $this->bank)->update(['balance'=>$savings_ledger_account_new_balance]);
//        AccountsModel::where('account_number',$this->bank)->update(['balance'=>$partner_bank_account_new_balance]);

        $reference_number = time();

        //dd(AccountsModel::where('account_number',$this->accountSelected)->get());


        //CREDIT RECORD
        general_ledger::create([
            'record_on_account_number'=> $this->accountSelected,
            'record_on_account_number_balance'=> $savings_account_new_balance,
            'sender_branch_id'=> '000',
            'beneficiary_branch_id'=> '000',
            'sender_product_id'=>  null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> '999999',//mirrorId
            'beneficiary_id'=> $this->member,
            'sender_name'=> 'Mirror Account',
            'beneficiary_name'=> ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=>  $this->bank,
            'beneficiary_account_number'=> $this->accountSelected,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $this->notes,
            'credit'=> (double)$this->amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
            'institution_id'=>$institution_id,
        ]);



        //DEBIT RECORD GL
        general_ledger::create([
            'record_on_account_number'=>  $this->bank,
            'record_on_account_number_balance'=> $savings_ledger_account_new_balance ,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> '999999',
            'beneficiary_id'=> $this->member,
            'sender_name'=> AccountsModel::where('account_number', $this->bank)->value('account_name'),

            'beneficiary_name'=>  ClientsModel::where('client_number',$this->member)->value('first_name').' '.ClientsModel::where('client_number',$this->member)->value('middle_name').' '.ClientsModel::where('client_number',$this->member)->value('last_name'),
            'sender_account_number'=>  $this->bank,
            'beneficiary_account_number'=> $this->accountSelected,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $this->notes,
            'credit'=> 0,
            'debit'=> (double)$this->amount,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
            'institution_id'=>$institution_id,


        ]);

//        $this->sendApproval($id,'New savings transaction','06');

        $this->resetData();

        Session::flash('message1', 'Savings has been successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }



    public function cashDeposit()
    {

        $institution_id=1;
        $reference_number=now();

    // Start a database transaction to ensure consistency
     DB::transaction(function () use($institution_id,$reference_number) {


    // Get account and teller information
    $get_account_id = DB::table('tellers')->where('employee_id', auth()->user()->employeeId)->value('account_id');
    $teller_account = DB::table('accounts')->where('id', $get_account_id)->value('account_number');

    // Retrieve current balances and update them
    $account = AccountsModel::where('account_number', $this->accountSelected)->first();
    $teller_account_info = AccountsModel::where('account_number', $teller_account)->first();

     // check for onprogress members

      $member_number=$account->client_number;
      //DB::table('clients')->where('client_number',)->first()->nida_number;
     $helper= new   NewMemberUpdateStatusHelper();

     $helper->updateMemberStatus($member_number);

    if (!$account || !$teller_account_info) {
        // Handle error if accounts are not found
        session()->flash('message_fail', 'Account not found.');

    }

    if($this->service == 'LOAN'){
        $account = AccountsModel::where('account_number', $this->accountSelected)->first();
        $savings_account_new_balance = (double)$account->balance - (double)$this->amount;
        $loan_sub_product = DB::table('loans')->where('loan_account_number',$this->accountSelected)->value('loan_sub_product');

        $loan_id = DB::table('loans')->where('loan_account_number',$this->accountSelected)->value('id');
        //$loan_id = DB::table('loans')->where('account_number',$this->accountSelected)->value('id');
        //dd($this->accountSelected);
        $product_account_id = DB::table('loan_sub_products')->where('sub_product_id',$loan_sub_product)->value('loan_product_account');
        $product_account_number = AccountsModel::where('id', $product_account_id)->value('account_number');
        $product_account_balance = AccountsModel::where('id', $product_account_id)->value('balance');
        $product_account_new_balance = $product_account_balance - (double)$this->amount;
        AccountsModel::where('account_number', $product_account_number)->update(['balance' => $product_account_new_balance]);
                $one = 0;
                $two =  (double)$this->amount;



                     // Record the transaction on the customer account
             general_ledger::create([
                 'record_on_account_number' => $product_account_number,
                 'record_on_account_number_balance' => $product_account_new_balance,
                 'sender_branch_id' => $institution_id,
                 'beneficiary_branch_id' => $institution_id,
                 'sender_product_id' => '0000',
                 'sender_sub_product_id' => '0000',
                 'beneficiary_product_id' => $account->product_number,
                 'beneficiary_sub_product_id' => $account->sub_product_number,
                 'sender_id' => '999999',
                 'beneficiary_id' => $this->member,
                 'sender_name' => 'Organization',
                 'beneficiary_name' => ClientsModel::where('client_number', $this->member)->value('first_name') . ' ' .
                     ClientsModel::where('client_number', $this->member)->value('middle_name') . ' ' .
                     ClientsModel::where('client_number', $this->member)->value('last_name'),
                 'sender_account_number' => '0000',
                 'beneficiary_account_number' => $this->accountSelected,
                 'transaction_type' => 'IFT',
                 'sender_account_currency_type' => 'TZS',
                 'beneficiary_account_currency_type' => 'TZS',
                 'narration' => $this->notes,
                 'credit' => $one,
                 'debit' => $two,
                 'reference_number' => $reference_number,
                 'trans_status' => 'Successful',
                 'trans_status_description' => 'Successful',
                 'swift_code' => null,
                 'destination_bank_name' => null,
                 'destination_bank_number' => null,
                 'payment_status' => 'Successful',
                 'recon_status' => 'Pending',
                 'institution_id' => $institution_id,
             ]);

             $this->update_repayment($loan_id, (double)$this->amount);

    }else{
        $savings_account_new_balance = (double)$account->balance + (double)$this->amount;
        $one = (double)$this->amount;
        $two = 0;
    }

    $teller_account_new_balance = (double)$teller_account_info->balance + (double)$this->amount;

    AccountsModel::where('account_number', $this->accountSelected)->update(['balance' => $savings_account_new_balance]);
    AccountsModel::where('account_number', $teller_account)->update(['balance' => $teller_account_new_balance]);






    // Record the transaction on the customer account
    general_ledger::create([
        'record_on_account_number' => $this->accountSelected,
        'record_on_account_number_balance' => $savings_account_new_balance,
        'sender_branch_id' => $institution_id,
        'beneficiary_branch_id' => $institution_id,
        'sender_product_id' => '0000',
        'sender_sub_product_id' => '0000',
        'beneficiary_product_id' => $account->product_number,
        'beneficiary_sub_product_id' => $account->sub_product_number,
        'sender_id' => '999999',
        'beneficiary_id' => $this->member,
        'sender_name' => 'Organization',
        'beneficiary_name' => ClientsModel::where('client_number', $this->member)->value('first_name') . ' ' .
                              ClientsModel::where('client_number', $this->member)->value('middle_name') . ' ' .
                              ClientsModel::where('client_number', $this->member)->value('last_name'),
        'sender_account_number' => '0000',
        'beneficiary_account_number' => $this->accountSelected,
        'transaction_type' => 'IFT',
        'sender_account_currency_type' => 'TZS',
        'beneficiary_account_currency_type' => 'TZS',
        'narration' => $this->notes,
        'credit' => $one,
        'debit' => $two,
        'reference_number' => $reference_number,
        'trans_status' => 'Successful',
        'trans_status_description' => 'Successful',
        'swift_code' => null,
        'destination_bank_name' => null,
        'destination_bank_number' => null,
        'payment_status' => 'Successful',
        'recon_status' => 'Pending',
        'institution_id' => $institution_id,
    ]);

    // Record the transaction on the teller account
    general_ledger::create([
        'record_on_account_number' => $teller_account,
        'record_on_account_number_balance' => $teller_account_new_balance,
        'sender_branch_id' => $institution_id,
        'beneficiary_branch_id' => $institution_id,
        'sender_product_id' => '0000',
        'sender_sub_product_id' => '0000',
        'beneficiary_product_id' => $teller_account_info->product_number,
        'beneficiary_sub_product_id' => $teller_account_info->sub_product_number,
        'sender_id' => '000',
        'beneficiary_id' => $teller_account_info->institution_number,
        'sender_name' => 'Organization',
        'beneficiary_name' => 'Organization',
        'sender_account_number' => '0000',
        'beneficiary_account_number' => $teller_account,
        'transaction_type' => 'IFT',
        'sender_account_currency_type' => 'TZS',
        'beneficiary_account_currency_type' => 'TZS',
        'narration' => $this->notes,
        'credit' => (double)$this->amount,
        'debit' => 0,
        'reference_number' => $reference_number,
        'trans_status' => 'Successful',
        'trans_status_description' => 'Successful',
        'swift_code' => null,
        'destination_bank_name' => null,
        'destination_bank_number' => null,
        'payment_status' => 'Successful',
        'recon_status' => 'Pending',
        'institution_id' => $institution_id,
    ]);
});


// Set success message for the user
Session::flash('message1', 'Funds deposited successfully!');
Session::flash('alert-class', 'alert-success');

    }

    public function withDrawDeposits()
    {
        if($this->payment_method=="BANK") {
            $this->validate(['amount1' => 'required', 'payment_method' => 'required', 'bank1' => 'required', 'bank_account_number' => 'required']);
        }
        else{
            $this->validate(['amount1' => 'required', 'payment_method' => 'required', 'bank1' => 'required']);

        }

        //$this->validate();
        // get bank  account number
        /////////////////////////////////////////////////////ON WITHDRAW   DEBIT TELLER ACCOUNT AND  CUSTOMER ACCOUNT   //////////////////////////////
        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');
        $teller_account=DB::table('accounts')->where('id',$get_account_id)->first();
        //// customer balance
        $customer_account=DB::table('accounts')->where('account_number',$this->accountSelected1)->value('balance');



        //////////////////mirror account //////////////////////
        $mirror_account=DB::table('accounts')->where('id',$this->bank1)->value('account_number');

        // mirror_accoun new balance
        $new_balance=(double)DB::table('accounts')->where('account_number',$mirror_account)->value('balance')+(double)$this->amount1;

        DB::table('accounts')->where('account_number',$mirror_account)->update(['balance'=>$new_balance]);


        //////////////////// CUSTOMER ACCOUNT DEBIT
        if($teller_account->balance > (double)$this->amount1  &&  $customer_account > (double)$this->amount1){

            ///////////////////// teller account number
            $teller_account_number = $teller_account->account_number;

            ////////////////////////   Teller account  update account
            $new_teller_account_balance=(double)($teller_account->balance -(double)$this->amount1);

            DB::table('accounts')->where('account_number',$teller_account_number)->update(['balance'=>$new_teller_account_balance]);
            /////////////////  CUSTOMER DETAILS (debit account)  //////////////////
            $customer_new_balance=  (double)AccountsModel::where('account_number', $this->accountSelected1)->value('balance') - (double)$this->amount1;

            // update customer accounts
            DB::table('accounts')->where('account_number',$this->accountSelected1)->update(['balance'=>$customer_new_balance]);


            $reference_number = (string)time();
            $this->transaction_reference_number=$reference_number;


            //DEBIT RECORD TO TELLER
            general_ledger::create([
                'record_on_account_number' =>$teller_account_number,
                'record_on_account_number_balance' => $new_teller_account_balance,
                'sender_branch_id' => auth()->user()->branch,
                'beneficiary_branch_id' => auth()->user()->branch,
                'sender_product_id' => AccountsModel::where('account_number',$teller_account_number )->value('product_number'),
                'sender_sub_product_id' => AccountsModel::where('account_number', $teller_account_number)->value('sub_product_number'),
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('sub_product_number'),
                'sender_id' => $this->member1,
                'beneficiary_id' => '999999',
                'sender_name' => ClientsModel::where('client_number', $this->member1)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member1)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member1)->value('last_name'),
                'beneficiary_name' =>'Organization',
                'sender_account_number' =>$this->accountSelected1,
                'beneficiary_account_number' => $this->accountSelected1,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' => $this->notes1,
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
                'institution_id'=> auth()->user()->institution_id,
            ]);



            //CREDIT  CUSTOMER ACCOUNT
            general_ledger::create([
                'record_on_account_number' => $this->accountSelected1,
                'record_on_account_number_balance' => $customer_new_balance,
                'sender_branch_id' =>  auth()->user()->branch,
                'beneficiary_branch_id' =>  auth()->user()->branch,
                'sender_product_id' => AccountsModel::where('account_number',$teller_account_number )->value('product_number'),
                'sender_sub_product_id' => AccountsModel::where('account_number', $teller_account_number)->value('sub_product_number'),
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('sub_product_number'),
                'sender_id' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
                'beneficiary_id' => $this->member1,
                'sender_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
                'beneficiary_name' => ClientsModel::where('client_number', $this->member1)->value('first_name') . ' ' . ClientsModel::where('client_number', $this->member1)->value('middle_name') . ' ' . ClientsModel::where('client_number', $this->member1)->value('last_name'),
                'sender_account_number' => $this->bank1,
                'beneficiary_account_number' =>$this->accountSelected1,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' => $this->notes1,
                'credit' => (double)$this->amount1,
                'debit' => 0,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => null,
                'destination_bank_name' => null,
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
                'institution_id'=> auth()->user()->institution_id
            ]);


        }
        else{
            session()->flash('message_fail1','sorry you dont have enough funds');
        }







    }



    public function resetData1()
    {
        // Reset the values of properties used in the function
        $this->bank1 = null;
        $this->accountSelected1 = null;
        $this->amount1 = null;
        $this->member1 = null;
        $this->notes1 = null;
        $this->reference_number1 = null;
        $this->payment_method=null;
        $this->bank_account_number=null;
    }

    public function dryDeposit($phone_number,$amount,$narration,$account_id)
    {
        //dd($phone_number,$amount,$narration,$account_id);
        ////////////////////////////////////DEBIT ON MIRROR ACCOUNT//////////////////////////////////////
        // get mirror account from which deposition
        $get_account = AccountsModel::where('id',$account_id)->first();
        ///

        $mirror_account=$get_account->account_number;
        // balance get;
        $mirror_account_balance=$get_account->balance;
        // new mirror account balance
        $mirror_account_new_balance=$mirror_account_balance-$amount;
        //update teller account
        DB::table('accounts')->where('id',$account_id)->update(['balance'=>$mirror_account_new_balance]);


        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING REGISTRATION FEE/////////////////////////////////
        $account_details=  DB::table('accounts')
            //->where('institution_number',auth()->user()->institution_id)
            ->where('sub_category_code',4209)->first();
        // get account balance

        $registration_fee_account_balance=$account_details->balance ? : 0;
        // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
        DB::table('accounts')
            //->where('institution_number',auth()->user()->institution_id)
            ->where('sub_category_code',4209)
            ->update(['balance'=>$registration_fee_account_new_balance]);


        $reference_number = time();

        $institution_id=auth()->user()->institution_id;


        try {
        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $mirror_account,
            'record_on_account_number_balance'=> $mirror_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('nida_number',$phone_number)->value('id'),
            'sender_name'=>DB::table('accounts')->where('account_number',$mirror_account )->value('account_name') ,
            'beneficiary_name'=>DB::table('accounts')->where('account_number',$account_details->account_number )->value('account_name') ,
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=> $account_details->account_number,
            'transaction_type'=> 'DEPOSIT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> $narration,
            'credit'=> 0,
            'debit'=> (double)$amount,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        } catch (Exception $e) {
            dd($e->getMessage());
            // Handle the exception here
            // For example, log the error or display an error message
            // Log::error($e->getMessage());
            // return response()->json(['error' => $e->getMessage()], 500);
        }

        //CREDIT RECORD LOAN ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $account_details->account_number,
            'record_on_account_number_balance'=> $registration_fee_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=> auth()->user()->branch,
            'sender_product_id'=>  null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> (int)auth()->user()->id,
            'beneficiary_id'=> (int)DB::table('pending_registrations')->where('nida_number',$phone_number)->value('id'),
            'sender_name'=> (int)AccountsModel::where('account_number',$mirror_account )->value('account_name'),
            'beneficiary_name'=> (int)AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
            'sender_account_number'=> (int)$mirror_account,
            'beneficiary_account_number'=>(int)$account_details->account_number ,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=>$narration,
            'credit'=> $amount,
            'debit'=> 0,
            'reference_number'=> (int)$reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
        $this->resetData();



        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done

    public function mandatoryShareDepositNewMember($phone_number,$amount,$account_id)
    {

        ////////////////////////////////////DEBIT ON MIRROR ACCOUNT//////////////////////////////////////
        // get mirror account from which deposition
        $get_account = AccountsModel::where('id',$account_id)->first();
        ///
        $mirror_account=$get_account->account_number;
        // balance get;
        $mirror_account_balance=$get_account->balance;
        // new mirror account balance
        $mirror_account_new_balance=$mirror_account_balance-$amount;
        //update mirror balance
        DB::table('accounts')->where('id',$account_id)->update(['balance'=>$mirror_account_new_balance]);


        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING REGISTRATION FEE/////////////////////////////////
        $account_details=  DB::table('accounts')
            ->where('institution_number',auth()->user()->institution_id)
            ->where('sub_category_code',3009)->first();
        // get account balance
        $registration_fee_account_balance=$account_details->balance;
        // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
        DB::table('accounts')->where('institution_number',auth()->user()->institution_id)
            ->where('sub_category_code',3009)
            ->update(['balance'=>$registration_fee_account_new_balance]);


        $reference_number = time();

        $institution_id=auth()->user()->institution_id;

        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $mirror_account,
            'record_on_account_number_balance'=> $mirror_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('nida_number',$phone_number)->value('id'),
            'sender_name'=>DB::table('accounts')->where('account_number',$mirror_account )->value('account_name') ,
            'beneficiary_name'=>DB::table('accounts')->where('account_number',$account_details->account_number )->value('account_name') ,
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=> $account_details->account_number,
            'transaction_type'=> 'DEPOSIT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'new member pay for mandatory shares',
            'credit'=> 0,
            'debit'=> (double)$amount,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD LOAN ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $account_details->account_number,
            'record_on_account_number_balance'=> $registration_fee_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=> auth()->user()->branch,
            'sender_product_id'=>  null,
            'sender_sub_product_id'=> null,
            'beneficiary_product_id'=> null,
            'beneficiary_sub_product_id'=> null,
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('nida_number',$phone_number)->value('id'),
            'sender_name'=> AccountsModel::where('account_number',$mirror_account )->value('account_name'),
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=> $account_details->account_number,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'new member pay for mandatory shares',
            'credit'=> $amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
        $this->resetData();



        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done



    public function mandatoryShareCashDepositNewMember($phone_number,$amount)
    {
        ////////////////////////////////////CREDIT TELLER ACCOUNT//////////////////////////////////////

        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');

        $get_account=DB::table('accounts')->where('id',$get_account_id)->first();


        $teller_account_number=$get_account->account_number;
        // balance get;
        $teller_balance=$get_account->balance;
        // new mirror account balance;
        $teller_new_balance=$teller_balance+$amount;
        //update mirror balance;

        DB::table('accounts')->where('id',$get_account_id)->update(['balance'=>$teller_new_balance]);


        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING REGISTRATION FEE/////////////////////////////////
        $account_details=DB::table('accounts')
            ->where('sub_category_code',3009)->first();
        // get account balance
        $registration_fee_account_balance=$account_details->balance;
        // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
        DB::table('accounts')
            ->where('sub_category_code',3009)
            ->update(['balance'=>$registration_fee_account_new_balance]);


        $reference_number = time();

        $institution_id='1';

        //CREDIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $teller_account_number,
            'record_on_account_number_balance'=> $teller_new_balance,
            'sender_branch_id'=> '0000',
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> '0000',
            'sender_sub_product_id'=>'0000',
            'beneficiary_product_id'=> DB::table('accounts')->where('account_number',$teller_account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> DB::table('accounts')->where('account_number',$teller_account_number)->value('sub_product_number'),
            'sender_id'=>'0',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->where('status','ACTIVE')->value('id'),
            'sender_name'=>'CASH' ,
            'beneficiary_name'=>DB::table('accounts')->where('account_number',$teller_account_number )->value('account_name') ,
            'sender_account_number'=> '0000',
            'beneficiary_account_number'=> $teller_account_number,
            'transaction_type'=> 'DEPOSIT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'new member pay for mandatory shares',
            'credit'=> (double)$amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD LOAN ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $account_details->account_number,
            'record_on_account_number_balance'=> $registration_fee_account_new_balance,
            'sender_branch_id'=>auth()->user()->branch,
            'beneficiary_branch_id'=>auth()->user()->branch,
            'sender_product_id'=>  '0000',
            'sender_sub_product_id'=> '0000',
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number') ? :null ,
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number') ? : null ,
            'sender_id'=> '0000',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->where('status','ACTIVE')->value('id') ? : null ,
            'sender_name'=> 'CASH',
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name') ? :null ,
            'sender_account_number'=> '0000',
            'beneficiary_account_number'=>$account_details->account_number ,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=>'new member pay for mandatory shares',
            'credit'=> $amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->resetData();
        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done


    public function cashRegistrationFees($phone_number,$amount,$account_id)
    {

        ////////////////////////////////////CREDIT ON TELLER SETTLEMENT  ACCOUNT//////////////////////////////////////

        //// get  teller account
        $get_account_id = DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->pluck('account_id')->toArray();
        $get_account=DB::table('accounts')->whereIn('id',$get_account_id)->first();
        ///
        $teller_account_number=$get_account->account_number;

        // balance get;
        $teller_balance=$get_account->balance;

        // new mirror account balance
        $teller_new_balance=$teller_balance+$amount;
        //update mirror balance
        DB::table('accounts')->where('account_number',$teller_account_number)->update(['balance'=>$teller_new_balance]);



        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING  CASH REGISTRATION FEE/////////////////////////////////
        $account_details=  DB::table('accounts')->where('major_category_code',4000)
            ->where('sub_category_code',4210)->first();


       ///   get account balance  ///////////////////////////////
        $registration_fee_account_balance=$account_details->balance;
        // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
        DB::table('accounts')
            ->where('major_category_code',4000)->where('sub_category_code',2560)
            ->update(['balance'=>$registration_fee_account_new_balance]);


        $reference_number = time();

        $institution_id=1;

        //CREDIT ON TELLER RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $teller_account_number,
            'record_on_account_number_balance'=> $teller_new_balance,
            'sender_branch_id'=>auth()->user()->branch,
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> '0000',
            'sender_sub_product_id'=> '0000',
            'beneficiary_product_id'=> DB::table('accounts')->where('account_number',$teller_account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> DB::table('accounts')->where('account_number',$teller_account_number)->value('sub_product_number'),
            'sender_id'=> '0000',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
            'sender_name'=>'CASH',
            'beneficiary_name'=>DB::table('accounts')->where('account_number',$teller_account_number )->value('account_name') ,
            'sender_account_number'=> '0000',
            'beneficiary_account_number'=> $teller_account_number,
            'transaction_type'=> 'DEPOSIT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=> 'new member pay for mandatory shares',
            'credit'=> (double)$amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

        //CREDIT RECORD LOAN ACCOUNT
        general_ledger::create([
            'record_on_account_number'=> $account_details->account_number,
            'record_on_account_number_balance'=> $registration_fee_account_new_balance,
            'sender_branch_id'=>auth()->user()->branch,
            'beneficiary_branch_id'=> auth()->user()->branch,
            'sender_product_id'=> '0000',
            'sender_sub_product_id'=>'0000',
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number') ? :00000,
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number') ? : 00000,
            'sender_id'=>'0000',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
            'sender_name'=>'CASH',
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
            'sender_account_number'=> 0000,
            'beneficiary_account_number'=>$account_details->account_number ,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=>'new member pay for mandatory shares',
            'credit'=> (double)$amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> null,
            'destination_bank_name'=> null,
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->resetData();
        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done

    public function resetData()
    {
        $this->member = '';
        $this->product = '';
        $this->accountSelected = '';
        $this->amount = '';
        $this->account_number = '';
        $this->notes = '';
        $this->bank = '';
        $this->reference_number = '';


    }



    public function push( $reference_number,$record_on_account_number,$record_on_account_number_balance,
                          $sender_branch_id,$beneficiary_branch_id,$sender_product_id,$sender_sub_product_id,
                          $beneficiary_product_id,$beneficiary_sub_product_id,$sender_id,$beneficiary_id,
                          $sender_name,$beneficiary_name,$sender_account_number,$beneficiary_account_number,
                          $transaction_type,$sender_account_currency_type,$beneficiary_account_currency_type,
                          $narration,$credit,$debit,$running_balance,$trans_status,$trans_status_description,
                          $swift_code,$destination_bank_name,$destination_bank_number,$partner_bank,
                          $partner_bank_name,$partner_bank_account_number,$partner_bank_transaction_reference_number,
                          $payment_status

    )


    {
        //dd('ssss');
        // Define the data to be posted
        $data = [
            'transaction_id' => $reference_number,
            'request_datetime' => now()->toDateTimeString(),
            'response_datetime' => null,
            'request_url' => 'http://localhost:8080/transactions/', // Example endpoint URL
            'request_payload' => 'Your request payload data here',
            'response_payload' => null,
            'status' => 'suspect',
            'error_message' => null,
            'response_code' => null,
            'institution_id' => auth()->user()->institution_id,
            'record_on_account_number' => $record_on_account_number,
            'record_on_account_number_balance' => $record_on_account_number_balance,
            'sender_branch_id' => $sender_branch_id,
            'beneficiary_branch_id' => $beneficiary_branch_id,
            'sender_product_id' => $sender_product_id,
            'sender_sub_product_id' => $sender_sub_product_id,
            'beneficiary_product_id' => $beneficiary_product_id,
            'beneficiary_sub_product_id' => $beneficiary_sub_product_id,
            'sender_id' =>$sender_id ,
            'beneficiary_id' => $beneficiary_id,
            'sender_name' => $sender_name,
            'beneficiary_name' => $beneficiary_name,
            'sender_account_number' => $sender_account_number,
            'beneficiary_account_number' => $beneficiary_account_number,
            'transaction_type' =>$transaction_type ,
            'sender_account_currency_type' => $sender_account_currency_type,
            'beneficiary_account_currency_type' => $beneficiary_account_currency_type,
            'narration' => $narration,
            'credit' => $credit,
            'debit' => $debit,
            'running_balance' => $running_balance,
            'reference_number' => $reference_number,
            'trans_status' => $trans_status,
            'trans_status_description' => $trans_status_description,
            'swift_code' => $swift_code,
            'destination_bank_name' => $destination_bank_name,
            'destination_bank_number' => $destination_bank_number,
            'partner_bank' => $partner_bank,
            'partner_bank_name' => $partner_bank_name,
            'partner_bank_account_number' => $partner_bank_account_number,
            'partner_bank_transaction_reference_number' => $partner_bank_transaction_reference_number,
            'payment_status' => $payment_status,
            'recon_status' => 'PENDING',
        ];



// Send the HTTP POST request
        $response = Http::post($data['request_url'], $data);

// Check if the request was successful
        if ($response->successful()) {
            // Request was successful
            $responseData = $response->json(); // If expecting JSON response
            // Process response data...

            dd($responseData);
        } else {
            // Request failed
            $errorCode = $response->status(); // HTTP status code
            $errorMessage = $response->body(); // Error message from response

            dd($errorMessage);
            // Handle error...
        }
    }








    function update_repayment($loan_id, $amount)
    {
        // Fetch bank and account information once
        $cash_account = DB::table('accounts')->where('id', $this->bank)->value('sub_category_code');
        $loan_account_sub_category_code = AccountsModel::where('account_number', $this->accountSelected)->value('sub_category_code');
        $interest_account_number = DB::table('loans')->where('loan_account_number', $this->accountSelected)->value('interest_account_number');
        $interest_account_sub_category_code = AccountsModel::where('account_number', $interest_account_number)->value('sub_category_code');

        // Fetch all pending schedules for the given loan ID
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', $loan_id)
            ->whereIn('completion_status', ['ACTIVE','PENDING', 'PARTIAL'])
            ->orderBy('installment_date', 'asc')
            ->get();

        foreach ($schedules as $schedule) {
            // Initialize payment values
            $interest_payment = 0;
            $principal_payment = 0;

            if ($schedule->installment == 0) {
                continue; // Skip if installment is 0
            }

            // Pay off the interest first
            if ($amount >= $schedule->interest - $schedule->interest_payment) {
                $interest_payment = $schedule->interest - $schedule->interest_payment;
                $amount -= $interest_payment;
            } else {
                $interest_payment = $amount;
                $amount = 0;
            }
            $schedule->interest_payment += $interest_payment;

            // Pay off the principal next
            if ($amount > 0) {
                if ($amount >= $schedule->principle - $schedule->principle_payment) {
                    $principal_payment = $schedule->principle - $schedule->principle_payment;
                    $amount -= $principal_payment;
                } else {
                    $principal_payment = $amount;
                    $amount = 0;
                }
                $schedule->principle_payment += $principal_payment;
            }

            // Calculate total payment made
            $total_payment = $schedule->interest_payment + $schedule->principle_payment;



            // Determine the completion status
            //$completion_status = $total_payment >= $schedule->installment ? 'PAID' : 'PARTIAL';

            $completion_status = floor($total_payment * 100) / 100 >= floor($schedule->installment * 100) / 100 ? 'PAID' : 'PARTIAL';



            // Update the schedule record in the database
            DB::table('loans_schedules')
                ->where('id', $schedule->id)
                ->update([
                    'interest_payment' => $schedule->interest_payment,
                    'principle_payment' => $schedule->principle_payment,
                    'payment' => $total_payment,
                    'completion_status' => $completion_status,
                    'updated_at' => now()
                ]);

            // Process transactions for repayments
            $this->processTransaction($loan_account_sub_category_code, $cash_account, $schedule->principle_payment, 'Loan Principal Repayment - Loan ID : '.$loan_id);
            $this->processTransaction($interest_account_sub_category_code, $cash_account, $schedule->interest_payment, 'Loan Interest Repayment - Loan ID : '.$loan_id);

            // If the remaining amount is exhausted, break out of the loop
            if ($amount <= 0) {
                break;
            }
        }

        // Check if all schedules are marked as "PAID" and set loan to "CLOSED" if true
        $remaining_schedules = DB::table('loans_schedules')
            ->where('loan_id', $loan_id)
            ->where('completion_status', '!=', 'PAID')
            ->count();

        if ($remaining_schedules === 0) {
            DB::table('loans')->where('id', $loan_id)->update(['status' => 'CLOSED']);
        }

        $this->resetData();
        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');
    }


    protected function processTransaction($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix)
    {
        // Check if the amount is null, and set it to 0 if true
        if (is_null($amount)) {
            $amount = 0;
        }

        $narration = $narrationSuffix;
        $debit_account = AccountsModel::where('sub_category_code', $debitAccountCode)->first();
        $credit_account = AccountsModel::where('sub_category_code', $creditAccountCode)->first();

        try {
            $transactionService = new TransactionPostingService();
            $data = [
                'first_account' => $credit_account,
                'second_account' => $debit_account,
                'amount' => $amount,
                'narration' => $narration,
            ];
            $response = $transactionService->postTransaction($data);
            session()->flash('message', json_encode($response));
        } catch (\Exception $e) {
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        }
    }



}

