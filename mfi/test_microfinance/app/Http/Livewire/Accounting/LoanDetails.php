<?php

namespace App\Http\Livewire\Accounting;

use App\Services\TransactionPostingService;
use App\Services\AccountCreationService;
use App\Helper\GenerateAccountNumber;
use App\Jobs\FundsTransfer;
use App\Models\AccountsModel;
use App\Models\approvals;
use App\Models\Charges;
use App\Models\ClientsModel;
use App\Models\general_ledger;
use App\Models\Insurances;
use App\Models\Loan_sub_products;
use App\Models\loans_schedules;
use App\Models\loans_summary;
use App\Models\LoansModel;
use App\Models\MembersModel;

use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class LoanDetails extends Component
{


    public $photo, $futureInterest = false, $collateral_type, $collateral_description, $daily_sales, $loan, $collateral_value, $loan_sub_product;
    public $principle = 0, $member, $guarantor, $disbursement_account, $collection_account_loan_interest;
    public $collection_account_loan_principle, $collection_account_loan_charges, $collection_account_loan_penalties;
    public $principle_min_value, $principle_max_value, $min_term, $max_term, $interest_value;
    public $principle_grace_period, $interest_grace_period, $amortization_method;
    public $days_in_a_month = 30, $loan_id, $loan_account_number, $member_number, $topUpBoolena, $new_principle;
    public $interest = 0, $business_licence_number, $business_tin_number, $business_inventory, $cash_at_hand;
    public $cost_of_goods_sold, $operating_expenses, $monthly_taxes, $other_expenses, $monthly_sales;
    public $gross_profit, $table = [], $tablefooter = [], $recommended_tenure, $recommended_installment;
    public $totalAmount, $recommended = true,$monthlyInstallmentValue, $business_age, $bank1 = 123456, $available_funds;
    public $interest_method, $loan_is_settled=false, $approved_term = 12, $approved_loan_value = 0, $future_interests, $futureInsteresAmount, $valueAmmount, $net_profit, $status, $products;
    public $coverage;
    public $idx;
    public $sub_product_id;
    public $product, $account;
    public $charges;

    public $institution1;
    public $institutionAmount;

    public $institution2;
    public $institutionAmount2,$insurance_list=[];
    public $daysBetweenx = 0;


    ///////////////
    public $non_permanent_income_non_taxable = 0;
    public $non_permanent_income_taxable = 0;
    public $take_home = 0;
    public $totalInstallment = 0;
    public $tenure = 12;
    public $max_loan, $selectedContracts=[];
    public $x ;
    public $isPhysicalCollateral = false;
    public $account1;
    public $account2;
    public $creditableAmount;
    public $referenceNumber;
    public $bank_account;
    public $debit_account;
    public $credit_account;
    public $amount;
    public $narration;
    public $product_account,$grace_days;

    public $chargesxx;
    public $insurance;
    public $loan_amount;
    public $loan_tenure;
    public $grace_period_interest;
    //public $principle_grace_period;

    public $transactionService;

    public $data;

    public $firstInterestAmount;

    public $selectedLoan;
    public $ClosedLoanBalance;

    // Payment method properties for loan disbursement
    public $memberNbcAccount;
    public $memberAccountHolderName;
    public $memberPhoneNumber;
    public $memberMnoProvider;
    public $memberWalletHolderName;
    public $memberBankCode;
    public $memberBankAccountNumber;
    public $memberBankAccountHolderName;



    protected $listeners = [
        'loanIdSet' => '$refresh',
    ];



    public function toggleAmount($amount, $key)
    {


        // Check if the current contract is selected or not
        if (in_array($key, $this->selectedContracts)) {
            // If selected, remove it and decrement the total amount
            $this->totalAmount -= $amount;
            $this->selectedContracts = array_diff($this->selectedContracts, [$key]);

            DB::table('settled_loans')->where('loan_array_id', $key)
                ->where('loan_id',session('currentloanID'))
                ->update([
                    'is_selected'=>false
                ]);
        } else {
            // If not selected, add it and increment the total amount
            $this->totalAmount += $amount;
            $this->selectedContracts[] = $key;
            DB::table('settled_loans')->where('loan_array_id', $key)
                ->where('loan_id',session('currentloanID'))
                ->update([
                    'is_selected'=>true
                ]);

        }

    }

    public function boot(): void
    {
        try {
            $this->creditableAmount = 0;

            $milliseconds = round(microtime(true) * 1000);
            $this->referenceNumber = 'TXN' . $milliseconds;

            $loan = LoansModel::find(Session::get('currentloanID'));

            if ($loan) {
                $this->idx = $loan->id;
                $this->loan_id = $loan->loan_id;
                $this->member_number = $loan->member_number;
                $this->sub_product_id = $loan->loan_sub_product;
                $this->take_home = $loan->take_home;
                $this->loan_amount = $loan->principle;
                $this->loan_tenure = $loan->tenure;
                $this->principle_grace_period = $loan->principle_grace_period;

                // Assuming LoanSubProduct is an Eloquent model
                $this->product = Loan_sub_products::where('sub_product_id', $this->sub_product_id)->first();

                if ($this->product) {
                    // Assuming Charges is related to LoanSubProduct via a 'product_id' foreign key
                    $this->charges = Charges::where('product_id', $this->product->sub_product_id)->get();
                    $this->insurance = Insurances::where('category','loans')->first();
                }
            }

            $this->interest_method = "flat";
            
            // Only call these methods if loan exists
            if ($loan) {
                $this->loadLoanDetails();
                $this->loadProductDetails();
                $this->loadMemberDetails();
                $this->receiveData();

                // Get data for loan_array_id = 1
                $settlement1 = DB::table('settled_loans')
                    ->where('loan_id', session('currentloanID'))
                    ->where('loan_array_id', 1)
                    ->first();

                if ($settlement1) {
                    $this->institution1 = $settlement1->institution;
                    $this->institutionAmount = $settlement1->amount;
                    $this->account1 = $settlement1->account;
                }

                // Get data for loan_array_id = 2
                $settlement2 = DB::table('settled_loans')
                    ->where('loan_id', session('currentloanID'))
                    ->where('loan_array_id', 2)
                    ->first();

                if ($settlement2) {
                    $this->institution2 = $settlement2->institution;
                    $this->institutionAmount2 = $settlement2->amount;
                    $this->account2 = $settlement2->account;
                }

                // Calculate total amount for buybacks
                $this->calculateTotal();
            }
            
        } catch (\Exception $e) {
            Log::error('Error in LoanDetails boot method: ' . $e->getMessage());
            // Initialize with default values to prevent errors
            $this->charges = collect();
            $this->insurance_list = collect();
            $this->product = null;
        }
    }




    public function updated($fieldName, $value) {
        //dd($fieldName, $value);
        $this->updateFieldInDatabase($fieldName, $value);
    }

    public function updateFieldInDatabase($fieldName, $value) {
        $model = LoansModel::find(Session::get('currentloanID'));
        $model->$fieldName = $value; // Update the field dynamically
        $model->save();
    }



    // Calculate the first interest amount of the first installment
    // Calculate the first interest amount of the first installment
    public function calculateFirstInterestAmount($principal, $monthlyInterestRate, $dayOfTheMonth) {
        // Get the current date (disbursement date)
        $disbursementDate = new DateTime(); // current date

        // Clone the current date to calculate the next drawdown date
        $nextDrawdownDate = clone $disbursementDate;

        // Set the day of the month for the drawdown date in the current month
        $nextDrawdownDate->setDate($disbursementDate->format('Y'), $disbursementDate->format('m'), $dayOfTheMonth);

        // Check if today's date equals the drawdown date, if yes, set daysBetween to 0
        if ($disbursementDate->format('Y-m-d') === $nextDrawdownDate->format('Y-m-d')) {
            $daysBetween = 0;
        } else {
            // If the drawdown date for this month has already passed, move to the next month
            if ($disbursementDate > $nextDrawdownDate) {
                $nextDrawdownDate->modify('first day of next month');
                $nextDrawdownDate->setDate($nextDrawdownDate->format('Y'), $nextDrawdownDate->format('m'), $dayOfTheMonth);
            }

            // Calculate the number of days between the disbursement date and the next drawdown date
            $daysBetween = $disbursementDate->diff($nextDrawdownDate)->days;
        }

        // Store the days between for debugging or further use
        $this->daysBetweenx = $daysBetween;

        // Get the number of days in the current month
        $daysInMonth = (int) $disbursementDate->format('t');

        // Calculate the daily interest rate based on the monthly interest rate
        $dailyInterestRate = $monthlyInterestRate / $daysInMonth;

        // Calculate the interest accrued for the days between
        $interestAccrued = $principal * $dailyInterestRate * $daysBetween;

        return $interestAccrued;
    }




    public function calculateTotal()
    {
        // Sum the two amounts
        $this->totalAmount = ($this->institutionAmount ?? 0) + ($this->institutionAmount2 ?? 0);
    }

    public function setSettlement()
    {
        DB::table('settled_loans')->updateOrInsert(
            [
                'loan_id' => session('currentloanID'),
                'loan_array_id' => 1
            ],
            [
                'amount' => $this->institutionAmount,
                'institution' => $this->institution1,
                'account' => $this->account1,
            ]
        );

        // Recalculate the total after setting the amount
        $this->calculateTotal();
    }

    public function setSettlement2()
    {
        DB::table('settled_loans')->updateOrInsert(
            [
                'loan_id' => session('currentloanID'),
                'loan_array_id' => 2
            ],
            [
                'amount' => $this->institutionAmount2,
                'institution' => $this->institution2,
                'account' => $this->account2,
            ]
        );

        // Recalculate the total after setting the amount
        $this->calculateTotal();
    }




    public function actionBtns($x)
    {
        switch ($x) {
            case 1:
                $this->recommended = false;
                $this->receiveData();
                break;
            case 2:
                $this->recommended = true;
                break;
            case 3:
                $this->commit();
                break;
            case 4:
                $this->approve();
                break;
            case 5:
                $this->reject();
                break;
            case 6:
                $this->disburse();
                break;
            case 7:
                $this->receiveData();
                break;
            case 33:
                $this->topUpBoolena = true;
                $this->topUp();
                break;
            case 44:
                $this->restructure();
                break;
            case 45:
                $this->disburse();
                break;
            case 55:
                $this->futureInterest = true;
                $this->closeLoan();
                break;
        }
    }

    public function receiveData()
    {
        $this->generateSchedule((float)$this->principle, (float)$this->interest, (float)$this->tenure);
    }

    private function loadLoanDetails(): void
    {
        $this->loan = LoansModel::find(Session::get('currentloanID'));
        if ($this->loan) {
            $this->fill($this->loan->toArray());

            //$this->collateral_value;
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
                $this->isPhysicalCollateral = true;
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

    private function loadMemberDetails(): void
    {
        $this->guarantor = MembersModel::where('member_number', $this->guarantor)->first();
        $this->member = MembersModel::where('member_number', $this->member_number)->first();
    }



    function sendToException(){

        $data = [

            'loan_type_3'  => "Exception",
        ];

        // Check if stage_id is numeric
        LoansModel::where('id', Session::get('currentloanID'))->update($data);
        Session::flash('loan_commit', 'The loan has been committed!');
        Session::flash('alert-class', 'alert-success');
        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');

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


    function approveLoan() {
        // Retrieve the loan and its current status
        $loan = LoansModel::find(Session::get('currentloanID'));
        $status = $loan->status;
        LoansModel::where('id', Session::get('currentloanID'))->update([
            'grace_days'=>$this->grace_days

        ]);

        $currentLoanID = Session::get('currentloanID');

        // Fetch all loan stages for the current loan ID
        $current_loans_stages = DB::table('current_loans_stages')
            ->where('loan_id', $currentLoanID)
            ->get();

        // Iterate through each loan stage
        foreach ($current_loans_stages as $stage) {
            // Check if the current stage's status matches the loan's status
            if ($status == $stage->stage_name) {
                // Get the current stage ID
                $currentStageID = $stage->id;

                // Fetch the next stage after the current one
                $nextStage = DB::table('current_loans_stages')
                    ->where('loan_id', $currentLoanID)
                    ->where('id', '>', $currentStageID) // Find the next stage with a greater ID
                    ->orderBy('id', 'asc') // Ensure the next stage is the one with the next higher ID
                    ->first();

                // Handle missing next stage (loan might be completed)
                if (!$nextStage) {

                    if ($stage->stage_type == 'Committee') {

                        // Fetch all stages for the current loan and check if all are approved
                        $allStages = DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->get();

                        $approvedStages = DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('status', 'APPROVED')
                            ->get();

                        DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('user_id', auth()->user()->id) // Example approver ID
                            ->update([
                                'status' => 'APPROVED'
                            ]);

                        if ($allStages->count() == ($approvedStages->count() + 1)) {
                            LoansModel::where('id', $currentLoanID)
                                ->update(['status' => 'ACCOUNTING']);
                        }else{

                        }
                    }else{
                        //dd('hh');
                        DB::table('approvers_of_loans_stages')
                            ->where('loan_id', $currentLoanID)
                            ->where('stage_name', $status)
                            ->where('user_id', null) // Example approver ID
                            ->update([
                                'status' => 'APPROVED',
                                'user_id' => auth()->user()->id,
                                'user_name' => auth()->user()->name

                            ]);

                        LoansModel::where('id', $currentLoanID)
                            ->update(['status' => 'ACCOUNTING']); // Mark loan as completed
                        Session::flash('loan_commit', 'Loan has been completed!');
                        Session::flash('alert-class', 'alert-success');
                    }




                    return;
                }

                // Get the next stage name
                $nextStageName = $nextStage->stage_name;

                // Handle 'Committee' stage type
                if ($stage->stage_type == 'Committee') {
                    // Update approval status for the current stage
                    DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('user_id', auth()->user()->id) // Example approver ID
                        ->update([
                            'status' => 'APPROVED'
                        ]);

                    // Fetch all stages for the current loan and check if all are approved
                    $allStages = DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->get();

                    $approvedStages = DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('status', 'APPROVED')
                        ->get();

                    // If all stages are approved, update loan status to the next stage
                    if ($allStages->count() == $approvedStages->count()) {
                        LoansModel::where('id', $currentLoanID)
                            ->update(['status' => $nextStageName]);
                    } else {
                        // If not all are approved, handle the case as necessary
                        // You could flash a message or take another action
                        Session::flash('loan_commit', 'Loan not fully approved yet.');
                        Session::flash('alert-class', 'alert-warning');
                    }
                } else {
                    // Handle non-Committee stages: approve stage and update loan status
                    DB::table('approvers_of_loans_stages')
                        ->where('loan_id', $currentLoanID)
                        ->where('stage_name', $status)
                        ->where('user_id', null) // Find approver-less stage
                        ->update([
                            'status' => 'APPROVED',
                            'user_id' => auth()->user()->id,
                            'user_name' => auth()->user()->name
                        ]);

                    // Move the loan to the next stage
                    LoansModel::where('id', $currentLoanID)
                        ->update(['status' => $nextStageName]);
                }

                // Flash success message and emit event
                Session::flash('loan_commit', 'The loan has been committed!');
                Session::flash('alert-class', 'alert-success');
                $this->emit('currentloanID');
            }
        }
    }



    public function approve()
    {


        // CREATE LOAN ACCOUNT
        $loan =  LoansModel::where('id', session()->get('currentloanID'))->first();
        LoansModel::where('id', Session::get('currentloanID'))->update([
            'grace_days'=>$this->grace_days

        ]);

        // $client_email = MembersModel::where('client_number', $loan->member_number)->first();
        // // $client_name = $client_email->first_name . ' ' . $client_email->middle_name . ' ' . $client_email->last_name;
        // // $officer_phone_number = Employee::where('id', $client_email->loan_officer)->value('email');

        //        Mail::to($client_email->email)->send(new LoanProgress($officer_phone_number,$client_name,'Your loan has been approved! We are now finalizing the disbursement process'));
        if (LoansModel::where('id', session()->get('currentloanID'))->value('loan_status') == "RESTRUCTURED") {

            loans_schedules::where('loan_id', $loan->restructure_loanId)->where('completion_status', 'ACTIVE')->update([
                'completion_status' => 'CLOSED',
                'status' => 'CLOSED'
            ]);


            //  LoansModel::where('id',session()->get('currentloanID'))->update(['status'=>"CLOSED"]);
            // source account number

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
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
                // $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }





            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => 'AWAITING DISBURSEMENT',
                //
            ]);
        } elseif (LoansModel::where('id', session()->get('currentloanID'))->value('loan_status') == "TOPUP") {
            // top up process here  TOPUP

            $loanValues = LoansModel::where('id', session()->get('currentloanID'))->where('loan_status', 'TOPUP')->first();


            //principle
            $prev_loan = $loanValues->restructure_loanId;
            // close loan
            LoansModel::where('loan_id', $loanValues->restructure_loanId)->update(['status' => "CLOSED"]);

            // close installment
            loans_schedules::where('loan_id', $prev_loan)->update(['completion_status' => 'CLOSED']);

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
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
                //   $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }



            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => 'AWAITING DISBURSEMENT',
                //
            ]);





            Session::flash('loan_commit', 'The loan has been Approved!');
            Session::flash('alert-class', 'alert-success');

            Session::put('currentloanID', null);
            Session::put('currentloanClient', null);
            $this->emit('currentloanID');
        } else {

            $next_due_date = Carbon::now()->toDateTimeString();

            foreach ($this->table as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
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
                //  $product->save();
            }

            foreach ($this->tablefooter as $installment) {
                $next_due_date = date('Y-m-d', strtotime($next_due_date . ' +30 days'));
                $product = new loans_summary;
                $product->loan_id = $loan->loan_id;
                $product->installment = $installment['Payment'];
                $product->interest = $installment['Interest'];
                $product->principle = $installment['Principle'];
                $product->balance = $installment['balance'];
                $product->bank_account_number = $loan->bank1;
                $product->completion_status = "ACTIVE";
                $product->status = "ACTIVE";
                //   $product->save();
            }

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
                $status = 'ACTIVE';
            }

            LoansModel::where('id', Session::get('currentloanID'))->update([
                'status' => $status

            ]);
        }

        Session::flash('loan_commit', 'The loan has been Approved!');
        Session::flash('alert-class', 'alert-success');

        Session::put('currentloanID', null);
        Session::put('currentloanClient', null);
        $this->emit('currentloanID');
    }




    public function setSchedule($x){
        $this->data=$x;
    }




public $loan_account_sub_category_code,$loan_account_number2;

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
            $this->processTransaction($loan_account_sub_category_code, $cash_account, $schedule->principle_payment, 'Loan Principal Repayment - Loan ID : '.$loan_id);
            //$this->processTransaction($interest_account_sub_category_code, $cash_account, $schedule->interest_payment, 'Loan Interest Repayment - Loan ID : '.$loan_id);

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

        //$this->resetData();
        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');
    }






    public function disburseLoan($payMethod, $loanType, $productCode)
    {
        $startTime = microtime(true);
        $loanID = null;
        $memberName = null;
        
        try {
            Log::info('Loan disbursement process started', [
                'payment_method' => $payMethod,
                'loan_type' => $loanType,
                'product_code' => $productCode,
                'user_id' => auth()->id(),
                'user_branch' => auth()->user()->branch,
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId()
            ]);

            // Validate required data
            Log::info('Starting input validation for loan disbursement');
            
            if (!$this->bank_account) {
                Log::error('Disbursement validation failed: No bank account selected', [
                    'user_id' => auth()->id(),
                    'payment_method' => $payMethod
                ]);
                throw new \Exception('Please select a disbursement account.');
            }

            if ($this->approved_loan_value <= 0) {
                Log::error('Disbursement validation failed: Invalid loan amount', [
                    'user_id' => auth()->id(),
                    'approved_loan_value' => $this->approved_loan_value,
                    'payment_method' => $payMethod
                ]);
                throw new \Exception('Invalid loan amount for disbursement.');
            }

            Log::info('Basic validation passed', [
                'bank_account' => $this->bank_account,
                'approved_loan_value' => $this->approved_loan_value
            ]);

            // Validate payment method specific data
            Log::info('Validating payment method specific data', ['payment_method' => $payMethod]);
            $this->validatePaymentMethodData($payMethod);
            Log::info('Payment method validation passed');

            $loanID = session('currentloanID');
            if (!$loanID) {
                Log::error('Disbursement failed: No loan ID in session', [
                    'user_id' => auth()->id(),
                    'session_data' => session()->all()
                ]);
                throw new \Exception('No loan selected for disbursement.');
            }

            Log::info('Loan ID retrieved from session', ['loan_id' => $loanID]);

            // Get loan and related data
            Log::info('Fetching loan data from database', ['loan_id' => $loanID]);
            $loan = DB::table('loans')->find($loanID);
            if (!$loan) {
                Log::error('Loan not found in database', [
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Loan not found.');
            }

            Log::info('Loan data retrieved successfully', [
                'loan_id' => $loan->id,
                'client_number' => $loan->client_number,
                'loan_sub_product' => $loan->loan_sub_product,
                'approved_loan_value' => $loan->approved_loan_value
            ]);

            Log::info('Fetching member data', ['client_number' => $loan->client_number]);
            $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
            if (!$member) {
                Log::error('Member not found in database', [
                    'client_number' => $loan->client_number,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Member not found.');
            }

            Log::info('Member data retrieved successfully', [
                'client_number' => $member->client_number,
                'member_name' => $member->first_name . ' ' . $member->last_name
            ]);

            Log::info('Fetching loan product data', ['sub_product_id' => $loan->loan_sub_product]);
            $loanProduct = DB::table('loan_sub_products')->where('sub_product_id', $loan->loan_sub_product)->first();
            if (!$loanProduct) {
                Log::error('Loan product not found in database', [
                    'sub_product_id' => $loan->loan_sub_product,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Loan product not found.');
            }

            Log::info('Loan product data retrieved successfully', [
                'sub_product_id' => $loanProduct->sub_product_id,
                'sub_product_name' => $loanProduct->sub_product_name,
                'loan_product_account' => $loanProduct->loan_product_account
            ]);

            // Get member full name
            $memberName = $member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name;

            // Set product account and narration
            $this->product_account = $loanProduct->loan_product_account;
            $this->narration = "Loan disbursement to member: {$memberName}";

            Log::info('Product account and narration set', [
                'product_account' => $this->product_account,
                'narration' => $this->narration
            ]);

            // Initialize account creation service
            Log::info('Initializing AccountCreationService');
            $accountService = new AccountCreationService();

            // Create loan principal account
            Log::info('Creating loan principal account', [
                'product_account' => $this->product_account,
                'member_name' => $memberName
            ]);
            
            $loanParentAccount = AccountsModel::where('account_number', $this->product_account)->first();
            if (!$loanParentAccount) {
                Log::error('Loan parent account not found', [
                    'sub_category_code' => $this->product_account,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Loan parent account not found.');
            }

            Log::info('Loan parent account found', [
                'parent_account_number' => $loanParentAccount->account_number,
                'parent_account_name' => $loanParentAccount->account_name
            ]);

            $loanAccount = $accountService->createAccount([
                'account_use' => 'external',
                'account_name' => $loanParentAccount->account_name . ':' . $memberName,
                'type' => 'asset_accounts',
                'product_number' => '4000',
                'member_number' => $loan->client_number,
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Loan Account: Loan ID ' . $loanID
            ], $loanParentAccount->account_number);

            Log::info('Loan principal account created successfully', [
                'account_number' => $loanAccount->account_number,
                'account_name' => $loanAccount->account_name,
                'account_id' => $loanAccount->id
            ]);

            // Update loan with account number
            Log::info('Updating loan with account number', [
                'loan_id' => $loanID,
                'account_number' => $loanAccount->account_number
            ]);
            
            DB::table('loans')->where('id', $loanID)->update(['loan_account_number' => $loanAccount->account_number]);
            Log::info('Loan updated with account number successfully');

            // Generate loan repayment schedule
            Log::info('Generating loan repayment schedule', [
                'loan_id' => $loanID,
                'approved_term' => $this->approved_term,
                'approved_loan_value' => $this->approved_loan_value
            ]);
            
            $this->createRepaymentSchedule($loanID, $loan, $loanProduct, $member);
            Log::info('Loan repayment schedule generated successfully');

            // Calculate charges and insurance
            Log::info('Calculating loan charges and insurance');
            $totalCharges = $this->calculateTotalCharges();
            $insuranceAmount = $this->calculateInsurance();
            
            Log::info('Charges and insurance calculated', [
                'total_charges' => $totalCharges,
                'insurance_amount' => $insuranceAmount
            ]);

            // Create interest account
            Log::info('Creating interest account', [
                'loan_interest_account' => $loanProduct->loan_interest_account
            ]);
            
            $interestParentAccount = AccountsModel::where('account_number', $loanProduct->loan_interest_account)->first();
            if (!$interestParentAccount) {
                Log::error('Interest parent account not found', [
                    'sub_category_code' => $loanProduct->loan_interest_account,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Interest parent account not found.');
            }

            $interestAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $interestParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'income_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Interest Account: Loan ID ' . $loanID
            ], $interestParentAccount->account_number);

            Log::info('Interest account created successfully', [
                'account_number' => $interestAccount->account_number,
                'account_name' => $interestAccount->account_name
            ]);

            // Create charges account
            Log::info('Creating charges account', [
                'loan_charges_account' => $loanProduct->loan_charges_account
            ]);
            
            $chargesParentAccount = AccountsModel::where('account_number', $loanProduct->loan_charges_account)->first();
            if (!$chargesParentAccount) {
                Log::error('Charges parent account not found', [
                    'sub_category_code' => $loanProduct->loan_charges_account,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Charges parent account not found.');
            }

            $chargesAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $chargesParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'income_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Charge Account: Loan ID ' . $loanID
            ], $chargesParentAccount->account_number);

            Log::info('Charges account created successfully', [
                'account_number' => $chargesAccount->account_number,
                'account_name' => $chargesAccount->account_name
            ]);

            // Create insurance account
            Log::info('Creating insurance account', [
                'loan_insurance_account' => $loanProduct->loan_insurance_account
            ]);
            
            $insuranceParentAccount = AccountsModel::where('account_number', $loanProduct->loan_insurance_account)->first();
            if (!$insuranceParentAccount) {
                Log::error('Insurance parent account not found', [
                    'sub_category_code' => $loanProduct->loan_insurance_account,
                    'loan_id' => $loanID,
                    'user_id' => auth()->id()
                ]);
                throw new \Exception('Insurance parent account not found.');
            }

            $insuranceAccount = $accountService->createAccount([
                'account_use' => 'internal',
                'account_name' => $insuranceParentAccount->account_name . ': Loan ID ' . $loanID,
                'type' => 'capital_accounts',
                'product_number' => '0000',
                'branch_number' => auth()->user()->branch,
                'sub_product_number' => $loanID,
                'notes' => 'Insurance Account: Loan ID ' . $loanID
            ], $insuranceParentAccount->account_number);

            Log::info('Insurance account created successfully', [
                'account_number' => $insuranceAccount->account_number,
                'account_name' => $insuranceAccount->account_name
            ]);

            // Handle top-up loan if applicable
            $topUpAmount = 0;
            if ($this->selectedLoan) {
                Log::info('Processing top-up loan', [
                    'selected_loan' => $this->selectedLoan
                ]);
                
                $topUpAmount = $this->handleTopUpLoan($loan);
                
                Log::info('Top-up loan processed', [
                    'top_up_amount' => $topUpAmount,
                    'selected_loan' => $this->selectedLoan
                ]);
            }

            // Process loan disbursement based on payment method
            $disbursementAmount = $this->approved_loan_value - $topUpAmount;
            
            Log::info('Processing loan disbursement', [
                'payment_method' => $payMethod,
                'disbursement_amount' => $disbursementAmount,
                'top_up_amount' => $topUpAmount,
                'total_approved_amount' => $this->approved_loan_value
            ]);
            
            $this->processDisbursementByPaymentMethod($payMethod, $loanAccount, $disbursementAmount, $memberName);
            Log::info('Loan disbursement processed successfully via ' . $payMethod);

            // Process other transactions (interest, charges, insurance)
            Log::info('Processing additional transactions (interest, charges, insurance)');
            
            $this->processLoanTransactions(
                $loanAccount->sub_category_code,
                $interestAccount->sub_category_code,
                $chargesAccount->sub_category_code,
                $insuranceAccount->sub_category_code,
                $topUpAmount
            );
            
            Log::info('Additional transactions processed successfully');

            // Update loan status and finalize disbursement
            Log::info('Finalizing loan disbursement - updating loan status', [
                'loan_id' => $loanID,
                'status' => 'ACCOUNTING',
                'disbursement_method' => $payMethod
            ]);
            
            DB::table('loans')->where('id', $loanID)->update([
                'status' => 'ACCOUNTING',
                'interest_account_number' => $interestAccount->account_number,
                'charge_account_number' => $chargesAccount->account_number,
                'insurance_account_number' => $insuranceAccount->account_number,
                'disbursement_date' => now(),
                'disbursement_method' => $payMethod,
            ]);
            
            Log::info('Loan status updated successfully');

            // Reset session and emit event
            Log::info('Clearing session and emitting refresh event');
            session()->forget('currentloanID');
            $this->emit('refreshLoanList');

            // Calculate execution time
            $executionTime = microtime(true) - $startTime;
            
            // Show success message
            $successMessage = 'Loan disbursed successfully to ' . $memberName . ' via ' . ucfirst(str_replace('_', ' ', $payMethod));
            session()->flash('success', $successMessage);

            // Log successful completion
            Log::info('Loan disbursement completed successfully', [
                'loan_id' => $loanID,
                'member_name' => $memberName,
                'payment_method' => $payMethod,
                'disbursement_amount' => $disbursementAmount,
                'execution_time_seconds' => round($executionTime, 3),
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString(),
                'accounts_created' => [
                    'loan_account' => $loanAccount->account_number,
                    'interest_account' => $interestAccount->account_number,
                    'charges_account' => $chargesAccount->account_number,
                    'insurance_account' => $insuranceAccount->account_number
                ]
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            
            // Log detailed error information
            Log::error('Loan disbursement failed', [
                'loan_id' => $loanID ?? null,
                'member_name' => $memberName ?? null,
                'payment_method' => $payMethod,
                'loan_type' => $loanType,
                'product_code' => $productCode,
                'user_id' => auth()->id(),
                'user_branch' => auth()->user()->branch ?? null,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'execution_time_seconds' => round($executionTime, 3),
                'timestamp' => now()->toISOString(),
                'session_id' => session()->getId(),
                'stack_trace' => $e->getTraceAsString(),
                'request_data' => [
                    'bank_account' => $this->bank_account ?? null,
                    'approved_loan_value' => $this->approved_loan_value ?? null,
                    'selected_loan' => $this->selectedLoan ?? null,
                    'first_interest_amount' => $this->firstInterestAmount ?? null
                ]
            ]);
            
            // Log additional context for debugging
            if ($loanID) {
                Log::error('Loan state at failure', [
                    'loan_id' => $loanID,
                    'loan_status' => DB::table('loans')->where('id', $loanID)->value('status'),
                    'loan_account_number' => DB::table('loans')->where('id', $loanID)->value('loan_account_number'),
                    'disbursement_date' => DB::table('loans')->where('id', $loanID)->value('disbursement_date')
                ]);
            }
            
            session()->flash('error', 'Loan disbursement failed: ' . $e->getMessage());
            
            // Re-throw the exception for proper error handling
            throw $e;
        }
    }

    /**
     * Validate payment method specific data
     */
    private function validatePaymentMethodData($payMethod)
    {
        switch ($payMethod) {
            case 'internal_transfer':
                if (empty($this->memberNbcAccount)) {
                    throw new \Exception('Member NBC account number is required for internal transfer.');
                }
                if (empty($this->memberAccountHolderName)) {
                    throw new \Exception('Account holder name is required for internal transfer.');
                }
                break;
            case 'tips_mno':
                if (empty($this->memberPhoneNumber)) {
                    throw new \Exception('Member phone number is required for MNO transfer.');
                }
                if (empty($this->memberMnoProvider)) {
                    throw new \Exception('MNO provider is required for MNO transfer.');
                }
                if (empty($this->memberWalletHolderName)) {
                    throw new \Exception('Wallet holder name is required for MNO transfer.');
                }
                break;
            case 'tips_bank':
                if (empty($this->memberBankCode)) {
                    throw new \Exception('Bank code is required for bank transfer.');
                }
                if (empty($this->memberBankAccountNumber)) {
                    throw new \Exception('Bank account number is required for bank transfer.');
                }
                if (empty($this->memberBankAccountHolderName)) {
                    throw new \Exception('Bank account holder name is required for bank transfer.');
                }
                break;
        }
    }

    /**
     * Handle top-up loan processing
     */
    private function handleTopUpLoan($loan)
    {
        $loan_account_number = DB::table('loans')->where('id', $loan->selectedLoan)->value('loan_account_number');
        $closedLoan = DB::table('sub_accounts')->where('account_number', $loan_account_number)->first();

        if (!$closedLoan) {
            throw new \Exception('Closed loan account not found for top-up.');
        }

        $this->loan_account_sub_category_code = $closedLoan->sub_category_code;
        $this->loan_account_number2 = $loan_account_number;
        $topUpAmount = $closedLoan->balance;

        $this->update_repayment($loan->selectedLoan, $closedLoan->balance);

        return $topUpAmount;
    }

    /**
     * Process all loan disbursement transactions
     */
    private function processLoanTransactions($loanAccountCode, $interestAccountCode, $chargesAccountCode, $insuranceAccountCode, $topUpAmount)
    {
        // Process principal disbursement
        $this->processTransaction($loanAccountCode, $this->bank_account, $this->approved_loan_value - $topUpAmount, 'Loan Principal');

        // Process first interest
        if ($this->firstInterestAmount > 0) {
            $this->processTransaction($interestAccountCode, $this->bank_account, $this->firstInterestAmount, 'First Interest');
        }

        // Process charges
        $totalCharges = $this->calculateTotalCharges();
        if ($totalCharges > 0) {
            $this->processTransaction($chargesAccountCode, $this->bank_account, $totalCharges, 'Loan Charges');
        }

        // Process insurance
        $insuranceAmount = $this->calculateInsurance();
        if ($insuranceAmount > 0) {
            $this->processTransaction($insuranceAccountCode, $this->bank_account, $insuranceAmount, 'Insurance Premium');
        }

        $this->ClosedLoanBalance = 0;
    }
























    // Method to create repayment schedule and save to database
    protected function createRepaymentSchedule($loanID, $loan, $loanProduct, $member)
    {
        $approvedTerm = $this->approved_term;
        $approvedLoanValue = $this->approved_loan_value;
        $memberCategory = $member->member_category;
        $dayOfMonth = DB::table('member_categories')->where('id', $memberCategory)->value('repayment_date') ?? "18";

        $schedule = $this->data['schedule'];
        $footer = $this->data['footer'];

        // Save each installment in the repayment schedule
        foreach ($schedule as $scheduleData) {
            $completion_status = 'ACTIVE';
            $payment = 0;
            $interest_payment = 0;
            if( $scheduleData['principal'] == 0){
                $completion_status = 'PAID';
                $payment = $scheduleData['interest'];
                $interest_payment = $scheduleData['interest'];
            }
            loans_schedules::create([
                'loan_id' => $loanID,
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
            'loan_id' => $loanID,
            'installment' => $footer['total_payment'],
            'interest' => $footer['total_interest'],
            'principle' => $footer['total_principal'],
            'balance' => $footer['final_closing_balance'],
            'completion_status' => 'ACTIVE',
            'status' => 'ACTIVE',
        ]);
    }


// Method to process transactions with error handling
    protected function processTransaction($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix)
    {
        //dd($debitAccountCode, $creditAccountCode, $amount, $narrationSuffix);
        $this->narration = "{$narrationSuffix} : Loan ID " . session('currentloanID');
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















    private function recordGeneralLedger($accountNumber, $newBalance, $amount, $description, $type)
    {

        $entryData = [
            'record_on_account_number' => $accountNumber,
            'record_on_account_number_balance' => $newBalance,
            'sender_branch_id' => auth()->user()->branch,
            'beneficiary_branch_id' => null,
            'sender_product_id' => '0000',
            'sender_sub_product_id' => '0000',
            'beneficiary_product_id' => null,
            'beneficiary_sub_product_id' => null,
            'sender_id' => 0,
            'beneficiary_id' => 0,
            'sender_name' => '0000',
            'beneficiary_name' => '0000',
            'sender_account_number' => '0000',
            'beneficiary_account_number' => '0000',
            'transaction_type' => ' ',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'reference_number' => $this->referenceNumber,
            'trans_status' => 'PENDING',
            'narration' => $description,
            'swift_code' => null,
            'destination_bank_name' => 'NBC',
            'destination_bank_number' => '0000',
            'recon_status' => 'PENDING',
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

    public function addLoanAccount($account_number,$product_account){



        $loanID = session('currentloanID');
        $loan = DB::table('loans')->find($loanID);
        $member = DB::table('clients')->where('client_number', $loan->client_number)->first();
        $product = DB::table('accounts')->where('sub_category_code', $product_account)->first();

        //dd($product);

        $id = AccountsModel::create([
            'account_use' => 'external',
            'institution_number'=> '1000',
            'branch_number'=> $loan->branch_id,
            'client_number'=> $loan->client_number,
            'product_number'=> '4000',
            'sub_product_number'=>  $loan->loan_sub_product,
            'major_category_code'=>$product->major_category_code,
            'category_code'=> $product->category_code,
            'sub_category_code'=>  $product->sub_category_code,
            'balance'=>  0,
            'account_name'=> $loan->loan_id .':'. $member->first_name .' '. $member->middle_name .' '. $member->last_name,
            'account_number'=>$account_number,
            'parent_account_number'=>$this->credit_account,
            'account_level'=>3,
            'type' => 'asset_accounts',

        ])->id;

        //dd($id);


    }







    function generateLoanRepaymentSchedule($loanId, $approvedTerm, $updatedPrinciple, $dayOfMonth, $disbursementDate, $interestRate = 0.12)
    {
        $schedule = [];
        $balance = $updatedPrinciple;

        if($this->principle_grace_period == true){
            //$this->daysBetweenx ;
        }else{
            $this->daysBetweenx = 0;
        }

        // Grace Period Interest Calculation
        $daysInGracePeriod = $this->daysBetweenx;
        $dailyInterestRate = $interestRate / 365;
        $gracePeriodInterest = $balance * $dailyInterestRate * $daysInGracePeriod;

        $this->grace_period_interest = $gracePeriodInterest;

        //dd($daysInGracePeriod);

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



        // Move to the next installment date
        $firstInstallmentDate = date('Y-m-' . $dayOfMonth, strtotime("+1 month", strtotime($disbursementDate)));

        // Remaining Installments
        for ($installment = 2; $installment <= $approvedTerm + 1; $installment++) {
            $monthlyInterest = $balance * ($interestRate / 12);
            $monthlyInterestRate = $interestRate / 12;
            $monthlyPayment = ($updatedPrinciple * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$approvedTerm));
            $principalPayment = $monthlyPayment - $monthlyInterest;
            $closingBalance = $balance - $principalPayment;

            $schedule[] = [
                'installment' => $installment,
                'installment_date' => $firstInstallmentDate,
                'opening_balance' => $balance,
                'payment' => $monthlyPayment,
                'principal' => $principalPayment,
                'interest' => $monthlyInterest,
                'closing_balance' => $closingBalance,
            ];

            $balance = $closingBalance;
            $firstInstallmentDate = date('Y-m-d', strtotime("+1 month", strtotime($firstInstallmentDate)));
        }

        // Calculate footer totals
        $totals = $this->calculateFooterTotals($schedule);

        return ['schedule' => $schedule, 'footer' => $totals];
    }

    function calculateFooterTotals($schedule)
    {
        $totals = [
            'total_payment' => 0,
            'total_principal' => 0,
            'total_interest' => 0,
            'final_closing_balance' => end($schedule)['closing_balance'],
        ];

        foreach ($schedule as $row) {
            $totals['total_payment'] += floatval($row['payment']);
            $totals['total_principal'] += floatval($row['principal']);
            $totals['total_interest'] += floatval($row['interest']);
        }

        return $totals;
    }



    public function calculateLoanProductCharge($product_id, $principle)
    {
        // Retrieve all charge records related to the product in a single query
        $charges = DB::table('charges')
            ->join('product_has_charges', 'charges.id', '=', 'product_has_charges.charge_id')
            ->where('product_has_charges.product_id', $product_id)
            ->select('charges.calculating_type', 'charges.value')
            ->get();

        $totalAmount = 0;

        foreach ($charges as $charge) {
            // Calculate the charge amount based on its type
            $chargeAmount = ($charge->calculating_type === "Percent")
                ? ($principle * ($charge->value / 100))
                : $charge->value;

            // Accumulate the total amount
            $totalAmount += $chargeAmount;
        }

        return $totalAmount;
    }




    public function calculateLoanProductInsurance($product_id,$principle){

            $insurances = DB::table('insurancelist')
            ->join('product_has_insurance', 'insurancelist.id', '=', 'product_has_insurance.insurance_id')
            ->where('product_has_insurance.product_id', $product_id)
            ->select('insurancelist.calculating_type', 'insurancelist.value')
            ->get();

            $totalAmount = 0;

            foreach ($insurances as $insurance) {
            $Amount = ($insurance->calculating_type === "Percent")
                ? ($principle * ($insurance->value / 100))
                : $insurance->value;

            // Accumulate the total amount
            $totalAmount += $Amount;
            }

            return $totalAmount;

    }
























    public function resetProperties()
    {
        $this->photo = null;
        $this->futureInterest = false;
        $this->collateral_type = null;
        $this->collateral_description = null;
        $this->daily_sales = null;
        $this->loan = null;
        $this->collateral_value = null;
        $this->loan_sub_product = null;
        $this->principle = 0;
        $this->member = null;
        $this->guarantor = null;
        $this->disbursement_account = null;
        $this->collection_account_loan_interest = null;
        $this->collection_account_loan_principle = null;
        $this->collection_account_loan_charges = null;
        $this->collection_account_loan_penalties = null;
        $this->principle_min_value = null;
        $this->principle_max_value = null;
        $this->min_term = null;
        $this->max_term = null;
        $this->interest_value = null;
        $this->principle_grace_period = null;
        $this->interest_grace_period = null;
        $this->amortization_method = null;
        $this->days_in_a_month = 30;
        $this->loan_id = null;
        $this->loan_account_number = null;
        $this->member_number = null;
        $this->topUpBoolena = null;
        $this->new_principle = null;
        $this->interest = 0;
        $this->business_licence_number = null;
        $this->business_tin_number = null;
        $this->business_inventory = null;
        $this->cash_at_hand = null;
        $this->cost_of_goods_sold = null;
        $this->operating_expenses = null;
        $this->monthly_taxes = null;
        $this->other_expenses = null;
        $this->monthly_sales = null;
        $this->gross_profit = null;
        $this->table = [];
        $this->tablefooter = [];
        $this->recommended_tenure = null;
        $this->recommended_installment = null;
        $this->totalAmount = null;
        $this->recommended = true;
        $this->monthlyInstallmentValue = null;
        $this->business_age = null;
        $this->bank1 = 123456; // Set to default if needed
        $this->available_funds = null;
        $this->interest_method = null;
        $this->loan_is_settled = false;
        $this->approved_term = 12;
        $this->approved_loan_value = 0;
        $this->future_interests = null;
        $this->futureInsteresAmount = null;
        $this->valueAmmount = null;
        $this->net_profit = null;
        $this->status = null;
        $this->products = null;
        $this->coverage = null;
        $this->idx = null;
        $this->sub_product_id = null;
        $this->product = null;
        $this->account = null;
        $this->charges = null;
        $this->institution1 = null;
        $this->institutionAmount = null;
        $this->institution2 = null;
        $this->institutionAmount2 = null;
        $this->daysBetweenx = 0;
        $this->non_permanent_income_non_taxable = 0;
        $this->non_permanent_income_taxable = 0;
        $this->take_home = 0;
        $this->totalInstallment = 0;
        $this->tenure = 12;
        $this->max_loan = null;
        $this->selectedContracts = [];
        $this->x = null;
        $this->isPhysicalCollateral = false;
        $this->account1 = null;
        $this->account2 = null;
        $this->creditableAmount = null;
        $this->referenceNumber = null;
        $this->bank_account = null;
    }










    public function render()
    {
        // Initialize charges and insurance lists
        $this->charges = collect();
        $this->insurance_list = collect();

        // Only process if product exists
        if ($this->product && $this->product->sub_product_id) {
            $charges_id = DB::table('product_has_charges')
                ->where('product_id', $this->product->sub_product_id)
                ->pluck('charge_id')->toArray();

            $this->charges = DB::table('chargeslist')->whereIn('id', $charges_id)->get();

            $insurance_id = DB::table('product_has_insurance')
                ->where('product_id', $this->product->sub_product_id)
                ->pluck('insurance_id')->toArray();

            $this->insurance_list = DB::table('insurancelist')->whereIn('id', $insurance_id)->get();
        }

        // Generate loan repayment schedule
        $schedule = [];
        $footer = null;
        
        if ($this->approved_loan_value && $this->approved_term && $this->product) {
            try {
                $interestRate = $this->product->interest_value ? (float)$this->product->interest_value / 100 : 0.12;
                
                $scheduleData = $this->generateLoanRepaymentSchedule(
                    session('currentloanID'),
                    $this->approved_term,
                    $this->approved_loan_value,
                    15, // Default day of month
                    now(), // Disbursement date
                    $interestRate
                );
                
                $schedule = $scheduleData['schedule'] ?? [];
                $footer = $scheduleData['footer'] ?? null;
            } catch (\Exception $e) {
                // If schedule generation fails, use empty schedule
                $schedule = [];
                $footer = null;
                Log::error('Error generating loan schedule: ' . $e->getMessage());
            }
        }

        return view('livewire.accounting.loan-details', [
            'schedule' => $schedule,
            'footer' => $footer
        ]);
    }








    public function post()
    {
        //$this->validate();





        DB::beginTransaction();
        try {
            // Validate account balances
            $debited_account_details = AccountsModel::where("account_number", $this->debit_account)->first();
//            if (!$debited_account_details || $debited_account_details->balance < $this->amount) {
//                throw new \Exception('Insufficient funds or invalid debit account.');
//            }



            $credited_account_details = AccountsModel::where("account_number", $this->credit_account)->first();
            if (!$credited_account_details) {
                throw new \Exception('Invalid credit account.');
            }

            // Create reference number
            $reference_number = time();

            //dd($reference_number, $debited_account_details, $credited_account_details);

            // Perform the posting
            $this->postTransaction($reference_number, $debited_account_details, $credited_account_details);

            DB::commit();

            // Logging for audit trail
            $this->logAudit('Transaction Posted', $reference_number);

            session()->flash('message', 'Transaction posted successfully and awaiting approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        }

        //$this->resetInputFields();
    }







    /**
     * @throws \Exception
     */
    private function postTransaction($reference_number, $loan_account, $cash_account)
    {
        try {
            // Start Transaction
            DB::beginTransaction();

            // Step 1: Ensure loan and cash accounts exist and have sufficient balances
            if (!$loan_account || !$cash_account) {
                throw new \Exception("Loan or Cash account does not exist.");
            }

            // Step 2: Calculate total charges and insurance
            $totalCharges = $this->calculateTotalCharges();
            $insuranceAmount = $this->calculateInsurance();
            if($this->principle_grace_period == 1){
                $amountToDebit = $this->loan_amount - ($totalCharges + $insuranceAmount + $this->grace_period_interest);
            }else{
                $amountToDebit = $this->loan_amount - ($totalCharges + $insuranceAmount);
            }


            // Ensure that amount to debit is not negative
            if ($amountToDebit <= 0) {
                throw new \Exception("Loan amount after charges and insurance is insufficient.");
            }

            // Log initial balances
            Log::info("Starting postTransaction", [
                'reference_number' => $reference_number,
                'loan_account' => $loan_account->account_number,
                'cash_account' => $cash_account->account_number,
                'loan_amount' => $this->loan_amount,
                'total_charges' => $totalCharges,
                'insurance_amount' => $insuranceAmount,
                'amount_to_debit' => $amountToDebit
            ]);

            // Step 3: Credit the full loan amount to the cash account
            $cash_account_new_balance = $cash_account->balance + $this->loan_amount;
            $this->credit($reference_number, $loan_account, $cash_account, $this->loan_amount, $cash_account_new_balance);
            Log::info("Credited cash account", [
                'cash_account' => $cash_account->account_number,
                'new_balance' => $cash_account_new_balance
            ]);

            // Step 4: Debit only the loan amount minus charges and insurance from the loan account
//            if ($loan_account->balance < $amountToDebit) {
//                throw new \Exception("Insufficient balance in loan account.");
//            }
            $loan_account_new_balance = $loan_account->balance - $amountToDebit;
            $this->debit($reference_number, $loan_account, $cash_account, $amountToDebit, $loan_account_new_balance);
            Log::info("Debited loan account", [
                'loan_account' => $loan_account->account_number,
                'new_balance' => $loan_account_new_balance
            ]);

            // Update cash and loan account balances in the database
            AccountsModel::where('account_number', $loan_account->account_number)
                ->update(['balance' => $loan_account_new_balance]);
            AccountsModel::where('account_number', $cash_account->account_number)
                ->update(['balance' => $cash_account_new_balance]);

            // Step 5: Process Charges and Insurance
            $this->processCharges($reference_number, $cash_account);
            $this->processInsurance($reference_number, $cash_account);
            if($this->principle_grace_period == true){
                $this->processFirstInterest($reference_number, $cash_account);
            }


            // Commit Transaction if everything went fine
            DB::commit();
            Log::info("Transaction committed successfully");

        } catch (\Exception $e) {
            // Rollback Transaction in case of errors
            DB::rollBack();
            Log::error("Transaction failed", ['error' => $e->getMessage()]);
            throw $e; // Optionally rethrow the exception
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
                $insuranceAmount = $this->loan_amount * 0.125/100 * $this->loan_tenure; // Fixed charge
            } else {
                $insuranceAmount = $this->loan_amount * 0.125/100 * $this->loan_tenure; // Percentage-based charge
            }

            $totalInsurance += $insuranceAmount;

        }


        //return ($this->loan_amount * (($this->insurance->monthly_rate / 100) * $this->loan_tenure));
        return $totalInsurance;
    }


    private function processCharges($reference_number, $cash_account)
    {
        $transactionService = new TransactionPostingService();
        foreach ($this->charges as $charge) {
            $chargeAmount = $this->calculateCharge($charge);
            Log::info("Processing charge", [
                'charge_type' => $charge->charge_type,
                'charge_amount' => $chargeAmount
            ]);
            $loan_charges_account = DB::table('accounts')->where('sub_category_code', '4111')->first();
            if (!$loan_charges_account) {
                throw new \Exception("Loan charges account not found.");
            }
            // Post transaction instead of direct update
            $transactionService->postTransaction([
                'first_account' => $loan_charges_account->account_number,
                'second_account' => $cash_account->account_number,
                'amount' => $chargeAmount,
                'narration' => $this->narration,
            ]);
            // AccountsModel::where('sub_category_code', '4101')->update(['balance' => $loan_charges_new_balance]);
        }
    }

    private function calculateCharge($charge)
    {

        //dd($charge);
        if ($charge['calculating_type'] == 'Fixed') {

            $chargeAmount = $charge['value']; // Fixed charge

            $chargeAmount = ($this->loan_amount * 0.3 / 100);

            if($chargeAmount > 30000){
                $chargeAmount = 30000;
            }elseif($chargeAmount < 10000){
                $chargeAmount = 10000;
            }else{
                $chargeAmount = $chargeAmount;
            }

            return $chargeAmount;
        } else {

            return ($this->loan_amount * 0.3 / 100);
        }

    }


    private function processInsurance($reference_number, $cash_account)
    {
        $transactionService = new TransactionPostingService();
        $insurance_amount = ($this->loan_amount * ((($this->insurance->monthly_rate / 100) * $this->loan_tenure)));
        Log::info("Processing insurance", [
            'insurance_monthly_rate' => $this->insurance->monthly_rate,
            'loan_tenure' => $this->loan_tenure,
            'insurance_amount' => $insurance_amount
        ]);
        $insurance_account = DB::table('accounts')->where('account_number', $this->insurance->account_number)->first();
        if (!$insurance_account) {
            throw new \Exception("Insurance account not found.");
        }
        $transactionService->postTransaction([
            'first_account' => $insurance_account->account_number,
            'second_account' => $cash_account->account_number,
            'amount' => $insurance_amount,
            'narration' => $this->narration,
        ]);
        // AccountsModel::where('account_number', $insurance_account->account_number)->update(['balance' => $insurance_new_balance]);
    }


    public function processFirstInterest($reference_number, $cash_account){
        $transactionService = new TransactionPostingService();
        $first_interest_amount = $this->grace_period_interest;
        $first_interest_account = DB::table('accounts')->where('sub_category_code', '4001')->first();
        if (!$first_interest_account) {
            throw new \Exception("Insurance account not found.");
        }
        $transactionService->postTransaction([
            'first_account' => $first_interest_account->account_number,
            'second_account' => $cash_account->account_number,
            'amount' => $first_interest_amount,
            'narration' => $this->narration,
        ]);
        // AccountsModel::where('account_number', $first_interest_account->account_number)->update(['balance' => $first_interest_balance]);
    }





    private function debit($reference_number, $debited_account, $credited_account, $amount, $new_amount)
    {
        Log::info("Debit entry", [
            'debited_account' => $debited_account->account_number,
            'amount' => $amount
        ]);

        general_ledger::create([
            'record_on_account_number' => $debited_account->account_number,
            'record_on_account_number_balance' => $new_amount,
            'major_category_code' => $debited_account->major_category_code,
            'category_code' => $debited_account->category_code,
            'sub_category_code' => $debited_account->sub_category_code,
            'sender_name' => $debited_account->account_name,
            'beneficiary_name' => $credited_account->account_name,
            'sender_account_number' => $debited_account->account_number,
            'beneficiary_account_number' => $credited_account->account_number,
            'narration' => $this->narration,
            'credit' => 0,
            'debit' => $amount,  // Debit the actual amount
            'reference_number' => $reference_number,
            'trans_status' => 'Pending Approval', // Status updated once approved
            'trans_status_description' => 'Awaiting Approval',
            'payment_status' => 'Pending',
            'recon_status' => 'Pending',
        ]);
    }

    private function credit($reference_number, $debited_account, $credited_account, $amount, $new_amount)
    {
        Log::info("Credit entry", [
            'credited_account' => $credited_account->account_number,
            'amount' => $amount
        ]);

        general_ledger::create([
            'record_on_account_number' => $credited_account->account_number,
            'record_on_account_number_balance' => $new_amount,
            'major_category_code' => $credited_account->major_category_code,
            'category_code' => $credited_account->category_code,
            'sub_category_code' => $credited_account->sub_category_code,
            'sender_name' => $debited_account->account_name,
            'beneficiary_name' => $credited_account->account_name,
            'sender_account_number' => $debited_account->account_number,
            'beneficiary_account_number' => $credited_account->account_number,
            'narration' => $this->narration,
            'credit' => $amount,  // Credit the actual amount
            'debit' => 0,
            'reference_number' => $reference_number,
            'trans_status' => 'Pending Approval',
            'trans_status_description' => 'Awaiting Approval',
            'payment_status' => 'Pending',
            'recon_status' => 'Pending',
        ]);
    }



    private function logAudit($action, $reference_number)
    {
        DB::table('audit_logs')->insert([
            'action' => $action,
            'reference_number' => $reference_number,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Process loan disbursement based on payment method
     */
    private function processDisbursementByPaymentMethod($payMethod, $loanAccount, $amount, $memberName)
    {
        switch ($payMethod) {
            case 'cash':
                return $this->processCashDisbursement($loanAccount, $amount, $memberName);
            case 'internal_transfer':
                return $this->processInternalTransferDisbursement($loanAccount, $amount, $memberName);
            case 'tips_mno':
                return $this->processTipsMnoDisbursement($loanAccount, $amount, $memberName);
            case 'tips_bank':
                return $this->processTipsBankDisbursement($loanAccount, $amount, $memberName);
            default:
                throw new \Exception('Invalid payment method for disbursement.');
        }
    }

    /**
     * Process cash disbursement
     */
    private function processCashDisbursement($loanAccount, $amount, $memberName)
    {
        // Get cash in safe account
        $cashInSafeAccount = AccountsModel::where('account_name', 'LIKE', '%cash in safe%')
            ->orWhere('account_name', 'LIKE', '%cash%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashInSafeAccount) {
            throw new \Exception('Cash in safe account not found. Please contact administrator.');
        }

        // Post the cash disbursement transaction
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $loanAccount->account_number, // Debit loan account
            'second_account' => $cashInSafeAccount->account_number, // Credit cash in safe account
            'amount' => $amount,
            'narration' => 'Cash loan disbursement: ' . $amount . ' to ' . $memberName,
            'action' => 'cash_loan_disbursement'
        ];

        $result = $transactionService->postTransaction($transactionData);
        
        if ($result['status'] !== 'success') {
            throw new \Exception('Failed to post cash disbursement transaction: ' . ($result['message'] ?? 'Unknown error'));
        }

        Log::info('Cash loan disbursement processed successfully', [
            'loan_account' => $loanAccount->account_number,
            'amount' => $amount,
            'member' => $memberName
        ]);

        return $result;
    }

    /**
     * Process internal transfer disbursement
     */
    private function processInternalTransferDisbursement($loanAccount, $amount, $memberName)
    {
        // Get cash at NBC account
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process internal fund transfer using NBC API
        $internalTransferService = new \App\Services\NbcPayments\InternalFundTransferService();
        
        $transferData = [
            'debitAccount' => $cashAtNbcAccount->account_number, // SACCO's NBC account
            'creditAccount' => $this->memberNbcAccount, // Member's NBC account (should be set in form)
            'amount' => $amount,
            'debitCurrency' => 'TZS',
            'creditCurrency' => 'TZS',
            'narration' => 'Internal transfer loan disbursement: ' . $memberName,
            'channelId' => config('services.nbc_internal_fund_transfer.channel_id'),
            'channelRef' => $this->referenceNumber,
            'pyrName' => $memberName
        ];

        $result = $internalTransferService->processInternalTransfer($transferData);

        if (!$result['success']) {
            throw new \Exception('Internal transfer failed: ' . ($result['message'] ?? 'Unknown error'));
        }

        // Post the internal transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $loanAccount->account_number, // Debit loan account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $amount,
            'narration' => 'Internal transfer loan disbursement: ' . $amount . ' to ' . $memberName,
            'action' => 'internal_transfer_loan_disbursement'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post internal transfer transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('Internal transfer loan disbursement processed successfully', [
            'loan_account' => $loanAccount->account_number,
            'nbc_account' => $this->memberNbcAccount,
            'amount' => $amount,
            'member' => $memberName,
            'nbc_reference' => $result['data']['hostReferenceCbs'] ?? null
        ]);

        return $transactionResult;
    }

    /**
     * Process TIPS MNO disbursement
     */
    private function processTipsMnoDisbursement($loanAccount, $amount, $memberName)
    {
        // Get cash at NBC account
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process TIPS MNO transfer using NBC API
        $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
        $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

        // First, perform lookup
        $lookupResult = $nbcLookupService->bankToWalletLookup(
            $this->memberPhoneNumber, // Should be set in form
            $this->memberMnoProvider, // Should be set in form
            $cashAtNbcAccount->account_number,
            $amount,
            'PERSON'
        );

        if (!$lookupResult['success']) {
            throw new \Exception('TIPS lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
        }

        // Then process the transfer
        $transferResult = $nbcPaymentService->processBankToWalletTransfer(
            $lookupResult['data'],
            $cashAtNbcAccount->account_number,
            $amount,
            $this->memberPhoneNumber,
            time(), // initiatorId
            'TIPS MNO loan disbursement: ' . $memberName
        );

        if (!$transferResult['success']) {
            throw new \Exception('TIPS MNO transfer failed: ' . ($transferResult['message'] ?? 'Unknown error'));
        }

        // Post the transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $loanAccount->account_number, // Debit loan account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $amount,
            'narration' => 'TIPS MNO loan disbursement: ' . $amount . ' to ' . $this->memberPhoneNumber . ' (' . $this->memberMnoProvider . ')',
            'action' => 'tips_mno_loan_disbursement'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post TIPS MNO transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('TIPS MNO loan disbursement processed successfully', [
            'loan_account' => $loanAccount->account_number,
            'phone_number' => $this->memberPhoneNumber,
            'mno_provider' => $this->memberMnoProvider,
            'amount' => $amount,
            'member' => $memberName,
            'tips_reference' => $transferResult['engineRef'] ?? null
        ]);

        return $transactionResult;
    }

    /**
     * Process TIPS Bank disbursement
     */
    private function processTipsBankDisbursement($loanAccount, $amount, $memberName)
    {
        // Get cash at NBC account
        $cashAtNbcAccount = AccountsModel::where('account_name', 'LIKE', '%cash at NBC%')
            ->orWhere('account_name', 'LIKE', '%NBC%')
            ->where('status', 'ACTIVE')
            ->first();

        if (!$cashAtNbcAccount) {
            throw new \Exception('Cash at NBC account not found. Please contact administrator.');
        }

        // Process TIPS Bank transfer using NBC API
        $nbcPaymentService = new \App\Services\NbcPayments\NbcPaymentService();
        $nbcLookupService = new \App\Services\NbcPayments\NbcLookupService();

        // First, perform lookup
        $lookupResult = $nbcLookupService->bankToBankLookup(
            $this->memberBankAccountNumber, // Should be set in form
            $this->memberBankCode, // Should be set in form
            $cashAtNbcAccount->account_number,
            $amount,
            'PERSON'
        );

        if (!$lookupResult['success']) {
            throw new \Exception('TIPS bank lookup failed: ' . ($lookupResult['message'] ?? 'Unknown error'));
        }

        // Then process the transfer
        $transferResult = $nbcPaymentService->processBankToBankTransfer(
            $lookupResult['data'],
            $cashAtNbcAccount->account_number,
            $amount,
            $this->memberPhoneNumber ?? '255000000000', // Default phone number if not provided
            time(), // initiatorId
            'TIPS Bank loan disbursement: ' . $memberName,
            'FTLC'
        );

        if (!$transferResult['success']) {
            throw new \Exception('TIPS bank transfer failed: ' . ($transferResult['message'] ?? 'Unknown error'));
        }

        // Post the transaction in our system
        $transactionService = new TransactionPostingService();
        $transactionData = [
            'first_account' => $loanAccount->account_number, // Debit loan account
            'second_account' => $cashAtNbcAccount->account_number, // Credit cash at NBC account
            'amount' => $amount,
            'narration' => 'TIPS Bank loan disbursement: ' . $amount . ' to ' . $this->memberBankAccountNumber . ' (' . $this->memberBankCode . ')',
            'action' => 'tips_bank_loan_disbursement'
        ];

        $transactionResult = $transactionService->postTransaction($transactionData);
        
        if ($transactionResult['status'] !== 'success') {
            throw new \Exception('Failed to post TIPS bank transaction: ' . ($transactionResult['message'] ?? 'Unknown error'));
        }

        Log::info('TIPS Bank loan disbursement processed successfully', [
            'loan_account' => $loanAccount->account_number,
            'bank_account' => $this->memberBankAccountNumber,
            'bank_code' => $this->memberBankCode,
            'amount' => $amount,
            'member' => $memberName,
            'tips_reference' => $transferResult['engineRef'] ?? null
        ]);

        return $transactionResult;
    }
}


