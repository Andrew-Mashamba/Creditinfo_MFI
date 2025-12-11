<?php

namespace App\Jobs;

use App\Helper\GenerateAccountNumber;
use App\Mail\LoanProgress;
use App\Models\AccountsModel;
use App\Models\Employee;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use App\Models\LoansModel;
use App\Models\LoanStage;
use App\Models\User;
use App\Services\TransactionPostingService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Services\LoanRepaymentSchedule;

class ProcessAutomaticLoans
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    public ?string $memberNumber;
    public ?float $amount;
    public ?string $loanOfficer;
    public ?string $loanProduct;
    public ?string $payMethod;
    public ?string $accountNumber;
    public ?string $loanType2;
    public ?string $tenure;
    public array $schedule;
    public array $footer;
    public ?float $firstInterestAmount;
    public ?int $graceDays;
    public $charges;
    public $insurance_list;
    public $selectedLoan = null;
    public $bank_account = "1009";
    public $loan_sub_product;
    public $current_loan_id = null;
    /**
     * Create a new job instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }


    /**
     * Execute the job.
     * @throws \Exception
     */
    public function handle()
    {
        try {
            Log::info('Starting job execution', ['job' => __CLASS__]);

            // Extract data
            $memberNumber = $this->data['member_number1'] ?? null;
            $amount = $this->data['amount2'] ?? null;
            $loanOfficer = $this->data['loan_officer'] ?? null;
            $loanProduct = $this->data['loan_product'] ?? null;
            $payMethod = $this->data['pay_method'] ?? null;
            $accountNumber = $this->data['account_number'] ?? null;
            $loanType2 = $this->data['loan_type_2'] ?? null;

            $this->memberNumber = $memberNumber;
            $this->amount = $amount;
            $this->loanOfficer = $loanOfficer;
            $this->loanProduct = $loanProduct;
            $this->payMethod = $payMethod;
            $this->accountNumber = $accountNumber;
            $this->loanType2 = $loanType2;
            $this->tenure = $this->data['tenure'] ?? null;
            $this->loan_sub_product = $loanProduct;

            Log::debug('Extracted data', ['data' => $this->data]);

            if (!$memberNumber) {
                throw new \Exception('Member number is required.');
            }

            // Fetch charges
            $charges_id = DB::table('product_has_charges')
                ->where('product_id', $this->loanProduct)
                ->pluck('charge_id')->toArray();

            Log::debug('Fetched charge IDs', ['charge_ids' => $charges_id]);

            $this->charges = DB::table('chargeslist')->whereIn('id', $charges_id)->get();
            Log::debug('Fetched charges', ['charges' => $this->charges]);

            // Fetch insurance
            $insurance_id = DB::table('product_has_insurance')
                ->where('product_id', $this->loanProduct)
                ->pluck('insurance_id')->toArray();

            Log::debug('Fetched insurance IDs', ['insurance_ids' => $insurance_id]);

            $this->insurance_list = DB::table('insurancelist')->whereIn('id', $insurance_id)->get();
            Log::debug('Fetched insurance list', ['insurance_list' => $this->insurance_list]);

            // Check if the user exists
            $checkUser = DB::table('clients')->where('client_number', $memberNumber)->exists();
            Log::debug('User existence check', ['member_number' => $memberNumber, 'exists' => $checkUser]);

            if (!$checkUser) {
                throw new \Exception('Invalid member.');
            }

            // Perform database transaction
            DB::transaction(function () use ($memberNumber, $amount, $loanOfficer, $loanProduct, $payMethod, $accountNumber, $loanType2) {
                Log::info('Starting database transaction');

                // Fetch client data
                $client = DB::table('clients')->where('client_number', $memberNumber)->first();
                Log::debug('Fetched client data', ['client' => $client]);

                // Fetch product ID
                $productId = DB::table('loan_sub_products')->where('sub_product_id', $loanProduct)->value('id');
                Log::debug('Fetched product ID', ['product_id' => $productId]);

                // Get the initial stage and committee
                $initialStage = LoanStage::where('loan_product_id', $productId)
                        ->orderBy('created_at', 'asc')
                        ->first()
                        ->committee_id ?? null;

                Log::debug('Fetched initial stage', ['initial_stage' => $initialStage]);

                $stageValue = $initialStage ? DB::table('committees')->where('id', $initialStage)->value('name') : '';
                Log::debug('Fetched stage value', ['stage_value' => $stageValue]);

                // Create loan record
                $loanID = LoansModel::create([
                    'principle' => $amount,
                    'client_id' => $client->id,
                    'client_number' => $memberNumber,
                    'loan_sub_product' => $loanProduct,
                    'pay_method' => $payMethod,
                    'branch_id' => 2,
                    'supervisor_id' => $loanOfficer,
                    'loan_id' => time(),
                    'loan_type_2' => $loanType2,
                    'stage' => $stageValue,
                    'stage_id' => $initialStage,
                    'tenure' => DB::table('loan_sub_products')->where('sub_product_id', $loanProduct)->value('interest_tenure'),
                    'interest' => DB::table('loan_sub_products')->where('sub_product_id', $loanProduct)->value('interest_value'),
                    'status' => 'ONPROGRESS',
                ])->id;

                $this->current_loan_id = $loanID;

                Log::info('Loan record created', ['loan_id' => $loanID]);

                // Set the current loan ID in the session
                session(['currentloanID' => $loanID]);

                Log::debug('Set current loan ID in session', ['currentloanID' => $loanID]);

                // Optional email notifications
                $officerEmail = Employee::where('id', $loanOfficer)->value('email');
                $clientName = "{$client->first_name} {$client->middle_name} {$client->last_name}";
                Log::debug('Prepared email data', ['officer_email' => $officerEmail, 'client_name' => $clientName]);

                // Uncomment for email functionality
                //Mail::to($officerEmail)->send(new LoanProgress($officerEmail, $clientName, 'Loan application received and processing...'));
                //Mail::to($client->email)->send(new LoanProgress($officerEmail, $clientName, 'Loan application received and processing...'));

                // Process loan stages (implement this logic)
                $this->processLoanStages($loanID, $loanProduct, $client);
            });

            Log::info('Job execution completed successfully');
        } catch (\Exception $e) {
            Log::error('Error during job execution', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }


    /**
     * Process loan stages.
     *
     * @param int $loanID
     * @param int $loan_sub_product
     * @param object $client
     */

    private function processLoanStages($loanID, $loan_sub_product, $client)
    {
        try {
            // Log the start of the process
            \Log::info("Starting processLoanStages for Loan ID: {$loanID}", ['loan_sub_product' => $loan_sub_product]);

            // Fetch product ID and stages for the loan sub-product
            $product_id = DB::table('loan_sub_products')->where('sub_product_id', $loan_sub_product)->value('id');
            \Log::debug("Fetched product ID", ['product_id' => $product_id]);

            $loanStages = DB::table('loan_stages')->where('loan_product_id', $product_id)->get();
            \Log::debug("Fetched loan stages", ['loanStages' => $loanStages]);

            foreach ($loanStages as $index => $stage) {
                \Log::debug("Processing stage", ['stage' => $stage]);

                if ($stage->stage_type == 'Department') {
                    $name = DB::table('departments')
                        ->where('id', $stage->stage_id)
                        ->value('department_name');
                    \Log::debug("Fetched department name", ['department_name' => $name]);
                } elseif ($stage->stage_type == 'Committee') {
                    $name = DB::table('committees')
                        ->where('id', $stage->stage_id)
                        ->value('name');
                    \Log::debug("Fetched committee name", ['committee_name' => $name]);
                } else {
                    \Log::warning("Unknown stage type", ['stage_type' => $stage->stage_type]);
                    $name = 'Unknown';
                }

                if ($index == 0) {
                    $affectedRows = LoansModel::where('id', $loanID)
                        ->update(['status' => $name]);
                    \Log::info("Updated loan status for first stage", ['affectedRows' => $affectedRows, 'status' => $name]);
                }

                // Insert current loan stage
                $current_loans_stages_id = DB::table('current_loans_stages')->insertGetId([
                    //'loan_id' => $this->current_loan_id,
                    'loan_id' => $this->current_loan_id,
                    'product_id' => $loan_sub_product,
                    'stage_id' => $stage->stage_id,
                    'stage_type' => $stage->stage_type,
                    'stage_name' => $name,
                    'status' => 'PENDING',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                \Log::info("Inserted current loan stage", ['current_loans_stages_id' => $current_loans_stages_id]);

                // Handle approvers based on stage type (Department or Committee)
                $this->processStageApprovers($stage, $loanID, $current_loans_stages_id);
            }

        } catch (\Exception $e) {
            \Log::error("Error processing loan stages", [
                'loanID' => $loanID,
                'loan_sub_product' => $loan_sub_product,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        \Log::info("Finished processLoanStages", ['loanID' => $loanID]);
    }


    private function processStageApprovers($stage, $loanID, $current_loans_stages_id)
    {
        try {
            \Log::info('Processing stage approvers', ['stage' => $stage, 'loanID' => $loanID, 'current_loans_stages_id' => $current_loans_stages_id]);

            if ($stage->stage_type == 'Department') {
                try {
                    \Log::info('Fetching department name', ['stage_id' => $stage->stage_id]);

                    // Department stage: fetch department name and insert approver
                    $department_name = DB::table('departments')->where('id', $stage->stage_id)->value('department_name');

                    \Log::info('Inserting department approver', ['department_name' => $department_name]);

                    DB::table('approvers_of_loans_stages')->insert([
                        'loan_id' => $this->current_loan_id,
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
                } catch (\Exception $e) {
                    \Log::error('Error processing department stage approvers', ['error' => $e->getMessage()]);
                    throw $e;
                }
            } elseif ($stage->stage_type == 'Committee') {
                try {
                    \Log::info('Fetching committee members', ['stage_id' => $stage->stage_id]);

                    // Committee stage: fetch committee members and insert each as approvers
                    $committee_members = DB::table('committee_users')->where('committee_id', $stage->stage_id)->get();

                    \Log::info('Fetching committee name', ['stage_id' => $stage->stage_id]);
                    $name = DB::table('committees')
                        ->where('id', $stage->stage_id)
                        ->value('name');

                    foreach ($committee_members as $committee_member) {
                        try {
                            \Log::info('Processing committee member', ['user_id' => $committee_member->user_id]);

                            $user_name = User::where('id', $committee_member->user_id)->value('name');

                            DB::table('approvers_of_loans_stages')->insert([
                                'loan_id' => $this->current_loan_id,
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
                        } catch (\Exception $e) {
                            \Log::error('Error inserting committee member as approver', ['user_id' => $committee_member->user_id, 'error' => $e->getMessage()]);
                            throw $e;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing committee stage approvers', ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            \Log::info('Calling disburseLoan');
            $this->disburseLoan();
        } catch (\Exception $e) {
            \Log::error('Error in processStageApprovers', ['error' => $e->getMessage()]);
            throw $e;
        }
    }







    ///////////////////////////////////////////////////////////////////////////////////////


    /**
     * @throws \Exception
     */
    public function disburseLoan()
    {


        \Log::info('////////////////DISBURSEMENT START/////////////////');

        //dd($this->selectedLoan);
        $loanID = $this->current_loan_id;
        $loan = DB::table('loans')->find($loanID);
        $member = DB::table('clients')->where('client_number', $this->memberNumber)->first();
        $loanProduct = DB::table('loan_sub_products')->where('sub_product_id', $this->loan_sub_product)->first();






        // Set product account and narration
        $this->product_account = $loanProduct->loan_product_account;
        $branchCode = 2;
        $productCode = $this->loan_sub_product;
        $this->narration = "Loan disbursement to member: {$member->first_name} {$member->middle_name} {$member->last_name}";

        // Generate and assign loan account number
        $accountHelper = new GenerateAccountNumber();
        $accountNumber = $accountHelper->generate_account_number((int)$branchCode, (int)$productCode);
        //$this->addLoanAccount($accountNumber, $this->product_account);

        $category = 'asset_accounts';
        $loan_account_data = AccountsModel::where('sub_category_code', $this->product_account)->first();

        $idp = AccountsModel::create([
            'account_use' => 'external',
            'institution_number' => 1,
            'branch_number' => 2,
            'client_number' => $this->memberNumber,
            'product_number' => '5000',
            'sub_product_number' => $loanID,
            'major_category_code' => $loan_account_data->major_category_code,
            'category_code' => $loan_account_data->category_code,
            'sub_category_code' => $loan_account_data->sub_category_code,
            'account_name' => 'Loan Account : Loan ID '.$loanID,
            'account_number' => $accountNumber,
            'notes' => 'Interest Account : Loan ID '.$loanID,
            'account_level' => '3',
            'parent_account_number' => $loan_account_data->account_number,
            'type' => $category,
        ])->id;


        DB::table('loans')->where('id', $loanID)->update(['loan_account_number' => $accountNumber]);

        // Update loan account balance
//        $loanAccountBalance = AccountsModel::where('account_number', $accountNumber)->value('balance');
//        $loanAccountBalance += (int)$this->creditableAmount;

        // Generate loan repayment schedule
       // $this->createRepaymentSchedule($loanID, $loan, $loanProduct, $member);

        $this->setSchedule();

        $totalCharges = $this->calculateTotalCharges();
        $insuranceAmount = $this->calculateInsurance();

        ///////GENERATE ACCOUNTS////////
        ///
        ///

        // Generate next account number
        $interest_account_number = $accountHelper->generate_account_number(2, $productCode);

        $category = 'income_accounts';
        $loan_interest_account_data = AccountsModel::where('sub_category_code', $loanProduct->loan_interest_account)->first();

        $idp = AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => 1,
            'branch_number' => 2,
            'major_category_code' => $loan_interest_account_data->major_category_code,
            'category_code' => $loan_interest_account_data->category_code,
            'sub_category_code' => $loan_interest_account_data->sub_category_code,
            'account_name' => 'Interest Account : Loan ID '.$loanID,
            'account_number' => $interest_account_number,
            'notes' => 'Interest Account : Loan ID '.$loanID,
            'account_level' => '3',
            'parent_account_number' => $loan_interest_account_data->account_number,
            'type' => $category,
        ])->id;


        // Generate next account number
        $charge_account_number = $accountHelper->generate_account_number(2, $productCode);

        $category = 'income_accounts';
        $loan_charges_account_data = AccountsModel::where('sub_category_code', $loanProduct->loan_charges_account)->first();

        $idp = AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => 1,
            'branch_number' => 2,
            'major_category_code' => $loan_charges_account_data->major_category_code,
            'category_code' => $loan_charges_account_data->category_code,
            'sub_category_code' => $loan_charges_account_data->sub_category_code,
            'account_name' => 'Charge Account : Loan ID '.$loanID,
            'account_number' => $charge_account_number,
            'notes' => 'Charge Account : Loan ID '.$loanID,
            'account_level' => '3',
            'parent_account_number' => $loan_charges_account_data->account_number,
            'type' => $category,
        ])->id;


        // Generate next account number
        $insurance_account_number = $accountHelper->generate_account_number(2, $productCode);

        $category = 'capital_accounts';
        $loan_insurance_account_data = AccountsModel::where('sub_category_code', $loanProduct->loan_insurance_account)->first();

        $idp = AccountsModel::create([
            'account_use' => 'internal',
            'institution_number' => 1,
            'branch_number' => 2,
            'major_category_code' => $loan_insurance_account_data->major_category_code,
            'category_code' => $loan_insurance_account_data->category_code,
            'sub_category_code' => $loan_insurance_account_data->sub_category_code,
            'account_name' => 'Insurance Account : Loan ID '.$loanID,
            'account_number' => $insurance_account_number,
            'notes' => 'Insurance Account : Loan ID '.$loanID,
            'account_level' => '3',
            'parent_account_number' => $loan_insurance_account_data->account_number,
            'type' => $category,
        ])->id;








        // Process loan disbursement transactions
        $loan_account_sub_category_code =  AccountsModel::where('account_number', $accountNumber)->value('sub_category_code');
        $interest_account_sub_category_code =  AccountsModel::where('account_number', $interest_account_number)->value('sub_category_code');
        $charge_account_sub_category_code =  AccountsModel::where('account_number', $charge_account_number)->value('sub_category_code');
        $insurance_account_sub_category_code =  AccountsModel::where('account_number', $insurance_account_number)->value('sub_category_code');


        ///IF TOP UP
        $topUpAmount=0;
        if($this->selectedLoan){
            $loan_account_number =  DB::table('loans')->where('id', $loan->selectedLoan)->value('loan_account_number');

            $ClosedLoan =  DB::table('sub_accounts')->where('account_number', $loan_account_number)->first();

            $this->loan_account_sub_category_code=$ClosedLoan->sub_category_code;
            $this->loan_account_number2=$loan_account_number;

            //  dd($loan->loan_id,$ClosedLoan->balance);

            //dd($this->bank_account, $ClosedLoan->sub_category_code, $ClosedLoan->balance, 'Loan Closure');
            $topUpAmount=$ClosedLoan->balance;
            //$this->processTransaction( $ClosedLoan->sub_category_code,$this->bank_account, $ClosedLoan->balance, 'Loan Closure');

            $this->update_repayment($loan->selectedLoan,$ClosedLoan->balance);

            // dd('done');

        }else{
            //$this->update_repayment($loanID,$this->amount);
            $this->createRepaymentSchedule($loanID);
        }

        $this->processTransaction($loan_account_sub_category_code, $this->bank_account, $this->amount-$topUpAmount, 'Loan Principal');
        $this->processTransaction($interest_account_sub_category_code, $this->bank_account, $this->firstInterestAmount, 'First Interest');
        $this->processTransaction($charge_account_sub_category_code, $this->bank_account, $totalCharges, 'Loan Charges');

        $this->processTransaction($insurance_account_sub_category_code, $this->bank_account, $insuranceAmount, 'Insurance Premium');

        $this->ClosedLoanBalance = 0;


        $status = "ACTIVE";
        if( $this->loan_sub_product == "1041532"){
            $status = "LOAN OFFICER";
        }
        // Update loan status and finalize disbursement
        DB::table('loans')->where('id', $loanID)->update(
            [
                'status' => $status,
                //'status' => 'ACCOUNTING',
                'interest_account_number' => $interest_account_number,
                'charge_account_number' => $charge_account_number,
                'insurance_account_number' => $insurance_account_number,
            ]);
        //$this->resetProperties();
        session()->forget('currentloanID');

    }


// Method to process transactions with error handling
    protected function processTransaction($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix)
    {
        //dd($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix);
        $this->narration = "{$narrationSuffix} : Loan ID " . $this->current_loan_id;
        $this->debit_account = AccountsModel::where('sub_category_code', $debitAccountCode)->first();
        $this->credit_account = AccountsModel::where('sub_category_code', $creditAccountCode)->first();
        $this->amount = $amount;

        try {
            $transactionService = new TransactionPostingService();
            $data = [
                'first_account' => $this->credit_account,
                'second_account' => $this->debit_account,
                'amount' => $this->amount,
                'narration' => $this->narration,
            ];

            $response = $transactionService->postTransaction($data);
            session()->flash('message', json_encode($response));
        } catch (\Exception $e) {
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        }
    }



    private function calculateTotalCharges()
    {
        $totalCharges = 0;
        foreach ($this->charges as $charge) {
            $totalCharges += $this->calculateCharge($charge);
        }
        return $totalCharges;
    }

    private function calculateInsurance()
    {

        $totalInsurance = 0;

        foreach ($this->insurance_list as $insurance) {

            if ($insurance->calculating_type === "Fixed") {
                $insuranceAmount = $this->amount * 0.125/100 * $this->tenure; // Fixed charge
            } else {
                $insuranceAmount = $this->amount * 0.125/100 * $this->tenure; // Percentage-based charge
            }

            $totalInsurance += $insuranceAmount;

        }


        //return ($this->loan_amount * (($this->insurance->monthly_rate / 100) * $this->loan_tenure));
        return $totalInsurance;
    }


    function update_repayment($loan_id, $amount)
    {
        // Fetch bank and account information once
        $cash_account =  $this->bank_account; //DB::table('accounts')->where('id', $this->bank)->value('sub_category_code');
        $loan_account_sub_category_code = $this->loan_account_sub_category_code; // AccountsModel::where('account_number', $this->accountSelected)->value('sub_category_code');
        $interest_account_number = DB::table('loans')->where('loan_account_number', $this->loan_account_number2)->value('interest_account_number');
        $interest_account_sub_category_code = AccountsModel::where('account_number', $interest_account_number)->value('sub_category_code');


        // dd($interest_account_sub_category_code,$loan_account_sub_category_code  );

        // Fetch all pending schedules for the given loan ID
        $schedules = DB::table('loans_schedules')
            ->where('loan_id', $this->current_loan_id)
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
//            if ($amount >= $schedule->interest - $schedule->interest_payment) {
//                $interest_payment = $schedule->interest - $schedule->interest_payment;
//                $amount -= $interest_payment;
//            } else {
//                $interest_payment = $amount;
//                $amount = 0;
//            }
//            $schedule->interest_payment += $interest_payment;

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
                    'interest_payment' => 0,
                    'principle_payment' => $schedule->principle_payment,
                    'payment' => $total_payment,
                    'completion_status' => $completion_status,
                    'updated_at' => now()
                ]);

            // Process transactions for repayments
            // dd($loan_account_sub_category_code, $cash_account, $schedule->principle_payment);
            $this->processTransaction($loan_account_sub_category_code, $cash_account, $schedule->principle_payment, 'Loan Principal Repayment - Loan ID : $this->current_loan_id');
            //$this->processTransaction($interest_account_sub_category_code, $cash_account, $schedule->interest_payment, 'Loan Interest Repayment - Loan ID : '.$loan_id);

            // If the remaining amount is exhausted, break out of the loop
            if ($amount <= 0) {
                break;
            }
        }

        // Check if all schedules are marked as "PAID" and set loan to "CLOSED" if true
        $remaining_schedules = DB::table('loans_schedules')
            ->where('loan_id', $this->current_loan_id)
            ->where('completion_status', '!=', 'PAID')
            ->count();

        if ($remaining_schedules === 0) {
            DB::table('loans')->where('id', $this->current_loan_id)->update(['status' => 'CLOSED']);
        }

        //$this->resetData();
        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');
    }

    private function calculateCharge($charge)
    {

        //$chargeAmount = $charge['value']; // Fixed charge

        $chargeAmount = ($this->amount * 0.3 / 100);

        if($chargeAmount > 30000){
            $chargeAmount = 30000;
        }elseif($chargeAmount < 10000){
            $chargeAmount = 10000;
        }else{
            $chargeAmount = $chargeAmount;
        }

        return $chargeAmount;

    }

    public function setSchedule()
    {
        // Initialize Loan Schedule Service and generate schedule data
        $loanId = Session::get('currentloanID');
        $approvedTerm = $this->tenure;
        $approvedLoanValue = $this->amount;

        // Additional parameters for loan scheduling
        $member = null;
        $member = DB::table('clients')->where('client_number', $this->memberNumber)->first();
        $member_category = $member->member_category;
        $dayOfMonth = DB::table('member_categories')->where('id', $member_category)->value('repayment_date');
        //$dayOfMonth = "18";


        //$updatedPrinciple = (float)$approvedLoanValue - (float)$firstInstallmentInterestAmount;
        $updatedPrinciple = (float)$approvedLoanValue;

        $product = DB::table('loan_sub_products')->where('sub_product_id', $this->loanProduct)->first();

        $monthlyInterestRate = $product->interest_value/$product->interest_tenure;



        $repaymentSchedule = new LoanRepaymentSchedule($loanId, $approvedTerm, $updatedPrinciple, $dayOfMonth, $product->interest_value,$product->principle_grace_period,$member->member_category);

        // $disbursementDate = date('Y-m-d');  // Example disbursement date
        $disbursementDate = date('Y-m-d');
        // dd($disbursementDatex,$disbursementDate);

        $data = $repaymentSchedule->generateSchedule($disbursementDate);


        $this->schedule = $data['schedule'];
        $this->footer = $data['footer'];
        $graceData=$data['graceData'];
        $this->firstInterestAmount = $graceData[0]['balance'];
        $this->grace_days=$graceData[0]['days'];
    }


    /**
     * @throws \Exception
     */
    protected function createRepaymentSchedule($loanID)
    {
        //dd($loanID);
        try {

            $schedule = $this->schedule;
            $footer = $this->footer;

            // Validate schedule and footer data
            if (empty($schedule) || empty($footer)) {
                throw new \Exception('Schedule or footer data is missing.');
            }

            // Save each installment in the repayment schedule
            foreach ($schedule as $scheduleData) {
                $completion_status = 'ACTIVE';
                $payment = 0;
                $interest_payment = 0;

                if ($scheduleData['principal'] == 0) {
                    $completion_status = 'PAID';
                    $payment = $scheduleData['interest'];
                    $interest_payment = $scheduleData['interest'];
                }

                loans_schedules::create([
                    'loan_id' => $this->current_loan_id,
                    'installment' => $scheduleData['payment'],
                    'interest' => $scheduleData['interest'],
                    'principle' => $scheduleData['principal'],
                    'opening_balance' => $scheduleData['opening_balance'],
                    'closing_balance' => $scheduleData['closing_balance'],
                    'completion_status' => $completion_status,
                    'status' => 'ACTIVE',
                    'payment' => $payment,
                    'interest_payment' => $interest_payment,
                    'installment_date' => $scheduleData['installment_date'],
                ]);
            }

            // Save loan summary
            loans_summary::create([
                'loan_id' => $this->current_loan_id,
                'installment' => $footer['total_payment'],
                'interest' => $footer['total_interest'],
                'principle' => $footer['total_principal'],
                'balance' => $footer['final_closing_balance'],
                'completion_status' => 'ACTIVE',
                'status' => 'ACTIVE',
            ]);

            // Log success
            Log::info('Repayment schedule and summary created successfully.', ['loan_id' => $this->current_loan_id]);

        } catch (\Exception $e) {
            // Log error with details
            Log::error('Error creating repayment schedule.', [
                'loan_id' => $this->current_loan_id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Optionally, rethrow or handle the exception based on the application requirements
            throw $e;
        }
    }


}
