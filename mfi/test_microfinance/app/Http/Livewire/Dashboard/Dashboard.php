<?php

namespace App\Http\Livewire\Dashboard;


use App\Http\Livewire\TellerManagement\Teller;
use App\Models\Grant;
use App\Models\institutions;
use App\Models\loans_schedules;
use App\Models\LoansModel;
use App\Models\PendingRegistration;
use Carbon\Carbon;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Models\sub_products;
use App\Models\LoanOfficerClients;
use Illuminate\Support\Facades\Session;
use App\Models\issured_shares;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AccountsModel;
use App\Models\general_ledger;
use App\Models\Clients;


use App\Models\approvals;
use App\Models\TeamUser;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use App\Traits\Livewire\WithModulePermissions;


class Dashboard extends Component
{
    use WithModulePermissions;

    public $tab_id = '1';
    public $title = 'Deposits report';
    public $term = "";
    public $showAddUser = false;
    public $memberStatus = 'All';
    public $numberOfProducts;
    public $products;
    public $item;

    public $member;
    public $product;
    public $number_of_shares;
    public $linked_savings_account;
    public $account_number;
    public $balance;
    public $deposit_charge_min_value;
    public $accountSelected;
    public $amount;
    public $notes;
    public $bank;
    public $reference_number;
    public $product_number;

    public $numberOfProducts1;
    public $products1;
    public $item1;

    public $member1;
    public $product1;
    public $number_of_shares1;
    public $linked_savings_account1;
    public $account_number1;
    public $balance1;
    public $deposit_charge_min_value1;
    public $accountSelected1;
    public $amount1;
    public $notes1;
    public $bank1;
    public $reference_number1;
    public $product_number1;
    public $ExternalAccounts;
    public $days;
    public $deposits;
    public $depositType;
    public $registrationFee;
    public $initial_shares_value=10000;
    public $new_member_deposit_notes;
    public $results;
    public $payment_method;
    public $setClientData  = false;
    public $selected=1;
    public $createPromises=false;
    public $promise_description;
    public $promise_date;
    public $selectedLoanId;
    ////
    public $deposit_type;
    public $notPromises;
    public $allPromises;


    public $inputValue = "Initial value";

    // temporary account
    public $phone_number;
    
    public function mount()
    {
        // Initialize the permission system for this module
        $this->initializeWithModulePermissions();
    }
    
    /**
     * Override to specify the module name for permissions
     * 
     * @return string
     */
    public function getModuleName(): string
    {
        return 'dashboard';
    }
    public $pay_by;
    public $startDate;
    public $endDate;
    public $pay_in_days;
    public $cheque_values;
    public bool $enableCheque=false;

    protected $listeners = [
        'refreshClientsListComponent' => '$refresh',
        'installmentPromises'=>'promises',
        'chequeData'=>'chequeList'
        ];




    public function visit($item)
    {

        Session::put('savingsViewItem', $item);
        $this->item = $item;
        $this->emit('refreshSavingsComponent');
    }

    public function promises($id){
        $this->selectedLoanId=$id;
        $data=loans_schedules::where('id',$id)->first();
        $this->promise_description=$data->comment;
        $this->promise_date=$data->promise_date;
        $this->createPromises=true;

    }



    public function  createPromise(){
        $this->validate(['promise_description'=>'required', 'promise_date'=>'required']);

        loans_schedules::where('id',$this->selectedLoanId)->update(['comment'=>$this->promise_description,
               'promise_date'=>$this-> promise_date
            ]);


        $this->resetPromises();
        session()->flash('message','successfully ');
        sleep(2);
        $this->emit('refreshLoanUpdateTable');
        $this->createPromises=false;


    }

    public function resetPromises(){
        $this->promise_date=null;
        $this->promise_description=null;

    }


    public function setView($id){
        $this->selected=$id;
    }

    public function boot()
    {



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

        $this->registrationFee = institutions::where('id',auth()->user()->institution_id)->value('registration_fees');
//        $this->registrationFee = institutions::where('institution_id',Session::get('institution'))->value('application_fee');
        $initial_shares = institutions::where('institution_id',Session::get('institution'))->value('initial_shares');
        $value_per_share = institutions::where('institution_id',Session::get('institution'))->value('value_per_share');
        if($initial_shares && $value_per_share){
            $this->initial_shares_value = $initial_shares * $value_per_share;
        }

    }



    public function setDashboardType($type)
    {
        if (!$this->authorize('view', 'You do not have permission to change dashboard type')) {
            return;
        }
        
        // Logic to handle dashboard type change would go here
        // For now, we'll just validate the permission
    }

    public function menuItemClicked($tabId)
    {
        if (!$this->authorize('view', 'You do not have permission to view this section')) {
            return;
        }
        
        $this->tab_id = $tabId;
        if ($tabId == '1') {
            $this->title = 'Deposits report';
        }
        if ($tabId == '2') {
            $this->title = 'Enter new shares deposits';
        }
        if ($tabId == '3') {
            $this->title = 'Enter new shares deposits';
        }

    }


    protected $rules = [

        'bank' => 'required|min:1',
        'amount' => 'required|min:1',
        'account_number' => 'required|min:1',
    ];

public function resetNewClientRegistrationData(){
    // reset your data
    $this->phone_number=null;
    $this->reference_number=null;
}


    public function process(){

    /// for bank new member reg fee
    if($this->deposit_type=="BANK") {
        if ($this->member == 'new') {
            if ($this->depositType == 'RegistrationFee') {
                $this->validate(['deposit_type' => 'required', 'depositType' => 'required', 'reference_number' => 'required', 'phone_number' => 'required|string|min:9']);
                //check if phonr number exist
                $check_phone_number = PendingRegistration::where('phone_number', $this->phone_number)->first();
                // register to the  tempo account
                if ($check_phone_number) {
                    session()->flash('message_fail', 'phone number has been taken');
                } else {
                    PendingRegistration::create([
                        'reference_number' => $this->reference_number,
                        'amount' => $this->registrationFee,
                        'account_id' => $this->bank,
                        'branch_id' => auth()->user()->branch,
                        'phone_number' => $this->phone_number,
                        'status' => "INITIAL PAY",
                    ]);
                    $this->dryDeposit($this->phone_number, $this->registrationFee, $this->new_member_deposit_notes, $this->bank);
                    $this->resetNewClientRegistrationData();
                }

            } else
                if ($this->depositType == "MandatoryShares") {

                    // set the minimun mandatory shares
                    $this->validate(['deposit_type' => 'required', 'depositType' => 'required', 'reference_number' => 'required', 'phone_number' => 'required|string|min:9']);
                    // check if reference number exist
                    $check_account = PendingRegistration::where('phone_number', $this->phone_number)->get();
                    if (count($check_account) == 1) {
                        PendingRegistration::create([
                            'reference_number' => $this->reference_number,
                            'amount' => $this->initial_shares_value,
                            'account_id' => $this->bank,
                            'branch_id' => auth()->user()->branch,
                            'phone_number' => $this->phone_number,
                            'status' => "ACTIVE",
                        ]);

                        $this->mandatoryShareDepositNewClient($this->phone_number, $this->initial_shares_value, $this->bank);
                        $this->resetNewClientRegistrationData();
                    } else if (count($check_account) == 2) {
                        session()->flash('message_fail', 'process fail please check your phone number');
                    } else {
                        session()->flash('message_fail', 'pay registration fees  before  mandatory shares');
                    }


                }

        }

        else{

////           dd($this->product_number);
//            if($this->product_number == '2200'){
                $this->saveSavings();
//            }
////          deposit
//            if($this->product_number == '3000'){
//                $this->saveDeposit();
//            }
        }
    }


    else if($this->deposit_type=="CASH"){
           /// cash new member///
            if($this->member == 'new') {
                if($this->depositType=='RegistrationFee'){
                    $this->validate(['deposit_type' => 'required', 'depositType' => 'required','phone_number' => 'required|string|min:9']);
                    //check if phone number exist
                    $check_phone_number = PendingRegistration::where('phone_number', $this->phone_number)->first();
                    // register to the  tempo account
                      if ($check_phone_number) {
                           session()->flash('message_fail', 'phone number has been taken');
                         } else {
                           PendingRegistration::create([
                            'reference_number' => $this->reference_number,
                            'amount' => $this->registrationFee,
                            'account_id' => $this->bank,
                            'branch_id' => auth()->user()->branch,
                            'phone_number' => $this->phone_number,
                            'status' => "INITIAL PAY",
                           ]);
                          $this->cashRegistrationFees($this->phone_number,$this->registrationFee,$this->bank);
                        $this->resetNewClientRegistrationData();


                      }
                    }
                else

                    if($this->depositType=="MandatoryShares"){
                    // set the minimun mandatory shares
                    $this->validate(['deposit_type' => 'required', 'depositType' => 'required', 'phone_number' => 'required|string|min:9']);
                   // check if reference number exist
                   $check_account = PendingRegistration::where('phone_number', $this->phone_number)->get();
                        if (count($check_account) == 1) {
                    PendingRegistration::create([
                        'reference_number' => $this->reference_number,
                        'amount' => $this->initial_shares_value,
                        'account_id' => $this->bank,
                        'branch_id' => auth()->user()->branch,
                        'phone_number' => $this->phone_number,
                        'status' => "ACTIVE",
                    ]);

                    $this->mandatoryShareCashDepositNewClient($this->phone_number, $this->initial_shares_value, $this->bank);
                    $this->resetNewClientRegistrationData();
                   } else
                       if (count($check_account) == 2) {
                    session()->flash('message_fail', 'process fail please check your phone number');
                    } else {
                    session()->flash('message_fail', 'pay registration fees  before  mandatory shares');
                   }

            }

            }


            else{

                $this->validate(['amount'=>'required','depositType' => 'required']);
                    $this->cashDeposit();
            }
        }


        }


    public function process1(){
     if($this->payment_method=="BANK"){
         $this->withDrawDeposits();

     }
     else if($this->payment_method=="CASH"){
         $this->witdrawCashNow();
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

        if($teller_account->balance >= (double)$this->amount1  && $customer_account_balance >=(double)$this->amount1 ){
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
            $institution_id=auth()->user()->institution_id;


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
                'beneficiary_account_number' => $this->accountSelected,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' =>'cash withdraw',
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => '',
                'destination_bank_name' => '',
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
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
                'beneficiary_account_number' => $this->accountSelected,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' =>'cash withdraw',
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => '',
                'destination_bank_name' => '',
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
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

        $user = auth()->user();

        $team = $user->currentTeam;

        $institution = TeamUser::where('user_id',Auth::user()->id)->value('institution');

        approvals::create([
            'institution' => $institution,
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

        $this->validate();

        $institution_id=auth()->user()->institution_id;

      $this->bank=DB::table('accounts')->where('id',$this->bank)->value('account_number');


/////////////////credit ////////
        $savings_account_new_balance = (double)AccountsModel::where('account_number',$this->accountSelected)->value('balance')+(double)$this->amount;
/////////debit/////////////////
        $savings_ledger_account_new_balance = (double)AccountsModel::where('account_number', $this->bank)->value('balance')-(double)$this->amount;

//        $partner_bank_account_new_balance = (double)AccountsModel::where('account_number',$this->bank)->value('balance')+(double)$this->amount;

        AccountsModel::where('account_number',$this->accountSelected)->update(['balance'=>$savings_account_new_balance]);
        AccountsModel::where('account_number', $this->bank)->update(['balance'=>$savings_ledger_account_new_balance]);
//        AccountsModel::where('account_number',$this->bank)->update(['balance'=>$partner_bank_account_new_balance]);

        $reference_number = time();


        //CREDIT RECORD
        general_ledger::create([
            'record_on_account_number'=> $this->accountSelected,
            'record_on_account_number_balance'=> $savings_account_new_balance,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number', $this->bank)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number', $this->bank)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('sub_product_number'),
            'sender_id'=> '999999',//mirrorId
            'beneficiary_id'=> $this->member,
            'sender_name'=> 'Mirror Account',
            'beneficiary_name'=> Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);



        //DEBIT RECORD GL
        general_ledger::create([
            'record_on_account_number'=>  $this->bank,
            'record_on_account_number_balance'=> $savings_ledger_account_new_balance ,
            'sender_branch_id'=> $institution_id,
            'beneficiary_branch_id'=> $institution_id,
            'sender_product_id'=>  AccountsModel::where('account_number', $this->bank)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number', $this->bank)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$this->accountSelected)->value('sub_product_number'),
            'sender_id'=> '999999',
            'beneficiary_id'=> $this->member,
            'sender_name'=> AccountsModel::where('account_number', $this->bank)->value('account_name'),

            'beneficiary_name'=>  Clients::where('client_number',$this->member)->value('first_name').' '.Clients::where('client_number',$this->member)->value('middle_name').' '.Clients::where('client_number',$this->member)->value('last_name'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'Pending',
        ]);

//        $this->sendApproval($id,'New savings transaction','06');

        $this->resetData();

        Session::flash('message1', 'Savings has been successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }



    public function cashDeposit()
    {

        //        $this->validate();
//////////////////////////////////////////////////////CREDIT TO TELLER AND CUSTOMER ACCOUNT///////////////////////////////////////
        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');

        $institution_id = auth()->user()->institution_id;
        $id = auth()->user()->id;


        $teller_account=DB::table('accounts')->where('id',$get_account_id)->value('account_number');


        $savings_account_new_balance = (double)AccountsModel::where('account_number', $this->accountSelected)->value('balance') + (double)$this->amount;

        $teller_account_new_balance = (double)AccountsModel::where('account_number', $teller_account)->value('balance') + (double)$this->amount;

        AccountsModel::where('account_number', $this->accountSelected)->update(['balance' => $savings_account_new_balance]);
        AccountsModel::where('account_number', $teller_account)->update(['balance' => $teller_account_new_balance]);
//       AccountsModel::where('account_number', $this->bank)->update(['balance' => $partner_bank_account_new_balance]);

        $reference_number = time();
        // as sender Branch Id;
        $institution_id=auth()->user()->institution_id;

        //CREDIT RECORD ON CUSTOMER ACCOUNT
        general_ledger::create([
            'record_on_account_number' => $this->accountSelected,
            'record_on_account_number_balance' => $savings_account_new_balance,
            'sender_branch_id' => $institution_id,
            'beneficiary_branch_id' => $institution_id,
            'sender_product_id' =>'0000',
            'sender_sub_product_id' =>'0000',
            'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('product_number'),
            'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected)->value('sub_product_number'),
            'sender_id' => '999999',
            'beneficiary_id' => $this->member,
            'sender_name' => 'Organization',
            'beneficiary_name' => Clients::where('client_number', $this->member)->value('first_name') . ' ' . Clients::where('client_number', $this->member)->value('middle_name') . ' ' . Clients::where('client_number', $this->member)->value('last_name'),
            'sender_account_number' => '0000',
            'beneficiary_account_number' => $this->accountSelected,
            'transaction_type' => 'IFT',
            'sender_account_currency_type' => 'TZS',
            'beneficiary_account_currency_type' => 'TZS',
            'narration' => $this->notes,
            'credit' => (double)$this->amount,
            'debit' => 0,
            'reference_number' => $reference_number,
            'trans_status' => 'Successful',
            'trans_status_description' => 'Successful',
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => null,
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
//            'partner_bank' => AccountsModel::where('account_number', $this->bank)->value('institution_number'),
//            'partner_bank_name' => AccountsModel::where('account_number', $this->bank)->value('account_name'),
//            'partner_bank_account_number' => $this->bank,
//            'partner_bank_transaction_reference_number' => $this->reference_number,
        ]);


        //CREDIT CASH ON TELLER ACCOUNT
        general_ledger::create([
            'record_on_account_number' => $teller_account,
            'record_on_account_number_balance' => $teller_account_new_balance,
            'sender_branch_id' => $institution_id,
            'beneficiary_branch_id' => $institution_id,
            'sender_product_id' =>'0000',
            'sender_sub_product_id' =>'0000',
            'beneficiary_product_id' => AccountsModel::where('account_number', $teller_account)->value('product_number'),
            'beneficiary_sub_product_id' => AccountsModel::where('account_number', $teller_account)->value('sub_product_number'),
            'sender_id' => '000',
            'beneficiary_id' => AccountsModel::where('account_number', $teller_account)->value('institution_number'),
            'sender_name' =>'Organization',
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
            'swift_code' => '',
            'destination_bank_name' => '',
            'destination_bank_number' => null,
            'payment_status' => 'Successful',
            'recon_status' => 'Pending',
        ]);





        $this->sendApproval($id,'New deposit transaction','07');

        $this->resetData();

        Session::flash('message', 'Funds deposited successfully!');
        Session::flash('alert-class', 'alert-success');

    }

    public function withDrawDeposits()
    {
        $this->validate(['amount1'=>'required','payment_method'=>'required']);

              $institution_id = 1;
        $id = auth()->user()->id;
        $currentUser = DB::table('team_user')->where('user_id', $id)->get();
        foreach ($currentUser as $User) {
             $institution_id = $User->team_id;
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
        if($teller_account->balance > (double)$this->amount1  &&  $customer_account < (double)$this->amount1){

        ///////////////////// teller account number
            $teller_account_number = $teller_account->account_number;

        ////////////////////////   Teller account  update account
            $new_teller_account_balance=(double)($teller_account->balance -(double)$this->amount1);

            DB::table('accounts')->where('account_number',$teller_account_number)->update([''=>$new_teller_account_balance]);
            /////////////////  CUSTOMER DETAILS (debit account)  //////////////////
          $customer_new_balance=  (double)AccountsModel::where('account_number', $this->accountSelected1)->value('balance') - (double)$this->amount1;

          // update customer accounts
            DB::table('accounts')->where('account_number',$this->accountSelected1)->update(['balance'=>$customer_new_balance]);


            $reference_number = time();

            //DEBIT RECORD TO TELLER
            general_ledger::create([
                'record_on_account_number' =>$teller_account_number,
                'record_on_account_number_balance' => $new_teller_account_balance,
                'sender_branch_id' => $institution_id,
                'beneficiary_branch_id' => $institution_id,
                'sender_product_id' => AccountsModel::where('account_number',$teller_account_number )->value('product_number'),
                'sender_sub_product_id' => AccountsModel::where('account_number', $teller_account_number)->value('sub_product_number'),
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('sub_product_number'),
                'sender_id' => $this->member1,
                'beneficiary_id' => '999999',
                'sender_name' => Clients::where('client_number', $this->member1)->value('first_name') . ' ' . Clients::where('client_number', $this->member1)->value('middle_name') . ' ' . Clients::where('client_number', $this->member1)->value('last_name'),
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
                'swift_code' => '',
                'destination_bank_name' => '',
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
            ]);



            //CREDIT  CUSTOMER ACCOUNT
            general_ledger::create([
                'record_on_account_number' => $this->accountSelected1,
                'record_on_account_number_balance' => $customer_new_balance,
                'sender_branch_id' => $institution_id,
                'beneficiary_branch_id' => $institution_id,
                'sender_product_id' => AccountsModel::where('account_number',$teller_account_number )->value('product_number'),
                'sender_sub_product_id' => AccountsModel::where('account_number', $teller_account_number)->value('sub_product_number'),
                'beneficiary_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('product_number'),
                'beneficiary_sub_product_id' => AccountsModel::where('account_number', $this->accountSelected1)->value('sub_product_number'),
                'sender_id' => AccountsModel::where('account_number', $this->bank1)->value('institution_number'),
                'beneficiary_id' => $this->member1,
                'sender_name' => AccountsModel::where('account_number', $this->bank1)->value('account_name'),
                'beneficiary_name' => Clients::where('client_number', $this->member1)->value('first_name') . ' ' . Clients::where('client_number', $this->member1)->value('middle_name') . ' ' . Clients::where('client_number', $this->member1)->value('last_name'),
                'sender_account_number' => $this->bank1,
                'beneficiary_account_number' =>$this->accountSelected1,
                'transaction_type' => 'IFT',
                'sender_account_currency_type' => 'TZS',
                'beneficiary_account_currency_type' => 'TZS',
                'narration' => $this->notes1,
                'credit' => 0,
                'debit' => (double)$this->amount1,
                'reference_number' => $reference_number,
                'trans_status' => 'Successful',
                'trans_status_description' => 'Successful',
                'swift_code' => '',
                'destination_bank_name' => '',
                'destination_bank_number' => null,
                'payment_status' => 'Successful',
                'recon_status' => 'Pending',
              ]);

        }
        else{
            session()->flash('message_fail1','sorry you dont have enough funds');
        }



        $this->sendApproval($id,'New withDraw transaction','08');

        $this->resetData1();

        Session::flash('message2', 'Funds deposited successfully!');
        Session::flash('alert-class', 'alert-success');

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
    }

    public function dryDeposit($phone_number,$amount,$narration,$account_id)
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
        //update teller account
        DB::table('accounts')->where('id',$account_id)->update(['balance'=>$mirror_account_new_balance]);


        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING REGISTRATION FEE/////////////////////////////////
      $account_details=  DB::table('accounts')->where('product_number',4000)
                         ->where('institution_number',auth()->user()->institution_id)
                          ->where('sub_product_number',4210)->first();
    // get account balance
        $registration_fee_account_balance=$account_details->balance;
     // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
              DB::table('accounts')->where('institution_number',auth()->user()->institution_id)
             ->where('product_number',4000)->where('sub_product_number',4210)
             ->update(['balance'=>$registration_fee_account_new_balance]);


              $reference_number = time();

        $institution_id=auth()->user()->institution_id;

        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $mirror_account,
            'record_on_account_number_balance'=> $mirror_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> DB::table('accounts')->where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> DB::table('accounts')->where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> DB::table('accounts')->where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> DB::table('accounts')->where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
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
            'sender_product_id'=>  AccountsModel::where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
            'sender_name'=> AccountsModel::where('account_number',$mirror_account )->value('account_name'),
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
            'sender_account_number'=> $mirror_account,
            'beneficiary_account_number'=>$account_details->account_number ,
            'transaction_type'=> 'IFT',
            'sender_account_currency_type'=> 'TZS',
            'beneficiary_account_currency_type'=> 'TZS',
            'narration'=>$narration,
            'credit'=> $amount,
            'debit'=> 0,
            'reference_number'=> $reference_number,
            'trans_status'=> 'Successful',
            'trans_status_description'=> 'Successful',
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
        $this->resetData();



        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done

    public function mandatoryShareDepositNewClient($phone_number,$amount,$account_id)
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
      $account_details=  DB::table('accounts')->where('product_number',2000)
                         ->where('institution_number',auth()->user()->institution_id)
                          ->where('sub_product_number',2560)->first();
    // get account balance
        $registration_fee_account_balance=$account_details->balance;
     // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
              DB::table('accounts')->where('institution_number',auth()->user()->institution_id)
             ->where('product_number',2000)->where('sub_product_number',2560)
             ->update(['balance'=>$registration_fee_account_new_balance]);


              $reference_number = time();

        $institution_id=auth()->user()->institution_id;

        //DEBIT RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $mirror_account,
            'record_on_account_number_balance'=> $mirror_account_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
            'beneficiary_branch_id'=>  auth()->user()->branch,
            'sender_product_id'=> DB::table('accounts')->where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> DB::table('accounts')->where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> DB::table('accounts')->where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> DB::table('accounts')->where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
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
            'sender_product_id'=>  AccountsModel::where('account_number',$mirror_account)->value('product_number'),
            'sender_sub_product_id'=> AccountsModel::where('account_number',$mirror_account)->value('sub_product_number'),
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=> auth()->user()->id,
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
            'sender_name'=> AccountsModel::where('account_number',$mirror_account )->value('account_name'),
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
            'sender_account_number'=> $mirror_account,
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
        $this->resetData();



        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done



    public function mandatoryShareCashDepositNewClient($phone_number,$amount,$account_id)
    {
        ////////////////////////////////////CREDIT TELLER ACCOUNT//////////////////////////////////////
        ///
        ///
        ///
        ///

        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');

        $get_account=DB::table('accounts')->where('id',$get_account_id)->first();

        ///
        $teller_account_number=$get_account->account_number;
        // balance get;
        $teller_balance=$get_account->balance;
        // new mirror account balance
        $teller_new_balance=$teller_balance+$amount;
        //update mirror balance
        DB::table('accounts')->where('id',$account_id)->update(['balance'=>$teller_new_balance]);


        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING REGISTRATION FEE/////////////////////////////////
      $account_details=DB::table('accounts')->where('product_number',2000)
                         ->where('institution_number',auth()->user()->institution_id)
                          ->where('sub_product_number',2560)->first();
    // get account balance
        $registration_fee_account_balance=$account_details->balance;
     // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
              DB::table('accounts')->where('institution_number',auth()->user()->institution_id)
             ->where('product_number',2000)->where('sub_product_number',2560)
             ->update(['balance'=>$registration_fee_account_new_balance]);


              $reference_number = time();

        $institution_id=auth()->user()->institution_id;

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
            'swift_code'=> '',
            'destination_bank_name'=> '',
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
            'sender_product_id'=>  '0000',
            'sender_sub_product_id'=> '0000',
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=> '0000',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->where('status','ACTIVE')->value('id'),
            'sender_name'=> 'CASH',
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
        $this->resetData();



        Session::flash('message1', 'Successfully deposited!');
        Session::flash('alert-class', 'alert-success');

    }   // done







    public function cashRegistrationFees($phone_number,$amount,$account_id)
    {
        ////////////////////////////////////CREDIT ON TELLER SETTLEMENT  ACCOUNT//////////////////////////////////////

        //// get  teller account


        $get_account_id =DB::table('tellers')->where('employee_id',auth()->user()->employeeId)->value('account_id');


        $get_account=DB::table('accounts')->where('id',$get_account_id)->first();
        ///
        $teller_account_number=$get_account->account_number;
        // balance get;
        $teller_balance=$get_account->balance;

        // new mirror account balance
        $teller_new_balance=$teller_balance+$amount;
        //update mirror balance
        DB::table('accounts')->where('account_number',$teller_account_number)->update(['balance'=>$teller_new_balance]);



        ///////////////////////////// CREDIT ON ACCOUNT FOR HOLDING  CASH REGISTRATION FEE/////////////////////////////////
      $account_details=  DB::table('accounts')->where('product_number',4000)
                             ->where('institution_number',auth()->user()->institution_id)
                             ->where('sub_product_number',4210)->first();


////////////   get account balance  ///////////////////////////////
        $registration_fee_account_balance=$account_details->balance;
     // new balance on registration fees
        $registration_fee_account_new_balance=$registration_fee_account_balance+$amount;
        // update registration fee account balance
              DB::table('accounts')->where('institution_number',auth()->user()->institution_id)
             ->where('product_number',4000)->where('sub_product_number',2560)
             ->update(['balance'=>$registration_fee_account_new_balance]);


              $reference_number = time();

        $institution_id=auth()->user()->institution_id;

        //CREDIT ON TELLER RECORD MEMBER
        general_ledger::create([
            'record_on_account_number'=> $teller_account_number,
            'record_on_account_number_balance'=> $teller_new_balance,
            'sender_branch_id'=> auth()->user()->branch,
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
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
            'sender_product_id'=> '0000',
            'sender_sub_product_id'=>'0000',
            'beneficiary_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('product_number'),
            'beneficiary_sub_product_id'=> AccountsModel::where('account_number',$account_details->account_number)->value('sub_product_number'),
            'sender_id'=>'0000',
            'beneficiary_id'=> DB::table('pending_registrations')->where('phone_number',$phone_number)->value('id'),
            'sender_name'=>'CASH',
            'beneficiary_name'=> AccountsModel::where('account_number',$account_details->account_number )->value('account_name'),
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
            'swift_code'=> '',
            'destination_bank_name'=> '',
            'destination_bank_number'=> null,
            'payment_status'=> 'Successful',
            'recon_status'=> 'PENDING',
        ]);

        $this->sendApproval($account_details->id,'Processed a new member deposit transaction - '.$this->new_member_deposit_notes,'06');
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

    public function back()
    {

        Session::put('memberNameInView', '');
        Session::put('memberIdInView', '');
        Session::put('showAddClient', false);
        $this->emit('refreshClientsListComponent');
    }

    public function setAccount($account){
        $this->accountSelected = $account;
        $this->product_number = AccountsModel::where('account_number', $account)->value('product_number');
        //dd($this->product_number);
    }

    public function setAccount1($account){
        $this->accountSelected1 = $account;
        $this->product_number1 = AccountsModel::where('account_number', $account)->value('product_number');
        //dd($this->product_number);
    }



    public function updatedTerm(){
        // Perform the search operation based on the $term value
        session(['term' => $this->term]); // Store the current term in the session
        $this->emit('refreshTransactionsTable');

    }

    public function render()
    {



        // get yesterday's date
        $yesterday=Carbon::yesterday()->format('Y-m-d');

        // Get today's date in 'Y-m-d' format
        $today = Carbon::today()->format('Y-m-d');



        // Get tomorrow's date in 'Y-m-d' format
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        // Get the day after tomorrow's date in 'Y-m-d' format
        $dayAfterTomorrow = Carbon::tomorrow()->addDay()->format('Y-m-d');

            $loanId=LoansModel::where('supervisor_id',auth()->user()->id)->pluck('loan_id');

            // Query to get records where the date is today, tomorrow, or day after tomorrow
            $this->allPromises = loans_schedules::query()->whereIn('loan_id',$loanId)->
            whereDate('promise_date', $today)
                ->orWhereDate('promise_date', $tomorrow)
                ->orWhereDate('promise_date', $yesterday)
                ->orWhereDate('promise_date', $dayAfterTomorrow)
              ->count();

                //count();


            $this->notPromises = loans_schedules::query()->whereIn('loan_id',$loanId)->
            whereDate('installment_date', $today)
                ->orWhereDate('installment_date', $tomorrow)
                ->orWhereDate('installment_date', $yesterday)
                ->orWhereDate('installment_date', $dayAfterTomorrow)->count();






        $query = sub_products::where('product_id', 13);
        $this->products = $query->get();
        $this->numberOfProducts = $query->count();
        Session::put('userRole','Teller');



        return view('livewire.dashboard.dashboard', array_merge(
            $this->permissions,
            [
                'permissions' => $this->permissions
            ]
        ));
    }
}
